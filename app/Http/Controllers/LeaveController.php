<?php

namespace App\Http\Controllers;

use App\DataTables\LeaveDataTable;
use App\Models\Attendance;
use App\Models\CompOffLeave;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DateTime;
use Carbon\Carbon;
use App\Exports\LeavesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LeaveDataTable $dataTable)
    {
        abort_if(Gate::denies('leave_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $all_reporting_user_ids = getUsersReportingToAuth();
        $all_user_details = User::with('getbranch')->whereDoesntHave('roles', function ($query) {
            $query->where('id', 29);
        })->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
        $all_users = array();
        foreach ($all_user_details as $k => $val) {
            $users[$k]['id'] = $val->id;
            $users[$k]['name'] = $val->name;
        }
        return $dataTable->render('leave.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
        
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required',
    //             'from_date' => 'required|before_or_equal:to_date',
    //             'to_date' => 'required|after_or_equal:from_date',
    //             'type' => 'required',
    //             'bal_type' => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             return redirect()->back()
    //                 ->withErrors($validator)
    //                 ->withInput();
    //         }
    //         $fromDate = new DateTime($request->from_date);
    //         $toDate = new DateTime($request->to_date);

    //         $dates = [];
    //         $days = 0;
    //         $currentDate = clone $fromDate;
    //         while ($currentDate <= $toDate) {
    //             $days++;
    //             $dates[] = $currentDate->format('Y-m-d');
    //             $currentDate->modify('+1 day');
    //         }

    //         foreach ($dates as $date) {
    //             Attendance::updateOrCreate(['user_id' => $request['user_id'], 'punchin_date' => date('Y-m-d', strtotime($date))], [
    //                 'user_id' => $request['user_id'],
    //                 'active' => 'Y',
    //                 'punchin_date' => date('Y-m-d', strtotime($date)),
    //                 'punchin_time' => date('G:i', strtotime('10:00:00')),
    //                 'punchin_summary' => !empty($request['reason']) ? $request['reason'] : '',
    //                 'working_type' => !empty($request['type']) ? $request['type'] : '',
    //                 'punchin_from' => 'App',
    //                 'created_at' => getcurentDateTime(),
    //                 'updated_at' => getcurentDateTime(),
    //             ]);
    //         }

    //         $leave = Leave::create([
    //             'user_id' => $request['user_id'],
    //             'active' => 'Y',
    //             'from_date' => date('Y-m-d', strtotime($request['from_date'])),
    //             'to_date' => date('Y-m-d', strtotime($request['to_date'])),
    //             'reason' => !empty($request['reason']) ? $request['reason'] : '',
    //             'type' => !empty($request['type']) ? $request['type'] : '',
    //             'bal_type' => !empty($request['bal_type']) ? $request['bal_type'] : NULL,
    //             'created_by' => auth()->user()->id,
    //             'created_at' => getcurentDateTime(),
    //             'updated_at' => getcurentDateTime(),
    //         ]);

    //         if ($request['bal_type'] === 'Comp-off Balance') {
    //             if ($request['type'] == 'First Half Leave' || $request['type'] == 'Second Half Leave') {
    //                 $compOff = CompOffLeave::where('user_id', $request['user_id'])
    //                     ->where('is_used', false)
    //                     ->where('expiry_date', '>=', now())
    //                     ->first();
    //             } else {
    //                 $compOff = CompOffLeave::where('user_id', $request['user_id'])
    //                     ->where('is_used', false)
    //                     ->where('expiry_date', '>=', now())
    //                     ->where('balance', '>', 0.6)
    //                     ->get();
    //             }

    //             if ($compOff) {

    //                 if ($request['type'] == 'First Half Leave' || $request['type'] == 'Second Half Leave') {
    //                     $compOff->balance = $compOff->balance - 0.50;
    //                     if (!empty($compOff->leave_id)) {
    //                         $compOff->leave_id = $compOff->leave_id . ',' . $leave->id;
    //                     } else {
    //                         $compOff->leave_id = $leave->id;
    //                     }
    //                     $compOff->is_used = false;
    //                     $compOff->save();
    //                     if ($compOff->balance == 0.00) {
    //                         $compOff->update(['is_used' => true, 'balance' => 0.00]);
    //                     }
    //                 } else {
    //                     if ($compOff->count() >= $days) {
    //                         $compOff->take($days)->each(function ($comp) use ($leave) {
    //                             $comp->update([
    //                                 'is_used'  => true,
    //                                 'leave_id' => $leave->id,
    //                                 'balance'  => 0.00
    //                             ]);
    //                         });
    //                     } else {
    //                         $leave->delete();
    //                         foreach ($dates as $date) {
    //                             Attendance::where(['user_id' => $leave->user_id, 'punchin_date' => date('Y-m-d', strtotime($date))])->delete();
    //                         }
    //                         return redirect()->back()->with('message_danger', 'No Comp Off Balance');
    //                     }
    //                 }
    //             } else {
    //                 $leave->delete();
    //                 foreach ($dates as $date) {
    //                     Attendance::where(['user_id' => $leave->user_id, 'punchin_date' => date('Y-m-d', strtotime($date))])->delete();
    //                 }
    //                 return redirect()->back()->with('message_danger', 'No Comp Off Balance');
    //             }
    //         } else {
    //             if ($request['type'] == 'First Half Leave' || $request['type'] == 'Second Half Leave') {
    //                 $user = User::find($request['user_id']);
    //                 if($user->leave_balance >= 0.5) {
    //                     $user->leave_balance = $user->leave_balance - 0.5;
    //                 }else{
    //                     $user->leave_balance = 0;
    //                 }
    //                 $user->save();
    //             } elseif ($request['type'] == 'Full Day Leave' || $request['type'] == 'Leave') {
    //                 $user = User::find($request['user_id']);
    //                 if($user->leave_balance >= $days) {
    //                     $user->leave_balance = $user->leave_balance - $days;
    //                 }else {
    //                     $user->leave_balance = 0;
    //                 }
    //                 $user->save();
    //             }
    //         }

    //         return Redirect::to('leaves')->with('message_success', 'Leave Added Successfully');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors($e->getMessage())->withInput();
    //     }
    // }
    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id',
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'type'      => 'required|in:First Half Leave,Second Half Leave,Full Day Leave,Leave',
            'bal_type'  => 'required|in:Casual Leave,Comp-off Balance',
            'reason'    => 'nullable|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (
                in_array($request->type, ['First Half Leave', 'Second Half Leave'], true)
                && $request->from_date !== $request->to_date
            ) {
                $validator->errors()->add('to_date', 'Half-day leave must start and end on the same date.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        $user = User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();

        // Calculate number of days
        $from = Carbon::parse($request->from_date);
        $to   = Carbon::parse($request->to_date);
        $total_days = $from->diffInDays($to) + 1;

        // Determine how much to deduct
        $deduct_amount = $total_days;

        if (in_array($request->type, ['First Half Leave', 'Second Half Leave'])) {
            $deduct_amount = 0.5 * $total_days;
        }

        // ────────────────────────────────────────────────
        // Check balance & prepare deduction
        // ────────────────────────────────────────────────
        $enough_balance = false;
        $balance_column = null;

        switch ($request->bal_type) {
            case 'Casual Leave':
                $balance_column = 'casual_leave_balance';
                $enough_balance = $user->casual_leave_balance >= $deduct_amount;
                break;

            case 'Comp-off Balance':
                // Your existing comp-off check logic
                if ($request->type == 'First Half Leave' || $request->type == 'Second Half Leave') {
                    $compOff = CompOffLeave::where('user_id', $request->user_id)
                        ->where('is_used', false)
                        ->whereDate('expiry_date', '>=', now())
                        ->where('balance', '>=', 0.5)
                        ->orderBy('expiry_date')
                        ->lockForUpdate()
                        ->first();

                    $enough_balance = $compOff !== null;
                } else {
                    $compOffs = CompOffLeave::where('user_id', $request->user_id)
                        ->where('is_used', false)
                        ->whereDate('expiry_date', '>=', now())
                        ->where('balance', '>', 0)
                        ->lockForUpdate()
                        ->get();

                    $total_comp_off = $compOffs->sum('balance');
                    $enough_balance = $total_comp_off >= $deduct_amount;
                }
                break;
        }
       

        if (!$enough_balance) {
            DB::rollBack();
            return redirect()->back()
                ->with('message_danger', "Insufficient {$request->bal_type} balance.")
                ->withInput();
        }

        // ────────────────────────────────────────────────
        // Create attendance entries
        // ────────────────────────────────────────────────
        $dates = [];
        $current = clone $from;
        while ($current->lte($to)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        foreach ($dates as $date) {
            Attendance::updateOrCreate(
                ['user_id' => $user->id, 'punchin_date' => $date],
                [
                    'user_id'         => $user->id,
                    'active'          => 'Y',
                    'punchin_date'    => $date,
                    'punchin_time'    => '10:00:00',
                    'punchin_summary' => $request->reason ?: 'Leave',
                    'working_type'    => $request->type,
                    'punchin_from'    => 'App',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        // ────────────────────────────────────────────────
        // Create leave record
        // ────────────────────────────────────────────────
        $leave = Leave::create([
            'user_id'    => $user->id,
            'active'     => 'Y',
            'from_date'  => $from->format('Y-m-d'),
            'to_date'    => $to->format('Y-m-d'),
            'reason'     => $request->reason ?: '',
            'type'       => $request->type,
            'bal_type'   => $request->bal_type,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ────────────────────────────────────────────────
        // Deduct balance
        // ────────────────────────────────────────────────
        if ($request->bal_type === 'Comp-off Balance') {
            // ── Your existing Comp-off deduction logic ──
            if ($request->type == 'First Half Leave' || $request->type == 'Second Half Leave') {
                if ($compOff) {
                    $compOff->balance -= 0.5;
                    $compOff->leave_id = trim($compOff->leave_id . ',' . $leave->id, ',');
                    $compOff->is_used = ($compOff->balance <= 0);
                    $compOff->save();
                }
            } else {
                $remaining = $deduct_amount;
                $compOffs = CompOffLeave::where('user_id', $user->id)
                    ->where('is_used', false)
                    ->whereDate('expiry_date', '>=', now())
                    ->where('balance', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($compOffs as $comp) {
                    if ($remaining <= 0) break;

                    $use = min($remaining, $comp->balance);
                    $comp->balance -= $use;
                    $remaining -= $use;

                    $comp->leave_id = trim($comp->leave_id . ',' . $leave->id, ',');
                    $comp->is_used = ($comp->balance <= 0);
                    $comp->save();
                }

                if ($remaining > 0.00001) {
                    throw new \RuntimeException('Comp-off balance became insufficient during processing.');
                }
            }

            $user->compb_off = CompOffLeave::where('user_id', $user->id)
                ->where('is_used', false)
                ->whereDate('expiry_date', '>=', now())
                ->sum('balance');
            $user->save();
        } else {
            // Normal leave types — deduct from correct column
            $user->$balance_column -= $deduct_amount;

            // Prevent negative balance
            if ($user->$balance_column < 0) {
                $user->$balance_column = 0;
            }

            $user->save();
        }

        DB::commit();

        return redirect()->route('leaves.index')
            ->with('message_success', 'Leave added successfully.');

    } catch (\Exception $e) {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        return redirect()->back()
            ->with('message_danger', 'Error: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function show(Leave $leave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function edit(Leave $leave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Leave $leave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     // try {
    //         $leave = Leave::find($id);
    //         $fromDate = new DateTime($leave->from_date);
    //         $toDate = new DateTime($leave->to_date);
    //         $dates = [];
    //         $currentDate = clone $fromDate;
    //         $days = 0;
    //         while ($currentDate <= $toDate) {
    //             $days++;
    //             $dates[] = $currentDate->format('Y-m-d');
    //             $currentDate->modify('+1 day');
    //         }

    //         foreach ($dates as $date) {
    //             Attendance::where(['user_id' => $leave->user_id, 'punchin_date' => date('Y-m-d', strtotime($date))])->delete();
    //         }

    //         if ($leave->type == 'First Half Leave' || $leave->type == 'Second Half Leave') {
    //             if ($leave->bal_type == 'Comp-off Balance') {
    //                 $compOffs = CompOffLeave::whereRaw("FIND_IN_SET(?, leave_id)", [$id])->get();

    //                 foreach ($compOffs as $compOff) {
    //                     $compOff->balance += 0.50;

    //                     $leaveIds = explode(',', $compOff->leave_id);
    //                     $leaveIds = array_filter($leaveIds, fn($ids) => $ids != $id);
    //                     $compOff->leave_id = implode(',', $leaveIds);
    //                     $compOff->is_used = false;
    //                     $compOff->save();
    //                 }
    //             }else {
    //                 $user = User::find($leave->user_id);
    //                 $user->leave_balance = $user->leave_balance + 0.50;
    //                 $user->save();
    //             }
    //         } elseif ($leave->type == 'Full Day Leave' || $leave->type == 'Leave') {
    //             if ($leave->bal_type == 'Comp-off Balance') {
    //                 $compOffs = CompOffLeave::whereRaw("FIND_IN_SET(?, leave_id)", [$id])->get();
    //                 foreach ($compOffs as $compOff) {
    //                     if ($compOff) {
    //                         $compOff->balance = $compOff->balance + 1.00;
    //                         $compOff->is_used = false;
    //                         $compOff->save();
    //                     }
    //                 }
    //             }else{
    //                 $user = User::find($leave->user_id);
    //                 $user->leave_balance = $user->leave_balance + $days;
    //                 $user->save();
    //             }
    //         }

    //         if ($leave->delete()) {
    //             return response()->json(['status' => 'success', 'message' => 'Leave deleted successfully!']);
    //         }
    //         return response()->json(['status' => 'error', 'message' => 'Error in Attendance Delete!']);
    //     // } catch (\Exception $e) {
    //     //     return redirect()->back()->withErrors($e->getMessage())->withInput();
    //     // }
    // }
    public function destroy($id)
{
    $leave = Leave::findOrFail($id);

    $from = Carbon::parse($leave->from_date);
    $to   = Carbon::parse($leave->to_date);
    $total_days = $from->diffInDays($to) + 1;

    $refund_amount = $total_days;
    if (in_array($leave->type, ['First Half Leave', 'Second Half Leave'])) {
        $refund_amount = 0.5 * $total_days;
    }

    // Delete attendance records
    $current = clone $from;
    while ($current->lte($to)) {
        Attendance::where('user_id', $leave->user_id)
            ->where('punchin_date', $current->format('Y-m-d'))
            ->delete();
        $current->addDay();
    }

    $user = User::find($leave->user_id);

    if ($leave->bal_type === 'Comp-off Balance') {
        // Refund comp-off logic
        $compOffs = CompOffLeave::whereRaw("FIND_IN_SET(?, leave_id)", [$leave->id])->get();

        foreach ($compOffs as $compOff) {
            $compOff->balance += ($leave->type === 'First Half Leave' || $leave->type === 'Second Half Leave') ? 0.5 : 1.0;
            $leaveIds = array_filter(explode(',', $compOff->leave_id), fn($lid) => $lid != $leave->id);
            $compOff->leave_id = implode(',', $leaveIds);
            $compOff->is_used = false;
            $compOff->save();
        }

        if ($user) {
            $user->compb_off = CompOffLeave::where('user_id', $user->id)
                ->where('is_used', false)
                ->whereDate('expiry_date', '>=', now())
                ->sum('balance');
            $user->save();
        }
    } else {
        // Refund normal leave
        $column_map = [
            'Casual Leave' => 'casual_leave_balance',
        ];

        $column = $column_map[$leave->bal_type] ?? null;

        if ($column && $user) {
            $user->$column += $refund_amount;
            $user->save();
        }
    }

    $leave->delete();

    return response()->json([
        'status'  => 'success',
        'message' => 'Leave deleted successfully and balance refunded!'
    ]);
}

    public function approveLeave(Request $request)
    {
        try {
            if (Leave::where('id', '=', $request['id'])->update([
                'status' => 1,
                'remark_status' => null
            ])) {
                return redirect()->back()->with('message_success', 'Leave Approved Successfully');
            }
            return redirect()->back()->with('message_danger', 'Error in Leave Approved')->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }


    public function rejectLeave(Request $request)
    {
        $remark_status  = $request['remark_status'] ?? null;
        try {
            if (Leave::where('id', '=', $request['leave_id'])->update([
                'status' => 2,
                'remark_status' => $remark_status ?? null,
            ])) {
                return Redirect::to('leaves')->with('message_success', 'Leave Rejected Successfully');
            }
            return redirect()->back()->with('message_danger', 'Error in Leave Rejected')->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    // comboOffLeave 
    public function comboOffLeave(Request $request){
        $expiryDate = Carbon::parse($request['combo_off_date'])->addDays(60);
        $isSunday = Carbon::parse($request['combo_off_date'])->isSunday();
        if(!$isSunday){
            return redirect()->back()->with('message_danger', 'Combo of leave apply only on sunday.')->withInput();
        }
        $compOffLeave = CompOffLeave::where(['user_id' => $request->user_id , 'comp_off_date' => $request['combo_off_date']])->first();
        
        if(isset($compOffLeave)){
            return redirect()->back()->with('message_danger', 'This date has already been added as a comp-off date for this user.')->withInput();
        }
        CompOffLeave::create([
            'user_id' => $request['user_id'],
            'comp_off_date' => $request['combo_off_date'],
            'expiry_date' => $expiryDate,
            'is_used' => false,
        ]);
        return redirect()->route('leaves.index')->with('message_success', 'A comp-off date added for this user.')->withInput();
    }
public function export(Request $request)
{
    try {
        $filters = [
            'executive_id' => $request->get('executive_id'),
            'start_date'   => $request->get('start_date'),
            'end_date'     => $request->get('end_date'),
        ];

        $today = Carbon::today();

        // If no date range is selected → use last 60 days
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            $filters['start_date'] = $today->copy()->subDays(60)->format('Y-m-d');
            $filters['end_date']   = $today->format('Y-m-d');
        } else {
            // Validate and fix if start date is after end date
            $start = Carbon::parse($filters['start_date']);
            $end   = Carbon::parse($filters['end_date']);

            if ($start->gt($end)) {
                $filters['start_date'] = $end->format('Y-m-d');
                $filters['end_date']   = $start->format('Y-m-d');
            }
        }

        // Generate meaningful filename
        $filename = 'Leaves_Report_' . $filters['start_date'] . '_to_' . $filters['end_date'] . '.xlsx';

        return Excel::download(
            new LeavesExport($filters),
            $filename
        );

    } catch (\Exception $e) {
        // This will show real error instead of blank screen
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate Excel file',
            'error'   => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ], 500);
    }
}
    
}
