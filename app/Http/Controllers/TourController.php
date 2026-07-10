<?php

namespace App\Http\Controllers;

use App\Models\TourProgramme;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use DataTables;
use Validator;
use Gate;
use Excel;
use App\DataTables\TourProgrammeDataTable;
use App\Models\TourDetail;
use App\Models\User; 
use App\Models\City;
use App\Models\TourLog;
use App\Imports\TourImport;
use App\Exports\TourExport;
use App\Models\Division;
use App\Models\UserCityAssign;
use App\Models\Designation;

class TourController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() 
    {     
        $this->middleware('auth');   
        $this->tours = new TourProgramme();
        
    }
    
    // public function index(TourProgrammeDataTable $dataTable)
    // {
    //     //abort_if(Gate::denies('tour_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    //     $userids = getUsersReportingToAuth();
    //     $users = User::where(function($query) use($userids){
    //                             if(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin'))
    //                             {
    //                                 $query->whereIn('id',$userids);
    //                             }
    //                         })->select('id','name')->orderBy('id','desc')->get();
    //     return $dataTable->render('tours.index',compact('users'));
    // }

    private function addTourLog($tourId, $action, $status, $remark = null)
    {
        TourLog::create([
            'tour_programme_id' => $tourId,
            'action'            => $action,
            'status'            => $status,
            'performed_by'      => auth()->id(),
            'remark'            => $remark,
        ]);
    }


        public function index(Request $request)
    {
        ////abort_if(Gate::denies('customer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');


        $search_branches = $request->input('search_branches');
        $all_reporting_user_ids = getUsersReportingToAuth();
        $all_user_branches = User::with('getbranch')->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
        $branches = array();
        $all_branch = array();
        $divisions = Division::where('active', 'Y')->get();
        $bkey = 0;
        $designations = Designation::all();
        foreach ($all_user_branches as $k => $val) {
            if($val->getbranch){
                if(!in_array($val->getbranch->id, $all_branch)){
                    array_push($all_branch, $val->getbranch->id);
                    $branches[$bkey]['id'] = $val->getbranch->id;
                    $branches[$bkey]['name'] = $val->getbranch->branch_name;
                    $bkey++;
                }
            }
        }
        if ($search_branches && count($search_branches) > 0 && $search_branches[0] != null) {
            $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)->whereIn('branch_id', $search_branches)->pluck('id')->toArray();
        }
        $all_user_details = User::with('getbranch')->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
        $all_users = array();
        foreach ($all_user_details as $k => $val) {
            $users[$k]['id'] = $val->id;
            $users[$k]['name'] = $val->name;
        
        }
        if($search_branches && count($search_branches) > 0 && $search_branches[0] != null) {
            if ($request->ajax()) {
                $response = ["users"=>$users, "status"=>true];
                return response()->json($response);
            }
        }


        $userids = getUsersReportingToAuth();
        // $userids = getUsersReportingToAuth();
        $users = User::where(function($query) use($userids){
                                if(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('subAdmin'))
                                {
                                    $query->whereIn('id',$userids);
                                }
                            })->whereDoesntHave('roles', function ($query) {
                                $query->where('id', 29);
                            })->select('id','name')->orderBy('id','desc')->get();

    
        if ($request->ajax()) {
             // $data = TourProgramme::with('customertypes','firmtypes','createdbyname')
               $data = TourProgramme::with('userinfo', 'city', 'districtRelation')->where(function ($query) use ($request , $all_reporting_user_ids) {
                            if(!empty($request['executive_id']))
                            {
                                $query->where('userid', $request['executive_id']);
                            }
                            if(!empty($request['division_id']))
                            {
                                $userIds = User::where('division_id', $request['division_id'])->pluck('id');
                                $query->whereIn('userid', $userIds);
                            }

                            if(!empty($request['start_date']) && !empty($request['end_date']))
                            {
                              $query->whereBetween('date',[$request['start_date'],$request['end_date']]); 
                            }

                          
                            if(!empty($request['search']) && is_array($request['search']) == false){
                                $search = $request['search'] ;
                                $query->where(function($query) use($search) {
                                    $query->where('town', 'like', "%{$search}%")
                                    ->Orwhere('objectives', 'like', "%{$search}%")
                                    ->Orwhere('type', 'like', "%{$search}%");
                                });
                            }

                            if ($request->designation_id) {
                                $query->whereHas('userinfo', function ($q) use ($request) {
                                    $q->whereIn('designation_id', $request->designation_id);
                                });
                            }

                            if(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('subAdmin'))
                            {
                                $query->whereIn('userid',$all_reporting_user_ids);
                            }
                            
                        })->orderBy('created_at', 'desc');
                        // ->orderBy(DB::raw('YEAR(date)'), 'DESC')->orderBy(DB::raw('DATE(date)'), 'ASC');
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($item) {
                        return '<input type="checkbox" id="manual_entry_'.$item->id.'" class="manual_entry_cb checked_all" value="'.$item->id.'" />';
                        })
                        ->editColumn('created_at', function($data)
                        {
                            return isset($data->created_at) ? showdatetimeformat($data->created_at) : '';
                        })
                        ->addColumn('stauts', function ($query) {
                            if($query->status == '0'){
                                $btn = ' <button type="button" data-status="0" class="btn btn-warning btn-just-icon btn-sm change_status" value="'.$query->id.'" title="Change Status(Pending)">
                                 <i class="material-icons">pending</i>
                                </button>';
                            }elseif($query->status == '1'){
                                 $btn = ' <button type="button" data-status="1" class="btn btn-success btn-just-icon btn-sm change_status" value="'.$query->id.'" title="Change Status(Approved)">
                                 <i class="material-icons">approval</i>
                                 </button>';
                            }else{
                                 $btn = ' <button type="button" data-status="2" class="btn btn-danger btn-just-icon btn-sm change_status" value="'.$query->id.'" title="Change Status(Rejected)">
                                 <i class="material-icons">circle</i>
                                 </button>';
                            }

                            return $btn;
                        })
                        ->addColumn('action', function ($query) {
                              $btn = '';
                              $activebtn ='';
                              // if(auth()->user()->can(['tour_edit']))
                              // {

                               $btn = $btn.'<a href"javascript:void(0)" class="btn btn-info btn-just-icon btn-sm edit" id="'.encrypt($query->id).'" title="'.trans('panel.global.edit').' '.trans('panel.category.title_singular').'">
                               <i class="material-icons">edit</i>
                                </a>';


                              // }
                              // if(auth()->user()->can(['customer_show']))
                              // {
                              //   $btn = $btn.'<a href="'.url("customers/".encrypt($query->id)).'" class="btn btn-theme btn-just-icon btn-sm" title="'.trans('panel.global.show').' '.trans('panel.customers.title_singular').'">
                              //                   <i class="material-icons">visibility</i>
                              //               </a>';
                              // }
                              // if(auth()->user()->can(['tour_delete']))
                              // {

                                $btn = $btn.' <a href="" class="btn btn-danger btn-just-icon btn-sm delete" value="'.$query->id.'" title="Delete Tour Plan">
                                <i class="material-icons">clear</i>
                               </a>';

                            //    if($query->status == '0'){
                            //        $btn = $btn.' <button type="button" data-status="0" class="btn btn-warning btn-just-icon btn-sm change_status" value="'.$query->id.'" title="Change Status(Pending)">
                            //         <i class="material-icons">pending</i>
                            //        </button>';
                            //    }elseif($query->status == '1'){
                            //         $btn = $btn.' <button type="button" data-status="1" class="btn btn-success btn-just-icon btn-sm change_status" value="'.$query->id.'" title="Change Status(Approved)">
                            //         <i class="material-icons">approval</i>
                            //         </button>';
                            //    }else{
                            //         $btn = $btn.' <button type="button" data-status="2" class="btn btn-danger btn-just-icon btn-sm change_status" value="'.$query->id.'" title="Change Status(Rejected)">
                            //         <i class="material-icons">circle</i>
                            //         </button>';
                            //    }


                              //}
                               
                              // if(auth()->user()->can(['customer_active']))
                              // {
                              //   $active = ($query->active == 'Y') ? 'checked="" value="'.$query->active.'"' : 'value="'.$query->active.'"';
                              //   $activebtn = '<div class="togglebutton">
                              //               <label>
                              //                 <input type="checkbox"'.$active.' id="'.$query->id.'" class="customerActive">
                              //                 <span class="toggle"></span>
                              //               </label>
                              //             </div>';
                              // }

                              $btn = $btn.'
                                <a href="'.route('tour.activities', $query->id).'" 
                                class="btn btn-primary btn-just-icon btn-sm" 
                                title="View Activities">
                                    <i class="material-icons">timeline</i>
                                </a>';

                              return '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                                            '.$btn.'
                                        </div>'.$activebtn;
                        })

                
                        ->rawColumns(['action', 'stauts','checkbox'])
                    ->make(true);
        }
      
       // return $dataTable->render('tours.index',compact('users','branches'));
        return view('tours.index', compact('users','branches', 'divisions','designations'));
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $userids = getUsersReportingToAuth();
        $users = User::where(function($query) use($userids){
                                if(!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin'))
                                {
                                    $query->whereIn('id',$userids);
                                }
                            })->whereDoesntHave('roles', function ($query) {
                                $query->where('id', 29);
                            })->select('id','name')->orderBy('id','desc')->get();
        return view('tours.create',compact('users'))->with('tours',$this->tours);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function store(Request $request)

{
    $request->validate([
        'detail'                 => 'required|array|min:1',
        'detail.*.date'          => 'required|date',
        'detail.*.userid'        => 'required|exists:users,id',
        'detail.*.district'      => 'nullable|string|max:100',     // string name
        'detail.*.city'          => 'required|string|max:100',     // string name
        'detail.*.objectives'    => 'nullable|string|max:500',
    ]);

        // $cityId = City::where('city_name', $data['city'])->value('id');
// $districtId = \App\Models\District::where('district_name', $data['district'])->value('id');


    foreach ($request->detail as $data) {

        $tour = TourProgramme::updateOrCreate(
            [
                'date'   => $data['date'],
                'userid' => $data['userid'],
            ],
            [
                'date'       => $data['date'],
                'userid'     => $data['userid'],
                'town'       => $data['city']     ?? null,      // ← city name (string)
                'district'   => $data['district'] ?? null,      // ← district name (string)
                'objectives' => $data['objectives'] ?? null,
                'status'     => 0,  // or your default value
            ]
        );

        $this->addTourLog(
            $tour->id,
            'created',
            'pending',
            'Tour created'
        );


        // Optional: still try to link TourDetail if city name exists
        $city = City::where('city_name', trim($data['city'] ?? ''))->first();

        if ($city) {
            TourDetail::updateOrCreate(
                [
                    'tourid'  => $tour->id,
                    'city_id' => $city->id,
                ],
                [
                    'tourid'      => $tour->id,
                    'city_id'     => $city->id,
                    // 'last_visited' => null,
                ]
            );
        }
    }

    return redirect()
        ->route('tours.index') // or wherever you want
        ->with('success', 'Tour programme created successfully.');
}

public function update(Request $request)
{
    $id = $request->input('id');
    if (!$id) {
        return back()->with('error', 'Tour ID missing');
    }

    $tour = TourProgramme::findOrFail($id);

    $tour->update([
        'date'       => $request->input('date'),
        'userid'     => $request->input('userid'),
        'town'       => $request->input('town'),       // ← hidden field = city ID
        'district'   => $request->input('district'),   // ← hidden field = district ID
        'objectives' => $request->input('objectives', ''),
    ]);

    // Optional: update TourDetail if needed
    if ($tour->town) {
        $lastVisited = TourDetail::where('city_id', $tour->town)
            ->whereHas('tourinfo', fn($q) => $q->where('userid', $tour->userid))
            ->whereNotNull('visited_date')
            ->latest('visited_date')
            ->value('visited_date');

            $this->addTourLog($tour->id, 'updated', 'pending', 'Tour updated');

        TourDetail::updateOrCreate(
            [
                'tourid'  => $tour->id,
                'city_id' => $tour->town,
            ],
            [
                'last_visited' => $lastVisited,
            ]
        );
    }

    return redirect()->route('tours.index')
        ->with('success', 'Tour updated successfully');
}

   public function show($id)
{
    $id = decrypt($id);
    $tour = TourProgramme::findOrFail($id);
    
return response()->json([
        'id'            => $tour->id,
        'date'          => $tour->date,
        'userid'        => $tour->userid,
        'district'      => $tour->district,                         // keep ID (for hidden field)
        'district_name' => $tour->districtRelation?->district_name ?? '—',
        'town'          => $tour->town,                             // keep ID
        'town_name'     => $tour->cityRelation?->city_name ?? '—',
        'objectives'    => $tour->objectives,
        'objective_options' => config('constants.tour_objectives', []),
    ]);
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
    $tour = TourProgramme::with(['userinfo', 'cityRelation', 'districtRelation'])->findOrFail($id);

    $districtName = $tour->districtRelation ? $tour->districtRelation->district_name : '';
    $cityName     = $tour->cityRelation ? $tour->cityRelation->city_name : '';

    return response()->json([
        'id'         => $tour->id,
        'date'       => $tour->date,
        'userid'     => $tour->userid,
        'district'   => $tour->district,      // still keep ID
        'district_name' => $districtName,     // name for dropdown
        'town'       => $tour->town,          // still keep ID
        'town_name'  => $cityName,            // name for dropdown
        'objectives' => $tour->objectives,
        'objective_options' => config('constants.tour_objectives', []),
    ]);
}

    public function destroy($id)
    {
        //abort_if(Gate::denies('tour_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // $user = TourProgramme::find($id);
        // if($user->delete())
        // {
        //     return response()->json(['status' => 'success','message' => 'TourProgramme deleted successfully!']);
        // }
        // return response()->json(['status' => 'error','message' => 'Error in TourProgramme Delete!']);

        $user = TourProgramme::find($id);
      if(!empty($user)){
        TourDetail::where('tourid',$id)->delete();
         $user->delete();
         return response()->json(['status' => 'success','message' => 'TourProgramme deleted successfully!']);
       }

        return response()->json(['status' => 'error','message' => 'Error in TourProgramme Delete!']);
    }

    public function upload(Request $request) 
    {
      //abort_if(Gate::denies('tour_upload'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        Excel::import(new TourImport,request()->file('import_file'));
        return back();
    }
    public function download(Request $request)
    {
      //abort_if(Gate::denies('tour_download'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return Excel::download(new TourExport($request), 'tours.xlsx');
    }
    public function template()
    {
      //abort_if(Gate::denies('tour_template'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (ob_get_contents()) ob_end_clean();
        ob_start();
        return Excel::download(new TourExport, 'tours.xlsx');
    }


    public function changeStatus(Request $request){
        // $tour = TourProgramme::find($request->id);
        // if($tour){
        //     $tour->status = $request->status;
        //     $tour->save();
        //     return response()->json(["status"=>"success"]);
        // }else{
        //     return response()->json(["status"=>false]);
        // }
    // dd($request->status);
         if (!$request->id) {
        return response()->json(["status" => false]);
    }

    $tours = TourProgramme::whereIn('id', $request->id)->get();

    if ($tours->isEmpty()) {
        return response()->json(["status" => false]);
    }

    foreach ($tours as $tour) {

        $oldStatus = $tour->status;

        $tour->update([
            'status' => $request->status
        ]);

        // Decide action name based on status
        $action = match ((int)$request->status) {
            1 => 'approved',
            2 => 'rejected',
            0 => 'pending',
            default => 'status_changed',
        };

        $oldLabel = $statusLabels[$oldStatus] ?? 'Unknown';
        $newLabel = $statusLabels[$request->status] ?? 'Unknown';   

        // Create log
        $this->addTourLog(
            $tour->id,
            $action,
            $request->status,
            "Status changed from $oldLabel to $newLabel"
        );
    }

    return response()->json(["status" => "success"]);


    }
    public function getUserTerritory(Request $request)
{
    $userId = $request->user_id;

    // Option A: if user has district column (simple)
    // $user = User::find($userId);
    // $districts = $user ? explode(',', $user->districts ?? '') : [];

    // Option B: better – assume you have relation or query from tour details / assigned territories
    // Example: get distinct districts user has entries for
    $districts = TourDetail::whereHas('tourinfo', function($q) use ($userId) {
            $q->where('userid', $userId);
        })
        ->join('cities', 'tour_details.city_id', '=', 'cities.id')
        ->distinct()
        ->pluck('cities.district_name')   // ← assuming you have district_name in cities table
        ->map(function($name) {
            return ['district' => $name];
        });

    // Or hard-coded / from user meta / pivot table
    // $districts = UserDistrict::where('user_id', $userId)->get(['district']);

    return response()->json([
        'districts' => $districts
    ]);
}

public function getCitiesByDistrictAndUser(Request $request)
{
    $userId   = $request->user_id;
    $district = $request->district;

    $cities = City::where('district_name', $district)
        ->whereIn('id', function($q) use ($userId) {
            $q->select('city_id')
              ->from('tour_details')
              ->whereHas('tourinfo', fn($sq) => $sq->where('userid', $userId));
        })
        ->get(['city_name']);

    // Or simpler if all cities in district are allowed:
    // $cities = City::where('district_name', $district)->get(['city_name']);

    return response()->json([
        'cities' => $cities
    ]);
}
public function ajaxUserCities(Request $request)
{
    $userId = $request->input('user_id');

    if (!$userId || !is_numeric($userId)) {
        return response()->json(['cities' => []]);
    }

    $assignments = UserCityAssign::where('userid', $userId)
        ->with('cityname.districtname')          // eager load both
        ->get();

    $cities = $assignments->map(function ($assign) {
        $city = $assign->cityname;

        // Skip if city doesn't exist
        if (!$city) {
            return null;
        }

        // Safely get district name (handles null district relation)
        $districtName = $city->districtname ? $city->districtname->district_name : '';

        return [
            'id'           => $city->id,
            'city_name'    => $city->city_name ?? 'Unknown City',
            'district_name'=> $districtName,
        ];
    })
    ->filter()           // remove null entries
    ->values();          // re-index array

    return response()->json([
        'cities' => $cities
    ]);
}

// In TourController.php

/**
 * Get distinct districts (with IDs) that the user has cities assigned to
 */
// TourController.php

public function ajaxUserDistricts(Request $request)
{
    $userId = $request->input('user_id');

    if (!$userId || !is_numeric($userId)) {
        return response()->json(['districts' => []]);
    }

    $districts = UserCityAssign::query()
        ->where('userid', $userId)
        ->join('cities', 'user_city_assigns.city_id', '=', 'cities.id')
        ->join('districts', 'cities.district_id', '=', 'districts.id')
        ->distinct()
        ->select('districts.id', 'districts.district_name')
        ->orderBy('districts.district_name')
        ->get()
        ->map(fn($d) => [
            'id'   => (int) $d->id,
            'name' => $d->district_name ?? '—',
        ]);

    return response()->json(['districts' => $districts]);
}

public function ajaxUserCitiesByDistrict(Request $request)
{
    $userId     = $request->input('user_id');
    $districtId = $request->input('district_id'); // FIXED

    if (!$userId || !$districtId) {
        return response()->json(['cities' => []]);
    }

    $cities = UserCityAssign::query()
        ->where('userid', $userId)
        ->join('cities', 'user_city_assigns.city_id', '=', 'cities.id')
        ->where('cities.district_id', $districtId) // FILTER BY ID
        ->select(
            'cities.id',
            'cities.city_name as name'
        )
        ->orderBy('cities.city_name')
        ->get();

    return response()->json(['cities' => $cities]);
}


public function activities($id)
{
    $tour = TourProgramme::with(['logs.user'])->findOrFail($id);

    return view('tours.activities', compact('tour'));
}

}
