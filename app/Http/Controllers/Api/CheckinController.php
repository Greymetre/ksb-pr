<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\CheckIn;
use App\Models\Customers;
use App\Models\VisitReport;
use App\Models\BeatSchedule;
use App\Models\CheckInDraft;
use App\Models\MasterDistributor;
use App\Models\SecondaryCustomer;

class CheckinController extends Controller
{
    public function __construct()
    {
        $this->checkin = new CheckIn();

        $this->successStatus  = 200;
        $this->created        = 201;
        $this->accepted       = 202;
        $this->noContent      = 204;
        $this->badrequest     = 400;
        $this->unauthorized   = 401;
        $this->notFound       = 404;
        $this->notactive      = 406;
        $this->internalError  = 500;
    }

    public function getCheckin(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated. Please login again.'
                ], $this->unauthorized); // 401
            }
            if ($user->active == 'N') {
                return response()->json(['status' => 'error', 'message' => 'User Inactive'], 401);
            }

            $user_id = $user->id;
            $pageSize = $request->input('pageSize');

            $query = $this->checkin->where('user_id', $user_id)
                ->select(
                    'id',
                    'customer_id',          // old field - kept for backward compatibility
                    'entity_type',
                    'entity_id',
                    'checkin_date',
                    'checkin_time',
                    'checkin_latitude',
                    'checkin_longitude',
                    'checkin_address',
                    'checkout_date',
                    'checkout_time',
                    'checkout_latitude',
                    'checkout_longitude',
                    'checkout_address',
                    'beatscheduleid'
                )
                ->orderBy('checkin_date', 'desc')
                ->orderBy('checkin_time', 'desc');

            $db_data = $pageSize ? $query->paginate($pageSize) : $query->get();

            $data = collect([]);
            if ($db_data->isNotEmpty()) {
                foreach ($db_data as $row) {
                    $entity = null;
                    $name   = null;
                    $type   = null;

                    if ($row->entity_type === 'distributor' && $row->entity_id) {
                        $entity = MasterDistributor::find($row->entity_id);
                        $name   = $entity ? ($entity->trade_name ?? $entity->legal_name ?? 'Unknown Distributor') : null;
                        $type   = $entity ? ($entity->category ?? 'Distributor') : null;
                    } elseif ($row->entity_type === 'secondary_customer' && $row->entity_id) {
                        $entity = SecondaryCustomer::find($row->entity_id);
                        $name   = $entity ? ($entity->shop_name ?? 'Unknown Shop') : null;
                        $type   = $entity ? ($entity->sub_type ?? 'Secondary Customer') : null;
                    } else {
                        // fallback to old customer
                        $entity = Customers::with('customertypes')->find($row->customer_id);
                        $name   = $entity ? $entity->name : null;
                        $type   = $entity ? ($entity->customertypes->customertype_name ?? '') : null;
                    }

                    $data->push([
                        'checkin_id'         => $row->id,
                        'entity_type'        => $row->entity_type ?? 'customer',
                        'entity_id'          => $row->entity_id ?? $row->customer_id,
                        'customer_name'      => $name ?? 'Unknown',
                        'customer_type'      => $type ?? '',
                        'checkin_date'       => $row->checkin_date,
                        'checkin_time'       => $row->checkin_time,
                        'checkin_latitude'   => $row->checkin_latitude,
                        'checkin_longitude'  => $row->checkin_longitude,
                        'checkin_address'    => $row->checkin_address,
                        'checkout_date'      => $row->checkout_date,
                        'checkout_time'      => $row->checkout_time,
                        'checkout_latitude'  => $row->checkout_latitude,
                        'checkout_longitude' => $row->checkout_longitude,
                        'checkout_address'   => $row->checkout_address,
                        'beat_schedule_id'   => $row->beatscheduleid ?? 0,
                    ]);
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data retrieved successfully.',
                    'data'    => $data
                ], $this->successStatus);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'No Record Found.',
                'data'    => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    public function submitCheckin(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated. Please login again.'
                ], $this->unauthorized); // 401
            }

            if ($user->active !== 'Y') {   // more explicit than == 'N'
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Your account is inactive. Contact support.'
                ], $this->notactive); // 406 or 403 if you prefer
            }
            
            if ($user->active == 'N') {
                return response()->json(['status' => 'error', 'message' => 'User Inactive'], 401);
            }

            $validator = Validator::make($request->all(), [
                'entity_type'       => 'required|in:customer,distributor,secondary_customer',
                'entity_id'         => 'required|integer|min:1',
                'checkin_latitude'  => 'required|numeric',
                'checkin_longitude' => 'required|numeric',
                'beatScheduleId'    => 'nullable|integer|exists:beat_schedules,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()
                ], $this->badrequest);
            }

            // ────────────────────────────────────────────────
            //   ←←←  Add this block  ←←←
            // ────────────────────────────────────────────────
            $existingActiveCheckin = CheckIn::where('user_id', $user->id)
                ->whereNull('checkout_date')           // not checked out yet
                ->whereNull('checkout_time')
                ->latest('checkin_date', 'checkin_time') // most recent
                ->first();

            if ($existingActiveCheckin) {
                // Optional: load name of current place
                $currentEntityName = 'Unknown';

                if ($existingActiveCheckin->entity_type === 'distributor') {
                    $dist = MasterDistributor::find($existingActiveCheckin->entity_id);
                    $currentEntityName = $dist ? ($dist->trade_name ?? $dist->legal_name) : 'Distributor';
                } elseif ($existingActiveCheckin->entity_type === 'secondary_customer') {
                    $sec = SecondaryCustomer::find($existingActiveCheckin->entity_id);
                    $currentEntityName = $sec ? ($sec->shop_name ?? 'Shop') : 'Secondary Customer';
                } elseif ($existingActiveCheckin->entity_type === 'customer') {
                    $cust = Customers::find($existingActiveCheckin->customer_id ?? $existingActiveCheckin->entity_id);
                    $currentEntityName = $cust ? $cust->name : 'Customer';
                }

                return response()->json([
                    'status'  => 'error',
                    'message' => "You have already checked in to {$currentEntityName}. Please check out first."
                ], $this->badrequest);   // or use 409 Conflict if you prefer
            }

            $entityType = $request->entity_type;
            $entityId   = $request->entity_id;

            // Load entity
            $entity = match ($entityType) {
                'distributor'        => MasterDistributor::find($entityId),
                'secondary_customer' => SecondaryCustomer::find($entityId),
                'customer'           => Customers::find($entityId),
                default              => null,
            };

            if (!$entity) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Entity not found'
                ], $this->notFound);
            }

            $distance = '';
            if ($request->checkin_latitude && $request->checkin_longitude) {
                // You may need to update distance() function to accept entity_type
                // For now we pass entity_id only (old behavior)
                $distance = distance($request->checkin_latitude, $request->checkin_longitude, $entityId, $entityType);
            }

            $beatScheduleId = $request->beatScheduleId;

            // Try to auto-detect beat if not provided (only for customer & secondary)
            if (!$beatScheduleId && in_array($entityType, ['customer', 'secondary_customer'])) {
                $beatScheduleId = BeatSchedule::where('user_id', $user->id)
                    ->whereDate('beat_date', getcurentDate())
                    ->whereHas('beatcustomers', function ($q) use ($entityId) {
                        $q->where('customer_id', $entityId);
                    })
                    ->value('id');
            }

            $checkinAddress = getLatLongToAddress(
                $request->checkin_longitude,
                $request->checkin_latitude
                
            );

            $checkinId = $this->checkin->insertGetId([
                'active'             => 'Y',
                'user_id'            => $user->id,
                'customer_id'        => ($entityType === 'customer') ? $entityId : null, // keep old field for backward compat
                'entity_type'        => $entityType,
                'entity_id'          => $entityId,
                'checkin_date'       => getcurentDate(),
                'checkin_time'       => getcurentTime(),
                'checkin_latitude'   => $request->checkin_latitude,
                'checkin_longitude'  => $request->checkin_longitude,
                'checkin_address'    => $checkinAddress,
                'distance'           => $distance,
                'beatscheduleid'     => $beatScheduleId,
            ]);

            if ($checkinId) {
                $name = match ($entityType) {
                    'distributor'        => $entity->trade_name ?? $entity->legal_name ?? 'Unknown',
                    'secondary_customer' => $entity->shop_name ?? 'Unknown',
                    'customer'           => $entity->name ?? 'Unknown',
                    default              => 'Unknown',
                };

                $typeName = match ($entityType) {
                    'distributor'        => $entity->category ?? 'Distributor',
                    'secondary_customer' => $entity->sub_type ?? 'Secondary Customer',
                    'customer'           => $entity->customertypes->customertype_name ?? 'Customer',
                    default              => '',
                };

                return response()->json([
                    'status'       => 'success',
                    'message'      => 'Check In successfully',
                    'checkin_id'   => $checkinId,
                    'entity_name'  => $name,
                    'entity_type'  => $typeName,
                    '$distance'    => $distance   
                ], $this->successStatus);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Error in Check In'
            ], $this->badrequest);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    public function submitCheckout(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->active == 'N') {
                return response()->json(['status' => 'error', 'message' => 'User Inactive'], 401);
            }

            $validator = Validator::make($request->all(), [
                'checkin_id'         => 'required|exists:check_in,id',
                'checkout_latitude'  => 'required|numeric',
                'checkout_longitude' => 'required|numeric',
                'description'        => 'required|string|max:1540',
                'entity_id'          => 'required|integer|min:1',
                'entity_type'        => 'required|in:customer,distributor,secondary_customer',
                'visit_type_id'      => 'nullable|exists:visit_types,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()
                ], $this->badrequest);
            }

            $checkIn = CheckIn::where('id', $request->checkin_id)->first();
            if (!$checkIn) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Check-in record not found'
                ], $this->notFound);
            }

            $checkoutAddress = getLatLongToAddress(
                $request->checkout_longitude,
                 $request->checkout_latitude
            );

            $timeInterval = gmdate(
                "H:i:s",
                strtotime(now()) - strtotime("{$checkIn->checkin_date} {$checkIn->checkin_time}")
            );

            $updated = $this->checkin->where('id', $request->checkin_id)->update([
                'checkout_date'      => getcurentDate(),
                'checkout_time'      => getcurentTime(),
                'checkout_latitude'  => $request->checkout_latitude,
                'checkout_longitude' => $request->checkout_longitude,
                'checkout_address'   => $checkoutAddress,
                'time_interval'      => $timeInterval,
            ]);

            if (!$updated) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to update checkout'
                ], $this->badrequest);
            }

            VisitReport::create([
                'checkin_id'    => $request->checkin_id,
                'user_id'       => $user->id,
                'customer_id'   => ($request->entity_type === 'customer') ? $request->entity_id : null,
                'visit_type_id' => $request->visit_type_id,
                'description'   => $request->description,
                'visit_image'   => '',
                'created_by'    => $user->id,
                'next_visit'    => $request->next_visit ? date('Y-m-d H:i:s', strtotime($request->next_visit)) : null,
                'created_at'    => now(),
            ]);

            // Clean draft if exists
            CheckInDraft::where('checkin_id', $request->checkin_id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Check Out successfully'
            ], $this->successStatus);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    public function addCheckinDraft(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->active == 'N') {
                return response()->json(['status' => 'error', 'message' => 'User Inactive'], 401);
            }

            $validator = Validator::make($request->all(), [
                'checkin_id' => 'required|exists:check_in,id',
                'draft_msg'  => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()
                ], $this->badrequest);
            }

            $draft = CheckInDraft::updateOrCreate(
                ['checkin_id' => $request->checkin_id],
                ['draft_msg' => $request->draft_msg]
            );

            return response()->json([
                'status'  => 'success',
                'data'    => $draft,
                'message' => 'Draft saved successfully'
            ], $this->successStatus);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    public function getCheckinDraft(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->active == 'N') {
                return response()->json(['status' => 'error', 'message' => 'User Inactive'], 401);
            }

            $validator = Validator::make($request->all(), [
                'checkin_id' => 'required|exists:check_in,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()
                ], $this->badrequest);
            }

            $draft = CheckInDraft::where('checkin_id', $request->checkin_id)->first();

            return response()->json([
                'status'  => 'success',
                'data'    => $draft,
                'message' => 'Draft retrieved successfully'
            ], $this->successStatus);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    public function getCheckinByEntity(Request $request)  
    {  
        try {  
            $user = $request->user();  
            if ($user->active == 'N') {  
                return response()->json(['status' => 'error', 'message' => 'User Inactive'], 401);  
            }  

            $validator = Validator::make($request->all(), [  
                'entity_type' => 'required|in:customer,distributor,secondary_customer',  
                'entity_id'   => 'required|integer|min:1',  
            ]);  

            if ($validator->fails()) {  
                return response()->json([  
                    'status'  => 'error',  
                    'message' => $validator->errors()  
                ], $this->badrequest);  
            }  

            $entityType = $request->entity_type;  
            $entityId   = $request->entity_id;  

            // Fetch the latest check-in for this entity + user (change to ->get() for all records)  
            $checkIn = CheckIn::where('user_id', $user->id)  
                ->where('entity_type', $entityType)  
                ->where('entity_id', $entityId)  
                ->select(  
                    'id',  
                    'entity_type',  
                    'entity_id',  
                    'checkin_date',  
                    'checkin_time',  
                    'checkin_latitude',  
                    'checkin_longitude',  
                    'checkin_address',  
                    'checkout_date',  
                    'checkout_time',  
                    'checkout_latitude',  
                    'checkout_longitude',  
                    'checkout_address',  
                    'time_interval',  
                    'distance',  
                    'beatscheduleid'  
                )  
                ->orderBy('checkin_date', 'desc')  
                ->orderBy('checkin_time', 'desc')  
                ->first(); // or ->get() for list  

            if (!$checkIn) {  
                return response()->json([  
                    'status'  => 'error',  
                    'message' => 'No check-in found for this entity.',  
                    'data'    => null  
                ], 200);  
            }  

            // Optional: load entity name/type like in getCheckin  
            $entity = null;  
            $name   = null;  
            $type   = null;  

            if ($entityType === 'distributor') {  
                $entity = MasterDistributor::find($entityId);  
                $name   = $entity ? ($entity->trade_name ?? $entity->legal_name ?? 'Unknown Distributor') : null;  
                $type   = $entity ? ($entity->category ?? 'Distributor') : null;  
            } elseif ($entityType === 'secondary_customer') {  
                $entity = SecondaryCustomer::find($entityId);  
                $name   = $entity ? ($entity->shop_name ?? 'Unknown Shop') : null;  
                $type   = $entity ? ($entity->sub_type ?? 'Secondary Customer') : null;  
            } elseif ($entityType === 'customer') {  
                $entity = Customers::with('customertypes')->find($entityId);  
                $name   = $entity ? $entity->name : null;  
                $type   = $entity ? ($entity->customertypes->customertype_name ?? '') : null;  
            }  

            $data = [  
                'checkin_id'         => $checkIn->id,  
                'entity_type'        => $checkIn->entity_type,  
                'entity_id'          => $checkIn->entity_id,  
                'entity_name'        => $name ?? 'Unknown',  
                'entity_type_label'  => $type ?? '',  
                'checkin_date'       => $checkIn->checkin_date,  
                'checkin_time'       => $checkIn->checkin_time,  
                'checkin_latitude'   => $checkIn->checkin_latitude,  
                'checkin_longitude'  => $checkIn->checkin_longitude,  
                'checkin_address'    => $checkIn->checkin_address,  
                'checkout_date'      => $checkIn->checkout_date,  
                'checkout_time'      => $checkIn->checkout_time,  
                'checkout_latitude'  => $checkIn->checkout_latitude,  
                'checkout_longitude' => $checkIn->checkout_longitude,  
                'checkout_address'   => $checkIn->checkout_address,  
                'time_interval'      => $checkIn->time_interval,  
                'distance'           => $checkIn->distance,  
                'beat_schedule_id'   => $checkIn->beatscheduleid ?? 0,  
            ];  

            return response()->json([  
                'status'  => 'success',  
                'message' => 'Check-in details retrieved successfully.',  
                'data'    => $data  
            ], $this->successStatus);  

        } catch (\Exception $e) {  
            return response()->json([  
                'status'  => 'error',  
                'message' => $e->getMessage()  
            ], $this->internalError);  
        }  
    }  
    /**
     * Get details of the currently open/active check-in
     * Route: GET /api/checkin/current or POST /api/checkin/current
     */
    public function getCurrentOpenCheckin(Request $request)
    {
        try {
            $user = $request->user();
    
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated. Please login again.'
                ], 401);
            }
    
            if ($user->active !== 'Y') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Your account is inactive. Contact support.'
                ], 403);
            }
    
            // Fetch the most recent open check-in
            $openCheckin = CheckIn::where('user_id', $user->id)
                ->whereNull('checkout_date')
                ->whereNull('checkout_time')
                ->latest('checkin_date')
                ->latest('checkin_time')
                ->first();
    
            if (!$openCheckin) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'No active check-in found. You are currently checked out.',
                    'has_open_checkin' => false,
                    'open_checkin'     => null
                ], 200);
            }
    
            // Load full entity (customer / distributor / secondary_customer) details
            $entityName = 'Unknown';
            $entityTypeName = '';
            $entityData = null;
    
            switch ($openCheckin->entity_type) {
                case 'distributor':
                    $dist = MasterDistributor::find($openCheckin->entity_id);
                    if ($dist) {
                        $entityName = $dist->trade_name ?? $dist->legal_name ?? 'Distributor';
                        $entityTypeName = $dist->category ?? 'Distributor';
                        $entityData = $dist;
                    }
                    break;
    
                case 'secondary_customer':
                    $sec = SecondaryCustomer::find($openCheckin->entity_id);
                    if ($sec) {
                        $entityName = $sec->shop_name ?? 'Shop';
                        $entityTypeName = $sec->sub_type ?? 'Secondary Customer';
                        $entityData = $sec;
                    }
                    break;
    
                case 'customer':
                    $cust = Customers::find($openCheckin->customer_id ?? $openCheckin->entity_id);
                    if ($cust) {
                        $entityName = $cust->name ?? 'Customer';
                        $entityTypeName = optional($cust->customertypes)->customertype_name ?? 'Customer';
                        $entityData = $cust;
                    }
                    break;
            }
    
            return response()->json([
                'status'            => 'success',
                'message'           => 'Active check-in found',
                'has_open_checkin'  => true,
                'open_checkin'      => [
                    'checkin_id'        => $openCheckin->id,
                    'checkin_date'      => $openCheckin->checkin_date,
                    'checkin_time'      => $openCheckin->checkin_time,
                    'entity_type'       => $openCheckin->entity_type,
                    'entity_id'         => $openCheckin->entity_id,
                    'entity_name'       => $entityName,
                    'entity_type_name'  => $entityTypeName,
                    'checkin_latitude'  => $openCheckin->checkin_latitude,
                    'checkin_longitude' => $openCheckin->checkin_longitude,
                    'checkin_address'   => $openCheckin->checkin_address,
                    'distance'          => $openCheckin->distance,
                    'beatscheduleid'    => $openCheckin->beatscheduleid,
                    // Full entity data (optional - remove if too heavy)
                    'entity_details'    => $entityData
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