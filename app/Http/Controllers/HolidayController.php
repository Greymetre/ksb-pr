<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Branch;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DataTables;
use Validator;
use Gate;
use Carbon\Carbon;


class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct() 
    {     
        $this->middleware('auth');   
        $this->holidays = new Holiday();
        $this->path = 'holidays';
    }


    public function index(Request $request)
    {

     $userids = getUsersReportingToAuth();
        if ($request->ajax()) {
            $data = Holiday::with('createdbyname', 'branches')->latest();
            return Datatables::of($data)
                    ->addIndexColumn()
                        ->editColumn('created_at', function($data)
                        {
                            return  date("Y", strtotime($data->created_at));
                        })
                        ->addColumn('branch_names', function ($holiday) {
                            return $holiday->branches->pluck('branch_name')->implode(', ')
                                ?: optional($holiday->getbranch)->branch_name;
                        })
                        ->addColumn('action', function ($query) {
                              $btn = '';
                              $activebtn ='';  
                              // if(auth()->user()->can(['holiday_edit']))
                              // {
                                $btn = $btn.'<a href="'.url("holidays/".encrypt($query->id).'/edit') .'" class="btn btn-info btn-just-icon btn-sm" title="'.trans('panel.global.edit').' '.trans('panel.holidays.title_singular').'">
                                                <i class="material-icons">edit</i>
                                            </a>';
                             // }

                              // if(auth()->user()->can(['holiday_delete']))
                              // {
                                $btn = $btn.' <a href="" class="btn btn-danger btn-just-icon btn-sm delete" value="'.$query->id.'" title="'.trans('panel.global.delete').' '.trans('panel.holidays.title_singular').'">
                                            <i class="material-icons">clear</i>
                                          </a>';
                              //}
                    
                              return '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                                            '.$btn.'
                                        </div>'.$activebtn;
                        })
                        ->rawColumns(['action'])
                    ->make(true);
        }
       return view('holidays.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {  
        $branches = Branch::get();
       return view('holidays.form',compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
           // 'branch' => 'required',
            'holiday_date' => 'required|array|min:1',
            'holiday_date.*' => 'required|date',
            'name' => 'required|array|min:1',
            'name.*' => 'required|string',
            'branch' => 'required|array|min:1',
            'branch.*' => 'required|integer|distinct|exists:branches,id',
           
        ]);
        $validator->after(function ($validator) use ($request) {
            if (count($request->input('holiday_date', [])) !== count($request->input('name', []))) {
                $validator->errors()->add('name', 'Each holiday date must have a holiday name.');
            }
        });
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
         $holiday_date = collect($request->holiday_date)
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->all();
         if (!empty($holiday_date)) {
         $holiday_date = implode(",", $holiday_date);
         } else {
         $holiday_date = '';
         }

         $name = $request->name;
         if (!empty($name)) {
         $name = implode(",", $name);
         } else {
         $name = '';
         }

        DB::transaction(function () use ($request, $name, $holiday_date) {
            $holiday = Holiday::create([
                'branch' => $request->branch[0],
                'name' => $name,
                'holiday_date' => $holiday_date,
                'created_by' => Auth::id(),
                'active'=> 'Y'
            ]);

            $holiday->branches()->sync($request->branch);
        });

        return redirect(route('holidays.index'))->with('message', 'Holiday added successfully');
  

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = decrypt($id); 
        $branches = Branch::get();
        $holidays = Holiday::with('branches')->findOrFail($id);
        return view('holidays.edit',compact('branches','holidays'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'branch' => 'required|array|min:1',
            'branch.*' => 'required|integer|distinct|exists:branches,id',
            'holiday_date' => 'required|array|min:1',
            'holiday_date.*' => 'required|date',
            'name' => 'required|array|min:1',
            'name.*' => 'required|string',
           
        ]);
        $validator->after(function ($validator) use ($request) {
            if (count($request->input('holiday_date', [])) !== count($request->input('name', []))) {
                $validator->errors()->add('name', 'Each holiday date must have a holiday name.');
            }
        });
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

         $holiday_date = collect($request->holiday_date)
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->all();
         if (!empty($holiday_date)) {
         $holiday_date = implode(",", $holiday_date);
         } else {
         $holiday_date = '';
         }

         $name = $request->name;
         if (!empty($name)) {
         $name = implode(",", $name);
         } else {
         $name = '';
         }

     
        $holidays = Holiday::find($id);
        if($holidays){
            $holidays->branch = $request->branch[0];
            $holidays->name = $name;
            $holidays->holiday_date = $holiday_date;
            $holidays->updated_by = Auth::id();
            $holidays->active = 'Y';
            DB::transaction(function () use ($holidays, $request) {
                $holidays->save();
                $holidays->branches()->sync($request->branch);
            });
            return redirect(route('holidays.index'))->with('message', 'Holiday updated successfully');
        }else{
            return redirect(route('holidays.index'))->with('error', 'Somthing went wroung');
        }

        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {  
         $holiday = Holiday::find($id);
        if($holiday->delete())
        {
            return response()->json(['status' => 'success','message' => 'Holiday deleted successfully!']);
        }
        return response()->json(['status' => 'error','message' => 'Error in Holiday Delete!']);
    }
}
