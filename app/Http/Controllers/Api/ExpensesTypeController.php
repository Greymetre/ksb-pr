<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Attendance;
use App\Models\ExpensesType;
use App\Models\Expenses;
use App\Models\ExpenseLog;
use App\Models\Media;
use App\Models\TourProgramme;
use App\Models\User;
use App\Models\UserLiveLocation;
use Validator;
use Auth;
use Carbon\Carbon;


use Illuminate\Http\Request;

class ExpensesTypeController extends Controller
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

    private function expenseFileData(Expenses $expense, bool $withIds = false): array
    {
        $files = [];
        $ids = [];

        foreach ($expense->getMedia('expense_file') as $expenseFile) {
            $files[] = $expenseFile->getFullUrl();

            if ($withIds) {
                $ids[] = $expenseFile->id;
            }
        }

        return $withIds ? [$files, $ids] : $files;
    }

    private function expenseTypeData(ExpensesType $expenseType): array
    {
        $payRolls = config('constants.pay_roll');
        $payrollIds = $expenseType->payrollIds();

        return [
            'id' => $expenseType->id ?? "",
            'name' => $expenseType->name ?? "",
            'rate' => $expenseType->rate ?? "",
            'allowance_type_id' => $expenseType->allowance_type_id ?? "",
            'payroll_id' => $expenseType->payroll_id ?? "",
            'payroll_ids' => $payrollIds,
            'payroll_names' => collect($payrollIds)
                ->map(fn ($id) => $payRolls[$id] ?? $id)
                ->values()
                ->toArray(),
        ];
    }

    private function resolveExpenseRate($expenseTypeId, $requestRate = null): string
    {
        if ($requestRate !== null && $requestRate !== '') {
            return (string) $requestRate;
        }

        return (string) (ExpensesType::where('id', $expenseTypeId)->value('rate') ?? 0);
    }

    private function calculateExpenseDistance(Expenses $expense): float
    {
        $events = collect();
        $expenseDate = Carbon::parse($expense->date)->toDateString();

        $addEvent = function ($latitude, $longitude, $timestamp) use ($events): void {
            if (!is_numeric($latitude) || !is_numeric($longitude) || empty($timestamp)) {
                return;
            }

            $latitude = round((float) $latitude, 6);
            $longitude = round((float) $longitude, 6);

            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                return;
            }

            $events->push([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timestamp' => Carbon::parse($timestamp),
            ]);
        };

        $attendance = Attendance::where('user_id', $expense->user_id)
            ->where('punchin_date', $expenseDate)
            ->orderBy('punchin_time')
            ->first();

        if ($attendance) {
            // Punch-in coordinates are stored in the opposite columns.
            $addEvent(
                $attendance->punchin_longitude,
                $attendance->punchin_latitude,
                $attendance->punchin_date . ' ' . $attendance->punchin_time
            );

            if ($attendance->punchout_time) {
                $addEvent(
                    $attendance->punchout_latitude,
                    $attendance->punchout_longitude,
                    ($attendance->punchout_date ?: $expenseDate) . ' ' . $attendance->punchout_time
                );
            }
        }

        UserLiveLocation::where('userid', $expense->user_id)
            ->whereDate('created_at', $expenseDate)
            ->orderBy('created_at')
            ->get(['latitude', 'longitude', 'created_at'])
            ->each(function ($location) use ($addEvent) {
                $addEvent($location->latitude, $location->longitude, $location->created_at);
            });

        CheckIn::where('user_id', $expense->user_id)
            ->where('checkin_date', $expenseDate)
            ->orderBy('checkin_time')
            ->get()
            ->each(function ($checkin) use ($addEvent, $expenseDate) {
                $addEvent(
                    $checkin->checkin_latitude,
                    $checkin->checkin_longitude,
                    $checkin->checkin_date . ' ' . $checkin->checkin_time
                );

                if ($checkin->checkout_time) {
                    $addEvent(
                        $checkin->checkout_latitude,
                        $checkin->checkout_longitude,
                        ($checkin->checkout_date ?: $expenseDate) . ' ' . $checkin->checkout_time
                    );
                }
            });

        $events = $events->sortBy('timestamp')->values();
        $totalDistance = 0.0;

        for ($index = 1; $index < $events->count(); $index++) {
            $previous = $events[$index - 1];
            $current = $events[$index];
            $roadDistance = getRoadDistance(
                $previous['latitude'],
                $previous['longitude'],
                $current['latitude'],
                $current['longitude']
            );

            // Do not silently replace a driving route with straight-line distance.
            if ($roadDistance === null) {
                throw new \RuntimeException('Unable to calculate road-driving distance from Google Maps.');
            }

            $totalDistance += $roadDistance;
        }

        return round($totalDistance, 3);
    }

    private function expenseRate(Expenses $expense): string
    {
        if ($expense->rate !== null && $expense->rate !== '') {
            return (string) $expense->rate;
        }

        return (string) ($expense->expense_type->rate ?? 0);
    }


    public function getExpensesType(Request $request)
    {
        // $expenses_type = ExpensesType::all();
        // return response()->json(['status'=>'success', 'data'=>$expenses_type], 200); 

        try {

            $validator = Validator::make($request->all(), [
                'payroll_id'  => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }

            $payroll_id = $request->payroll_id;
            $expense_types = ExpensesType::forPayroll($payroll_id)->get();
            if (!empty($expense_types)) {

                $datas = array();
                foreach ($expense_types as $expense_type) {
                    $datas[] = $this->expenseTypeData($expense_type);
                }

                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $datas], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $expense], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }



    public function createExpense(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');

            $dates = Carbon::now();
            $current_date_time = $dates->setTimezone('Asia/Kolkata');

            $userid = $request->user()->id;
            $validator = Validator::make(
                $request->all(),
                [
                    // 'customer_id'   => 'nullable|exists:customers,id',
                    'expenses_type'  => "required",
                    'claim_amount'  => "required",
                    'date'  => "required",
                    'expense_file.*' => 'mimes:jpeg,jpg,png,pdf,doc,webp',
                ]
            );


            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }


            if ($expenses = Expenses::create([
                'user_id' => $userid,
                'expenses_type' => isset($request->expenses_type) ? $request->expenses_type : null,
                'rate' => $this->resolveExpenseRate($request->expenses_type, $request->rate),
                'date' => isset($request->date) ? $request->date : null,
                'claim_amount' => isset($request->claim_amount) ? $request->claim_amount : null,
                'start_km' => isset($request->start_km) ? $request->start_km : null,
                'stop_km' => isset($request->stop_km) ? $request->stop_km : null,
                'total_km' => isset($request->total_km) ? $request->total_km : null,
                'note' => isset($request->note) ? $request->note : null,
                'created_by' => $userid,
                'created_at' => $current_date_time
            ])) {

                $logdata = array(
                    'log_date' => date('Y-m-d'),
                    'expense_id' => $expenses->id,
                    'created_by' => $userid,
                    'status_type' => 'generated',
                    'created_at' => $current_date_time
                );

                ExpenseLog::create($logdata);
                if ($request->hasFile('expense_file')) {
                    $files = $request->file('expense_file');
                    foreach ($files as $file) {
                        $customname = time() . '.' . $file->getClientOriginalExtension();
                        $expenses->addMedia($file)
                            ->usingFileName($customname)
                            ->toMediaCollection('expense_file', 'public');
                    }
                }

                return response()->json(['status' => 'success', 'message' => 'Data inserted successfully.', 'data' => $expenses], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'Error in No Record Found.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }


    public function expenseListing(Request $request)
    {
        try {
            $payroll_id = $request->payroll_id ?? '';
            $userids = getUsersReportingToAuth();
            $pageSize = $request->input('pageSize');
            // $query = Expenses::with('media','expense_type')->whereIn('user_id',$userids)->orderBy('id','desc');
            $query = Expenses::with('media', 'expense_type', 'users')->where(['user_id' => Auth::Id()])->orderBy('id', 'desc');
            $expence_types = ExpensesType::forPayroll($payroll_id ?: optional($request->user())->payroll)->get()->map(function($item) {
                return $this->expenseTypeData($item);
            })->toArray();
            //$expenses = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->get();
            if (!empty($request['start_date']) && !empty($request['end_date'])) {
                $query->whereBetween('date', [$request['start_date'], $request['end_date']]);
            }
            if (!empty($request['expenses_type'])) {
                $query->where('expenses_type',$request['expenses_type']);
            }
            if ((!empty($request['status']) || $request['status'] == 0) && $request['status'] != null) {
                $query->where('checker_status',$request['status']);
            }
            $all_status = [['id' => '0', 'name' => 'Pending'], ['id' => '1', 'name' => 'Approved'], ['id' => '2', 'name' => 'Rejected'], ['id' => '3', 'name' => 'Checked'] ,['id' => '4', 'name' => 'Checked By Reporting']];
            $expenses = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->paginate(100);
            $datas = array();
            if ($expenses->isNotEmpty()) {

               
                foreach ($expenses as $expense) {

                    // $image = '';  
                    // if(isset($expense) && $expense->getMedia('expense_file')->count() > 0 && file_exists($expense->getFirstMedia('expense_file')->getPath())){
                    //  $image = $expense->getFirstMedia('expense_file')->getFullUrl();
                    // }

                    $image = array();

                    $image = $this->expenseFileData($expense);


                    if ($expense->checker_status == '1') {
                        $exp_status = 'Approved';
                    } elseif ($expense->checker_status == '2') {
                        $exp_status = 'Rejected';
                    } elseif ($expense->checker_status == '3') {
                        $exp_status = 'Checked';
                    } elseif ($expense->checker_status == '4') {
                        $exp_status = 'Checked By Reporting';
                    } else {
                        $exp_status = 'Pending';
                    }


                    $datas[] = array(
                        'id' => $expense->id ?? "",
                        'expenses_type' => $expense->expenses_type ?? "",
                        'expenses_type_name' => $expense->expense_type->name ?? "",
                        'rate' => $this->expenseRate($expense),
                        'user_id' => $expense->user_id ?? "",
                        'date' => date("d-m-Y", strtotime($expense->date)),
                        'note' => $expense->note ?? "",
                        'start_km' => $expense->start_km ?? "",
                        'stop_km' => $expense->stop_km ?? "",
                        'total_km' => $expense->total_km ?? "",
                        'claim_amount' => $expense->claim_amount ?? NULL,
                        'approve_amount' => $expense->approve_amount ?? "",
                        'reason' => $expense->reason,
                        'status' => $exp_status,
                        // 'claim_amount' => '$'.number_format($expense->claim_amount ?? 0,2),
                        //'expense_image' =>  $expense->getFirstMedia('expense_file')->getFullUrl(),
                        'expense_image' =>  $image,

                    );
                }

                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $datas , 'expence_types' => $expence_types , 'all_status' => $all_status], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $datas , 'expence_types' => $expence_types , 'all_status' => $all_status], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function expenseDetails(Request $request)
    {
        try {
            $userid = $request->user()->id;
            $validator = Validator::make($request->all(), [
                'expense_id'  => "required",
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }

            $expense_id = $request->expense_id;
            $expense = Expenses::with('media', 'expense_type', 'users')->where('id', $expense_id)->first();
            if (!empty($expense)) {

                [$image, $image_id] = $this->expenseFileData($expense, true);


                if ($expense->checker_status == '1') {
                    $exp_status = 'Approved';
                } elseif ($expense->checker_status == '2') {
                    $exp_status = 'Rejected';
                } elseif ($expense->checker_status == '3') {
                    $exp_status = 'Checked';
                } elseif ($expense->checker_status == '4') {
                    $exp_status = 'Checked By Reporting';
                } else {
                    $exp_status = 'Pending';
                }

                // $datas[] = array(
                // 'id' => $expense->id ?? "",
                // 'expenses_type' => $expense->expenses_type ?? "",
                // 'expenses_type_name' => $expense->expense_type->name?? "",
                // 'user_id' => $expense->user_id ?? "",
                // 'date' => date("d-m-Y", strtotime($expense->date)),
                // 'note' => $expense->note ?? "",
                // 'start_km' => $expense->start_km ?? "",
                // 'stop_km' => $expense->stop_km ?? "",
                // 'total_km' => $expense->total_km ?? "",
                // 'claim_amount' => $expense->claim_amount ?? "",
                // 'approve_amount' => $expense->approve_amount ?? "",
                // 'status' => $exp_status,
                // 'expense_image' =>  $image,
                //    );

                $datas = array();
                $datas['id'] = $expense->id ?? "";
                $datas['expenses_type'] = $expense->expenses_type ?? "";
                $datas['expenses_type_name'] = $expense->expense_type->name ?? "";
                $datas['rate'] = $this->expenseRate($expense);
                $datas['allowance_type_id'] = $expense->expense_type->allowance_type_id ?? "";
                $datas['user_id'] = $expense->user_id ?? "";
                $datas['user_name'] = $expense->users->name ?? "";
                $datas['employee_code'] = $expense->users->employee_codes ?? $expense->users->emp_code ?? "";
                $datas['date'] = date("d-m-Y", strtotime($expense->date));
                $datas['note'] = $expense->note ?? "";
                $datas['start_km'] = $expense->start_km ?? "";
                $datas['stop_km'] = $expense->stop_km ?? "";
                $datas['total_km'] = $expense->total_km ?? "";
                $datas['claim_amount'] = $expense->claim_amount ?? "";
                $datas['expense_image'] = $image;
                $datas['image_id'] = $image_id;

                $datas['approve_amount'] = $expense->approve_amount ?? "";
                $datas['status'] = $exp_status;
                $datas['reason'] = $expense->reason ?? "";

                $plan = TourProgramme::with('city:id,city_name')
                    ->where('userid', $expense->user_id)
                    ->where('date', $expense->date)
                    ->first();

                if ($plan) {
                    $townId = $plan->town;
                    $plan->town_id = $townId;
                    $plan->town = $plan->city->city_name ?? '';
                    $plan->unsetRelation('city');
                }
                $total_visit = count(CheckIn::where('user_id', $expense->user_id)->where('checkin_date', $expense->date)->groupBy('customer_id')->get());

                $isPastExpense = Carbon::parse($expense->date)->startOfDay()->lt(Carbon::today());

                if ($expense->distance_calculated) {
                    $total_dis = (float) $expense->total_distance;
                } else {
                    $total_dis = $this->calculateExpenseDistance($expense);
                    $expense->total_distance = $total_dis;
                    $expense->distance_calculated = $isPastExpense;
                    $expense->save();
                }

                $datas['plan'] = $plan;
                $datas['total_visit'] = (string)$total_visit;
                $datas['total_dis'] = (string)number_format($total_dis, '2');


                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $datas], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $expense], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }


    public function updateExpense(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');

            $dates = Carbon::now();
            $current_date_time = $dates->setTimezone('Asia/Kolkata');

            $userid = $request->user()->id;
            $validator = Validator::make(
                $request->all(),
                [
                    'expense_id'  => "required",
                    'expense_file.*' => 'mimes:jpeg,jpg,png,pdf,doc,webp',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
            }

            $expense_detail = Expenses::where('id', $request['expense_id'])->first();
            $expenseTypeId = isset($request['expenses_type']) ? $request['expenses_type'] : $expense_detail->expenses_type;
            $rate = isset($request['expenses_type']) && (string) $request['expenses_type'] !== (string) $expense_detail->expenses_type
                ? $this->resolveExpenseRate($expenseTypeId, $request->rate)
                : $this->resolveExpenseRate($expenseTypeId, $request->rate ?? $expense_detail->rate);
            if ($expenses = Expenses::where('id', $request['expense_id'])->update([
                'user_id' => $userid,
                'expenses_type' => $expenseTypeId,
                'rate' => $rate,
                //'date' => isset($request['date'])? $request['date']:$expense_detail->date,
                'note' => isset($request['note']) ? $request['note'] : $expense_detail->note,
                'start_km' => isset($request['start_km']) ? $request['start_km'] : $expense_detail->start_km,
                'stop_km' => isset($request['stop_km']) ? $request['stop_km'] : $expense_detail->stop_km,
                'total_km' => isset($request['total_km']) ? $request['total_km'] : $expense_detail->total_km,
                'claim_amount' => isset($request['claim_amount']) ? $request['claim_amount'] : $expense_detail->claim_amount,
                'created_at' => $current_date_time
            ])) {

                $logdata = array(
                    'log_date' => date('Y-m-d'),
                    'expense_id' => $request['expense_id'],
                    'created_by' => $userid,
                    'status_type' => 'updated',
                    'created_at' => $current_date_time
                );

                ExpenseLog::create($logdata);

                if ($request->hasFile('expense_file')) {
                    $files = $request->file('expense_file');
                    foreach ($files as $file) {
                        $customname = time() . '.' . $file->getClientOriginalExtension();
                        $expense_detail->addMedia($file)
                            ->usingFileName($customname)
                            ->toMediaCollection('expense_file', 'public');
                    }
                }


                if (!empty($request->image_id)) {
                    Media::where('id', $request->image_id)->delete();
                }

                return response()->json(['status' => 'success', 'message' => 'Data updated successfully.'], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Updated.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function allExpenseListing(Request $request)
    {
        try {

            $user = $request->user();
            $user_id = $user->id;
            $userids = getUsersReportingToAuth($user_id);
            $search_branches = $request->input('search_branches');
            if ($search_branches && count($search_branches) > 0 && $search_branches[0] != null) {
                $userids = User::whereIn('id', $userids)->whereIn('branch_id', $search_branches)->pluck('id')->toArray();
            }
            $pageSize = $request->input('pageSize');
            $query = Expenses::with('media', 'expense_type', 'users')->orderBy('id', 'desc');
            $filterUser = !empty($request['user_id'])
                ? User::find($request['user_id'])
                : $user;
            $expence_types = ExpensesType::forPayroll(optional($filterUser)->payroll)->get()->map(function ($item) {
                return $this->expenseTypeData($item);
            })->toArray();
            // $query = Expenses::with('media','expense_type')->where(['user_id'=>Auth::Id()])->orderBy('id','desc');
            //$expenses = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->get();
            if (!empty($request['start_date']) && !empty($request['end_date'])) {
                $query->whereBetween('date', [$request['start_date'], $request['end_date']]);
            }
            if ((!empty($request['status']) || $request['status'] == 0) && $request['status'] != null) {
                $query->where('checker_status',$request['status']);
            }
            if (!empty($request['expenses_type'])) {
                $query->where('expenses_type', $request['expenses_type']);
            }
            if (!empty($request['user_id'])) {
                $query->where('user_id', $request['user_id']);
            } else {
                $query->whereIn('user_id', $userids);
            }
            // dd($query->toSql());
            $expenses = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->paginate(100);

            $all_status = [['id' => '0', 'name' => 'Pending'], ['id' => '1', 'name' => 'Approved'], ['id' => '2', 'name' => 'Rejected'], ['id' => '3', 'name' => 'Checked'] ,['id' => '4', 'name' => 'Checked By Reporting']];
            $datas = array();
            $all_user_branches = User::with('getbranch')->whereDoesntHave('roles', function ($query) {
                $query->where('id', 29);
            })->whereIn('id', getUsersReportingToAuth($user_id))->orderBy('branch_id')->get();
            $branches = array();
            $all_branch = array();
            $bkey = 0;
            foreach ($all_user_branches as $k => $val) {
                if ($val->getbranch) {
                    if (!in_array($val->getbranch->id, $all_branch)) {
                        array_push($all_branch, $val->getbranch->id);
                        $branches[$bkey]['id'] = $val->getbranch->id;
                        $branches[$bkey]['name'] = $val->getbranch->branch_name;
                        $bkey++;
                    }
                }
            }

            $all_user_details = User::with('getbranch')->whereDoesntHave('roles', function ($query) {
                $query->where('id', 29);
            })->whereIn('id', $userids)->orderBy('name', 'asc')->get();
            $all_users = array();
            foreach ($all_user_details as $k => $val) {
                $all_users[$k]['id'] = $val->id;
                $all_users[$k]['name'] = $val->name;
            }
            if ($expenses->isNotEmpty()) {

               
                foreach ($expenses as $expense) {
                    // $image = '';  
                    // if(isset($expense) && $expense->getMedia('expense_file')->count() > 0 && file_exists($expense->getFirstMedia('expense_file')->getPath())){
                    //  $image = $expense->getFirstMedia('expense_file')->getFullUrl();
                    // }

                    $image = array();

                    $image = $this->expenseFileData($expense);


                    if ($expense->checker_status == '1') {
                        $exp_status = 'Approved';
                    } elseif ($expense->checker_status == '2') {
                        $exp_status = 'Rejected';
                    } elseif ($expense->checker_status == '3') {
                        $exp_status = 'Checked';
                    } elseif ($expense->checker_status == '4') {
                        $exp_status = 'Checked By Reporting';
                    } else {
                        $exp_status = 'Pending';
                    }


                    $datas[] = array(
                        'id' => $expense->id ?? "",
                        'expenses_type' => $expense->expenses_type ?? "",
                        'expenses_type_name' => $expense->expense_type->name ?? "",
                        'rate' => $this->expenseRate($expense),
                        'user_id' => $expense->user_id ?? "",
                        'date' => date("d-m-Y", strtotime($expense->date)),
                        'note' => $expense->note ?? "",
                        'start_km' => $expense->start_km ?? "",
                        'stop_km' => $expense->stop_km ?? "",
                        'total_km' => $expense->total_km ?? "",
                        'claim_amount' => $expense->claim_amount ?? "",
                        'approve_amount' => $expense->approve_amount ?? "",
                        'reason' => $expense->reason,
                        'status' => $exp_status,
                        // 'claim_amount' => '$'.number_format($expense->claim_amount ?? 0,2),
                        //'expense_image' =>  $expense->getFirstMedia('expense_file')->getFullUrl(),
                        'expense_image' =>  $image,
                        'user_name' =>  $expense->users->name,
                        'self' =>  $expense->is_self,

                    );
                }

               

                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'users' => $all_users, 'branches' => $branches,'all_status'=> $all_status, 'expence_types' => $expence_types, 'data' => $datas], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.',  'users' => $all_users, 'branches' => $branches,'all_status'=> $all_status, 'expence_types' => $expence_types, 'data' => $datas , 'dummy' =>$request['status']], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function approveExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_id'  => "required",
            'approve_amnt'  => "required",
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
        }
        $dates = Carbon::now();
        $current_date_time = $dates->setTimezone('Asia/Kolkata');
        $expense_detail = Expenses::where('id', $request->expense_id)->first();
        $approve_amnt = $request->approve_amnt;
        $expense_id = $request->expense_id;
        $reason = $request->reasons ?? NULL;

        if ($expense_detail->claim_amount < $approve_amnt) {
            return response()->json(['status' => 'error', 'message' => 'Approve amount greater than to claim amount']);
        }

        Expenses::where('id', $expense_id)->update(['reason' => $reason, 'checker_status' => '4', 'approve_reject_by' => Auth::user()->id, 'approve_amount' => $approve_amnt]);

        if ($expense_id) {
            $logdata = array(
                'log_date' => date('Y-m-d'),
                'expense_id' => $expense_id,
                'created_by' => Auth::user()->id,
                'status_type' => 'approved'
            );
            ExpenseLog::create($logdata);
        }

        return response()->json(['status' => 'success', 'message' => 'Approve amount.']);
    }

    public function rejectExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_id'  => "required",
            'reasons'  => "required"
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' =>  $validator->errors()], $this->badrequest);
        }
        $dates = Carbon::now();
        $current_date_time = $dates->setTimezone('Asia/Kolkata');
        $expense_id = $request->expense_id;
        $reason = $request->reason ?? NULL;
        Expenses::where('id', $expense_id)->update(['reason' => $reason, 'checker_status' => '2', 'approve_reject_by' => Auth::user()->id, 'approve_amount' => NULL]);
        //return redirect(route('expenses.index')); 

        if ($expense_id) {
            $logdata = array(
                'log_date' => date('Y-m-d'),
                'expense_id' => $expense_id,
                'created_by' => Auth::user()->id,
                'status_type' => 'rejected'
            );
            ExpenseLog::create($logdata);
        }
        return response()->json(['status' => 'success', 'message' => 'Expense reject successfully']);
    }
}
