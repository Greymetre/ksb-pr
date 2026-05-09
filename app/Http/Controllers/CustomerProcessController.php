<?php

namespace App\Http\Controllers;

use App\Models\CustomerProcess;
use App\Models\CustomerProcessStep;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class CustomerProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('process_access'), Response::HTTP_FORBIDDEN, '403 Forbidden, You do not have permission to create this process!');
        if ($request->ajax()) {
            $data = CustomerProcess::with('creatbyname')->orderBy('id', 'desc');
            return datatables()->eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function ($query) {
                    $btn = '';
                    if (auth()->user()->can(['process_edit'])) {
                        $btn = $btn . '<a href="' . url("customer_process/" . $query->id . '/edit') . '" class="btn btn-info btn-just-icon btn-sm" title="' . trans('panel.global.edit') . ' Process">
                                      <i class="material-icons">edit</i>
                                  </a>';
                    }
                    if (auth()->user()->can(['process_delete'])) {
                        $btn = $btn . '<a href="javascript:void(0)" class="btn btn-danger btn-just-icon btn-sm delete" value="' . $query->id . '" title="' . trans('panel.global.delete') . ' Process">
                                  <i class="material-icons">clear</i>
                                </a>';
                    }
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                                  ' . $btn . '
                              </div>';
                })
                ->make(true);
        }
        return view('customer_process.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $process = new CustomerProcess();
        return view('customer_process.create')->with('process', $process);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            abort_if(Gate::denies('process_create'), Response::HTTP_FORBIDDEN, '403 Forbidden, You do not have permission to create this process!');
            $validator = Validator::make($request->all(), [
                'process_name' => 'required|string',
                'steps' => 'nullable|array',
                'steps.*.value' => 'required',
                'steps.*.sort_order' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $request['created_by'] = Auth::user()->id;
            if ($process = CustomerProcess::create($request->except(['_token']))) {
                if ($request['steps']) {
                    foreach ($request['steps'] as $key => $value) {
                        if (!empty($value['value'])) {
                            CustomerProcessStep::create([
                                'customer_process_id' => $process['id'],
                                'value' => $value['value'],
                                'sort_order' => $value['sort_order'],
                            ]);
                        }
                    }
                }
                return Redirect::to('customer_process')->with('message_success', 'Process Store Successfully');
            }
            return redirect()->back()->with('message_danger', 'Error in Data Store')->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerProcess  $customerProcess
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerProcess $customerProcess)
    {
        $customerProcess->load('steps', 'creatbyname');
        dd($customerProcess, 'show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerProcess  $customerProcess
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerProcess $customerProcess)
    {
        abort_if(Gate::denies('process_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden, You do not have permission to edit this process!');
        $process = $customerProcess;
        return view('customer_process.create')->with('process', $process);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerProcess  $customerProcess
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerProcess $customerProcess)
    {
        abort_if(Gate::denies('process_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden, You do not have permission to edit this process!');
        $request->validate([
            'process_name' => 'required|string',
            'steps' => 'nullable|array',
            'steps.*.value' => 'required',
            'steps.*.sort_order' => 'required|numeric',
        ]);
        try {
            $customerProcess->update($request->except(['_token', '_method', 'steps']));
            if ($request->has('steps')) {
                $stepValues = [];
                foreach ($request->steps as $step) {
                    if (!empty($step['value'])) {
                        CustomerProcessStep::updateOrCreate(
                            ['customer_process_id' => $customerProcess->id, 'value' => $step['value']],
                            ['sort_order' => $step['sort_order']]
                        );
                        $stepValues[] = $step['value'];
                    }
                }
                if(!empty($stepValues) && count($stepValues) > 0){
                    CustomerProcessStep::where('customer_process_id', $customerProcess->id)
                        ->whereNotIn('value', $stepValues)
                        ->delete();
                }
            }
            return redirect()->route('customer_process.index')->with('message_success', 'Data updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerProcess  $customerProcess
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerProcess $customerProcess)
    {
        abort_if(Gate::denies('process_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden, You do not have permission to delete this process!');
        if ($customerProcess->delete()) {
            return response()->json(['status'  => 'success', 'message' => 'Process deleted successfully!']);
        }
        return response()->json(['status'  => 'error', 'message' => 'Error in Process Delete!']);
    }
}
