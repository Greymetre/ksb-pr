<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use Gate;
use App\Models\User;
use App\Models\Tasks;
use App\Models\UserLiveLocation;
use App\Models\TourProgramme;
use App\Models\{State, District, City, Pincode, Country, Beat};
use App\Models\UserCityAssign;
use App\Models\UserActivity;
use App\Models\Notification;

class UserController extends Controller
{
    public function __construct()
    {

        $this->successStatus = 200;
        $this->created = 201;
        $this->accepted = 202;
        $this->noContent = 204;
        $this->badrequest = 400;
        $this->unauthorized = 401;
        $this->notFound = 404;
        $this->notactive = 406;
        $this->internalError = 500;
    }

    public function getUpcomingTasks(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;
            $pageSize = $request->input('pageSize');
            $query = Tasks::with('customers')
                ->where(function ($query) use ($user_id) {
                    //$query->where('completed', '=', false);
                    $query->where('user_id', '=', $user_id);
                })
                ->orderBy('completed', 'asc')
                ->orderBy('datetime', 'desc')
                ->select('id', 'title', 'descriptions', 'datetime', 'reminder', 'completed_at', 'completed', 'is_done', 'customer_id', 'status_id', 'remark')->latest();
            $data = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->get();
            if ($data->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function createNewTask(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'customer_id'   => 'nullable|exists:customers,id',
                'title'  => "required",
                'descriptions'  => "required",
                'datetime'  => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            if ($task = Tasks::create([
                'user_id' => $userid,
                'title' => isset($request->title) ? $request->title : '',
                'descriptions' => isset($request->descriptions) ? $request->descriptions : '',
                'datetime' => date('Y-m-d H:i:s', strtotime($request->datetime)),
                'reminder' => isset($request->reminder) ? date('Y-m-d H:i:s', strtotime($request->reminder)) : null,
                'completed' => isset($request->completed) ? $request->completed : 0,
                'remark' => isset($request->remark) ? $request->remark : '',
                'customer_id' => isset($request->customer_id) ? $request->customer_id : null,
                'created_by' => $userid,
                'created_at' => date('Y-m-d H:i:s')
            ])) {
                return response()->json(['status' => 'success', 'message' => 'Data inserted successfully.', 'data' => $task], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in No Record Found.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function taskMarkComplite(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'task_id'   => 'required|exists:tasks,id',
                'remark'  => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            if ($task = Tasks::where('id', '=', $request['task_id'])->update([
                'completed' => 1,
                'remark' => isset($request['remark']) ? $request['remark'] : '',
                'completed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ])) {
                return response()->json(['status' => 'success', 'message' => 'Task Completed successfully.'], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in Task Complete'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }
    public function getTaskInfo(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'task_id'   => 'required|exists:tasks,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            if ($task = Tasks::with('customers', 'users')->where('id', '=', $request['task_id'])->select('id', 'user_id', 'title', 'descriptions', 'datetime', 'reminder', 'completed_at', 'completed', 'is_done', 'customer_id', 'status_id', 'remark')->first()) {
                return response()->json(['status' => 'success', 'message' => 'Task Completed successfully.', 'data' => $task], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in Task Complete'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }
    public function updateLiveLocation(Request $request)
    {
        \Log::info('Live location update request received', ['request' => $request->all(), 'User ID' => $request->user()->id]);
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'locations'  => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            $lastLocation = UserLiveLocation::where('userid', $userid)
                ->orderBy('time', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if (is_array($request['locations'])) {
                $collection = array();
                foreach ($request['locations'] as $key => $row) {
                    $locationTime = date('Y-m-d H:i:s', strtotime($row['time']));
                    if (!$this->shouldStoreLiveLocation($lastLocation, $row['latitude'], $row['longitude'], $locationTime)) {
                        continue;
                    }

                    $location = array("active"   =>  "Y", "userid" => $userid, 'latitude' => $row['latitude'], 'longitude' => $row['longitude'], 'time' => $locationTime, 'created_at' => date('Y-m-d H:i:s'));
                    array_push($collection, $location);
                    $lastLocation = (object) $location;
                }
            } else {
                $locationTime = date('Y-m-d H:i:s', strtotime($request['time']));
                $collection = [];
                if ($this->shouldStoreLiveLocation($lastLocation, $request['latitude'], $request['longitude'], $locationTime)) {
                    $collection = array('active'  =>  'Y', 'userid' => $userid, 'latitude' => $request['latitude'], 'longitude' => $request['longitude'], 'time' => $locationTime, 'created_at' => date('Y-m-d H:i:s'));
                }
            }
            if (empty($collection)) {
                return response()->json(['status' => 'success', 'message' => 'Live location skipped. Last location is same or less than 4 minutes old.'], $this->successStatus);
            }

            if (UserLiveLocation::insert($collection)) {
                return response()->json(['status' => 'success', 'message' => 'Data inserted successfully.'], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in No Record Found.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    private function shouldStoreLiveLocation($lastLocation, $latitude, $longitude, $locationTime)
    {
        if (empty($lastLocation)) {
            return true;
        }

        $lastLatitude = (string) $lastLocation->latitude;
        $lastLongitude = (string) $lastLocation->longitude;
        $newLatitude = (string) $latitude;
        $newLongitude = (string) $longitude;

        // if ($lastLatitude === $newLatitude && $lastLongitude === $newLongitude) {
        //     return false;
        // }

        $lastTime = $lastLocation->time ?? $lastLocation->created_at ?? null;
        if (empty($lastTime)) {
            return true;
        }
        return abs(strtotime($locationTime) - strtotime($lastTime)) >= 170;
    }

    public function addTourProgramme(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'programme'  => "required",
                'programme.*.city_id' => 'nullable|exists:cities,id',
                'programme.*.programme_date' => 'required',
                'programme.*.objectives' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            if (is_array($request['programme'])) {
                $collection = array();
                foreach ($request['programme'] as $key => $row) {

                    $lastvisited = DB::table('tour_programmes')
                        ->where('userid', '=', $userid)
                        ->where('city_id', '=', $row['city_id'])
                        ->whereNotNull('visited_date')
                        ->select('visited_date')
                        ->latest()
                        ->first();

                    array_push($collection, array(
                        "userid" => $userid,
                        'city_id' => $row['city_id'],
                        'objectives' => $row['objectives'],
                        'type' => $row['type'],
                        'programme_date' => date('Y-m-d', strtotime($row['programme_date'])),
                        'last_visited' => !empty($lastvisited->visited_date) ? date('Y-m-d', strtotime($lastvisited->visited_date)) : null,
                        'created_at' => date('Y-m-d H:i:s')
                    ));
                }
            }
            if (TourProgramme::insert($collection)) {
                return response()->json(['status' => 'success', 'message' => 'Data inserted successfully.'], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in No Record Found.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }
    public function upcommingTourProgramme(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $pageSize = $request->input('pageSize');
            $filter = $request->input('filter');
            $data = TourProgramme::where(function ($query) use ($user_id, $filter) {
                $query->where('type', '=', '');
                //$query->whereNull('type');
                if (!empty($filter)) {
                    $query->whereDate('date', '=', date('Y-m-d'));
                }
                $query->where('userid', '=', $user_id);
            })
                ->select('id', 'date', 'userid', 'town', 'objectives', 'type', 'status')->latest()->get();
            if ($data->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->update([
            'deleted_at' => now(),
            'active' => 'N',
            'isDeleted' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function userCityList(Request $request)
    {
        try {
            $cityname = $request->input('cityname');
            $user_id = $request->user()->id;
            $cityids = UserCityAssign::where('userid', '=', $user_id)->pluck('city_id')->toArray();
            //$data = City::whereIn('id',$cityids)->select('id','city_name', 'grade')->orderBy('city_name','asc')->get();

            $data = City::whereIn('id', $cityids)->select('id', 'city_name', 'grade');
            if ($cityname) {
                $data->where('city_name', 'LIKE', trim($cityname) . '%');
            }
            $data = $data->orderBy('city_name', 'asc')->get();

            if ($data->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    private function formatActivityCustomer($customer)
    {
        if (!$customer) {
            return null;
        }

        $personName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
        $displayName = $customer->name ?: $personName;

        return [
            'id' => $customer->id,
            'name' => $displayName,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile' => $customer->mobile,
            'contact_number' => $customer->contact_number,
            'email' => $customer->email,
            'profile_image' => $customer->profile_image,
            'shop_image' => $customer->shop_image,
            'customer_code' => $customer->customer_code,
            'customer_type_id' => $customer->customertype,
            'customer_type' => optional($customer->customertypes)->customertype_name,
            'latitude' => $customer->latitude,
            'longitude' => $customer->longitude,
            'address' => optional($customer->customeraddress)->full_address,
        ];
    }

    public function getUserActivity(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $date = $request->input('date') ? $request->input('date') : date('Y-m-d');
            $data = UserActivity::with(['customers.customertypes', 'customers.customeraddress'])->where(function ($query) use ($user_id, $date) {
                $query->whereDate('time', '=', date('Y-m-d', strtotime($date)));
                $query->where('userid', '=', $user_id);
            })->select('id', 'customerid', 'latitude', 'longitude', 'time', 'address', 'description', 'type')->get()
                ->map(function ($activity) {
                    $customer = $this->formatActivityCustomer($activity->customers);

                    $activity->customer_id = $activity->customerid;
                    $activity->customer_name = $customer['name'] ?? null;
                    $activity->customer_mobile = $customer['mobile'] ?? null;
                    $activity->customer_contact_number = $customer['contact_number'] ?? null;
                    $activity->customer_code = $customer['customer_code'] ?? null;
                    $activity->customer_type_id = $customer['customer_type_id'] ?? null;
                    $activity->customer_type = $customer['customer_type'] ?? null;
                    $activity->customer = $customer;

                    return $activity;
                });
            if ($data->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function requestReport(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            return response()->json(['status' => 'success', 'message' => 'Report Accepted'], $this->successStatus);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getNotification(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $date = $request->input('date') ? $request->input('date') : date('Y-m-d');
            $data = Notification::with('users')->select('id', 'type', 'data', 'customer_id', 'user_id', 'created_at')->get();
            if ($data->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function masterStateCity(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $cityids = UserCityAssign::where('userid', '=', $user_id)->pluck('city_id')->toArray();
            $cities = City::whereIn('id', $cityids)->select('id', 'city_name', 'grade', 'district_id')->orderBy('city_name', 'asc')->get();
            $districtids = !empty($cities) ? $cities->pluck('district_id')->toArray() : array();

            $districts = District::where(function ($query) use ($districtids) {
                $query->whereIn('id', $districtids);
            })->select('id', 'district_name', 'state_id')->get();

            $stateids = !empty($districts) ? $districts->pluck('state_id')->toArray() : array();

            $states = State::where(function ($query) use ($stateids) {
                $query->whereIn('id', $stateids);
            })->select('id', 'state_name', 'country_id')->get();
            $countryids = !empty($states) ? $states->pluck('country_id')->toArray() : array();

            $pincodes = Pincode::where(function ($query) use ($cityids) {
                $query->whereIn('city_id', $cityids);
            })
                ->select('id', 'pincode')->get();

            $countries = Country::where(function ($query) use ($countryids) {
                $query->whereIn('id', $countryids);
            })
                ->select('id', 'country_name')->get();
            $data = collect([
                'cities' => $cities,
                'districts' => $districts,
                'states' => $states,
                'pincodes' => $pincodes,
                'countries' => $countries,
            ]);
            if ($data->isNotEmpty()) {
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getPunchinMasterData(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $tours = TourProgramme::where(function ($query) use ($user_id) {
                $query->where('type', '=', '');
                $query->whereDate('date', '=', date('Y-m-d'));
                $query->where('userid', '=', $user_id);
            })
                ->select('id', 'date', 'userid', 'town', 'objectives', 'type', 'status')
                ->latest()->get();

            $cities = City::whereHas('assignusers', function ($query) use ($user_id) {
                $query->where('userid', '=', $user_id);
            })
                ->select('id', 'city_name', 'grade')
                ->orderBy('city_name', 'asc')->get();
            $worktypes = collect([
                collect(["type" => 'Tour', "is_city" => true, "is_beat" => true, 'image' => true, 'summary' => true, 'city_required' => true, 'beat_required' => true]),
                collect(["type" => 'Office Work', "is_city" => true, "is_beat" => false, 'image' => true, 'summary' => true, 'city_required' => true, 'beat_required' => false]),
                collect(["type" => 'Suburban', "is_city" => true, "is_beat" => true, 'image' => true, 'summary' => true, 'city_required' => true, 'beat_required' => true]),
                collect(["type" => 'Central Market', "is_city" => true, "is_beat" => true, 'image' => true, 'summary' => true, 'city_required' => true, 'beat_required' => true]),
                collect(["type" => 'Holiday', "is_city" => false, "is_beat" => false, 'image' => false, 'summary' => false, 'city_required' => false, 'beat_required' => false]),
                collect(["type" => 'Leave', "is_city" => false, "is_beat" => false, 'image' => false, 'summary' => false, 'city_required' => false, 'beat_required' => false]),
            ]);

            $beats = Beat::whereHas('beatusers', function ($query) use ($user_id) {
                $query->where('user_id', '=', $user_id);
                $query->where('active', '=', 'Y');
            })
                ->select('id as beat_id', 'beat_name', 'city_id')
                ->orderBy('city_id', 'asc')
                ->get();

            $data = collect([
                "tours" => $tours,
                "cities" => $cities,
                "worktypes" => $worktypes,
                "beats" => $beats
            ]);
            return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function userDistrictList(Request $request)
    {
        try {
            $districtname = $request->input('districtname');

            // Get target user_id: prefer query param, fallback to authenticated user
            $targetUserId = $request->query('user_id')
                ? $request->query('user_id')
                : $request->user()->id;

            // Optional: Add permission check (very recommended!)
            // Example: only allow if current user is admin or viewing own data
            if ($targetUserId != $request->user()->id && !Gate::allows('view-other-users-data')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized to view other user\'s data'
                ], 403);
            }

            // Get assigned cities for the target user
            $cityIds = UserCityAssign::where('userid', $targetUserId)
                ->pluck('city_id')
                ->toArray();

            // Get unique districts from those cities
            $districtIds = City::whereIn('id', $cityIds)
                ->pluck('district_id')
                ->unique()
                ->filter() // remove nulls if any
                ->toArray();

            $query = District::whereIn('id', $districtIds)
                ->select('id', 'district_name', 'state_id');

            if ($districtname) {
                $query->where('district_name', 'LIKE', trim($districtname) . '%');
            }

            $data = $query->orderBy('district_name', 'asc')->get();

            if ($data->isNotEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data retrieved successfully.',
                    'data'    => $data
                ], $this->successStatus);
            }

            return response([
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
    public function userCitiesByDistrict(Request $request)
    {
        try {
            $districtId = $request->query('district_id');
            $cityname   = $request->input('cityname'); // optional search

            if (!$districtId) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'district_id is required'
                ], $this->badrequest);
            }

            // Determine target user
            $targetUserId = $request->query('user_id')
                ? $request->query('user_id')
                : $request->user()->id;

            // Optional: permission check (recommended)
            if ($targetUserId != $request->user()->id && !Gate::allows('view-other-users-data')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized to view other user\'s data'
                ], 403);
            }

            // Get assigned city IDs for the target user
            $assignedCityIds = UserCityAssign::where('userid', $targetUserId)
                ->pluck('city_id')
                ->toArray();

            // Build query: cities in the given district + assigned to user
            $query = City::where('district_id', $districtId)
                ->whereIn('id', $assignedCityIds)
                ->select('id', 'city_name', 'grade');

            if ($cityname) {
                $query->where('city_name', 'LIKE', trim($cityname) . '%');
            }

            $data = $query->orderBy('city_name', 'asc')->get();

            if ($data->isNotEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Cities retrieved successfully.',
                    'data'    => $data
                ], $this->successStatus);
            }

            return response([
                'status'  => 'error',
                'message' => 'No cities found in this district for the user.',
                'data'    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }
}
