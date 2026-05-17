<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterDistributor;
use App\Models\SecondaryCustomer;
use App\Models\User;
use App\Models\FieldKonnectAppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerApiController extends Controller
{
    // private function getVisibleUserIds(User $user): array
    // {
    //     $allIds = [$user->id];           // include myself
    
    //     $this->collectDownlineIds($user->id, $allIds);
    
    //     return array_unique($allIds);
    // }
    
    // /**
    //  * Recursively collect all user IDs in the downline
    //  */
    // private function collectDownlineIds(int $managerId, array &$ids): void
    // {
    //     $directReports = User::where('reportingid', $managerId)
    //         ->pluck('id')
    //         ->toArray();
    
    //     if (empty($directReports)) {
    //         return;
    //     }
    
    //     foreach ($directReports as $reportId) {
    //         if (!in_array($reportId, $ids)) {   // prevent potential cycles (rare)
    //             $ids[] = $reportId;
    //             $this->collectDownlineIds($reportId, $ids);
    //         }
    //     }
    // }
    /* =============================================
       Distributor List API
    ============================================= */
    public function distributors(Request $request)
    {
        try {
            $authUser = $request->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated - please provide valid token',
                ], 401);
            }
    
            $query = MasterDistributor::select(
                'id',
                'legal_name',
                'trade_name',
                'distributor_code',
                'mobile',
                'email',
                'billing_city',
                'billing_state',
                'billing_pincode',
                'business_status',
                'created_by',
                'sales_executive_id'   // optional: you can remove if not needed in response
            )
            ->where('business_status', '!=', 'Inactive');
    
            // ────────────────────────────────────────────────
            // SUPERADMIN CHECK
            // ────────────────────────────────────────────────
            $isSuperAdmin = false;
    
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
                // Super Admin can see all, but can filter by specific user if for_user_id is passed
                if ($request->filled('for_user_id')) {
                    $targetUserId = (int) $request->for_user_id;
    
                    $targetUser = User::find($targetUserId);
                    if (!$targetUser) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Requested user not found',
                        ], 404);
                    }
    
                    $query->where(function ($q) use ($targetUserId) {
                        $id = (int) $targetUserId;
                        $q->where('created_by', $id)
                          ->orWhere('sales_executive_id', 'LIKE', "%\"{$id}\"%")
                          ->orWhere('sales_executive_id', 'LIKE', "%{$id}%")
                          ->orWhereRaw("JSON_CONTAINS(sales_executive_id, '\"{$id}\"')")
                          ->orWhereRaw("JSON_SEARCH(sales_executive_id, 'one', '{$id}') IS NOT NULL");
                    });
                }
                // Else: Superadmin sees ALL active distributors
            } 
            else {

                // ────────────────────────────────────────────────
                // BM Role Check
                // ────────────────────────────────────────────────
                $isBM = false;
            
                if (method_exists($authUser, 'hasRole')) {
                    $isBM = $authUser->hasRole('BM.');
                }
            
                if (!$isBM && $authUser->relationLoaded('roles')) {
                    $roles = $authUser->roles->pluck('name');
                    $isBM = $roles->contains('BM.');
                }
            
                $targetUserId = $request->query('for_user_id');
            
                // ────────────────────────────────────────────────
                // BM FLOW
                // ────────────────────────────────────────────────
                if ($isBM) {
            
                    $visibleUserIds = User::where('branch_id', $authUser->branch_id)
                        ->pluck('id')
                        ->toArray();
            
                    // Optional for_user_id support
                    if ($targetUserId) {
            
                        if (!in_array((int)$targetUserId, $visibleUserIds)) {
            
                            return response()->json([
                                'status' => false,
                                'message' => 'You do not have permission to view this user distributors',
                            ], 403);
                        }
            
                        $visibleUserIds = [(int)$targetUserId];
                    }
            
                } else {
            
                    // ────────────────────────────────────────────────
                    // NORMAL HIERARCHY FLOW
                    // ────────────────────────────────────────────────
                    if ($targetUserId) {
            
                        $targetUser = User::find((int)$targetUserId);
            
                        if (!$targetUser) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Requested user not found',
                            ], 404);
                        }
            
                        $myVisibleIds = $this->getVisibleUserIds($authUser);
            
                        $myVisibleIds[] = $authUser->id;
            
                        $myVisibleIds = array_unique($myVisibleIds);
            
                        if (!in_array((int)$targetUserId, $myVisibleIds)) {
            
                            return response()->json([
                                'status' => false,
                                'message' => 'You do not have permission to view this user distributors',
                            ], 403);
                        }
            
                        $visibleUserIds = [(int)$targetUserId];
            
                    } else {
            
                        $visibleUserIds = $this->getVisibleUserIds($authUser);
            
                        $visibleUserIds[] = $authUser->id;
            
                        $visibleUserIds = array_unique($visibleUserIds);
                    }
                }
            
                // ────────────────────────────────────────────────
                // created_by + sales_executive_id filter
                // ────────────────────────────────────────────────
                $query->where(function ($q) use ($visibleUserIds) {
            
                    $q->whereIn('created_by', $visibleUserIds);
            
                    foreach ($visibleUserIds as $userId) {
            
                        $id = (int) $userId;
            
                        $q->orWhere('sales_executive_id', 'LIKE', "%\"{$id}\"%")
                          ->orWhere('sales_executive_id', 'LIKE', "%'{$id}'%")
                          ->orWhere('sales_executive_id', 'LIKE', "%[{$id}]%")
                          ->orWhere('sales_executive_id', 'LIKE', "%[{$id},%")
                          ->orWhere('sales_executive_id', 'LIKE', "%,{$id}]%")
                          ->orWhere('sales_executive_id', 'LIKE', "%,{$id},%")
                          ->orWhere('sales_executive_id', 'LIKE', "%{$id}%")
                          ->orWhereRaw("JSON_CONTAINS(sales_executive_id, '\"{$id}\"')")
                          ->orWhereRaw("JSON_CONTAINS(sales_executive_id, '{$id}')")
                          ->orWhereRaw("JSON_SEARCH(sales_executive_id, 'one', '{$id}') IS NOT NULL");
                    }
                });
            }
    
            // Optional: Add global search if needed in future
            if ($request->filled('global_search')) {
                $search = $request->global_search;
                $query->where(function ($q) use ($search) {
                    $q->where('legal_name', 'like', "%{$search}%")
                      ->orWhere('trade_name', 'like', "%{$search}%")
                      ->orWhere('distributor_code', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            }
    
            $distributors = $query->get();
    
            return response()->json([
                'status' => true,
                'message' => 'Distributor list fetched successfully',
                'data' => $distributors,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch distributor list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all visible user IDs for the logged-in user (Hierarchy)
     * TEMPORARY SAFE VERSION - Returns only self for now
     */
    protected function getVisibleUserIds(User $user): array
    {
        $allIds = [$user->id];
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

    /**
     * Get Secondary Customers List - With SuperAdmin + Hierarchy Support
     */
    public function secondaryCustomers()
    {
        $authUser = request()->user();
        if (!$authUser) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated - please provide valid token',
            ], 401);
        }
    
        $type = request()->query('type'); // Optional: filter by type if needed
    
        try {
            // ────────────────────────────────────────────────
            // SUPERADMIN CHECK (Same logic as index())
            // ────────────────────────────────────────────────
            $isSuperAdmin = false;
    
            // First try: Spatie hasRole
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
            if (!$isSuperAdmin && !empty($authUser->user_type)) {
                $userTypes = $authUser->user_type;
            
                if (is_string($userTypes)) {
                    $userTypes = json_decode($userTypes, true) ?? [];
                }
            
                $isSuperAdmin = 
                    in_array('superadmin', (array)$userTypes, true) ||
                    in_array('subAdmin', (array)$userTypes, true);
            }
    
            // ────────────────────────────────────────────────
            // Build Query
            // ────────────────────────────────────────────────
            $query = SecondaryCustomer::with([
                'country:id,country_name',
                'state:id,state_name',
                'district:id,district_name',
                'city:id,city_name',
                'pincode:id,pincode',
                'beat:id,beat_name',
                'distributor:id,legal_name'
            ])
            ->select(
                'id',
                'owner_name',
                'shop_name',
                'mobile_number',
                'whatsapp_number',
                'address_line',
                'country_id',
                'state_id',
                'district_id',
                'city_id',
                'pincode_id',
                'beat_id',
                'distributor_name',
                'type',
                'status',
                'active',
                'created_by',
                'employee_id',
                'created_at'
            )
            ->where('active', 'Y')
            ->where('status', 'APPROVED');   // You can make this dynamic if needed
    
            // Apply type filter if provided
            if ($type && in_array($type, ['RETAILER', 'WORKSHOP', 'MECHANIC', 'GARAGE'])) {
                $query->where('type', $type);
            }
    
            // ────────────────────────────────────────────────
            // Super Admin vs Normal User Logic
            // ────────────────────────────────────────────────
            if ($isSuperAdmin) {
                // Super Admin sees ALL customers (unless for_user_id is passed)
                if (request()->filled('for_user_id')) {
                    $targetUserId = request()->for_user_id;
    
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
                // Else: No where condition → Superadmin sees everything
            } else {

                // ────────────────────────────────────────────────
                // BM Role Check
                // ────────────────────────────────────────────────
                $isBM = false;
            
                if (method_exists($authUser, 'hasRole')) {
                    $isBM = $authUser->hasRole('BM.');
                }
            
                if (!$isBM && $authUser->relationLoaded('roles')) {
                    $roles = $authUser->roles->pluck('name');
                    $isBM = $roles->contains('BM.');
                }
            
                $targetUserId = request()->query('for_user_id');
            
                // ────────────────────────────────────────────────
                // BM FLOW
                // ────────────────────────────────────────────────
                if ($isBM) {
            
                    $visibleUserIds = User::where('branch_id', $authUser->branch_id)
                        ->pluck('id')
                        ->toArray();
            
                    // Optional for_user_id support
                    if ($targetUserId) {
            
                        if (!in_array($targetUserId, $visibleUserIds)) {
            
                            return response()->json([
                                'status' => false,
                                'message' => 'You do not have permission to view this user customers',
                            ], 403);
                        }
            
                        $visibleUserIds = [$targetUserId];
                    }
            
                } else {
            
                    // ────────────────────────────────────────────────
                    // NORMAL HIERARCHY FLOW
                    // ────────────────────────────────────────────────
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
                                'message' => 'You do not have permission to view this user customers',
                            ], 403);
                        }
            
                        $visibleUserIds = [$targetUserId];
            
                    } else {
            
                        $visibleUserIds = $this->getVisibleUserIds($authUser);
            
                        $visibleUserIds[] = $authUser->id;
            
                        $visibleUserIds = array_unique($visibleUserIds);
                    }
                }
            
                $query->where(function ($q) use ($visibleUserIds) {
            
                    $q->whereIn('created_by', $visibleUserIds)
                      ->orWhereIn('employee_id', $visibleUserIds);
                });
            }
    
            // Optional: Global Search
            if (request()->filled('global_search')) {
                $search = request()->global_search;
                $query->where(function ($q) use ($search) {
                    $q->where('owner_name', 'like', "%{$search}%")
                      ->orWhere('shop_name', 'like', "%{$search}%")
                      ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            }
    
            // You can add more filters here (owner_name, shop_name, city, beat, etc.) as needed
    
            $customers = $query->orderBy('created_at', 'desc')->get();
    
            return response()->json([
                'status' => true,
                'message' => 'Secondary customers fetched successfully',
                'data' => $customers
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch secondary customers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Keep your other methods as they are (customerList, getLocationByPincode)
    public function customerList()
    {
        $distributors = MasterDistributor::select(
            'id',
            'legal_name as name',
            'mobile'
        )->get()->map(function ($item) {
            $item->type = 'distributor';
            return $item;
        });

        $customers = SecondaryCustomer::select(
            'id',
            'shop_name as name',
            'mobile_number as mobile'
        )->get()->map(function ($item) {
            $item->type = 'secondary_customer';
            return $item;
        });

        $combined = $distributors->merge($customers);

        return response()->json([
            'status' => true,
            'customer' => $combined
        ]);
    }

    public function getLocationByPincode(Request $request)
    {
        $request->validate(['pincode' => 'required']);
    
        $data = DB::table('pincodes')
            ->join('cities','cities.id','=','pincodes.city_id')
            ->join('districts','districts.id','=','cities.district_id')
            ->join('states','states.id','=','districts.state_id')
            ->join('countries','countries.id','=','states.country_id')
            ->where('pincodes.pincode', $request->pincode)
            ->select(
                'pincodes.id as pincode_id',
                'pincodes.pincode',
                'cities.id as city_id',
                'cities.city_name as city',
                'districts.id as district_id',
                'districts.district_name as district',
                'states.id as state_id',
                'states.state_name as state',
                'countries.id as country_id',
                'countries.country_name as country'
            )
            ->get();
    
        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Pincode not found'
            ]);
        }
    
        $first = $data->first();
    
        // Add arrays
        $first->city_ids = $data->pluck('city_id')->unique()->values();
        $first->cities = $data->pluck('city')->unique()->values();
    
        // Add full data array
        $first->full_data = $data->map(function ($item) {
            return [
                'city_id'   => $item->city_id,
                'city'      => $item->city,
                'district_id' => $item->district_id,
                'district'  => $item->district,
                'state_id'  => $item->state_id,
                'state'     => $item->state,
                'country_id' => $item->country_id,
                'country'   => $item->country,
            ];
        })->unique('city_id')->values();
    
        return response()->json([
            'status' => true,
            ...(array) $first
        ]);
    }
    
    public function getAppVersion()
    {
        try {
    
            // =========================================
            // GET LATEST SETTINGS
            // =========================================
            $setting = FieldKonnectAppSetting::latest()->first();
    
            if (!$setting) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Settings not found.'
                ], 404);
            }
    
            // =========================================
            // SUCCESS RESPONSE
            // =========================================
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'android_version' => $setting->app_version ?? '',
                    'ios_version'     => $setting->app_ios_version ?? '',
                ]
            ], 200);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
    
        }
    }
}