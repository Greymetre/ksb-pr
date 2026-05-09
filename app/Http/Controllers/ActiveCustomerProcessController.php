<?php

namespace App\Http\Controllers;

use App\Models\ActiveCustomerProcess;
use App\Models\ActiveCustomerProcessStep;
use App\Models\CustomerProcess;
use App\Models\CustomerProcessStep;
use App\Models\Customers;
use App\Models\EmployeeDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class ActiveCustomerProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users_ids = getUsersReportingToAuth();
        $customer_ids = EmployeeDetail::whereIn('user_id', $users_ids)->pluck('customer_id')->toArray();
        $customers = Customers::where('active', 'Y');
        if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('Admin')) {
            $customers->whereIn('id', $customer_ids);
        }
        $customers = $customers->select('id', 'name')->get();
        if ($request->ajax()) {
            $completedCustomerIds = ActiveCustomerProcess::select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) = 0', ['completed'])
                ->pluck('customer_id')
                ->toArray();

            $query = ActiveCustomerProcess::with([
                'customer:id,name,mobile,creation_date',
                'process:id,process_name',
                'assignedBy:id,name'
            ]);

            if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('Admin')) {
                $query->whereIn('customer_id', $customer_ids);
            }

            if ($request->customer_id && !empty($request->customer_id)) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->start_date && !empty($request->start_date)) {
                $query->whereHas('customer', function ($query) use ($request) {
                    $query->where('creation_date', '>=', $request->start_date);
                });
            }

            if ($request->end_date && !empty($request->end_date)) {
                $query->whereHas('customer', function ($query) use ($request) {
                    $query->where('creation_date', '<=', $request->end_date);
                });
            }

            if ($request->status == 'closed') {
                $query->whereIn('customer_id', $completedCustomerIds);
            } else if ($request->status == 'active') {
                $query->whereNotIn('customer_id', $completedCustomerIds);
            }

            $query = $query->select('active_customer_processes.*');

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('action', function ($row) {
                    // $view = '<a href="' . route('active_customer_process.show', $row->id) . '" class="btn btn-sm btn-info">View</a>';
                    if (auth()->user()->can(['active_process_delete'])) {
                        $delete = '<button class="btn btn-sm btn-danger delete" data-id="' . $row->id . '" title="Delete Active Process"><i class="material-icons">delete</i></button>';
                        return $delete;
                    }
                })

                ->editColumn('customer.creation_date', function ($row) {
                    $date = $row->customer->creation_date ?? null;

                    if (!empty($date) && strtotime($date)) {
                        return date('d M Y', strtotime($date));
                    }

                    return '';
                })

                ->addColumn('steps', function ($row) {
                    return '<button class="btn btn-sm btn-info steps" data-id="' . $row->id . '" title="Steps"><i class="material-icons">list</i></button>';
                })
                ->rawColumns(['action', 'steps'])
                ->make(true);
        }
        return view('active_customer_process.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_if(Gate::denies('active_process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $customers = Customers::where('active', 'Y')->select('id', 'name')->get();
        $processes = CustomerProcess::with('steps')->get();
        $active_process = new ActiveCustomerProcess();
        return view('active_customer_process.create', compact('customers', 'processes'))->with('active_process', $active_process);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('active_process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'process_id' => 'required|array',
            'process_id.*' => 'required|exists:customer_processes,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->process_id as $process_id) {
                $activeProcess = ActiveCustomerProcess::updateOrCreate([
                    'customer_id' => $request->customer_id,
                    'process_id' => $process_id,
                ], [
                    'assigned_by' => auth()->id(),
                    'status' => 'pending',
                ]);
                $processSteps = CustomerProcessStep::where('customer_process_id', $process_id)->get();
                foreach ($processSteps as $step) {
                    ActiveCustomerProcessStep::updateOrCreate([
                        'active_customer_process_id' => $activeProcess->id,
                        'customer_process_step_id' => $step->id,
                    ], [
                        'status' => 'pending',
                    ]);
                }
            }
        });
        return redirect()->route('active_customer_process.index')->with('success', 'Process assigned successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActiveCustomerProcess  $activeCustomerProcess
     * @return \Illuminate\Http\Response
     */
    public function show(ActiveCustomerProcess $activeCustomerProcess)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ActiveCustomerProcess  $activeCustomerProcess
     * @return \Illuminate\Http\Response
     */
    public function edit(ActiveCustomerProcess $activeCustomerProcess)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ActiveCustomerProcess  $activeCustomerProcess
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ActiveCustomerProcess $activeCustomerProcess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ActiveCustomerProcess  $activeCustomerProcess
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActiveCustomerProcess $activeCustomerProcess)
    {
        abort_if(Gate::denies('active_process_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden, You do not have permission to delete this process!');
        if ($activeCustomerProcess->delete()) {
            return response()->json(['status'  => 'success', 'message' => 'Active Process deleted successfully!']);
        }
        return response()->json(['status'  => 'error', 'message' => 'Error in Process Delete!']);
    }

    public function getActiveProcessSteps(ActiveCustomerProcess $activeCustomerProcess)
    {
        return response()->json(['status' => 'success', 'steps' => $activeCustomerProcess->steps->load('step', 'completedByUser')]);
    }

    public function completeProcessStep(ActiveCustomerProcessStep $step, Request $request)
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
            'status' => 'required|in:pending,completed',
        ]);

        $step->update([
            'status' => $request->status,
            'completed_at' => $request->status == 'completed' ? $request->completed_at : null,
            'remark' => $request->remarks,
        ]);

        $process = $step->activeProcess; // assuming you have a relation in step model

        $allCompleted = $process->steps()->where('status', '!=', 'completed')->count() === 0;

        if ($allCompleted) {
            $process->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }else{
            $process->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Step marked as completed successfully.',
        ]);
    }

    public function updateRemark(Request $request, ActiveCustomerProcessStep $step)
    {
        $request->validate([
            'remark' => 'required|string|max:255',
        ]);

        $step->remark = $request->remark;
        $step->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Remark updated successfully.',
            'remark' => $step->remark,
        ]);
    }
}
