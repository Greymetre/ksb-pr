<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterDistributorsExport;
use App\Exports\MasterDistributorsTemplateExport;
use App\Models\User;

class MasterDistributorApiController extends Controller
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

    private function hasAnyRole(User $user, array $roles): bool
    {
        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        if ($user->relationLoaded('roles')) {
            return $user->roles->pluck('name')->intersect($roles)->isNotEmpty();
        }

        if (!empty($user->user_type)) {
            $userTypes = is_string($user->user_type)
                ? (json_decode($user->user_type, true) ?? [])
                : (array) $user->user_type;

            return !empty(array_intersect($roles, $userTypes));
        }

        return false;
    }

    private function visibleUserIdsFor(User $user): array
    {
        if ($this->hasAnyRole($user, ['BM.', 'Marketing Team'])) {
            $branches = array_filter(array_map('trim', explode(',', (string) $user->branch_id)));

            if (!empty($branches)) {
                return User::where('active', 'Y')
                    ->where(function ($query) use ($branches) {
                        foreach ($branches as $branch) {
                            $query->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branch]);
                        }
                    })
                    ->pluck('id')
                    ->toArray();
            }
        }

        $visibleUserIds = $this->getVisibleUserIds($user);
        $visibleUserIds[] = $user->id;

        return array_values(array_unique($visibleUserIds));
    }

    private function applyDistributorAccessScope($query, User $user)
    {
        if ($this->hasAnyRole($user, ['Distributor'])) {
            return $query->where('id', $user->customerid);
        }

        if ($this->hasAnyRole($user, ['superadmin', 'subAdmin'])) {
            return $query;
        }

        $visibleUserIds = $this->visibleUserIdsFor($user);

        return $query->where(function ($q) use ($visibleUserIds) {
            $q->whereIn('created_by', $visibleUserIds)
                ->orWhereIn('supervisor_id', $visibleUserIds);

            foreach ($visibleUserIds as $userId) {
                $id = (int) $userId;

                $q->orWhereJsonContains('sales_executive_id', $id)
                    ->orWhereJsonContains('sales_executive_id', (string) $id)
                    ->orWhereRaw("JSON_SEARCH(sales_executive_id, 'one', ?) IS NOT NULL", [(string) $id]);
            }
        });
    }

    private function accessibleDistributorQuery(User $user)
    {
        return $this->applyDistributorAccessScope(MasterDistributor::query(), $user);
    }

    private function findAccessibleDistributor($id, User $user): ?MasterDistributor
    {
        return $this->accessibleDistributorQuery($user)->find($id);
    }
    
    public function index(Request $request)
    {
        try {
            $authUser = $request->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated - please provide valid token',
                ], 401);
            }
    
            $query = MasterDistributor::query();
    
            // ────────────────────────────────────────────────
            // SUPERADMIN CHECK (Same as SecondaryCustomer)
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
                // Super Admin sees everything (with optional for_user_id filter)
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
                        $id = (int) $targetUserId;
                        $q->where('created_by', $id)
                          ->orWhere('sales_executive_id', 'LIKE', "%\"{$id}\"%")
                          ->orWhere('sales_executive_id', 'LIKE', "%{$id}%")
                          ->orWhereRaw("JSON_CONTAINS(sales_executive_id, '\"{$id}\"')")
                          ->orWhereRaw("JSON_SEARCH(sales_executive_id, 'one', '{$id}') IS NOT NULL");
                    });
                }
                // Else: Superadmin sees ALL master distributors
            } 
            else {
                // ────────────────────────────────────────────────
                // Normal User - Hierarchy + Sales Executive Logic
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
                            'message' => 'You do not have permission to view this user\'s distributors',
                        ], 403);
                    }
    
                    $visibleUserIds = [$targetUserId];
                } else {
                    $visibleUserIds = $this->getVisibleUserIds($authUser);
                    $visibleUserIds[] = $authUser->id;
                    $visibleUserIds = array_unique($visibleUserIds);
                }
    
                // ──────── FIXED Sales Executive Filter ────────
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
                
                // If BM → get all branch users
                if ($isBM) {
                
                    $visibleUserIds = User::where('branch_id', $authUser->branch_id)
                        ->pluck('id')
                        ->toArray();
                }
                
                // ──────── Sales Executive Filter ────────
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
    
            // ────────────────────────────────────────────────
            // Filters (Existing + Improved)
            // ────────────────────────────────────────────────
            if ($request->filled('global_search')) {
                $search = $request->global_search;
                $query->where(function ($q) use ($search) {
                    $q->where('distributor_code', 'like', "%{$search}%")
                      ->orWhere('legal_name', 'like', "%{$search}%")
                      ->orWhere('trade_name', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            }
    
            if ($request->filled('distributor_code')) {
                $query->where('distributor_code', 'like', "%{$request->distributor_code}%");
            }
            if ($request->filled('legal_name')) {
                $query->where('legal_name', 'like', "%{$request->legal_name}%");
            }
            if ($request->filled('trade_name')) {
                $query->where('trade_name', 'like', "%{$request->trade_name}%");
            }
            if ($request->filled('contact_person')) {
                $query->where('contact_person', 'like', "%{$request->contact_person}%");
            }
            if ($request->filled('mobile')) {
                $query->where('mobile', 'like', "%{$request->mobile}%");
            }
            if ($request->filled('business_status')) {
                $query->where('business_status', $request->business_status);
            } else {
                $query->where('business_status', '!=', 'Inactive'); // default
            }
    
            // ── Add check-in & check-out status (same as before) ──
            $today = now()->startOfDay()->toDateString();
    
            $query->addSelect([
                // Check-in fields
                'last_checkin_date' => \App\Models\CheckIn::select('checkin_date')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
    
                'last_checkin_time' => \App\Models\CheckIn::select('checkin_time')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
    
                'has_checked_in_today' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->whereDate('checkin_date', $today),
    
                // Check-out fields
                'last_checkout_date' => \App\Models\CheckIn::select('checkout_date')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
    
                'current_visit_is_open' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->whereNull('checkout_date')
                    ->whereDate('checkin_date', $today),
    
                'last_checkout_time' => \App\Models\CheckIn::select('checkout_time')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
    
                'has_checked_out_today' => \App\Models\CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->whereDate('checkout_date', $today),
    
                'last_checkin_id' => \App\Models\CheckIn::select('id')
                    ->whereColumn('entity_id', 'master_distributors.id')
                    ->where('entity_type', 'distributor')
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
            ]);
    
            // Sort by newest first
            $query->orderBy('created_at', 'desc');
    
            // Pagination
            $perPage = $request->query('per_page', 10);
            $distributors = $query->paginate($perPage);
    
            // Clean response
            $cleanData = [
                'current_page' => $distributors->currentPage(),
                'data' => $distributors->items(),
                'from' => $distributors->firstItem(),
                'to' => $distributors->lastItem(),
                'per_page' => $distributors->perPage(),
                'total' => $distributors->total(),
                'last_page' => $distributors->lastPage(),
            ];
    
            return response()->json([
                'status' => true,
                'message' => 'Master distributors retrieved successfully',
                'data' => $cleanData,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch master distributors',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $authUser = request()->user();

            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Load only distributors visible to the authenticated user.
            $distributor = $this->applyDistributorAccessScope(MasterDistributor::with([
                'supervisor',
                // Corrected relationships using your actual column names
                'billingCity' => function ($query) {
                    $query->select('id', 'city_name'); // adjust column name if different
                },
                'billingPincode' => function ($query) {
                    $query->select('id', 'pincode');
                },
            ]), $authUser)->find($id);

            if (!$distributor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Distributor not found',
                ], 404);
            }

            // Manually load sales executives (because it's not a real relation)
            $distributor->sales_executives = $distributor->salesExecutives();
            

            // ────────────────────────────────────────────────
            //   Add check-in / check-out data for this user
            // ────────────────────────────────────────────────
            $today = now()->startOfDay()->toDateString();

            $checkInQuery = \App\Models\CheckIn::where('entity_type', 'distributor')
                ->where('entity_id', $id)
                ->where('user_id', $authUser->id);

            // Latest check-in (regardless of checkout status)
            $lastCheckIn = (clone $checkInQuery)
                ->orderByDesc('checkin_date')
                ->orderByDesc('checkin_time')
                ->first(['id', 'checkin_date', 'checkin_time', 'checkin_address', 'checkout_date', 'checkout_time', 'checkout_address', 'time_interval']);

            // Latest check-out (only records that actually have checkout)
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

            // Prepare clean check-in/check-out data
            $checkData = [
                'last_checkin' => $lastCheckIn ? [
                    'checkin_id'       => $lastCheckIn->id,
                    'checkin_datetime' => $lastCheckIn->checkin_date . ' ' . $lastCheckIn->checkin_time,
                    'checkin_address'  => $lastCheckIn->checkin_address,
                    'checkout_datetime'=> $lastCheckIn->checkout_date 
                        ? $lastCheckIn->checkout_date . ' ' . $lastCheckIn->checkout_time 
                        : null,
                    'checkout_address' => $lastCheckIn->checkout_address,
                    'duration'         => $lastCheckIn->time_interval,
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

            return response()->json([
                'status'      => true,
                'message'     => 'Distributor retrieved successfully',
                'data'        => $distributor,
                'check_status'=> $checkData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve distributor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
    \Log::info('MasterDistributor store called', [
        'all_input' => $request->all(),
        'files' => array_keys($request->allFiles()),
        'memory_start' => memory_get_usage(true) / 1024 / 1024 . ' MB',
    ]);

    try {
        $validated = $this->validateData($request);

        \Log::info('Validation passed', ['validated' => $validated]);

        DB::beginTransaction();

        $data = $validated;

        $data['same_as_billing'] = $request->boolean('same_as_billing');

        if ($data['same_as_billing']) {
            $data['shipping_address'] = $data['billing_address'] ?? null;
            $data['shipping_city'] = $data['billing_city'] ?? null;
            $data['shipping_district'] = $data['billing_district'] ?? null;
            $data['shipping_state'] = $data['billing_state'] ?? null;
            $data['shipping_country'] = $data['billing_country'] ?? null;
            $data['shipping_pincode'] = $data['billing_pincode'] ?? null;
        }

        $data['sales_executive_id'] = json_encode($request->input('sales_executive_id', []));

        // File uploads with logging
        if ($request->hasFile('shop_image')) {
            \Log::info('Uploading shop_image');
            $data['shop_image'] = $request->file('shop_image')->store('distributors/shop_images', 'public');
        }
        if ($request->hasFile('profile_image')) {
            \Log::info('Uploading profile_image');
            $data['profile_image'] = $request->file('profile_image')->store('distributors/profile_images', 'public');
        }
        if ($request->hasFile('cancelled_cheque')) {
            \Log::info('Uploading cancelled_cheque');
            $data['cancelled_cheque'] = $request->file('cancelled_cheque')->store('distributors/cheques', 'public');
        }
        if ($request->hasFile('mou_file')) {
            \Log::info('Uploading mou_file');
            $data['mou_file'] = $request->file('mou_file')->store('distributors/mou', 'public');
        }
        if ($request->hasFile('documents')) {
            \Log::info('Uploading documents', ['count' => count($request->file('documents'))]);
            $paths = [];
            foreach ($request->file('documents') as $file) {
                $paths[] = $file->store('distributors/documents', 'public');
            }
            $data['documents'] = json_encode($paths);
        }

        \Log::info('Creating record', ['data' => $data]);

        $distributor = MasterDistributor::create($data);

        DB::commit();

        \Log::info('MasterDistributor created successfully', ['id' => $distributor->id]);

        return response()->json([
            'status'  => true,
            'message' => 'Master distributor created successfully',
            'data'    => $distributor,  // ← no ->load()
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        \Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('MasterDistributor store failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'memory_peak' => memory_get_peak_usage(true) / 1024 / 1024 . ' MB'
        ]);
        return response()->json([
            'status'  => false,
            'message' => 'Failed to create master distributor',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $distributor = $this->findAccessibleDistributor($id, $authUser);

        if (!$distributor) {
            return response()->json([
                'status'  => false,
                'message' => 'Distributor not found',
            ], 404);
        }

        $validated = $this->validateData($request, $id);

        DB::beginTransaction();
        try {
            $data = $validated;

            // Handle same as billing
            $data['same_as_billing'] = $request->boolean('same_as_billing');
            if ($data['same_as_billing']) {
                $data['shipping_address'] = $data['billing_address'];
                $data['shipping_city'] = $data['billing_city'];
                $data['shipping_district'] = $data['billing_district'];
                $data['shipping_state'] = $data['billing_state'];
                $data['shipping_country'] = $data['billing_country'];
                $data['shipping_pincode'] = $data['billing_pincode'];
            }

            // Handle sales executive IDs
            $data['sales_executive_id'] = json_encode($request->input('sales_executive_id', []));

            // Handle files
            if ($request->hasFile('shop_image')) {
                if ($distributor->shop_image) Storage::disk('public')->delete($distributor->shop_image);
                $data['shop_image'] = $request->file('shop_image')->store('distributors/shop_images', 'public');
            }
            if ($request->hasFile('profile_image')) {
                if ($distributor->profile_image) Storage::disk('public')->delete($distributor->profile_image);
                $data['profile_image'] = $request->file('profile_image')->store('distributors/profile_images', 'public');
            }
            if ($request->hasFile('cancelled_cheque')) {
                if ($distributor->cancelled_cheque) Storage::disk('public')->delete($distributor->cancelled_cheque);
                $data['cancelled_cheque'] = $request->file('cancelled_cheque')->store('distributors/cheques', 'public');
            }
            if ($request->hasFile('mou_file')) {
                if ($distributor->mou_file) Storage::disk('public')->delete($distributor->mou_file);
                $data['mou_file'] = $request->file('mou_file')->store('distributors/mou', 'public');
            }
            if ($request->hasFile('documents')) {
                $paths = json_decode($distributor->documents, true) ?? [];
                foreach ($request->file('documents') as $file) {
                    $paths[] = $file->store('distributors/documents', 'public');
                }
                $data['documents'] = json_encode($paths);
            }

            $distributor->update($data);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Master distributor updated successfully',
                'data'    => $distributor->fresh(),  // ← no ->load()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update master distributor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $authUser = request()->user();

            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $distributor = $this->findAccessibleDistributor($id, $authUser);

            if (!$distributor) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Distributor not found',
                ], 404);
            }

            // Delete files
            foreach (['shop_image', 'profile_image', 'cancelled_cheque', 'mou_file'] as $file) {
                if ($distributor->$file && Storage::exists($distributor->$file)) {
                    Storage::delete($distributor->$file);
                }
            }
            if ($distributor->documents) {
                $docs = json_decode($distributor->documents, true) ?? [];
                foreach ($docs as $doc) {
                    if (Storage::exists($doc)) Storage::delete($doc);
                }
            }

            $distributor->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Distributor deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete distributor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function validateData(Request $request, $id = null)
    {
        $rules = [
            'legal_name'         => 'required|string|max:255',
            'trade_name'         => 'nullable|string|max:255',
            'distributor_code'   => [
                'required',
                'string',
                'max:100',
                Rule::unique('master_distributors', 'distributor_code')->ignore($id),
            ],
            'category'           => 'required|string',
            'business_status'    => 'required|in:Active,Inactive,On Hold',
            'business_start_date'=> 'required|date',
            'contact_person'     => 'required|string|max:255',
            'designation'        => 'nullable|string|max:255',
            'mobile'             => [
                'required',
                'digits:10',
                Rule::unique('master_distributors', 'mobile')->ignore($id),
            ],
            'alternate_mobile'   => 'nullable|digits:10',
            'email'              => [
                'nullable',
                'email',
                Rule::unique('master_distributors', 'email')->ignore($id),
            ],
            'secondary_email'    => 'nullable|email',

            'billing_address'    => 'required|string|max:500',
            'billing_city'       => 'required|string',
            'billing_district'   => 'required|string',
            'billing_state'      => 'required|string',
            'billing_country'    => 'required|string',
            'billing_pincode'    => 'required|string',

            'same_as_billing'    => 'boolean',

            'shipping_address'   => 'required_if:same_as_billing,false|string|max:500',
            'shipping_city'      => 'required_if:same_as_billing,false|string',
            'shipping_district'  => 'required_if:same_as_billing,false|string',
            'shipping_state'     => 'required_if:same_as_billing,false|string',
            'shipping_country'   => 'required_if:same_as_billing,false|string',
            'shipping_pincode'   => 'required_if:same_as_billing,false|string',

            'sales_zone'         => 'required|string',
            'area_territory'     => 'required|string',
            'beat_route'         => 'required|string',
            'market_classification' => 'required|string',
            'competitor_brands'  => 'required|string',

            'gst_number'         => 'required|string',
            'pan_number'         => 'required|string',
            'registration_type'  => 'required|string',

            'bank_name'          => 'required|string',
            'account_holder'     => 'required|string',
            'account_number'     => 'required|string',
            'ifsc'               => 'required|string',
            'branch_name'        => 'required|string',
            'credit_limit'       => 'required|numeric|min:0',
            'credit_days'        => 'required|integer|min:0',
            'avg_monthly_purchase' => 'required|numeric|min:0',
            'outstanding_balance'  => 'required|numeric',
            'preferred_payment_method' => 'required|string',

            // Updated fields as per your request
            'monthly_sales'      => 'required|numeric|min:0',
            'product_categories' => 'required|string',
            'secondary_sales_required' => 'required|in:Yes,No', // ← only Yes or No
            'last_12_months_sales' => 'required|numeric|min:0',
            'sales_executive_id' => 'required|array|min:1',
            'sales_executive_id.*' => 'exists:users,id',
            'supervisor_id'      => 'required|exists:users,id',
            'customer_segment'   => 'required|string',

            'weekly_tai_alert'   => 'required|in:A,B', // ← only A or B
            'target_vs_achievement' => 'required|string|max:255', // ← open input (can be %, text, etc.)
            'schemes_updates'    => 'required|in:A,B', // ← only A or B
            'new_launch_update'  => 'required|in:A,B', // ← only A or B
            'payment_alert'      => 'required|in:A,B', // ← only A or B
            'pending_orders'     => 'required|in:A,B', // ← only A or B
            'inventory_status'   => 'required|in:A,B', // ← only A or B

            'turnover'           => 'required|numeric|min:0',
            'staff_strength'     => 'required|integer|min:0',
            'vehicles_capacity'  => 'required|string',
            'area_coverage'      => 'required|string',
            'other_brands_handled' => 'required|string',
            'warehouse_size'     => 'required|string',

            'shop_image'         => 'nullable|file|mimes:jpeg,png,jpg|max:3072',
            'profile_image'      => 'nullable|file|mimes:jpeg,png,jpg|max:3072',
            'cancelled_cheque'   => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'mou_file'           => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'documents.*'        => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ];

        $messages = [
            'sales_executive_id.required'          => 'At least one sales executive is required.',
            'sales_executive_id.*.exists'          => 'One or more selected sales executives are invalid.',
            'secondary_sales_required.in'          => 'Secondary sales required must be Yes or No.',
            'weekly_tai_alert.in'                  => 'Weekly TAI alert must be A or B.',
            'schemes_updates.in'                   => 'Schemes updates must be A or B.',
            'new_launch_update.in'                 => 'New launch update must be A or B.',
            'payment_alert.in'                     => 'Payment alert must be A or B.',
            'pending_orders.in'                    => 'Pending orders must be A or B.',
            'inventory_status.in'                  => 'Inventory status must be A or B.',
        ];

        return $request->validate($rules, $messages);
    }

    public function getStates($country_id)
    {
        $states = \App\Models\State::where('country_id', $country_id)
            ->orderBy('state_name')
            ->get(['id', 'state_name']);

        return response()->json($states);
    }

    public function getDistricts($state_id)
    {
        $districts = \App\Models\District::where('state_id', $state_id)
            ->orderBy('district_name')
            ->get(['id', 'district_name']);

        return response()->json($districts);
    }

    public function getCities($district_id)
    {
        $cities = \App\Models\City::where('district_id', $district_id)
            ->orderBy('city_name')
            ->get(['id', 'city_name']);

        return response()->json($cities);
    }

    public function getPincodes($city_id)
    {
        $pincodes = \App\Models\Pincode::where('city_id', $city_id)
            ->orderBy('pincode')
            ->get(['id', 'pincode']);

        return response()->json($pincodes);
    }

    public function downloadExcel(Request $request)
    {
        $filename = 'master_distributors_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new MasterDistributorsExport($request->all()), $filename);
    }

    public function downloadTemplate(Request $request)
    {
        $filename = 'template_master_distributors_upload.xlsx';
        return Excel::download(new MasterDistributorsTemplateExport(), $filename);
    }

    public function getSupervisors(Request $request)
    {
        $supervisors = User::query()
            ->where('active', 'Y')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($u) => [
                'id'          => $u->id,
                'name'        => $u->name,
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'Supervisors fetched',
            'data'    => $supervisors,
            'count'   => $supervisors->count(),
        ]);
    }
}
