<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Brand;
use App\Models\UnitMeasure;
use App\Models\ProductDetails;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use DataTables;
// use Validator;
use Gate;
use App\DataTables\ProductDataTable;
use App\Services\Odoo\ProductSync;
use App\Exports\BranchStockExport;
use App\Exports\BranchStockTemplate;
use App\Imports\ProductImport;
use App\Exports\ProductExport;
use App\Exports\ProductTemplate;
use App\Imports\BranchStockImport;
use App\Models\Branch;
use App\Models\BranchStock;
use Excel;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct() 
    {     
        $this->middleware('auth');   
        $this->products = new Product();
        $this->path = 'products';
    }
    
    public function index(Request $request)
    {
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $categories = Category::where('active', 'Y')->get();
        $subCategories = Subcategory::where('active', 'Y')->get();
        if(!isCustomerUser()){
            // Odoo sync panel: products come from Odoo (cron twice a day, or Sync now)
            $odooLastRun = DB::table('odoo_sync_logs')->where('entity', ProductSync::ENTITY)->orderByDesc('id')
                ->first(['created_at', 'status_code', 'received_count', 'failed_count']);
            $odooCounts = [
                'total' => Product::whereNotNull('odoo_product_id')->count(),
                'active' => Product::whereNotNull('odoo_product_id')->where('active', 'Y')->count(),
            ];
            return view('products.index', compact('categories', 'subCategories', 'odooLastRun', 'odooCounts'));
        }else{
            return view('products.dealer_product', compact('categories', 'subCategories'));
        }
    }

    /**
     * "Sync now" on Product List: the same pull the cron runs twice a day.
     */
    public function odooSync(ProductSync $sync)
    {
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // ~1300 products over a few Odoo pages; don't let PHP's web time limit cut the sync
        set_time_limit(300);

        $result = $sync->run();
        unset($result['errors']);

        return response()->json($result, $result['status_code'] === 502 ? 502 : 200);
    }

    public function productList(ProductDataTable $dataTable, Request $request)
    {
        return $dataTable->render('products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_if(Gate::denies('product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('products.create', $this->formOptions())->with('products',$this->products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        abort_if(Gate::denies('product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $product = Product::create($this->productAttributes($request) + ['created_by' => Auth::user()->id]);
        $this->savePrimaryDetail($product->id, $request);
        return Redirect::to('products')->with('message_success', 'Product Store Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort_if(Gate::denies('product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $id = decrypt($id);
        $products = Product::with(['categories', 'subcategories', 'unitmeasures', 'productdetails'])->findOrFail($id);
        // Odoo sync row for this product (cost price, Odoo timestamps, sub-category name)
        $odoo = $products->odoo_product_id
            ? DB::table('odoo_products')->where('product_odoo_id', $products->odoo_product_id)->first()
            : null;
        return view('products.show', compact('odoo'))->with('products',$products);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $id = decrypt($id);
        $products = Product::with('productdetails')->findOrFail($id);
        return view('products.create', $this->formOptions())->with('products',$products);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $product = Product::findOrFail(decrypt($id));
        $product->update($this->productAttributes($request) + ['updated_by' => Auth::user()->id]);
        $this->savePrimaryDetail($product->id, $request);
        return Redirect::to('products')->with('message_success', 'Product Update Successfully');
    }

    /**
     * Category, sub-category and unit choices for the product form.
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::where('active','=','Y')->orderBy('category_name')->select('id', 'category_name')->get(),
            'subcategories' => Subcategory::where('active','=','Y')->orderBy('subcategory_name')->select('id', 'subcategory_name', 'category_id')->get(),
            'units' => UnitMeasure::where('active','=','Y')->orderBy('unit_name')->select('id', 'unit_name')->get(),
        ];
    }

    /**
     * The product fields Odoo syncs: name, category, sub-category, unit, status.
     */
    private function productAttributes(ProductRequest $request): array
    {
        return [
            'product_name'   => $request->input('product_name'),
            'display_name'   => $request->input('product_name'),
            'category_id'    => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'unit_id'        => $request->input('unit_id'),
            'active'         => $request->input('active', 'Y'),
        ];
    }

    /**
     * MRP and GST on the primary product_details row, the same row ProductSync writes
     * (price and selling price follow MRP, as in the sync).
     */
    private function savePrimaryDetail(int $productId, ProductRequest $request): void
    {
        $mrp = (float) $request->input('mrp');
        $detail = [
            'mrp' => $mrp,
            'price' => $mrp,
            'selling_price' => $mrp,
            'gst' => $request->filled('gst') ? (float) $request->input('gst') : null,
        ];
        $detailId = ProductDetails::where('product_id', $productId)->orderByDesc('isprimary')->orderBy('id')->value('id');

        if ($detailId) {
            ProductDetails::where('id', $detailId)->update($detail);
        } else {
            ProductDetails::create($detail + ['product_id' => $productId, 'active' => 'Y', 'isprimary' => 1]);
        }
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        ProductDetails::where('product_id',$id)->delete();
        $product = Product::find($id);
        if($product->delete())
        {
            return response()->json(['status' => 'success','message' => 'Product deleted successfully!']);
        }
        return response()->json(['status' => 'error','message' => 'Error in User Delete!']);
    }
    
    public function active(Request $request)
    {
        if(Product::where('id',$request['id'])->update(['active' => ($request['active'] == 'Y') ? 'N' :'Y']))
        {
            $message = ($request['active'] == 'Y') ? 'Inactive' :'Active';
            return response()->json(['status' => 'success','message' => 'Product '.$message.' Successfully!']);
        }
        return response()->json(['status' => 'error','message' => 'Error in Status Update']);
    }
    public function upload(Request $request) 
    {
        abort_if(Gate::denies('product_upload'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        Excel::import(new ProductImport,request()->file('import_file'));
        return back();
    }
    public function download(Request $request)
    {
        abort_if(Gate::denies('product_download'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return Excel::download(new ProductExport($request), 'products.xlsx');
    }
    public function template()
    {
        abort_if(Gate::denies('product_template'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return Excel::download(new ProductTemplate, 'products.xlsx');
    }

    // public function stockInfo(Request $request)
    // {
    //     $products = ProductDetails::select('id','product_id','stock_qty','detail_title','mrp')->get();
    //     return view('products.stock',compact('products') );
    // }

    // public function stockUpdate(Request $request)
    // {
    //     foreach ($request['detail'] as $key => $rows) {
    //         ProductDetails::where('id',$rows['detail_id'])->update([
    //             'stock_qty'      => isset($rows['stock_qty']) ? $rows['stock_qty'] :null,
    //             'updated_at'    => getcurentDateTime(),
    //         ]);
    //     }
    //     return Redirect::to('stockinfo')->with('message_success', 'Product Update Successfully');
    // }

    public function production(Request $request)
    {
        $products = ProductDetails::select('id','product_id','production_qty','detail_title','mrp')->get();
        return view('products.production',compact('products') );
    }

    public function productionUpdate(Request $request)
    {
        foreach ($request['detail'] as $key => $rows) {
            ProductDetails::where('id',$rows['detail_id'])->update([
                'production_qty'      => isset($rows['production_qty']) ? $rows['production_qty'] :null,
                'updated_at'    => getcurentDateTime(),
            ]);
        }
        return Redirect::to('production')->with('message_success', 'Product Update Successfully');
    }

    public function checkProductCode(Request $request){
        $check_code = Product::where('product_code', $request->product_code)->first();
        if($check_code){
            return false;
        }else{
            return true;
        }
    }

    public function stock(Request $request)
    {
        $userids = getUsersReportingToAuth();
        $branches = Branch::where('active', 'Y')->latest()->get();

        if ($request->ajax()) {
            $data = BranchStock::with('branch','division', 'warehouse')->select(
                'division_id',
                'branch_id',
                'warehouse_id',
                'year',
                'quarter',
                DB::raw('SUM(amount) as total_amounts'),
                DB::raw('GROUP_CONCAT(amount) as amounts'),
                DB::raw('GROUP_CONCAT(days) as days'),
                DB::raw('JSON_OBJECTAGG(days, amount) as day_amount_pairs'),
            );
            if($request->branch_id && !empty($request->branch_id)){
                $data->where('branch_id', $request->branch_id);
            }
            $data = $data->groupBy('division_id','branch_id', 'year', 'quarter', 'warehouse_id');
            
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('first_slot', function ($data) {
                    $day_wise_amount_array = json_decode($data->day_amount_pairs, true);
                    return $day_wise_amount_array['0-30']??'0';
                })
                ->addColumn('second_slot', function ($data) {
                    $day_wise_amount_array = json_decode($data->day_amount_pairs, true);
                    return $day_wise_amount_array['31-60']??'0';
                })
                ->addColumn('thired_slot', function ($data) {
                    $day_wise_amount_array = json_decode($data->day_amount_pairs, true);
                    return $day_wise_amount_array['61-90']??'0';
                })
                ->addColumn('fourth_slot', function ($data) {
                    $day_wise_amount_array = json_decode($data->day_amount_pairs, true);
                    return $day_wise_amount_array['91-150']??'0';
                })
                ->addColumn('fifth_slot', function ($data) {
                    $day_wise_amount_array = json_decode($data->day_amount_pairs, true);
                    return $day_wise_amount_array['150']??'0';
                })

                ->rawColumns(['first_slot','second_slot','thired_slot','fourth_slot','fifth_slot'])
                ->make(true);
        }

        return view('products.stock', compact('branches'));
    }

    public function stock_upload(Request $request)
    {
        abort_if(Gate::denies('stock_upload'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        Excel::import(new BranchStockImport, request()->file('import_file'));

        return back()->with('success', 'Primary Sales Import successfully !!');
    }

    public function stock_template(Request $request)
    {
        abort_if(Gate::denies('stock_template'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return Excel::download(new BranchStockTemplate, 'branch_stock_template.xlsx');
    }

    public function stock_download(Request $request)
    {
        abort_if(Gate::denies('stock_download'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return Excel::download(new BranchStockExport($request), 'stock.xlsx');
    }

    public function dealer_product(Request $request)
    {
        $categories = Category::where('active', 'Y')->get();
        
        return view('products.dealer_product', compact('categories'));
    }

    public function dealer_product_list($category_id)
    {
        $products = Product::with('productpriceinfo')->where('category_id', $category_id)->get();
        $categories = Category::where('active', 'Y')->get();

        return view('products.dealer_product_list', compact('products', 'category_id', 'categories'));
    }
public function getProductsBySubcategory(Request $request)
{
    $products = Product::with(['productdetails' => function ($query) {
                    $query->orderByRaw("CASE WHEN isprimary IN ('Y', '1') THEN 0 ELSE 1 END")
                        ->orderBy('id');
                }])
                ->where('subcategory_id', $request->subcategory_id)
                ->where('active', 'Y')
                ->select('id', 'product_name', 'product_image', 'display_name', 'product_code', 'subcategory_id', 'hsn_sac')
                ->orderBy('product_name')
                ->get()
                ->map(function ($product) {
                    $detail = $product->productdetails->first();
                    $product->unit_price = (float) ($detail?->price ?? $detail?->mrp ?? $detail?->selling_price ?? 0);
                    return $product;
                });

    return response()->json([
        'products' => $products
    ]);
}
}
