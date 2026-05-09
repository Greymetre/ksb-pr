<?php

namespace App\Http\Controllers;

use App\Models\SecondaryCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use DataTables;
use App\Models\Beat;
use App\Models\MasterDistributor;
use App\Models\City;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SecondaryCustomersExport;
use App\Exports\SecondaryCustomersTemplateExport;
use App\Imports\SecondaryCustomersImport;
use App\Models\User;

use Illuminate\Support\Facades\Auth;



class SecondaryCustomerController extends Controller
{
    


    public function index(Request $request)
{
    
    $type = $this->getTypeFromRoute();

    $ownerNames = SecondaryCustomer::where('type', $type)
        ->distinct()
        ->orderBy('owner_name')
        ->pluck('owner_name')
        ->filter()
        ->values();

        $ownerNamesArray = $ownerNames->mapWithKeys(fn($item) => [$item => $item])->toArray();
    $shopNames = SecondaryCustomer::where('type', $type)
        ->distinct()
        ->orderBy('shop_name')
        ->pluck('shop_name')
        ->filter()
        ->values();

        $shopNamesArray = $shopNames->mapWithKeys(fn($item) => [$item => $item])->toArray();

    $mobiles = SecondaryCustomer::where('type', $type)
        ->distinct()
        ->orderBy('mobile_number')
        ->pluck('mobile_number')
        ->filter()
        ->values();

        $mobilesArray = $mobiles->mapWithKeys(fn($item) => [$item => $item])->toArray();
                

        $beats = \App\Models\Beat::where('active', 'Y')
        ->orderBy('beat_name')
        ->get(['id', 'beat_name']);

    $beatsArray = $beats->pluck('beat_name', 'id')->toArray();
        
    $states = \App\Models\State::orderBy('state_name')->get(['id', 'state_name']);


    $query = SecondaryCustomer::with(['state', 'district', 'city', 'pincode', 'beat', 'country'])
        ->select('secondary_customers.*'); // Important: select table with alias or all

    


    if ($request->ajax()) {
$query = SecondaryCustomer::with([
    'state',
    'district',
    'city',
    'beat'
])->select([
    'id',
    'owner_name',
    'shop_name',
    'mobile_number',
    'type',
    'state_id',
    'city_id',
    'district_id',
    'beat_id',
    'created_at',
    'active',
    'status'
]);


$query->where('type', $type);
   $userIds = getUsersReportingToAuth();
    $query->whereIn('created_by', $userIds);

// ==================== DATE FILTER (Safe Version) ====================
if ($request->filled('start_date') && trim($request->start_date) !== '') {
    try {
        // Try multiple common formats
        $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($request->start_date))
                        ->startOfDay();
        $query->where('created_at', '>=', $startDate);
    } catch (\Exception $e) {
        // Fallback: try dd/mm/yyyy or Y-m-d
        try {
            $startDate = \Carbon\Carbon::parse(trim($request->start_date))->startOfDay();
            $query->where('created_at', '>=', $startDate);
        } catch (\Exception $e2) {
            // If both fail, ignore this filter silently (prevents crash)
            \Log::warning('Invalid start_date format: ' . $request->start_date);
        }
    }
}

if ($request->filled('end_date') && trim($request->end_date) !== '') {
    try {
        $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($request->end_date))
                        ->endOfDay();
        $query->where('created_at', '<=', $endDate);
    } catch (\Exception $e) {
        try {
            $endDate = \Carbon\Carbon::parse(trim($request->end_date))->endOfDay();
            $query->where('created_at', '<=', $endDate);
        } catch (\Exception $e2) {
            \Log::warning('Invalid end_date format: ' . $request->end_date);
        }
    }
}
// =================================================================
        // Global Search
    if ($request->filled('global_search')) {
        $search = $request->global_search;
        $query->where(function ($q) use ($search) {
            $q->where('owner_name', 'like', "%{$search}%")
              ->orWhere('shop_name', 'like', "%{$search}%")
              ->orWhere('mobile_number', 'like', "%{$search}%");
        });
    }

    // Individual Filters
    if ($request->filled('owner_name')) {
        $query->where('owner_name', 'like', "%{$request->owner_name}%");
    }

    if ($request->filled('shop_name')) {
        $query->where('shop_name', 'like', "%{$request->shop_name}%");
    }

    if ($request->filled('mobile')) {
        $query->where('mobile_number', 'like', "%{$request->mobile}%");
    }

    if ($request->filled('beat_id') && $request->beat_id != '') {
        $query->where('beat_id', $request->beat_id);
    }

    if ($request->filled('state_id') && $request->state_id != '') {
        $query->where('state_id', $request->state_id);
    }

    if ($request->filled('city_id') && $request->city_id != '') {
        $query->where('city_id', $request->city_id);
    }

    if ($request->filled('status') && $request->status != '') {
    $query->where('status', $request->status);
    }

    if ($request->filled('active') && $request->active != '') {
        $query->where('active', $request->active);
    }

    if (!empty($request->designation_id)) {
        $query->whereHas('employee', function ($q) use ($request) {
            $q->whereIn('designation_id', $request->designation_id);
        });
    }

    // if ($request->filled('opportunity_status') && $request->opportunity_status != '') {
    //     $query->where('opportunity_status', $request->opportunity_status);
    // }

    // // Awareness Status Filter - Dynamic based on type
    // if ($request->filled('awareness_status') && $request->awareness_status != '') {
    //     $status = $request->awareness_status === 'Done' ? 'Done' : 'Not Done';
    //     if (in_array($type, ['RETAILER', 'WORKSHOP'])) {
    //         $query->where('nistha_awareness_status', $status);
    //     } else {
    //         $query->where('saathi_awareness_status', $status);
    //     }
    // }

    return DataTables::of($query)
        
        ->addColumn('action', function ($row) use ($type) {
            $btn = '';
            $routePrefix = strtolower($type) . 's';
            $encryptedId = encrypt($row->id);
            if(auth()->user()->can(['retailer_edit']))
            {
            $btn = '<a href="' . route($routePrefix . '.edit', $encryptedId) . '" class="btn btn-info btn-just-icon btn-sm" title="Edit">
                        <i class="material-icons">edit</i>
                    </a>';
            }
            if(auth()->user()->can(['retailer_show']))
            {        
            $btn .= '<a href="' . route($routePrefix . '.show', $encryptedId) . '" class="btn btn-theme btn-just-icon btn-sm" title="View">
                        <i class="material-icons">visibility</i>
                    </a>';
            }
                    // DELETE
            if(auth()->user()->can(['retailer_delete']))
            {        
            $btn .= '<button data-url="'.route($routePrefix.'.destroy',$row->id).'" 
            class="btn btn-danger btn-just-icon btn-sm deleteCustomer">
            <i class="material-icons">delete</i></button>';
            }
            // STATUS DROPDOWN BUTTON

            // STATUS ICON + COLOR
            $statusIcon = 'hourglass_empty';
            $statusClass = 'btn-secondary';

            if ($row->status == 'APPROVED') {
                $statusIcon = 'check_circle';
                $statusClass = 'btn-success';
            } elseif ($row->status == 'REJECTED') {
                $statusIcon = 'cancel';
                $statusClass = 'btn-danger';
            } elseif ($row->status == 'PENDING') {
                $statusIcon = 'hourglass_empty';
                $statusClass = 'btn-warning';
            }

            if(auth()->user()->can(['retailer_approve']))
            {
            $btn .= '
            <div class="dropdown d-inline">

            <button class="btn '.$statusClass.' btn-just-icon btn-sm dropdown-toggle"
                    type="button"
                    data-toggle="dropdown"
                    title="Change Status">

            <i class="material-icons">'.$statusIcon.'</i>

            </button>

            <div class="dropdown-menu">

            <a class="dropdown-item changeStatus"
            data-id="'.$row->id.'"
            data-status="APPROVED"
            href="javascript:void(0)">
            <i class="material-icons text-success">check_circle</i> Approve
            </a>

            <a class="dropdown-item changeStatus"
            data-id="'.$row->id.'"
            data-status="REJECTED"
            href="javascript:void(0)">
            <i class="material-icons text-danger">cancel</i> Reject
            </a>

            <a class="dropdown-item changeStatus"
            data-id="'.$row->id.'"
            data-status="PENDING"
            href="javascript:void(0)">
            <i class="material-icons text-warning">hourglass_empty</i> Pending
            </a>

            </div>
            </div>';
            }
            // ACTIVE / INACTIVE
            if(auth()->user()->can(['retailer_active']))
                {
            $checked = $row->active == 'Y' ? 'checked' : '';

            $btn .= '<div class="togglebutton">
                            <label>
                                <input type="checkbox" ' . $checked . ' 
                                    id="distributor_' . $row->id . '" 
                                    class="distributor-status-toggle" 
                                    data-id="' . $row->id . '">
                                <span class="toggle"></span>
                            </label>
                        </div>';
                }
            return '<div class="btn-group">' . $btn . '</div>';
        })

        ->addColumn('status', function ($row) {

            if ($row->status == 'APPROVED') {
                return '<span class="badge badge-success">APPROVED</span>';
            }

            if ($row->status == 'REJECTED') {
                return '<span class="badge badge-danger">REJECTED</span>';
            }

            return '<span class="badge badge-warning">PENDING</span>';
        })

        ->addColumn('active', function ($row) {

            if ($row->active == 'Y') {
                return '<span class="badge badge-success">ACTIVE</span>';
            }

            return '<span class="badge badge-danger">INACTIVE</span>';
        })
        ->editColumn('mobile_number', function ($row) {

    return explode(',', $row->mobile_number)[0] ?? '-';

        })

        ->editColumn('beat_id', function ($row) {
            return $row->beat?->beat_name ?? '-';
        })

        ->editColumn('state_id', function ($row) {
            return $row->state?->state_name ?? '-';
        })

        ->editColumn('city_id', function ($row) {
            return $row->city?->city_name ?? '-';
        })

        ->editColumn('district_id', function ($row) {
        return $row->district?->district_name ?? '-';
        })

        ->editColumn('created_at', function ($row) {
            return showdatetimeformat($row->created_at);
        })

        ->rawColumns(['action','status','active'])
        ->make(true);
    }

    // dd($query);
    $folder = strtolower($type) . 's'; // MECHANIC → mechanics
    $typeTitle = $this->getTypeTitle($type);

    $downloadRoute = route(strtolower($type) . 's.download');
    $templateRoute = route(strtolower($type) . 's.template');
    $retailerCount = SecondaryCustomer::where('type', $type)->count();

   
    return view($folder . '.index', compact('type',
        'typeTitle',
        'ownerNames',
        'shopNames',
        'mobiles',
        'beats',
        'ownerNamesArray',
        'shopNamesArray',
        'mobilesArray',
        'beatsArray',
        'states',
        'downloadRoute',
        'templateRoute',
        'retailerCount' 
    ));
}

private function getTypeFromRoute()
{
    $routeName = request()->route()->getName();

    if (str_contains($routeName, 'retailers')) return 'RETAILER';
    if (str_contains($routeName, 'mechanics')) return 'MECHANIC';
    if (str_contains($routeName, 'workshops')) return 'WORKSHOP';
    if (str_contains($routeName, 'garages')) return 'GARAGE';

    // Fallback
    $segment = request()->segment(1);
    return strtoupper($segment) === 'RETAILERS' ? 'RETAILER' : 'MECHANIC';
}

private function getTypeTitle($type)
{
    return match($type) {
        'MECHANIC' => 'Mechanics List',
        'GARAGE' => 'Garages List',
        'RETAILER' => 'Retailers List',
        'WORKSHOP' => 'Workshops List',
        default => 'Customers List'
    };
}
    



public function create(Request $request)
{

// dd($request);
    $type = $this->getTypeFromRoute(); // MECHANIC, GARAGE, etc.

    $customer = new SecondaryCustomer();
    $customer->type = $type; // Pre-fill type

$beats = Beat::where('active', 'Y')
    ->whereHas('beatusers', function ($q) {
        $q->where('user_id', Auth::id());
    })
    ->orderBy('beat_name')
    ->pluck('beat_name', 'id');

    $distributors = MasterDistributor::orderBy('legal_name')
    ->get(['id', 'distributor_code', 'legal_name']);

$distributorOptions = [];

foreach ($distributors as $dist) {
    $distributorOptions[$dist->id] = $dist->distributor_code . ' - ' . $dist->legal_name;
}
  $users = User::orderBy('name')
    ->pluck('name', 'id');
    $folder = strtolower($type) . 's'; // MECHANIC → mechanics

    
    return view("{$folder}.create", compact('customer', 'beats', 'type', 'distributorOptions','users'));
}

public function edit($id)
{
    $customer = SecondaryCustomer::findOrFail(decrypt($id));

    
    $type = $customer->type;

$beats = Beat::where('active', 'Y')
    ->whereHas('beatusers', function ($q) {
        $q->where('user_id', Auth::id());
    })
    ->orderBy('beat_name')
    ->pluck('beat_name', 'id');
                  $distributors = MasterDistributor::orderBy('legal_name')
    ->get(['id', 'distributor_code', 'legal_name']);

$distributorOptions = [];

foreach ($distributors as $dist) {
    $distributorOptions[$dist->id] = $dist->distributor_code . ' - ' . $dist->legal_name;
}

$mobiles = $customer->mobile_number ? explode(',', $customer->mobile_number) : [''];
    
$users = User::orderBy('name')
    ->pluck('name', 'id');


    $folder = strtolower($type) . 's'; 

    return view("{$folder}.edit", compact('customer', 'beats', 'type', 'distributorOptions','users'));
}

    /* ================= STORE ================= */
    // public function store(Request $request)
    // {
    //     $validated = $this->validateData($request);

    //     DB::beginTransaction();
    //     try {

    //         foreach (['owner_photo', 'shop_photo'] as $file) {
    //             $validated[$file] = $this->uploadFile($request, $file);
    //         }

    //         SecondaryCustomer::create($validated);

    //         DB::commit();
    //         return redirect()
    //             ->route('secondary-customers.index')
    //             ->with('success', 'Secondary Customer created successfully');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withErrors($e->getMessage())->withInput();
    //     }
    // }
    public function store(Request $request)
{
    // dd($request);
    // dd($request);
    $type = $this->getTypeFromRoute(); // MECHANIC, GARAGE etc.

    $validated = $this->validateData($request);
    $validated['created_by'] = Auth::id();
    if ($request->filled('agri_distributor')) {
    $validated['agri_distributor'] = $request->agri_distributor;
}

// if ($request->filled('employee_id')) {
//     $validated['employee_id'] = $request->employee_id;
if ($request->has('employee_id')) {
    $validated['employee_id'] = implode(',', $request->employee_id);
}
// }
// if ($request->has('distributor_name')) {
//     $validated['distributor_name'] = implode(',', $request->distributor_name);
// }

if ($request->has('mobile_numbers')) {
    $validated['mobile_number'] = implode(',', $request->mobile_numbers);
}
$inputMobiles = $request->mobile_numbers;

// Existing DB mobiles fetch karo
$existingMobiles = SecondaryCustomer::pluck('mobile_number')->toArray();

// Flatten karo (comma split)
$dbMobiles = [];
if (!empty($validated['gps_location'])) {

    $coords = explode(',', $validated['gps_location']);

    if (count($coords) == 2) {
        $lat = trim($coords[0]);
        $lng = trim($coords[1]);

        $validated['gmap'] = getLatLongToAddress($lat, $lng);
    }
}

foreach ($existingMobiles as $mobileString) {
    $numbers = explode(',', $mobileString);
    foreach ($numbers as $num) {
        $dbMobiles[] = trim($num);
    }
}

// Check duplicate
foreach ($inputMobiles as $mobile) {
    if (in_array($mobile, $dbMobiles)) {
        return back()
            ->withErrors(['mobile_numbers' => 'This retailer already exist'])
            ->withInput();
    }
}
    DB::beginTransaction();
    try {
        foreach ([
            'owner_photo',
    'shop_photo',
    'gst_attachment',
    'pan_attachment',
    'bank_proof'
] as $file) {
            if ($request->hasFile($file)) {
                $validated[$file] = $this->uploadFile($request, $file);
            }
        }

        SecondaryCustomer::create($validated);

        DB::commit();

        $routePrefix = strtolower($type) . 's'; // mechanics, garages etc.

        return redirect()
            ->route($routePrefix . '.index')
            ->with('success', 'Customer created successfully');

    } catch (\Exception $e) {
        DB::rollBack();

        
        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}

    /* ================= EDIT ================= */
    // public function edit($id)
    // {
    //     $customer = SecondaryCustomer::findOrFail(decrypt($id));
    //     return view('secondary_customers.create_edit', compact('customer'));
    // }

    /* ================= UPDATE ================= */
   public function update(Request $request, $id)
{

    // dd($request);
    $customer = SecondaryCustomer::findOrFail($id);

    // Actual type customer ke record se lo (safe)
    $type = $customer->type;
    $routePrefix = strtolower($type) . 's'; // MECHANIC → mechanics, GARAGE → garages etc.

    $validated = $this->validateData($request, $id);

    // remove confirm field
unset($validated['bank_account_number_confirm']);

// agar bank account empty hai to update mat karo
if (!$request->filled('bank_account_number')) {
    unset($validated['bank_account_number']);
}

if ($request->filled('agri_distributor')) {
    $validated['agri_distributor'] = $request->agri_distributor;
}

// if ($request->filled('employee_id')) {
//     $validated['employee_id'] = $request->employee_id;
// }

if ($request->has('employee_id')) {
    $validated['employee_id'] = implode(',', $request->employee_id);
}

//     if ($request->has('distributor_name')) {
//     $validated['distributor_name'] = implode(',', $request->distributor_name);
// }

if ($request->has('mobile_numbers')) {
    $validated['mobile_number'] = implode(',', $request->mobile_numbers);
}
if (!empty($validated['gps_location'])) {

    $coords = explode(',', $validated['gps_location']);

    if (count($coords) == 2) {
        $lat = trim($coords[0]);
        $lng = trim($coords[1]);

        $validated['gmap'] = getLatLongToAddress($lat, $lng);
    }
}
$inputMobiles = $request->mobile_numbers;

$currentMobiles = explode(',', $customer->mobile_number);

// Current customer exclude karo
$existingMobiles = SecondaryCustomer::where('id', '!=', $customer->id)
    ->pluck('mobile_number')
    ->toArray();

$dbMobiles = [];

foreach ($existingMobiles as $mobileString) {
    $numbers = explode(',', $mobileString);
    foreach ($numbers as $num) {
        $dbMobiles[] = trim($num);
    }
}

foreach ($inputMobiles as $mobile) {

    // agar same number already current record me hai → allow
    if (in_array($mobile, $currentMobiles)) {
        continue;
    }

    // agar kisi aur record me mil gaya → error
    if (in_array($mobile, $dbMobiles)) {
        return back()
            ->withErrors(['mobile_numbers' => 'This retailer already exist'])
            ->withInput();
    }
}

// if ($request->filled('bank_account_number')) {
//     $customer->bank_account_number = $request->bank_account_number;
// }



    DB::beginTransaction();
    try {
        foreach (['owner_photo', 'shop_photo',
        'gst_attachment',
    'pan_attachment',
    'bank_proof'] as $file) {
            if ($request->hasFile($file)) {
                if ($customer->$file) {
                    Storage::delete($customer->$file);
                }
                $validated[$file] = $this->uploadFile($request, $file);
            }
        }

        $customer->update($validated);

        DB::commit();

        return redirect()
            ->route($routePrefix . '.index')  // ← YEH CHANGE KARO
            ->with('success', 'Customer updated successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors($e->getMessage())->withInput();
    }
}

    /* ================= SHOW ================= */
public function show($id)
{
    $customer = SecondaryCustomer::with([
        'country', 'state', 'district', 'city', 'pincode', 'beat',
        
    ])->findOrFail(decrypt($id));

    $type = $customer->type;
    $folder = strtolower($type) . 's';

    return view("{$folder}.show", compact('customer'));
}

    /* ================= DELETE ================= */
    public function destroy($id)
{
    $customer = SecondaryCustomer::findOrFail($id);
    $type = $customer->type;
    $routePrefix = strtolower($type) . 's';

    // delete photos...

    $customer->delete();

    return redirect()
        ->route($routePrefix . '.index')
        ->with('success', 'Customer deleted successfully');
}

    /* ================= VALIDATION ================= */
    private function validateData(Request $request, $id = null)
{
    $rules = [
        'type' => 'required|string|in:RETAILER,WORKSHOP,MECHANIC,GARAGE',
        'sub_type' => 'nullable|string|max:255', // Mechanic ke liye required hai, baaki ke liye optional
        'owner_name' => 'required|string|max:255',
        'shop_name' => 'required|string|max:255',
'mobile_numbers' => 'required|array|min:1|max:5',
'mobile_numbers.*' => 'required|digits:10',
        'whatsapp_number' => 'nullable|digits:10',
        'owner_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'shop_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'vehicle_segment' => 'nullable|string|max:255',
        'address_line' => 'required|string',
        'belt_area_market_name' => 'nullable|string|max:255',
        // 'saathi_awareness_status' => 'nullable|in:Done,Not Done',
        
// 'distributor_name' => 'required|array',
// 'distributor_name.*' => 'exists:master_distributors,id',
'distributor_name' => 'required|exists:master_distributors,id',
'agri_distributor' => 'nullable|exists:master_distributors,id',
// 'employee_id' => 'nullable|exists:users,id',
'employee_id' => 'nullable|array',
'employee_id.*' => 'exists:users,id',
        // 'opportunity_status' => 'required|in:HOT,WARM,COLD,LOST',
        'gps_location' => 'nullable|string|max:255',

        // ====== YE RULES ADD KARO ======
        'country_id' => 'required|exists:countries,id',
        'state_id' => 'required|exists:states,id',
        'district_id' => 'required|exists:districts,id',
        'city_id' => 'required|exists:cities,id',
        'pincode_id' => 'required|exists:pincodes,id',
        'beat_id' => 'nullable|exists:beats,id',

        'gst_attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
'pan_attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
'bank_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
'gst_number' => 'nullable|string|max:20',
'pan_number' => 'nullable|string|max:20',

'bank_account_type' => 'nullable|string',
'bank_account_number' => 'nullable|string|max:50|same:bank_account_number_confirm',
'bank_account_number_confirm' => 'nullable|string|max:50',
'bank_name' => 'nullable|string|max:255',
'ifsc_code' => 'nullable|string|max:20',
'account_holder_name' => 'nullable|string|max:255',
        // ================================
    ];

    // Agar Mechanic hai to sub_type required
    if ($request->type === 'MECHANIC') {
        $rules['sub_type'] = 'required|string|max:255';
    }

    // Retailer & Workshop ke liye distributor (agar abhi bhi hai)
    if (in_array($request->type, ['RETAILER', 'WORKSHOP'])) {
        $rules['distributor_name'] = 'required|string';
    }
    if (in_array($request->type, ['RETAILER', 'WORKSHOP'])) {
        $rules['distributor_name'] = 'required|exists:master_distributors,id';
        // $rules['nistha_awareness_status'] = 'required|in:Done,Not Done'; // NAYA REQUIRED
        // saathi_awareness_status optional ho gaya
    } else {
        // Mechanic & Garage ke liye saathi required rahe
        // $rules['saathi_awareness_status'] = 'required|in:Done,Not Done';
    }

    return $request->validate($rules);
}

    /* ================= FILE UPLOADER ================= */
    private function uploadFile(Request $request, $field)
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store('secondary_customers', 'public');
        }
        return null;
    }

    public function country()
{
    return $this->belongsTo(\App\Models\Country::class);
}

public function state()
{
    return $this->belongsTo(\App\Models\State::class);
}

public function district()
{
    return $this->belongsTo(\App\Models\District::class);
}
public function city()
{
    return $this->belongsTo(\App\Models\City::class);
}
public function getCities(Request $request)
{
    $state_id = $request->state_id;

    if (!$state_id) {
        return response()->json([]);
    }

    $cities = \App\Models\City::where('state_id', $state_id)
        ->orderBy('city_name')
        ->get(['id', 'city_name']);

    return response()->json($cities);
}

public function downloadExcel(Request $request)
{
    $type = $this->getTypeFromRoute();

    // dd($request);

   
    $filename = strtolower($type) . 's_' . now()->format('Y-m-d') . '.xlsx';
    // Example: retailers_2026-01-05.xlsx

    return Excel::download(
        new SecondaryCustomersExport($request->all(), $type),
        $filename
    );
}
public function downloadTemplate(Request $request)
{
    $type = $this->getTypeFromRoute(); // MECHANIC, GARAGE etc.

    $filename = 'template_' . strtolower($type) . 's_upload.xlsx';

    return Excel::download(new SecondaryCustomersTemplateExport($type), $filename);
}
public function import(Request $request)
{
    $request->validate([
        'import_file' => 'required|mimes:xls,xlsx'
    ]);

    $type = $this->getTypeFromRoute();

    $import = new SecondaryCustomersImport($type);

    Excel::import($import, $request->file('import_file'));

    if (!empty($import->errors)) {

        return redirect()->back()->with('importErrors', $import->errors);
    }

    return redirect()->back()->with('success', 'Data Imported Successfully');
}

public function changeStatus(Request $request)
{
    $request->validate([
        'id'     => 'required|exists:secondary_customers,id',
        'status' => 'required|in:APPROVED,REJECTED,PENDING',
        'remark' => 'nullable|string|max:500'
    ]);

    $customer = SecondaryCustomer::findOrFail($request->id);

    // ✅ Status update
    $customer->status = $request->status;

    // ✅ Approved/Rejected by (logged in user)
    $customer->approve_reject_by = auth()->id();

    // ✅ Remark logic
    if ($request->status === 'REJECTED') {
        $customer->remark = $request->remark;
    } else {
        // Optional: clear remark if approved
        $customer->remark = null;
    }

    $customer->save();

    return response()->json([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);
}

public function toggleActive(Request $request)
{
    \Log::info('toggleActive method STARTED', [
        'ip'     => $request->ip(),
        'all'    => $request->all(),
        'active' => $request->input('active'),
        'id'     => $request->input('id'),
    ]);

    try {
        $validated = $request->validate([
            'id'     => 'required|integer|exists:secondary_customers,id',
            'active' => 'required|in:Y,N',
        ]);

        \Log::info('Validation passed', $validated);

        $customer = SecondaryCustomer::findOrFail($request->id);
        $old = $customer->active;

        $customer->active = $validated['active'];
        $saved = $customer->save();

        \Log::info('Toggle executed', [
            'customer_id' => $customer->id,
            'was'         => $old,
            'now_set_to'  => $validated['active'],
            'save_returned' => $saved,
            'after_fresh' => $customer->fresh()->active,
        ]);

        return response()->json([
            'success' => true,
            'was'     => $old,
            'now'     => $customer->fresh()->active,
        ]);
    }
    catch (\Exception $e) {
        \Log::error('toggleActive FAILED', [
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'success' => false,
            'error'   => $e->getMessage()
        ], 422);
    }
}
}