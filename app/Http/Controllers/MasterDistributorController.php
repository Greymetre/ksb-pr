<?php

namespace App\Http\Controllers;

use App\Models\MasterDistributor;
use App\Models\User;
use App\Models\City;
use App\Models\Beat;
use App\Models\State;
use App\Models\Pincode;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterDistributorsExport;
use App\Exports\MasterDistributorsTemplateExport;
use App\Imports\MasterDistributorsImport;
use Illuminate\Support\Facades\Auth;

class MasterDistributorController extends Controller
{
    /* ================= INDEX ================= */
    public function index(Request $request)
{
    if ($request->ajax()) {
        $query = MasterDistributor::query()->select([
            'id', 'distributor_code', 'legal_name', 'trade_name',
            'contact_person', 'mobile', 'billing_city', 'billing_state',
            'business_status', 'created_at'
        ]);
        $allowedUserIds = getUsersReportingToAuth();

            $query->where(function ($q) use ($allowedUserIds) {
                
                // supervisor match
                $q->whereIn('supervisor_id', $allowedUserIds);

                // OR sales_executive JSON match
                $q->orWhere(function ($sub) use ($allowedUserIds) {
                    foreach ($allowedUserIds as $id) {
                        $sub->orWhereJsonContains('sales_executive_id', $id);
                    }
                });

            });

        if ($request->filled('global_search')) {
        $search = $request->global_search;
        $query->where(function ($q) use ($search) {
        $q->where('distributor_code', 'like', "%{$search}%")
          ->orWhere('legal_name', 'like', "%{$search}%")
          ->orWhere('trade_name', 'like', "%{$search}%")
          ->orWhere('contact_person', 'like', "%{$search}%")
          ->orWhere('mobile', 'like', "%{$search}%")
          ->orWhere('billing_city', 'like', "%{$search}%")
          ->orWhere('billing_state', 'like', "%{$search}%");
    });
    }

        // Filters
        if ($request->code) {
            $query->where('distributor_code', 'like', "%{$request->code}%");
        }
        if ($request->name) {
            $query->where('legal_name', 'like', "%{$request->name}%");
        }
        if ($request->trade_name) {
            $query->where('trade_name', 'like', "%{$request->trade_name}%");
        }
        if ($request->contact_person) {
            $query->where('contact_person', 'like', "%{$request->contact_person}%");
        }
        if ($request->mobile) {
            $query->where('mobile', 'like', "%{$request->mobile}%");
        }
        // if ($request->billing_city) {
        //     $cityName = City::find($request->billing_city)?->city_name;
        //     if ($cityName) {
        //         $query->where('billing_city', $cityName);
        //     }
        // }
        // if ($request->billing_state) {
        //     $stateName = State::find($request->billing_state)?->state_name;
        //     if ($stateName) {
        //         $query->where('billing_state', $stateName);
        //     }
        // }

        if ($request->filled('billing_state_id')) {
    $stateName = State::find($request->billing_state_id)?->state_name;
    if ($stateName) {
        $query->where('billing_state', 'like', '%' . trim($stateName) . '%');
    }
}
//         if ($request->filled('billing_state')) {
//     $query->where('billing_state', 'like', '%' . trim($request->billing_state) . '%');
// }

if ($request->filled('billing_city')) {
    $query->where('billing_city', 'like', '%' . trim($request->billing_city) . '%');
}
        if ($request->status) {
            $query->where('business_status', $request->status);
        }

        // YE SEARCH FILTER ADD KIYA - TOP SEARCH BOX KE LIYE
        if ($request->filled('search')['value'] ?? false) {
            $search = $request->input('search.value');
            $query->where(function($q) use ($search) {
                $q->where('distributor_code', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'like', "%{$search}%")
                  ->orWhere('trade_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="manual_entry_cb" name="selected_ids[]" value="' . $row->id . '" />';
            })
            ->addColumn('action', function ($row) {
    $btn = '';
    $activebtn = '';

    // TEMPORARY: Sab permissions ignore kar ke buttons dikhao
    if(auth()->user()->can(['master_distributor_edit']))
    {
    $btn .= '<a href="' . route('master-distributors.edit', $row->id) . '" 
                class="btn btn-info btn-just-icon btn-sm" title="Edit">
                <i class="material-icons">edit</i>
             </a>';
    }
    if(auth()->user()->can(['master_distributor_show']))
    {
    $btn .= '<a href="' . route('master-distributors.show', $row->id) . '" 
                class="btn btn-info btn-just-icon btn-sm" title="View">
                <i class="material-icons">visibility</i>
             </a>';
    }
    if(auth()->user()->can(['master_distributor_delete']))
    {
             $btn .= '<form action="' . route('master-distributors.destroy', $row->id) . '" method="POST" style="display:inline;">
            ' . csrf_field() . '
            ' . method_field('DELETE') . '
            <button type="submit" class="btn btn-danger btn-just-icon btn-sm " title="Delete"
                    onclick="return confirm(\'Are you sure you want to delete this distributor?\')">
                <i class="material-icons">delete</i>
            </button>
         </form>';

    }
    if(auth()->user()->can(['master_distributor_active']))
    {
    $checked = ($row->business_status === 'Active') ? 'checked' : '';
    $activebtn = '<div class="togglebutton">
                    <label>
                        <input type="checkbox" ' . $checked . ' 
                               id="distributor_' . $row->id . '" 
                               class="distributor-status-toggle" 
                               data-id="' . $row->id . '">
                        <span class="toggle"></span>
                    </label>
                  </div>';
    }
    return '<div class="btn-group btn-group-sm" role="group">
                ' . $btn . ' ' . $activebtn . '
            </div>';
            
    })
            ->editColumn('business_status', function ($row) {
                return $row->business_status === 'Active'
                    ? '<span class="badge badge-success">ACTIVE</span>'
                    : '<span class="badge badge-danger">INACTIVE</span>';
            })
            ->editColumn('created_at', function ($row) {
                return showdatetimeformat($row->created_at);
            })
            ->editColumn('beat_route', function ($row) {
    return $row->beat_route 
        ? '<span class="badge badge-info">'.$row->beat_route.'</span>' 
        : '<span class="text-muted">-</span>';
})
// ->rawColumns(['beat_route'])
            // YE TEENO RAW COLUMNS ZAROORI HAIN!
            ->rawColumns(['checkbox', 'action', 'business_status','beat_route'])
            
            ->make(true);
            
    }     


    // View mein dropdown options ke liye data fetch kar rahe hain
   $filters = [
        'distributor_codes'  => MasterDistributor::distinct()->orderBy('distributor_code')->pluck('distributor_code')->filter()->values()->toArray(),
        'legal_names'        => MasterDistributor::distinct()->orderBy('legal_name')->pluck('legal_name')->filter()->values()->toArray(),
        'trade_names'        => MasterDistributor::distinct()->whereNotNull('trade_name')->orderBy('trade_name')->pluck('trade_name')->filter()->values()->toArray(),
        'contact_persons'    => MasterDistributor::distinct()->orderBy('contact_person')->pluck('contact_person')->filter()->values()->toArray(),
        'mobiles'            => MasterDistributor::distinct()->orderBy('mobile')->pluck('mobile')->filter()->values()->toArray(),
        'billing_cities'     => City::orderBy('city_name')->pluck('city_name', 'id')->toArray(),
        'billing_states'     => State::orderBy('state_name')->pluck('state_name', 'id')->toArray(),
        'business_statuses'  => ['Active', 'Inactive'],
    ];
    

    return view('master_distributors.index', compact('filters'));
}


    /* ================= CREATE ================= */
    public function create()
    {
        $distributor = new MasterDistributor();
        $users = User::pluck('name', 'id');
$beats = Beat::where('active', 'Y')
    ->whereHas('beatusers', function ($q) {
        $q->where('user_id', Auth::id());
    })
    ->orderBy('beat_name')
    ->pluck('beat_name', 'id');

        // dd($users->toArray());

        return view('master_distributors.create_edit', compact('distributor', 'users','beats'));
    }

    /* ================= STORE ================= */
   public function store(Request $request)
{
    // dd($request);
    $rules = [
        // Basic Info
        // 'legal_name'         => 'required|string|max:255',
        'distributor_code'   => 'required|string|max:100|unique:master_distributors,distributor_code',
        // 'category'           => 'required',
        'business_status'    => 'required|in:Active,Inactive,On Hold',
        'business_start_date'=> 'required|date',

        // Contact
        'contact_person'     => 'required|string|max:255',
        'mobile'             => 'required|digits:10|unique:master_distributors,mobile',
        'email'              => 'required|email|unique:master_distributors,email',

        // Business & Operation
        // 'sales_zone'         => 'required',
        // 'area_territory'     => 'required',
        'beat_route' => 'required|string',
        // 'market_classification' => 'required',
        // 'competitor_brands'  => 'required|string',
        

        // KYC
        // 'gst_number'         => 'required|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        // 'pan_number'         => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'registration_type'  => 'required',
        

        // Banking
        // 'bank_name'          => 'required|string',
        // 'account_holder'     => 'required|string',
        // 'account_number'     => 'required|string',
        // 'ifsc'               => 'required|string',
        // 'credit_limit'       => 'required|numeric|min:0',
        // 'credit_days'        => 'required|integer|min:0' ,

        // Sales
        // 'monthly_sales'      => 'required|numeric|min:0',
        // 'product_categories' => 'required|string',
        // 'sales_executive_id' => 'required|array|min:1',
        'sales_executive_id.*' => 'exists:users,id',
        'supervisor_id'      => 'required|exists:users,id',
        'customer_segment'   => 'required',

        // Additional
        // 'weekly_tai_alert'       => 'required',
        // 'target_vs_achievement'  => 'required',
        // 'schemes_updates'        => 'required',
        // 'new_launch_update'      => 'required',
        // 'payment_alert'          => 'required',
        // 'pending_orders'         => 'required',
        // 'inventory_status'       => 'required',

        // Capacity
        // 'turnover'               => 'required|numeric|min:0',
        // 'staff_strength'         => 'required|string',
        // 'vehicles_capacity'      => 'required|string',
        // 'area_coverage'          => 'required|string',
        // 'other_brands_handled'   => 'required|string',
        // 'warehouse_size'         => 'required|string',

        // Files
        'shop_image'             => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        'profile_image'          => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        'cancelled_cheque'       => 'nullable|mimes:pdf,jpeg,png,jpg|max:5120',
        'documents.*'            => 'nullable|mimes:pdf,jpeg,png,jpg|max:5120',
        'mou_file'               => 'nullable|mimes:pdf,jpeg,png,jpg|max:5120',
    ];

    // Agar same_as_billing checked nahi hai to shipping fields required
    // if (!$request->has('same_as_billing') || $request->same_as_billing != 1) {
    //     $rules += [
    //         'shipping_address'   => 'required|string',
    //         'shipping_city'      => 'required|string',
    //         'shipping_district'  => 'required|string',
    //         'shipping_state'     => 'required|string',
    //         'shipping_country'   => 'required|string',
    //         'shipping_pincode'   => 'required|string',
    //     ];
    // }

    $request->validate($rules);
    
    // Data collect
    $data = $request->all();
    $data['created_by'] = Auth::id(); 
    $data['beat_route'] = $request->beat_route;
    $data['beat_id'] = $request->beat_id;

    // Handle same as billing
// $data['same_as_billing'] = $request->boolean('same_as_billing');  // true/false

// if ($data['same_as_billing']) {
//     $data['shipping_address']     = $data['billing_address'] ?? null;
//     $data['shipping_city']        = $data['billing_city'] ?? null;
//     $data['shipping_district']    = $data['billing_district'] ?? null;
//     $data['shipping_state']       = $data['billing_state'] ?? null;
//     $data['shipping_country']     = $data['billing_country'] ?? null;
//     $data['shipping_pincode']     = $data['billing_pincode'] ?? null;

//     // Also copy IDs if you're using them
//     $data['shipping_country_id']  = $request->country_id;
//     $data['shipping_state_id']    = $request->state_id;
//     $data['shipping_district_id'] = $request->district_id;
//     $data['shipping_city_id']     = $request->city_id;
//     $data['shipping_pincode_id']  = $request->pincode_id;
// }

    // File uploads
    if ($request->hasFile('shop_image')) {
        $data['shop_image'] = $request->file('shop_image')->store('distributors/shop_images', 'public');
    }

    if ($request->hasFile('profile_image')) {
        $data['profile_image'] = $request->file('profile_image')->store('distributors/profile_images', 'public');
    }

    if ($request->hasFile('cancelled_cheque')) {
        $data['cancelled_cheque'] = $request->file('cancelled_cheque')->store('distributors/cheques', 'public');
    }

    if ($request->hasFile('mou_file')) {
    $data['mou_file'] = $request->file('mou_file')->store('distributors/mou', 'public');
}

    if ($request->has('sales_executive_id') && is_array($request->sales_executive_id)) {
    $data['sales_executive_id'] = json_encode(array_filter($request->sales_executive_id));
    } else {
    $data['sales_executive_id'] = null;
    }

    if ($request->hasFile('documents')) {
        $paths = [];
        foreach ($request->file('documents') as $file) {
            if ($file->isValid()) {
                $paths[] = $file->store('distributors/documents', 'public');
            }
        }
        $data['documents'] = $paths ? json_encode($paths) : null;
    }
    $data['billing_address'] = $request->address1;

$data['billing_country'] = \App\Models\Country::find($request->billing_country_id ?? $request->country_id)?->country_name;
$data['billing_state'] = \App\Models\State::find($request->billing_state_id ?? $request->state_id)?->state_name;
$data['billing_district'] = \App\Models\District::find($request->billing_district_id ?? $request->district_id)?->district_name;
$data['billing_city'] = \App\Models\City::find($request->billing_city_id ?? $request->city_id)?->city_name;
$data['billing_pincode'] = \App\Models\Pincode::find($request->billing_pincode_id ?? $request->pincode_id)?->pincode;
    //  $data['country_id'] = $request->country_id;
    // $data['state_id'] = $request->state_id;
    // $data['district_id'] = $request->district_id;
    // $data['city_id'] = $request->city_id;
    // $data['pincode_id'] = $request->pincode_id;

    // $data['shipping_country_id'] = $request->shipping_country_id ?? $request->country_id;
    // $data['shipping_state_id'] = $request->shipping_state_id ?? $request->state_id;
    // $data['shipping_district_id'] = $request->shipping_district_id ?? $request->district_id;
    // $data['shipping_city_id'] = $request->shipping_city_id ?? $request->city_id;
    // $data['shipping_pincode_id'] = $request->shipping_pincode_id ?? $request->pincode_id;

    // if ($request->has('same_as_billing') && $request->same_as_billing == 1) {
    //     $data['shipping_country_id'] = $request->country_id;
    //     $data['shipping_state_id'] = $request->state_id;
    //     $data['shipping_district_id'] = $request->district_id;
    //     $data['shipping_city_id'] = $request->city_id;
    //     $data['shipping_pincode_id'] = $request->pincode_id;
    // }

$data['credit_days'] = $request->input('credit_days') !== null 
    ? $request->input('credit_days') 
    : 7;
    // Create
    $distributor = new MasterDistributor();
$distributor->fill($data); // fillable fields
$distributor->billing_country = $request->billing_country_id ?? $request->country_id;
$distributor->billing_state = $request->billing_state_id ?? $request->state_id;
$distributor->billing_district = $request->billing_district_id ?? $request->district_id;
$distributor->billing_city = $request->billing_city_id ?? $request->city_id;
$distributor->billing_pincode = $request->billing_pincode_id ?? $request->pincode_id;
$distributor->save();
    // MasterDistributor::create($data);

    return redirect()->route('master-distributors.index')
                     ->with('success', 'Master Distributor created successfully!');
}

    /* ================= EDIT ================= */
    public function edit($id)
    {
        
        $distributor = MasterDistributor::findOrFail($id);
        $users = User::pluck('name', 'id');
$beats = Beat::where('active', 'Y')
    ->whereHas('beatusers', function ($q) {
        $q->where('user_id', Auth::id());
    })
    ->orderBy('beat_name')
    ->pluck('beat_name', 'id');
                  
    // dd($distributor,$users,$beats);

        return view('master_distributors.create_edit', compact('distributor', 'users', 'beats'));
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $id)
{

// dd($request);
    $distributor = MasterDistributor::findOrFail($id);
    $beats = Beat::where('active', 'Y')
                  ->orderBy('beat_name')
                  ->pluck('beat_name', 'id');

    // Validation (existing)
    $validated = $this->validateData($request, $id);

    // YE PURA DATA BANAYO (including IDs)
    $data = $request->all();

    // IDs assign karo
    $data['country_id'] = $request->country_id;
    $data['state_id'] = $request->state_id;
    $data['district_id'] = $request->district_id;
    $data['city_id'] = $request->city_id;
    $data['pincode_id'] = $request->pincode_id;

    // $data['shipping_country_id'] = $request->shipping_country_id ?? $request->country_id;
    // $data['shipping_state_id'] = $request->shipping_state_id ?? $request->state_id;
    // $data['shipping_district_id'] = $request->shipping_district_id ?? $request->district_id;
    // $data['shipping_city_id'] = $request->shipping_city_id ?? $request->city_id;
    // $data['shipping_pincode_id'] = $request->shipping_pincode_id ?? $request->pincode_id;

    $data['beat_route'] = $request->beat_route;
    $data['beat_id'] = $request->beat_id;

$data['same_as_billing'] = $request->boolean('same_as_billing');  // true/false

// if ($data['same_as_billing']) {
//     $data['shipping_address']     = $data['billing_address'] ?? null;
//     $data['shipping_city']        = $data['billing_city'] ?? null;
//     $data['shipping_district']    = $data['billing_district'] ?? null;
//     $data['shipping_state']       = $data['billing_state'] ?? null;
//     $data['shipping_country']     = $data['billing_country'] ?? null;
//     $data['shipping_pincode']     = $data['billing_pincode'] ?? null;

//     // Also copy IDs if you're using them
//     $data['shipping_country_id']  = $request->country_id;
//     $data['shipping_state_id']    = $request->state_id;
//     $data['shipping_district_id'] = $request->district_id;
//     $data['shipping_city_id']     = $request->city_id;
//     $data['shipping_pincode_id']  = $request->pincode_id;
// }

    DB::beginTransaction();
try {
    // ───────────────────────────────────────────────
    // SINGLE FILE FIELDS (shop, profile, cheque, mou)
    // ───────────────────────────────────────────────
    $singleFileFields = [
        'shop_image'       => 'distributors/shop_images',
        'profile_image'    => 'distributors/profile_images',
        'cancelled_cheque' => 'distributors/cheques',
        'mou_file'         => 'distributors/mou',
    ];

    foreach ($singleFileFields as $field => $folder) {
        if ($request->hasFile($field)) {
            // Delete old file if exists
            if ($distributor->$field) {
                Storage::disk('public')->delete($distributor->$field);
            }

            // Upload new file to correct folder
            $data[$field] = $request->file($field)->store($folder, 'public');
        }
    }

    // ───────────────────────────────────────────────
    // MULTIPLE FILES → documents
    // ───────────────────────────────────────────────
    if ($request->hasFile('documents')) {
        // Optional: purane documents delete karna chahte ho to yeh uncomment kar do
        /*
        if ($distributor->documents) {
            $oldPaths = json_decode($distributor->documents, true) ?? [];
            foreach ($oldPaths as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        */

        $paths = [];
        foreach ($request->file('documents') as $file) {
            if ($file->isValid()) {
                $paths[] = $file->store('distributors/documents', 'public');
            }
        }

        $data['documents'] = !empty($paths) ? json_encode($paths) : null;
    }

    // ───────────────────────────────────────────────
    // Final update
    // ───────────────────────────────────────────────
    $distributor->fill($data); // fillable fields
$distributor->billing_country = $request->billing_country_id ?? $request->country_id;
$distributor->billing_state = $request->billing_state_id ?? $request->state_id;
$distributor->billing_district = $request->billing_district_id ?? $request->district_id;
$distributor->billing_city = $request->billing_city_id ?? $request->city_id;
$distributor->billing_pincode = $request->billing_pincode_id ?? $request->pincode_id;
    $distributor->update($data);

    DB::commit();

    return redirect()
        ->route('master-distributors.index')
        ->with('success', 'Master Distributor updated successfully');
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors($e->getMessage())->withInput();
}
}

    /* ================= SHOW ================= */
    public function show($id)
    {
        $distributor = MasterDistributor::findOrFail($id);
        return view('master_distributors.show', compact('distributor'));
    }

    /* ================= DELETE ================= */
    public function destroy($id)
{
    $distributor = MasterDistributor::findOrFail($id);

    // Delete associated files
    foreach (['shop_image', 'profile_image', 'cancelled_cheque','mou_file'] as $file) {
        if ($distributor->$file) {
            Storage::delete($distributor->$file);
        }
    }

    if ($distributor->documents) {
        $docs = json_decode($distributor->documents, true);
        if (is_array($docs)) {
            foreach ($docs as $doc) {
                Storage::delete($doc);
            }
        }
    }

    $distributor->delete();

    return redirect()->route('master-distributors.index')
                     ->with('success', 'Master Distributor deleted successfully!');
}

    /* ================= VALIDATION ================= */
    private function validateData(Request $request, $id = null)
    {
        return $request->validate([
            'legal_name' => 'required|string|max:255',
            'distributor_code' => 'required|string|max:100|unique:master_distributors,distributor_code,' . $id,
            'business_status' => 'required',
            'contact_person' => 'required',
            'mobile' => 'required',
            'beat_route' => 'required|string',
        ]);
    }

    /* ================= FILE UPLOADER ================= */
private function uploadFile(Request $request, $field)
{
    if (!$request->hasFile($field)) {
        return null;
    }

    $file = $request->file($field);

    // Handle multiple files (like documents)
    if (is_array($file)) {
        $paths = [];
        foreach ($file as $singleFile) {
            if ($singleFile->isValid()) {
                $paths[] = $singleFile->store('distributors/documents', 'public');
            }
        }
        return $paths ? json_encode($paths) : null;
    }

    // Single file
    // You can make path dynamic per field if you want
    $path = match ($field) {
        'shop_image'       => 'distributors/shop_images',
        'profile_image'    => 'distributors/profile_images',
        'cancelled_cheque' => 'distributors/cheques',
        'mou_file'         => 'distributors/mou',
        'documents'        => 'distributors/documents',
        default            => 'distributors/others',
    };

    return $file->store($path, 'public');
}

public function export(Request $request)
{
    $query = MasterDistributor::query();

    /*
    |--------------------------------------------------------------------------
    | Role Based Access Filter
    |--------------------------------------------------------------------------
    */

    $allowedUserIds = getUsersReportingToAuth();

    $query->where(function ($q) use ($allowedUserIds) {

        // supervisor match
        $q->whereIn('supervisor_id', $allowedUserIds);

        // sales executive JSON match
        $q->orWhere(function ($sub) use ($allowedUserIds) {

            foreach ($allowedUserIds as $id) {
                $sub->orWhereJsonContains('sales_executive_id', $id);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    if ($request->code) {
        $query->where('distributor_code', 'like', "%{$request->code}%");
    }

    if ($request->name) {
        $query->where('legal_name', 'like', "%{$request->name}%");
    }

    if ($request->trade_name) {
        $query->where('trade_name', 'like', "%{$request->trade_name}%");
    }

    if ($request->contact_person) {
        $query->where('contact_person', 'like', "%{$request->contact_person}%");
    }

    if ($request->mobile) {
        $query->where('mobile', $request->mobile);
    }

    if ($request->filled('billing_state_id')) {

        $stateName = State::find($request->billing_state_id)?->state_name;

        if ($stateName) {
            $query->where(
                'billing_state',
                'like',
                '%' . trim($stateName) . '%'
            );
        }
    }

    if ($request->filled('global_search')) {

        $search = trim($request->global_search);

        $query->where(function ($q) use ($search) {

            $q->where('distributor_code', 'like', "%{$search}%")
                ->orWhere('legal_name', 'like', "%{$search}%")
                ->orWhere('trade_name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('billing_city', 'like', "%{$search}%")
                ->orWhere('billing_state', 'like', "%{$search}%");
        });
    }

    if ($request->filled('billing_city')) {

        $query->where(
            'billing_city',
            'like',
            '%' . trim($request->billing_city) . '%'
        );
    }

    if ($request->status) {
        $query->where('business_status', $request->status);
    }

    $distributors = $query->get();

    return Excel::download(
        new MasterDistributorsExport($distributors),
        'master_distributors_' . date('Y-m-d_H-i-s') . '.xlsx'
    );
}

public function template()
{
    return Excel::download(
        new MasterDistributorsTemplateExport(),
        'master_distributors_template_' . now()->format('Y-m-d') . '.xlsx'
    );
}

public function getStates($country_id)
{
    $states = State::where('country_id', $country_id)
        ->where('active', 'Y')
        ->orderBy('state_name')
        ->get(['id', 'state_name']); // Important: get() with array, not pluck

    return response()->json($states);
    // Ye array of objects return karega → [{"id":1,"state_name":"Maharashtra"}, ...]
}

public function getDistricts($state_id)
{
    $districts = District::where('state_id', $state_id)
        ->where('active', 'Y')
        ->orderBy('district_name')
        ->get(['id', 'district_name']);

    return response()->json($districts);
}

public function getCities($district_id)
{
    $cities = City::where('district_id', $district_id)
        ->where('active', 'Y')
        ->orderBy('city_name')
        ->get(['id', 'city_name']);

    return response()->json($cities);
}

public function getPincodes($city_id)
{
    $pincodes = Pincode::where('city_id', $city_id)
        ->where('active', 'Y')
        ->orderBy('pincode')
        ->get(['id', 'pincode']);

    return response()->json($pincodes);
}

public function toggleStatus(Request $request)
{
    $id = $request->id;
    $distributor = MasterDistributor::findOrFail($id);

    // Toggle status
    $distributor->business_status = $distributor->business_status === 'Active' ? 'Inactive' : 'Active';
    $distributor->save();

    return response()->json([
        'success' => true,
        'new_status' => $distributor->business_status
    ]);
}




public function getCitiesForState($state_id)
{
    $districtIds = District::where('state_id', $state_id)
        ->where('active', 'Y')
        ->pluck('id');

    $cities = City::whereIn('district_id', $districtIds)
        ->where('active', 'Y')
        ->orderBy('city_name')
        ->pluck('city_name');

    return response()->json($cities);
}



public function import(Request $request)
{
    $request->validate([
        'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
    ]);

    $import = new MasterDistributorsImport();

    try {

        Excel::import($import, $request->file('import_file'));

        $count = $import->getImportedRowCount() ?? 0;

        // ✅ Validation Failures
        $validationErrors = [];
        foreach ($import->failures() as $failure) {
            $validationErrors[] = [
                'row' => $failure->row(),
                'column' => $failure->attribute(),
                'errors' => implode(', ', $failure->errors()),
                'value' => $failure->values()[$failure->attribute()] ?? null
            ];
        }

        // ✅ Custom Errors (Exception wale)
        $customErrors = [];
        foreach ($import->errors() as $error) {
            $customErrors[] = [
                'message' => $error->getMessage()
            ];
        }

        return redirect()
            ->route('master-distributors.index')
            ->with([
                'success' => "Successfully imported $count distributors!",
                'validationErrors' => $validationErrors,
                'customErrors' => $customErrors
            ]);

    } catch (\Throwable $e) {

        return back()->with('error', $e->getMessage());
    }
}


}