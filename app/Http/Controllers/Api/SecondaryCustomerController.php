<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecondaryCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SecondaryCustomersExport;
use App\Exports\SecondaryCustomersTemplateExport;
use Illuminate\Validation\Rule;   // ← this line is MISSING or commented out
use App\Models\User;
class SecondaryCustomerController extends Controller
{
    
    private function getVisibleUserIds(User $user): array
    {
        $allIds = [$user->id];           // include myself
    
        $this->collectDownlineIds($user->id, $allIds);
    
        return array_unique($allIds);
    }
    
    /**
     * Recursively collect all user IDs in the downline
     */
    private function collectDownlineIds(int $managerId, array &$ids): void
    {
        $directReports = User::where('reportingid', $managerId)
            ->pluck('id')
            ->toArray();
    
        if (empty($directReports)) {
            return;
        }
    
        foreach ($directReports as $reportId) {
            if (!in_array($reportId, $ids)) {   // prevent potential cycles (rare)
                $ids[] = $reportId;
                $this->collectDownlineIds($reportId, $ids);
            }
        }
    }
    
    public function index(Request $request)
    {
        $type = $request->query('type');

        if (!$type || !in_array($type, ['RETAILER', 'WORKSHOP', 'MECHANIC', 'GARAGE'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or missing type parameter.',
            ], 400);
        }

        try {
            $authUser = $request->user();

            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated - please provide valid token',
                ], 401);
            }
            

            $today = now()->startOfDay()->toDateString(); // e.g. '2026-02-25'
            // ────────────────────────────────────────────────
            // SUPERADMIN CHECK (Only change is here)
            // ────────────────────────────────────────────────
            $isSuperAdmin = false;
    
            // First try: Spatie hasRole (most accurate)
            if (method_exists($authUser, 'hasRole')) {
                $isSuperAdmin = 
                    $authUser->hasRole('superadmin') || 
                    $authUser->hasRole('subAdmin');
            }
    
            // Fallback: Check roles relation if loaded
            if (!$isSuperAdmin && $authUser->relationLoaded('roles')) {
                $roles = $authUser->roles->pluck('name');
            
                $isSuperAdmin = 
                    $roles->contains('superadmin') || 
                    $roles->contains('subAdmin');
            }
    
            // Final fallback: Check user_type (as sent in login response)
            // if (!$isSuperAdmin && !empty($authUser->user_type)) {
            //     $userTypes = $authUser->user_type;
            //     if (is_string($userTypes)) {
            //         $userTypes = json_decode($userTypes, true) ?? [];
            //     }
            //     $isSuperAdmin = in_array('superadmin', (array)$userTypes, true);
            // }
            if (!$isSuperAdmin && !empty($authUser->user_type)) {
                $userTypes = $authUser->user_type;
            
                if (is_string($userTypes)) {
                    $userTypes = json_decode($userTypes, true) ?? [];
                }
            
                $isSuperAdmin = 
                    in_array('superadmin', (array)$userTypes, true) ||
                    in_array('subAdmin', (array)$userTypes, true);
            }
            
            
            if ($isSuperAdmin) {
                $query = SecondaryCustomer::with([
                    'country', 'state', 'district', 'city', 'pincode', 'beat', 'distributor'
                ])
                ->where('type', $type)
                ->where('active', 'Y')
                ->select('secondary_customers.*');
    
                // If for_user_id is provided → show only that user's customers
                if ($request->filled('for_user_id')) {
                    $targetUserId = $request->for_user_id;
    
                    $targetUser = User::find($targetUserId);
                    if (!$targetUser) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Requested user not found',
                        ], 404);
                    }
    
                    $query->where(function ($q) use ($targetUserId) {
                        $q->where('created_by', $targetUserId)
                          ->orWhere('employee_id', $targetUserId);
                    });
                }
                // Else → Superadmin sees ALL customers (no restriction)
            } else {
                // ────────────────────────────────────────────────
                // Normal User / Hierarchy Logic (Existing)
                // ────────────────────────────────────────────────
                $targetUserId = $request->query('for_user_id');
    
                if ($targetUserId) {
                    $targetUser = User::find($targetUserId);
                    if (!$targetUser) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Requested user not found',
                        ], 404);
                    }
    
                    $myVisibleIds = $this->getVisibleUserIds($authUser);
                    $myVisibleIds[] = $authUser->id;
                    $myVisibleIds = array_unique($myVisibleIds);
    
                    if (!in_array($targetUserId, $myVisibleIds)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'You do not have permission to view this user\'s customers',
                        ], 403);
                    }
    
                    $visibleUserIds = [$targetUserId];
                } else {
                    $visibleUserIds = $this->getVisibleUserIds($authUser);
                    $visibleUserIds[] = $authUser->id;
                    $visibleUserIds = array_unique($visibleUserIds);
                }
    
                // Check BM role
                $isBM = false;
                
                if (method_exists($authUser, 'hasRole')) {
                    $isBM = $authUser->hasRole('BM.');
                }
                
                if (!$isBM && $authUser->relationLoaded('roles')) {
                    $roles = $authUser->roles->pluck('name');
                    $isBM = $roles->contains('BM.');
                }
                
                $query = SecondaryCustomer::with([
                    'country', 'state', 'district', 'city', 'pincode', 'beat', 'distributor'
                ])
                ->where('type', $type)
                ->where('active', 'Y');
                
                if ($isBM) {
                
                    // Get all users of same branch
                    $branchUserIds = User::where('branch_id', $authUser->branch_id)
                        ->pluck('id')
                        ->toArray();
                
                    $query->where(function ($q) use ($branchUserIds) {
                        $q->whereIn('created_by', $branchUserIds)
                          ->orWhereIn('employee_id', $branchUserIds);
                    });
                
                } else {
                
                    $query->where(function ($q) use ($visibleUserIds) {
                        $q->whereIn('created_by', $visibleUserIds)
                          ->orWhereIn('employee_id', $visibleUserIds);
                    });
                }
                
                $query->select('secondary_customers.*');
            }

            // Global search
            if ($request->filled('global_search')) {
                $search = $request->global_search;
                $query->where(function ($q) use ($search) {
                    $q->where('owner_name', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('city_name')) {
                $query->whereHas('city', function ($q) use ($request) {
                    $q->where('city_name', 'like', '%' . $request->city_name . '%');
                });
            }

            // Individual filters
            if ($request->filled('owner_name')) {
                $query->where('owner_name', 'like', "%{$request->owner_name}%");
            }
            if ($request->filled('shop_name')) {
                $query->where('shop_name', 'like', "%{$request->shop_name}%");
            }
            if ($request->filled('mobile')) {
                $query->where('mobile_number', 'like', "%{$request->mobile}%");
            }
            if ($request->filled('beat_id')) {
                $query->where('beat_id', $request->beat_id);
            }
            if ($request->filled('state_id')) {
                $query->where('state_id', $request->state_id);
            }
            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }
            if ($request->filled('opportunity_status')) {
                $query->where('opportunity_status', $request->opportunity_status);
            }

            // Awareness status filter
            if ($request->filled('awareness_status')) {
                $status = $request->awareness_status === 'Done' ? 'Done' : 'Not Done';

                if (in_array($type, ['RETAILER', 'WORKSHOP'])) {
                    $query->where('nistha_awareness_status', $status);
                } else {
                    $query->where('saathi_awareness_status', $status);
                }
            }

            // ── Add check-in & check-out status (per logged-in user) ──
            $query->addSelect([
                // Check-in fields
                'last_checkin_date' => \App\Models\CheckIn::select('checkin_date')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),

                'last_checkin_time' => \App\Models\CheckIn::select('checkin_time')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),

                'has_checked_in_today' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->whereDate('checkin_date', $today),

                // Check-out fields
                'last_checkout_date' => \App\Models\CheckIn::select('checkout_date')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
                'current_visit_is_open' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->whereNull('checkout_date')
                    ->whereDate('checkin_date', $today),

                'last_checkout_time' => \App\Models\CheckIn::select('checkout_time')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),

                'has_checked_out_today' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->whereDate('checkout_date', $today),
                    
                'last_checkin_id' => \App\Models\CheckIn::select('id')
                    ->whereColumn('entity_id', 'secondary_customers.id')
                    ->where('entity_type', 'secondary_customer')
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
            ]);

            // Sort by created_at DESCENDING (newest first)
            $query->orderBy('created_at', 'desc');

            // Pagination (still using Laravel paginator, but we'll clean the output)
            $perPage   = $request->query('per_page', 10);
            $customers = $query->paginate($perPage);

            // Clean response - remove unwanted pagination link fields
            $cleanData = [
                'current_page' => $customers->currentPage(),
                'data'         => $customers->items(),           // only the records
                'from'         => $customers->firstItem(),
                'to'           => $customers->lastItem(),
                'per_page'     => $customers->perPage(),
                'total'        => $customers->total(),
                'last_page'    => $customers->lastPage(),
                // removed: links, first_page_url, last_page_url, next_page_url, prev_page_url, path
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Secondary customers retrieved successfully',
                'data'    => $cleanData,
                // 'cities'  => $cities
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch secondary customers',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getUsedCities()
    {
        try {
            $cities = \App\Models\City::whereIn('id', function ($query) {
                    $query->select('city_id')
                          ->from('secondary_customers')
                          ->where('active', 'Y') // optional but recommended
                          ->whereNotNull('city_id')
                          ->distinct();
                })
                ->where('active', 'Y')
                ->select('id', 'city_name')
                ->orderBy('city_name')
                ->get();
    
            return response()->json([
                'status' => true,
                'message' => 'Cities retrieved successfully',
                'data' => $cities
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch cities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $authUser = $request->user();

            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated - please provide valid token',
                ], 401);
            }

            // Load the secondary customer with its relationships
            $customer = SecondaryCustomer::with([
                'country', 'state', 'district', 'city', 'pincode', 'beat', 'distributor', 'creator'
            ])->find($id);

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer not found',
                ], 404);
            }
            
            // ────────────────────────────────────────────────
            // Calculate Hierarchy Level for created_by user
            // ────────────────────────────────────────────────
            $createdById = $customer->created_by;   // Assuming the column name is 'created_by'
    
            $hierarchy_level = 0;   // Default
            $hierarchy_label = 'Self';
    
            if ($createdById && $createdById != $authUser->id) {
                $hierarchy_level = getHierarchyLevel($createdById, $authUser->id);
                
                $hierarchy_label = match($hierarchy_level) {
                    0   => 'Self',
                    -1  => 'Not in Hierarchy',
                    default => 'Level ' . $hierarchy_level
                };
            }

            // ────────────────────────────────────────────────
            //   Fetch check-in / check-out data for this user
            // ────────────────────────────────────────────────
            $today = now()->startOfDay()->toDateString();

            $checkInQuery = \App\Models\CheckIn::where('entity_type', 'secondary_customer')
                ->where('entity_id', $id)
                ->where('user_id', $authUser->id);

            // Latest check-in record (may or may not have checkout)
            $lastCheckIn = (clone $checkInQuery)
                ->orderByDesc('checkin_date')
                ->orderByDesc('checkin_time')
                ->first([
                    'id',
                    'checkin_date',
                    'checkin_time',
                    'checkin_address',
                    'checkout_date',
                    'checkout_time',
                    'checkout_address',
                    'time_interval'
                ]);

            // Latest completed check-out (only records with checkout)
            $lastCheckOut = (clone $checkInQuery)
                ->whereNotNull('checkout_date')
                ->orderByDesc('checkout_date')
                ->orderByDesc('checkout_time')
                ->first(['checkout_date', 'checkout_time', 'checkout_address']);

            // Today's status
            $hasCheckedInToday = (clone $checkInQuery)
                ->whereDate('checkin_date', $today)
                ->exists();

            $hasCheckedOutToday = (clone $checkInQuery)
                ->whereDate('checkout_date', $today)
                ->exists();

            // Prepare clean check-in/check-out data structure
            $checkData = [
                'last_checkin' => $lastCheckIn ? [
                    'checkin_id'       => $lastCheckIn->id,
                    'checkin_datetime' => $lastCheckIn->checkin_date . ' ' . $lastCheckIn->checkin_time,
                    'checkin_address'  => $lastCheckIn->checkin_address,
                    'checkout_datetime'=> $lastCheckIn->checkout_date 
                        ? $lastCheckIn->checkout_date . ' ' . $lastCheckIn->checkout_time 
                        : null,
                    'checkout_address' => $lastCheckIn->checkout_address,
                    'duration'         => $lastCheckIn->time_interval ?? null,
                ] : null,

                'last_checkout' => $lastCheckOut ? [
                    'checkout_datetime' => $lastCheckOut->checkout_date . ' ' . $lastCheckOut->checkout_time,
                    'checkout_address'  => $lastCheckOut->checkout_address,
                ] : null,

                'today' => [
                    'has_checked_in'  => $hasCheckedInToday,
                    'has_checked_out' => $hasCheckedOutToday,
                ],
            ];
            
            $linkedDistributors = collect();

            if ($customer && $customer->distributor_name) {
                $ids = array_map('trim', explode(',', $customer->distributor_name));
                $ids = array_filter($ids, fn($id) => is_numeric($id) && $id !== '');
            
                if (!empty($ids)) {
                    $linkedDistributors = \App\Models\MasterDistributor::query()
                        ->whereIn('id', $ids)
                        ->select('id', 'legal_name')           // or whatever field you want to show
                        ->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")  // preserve input order
                        ->get()
                        ->map(fn($d) => [
                            'id'        => $d->id,
                            'shop_name' => $d->legal_name,
                        ]);
                }
            }

            return response()->json([
                'status'      => true,
                'message'     => 'Customer retrieved successfully',
                // Hierarchy info for the customer creator (outside data as per your preference)
                'hierarchy_level' => $hierarchy_level,
                'hierarchy_label' => $hierarchy_label,
                
                'data'        => $customer,
                'check_status'=> $checkData,
                'distributors' => $linkedDistributors,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve customer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
    
        DB::beginTransaction();
    
        try {
            
                    // ✅ ADD THIS LINE
        $validated['employee_id'] = $request->user()->id;
        $validated['created_by'] = $request->user()->id;

        // (optional but recommended)
\Log::info('RAW REQUEST:', $request->all());

\Log::info('GPS VALUE:', [
    'gps_location' => $validated['gps_location'] ?? null
]);

if (!empty($validated['gps_location'])) {

    $coords = explode(',', $validated['gps_location']);

    \Log::info('COORDS:', $coords);

    if (count($coords) == 2) {

        $lat = trim($coords[0]);
        $lng = trim($coords[1]);

        $address = getLatLongToAddress($lng, $lat);

        \Log::info('ADDRESS:', ['value' => $address]);

        $validated['gmap'] = $address;
    }
}
    
            $files = [
                'owner_photo',
                'shop_photo',
                'gst_attachment',
                'pan_attachment',
                'bank_proof'
            ];
    
            foreach ($files as $file) {
                if ($request->hasFile($file)) {
                    $validated[$file] = $this->uploadFile($request, $file);
                }
            }
    
            $customer = SecondaryCustomer::create($validated);
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Secondary customer created successfully',
                'data' => $customer->load([
                    'country','state','district','city','pincode','beat','distributor'
                ])
            ],201);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ],500);
        }
    }

   public function update(Request $request, $id)
    {
        $customer = SecondaryCustomer::findOrFail($id);
    
        $validated = $this->validateData($request, $id);
    
        DB::beginTransaction();
    
        try {
    
            $files = [
                'owner_photo',
                'shop_photo',
                'gst_attachment',
                'pan_attachment',
                'bank_proof'
            ];
            
            if (!empty($validated['gps_location'])) {
                $coords = explode(',', $validated['gps_location']);
    
                if (count($coords) == 2) {
                    $lat = trim($coords[0]);
                    $lng = trim($coords[1]);
    
                    $validated['gmap'] = getLatLongToAddress($lng, $lat);
                }
            }
    
            foreach ($files as $file) {
    
                if ($request->hasFile($file)) {
    
                    if ($customer->$file && Storage::disk('public')->exists($customer->$file)) {
                        Storage::disk('public')->delete($customer->$file);
                    }
    
                    $validated[$file] = $this->uploadFile($request, $file);
                }
            }
    
            $customer->update($validated);
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Secondary customer updated successfully',
                'data' => $customer->load([
                    'country','state','district','city','pincode','beat','distributor'
                ])
            ],200);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function destroy($id)
    {
        try {
            $customer = SecondaryCustomer::find($id);

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer not found',
                ], 404);
            }

            // delete files if exist
            foreach (['owner_photo', 'shop_photo'] as $file) {
                if ($customer->$file && Storage::exists($customer->$file)) {
                    Storage::delete($customer->$file);
                }
            }

            $customer->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Customer deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete customer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function validateData(Request $request, $id = null)
    {
        return $request->validate([
    
            'type' => 'required|in:RETAILER,WORKSHOP,MECHANIC,GARAGE',
            'sub_type' => 'nullable|string|max:100',
    
            'owner_name' => 'required|string|max:255',
            'shop_name' => 'required|string|max:255',
    
        'mobile_number' => [
        'required',
        function ($attribute, $value, $fail) use ($id) {
    
            $numbers = explode(',', $value);
    
            foreach ($numbers as $number) {
    
                $number = trim($number);
    
                if (!preg_match('/^[0-9]{10}$/', $number)) {
                    $fail('Each mobile number must be 10 digits.');
                    return;
                }
    
                // ✅ UNIQUE CHECK
                $exists = \App\Models\SecondaryCustomer::where('mobile_number', 'like', "%$number%")
                    ->when($id, function ($q) use ($id) {
                        $q->where('id', '!=', $id);
                    })
                    ->exists();
    
                if ($exists) {
                    $fail($number . ' mobile number already exists');
                    return;
                }
            }
        }
    ],

        'whatsapp_number' => 'nullable|digits:10',

        'vehicle_segment' => 'nullable|string|max:100',
        'address_line' => 'required|string|max:500',

        'belt_area_market_name' => 'nullable|string|max:150',

        'gps_location' => 'nullable|string|max:100',

        'country_id' => 'required|exists:countries,id',
        'state_id' => 'required|exists:states,id',
        'district_id' => 'required|exists:districts,id',
        'city_id' => 'required|exists:cities,id',
        'pincode_id' => 'required|exists:pincodes,id',

        'beat_id' => 'nullable|exists:beats,id',
        'distributor_name' => 'nullable|exists:master_distributors,id',
        'agri_distributor' => 'nullable|exists:master_distributors,id',
        // 'opportunity_status' => 'required|in:HOT,WARM,COLD,LOST',

        'nistha_awareness_status' => 'nullable|in:Done,Not Done',
        'saathi_awareness_status' => 'nullable|in:Done,Not Done',

        // GST + PAN
        'gst_number' => 'nullable|string|max:50',
        'pan_number' => 'nullable|string|max:50',

        // Bank details
        'bank_account_type' => 'nullable|string|max:50',
        'bank_account_number' => 'nullable|string|max:50',
        'bank_name' => 'nullable|string|max:100',
        'ifsc_code' => 'nullable|string|max:20',
        'account_holder_name' => 'nullable|string|max:255',

        // Files
        'owner_photo' => 'nullable|file|max:10240',
        'shop_photo' => 'nullable|file|max:10240',
        'gst_attachment' => 'nullable|file|max:10240',
        'pan_attachment' => 'nullable|file|max:10240',
        'bank_proof' => 'nullable|file|max:10240',

        'status' => 'nullable|string',
        'active' => 'nullable|string'

    ]);
}

    // ────────────────────────────────────────────────
    //   FILE UPLOAD HELPER  (this was missing)
    // ────────────────────────────────────────────────
    private function uploadFile(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field) || !$request->file($field)->isValid()) {
            return null;
        }

        $file = $request->file($field);

        // Minimal logging
        \Log::info("Minimal upload start", ['field' => $field, 'size' => $file->getSize()]);

        // Use move() instead of storeAs() — sometimes more memory efficient in certain PHP versions
        $path = 'secondary-customers/' . now()->format('Y/m') . '/' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(storage_path('app/public/' . dirname($path)), basename($path));

        \Log::info("Minimal upload done", ['path' => $path]);

        return $path;
    }

    // Helper endpoints
    public function getCities(Request $request) {
        $state_id = $request->state_id;
        if (!$state_id) return response()->json([], 400);
        $cities = \App\Models\City::where('state_id', $state_id)->orderBy('city_name')->get(['id', 'city_name']);
        return response()->json($cities);
    }

    public function downloadExcel(Request $request) {
        $type = $request->query('type');
        if (!$type) return response()->json(['error' => 'Missing type'], 400);
        $filename = strtolower($type) . 's_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new SecondaryCustomersExport($request->all(), $type), $filename);
    }

    public function downloadTemplate(Request $request) {
        $type = $request->query('type');
        if (!$type) return response()->json(['error' => 'Missing type'], 400);
        $filename = 'template_' . strtolower($type) . 's_upload.xlsx';
        return Excel::download(new SecondaryCustomersTemplateExport($type), $filename);
    }
    
    /**
     * Change approval status of a secondary customer (with optional remark)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, $id)
    {
        $customer = SecondaryCustomer::find($id);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }
    
        $validated = $request->validate([
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
            'remark' => 'nullable|string|max:500',
        ]);
    
        // ── Debug ────────────────────────────────────────
        \Log::info('ChangeStatus input', [
            'raw_remark'   => $request->input('remark'),
            'validated'    => $validated,
            'before'       => $customer->only(['status', 'remark']),
            'user_id'      => auth()->id(),
        ]);
        // ─────────────────────────────────────────────────
    
        $customer->update([
            'status' => $validated['status'],
            'remark' => $validated['remark'] ?? null,
            'approve_reject_by' => auth()->id(),
            'status_updated_at'  => now(),
        ]);
    
        \Log::info('ChangeStatus after', $customer->only(['status', 'remark']));
    
        return response()->json([
            'status'  => true,
            'message' => 'Status updated successfully',
            'data'    => [
                'id'         => $customer->id,
                'status'     => $customer->status,
                'remark'     => $customer->remark,           // ← look here
                'approved_by'=> $customer->approve_reject_by,
                'updated_at' => $customer->updated_at,
            ]
        ], 200);
    }
    
    /**
     * GET /api/my-hierarchy-users
     * Returns list of all users in your downline + yourself
     * with count, ids, names
     */
    public function getMyHierarchyUsers(Request $request)
    {
        try {
            $authUser = $request->user();
            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
            
            $isSuperAdmin = false;

            // First try: Spatie hasRole
            if (method_exists($authUser, 'hasRole')) {
                $isSuperAdmin =
                    $authUser->hasRole('superadmin') ||
                    $authUser->hasRole('subAdmin');
            }
            
            // Fallback: relation loaded
            if (!$isSuperAdmin && $authUser->relationLoaded('roles')) {
                $roles = $authUser->roles->pluck('name');
            
                $isSuperAdmin =
                    $roles->contains('superadmin') ||
                    $roles->contains('subAdmin');
            }
            
            // Final fallback: user_type
            if (!$isSuperAdmin && !empty($authUser->user_type)) {
                $userTypes = $authUser->user_type;
            
                if (is_string($userTypes)) {
                    $userTypes = json_decode($userTypes, true) ?? [];
                }
            
                $isSuperAdmin =
                    in_array('superadmin', (array)$userTypes, true) ||
                    in_array('subAdmin', (array)$userTypes, true);
            }
            
            $isBM = false;

            // BM Role Check
            if (method_exists($authUser, 'hasRole')) {
                $isBM = $authUser->hasRole('BM.');
            }
            
            if (!$isBM && $authUser->relationLoaded('roles')) {
                $roles = $authUser->roles->pluck('name');
                $isBM = $roles->contains('BM.');
            }
            
            if ($isSuperAdmin) {
            
                // Superadmin → all users
                $allIds = User::pluck('id')->toArray();
            
            } elseif ($isBM) {
            
                // BM → all users of same branch
                $allIds = User::where('branch_id', $authUser->branch_id)
                    ->pluck('id')
                    ->toArray();
            
            } else {
            
                // Normal hierarchy flow
                $allIds = [$authUser->id];
            
                $this->collectDownlineIds($authUser->id, $allIds);
            }
    
            // $allIds   = [$authUser->id];
            // $this->collectDownlineIds($authUser->id, $allIds);
    
            $users = User::whereIn('id', $allIds)
                ->select('id', 'name', 'mobile', 'designation_id', 'reportingid') // add more fields if needed
                ->orderByRaw("FIELD(id, " . implode(',', $allIds) . ")") // try to keep roughly hierarchical order
                ->get();
    
            // Optional: Build a simple tree structure if frontend wants nested view
            // $tree = $this->buildSimpleTree($users, $authUser->id);
    
            return response()->json([
                'status'       => true,
                'message'      => 'Hierarchy users retrieved',
                'total_users'  => $users->count(),
                'myself'       => [
                    'id'   => $authUser->id,
                    'name' => $authUser->name,
                ],
                'users'        => $users->map(function ($u) {
                    return [
                        'id'          => $u->id,
                        'name'        => $u->name,
                        'mobile'      => $u->mobile ?? null,
                        'reportingid' => $u->reportingid,
                        // 'designation' => $u->getdesignation->designation_name ?? null, // if relation exists
                    ];
                }),
                // 'tree'      => $tree,   // uncomment if you want nested structure
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch hierarchy',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}