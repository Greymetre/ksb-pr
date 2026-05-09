<?php

namespace App\Http\Controllers;

use App\Models\CustomerCustomField;
use App\Models\CustomerCustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CustomerCustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CustomerCustomField::with('creatbyname')->orderBy('id', 'desc');
            return datatables()->eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function ($query) {
                    $btn = '';
                    if (auth()->user()->can(['customer_custom_field_edit'])) {
                        $btn = $btn . '<a href="' . url("customer-custom-fields/" . $query->id . '/edit') . '" class="btn btn-info btn-just-icon btn-sm" title="' . trans('panel.global.edit') . ' Cusrom Field">
                                      <i class="material-icons">edit</i>
                                  </a>';
                    }
                    if (auth()->user()->can(['customer_custom_field_delete'])) {
                        $btn = $btn . ' <a href="javascript:void(0)" class="btn btn-danger btn-just-icon btn-sm delete" value="' . $query->id . '" title="' . trans('panel.global.delete') . ' Cusrom Field">
                                  <i class="material-icons">clear</i>
                                </a>';
                    }
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                                  ' . $btn . '
                              </div>';
                })
                ->make(true);
        }
        return view('customer-custom-fields.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $fields = new CustomerCustomField();
        return view('customer-custom-fields.create')->with('fields', $fields);
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
            //abort_if(Gate::denies($useraccess), Response::HTTP_FORBIDDEN, '403 Forbidden');
            $request['created_by'] = Auth::user()->id;
            if ($fields = CustomerCustomField::create($request->except(['_token']))) {
                if ($request['details']) {
                    foreach ($request['details'] as $key => $value) {
                        if (!empty($value['value'])) {
                            CustomerCustomFieldValue::create([
                                'custom_field_id' => $fields['id'],
                                'value' => $value['value'],
                            ]);
                        }
                    }
                }
                return Redirect::to('customer-custom-fields')->with('message_success', 'Field Store Successfully');
            }
            return redirect()->back()->with('message_danger', 'Error in Data Store')->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerCustomField  $customerCustomField
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerCustomField $customerCustomField)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerCustomField  $customerCustomField
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerCustomField $customerCustomField)
    {
        return view('customer-custom-fields.create')->with('fields', $customerCustomField);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerCustomField  $customerCustomField
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerCustomField $customerCustomField)
    {
        try {
            if (CustomerCustomField::where('id', $customerCustomField->id)->update($request->except(['_token', '_method', 'image', 'details']))) {
                if ($request['details']) {
                    $fieldValue = array();
                    foreach ($request['details'] as $key => $value) {
                        if (!empty($value['value'])) {
                            CustomerCustomFieldValue::updateOrCreate(
                                ['custom_field_id' => $customerCustomField->id, 'value' => $value['value']],
                                [
                                    'custom_field_id' => $customerCustomField->id,
                                    'value' => $value['value']
                                ]
                            );
                            array_push($fieldValue, $value['value']);
                        }
                    }
                    CustomerCustomFieldValue::where('custom_field_id', $customerCustomField->id)->whereNotIn('value', $fieldValue)->delete();
                }

                return Redirect::to('customer-custom-fields')->with('message_success', 'Data Update Successfully');
            }
            return redirect()->back()->with('message_danger', 'Error in Data Store')->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerCustomField  $customerCustomField
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerCustomField $customerCustomField)
    {
        $customerCustomField->values()->delete();
        if($customerCustomField->delete())
        {
            return response()->json(['status' => 'success','message' => 'Field deleted successfully!']);
        }
        return response()->json(['status' => 'error','message' => 'Error in User Delete!']);
    }
}
