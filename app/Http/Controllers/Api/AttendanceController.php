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
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\TourProgramme;
use App\Models\BeatSchedule;
use App\Models\Beat;
use App\Models\CompOffLeave;
use App\Models\Holiday;
use App\Models\TourDetail;
use App\Models\User;
use App\Models\Division;
use App\Models\SalesTargetUsers;
use App\Models\SecondaryCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->attendances = new Attendance();
        $this->successStatus = 200;
        $this->created = 201;
        $this->accepted = 202;
        $this->noContent = 204;
        $this->badrequest = 400;
        $this->unauthorized = 401;
        $this->notFound = 404;
        $this->notactive = 406;
        $this->internalError = 500;
        $this->path = 'attendances';
    }

    private function getZoneSortOrder($zoneName)
    {
        $zoneOrder = ['north', 'east', 'west', 'south'];
        $zoneName = strtolower((string) $zoneName);

        foreach ($zoneOrder as $index => $zone) {
            if (strpos($zoneName, $zone) !== false) {
                return $index;
            }
        }

        return count($zoneOrder);
    }

    private function sortZoneBuckets(array $zones)
    {
        uksort($zones, function ($firstZone, $secondZone) {
            $orderComparison = $this->getZoneSortOrder($firstZone) <=> $this->getZoneSortOrder($secondZone);

            return $orderComparison ?: strcasecmp($firstZone, $secondZone);
        });

        return $zones;
    }

    private function sortZoneList(array $zones)
    {
        usort($zones, function ($firstZone, $secondZone) {
            $firstName = $firstZone['name'] ?? $firstZone['zone'] ?? '';
            $secondName = $secondZone['name'] ?? $secondZone['zone'] ?? '';
            $orderComparison = $this->getZoneSortOrder($firstName) <=> $this->getZoneSortOrder($secondName);

            return $orderComparison ?: strcasecmp($firstName, $secondName);
        });

        return $zones;
    }

    public function getPunchin(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;
            $pageSize = $request->input('pageSize');
            $query = $this->attendances
                ->where(function ($query) use ($user_id) {
                    $query->where('user_id', '=', $user_id);
                })
                ->select(
                    'id',
                    'punchin_date',
                    'punchin_time',
                    'punchin_longitude',
                    'punchin_latitude',
                    'punchin_address',
                    'punchin_image',
                    'punchout_date',
                    'punchout_time',
                    'punchout_latitude',
                    'punchout_longitude',
                    'punchout_address',
                    'flag',
                    'punchout_image',
                    'working_type'
                )
                ->orderBy('punchin_date', 'desc')
                ->whereDate('punchin_date', Carbon::today());
            $db_data = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->get();
            $data = collect([]);
            if ($db_data->isNotEmpty()) {
                foreach ($db_data as $key => $value) {
                    $data->push([
                        'punchin_id' => !empty($value['id']) ? $value['id'] : 0,
                        'punchin_date' => !empty($value['punchin_date']) ? $value['punchin_date'] : '',
                        'punchin_time' => !empty($value['punchin_time']) ? $value['punchin_time'] : '',
                        'punchin_longitude' => !empty($value['punchin_longitude']) ? $value['punchin_longitude'] : '',
                        'punchin_latitude' => !empty($value['punchin_latitude']) ? $value['punchin_latitude'] : '',
                        'punchin_address' => !empty($value['punchin_address']) ? $value['punchin_address'] : '',
                        'punchin_image' => !empty($value['punchin_image']) ? $value['punchin_image'] : '',
                        'punchout_date' => !empty($value['punchout_date']) ? $value['punchout_date'] : '',
                        'punchout_time' => !empty($value['punchout_time']) ? $value['punchout_time'] : '',
                        'punchout_latitude' => !empty($value['punchout_latitude']) ? $value['punchout_latitude'] : '',
                        'punchout_longitude' => !empty($value['punchout_longitude']) ? $value['punchout_longitude'] : '',
                        'punchout_address' => !empty($value['punchout_address']) ? $value['punchout_address'] : '',
                        'punchout_image' => !empty($value['punchout_image']) ? $value['punchout_image'] : '',
                        'punchin_flag' => !empty($value['flag']) ? true : false,
                        'working_type' => !empty($value['working_type']) ? $value['working_type'] : '',
                    ]);
                }
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    /**
     * Format tour details using comma separated tour IDs
     */
    private function getFormattedTourDetails($tourIdString)
    {
        if (empty($tourIdString)) {
            return [];
        }

        $tourIds = array_filter(explode(',', $tourIdString));

        $tourDetails = \App\Models\TourProgramme::whereIn('id', $tourIds)->get();

        if ($tourDetails->isEmpty()) {
            return [];
        }

        $townIds = $tourDetails->pluck('town')->unique()->filter();
        $districtIds = $tourDetails->pluck('district')->unique()->filter();

        $cities = \App\Models\City::whereIn('id', $townIds)
            ->pluck('city_name', 'id');

        $districts = \App\Models\District::whereIn('id', $districtIds)
            ->pluck('district_name', 'id');

        return $tourDetails->map(function ($item) use ($cities, $districts) {
            return [
                'id' => $item->id,
                'town_name' => $cities[$item->town] ?? '',
                'district_name' => $districts[$item->district] ?? '',
                'objective'     => $item->objectives ?? '',
            ];
        })->values()->toArray();
    }

    public function userPunchin(Request $request)
    {
        try {
            $user = $request->user();
            $validator = Validator::make($request->all(), [
                'punchin_latitude' => 'required',
                'punchin_longitude' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            if ($request->file('image')) {
                $image = $request->file('image');
                // $filename = 'punchin_'.autoIncrementId('Attendance', 'id');
                $filename = 'punchin';
                $request['punchin_image'] = fileupload($image, $this->path, $filename);
            }
            $punchin_date = getcurentDate();
            $request['punchin_date'] = $punchin_date;
            $branchIds = explode(',', $user->branch_id);

            $punchinDate = Carbon::parse($request['punchin_date'])->format('Y-m-d');
            $isSunday = Carbon::parse($request['punchin_date'])->isSunday();
            $holidayDates = Holiday::whereIn('branch', $branchIds)
                ->pluck('holiday_date')
                ->map(function ($dateString) {
                    return explode(',', $dateString);
                })
                ->collapse()
                ->map('trim')
                ->toArray();

            $isHoliday = in_array($punchinDate, $holidayDates);

            if ($isSunday || $isHoliday) {
                $expiryDate = Carbon::parse($request['punchin_date'])->addDays(60);

                CompOffLeave::create([
                    'user_id' => $user->id,
                    'comp_off_date' => $punchinDate,
                    'expiry_date' => $expiryDate,
                    'is_used' => false,
                ]);
            }
            // $request['punchin_address'] = getLatLongToAddress($request['punchin_latitude'],$request['punchin_longitude']);
            $request['punchin_address'] = getLatLongToAddress($request['punchin_longitude'], $request['punchin_latitude']);
            // dd($request['punchin_address']);
            //$request['punchin_address'] = '';
            if ($punchin = $this->attendances->updateOrCreate([
                'user_id' => $user->id,
                'punchin_date' => $punchin_date
            ], [
                'active' => 'Y',
                'flag' => 'true',
                'user_id' => $user->id,
                'punchin_date' => $punchin_date,
                'punchin_time' => getcurentTime(),
                'tourid' => !empty($request['tourid']) ? $request['tourid'] : null,
                // 'punchin_longitude' => !empty($request['punchin_longitude']) ? $request['punchin_longitude'] :'',
                // 'punchin_latitude' => !empty($request['punchin_latitude']) ? $request['punchin_latitude'] :'',
                'city' => !empty($request['city']) ? trim($request['city']) : null,
                'punchin_longitude' => !empty($request['punchin_latitude']) ? $request['punchin_latitude'] : '',
                'punchin_latitude' => !empty($request['punchin_longitude']) ? $request['punchin_longitude'] : '',
                'punchin_address' => !empty($request['punchin_address']) ? $request['punchin_address'] : '',
                'punchin_image' => !empty($request['punchin_image']) ? $request['punchin_image'] : '',
                'punchin_summary' => !empty($request['punchin_summary']) ? $request['punchin_summary'] : '',
                'working_type' => !empty($request['type']) ? $request['type'] : '',
                'punchin_from' => 'App',
                'created_at' => getcurentDateTime(),
            ])) {
                $punchindata = $this->attendances->where('id', $punchin->id)->select('active', 'user_id', 'punchin_date', 'punchin_time', 'punchin_longitude', 'punchin_latitude', 'punchin_address', 'punchin_image')->first();
                // $useractivity = array(
                //         'userid' => $user->id, 
                //         'latitude' => $request['punchin_latitude'], 
                //         'longitude' => $request['punchin_longitude'], 
                //         'type' => 'Punchin',
                //         'description' => 'User Login',
                //     );
                // submitUserActivity($useractivity);
                if (!empty($request['beats']) && $request['beats'] != '') {
                    $this->attendances->where('id', $punchin->id)->update(['beat_id' => $request['beats']]);
                    $collection = array();
                    $beats = explode(',', $request['beats']);
                    if (!empty($beats)) {
                        foreach ($beats as $key => $beat) {
                            array_push($collection, array(
                                "user_id" => $user->id,
                                'beat_id' => $beat,
                                'tourid' => $request['tourid'],
                                'beat_date' => date('Y-m-d'),
                                'created_at' => date('Y-m-d H:i:s')
                            ));
                        }
                        BeatSchedule::insert($collection);
                    }
                }
                if (!empty($request['tourid'])) {
                    $tourIds = explode(',', $request['tourid']); // convert to ar
                    TourProgramme::whereIn('id', $tourIds)->update([
                        'type' => !empty($request['type']) ? $request['type'] : ''
                    ]);

                    $cityids = Beat::whereHas('beatschedules', function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                        $query->whereDate('beat_date', '=', date('Y-m-d'));
                    })
                        ->orderBy('city_id', 'asc')
                        ->pluck('city_id');
                    $cityids = $cityids->unique();


                    /*  foreach ($cityids as $key => $city) {
                        $updatecity = TourDetail::where('tourid','=',$request['tourid'])->whereNull('visited_cityid')->first();
                        if(!empty($updatecity))
                        {
                            $updatecity->update([
                                'visited_cityid' => $city,
                                 'visited_date' => date('Y-m-d'),
                            ]);
                        }
                        else
                        {
                            TourDetail::create([
                                'tourid' => $request['tourid'],
                                'city_id' => null, 
                                'visited_cityid' => $city,
                                'visited_date' => date('Y-m-d'),
                                'last_visited' => date('Y-m-d'),
                            ]); 
                        }
                    }*/

                    //start new

                    if (!empty($request['city'])) {

                        $city_datas = explode(",", $request['city']);
                        foreach ($city_datas as $key => $city) {
                            $updatecity = TourDetail::where('tourid', '=', $request['tourid'])->whereNull('visited_cityid')->first();
                            if (!empty($updatecity)) {
                                $updatecity->update([
                                    'visited_cityid' => $city,
                                    'visited_date' => date('Y-m-d'),
                                ]);
                            } else {
                                TourDetail::create([
                                    'tourid' => $request['tourid'],
                                    'city_id' => null,
                                    'visited_cityid' => $city,
                                    'visited_date' => date('Y-m-d'),
                                    'last_visited' => date('Y-m-d'),
                                ]);
                            }
                        }
                    }

                    ///end






                }
                // $zsmnotify = collect([
                //     'title' => 'Successfully punched in',
                //     'body' =>  $user->name.' has Punched in'
                // ]);
                // sendNotification($user->reportingid,$zsmnotify);
                // $asmnotify = collect([
                //     'title' => 'Successfully punched in',
                //     'body' =>  'You have successfully Punched in'
                // ]);
                // sendNotification($user->id,$asmnotify);
                return response()->json(['status' => 'success', 'message' => 'Punch In successfully', 'punchin_id' => $punchin->id, 'punchin' => $punchindata], $this->successStatus);
            }
            return response()->json(['status' => 'error', 'message' => 'Error in Check In'], $this->badrequest);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function userPunchout(Request $request)
    {
        try {

            $user = $request->user();
            $validator = Validator::make($request->all(), [
                'punchin_id' => 'required|exists:attendances,id',
                'punchout_longitude' => 'required',
                'punchout_latitude' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }
            if ($request->file('image')) {
                $image = $request->file('image');
                $filename = 'punchout_' . $request['punchin_id'];
                $request['punchout_image'] = fileupload($image, $this->path, $filename);
            }
            $punchindetails = Attendance::where('id', $request->punchin_id)->where('user_id', $user->id)->first();
            if ($punchindetails->working_type == 'Second Half Leave') {
                $punchout_time = '14:00:00';
            } else {
                $punchout_time = getcurentTime();
            }
            $request['punchout_address'] = getLatLongToAddress($request['punchout_longitude'], $request['punchout_latitude']);
            $punchout = Attendance::where('id', $request->punchin_id)->where('user_id', $user->id)->first();
            $punchout->punchout_date = getcurentDate();
            $punchout->punchout_time = $punchout_time;
            $punchout->punchout_latitude = !empty($request['punchout_latitude']) ? $request['punchout_latitude'] : null;
            $punchout->punchout_longitude = !empty($request['punchout_longitude']) ? $request['punchout_longitude'] : null;
            $punchout->punchout_address = !empty($request['punchout_address']) ? $request['punchout_address'] : '';
            $punchout->punchout_image = !empty($request['punchout_image']) ? $request['punchout_image'] : '';
            $punchout->punchout_summary = !empty($request['punchout_summary']) ? $request['punchout_summary'] : '';
            $punchout->worked_time = gmdate("H:i:s", strtotime(getcurentDateTime()) - strtotime($punchout->punchin_date . ' ' . $punchout->punchin_time));
            if ($punchout->save()) {
                // $useractivity = array(
                //         'userid' => $user->id, 
                //         'latitude' => $request['punchout_latitude'], 
                //         'longitude' => $request['punchout_longitude'], 
                //         'type' => 'Punchout',
                //         'description' => 'User Logout',
                //     );
                // submitUserActivity($useractivity);
                // $zsmnotify = collect([
                //     'title' => 'Successfully punched out',
                //     'body' =>  $user->name.' has Punched out'
                // ]);
                // sendNotification($user->reportingid,$zsmnotify);
                // $asmnotify = collect([
                //     'title' => 'Successfully punched out',
                //     'body' =>  'You have successfully Punched out'
                // ]);
                // sendNotification($user->id,$asmnotify);
                return response()->json(['status' => 'success', 'message' => 'Punch Out successfully', 'punchout' => $punchout], $this->successStatus);
            }
            return response()->json(['status' => 'error', 'message' => 'Error in Punch Out'], $this->badrequest);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getAllUserPunchInOut(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;

            $pageSize        = $request->input('pageSize');
            $search_name     = $request->input('search_name');
            $search_branches = $request->input('search_branches');
            $designation     = $request->input('designation');
            $zone            = $request->input('zone');
            $zone_id         = $request->input('zone_id');
            $branch          = $request->input('branch');
            $branch_id       = $request->input('branch_id');
            $start_date      = $request->input('start_date');
            $end_date        = $request->input('end_date');
            $filterType      = $request->input('type');
            $normalizeIds = function ($value) {
                $ids = is_array($value) ? $value : explode(',', (string) $value);

                return array_values(array_filter(array_map('trim', $ids), fn($id) => $id !== ''));
            };
            $search_branches = $normalizeIds($search_branches);
            $designationIds = $normalizeIds($designation);
            $branchIds = $normalizeIds($branch_id);

            $validator = Validator::make($request->all(), [
                'end_date' => 'required_with:start_date|date',
                'start_date' => 'nullable|date',
                'designation' => 'nullable',
                'zone' => 'nullable|string',
                'zone_id' => 'nullable',
                'branch' => 'nullable|string',
                'branch_id' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
            }

            // Get reporting users
            if ($search_name && $search_name != '') {
                $all_reporting_user_ids = [$search_name];
            } else {
                $all_reporting_user_ids = getUsersReportingToAuth($user_id);
            }

            if (!empty($designationIds)) {
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->whereIn('designation_id', $designationIds)
                    ->pluck('id')
                    ->toArray();
            }

            if (!empty($zone_id)) {
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->where('division_id', $zone_id)
                    ->pluck('id')
                    ->toArray();
            } elseif (!empty($zone)) {
                $zoneIds = Division::where('division_name', 'LIKE', "%{$zone}%")->pluck('id')->toArray();
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->whereIn('division_id', $zoneIds)
                    ->pluck('id')
                    ->toArray();
            }

            if (!empty($branchIds)) {
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->where(function ($q) use ($branchIds) {
                        $q->whereIn('branch_id', $branchIds);
                        foreach ($branchIds as $branchId) {
                            $q->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                        }
                    })
                    ->pluck('id')
                    ->toArray();
            } elseif (!empty($branch)) {
                $branchNameIds = Branch::where('branch_name', 'LIKE', "%{$branch}%")->pluck('id')->toArray();
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->where(function ($q) use ($branchNameIds) {
                        $q->whereIn('branch_id', $branchNameIds);
                        foreach ($branchNameIds as $branchId) {
                            $q->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                        }
                    })
                    ->pluck('id')
                    ->toArray();
            }

            // Branch logic
            $all_user_branches = User::with('getbranch')
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('id', config('constants.customer_roles'));
                })
                ->whereIn('id', getUsersReportingToAuth($user_id))
                ->orderBy('branch_id')
                ->get();

            $branches = [];
            $all_branch = [];
            $bkey = 0;
            foreach ($all_user_branches as $val) {
                if ($val->getbranch && !in_array($val->getbranch->id, $all_branch)) {
                    $all_branch[] = $val->getbranch->id;
                    $branches[$bkey]['id'] = $val->getbranch->id;
                    $branches[$bkey]['name'] = $val->getbranch->branch_name;
                    $bkey++;
                }
            }

            if (!empty($search_branches)) {
                $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)
                    ->where(function ($q) use ($search_branches) {
                        $q->whereIn('branch_id', $search_branches);
                        foreach ($search_branches as $branchId) {
                            $q->orWhereRaw('FIND_IN_SET(?, branch_id)', [$branchId]);
                        }
                    })
                    ->whereDoesntHave('roles', function ($q) {
                        $q->whereIn('id', config('constants.customer_roles'));
                    })
                    ->pluck('id')
                    ->toArray();
            }

            // Pre-calculate hierarchy levels
            $hierarchyLevels = [];
            foreach ($all_reporting_user_ids as $uid) {
                $hierarchyLevels[$uid] = getHierarchyLevel($uid, $user_id);
            }

            // Main Query
            $all_punch_in_out = Attendance::with('users')
                ->whereIn('user_id', $all_reporting_user_ids);

            // Date filter
            if ($start_date && $start_date != '' && $start_date != null) {
                $start_date = date('Y-m-d', strtotime($start_date));
                $end_date   = date('Y-m-d', strtotime($end_date));
                $all_punch_in_out->whereBetween('punchin_date', [$start_date, $end_date]);
            }

            // Leave / Normal filter
            $leaveTypes = ['Full Day Leave', 'First Half Leave', 'Second Half Leave'];
            if ($filterType === 'leave') {
                $all_punch_in_out->whereIn('working_type', $leaveTypes);
            } elseif ($filterType === 'normal') {
                $all_punch_in_out->where(function ($q) use ($leaveTypes) {
                    $q->whereNotIn('working_type', $leaveTypes)
                        ->orWhereNull('working_type');
                });
            }

            $all_punch_in_out->orderBy('punchin_date', 'desc');

            if ($request->status != null) {
                $all_punch_in_out->where('attendance_status', $request->status);
            }

            $all_punch_in_out = !empty($pageSize)
                ? $all_punch_in_out->paginate($pageSize)
                : $all_punch_in_out->paginate(100);

            // Users list
            $all_user_details = User::with('getbranch')
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('id', 29);
                })
                ->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('id', config('constants.customer_roles'));
                })
                ->whereIn('id', $all_reporting_user_ids)
                ->orderBy('name', 'asc')
                ->get();

            $all_users = [];
            foreach ($all_user_details as $k => $val) {
                $all_users[$k]['id']   = $val->id;
                $all_users[$k]['name'] = $val->name;
            }

            // Build Response Data - FIXED VERSION
            $data = [];
            foreach ($all_punch_in_out as $key => $checkIn) {
                $attendanceUser = $checkIn->users;           // Get the related user
                $userId         = $attendanceUser ? $attendanceUser->id : null;

                $hierarchyLevel = $userId ? ($hierarchyLevels[$userId] ?? -1) : -1;

                $data[$key] = [
                    'attendance_id'    => $checkIn->id,
                    'name'             => $attendanceUser ? $attendanceUser->name : 'N/A',
                    'date'             => date('d/m/Y', strtotime($checkIn->punchin_date)),
                    'punch_in'         => $checkIn->punchin_time ?? '',
                    'punch_out'        => $checkIn->punchout_time ?? '',
                    'working_type'     => $checkIn->working_type ?? '',
                    'status'           => match ($checkIn->attendance_status) {
                        1 => 'Approve',
                        2 => 'Rejected',
                        default => 'Pending'
                    },
                    'self'             => ($userId == $user_id),
                    'hierarchy_level'  => $hierarchyLevel,
                    'hierarchy_label'  => match ($hierarchyLevel) {
                        0   => 'Self',
                        -1  => 'Not in Hierarchy',
                        default => 'Level ' . $hierarchyLevel
                    }
                ];
            }

            $all_status = [
                ['id' => '0', 'name' => 'Pending'],
                ['id' => '1', 'name' => 'Approved'],
                ['id' => '2', 'name' => 'Rejected']
            ];

            return response()->json([
                'status'      => 'success',
                'message'     => count($data) > 0 ? 'Data retrieved successfully.' : 'No Record Found.',
                'users'       => $all_users,
                'branches'    => $branches,
                'page_count'  => $all_punch_in_out->lastPage(),
                'all_status'  => $all_status,
                'data'        => $data
            ], count($data) > 0 ? $this->successStatus : $this->badrequest);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;
            $status = $request->input('status');
            $remark_status = $request->input('remark_status');
            $attendance_id = $request->input('attendance_id');

            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'attendance_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], 400);
            }
            if ($status == '2') {
                $validator = Validator::make($request->all(), [
                    'remark_status' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json(['status' => 'error', 'message' =>  'If you want to reject the attendance please add a remark.'], 400);
                }
            }

            $ids = explode(',', $attendance_id);

            foreach ($ids as $key => $value) {
                Attendance::where('id', '=', $value)->update([
                    'attendance_status' => $status,
                    'approve_reject_by' => $user_id,
                    'remark_status' => $request->input('remark_status')
                ]);
            }
            return response()->json(['status' => 'success', 'message' => 'Status changed successfully.'], $this->successStatus);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function showAttendance(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>  $validator->errors()], 400);
        }

        $attendance_id = $request->input('attendance_id');
        $attendance = Attendance::with('users')->find($attendance_id);


        if ($attendance) {

            $tourDetails = $this->getFormattedTourDetails($attendance->tourid);
            // ✅ STEP 1: Convert comma string to array
            $cityIds = [];
            if (!empty($attendance->city)) {
                $cityIds = explode(',', $attendance->city);
            }

            // ✅ STEP 2: Fetch cities from DB
            $cities = \App\Models\City::whereIn('id', $cityIds)
                ->pluck('city_name', 'id'); // [id => name]

            // ✅ STEP 3: Maintain order + build array
            $cityNamesArray = [];
            foreach ($cityIds as $id) {
                if (isset($cities[$id])) {
                    $cityNamesArray[] = $cities[$id];
                }
            }

            // ✅ STEP 4: Convert to comma separated string
            $cityNamesString = implode(', ', $cityNamesArray);
            // response
            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully.',
                'data' => $attendance,
                'tour_details' => $tourDetails,
                'city_names_string' => $cityNamesString // comma separated
            ], $this->successStatus);
        } else {
            return response([
                'status' => 'error',
                'message' => 'No Record Found.'
            ], $this->badrequest);
        }
    }

    public function getTodayMyTeamAttendanceSummary(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;
            $today = Carbon::today()->format('Y-m-d');
            $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd   = Carbon::now()->endOfMonth()->format('Y-m-d');
            $currentYearStart  = Carbon::now()->startOfYear()->format('Y-m-d');
            $currentYearEnd    = Carbon::now()->endOfYear()->format('Y-m-d');

            $myTeamUserIds = getUsersReportingToAuth($user_id);

            if (empty($myTeamUserIds)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No team members found.',
                    'data' => [
                        'total_users' => 0,
                        'total_punch_in' => 0,
                        'total_not_punch_in' => 0,
                        'asr' => ['total' => 0, 'checked_in_today' => 0, 'not_checked_in_today' => 0],
                        'dsr' => ['total' => 0, 'checked_in_today' => 0, 'not_checked_in_today' => 0],
                        'today_orders' => ['quantity' => 0, 'value' => 0],
                        'current_month_orders' => ['quantity' => 0, 'value' => 0],
                        'today_orders_dsr' => ['quantity' => 0, 'value' => 0],
                        'current_month_orders_dsr' => ['quantity' => 0, 'value' => 0],
                        'unique_buyers_from_asr' => 0,
                        'total_unique_buyers_current_year' => 0,
                        'punchout_remaining_asr_today' => 0,
                        'punchout_remaining_dsr_today' => 0,
                        'secondary_customers_registered_approved_today' => 0,
                        'secondary_customers_registered_approved_current_year' => 0,
                        'secondary_customers_with_order_current_year' => 0,
                        'total_orders_current_year' => 0,
                        'total_order_quantity_current_year' => 0,
                        'total_order_value_current_year' => 0,
                        'top_5_products' => [],
                        'top_5_products_total' => ['quantity' => 0, 'value' => 0],
                        'top_5_products_current_month' => [],
                        'top_5_products_current_year' => [],
                        'top_5_products_total_current_month' => ['quantity' => 0, 'value' => 0],
                        'top_5_products_total_current_year' => ['quantity' => 0, 'value' => 0],
                        'working_type_asr_today' => ['retailer_visit' => 0, 'nukkad_meet' => 0, 'field_demo' => 0, 'other' => 0],
                        'working_type_asr_current_month' => ['retailer_visit' => 0, 'nukkad_meet' => 0, 'field_demo' => 0, 'other' => 0],
                        'working_type_asr_current_year' => ['retailer_visit' => 0, 'nukkad_meet' => 0, 'field_demo' => 0, 'other' => 0],
                        'working_type_dsr_today' => ['retailer_visit' => 0, 'nukkad_meet' => 0, 'field_demo' => 0, 'other' => 0],
                        'working_type_dsr_current_month' => ['retailer_visit' => 0, 'nukkad_meet' => 0, 'field_demo' => 0, 'other' => 0],
                        'working_type_dsr_current_year' => ['retailer_visit' => 0, 'nukkad_meet' => 0, 'field_demo' => 0, 'other' => 0]
                    ]
                ], $this->successStatus);
            }

            $totalUsers = count($myTeamUserIds);

            // ===================== Attendance Summary =====================
            $totalPunchInToday = Attendance::whereIn('user_id', $myTeamUserIds)
                ->whereDate('punchin_date', $today)
                ->whereNotNull('punchin_time')
                ->distinct('user_id')
                ->count('user_id');



            $totalNotPunchInToday = $totalUsers - $totalPunchInToday;

            $totalAsr = User::whereIn('id', $myTeamUserIds)->whereDoesntHave('roles', function ($q) {
                $q->whereIn('id', config('constants.customer_roles'));
            })->where('designation_id', 3)->count();
            $totalDsr = User::whereIn('id', $myTeamUserIds)->whereDoesntHave('roles', function ($q) {
                $q->whereIn('id', config('constants.customer_roles'));
            })->where('designation_id', 6)->count();

            $asrUserIds = User::whereIn('id', $myTeamUserIds)->whereDoesntHave('roles', function ($q) {
                $q->whereIn('id', config('constants.customer_roles'));
            })->where('designation_id', 3)->pluck('id')->toArray();
            $dsrUserIds = User::whereIn('id', $myTeamUserIds)->whereDoesntHave('roles', function ($q) {
                $q->whereIn('id', config('constants.customer_roles'));
            })->where('designation_id', 6)->pluck('id')->toArray();

            $currentMonthName = Carbon::now()->format('M'); // Apr
            $currentYear = Carbon::now()->year;
            // ===================== LEAVE COUNT (ASR) =====================

            $leaveAsrToday = Attendance::whereIn('user_id', $asrUserIds)
                ->whereDate('punchin_date', $today)
                ->whereNotNull('working_type')
                ->select(
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN working_type = 'Full Day Leave' THEN 1
                                WHEN working_type IN ('First Half Leave', 'Second Half Leave') THEN 0.5
                                ELSE 0
                            END
                        ) as total_leave
                    ")
                )->first();
            $leaveDsrToday = Attendance::whereIn('user_id', $dsrUserIds)
                ->whereDate('punchin_date', $today)
                ->select(DB::raw("
                    SUM(
                        CASE 
                            WHEN working_type = 'Full Day Leave' THEN 1
                            WHEN working_type IN ('First Half Leave', 'Second Half Leave') THEN 0.5
                            ELSE 0
                        END
                    ) as total_leave
                "))->first();
            // ===================== TARGET SUMMARY =====================

            // ASR Target
            $asrTargetData = SalesTargetUsers::whereIn('user_id', $asrUserIds)
                ->where('type', 'secondary') // ✅ FIXED
                ->where('month', $currentMonthName) // ✅ FIXED
                ->where('year', $currentYear)
                ->select(
                    DB::raw('COALESCE(SUM(target),0) as total_target'),
                    DB::raw('COALESCE(SUM(achievement),0) as total_achievement')
                )
                ->first();

            $asrQtyTargetData = SalesTargetUsers::whereIn('user_id', $asrUserIds)
                ->where('type', 'secondary') // ✅ FIXED
                ->where('month', $currentMonthName) // ✅ FIXED
                ->where('year', $currentYear)
                ->select(
                    DB::raw('COALESCE(SUM(qunatity_target),0) as total_qty_target'),
                    DB::raw('COALESCE(SUM(qunatity_achievement),0) as total_qty_achievement')
                )
                ->first();

            // DSR Target
            $dsrTargetData = SalesTargetUsers::whereIn('user_id', $dsrUserIds)
                ->where('type', 'secondary') // ✅ FIXED
                ->where('month', $currentMonthName) // ✅ FIXED
                ->where('year', $currentYear)
                ->select(
                    DB::raw('COALESCE(SUM(target),0) as total_target'),
                    DB::raw('COALESCE(SUM(achievement),0) as total_achievement')
                )
                ->first();
            // DSR Target
            $dsrQtyTargetData = SalesTargetUsers::whereIn('user_id', $dsrUserIds)
                ->where('type', 'secondary') // ✅ FIXED
                ->where('month', $currentMonthName) // ✅ FIXED
                ->where('year', $currentYear)
                ->select(
                    DB::raw('COALESCE(SUM(qunatity_target),0) as total_qty_target'),
                    DB::raw('COALESCE(SUM(qunatity_achievement),0) as total_qty_achievement')
                )
                ->first();

            // Calculate %
            $asrAchievementPercent = $asrTargetData->total_target > 0
                ? round(($asrTargetData->total_achievement / $asrTargetData->total_target) * 100, 2)
                : 0;
            $asrQtyAchievementPercent = ($asrQtyTargetData->total_qty_target ?? 0) > 0
                ? round(($asrQtyTargetData->total_qty_achievement / $asrQtyTargetData->total_qty_target) * 100, 2)
                : 0;

            $dsrAchievementPercent = $dsrTargetData->total_target > 0
                ? round(($dsrTargetData->total_achievement / $dsrTargetData->total_target) * 100, 2)
                : 0;
            $dsrQtyAchievementPercent = ($dsrQtyTargetData->total_qty_target ?? 0) > 0
                ? round(($dsrQtyTargetData->total_qty_achievement / $dsrQtyTargetData->total_qty_target) * 100, 2)
                : 0;

            $checkedInUserIds = Attendance::whereIn('user_id', $myTeamUserIds)
                ->whereDate('punchin_date', $today)
                ->whereNotNull('punchin_time')
                ->pluck('user_id')->toArray();

            $asrCheckedIn = count(array_intersect($asrUserIds, $checkedInUserIds));
            $asrNotCheckedIn = $totalAsr - $asrCheckedIn;

            $dsrCheckedIn = count(array_intersect($dsrUserIds, $checkedInUserIds));
            $dsrNotCheckedIn = $totalDsr - $dsrCheckedIn;

            // ===================== Punchout Remaining Today (ASR & DSR) =====================
            // Users who punched in today but have NOT punched out yet
            $punchoutRemainingAsr = Attendance::whereIn('user_id', $asrUserIds)
                ->whereDate('punchin_date', $today)
                ->whereNotNull('punchin_time')
                ->whereNull('punchout_time')           // Punchout not done
                ->count();

            $punchoutRemainingDsr = Attendance::whereIn('user_id', $dsrUserIds)
                ->whereDate('punchin_date', $today)
                ->whereNotNull('punchin_time')
                ->whereNull('punchout_time')
                ->count();

            // ===================== Secondary Customer Summary =====================
            $visibleUserIds = array_merge([$user_id], $myTeamUserIds); // You + All assigned users

            // 1. Secondary Customers registered AND approved today
            $secondaryRegisteredApprovedToday = SecondaryCustomer::whereIn('created_by', $visibleUserIds)
                ->whereDate('created_at', $today)
                ->where('status', 'approved')        // Adjust if your approved status is different
                ->where('active', 'Y')
                ->count();

            // 1.5 Registered & Approved in CURRENT YEAR (New as per your request)
            $secondaryRegisteredApprovedCurrentYear = SecondaryCustomer::whereIn('created_by', $visibleUserIds)
                ->whereBetween('created_at', [$currentYearStart, $currentYearEnd])
                ->where('status', 'approved')
                ->where('active', 'Y')
                ->count();

            // 2. Unique Secondary Customers who gave at least one order in current year
            $secondaryWithOrderCurrentYear = SecondaryCustomer::whereHas('orders', function ($q) use ($currentYearStart, $currentYearEnd) {
                $q->whereBetween('order_date', [$currentYearStart, $currentYearEnd]);
            })
                ->whereIn('created_by', $visibleUserIds)   // Only customers created by your team
                ->count();

            // 3. Total Orders, Quantity & Value in Current Year (by your team)
            $orderStatsCurrentYear = DB::table('orders')
                ->whereIn('created_by', $visibleUserIds)
                ->whereBetween('order_date', [$currentYearStart, $currentYearEnd])
                ->select(
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('COALESCE(SUM(total_qty), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(grand_total), 0) as total_value')
                )
                ->first();

            // ===================== Order Summary (You + ASR) =====================
            $relevantUserIds = array_merge([$user_id], $asrUserIds);
            $relevantDsrUserIds = $dsrUserIds;

            $todayOrders = DB::table('orders')
                ->whereIn('created_by', $relevantUserIds)
                ->whereDate('order_date', $today)
                ->select(
                    DB::raw('COALESCE(SUM(total_qty), 0) as today_quantity'),
                    DB::raw('COALESCE(SUM(grand_total), 0) as today_value')
                )->first();

            $currentMonthOrders = DB::table('orders')
                ->whereIn('created_by', $relevantUserIds)
                ->whereBetween('order_date', [$currentMonthStart, $currentMonthEnd])
                ->select(
                    DB::raw('COALESCE(SUM(total_qty), 0) as month_quantity'),
                    DB::raw('COALESCE(SUM(grand_total), 0) as month_value')
                )->first();

            $todayOrdersDsr = DB::table('orders')
                ->whereIn('created_by', $relevantDsrUserIds)
                ->whereDate('order_date', $today)
                ->select(
                    DB::raw('COALESCE(SUM(total_qty), 0) as today_quantity'),
                    DB::raw('COALESCE(SUM(grand_total), 0) as today_value')
                )->first();
            $currentMonthOrdersDsr = DB::table('orders')
                ->whereIn('created_by', $relevantDsrUserIds)
                ->whereBetween('order_date', [$currentMonthStart, $currentMonthEnd])
                ->select(
                    DB::raw('COALESCE(SUM(total_qty), 0) as month_quantity'),
                    DB::raw('COALESCE(SUM(grand_total), 0) as month_value')
                )->first();

            // Unique Buyers from ASR - Current Month
            $uniqueBuyersFromAsr = DB::table('orders')
                ->whereIn('orders.created_by', $asrUserIds)
                ->whereBetween('orders.order_date', [$currentMonthStart, $currentMonthEnd])
                ->whereNotNull('orders.buyer_id')
                ->distinct('orders.buyer_id')
                ->count('orders.buyer_id');

            // Unique Buyers from DSR - Current Month
            $uniqueBuyersFromDsr = DB::table('orders')
                ->whereIn('orders.created_by', $dsrUserIds)
                ->whereBetween('orders.order_date', [$currentMonthStart, $currentMonthEnd])
                ->whereNotNull('orders.buyer_id')
                ->distinct('orders.buyer_id')
                ->count('orders.buyer_id');

            // ===================== Working Type - ASR =====================
            $baseAsr = Attendance::whereIn('user_id', $asrUserIds)
                ->whereNotNull('working_type')->where('working_type', '!=', '');

            // $wtAsrToday = (clone $baseAsr)->whereDate('punchin_date', $today)
            //     ->select([
            //         DB::raw("SUM(CASE WHEN FIND_IN_SET('Retailer Visit', working_type) > 0 THEN 1 ELSE 0 END) as retailer_visit"),
            //         DB::raw("SUM(CASE WHEN FIND_IN_SET('Retailer Meet', working_type) > 0 THEN 1 ELSE 0 END) as retailer_meet"),
            //         DB::raw("SUM(CASE WHEN FIND_IN_SET('Nukkad Meet', working_type) > 0 THEN 1 ELSE 0 END) as nukkad_meet"),
            //         DB::raw("SUM(CASE WHEN FIND_IN_SET('Field Demo', working_type) > 0 THEN 1 ELSE 0 END) as field_demo"),
            //         DB::raw("SUM(CASE WHEN NOT (FIND_IN_SET('Retailer Visit', working_type) > 0 
            //                                   OR FIND_IN_SET('Nukkad Meet', working_type) > 0 
            //                                   OR FIND_IN_SET('Field Demo', working_type) > 0) THEN 1 ELSE 0 END) as other")
            //     ])->first();

            $wtAsrToday = (clone $baseAsr)->whereDate('punchin_date', $today)
                ->select([

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Visit', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_visit
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Nukkad Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as nukkad_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Field Demo', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as field_demo
                    "),

                    DB::raw("
                    SUM(
                        CASE
                            WHEN
                                TRIM(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            REPLACE(
                                                                REPLACE(
                                                                    REPLACE(
                                                                        REPLACE(
                                                                            working_type,
                                                                            'Retailer Visit', ''
                                                                        ),
                                                                        'Retailer Meet', ''
                                                                    ),
                                                                    'Nukkad Meet', ''
                                                                ),
                                                                'Field Demo', ''
                                                            ),
                                                            'Full Day Leave', ''
                                                        ),
                                                        'First Half Leave', ''
                                                    ),
                                                    'Second Half Leave', ''
                                                ),
                                                ',', ''
                                            ),
                                            '-', ''
                                        ),
                                        '  ',
                                        ''
                                    )
                                ) != ''
                            THEN 1
                            ELSE 0
                        END
                    ) as other
                    ")

                ])->first();

            $wtAsrMonth = (clone $baseAsr)->whereBetween('punchin_date', [$currentMonthStart, $currentMonthEnd])
                ->select([

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Visit', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_visit
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Nukkad Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as nukkad_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Field Demo', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as field_demo
                    "),

                    DB::raw("
                    SUM(
                        CASE
                            WHEN
                                TRIM(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            REPLACE(
                                                                REPLACE(
                                                                    REPLACE(
                                                                        REPLACE(
                                                                            working_type,
                                                                            'Retailer Visit', ''
                                                                        ),
                                                                        'Retailer Meet', ''
                                                                    ),
                                                                    'Nukkad Meet', ''
                                                                ),
                                                                'Field Demo', ''
                                                            ),
                                                            'Full Day Leave', ''
                                                        ),
                                                        'First Half Leave', ''
                                                    ),
                                                    'Second Half Leave', ''
                                                ),
                                                ',', ''
                                            ),
                                            '-', ''
                                        ),
                                        '  ',
                                        ''
                                    )
                                ) != ''
                            THEN 1
                            ELSE 0
                        END
                    ) as other
                    ")

                ])->first();

            $wtAsrYear = (clone $baseAsr)->whereBetween('punchin_date', [$currentYearStart, $currentYearEnd])
                ->select([

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Visit', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_visit
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Nukkad Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as nukkad_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Field Demo', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as field_demo
                    "),

                    DB::raw("
                    SUM(
                        CASE
                            WHEN
                                TRIM(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            REPLACE(
                                                                REPLACE(
                                                                    REPLACE(
                                                                        REPLACE(
                                                                            working_type,
                                                                            'Retailer Visit', ''
                                                                        ),
                                                                        'Retailer Meet', ''
                                                                    ),
                                                                    'Nukkad Meet', ''
                                                                ),
                                                                'Field Demo', ''
                                                            ),
                                                            'Full Day Leave', ''
                                                        ),
                                                        'First Half Leave', ''
                                                    ),
                                                    'Second Half Leave', ''
                                                ),
                                                ',', ''
                                            ),
                                            '-', ''
                                        ),
                                        '  ',
                                        ''
                                    )
                                ) != ''
                            THEN 1
                            ELSE 0
                        END
                    ) as other
                    ")

                ])->first();

            // ===================== Working Type - DSR =====================
            $baseDsr = Attendance::whereIn('user_id', $dsrUserIds)
                ->whereNotNull('working_type')->where('working_type', '!=', '');

            $wtDsrToday = (clone $baseDsr)->whereDate('punchin_date', $today)
                ->select([

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Visit', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_visit
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Nukkad Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as nukkad_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Field Demo', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as field_demo
                    "),

                    DB::raw("
                    SUM(
                        CASE
                            WHEN
                                TRIM(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            REPLACE(
                                                                REPLACE(
                                                                    REPLACE(
                                                                        REPLACE(
                                                                            working_type,
                                                                            'Retailer Visit', ''
                                                                        ),
                                                                        'Retailer Meet', ''
                                                                    ),
                                                                    'Nukkad Meet', ''
                                                                ),
                                                                'Field Demo', ''
                                                            ),
                                                            'Full Day Leave', ''
                                                        ),
                                                        'First Half Leave', ''
                                                    ),
                                                    'Second Half Leave', ''
                                                ),
                                                ',', ''
                                            ),
                                            '-', ''
                                        ),
                                        '  ',
                                        ''
                                    )
                                ) != ''
                            THEN 1
                            ELSE 0
                        END
                    ) as other
                    ")

                ])->first();

            $wtDsrMonth = (clone $baseDsr)->whereBetween('punchin_date', [$currentMonthStart, $currentMonthEnd])
                ->select([

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Visit', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_visit
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Nukkad Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as nukkad_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Field Demo', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as field_demo
                    "),

                    DB::raw("
                    SUM(
                        CASE
                            WHEN
                                TRIM(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            REPLACE(
                                                                REPLACE(
                                                                    REPLACE(
                                                                        REPLACE(
                                                                            working_type,
                                                                            'Retailer Visit', ''
                                                                        ),
                                                                        'Retailer Meet', ''
                                                                    ),
                                                                    'Nukkad Meet', ''
                                                                ),
                                                                'Field Demo', ''
                                                            ),
                                                            'Full Day Leave', ''
                                                        ),
                                                        'First Half Leave', ''
                                                    ),
                                                    'Second Half Leave', ''
                                                ),
                                                ',', ''
                                            ),
                                            '-', ''
                                        ),
                                        '  ',
                                        ''
                                    )
                                ) != ''
                            THEN 1
                            ELSE 0
                        END
                    ) as other
                    ")

                ])->first();

            $wtDsrYear = (clone $baseDsr)->whereBetween('punchin_date', [$currentYearStart, $currentYearEnd])
                ->select([

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Visit', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_visit
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Retailer Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as retailer_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Nukkad Meet', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as nukkad_meet
                    "),

                    DB::raw("
                        SUM(
                            CASE 
                                WHEN FIND_IN_SET('Field Demo', REPLACE(working_type, ', ', ',')) > 0 
                                THEN 1 ELSE 0 
                            END
                        ) as field_demo
                    "),

                    DB::raw("
                    SUM(
                        CASE
                            WHEN
                                TRIM(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        REPLACE(
                                                            REPLACE(
                                                                REPLACE(
                                                                    REPLACE(
                                                                        REPLACE(
                                                                            working_type,
                                                                            'Retailer Visit', ''
                                                                        ),
                                                                        'Retailer Meet', ''
                                                                    ),
                                                                    'Nukkad Meet', ''
                                                                ),
                                                                'Field Demo', ''
                                                            ),
                                                            'Full Day Leave', ''
                                                        ),
                                                        'First Half Leave', ''
                                                    ),
                                                    'Second Half Leave', ''
                                                ),
                                                ',', ''
                                            ),
                                            '-', ''
                                        ),
                                        '  ',
                                        ''
                                    )
                                ) != ''
                            THEN 1
                            ELSE 0
                        END
                    ) as other
                    ")

                ])->first();

            $top5Products = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereIn('orders.created_by', $visibleUserIds)
                ->whereBetween('orders.order_date', [$currentYearStart, $currentYearEnd])
                ->whereNotNull('order_details.product_id')
                ->groupBy('order_details.product_id', 'products.product_name')
                ->select(
                    'order_details.product_id',
                    'products.product_name',
                    DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(order_details.line_total), 0) as total_value')
                )
                ->orderBy('total_quantity', 'desc')
                ->limit(5)
                ->get();

            // Calculate total of top 5
            $top5TotalQuantity = $top5Products->sum('total_quantity');
            $top5TotalValue    = $top5Products->sum('total_value');

            $top5ProductsValueWise = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereIn('orders.created_by', $visibleUserIds)
                ->whereBetween('orders.order_date', [$currentYearStart, $currentYearEnd])
                ->whereNotNull('order_details.product_id')
                ->groupBy('order_details.product_id', 'products.product_name')
                ->select(
                    'order_details.product_id',
                    'products.product_name',
                    DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(order_details.line_total), 0) as total_value')
                )
                ->orderBy('total_value', 'desc')
                ->limit(5)
                ->get();

            $top5ProductsValueWiseTotalQty = $top5ProductsValueWise->sum('total_quantity');
            $top5ProductsValueWiseTotalValue = $top5ProductsValueWise->sum('total_value');

            $top5MonthValueWise = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereIn('orders.created_by', $visibleUserIds)
                ->whereBetween('orders.order_date', [$currentMonthStart, $currentMonthEnd])
                ->groupBy('order_details.product_id', 'products.product_name')
                ->select(
                    'products.product_name',
                    DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(order_details.line_total), 0) as total_value')
                )
                ->orderBy('total_value', 'desc')
                ->limit(5)
                ->get();

            $top5MonthValueWiseTotalQty = $top5MonthValueWise->sum('total_quantity');
            $top5MonthValueWiseTotalValue = $top5MonthValueWise->sum('total_value');

            // Total Unique Buyers in Current Year (New as requested)
            $totalUniqueBuyersCurrentYear = DB::table('orders')
                ->whereIn('created_by', $visibleUserIds)
                ->whereBetween('order_date', [$currentMonthStart, $currentMonthEnd])
                ->distinct('buyer_id')
                ->count('buyer_id');

            // ===================== Top 5 Products - Current Month =====================
            $top5Month = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereIn('orders.created_by', $visibleUserIds)
                ->whereBetween('orders.order_date', [$currentMonthStart, $currentMonthEnd])
                ->groupBy('order_details.product_id', 'products.product_name')
                ->select(
                    'products.product_name',
                    DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(order_details.line_total), 0) as total_value')
                )
                ->orderBy('total_quantity', 'desc')
                ->limit(5)
                ->get();

            $top5MonthTotalQty = $top5Month->sum('total_quantity');
            $top5MonthTotalValue = $top5Month->sum('total_value');

            // ===================== Top 5 Products - Current Year =====================
            $top5Year = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereIn('orders.created_by', $visibleUserIds)
                ->whereBetween('orders.order_date', [$currentYearStart, $currentYearEnd])
                ->groupBy('order_details.product_id', 'products.product_name')
                ->select(
                    'products.product_name',
                    DB::raw('COALESCE(SUM(order_details.quantity), 0) as total_quantity'),
                    DB::raw('COALESCE(SUM(order_details.line_total), 0) as total_value')
                )
                ->orderBy('total_quantity', 'desc')
                ->limit(5)
                ->get();

            $top5YearTotalQty = $top5Year->sum('total_quantity');
            $top5YearTotalValue = $top5Year->sum('total_value');

            $summary = [
                'total_users'        => $totalUsers,
                'total_punch_in'     => $totalPunchInToday,
                'total_not_punch_in' => $totalNotPunchInToday,
                'leave_asr_today' => (float) ($leaveAsrToday->total_leave ?? 0),

                'leave_dsr_today' => (float) ($leaveDsrToday->total_leave ?? 0),
                'asr' => [
                    'total'                => $totalAsr,
                    'checked_in_today'     => $asrCheckedIn,
                    'not_checked_in_today' => $asrNotCheckedIn,
                ],

                'dsr' => [
                    'total'                => $totalDsr,
                    'checked_in_today'     => $dsrCheckedIn,
                    'not_checked_in_today' => $dsrNotCheckedIn,
                ],

                'today_orders' => [
                    'quantity' => (int) ($todayOrders->today_quantity ?? 0),
                    'value'    => round($todayOrders->today_value ?? 0, 2),
                ],

                'current_month_orders' => [
                    'quantity' => (int) ($currentMonthOrders->month_quantity ?? 0),
                    'value'    => round($currentMonthOrders->month_value ?? 0, 2),
                ],
                // DSR Orders
                'today_orders_dsr' => [
                    'quantity' => (int) ($todayOrdersDsr->today_quantity ?? 0),
                    'value' => round($todayOrdersDsr->today_value ?? 0, 2),
                ],
                'current_month_orders_dsr' => [
                    'quantity' => (int) ($currentMonthOrdersDsr->month_quantity ?? 0),
                    'value' => round($currentMonthOrdersDsr->month_value ?? 0, 2),
                ],
                'asr_target' => [
                    'target' => (int) ($asrTargetData->total_target ?? 0),
                    'achievement' => (int) ($asrTargetData->total_achievement ?? 0),
                    'achievement_percent' => $asrAchievementPercent,
                    'target_qty' => (int) ($asrQtyTargetData->total_qty_target ?? 0),
                ],

                'dsr_target' => [
                    'target' => (int) ($dsrTargetData->total_target ?? 0),
                    'achievement' => (int) ($dsrTargetData->total_achievement ?? 0),
                    'achievement_percent' => $dsrAchievementPercent,
                    'target_qty' => (int) ($dsrQtyTargetData->total_qty_target ?? 0),
                ],

                'unique_buyers_from_asr' => $uniqueBuyersFromAsr,
                'unique_buyers_from_dsr' => $uniqueBuyersFromDsr,
                'total_unique_buyers_current_year' => $totalUniqueBuyersCurrentYear,
                // Punchout Remaining Today
                'punchout_remaining_asr_today' => $punchoutRemainingAsr,
                'punchout_remaining_dsr_today' => $punchoutRemainingDsr,

                // New Secondary Customer Metrics
                'secondary_customers_registered_approved_today' => $secondaryRegisteredApprovedToday,
                'secondary_customers_registered_approved_current_year' => $secondaryRegisteredApprovedCurrentYear,   // ← New Field
                'secondary_customers_with_order_current_year'   => $secondaryWithOrderCurrentYear,
                'total_orders_current_year'                     => (int) ($orderStatsCurrentYear->total_orders ?? 0),
                'total_order_quantity_current_year'             => (int) ($orderStatsCurrentYear->total_quantity ?? 0),
                'total_order_value_current_year'                => round($orderStatsCurrentYear->total_value ?? 0, 2),
                // Top 5 Products
                'top_5_products_current_month' => $top5Month->map(fn($item) => [
                    'product_name' => $item->product_name ?? 'N/A',
                    'quantity'     => (int) $item->total_quantity,
                    'value'        => round($item->total_value, 2),
                ])->toArray(),

                'top_5_products_current_year' => $top5Year->map(fn($item) => [
                    'product_name' => $item->product_name ?? 'N/A',
                    'quantity'     => (int) $item->total_quantity,
                    'value'        => round($item->total_value, 2),
                ])->toArray(),

                'top_5_products_total_current_month' => [
                    'quantity' => (int) $top5MonthTotalQty,
                    'value'    => round($top5MonthTotalValue, 2),
                ],

                'top_5_products_total_current_year' => [
                    'quantity' => (int) $top5YearTotalQty,
                    'value'    => round($top5YearTotalValue, 2),
                ],
                // ===================== Top 5 Products =====================
                'top_5_products' => $top5Products->map(function ($item) {
                    return [
                        'product_name' => $item->product_name ?? 'N/A',
                        'quantity'     => (int) $item->total_quantity,
                        'value'        => round($item->total_value, 2),
                    ];
                })->toArray(),

                'top_5_products_total' => [
                    'quantity' => (int) $top5TotalQuantity,
                    'value'    => round($top5TotalValue, 2),
                ],

                'top_5_products_value_wise' => $top5ProductsValueWise->map(function ($item) {
                    return [
                        'product_name' => $item->product_name ?? 'N/A',
                        'quantity'     => (int) $item->total_quantity,
                        'value'        => round($item->total_value, 2),
                    ];
                })->toArray(),

                'top_5_products_total_value_wise' => [
                    'quantity' => (int) $top5ProductsValueWiseTotalQty,
                    'value'    => round($top5ProductsValueWiseTotalValue, 2),
                ],

                'top_5_products_current_month_value_wise' => $top5MonthValueWise->map(function ($item) {
                    return [
                        'product_name' => $item->product_name ?? 'N/A',
                        'quantity'     => (int) $item->total_quantity,
                        'value'        => round($item->total_value, 2),
                    ];
                })->toArray(),

                'top_5_products_total_current_month_value_wise' => [
                    'quantity' => (int) $top5MonthValueWiseTotalQty,
                    'value'    => round($top5MonthValueWiseTotalValue, 2),
                ],

                // Working Type - ASR
                'working_type_asr_today' => [
                    'retailer_visit' => (int) ($wtAsrToday->retailer_visit ?? 0),
                    'retailer_meet' => (int) ($wtAsrToday->retailer_meet ?? 0),
                    'nukkad_meet'    => (int) ($wtAsrToday->nukkad_meet ?? 0),
                    'field_demo'     => (int) ($wtAsrToday->field_demo ?? 0),
                    'other'          => (int) ($wtAsrToday->other ?? 0),
                ],
                'working_type_asr_current_month' => [
                    'retailer_visit' => (int) ($wtAsrMonth->retailer_visit ?? 0),
                    'retailer_meet' => (int) ($wtAsrToday->retailer_meet ?? 0),
                    'nukkad_meet'    => (int) ($wtAsrMonth->nukkad_meet ?? 0),
                    'field_demo'     => (int) ($wtAsrMonth->field_demo ?? 0),
                    'other'          => (int) ($wtAsrMonth->other ?? 0),
                ],
                'working_type_asr_current_year' => [
                    'retailer_visit' => (int) ($wtAsrYear->retailer_visit ?? 0),
                    'retailer_meet' => (int) ($wtAsrToday->retailer_meet ?? 0),
                    'nukkad_meet'    => (int) ($wtAsrYear->nukkad_meet ?? 0),
                    'field_demo'     => (int) ($wtAsrYear->field_demo ?? 0),
                    'other'          => (int) ($wtAsrYear->other ?? 0),
                ],

                // Working Type - DSR
                'working_type_dsr_today' => [
                    'retailer_visit' => (int) ($wtDsrToday->retailer_visit ?? 0),
                    'retailer_meet' => (int) ($wtAsrToday->retailer_meet ?? 0),
                    'nukkad_meet'    => (int) ($wtDsrToday->nukkad_meet ?? 0),
                    'field_demo'     => (int) ($wtDsrToday->field_demo ?? 0),
                    'other'          => (int) ($wtDsrToday->other ?? 0),
                ],
                'working_type_dsr_current_month' => [
                    'retailer_visit' => (int) ($wtDsrMonth->retailer_visit ?? 0),
                    'retailer_meet' => (int) ($wtAsrToday->retailer_meet ?? 0),
                    'nukkad_meet'    => (int) ($wtDsrMonth->nukkad_meet ?? 0),
                    'field_demo'     => (int) ($wtDsrMonth->field_demo ?? 0),
                    'other'          => (int) ($wtDsrMonth->other ?? 0),
                ],
                'working_type_dsr_current_year' => [
                    'retailer_visit' => (int) ($wtDsrYear->retailer_visit ?? 0),
                    'retailer_meet' => (int) ($wtAsrToday->retailer_meet ?? 0),
                    'nukkad_meet'    => (int) ($wtDsrYear->nukkad_meet ?? 0),
                    'field_demo'     => (int) ($wtDsrYear->field_demo ?? 0),
                    'other'          => (int) ($wtDsrYear->other ?? 0),
                ]
            ];

            return response()->json([
                'status'  => 'success',
                'message' => "Today's team attendance & order summary retrieved successfully.",
                'data'    => $summary
            ], $this->successStatus);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], $this->internalError);
        }
    }

    private function getUserIdsFromTree($users)
    {
        $ids = [];

        foreach ($users as $user) {
            $ids[] = $user->id;

            if (!empty($user->children)) {
                $ids = array_merge($ids, $this->getUserIdsFromTree($user->children));
            }
        }

        return $ids;
    }

    public function getTodayTeamAttendanceList(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;

            $today = now()->toDateString();

            // ✅ Assigned users
            $myTeamUserIds = getUsersReportingToAuth($user_id);
            $myTeamUserIds = array_unique(array_merge([$user_id], $myTeamUserIds ?? []));

            // ✅ Params
            $designation = strtolower($request->get('designation'));
            $branch = $request->get('branch');
            $zone = $request->get('zone');
            $userFilter = $request->get('user_id');
            $status = $request->get('status'); // punch_in / not_punch_in

            // ✅ designation mapping
            $designationIds = [];
            if ($designation == 'asr') $designationIds = [3];
            if ($designation == 'dsr') $designationIds = [6];

            // 🔥 BASE QUERY
            $query = DB::table('users')
                ->leftJoin('users as reporting_user', 'users.reportingid', '=', 'reporting_user.id')
                ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
                ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
                ->leftJoin('attendances', function ($join) use ($today) {
                    $join->on('users.id', '=', 'attendances.user_id')
                        ->whereDate('attendances.punchin_date', $today);
                })
                ->whereIn('users.id', $myTeamUserIds)
                ->where('users.active', 'Y');

            // =========================
            // ✅ APPLY FILTERS
            // =========================

            if (!empty($designationIds)) {
                $query->whereIn('users.designation_id', $designationIds);
            }

            if (!empty($branch)) {
                $query->where('branches.branch_name', 'LIKE', "%$branch%");
            }

            if (!empty($zone)) {
                $query->where('divisions.division_name', 'LIKE', "%$zone%");
            }

            if (!empty($userFilter)) {
                $query->where('users.id', $userFilter);
            }

            // 🔥 SELECT
            $data = $query->select(
                'users.id',
                'users.name',
                'users.reportingid',
                'reporting_user.name as reporting_name',
                'reporting_user.mobile as reporting_mobile',
                'branches.branch_name',
                'divisions.division_name',
                'attendances.working_type',
                DB::raw('CASE WHEN attendances.id IS NOT NULL THEN 1 ELSE 0 END as punchin')
            )
                ->orderBy('reporting_user.name', 'ASC')
                ->orderBy('users.name', 'ASC')
                ->get();

            // =========================
            // 🔥 STATUS FILTER (AFTER QUERY)
            // =========================

            if ($status == 'punch_in') {
                $data = $data->where('punchin', 1)->values();
            } elseif ($status == 'not_punch_in') {
                $data = $data->where('punchin', 0)->values();
            }

            // =========================
            // 🔥 FORMAT RESPONSE
            // =========================
            $leaveTypes = ['Full Day Leave', 'First Half Leave', 'Second Half Leave'];
            $result = [];
            $totalUsers = 0;
            $totalPunchIn = 0;
            $totalLeave = 0;
            foreach ($data as $row) {

                $zoneName = $row->division_name ?? 'Unknown';

                if (!isset($result[$zoneName])) {
                    $result[$zoneName] = [
                        'zone' => $zoneName,
                        'users' => [],
                    ];
                }

                $isPunchIn = (bool)$row->punchin;
                // ✅ CHECK LEAVE
                $isLeave = false;
                if (!empty($row->working_type)) {
                    foreach ($leaveTypes as $type) {
                        if (stripos($row->working_type, $type) !== false) {
                            $isLeave = true;
                            break;
                        }
                    }
                }
                $isWorking = $isPunchIn && !$isLeave;
                if ($status == 'leave' && !$isLeave) {
                    continue;
                }
                $result[$zoneName]['users'][] = [
                    'id' => $row->id,
                    'name' => $row->name,
                    'branch' => $row->branch_name ?? 'N/A',
                    'reporting' => [
                        'id' => $row->reportingid,
                        'name' => $row->reporting_name,
                        'mobile' => $row->reporting_mobile,
                    ],
                    'punchin' => $isPunchIn,
                    'not_punchin' => !$isPunchIn,

                    // ✅ NEW TAGS
                    'on_leave' => $isLeave,
                    'working' => $isWorking,
                ];
                // $result[$zoneName]['users'][] = [
                //     'id' => $row->id,
                //     'name' => $row->name,
                //     'branch' => $row->branch_name ?? 'N/A',
                //     'punchin' => $isPunchIn,
                //     'not_punchin' => !$isPunchIn,
                // ];

                $totalUsers++;

                if ($isPunchIn && !$isLeave) {
                    $totalPunchIn++;
                }
                if ($isLeave) $totalLeave++;
            }

            $result = $this->sortZoneBuckets($result);

            return response()->json([
                'success' => true,
                'message' => 'Today team attendance fetched successfully',
                'data' => [
                    'zones' => array_values($result),
                    'summary' => [
                        'total_users' => $totalUsers,
                        'total_punch_in' => $totalPunchIn,
                        'total_not_punch_in' => $totalUsers - $totalPunchIn,
                        'total_on_leave' => $totalLeave,
                        'total_working' => $totalPunchIn,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function getAssignedUsersBasicList(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;

            // ✅ Assigned users
            $myTeamUserIds = getUsersReportingToAuth($user_id);
            $myTeamUserIds = array_unique(array_merge([$user_id], $myTeamUserIds ?? []));

            $zone = $request->get('zone');
            $zoneId = $request->get('zone_id');
            $branch = $request->get('branch');
            $branchId = $request->get('branch_id');
            $branchIds = [];
            $branchNameIds = [];
            $branchNameFilterRequested = false;

            if (!empty($branchId)) {
                $branchIds = is_array($branchId) ? $branchId : explode(',', $branchId);
                $branchIds = array_values(array_filter(array_map('trim', $branchIds), fn($value) => $value !== ''));
            }

            if (empty($branchIds) && !empty($branch)) {
                $branchNameFilterRequested = true;
                $branchNameIds = DB::table('branches')
                    ->where('branch_name', 'LIKE', "%{$branch}%")
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();
            }

            // 🔥 MAIN QUERY (NO attendance join)
            $data = DB::table('users')
                ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
                ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
                ->whereIn('users.id', $myTeamUserIds)
                ->where('users.active', 'Y')
                ->when(!empty($zoneId), function ($q) use ($zoneId) {
                    $q->where('users.division_id', $zoneId);
                })
                ->when(empty($zoneId) && !empty($zone), function ($q) use ($zone) {
                    $q->where('divisions.division_name', 'LIKE', "%{$zone}%");
                })
                ->when(!empty($branchIds), function ($q) use ($branchIds) {
                    $q->where(function ($branchQuery) use ($branchIds) {
                        $branchQuery->whereIn('users.branch_id', $branchIds);
                        foreach ($branchIds as $id) {
                            $branchQuery->orWhereRaw('FIND_IN_SET(?, users.branch_id)', [$id]);
                        }
                    });
                })
                ->when(empty($branchIds) && $branchNameFilterRequested, function ($q) use ($branchNameIds) {
                    $q->where(function ($branchQuery) use ($branchNameIds) {
                        $branchQuery->whereIn('users.branch_id', $branchNameIds);
                        foreach ($branchNameIds as $id) {
                            $branchQuery->orWhereRaw('FIND_IN_SET(?, users.branch_id)', [$id]);
                        }
                    });
                })
                ->select(
                    'users.id',
                    'users.name',
                    'users.division_id',
                    'users.branch_id',
                    'divisions.id as zone_id',
                    'divisions.division_name',
                    'branches.id as branch_master_id',
                    'branches.branch_name'
                )
                ->get();

            // =========================
            // 🔥 PREPARE LISTS
            // =========================

            $users = [];
            $zones = [];
            $branches = [];
            $seenZones = [];
            $branchZonePairs = [];
            $allBranchIds = [];

            foreach ($data as $row) {

                // ✅ Users list
                $users[] = [
                    'id' => $row->id,
                    'name' => $row->name
                ];

                // ✅ Unique zones
                if ($row->zone_id && !in_array($row->zone_id, $seenZones)) {
                    $seenZones[] = $row->zone_id;
                    $zones[] = [
                        'id' => $row->zone_id,
                        'name' => $row->division_name
                    ];
                }

                foreach (explode(',', (string) $row->branch_id) as $userBranchId) {
                    $userBranchId = trim($userBranchId);
                    if ($userBranchId !== '') {
                        $allBranchIds[] = $userBranchId;
                        $branchZonePairs[$userBranchId] = $row->zone_id;
                    }
                }
            }

            $zones = $this->sortZoneList($zones);

            if (!empty($allBranchIds)) {
                $branchMasters = DB::table('branches')
                    ->whereIn('id', array_values(array_unique($allBranchIds)))
                    ->orderBy('branch_name')
                    ->select('id', 'branch_name')
                    ->get();

                foreach ($branchMasters as $branchMaster) {
                    $branches[] = [
                        'id' => $branchMaster->id,
                        'name' => $branchMaster->branch_name,
                        'zone_id' => $branchZonePairs[$branchMaster->id] ?? null
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'success' => true,
                'message' => 'Assigned users basic list fetched successfully',
                'data' => [
                    'users' => $users,
                    'zones' => array_values($zones),
                    'branches' => array_values($branches)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function getTodayTeamSalesList(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;

            $today = now()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();

            $currentMonthName = now()->format('M');
            $currentYear = now()->year;

            // ✅ Team users
            $teamUserIds = getUsersReportingToAuth($user_id);
            $teamUserIds = array_unique(array_merge([$user_id], $teamUserIds ?? []));

            // ✅ Filters
            $designation = strtolower($request->get('designation'));
            $branch = $request->get('branch');
            $zone = $request->get('zone');
            $userFilter = $request->get('user_id');

            $designationIds = [];
            if ($designation == 'asr') $designationIds = [3];
            if ($designation == 'dsr') $designationIds = [6];

            // =========================
            // 🔥 BASE USER QUERY
            // =========================
            $query = DB::table('users')
                ->leftJoin('users as reporting_user', 'users.reportingid', '=', 'reporting_user.id')
                ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
                ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
                ->whereIn('users.id', $teamUserIds)
                ->where('users.active', 'Y');

            if (!empty($designationIds)) {
                $query->whereIn('users.designation_id', $designationIds);
            }

            if (!empty($branch)) {
                $query->where('branches.branch_name', 'LIKE', "%$branch%");
            }

            if (!empty($zone)) {
                $query->where('divisions.division_name', 'LIKE', "%$zone%");
            }

            if (!empty($userFilter)) {
                $query->where('users.id', $userFilter);
            }

            $users = $query->select(
                'users.id',
                'users.name',
                'users.reportingid',
                'reporting_user.name as reporting_name',
                'reporting_user.mobile as reporting_mobile',
                'branches.branch_name',
                'divisions.division_name'
            )
                ->orderBy('reporting_user.name', 'ASC') // A to Z by reporting name
                ->orderBy('users.name', 'ASC') // optional secondary sorting
                ->get();

            // =========================
            // 🔥 PRELOAD TARGETS (FAST)
            // =========================
            $targets = SalesTargetUsers::whereIn('user_id', $users->pluck('id'))
                ->where('type', 'secondary')
                ->where('month', $currentMonthName)
                ->where('year', $currentYear)
                ->select('user_id', DB::raw('SUM(target) as target'))
                ->groupBy('user_id')
                ->pluck('target', 'user_id');
            $targetsQty = SalesTargetUsers::whereIn('user_id', $users->pluck('id'))
                ->where('type', 'secondary')
                ->where('month', $currentMonthName)
                ->where('year', $currentYear)
                ->select('user_id', DB::raw('SUM(qunatity_target) as qunatity_target'))
                ->groupBy('user_id')
                ->pluck('qunatity_target', 'user_id');

            $result = [];
            $summary = [
                'total_users' => 0,
                'total_target' => 0,
                'total_target_qty' => 0,
                'total_month_value' => 0,
                'total_today_value' => 0,
                'total_today_orders' => 0,
                'total_month_orders' => 0,
                'total_visits_today' => 0,
                'total_visits_month' => 0,
                'month_unique_retailer_visits' => 0,
                'total_unique_retailers_month' => 0
            ];

            foreach ($users as $row) {

                $uid = $row->id;

                // ================= ORDERS =================
                $todayOrders = DB::table('orders')
                    ->where('created_by', $uid)
                    ->whereDate('order_date', $today)
                    ->select(
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('COALESCE(SUM(total_qty),0) as qty'),
                        DB::raw('COALESCE(SUM(grand_total),0) as value')
                    )->first();

                $monthOrders = DB::table('orders')
                    ->where('created_by', $uid)
                    ->whereBetween('order_date', [$monthStart, $monthEnd])
                    ->select(
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('COALESCE(SUM(total_qty),0) as qty'),
                        DB::raw('COALESCE(SUM(grand_total),0) as value'),
                        DB::raw('COUNT(DISTINCT buyer_id) as unique_retailers')
                    )->first();

                // ================= VISITS =================
                $todayVisits = DB::table('check_in')
                    ->where('user_id', $uid)
                    ->where('entity_type', 'secondary_customer')
                    ->whereDate('checkin_date', $today)
                    ->count();

                $monthVisitData = DB::table('check_in')
                    ->where('user_id', $uid)
                    ->where('entity_type', 'secondary_customer')
                    ->whereBetween('checkin_date', [$monthStart, $monthEnd])
                    ->select(
                        DB::raw('COUNT(*) as total_visits'),
                        DB::raw('COUNT(DISTINCT entity_id) as unique_visits')
                    )->first();

                $monthVisits = (int) ($monthVisitData->total_visits ?? 0);
                $monthUniqueVisits = (int) ($monthVisitData->unique_visits ?? 0);

                // ================= RETAILERS =================
                $registeredRetailers = DB::table('secondary_customers')
                    ->where('created_by', $uid)
                    ->where('status', 'approved')
                    ->count();

                // ================= TARGET =================
                $target = (int) ($targets[$uid] ?? 0);
                $targetQty = (int) ($targetsQty[$uid] ?? 0);

                // ================= ACHIEVEMENT =================
                $achievement = $target > 0
                    ? round(($monthOrders->value / $target) * 100, 2)
                    : 0;
                $achievementQty = $targetQty > 0
                    ? round(($monthOrders->qty / $targetQty) * 100, 2)
                    : 0;

                $zoneName = $row->division_name ?? 'Unknown';

                if (!isset($result[$zoneName])) {
                    $result[$zoneName] = [
                        'zone' => $zoneName,
                        'users' => [],
                        'totals' => [
                            'target' => 0,
                            'month_value' => 0,
                            'today_value' => 0
                        ]
                    ];
                }

                $result[$zoneName]['users'][] = [
                    'id' => $uid,
                    'name' => $row->name,
                    'branch' => $row->branch_name ?? 'N/A',
                    // ✅ ADD THIS
                    'reporting' => [
                        'id' => $row->reportingid,
                        'name' => $row->reporting_name,
                        'mobile' => $row->reporting_mobile,
                    ],

                    'registered_retailers' => $registeredRetailers,
                    'target' => $target,
                    'targetQty' => $targetQty,
                    'today_order_value' => (float)$todayOrders->value,
                    'today_order_qty' => (int)$todayOrders->qty,
                    'today_order_count' => (int)$todayOrders->total_orders,

                    'month_order_value' => (float)$monthOrders->value,
                    'month_order_qty' => (int)$monthOrders->qty,
                    'month_order_count' => (int)$monthOrders->total_orders,

                    'achievement_percent' => $achievement,
                    'achievement_percent_qty' => $achievementQty,

                    'today_visits' => $todayVisits,
                    'month_visits' => $monthVisits,
                    'month_unique_retailer_visits' => $monthUniqueVisits,

                    'unique_retailers_month' => (int)$monthOrders->unique_retailers
                ];

                // Zone totals
                $result[$zoneName]['totals']['target'] += $target;
                $result[$zoneName]['totals']['month_value'] += $monthOrders->value;
                $result[$zoneName]['totals']['today_value'] += $todayOrders->value;

                // Summary
                $summary['total_users']++;
                $summary['total_target'] += $target;
                $summary['total_target_qty'] += $targetQty;
                $summary['total_month_value'] += $monthOrders->value;
                $summary['total_today_value'] += $todayOrders->value;
                $summary['total_today_orders'] += $todayOrders->total_orders;
                $summary['total_month_orders'] += $monthOrders->total_orders;
                $summary['total_visits_today'] += $todayVisits;
                $summary['total_visits_month'] += $monthVisits;
                $summary['month_unique_retailer_visits'] += $monthUniqueVisits;
                $summary['total_unique_retailers_month'] += $monthOrders->unique_retailers;
            }

            $result = $this->sortZoneBuckets($result);

            return response()->json([
                'success' => true,
                'message' => 'Today team sales fetched successfully',
                'data' => [
                    'zones' => array_values($result),
                    'summary' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function getRetailerSalesSummary(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;

            $today = now()->toDateString();
            $yearStart = now()->startOfYear()->toDateString();

            $teamUserIds = getUsersReportingToAuth($user_id);
            $teamUserIds = array_unique(array_merge([$user_id], $teamUserIds ?? []));

            $designation = strtolower($request->get('designation'));
            $branch = $request->get('branch');
            $zone = $request->get('zone');
            $userFilter = $request->get('user_id');

            $designationIds = [];
            if ($designation == 'asr') $designationIds = [3];
            if ($designation == 'dsr') $designationIds = [6];

            $query = DB::table('users')
                ->leftJoin('users as reporting_user', 'users.reportingid', '=', 'reporting_user.id')
                ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
                ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
                ->whereIn('users.id', $teamUserIds)
                ->where('users.active', 'Y');

            if (!empty($designationIds)) {
                $query->whereIn('users.designation_id', $designationIds);
            }

            if (!empty($branch)) {
                $query->where('branches.branch_name', 'LIKE', "%$branch%");
            }

            if (!empty($zone)) {
                $query->where('divisions.division_name', 'LIKE', "%$zone%");
            }

            if (!empty($userFilter)) {
                $query->where('users.id', $userFilter);
            }

            $users = $query->select(
                'users.id',
                'users.name',
                'users.reportingid',
                'reporting_user.name as reporting_name',
                'reporting_user.mobile as reporting_mobile',
                'branches.branch_name',
                'divisions.division_name'
            )
                ->orderBy('reporting_user.name', 'ASC')
                ->orderBy('users.name', 'ASC')
                ->get();

            $result = [];
            $summary = [
                'total_users' => 0,
                'total_registered_retailers' => 0,
                'total_today_registered_retailers' => 0,
                'total_unique_orders' => 0,
                'total_orders' => 0,
                'total_order_qty' => 0,
                'total_order_value' => 0
            ];

            $totalOrderQty = 0;
            $totalOrderValue = 0;
            $zoneOrderQty = [];
            $zoneOrderValue = [];
            $formatQuantityInThousands = function ($quantity) {
                return number_format($quantity / 1000, 2, '.', '');
            };

            foreach ($users as $row) {
                $uid = $row->id;

                $registeredRetailers = DB::table('secondary_customers')
                    ->where('created_by', $uid)
                    ->where('status', 'approved')
                    ->count();

                $todayRegisteredRetailers = DB::table('secondary_customers')
                    ->where('created_by', $uid)
                    ->where('status', 'approved')
                    ->whereDate('created_at', $today)
                    ->count();

                $orderData = DB::table('orders')
                    ->where('executive_id', $uid)
                    ->whereBetween('order_date', [$yearStart, $today])
                    ->select(
                        DB::raw('COUNT(DISTINCT buyer_id) as unique_orders'),
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('COALESCE(SUM(grand_total),0) as total_value')
                    )->first();

                $orderQty = (float) DB::table('order_details')
                    ->join('orders', 'order_details.order_id', '=', 'orders.id')
                    ->where('orders.executive_id', $uid)
                    ->whereBetween('orders.order_date', [$yearStart, $today])
                    ->sum('order_details.quantity');

                $orderValue = (float) ($orderData->total_value ?? 0);
                $orderQty = (int) $orderQty;
                $orderValueInLacs = (int) round($orderValue / 100000);

                $zoneName = $row->division_name ?? 'Unknown';

                if (!isset($result[$zoneName])) {
                    $zoneOrderQty[$zoneName] = 0;
                    $zoneOrderValue[$zoneName] = 0;

                    $result[$zoneName] = [
                        'zone' => $zoneName,
                        'users' => [],
                        'totals' => [
                            'registered_retailers' => 0,
                            'today_registered_retailers' => 0,
                            'unique_orders' => 0,
                            'total_orders' => 0,
                            'order_total_qty' => 0,
                            'order_total_value' => 0
                        ]
                    ];
                }

                $result[$zoneName]['users'][] = [
                    'id' => $uid,
                    'name' => $row->name,
                    'branch' => $row->branch_name ?? 'N/A',
                    'reporting' => [
                        'id' => $row->reportingid,
                        'name' => $row->reporting_name,
                        'mobile' => $row->reporting_mobile,
                    ],
                    'registered_retailers' => $registeredRetailers,
                    'today_registered_retailers' => $todayRegisteredRetailers,
                    'unique_orders' => (int) ($orderData->unique_orders ?? 0),
                    'total_orders' => (int) ($orderData->total_orders ?? 0),
                    'order_total_qty' => $formatQuantityInThousands($orderQty),
                    'order_total_value' => $orderValueInLacs
                ];

                $result[$zoneName]['totals']['registered_retailers'] += $registeredRetailers;
                $result[$zoneName]['totals']['today_registered_retailers'] += $todayRegisteredRetailers;
                $result[$zoneName]['totals']['unique_orders'] += (int) ($orderData->unique_orders ?? 0);
                $result[$zoneName]['totals']['total_orders'] += (int) ($orderData->total_orders ?? 0);
                $zoneOrderQty[$zoneName] += $orderQty;
                $zoneOrderValue[$zoneName] += $orderValue;
                $result[$zoneName]['totals']['order_total_qty'] = $formatQuantityInThousands($zoneOrderQty[$zoneName]);
                $result[$zoneName]['totals']['order_total_value'] = ((int) round($zoneOrderValue[$zoneName] / 100000));

                $summary['total_users']++;
                $summary['total_registered_retailers'] += $registeredRetailers;
                $summary['total_today_registered_retailers'] += $todayRegisteredRetailers;
                $summary['total_unique_orders'] += (int) ($orderData->unique_orders ?? 0);
                $summary['total_orders'] += (int) ($orderData->total_orders ?? 0);
                $totalOrderQty += $orderQty;
                $totalOrderValue += $orderValue;
            }

            $summary['total_order_qty'] = $formatQuantityInThousands($totalOrderQty);
            $summary['total_order_value'] = ((int) round($totalOrderValue / 100000));

            $result = $this->sortZoneBuckets($result);

            return response()->json([
                'success' => true,
                'message' => 'Retailer sales summary fetched successfully',
                'data' => [
                    'zones' => array_values($result),
                    'summary' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
