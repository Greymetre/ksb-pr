<?php

  namespace App\Http\Controllers;

  use App\Models\Beat;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Illuminate\Support\Facades\Redirect;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;

  use DataTables;
  use Validator;
  use Gate;
  use App\Models\{BeatCustomer, BeatSchedule, 
  // Customers,
  District, BeatUser, State, City};
  use App\Models\User;
  use App\DataTables\SchedulesDataTable;
  use Excel;
  use Carbon\Carbon;
  use App\Imports\BeatImport;
  use App\Exports\BeatExport;
  use App\Exports\BeatTemplate;
  use App\Http\Requests\BeatRequest;
  use App\Models\MasterDistributor;
  use App\Models\SecondaryCustomer;

  class BeatController extends Controller
  {
    public function __construct()
    {
      $this->middleware('auth');
      $this->beats = new Beat();
    }

    // public function index(SchedulesDataTable $dataTable)
    // {
    //   ////abort_if(Gate::denies('beat_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    //   return $dataTable->render('beats.index');
    // } a 

  public function index(SchedulesDataTable $dataTable)
  {
      $states = State::where('active', 'Y')
                  ->select('id','state_name')
                  ->orderBy('state_name')
                  ->get();

      $beatsList = Beat::select('id','beat_name')
                      ->orderBy('beat_name')
                      ->get();

                      // dd($beatsList);

  
      $users = User::whereDoesntHave('roles', function ($query) {
          $query->whereIn('id', config('constants.customer_roles'));
      })->select('id','name')->orderBy('name')->get();

      return $dataTable->render('beats.index', compact('states','beatsList','users'));
  }

  public function getDistricts($state_id)
  {
      $districts = District::where('state_id', $state_id)
                      ->select('id','district_name')
                      ->orderBy('district_name')
                      ->get();

      return response()->json($districts);
  }

  public function getCities($district_id)
  {
      $cities = City::where('district_id', $district_id)
                  ->select('id','city_name')
                  ->orderBy('city_name')
                  ->get();

      return response()->json($cities);
  }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
  {
      $users = User::whereDoesntHave('roles', function ($query) {
          $query->whereIn('id', config('constants.customer_roles'));
      })->select('id', 'name', 'mobile')->get();

      $states = State::where('active', 'Y')
          ->select('id', 'state_name')
          ->get();

      $cities = [];
      $districts = [];

      /* ---------- Retailers (Secondary Customers) ---------- */

      $retailers = SecondaryCustomer::select(
          'id',
          DB::raw("'retailer' as type"),
          'shop_name as name',
          'mobile_number as mobile'
      );

      /* ---------- Distributors ---------- */

      $distributors = MasterDistributor::select(
          'id',
          DB::raw("'distributor' as type"),
          'trade_name as name',
          'mobile'
      );

      /* ---------- Merge Both ---------- */

      $customers = $retailers
          ->unionAll($distributors)
          ->orderBy('name')
          ->get();

      $beatsList = Beat::select('id','beat_name')
          ->orderBy('beat_name')
          ->get();

      return view('beats.create',
          compact('users','customers','states','cities','districts','beatsList')
      )->with('beats', $this->beats);
  }
  // public function create()
  // {
  //     $users = User::whereDoesntHave('roles', function ($query) {
  //         $query->whereIn('id', config('constants.customer_roles'));
  //     })->select('id', 'name', 'mobile')->get();

  //     $states = State::where('active', '=', 'Y')->select('id', 'state_name')->get();
  //     $cities = [];
  //     $districts = [];
  //     $customers = Customers::where('active', '=', 'Y')->select('id', 'name', 'mobile')->get();

  //     $beatsList = Beat::select('id','beat_name')->orderBy('beat_name')->get(); // 👈 ADD THIS

  //     return view('beats.create',
  //         compact('users', 'customers', 'states', 'cities', 'districts', 'beatsList')
  //     )->with('beats', $this->beats);
  // }


  //   public function destroy($id)
  // {
  //     $beat = Beat::findOrFail($id);
  //     $beat->delete();

  //     return response()->json([
  //         'status' => true,
  //         'message' => 'Beat deleted successfully'
  //     ]);
  // }

  public function destroy($id)
  {
      $id = decrypt($id);

      DB::beginTransaction();

      try {

          BeatSchedule::where('beat_id', $id)->delete();
          BeatUser::where('beat_id', $id)->delete();
          BeatCustomer::where('beat_id', $id)->delete();

          $beat = Beat::findOrFail($id);
          $beat->delete();

          DB::commit();

          return response()->json([
              'status' => true
          ]);

      } catch (\Exception $e) {

          DB::rollback();

          return response()->json([
              'status' => false,
              'message' => $e->getMessage()
          ], 500);
      }
  }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BeatRequest $request)
    {
      // try
      // { 
      ////abort_if(Gate::denies('beat_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $request['active'] = 'Y';
      $request['created_by'] = Auth::user()->id;
      if (is_array($request->district_id)) {
        $request['district_id'] = implode(',', $request->district_id);
      }
      if (is_array($request->city_id)) {
        $request['city_id'] = implode(',', $request->city_id);
      }
      $response = $this->beats->create([
        'active' => 'Y',
        'beat_name' => isset($request['beat_name']) ? ucfirst($request['beat_name']) : '',
        'description' => isset($request['description']) ? $request['description'] : '',
        'country_id' => isset($request['country_id']) ? $request['country_id'] : null,
        'state_id' => isset($request['state_id']) ? $request['state_id'] : null,
        'district_id' => isset($request['district_id']) ? $request['district_id'] : null,
        'city_id' => isset($request['city_id']) ? $request['city_id'] : null,
        'region_id' => isset($request['region_id']) ? $request['region_id'] : null,
        'created_by' => isset($request['created_by']) ? $request['created_by'] : null,
        'created_at' => getcurentDateTime(),
        'updated_at' => getcurentDateTime()
      ]);
      if (!empty($response)) {

      
        $collection = collect([]);
        if ($request['customers']) {
          foreach ($request['customers'] as $key => $value) {
            if (!empty($value)) {

                    list($type,$id) = explode('_',$value);

        BeatCustomer::updateOrCreate(
        [
            'beat_id' => $response['id'],
            'distributor_id' => $id,
            'customer_type' => $type
        ],
        [
            'active' => 'Y',
            'created_at' => getcurentDateTime(),
            'updated_at' => getcurentDateTime(),
        ]);
              // BeatCustomer::updateOrCreate(['customer_id' => $value], [
              //   'active' => $request['active'],
              //   'beat_id' => $response['id'],
              //   'customer_id' => $value,
              //   'created_at' => getcurentDateTime(),
              //   'updated_at' => getcurentDateTime(),
              // ]);
              // $collection->push([
              //   'active' => $request['active'],
              //   'beat_id' => $response['id'],
              //   'customer_id' => $value,
              //   'created_at' => getcurentDateTime(),
              //   'updated_at' => getcurentDateTime(),
              // ]);
            }
          }
        }
        $beatusers = collect([]);
        if ($request['users']) {
          foreach ($request['users'] as $key => $value) {
            if (!empty($value)) {
              $beatusers->push([
                'active' => $request['active'],
                'beat_id' => $response['id'],
                'user_id' => $value,
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);
            }
          }
        }
        $schedules = collect([]);
        if (!empty($request['beatdetail'])) {
          foreach ($request['beatdetail'] as $key => $rows) {
            if (isset($rows['user_id']) && isset($rows['beat_date'])) {
              $schedules->push([
                'active' => $request['active'],
                'beat_id' => $response['id'],
                'user_id' => $rows['user_id'],
                'beat_date' => $rows['beat_date'],
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);
            }
          }
        }
        // if($collection->isNotEmpty())
        // {
        //   BeatCustomer::insert($collection->toArray());
        // }
        if ($beatusers->isNotEmpty()) {
          BeatUser::insert($beatusers->toArray());
        }
        if ($schedules->isNotEmpty()) {
          BeatSchedule::insert($schedules->toArray());
        }
        return Redirect::to('beats')->with('message_success', 'beats Store Successfully');
      }
      return redirect()->back()->with('message_danger', 'Error in beats Store')->withInput();
      // }         
      // catch(\Exception $e)
      // {
      //   return redirect()->back()->withErrors($e->getMessage())->withInput();
      // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\beats  $beats
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //   ////abort_if(Gate::denies('beat_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    //   $id = decrypt($id);
    //   $beats = Beat::with('beatusers')->find($id);
    //   $city_names = City::whereIn('id', explode(',', $beats->city_id))->pluck('city_name')->toArray();
    //   $beats['city_name'] = implode(',', $city_names);
    //   $district_names = District::whereIn('id', explode(',', $beats->district_id))->pluck('district_name')->toArray();
    //   $beats['district_name'] = implode(',', $district_names);
    //   $schedules = BeatSchedule::where('beat_id', $id)->get();
    //   $customers = BeatCustomer::where('beat_id', $id)->get();
    //   return view('beats.show', compact('schedules', 'customers'))->with('beats', $beats);
    // }

    public function show($id)
{
    $id = decrypt($id);

    // Fetch beat with relationships
    $beats = Beat::with([
        'beatcustomers.retailer',
        'beatcustomers.distributor',
        'beatusers.users'
    ])->find($id);

    // Map beatcustomers to include a unified 'customer' object
    $beats->beatcustomers->transform(function ($bc) {
        if ($bc->customer_type === 'master') {
            $bc->customer = $bc->distributor;
        } elseif ($bc->customer_type === 'secondary') {
            $bc->customer = $bc->retailer;
        } else {
            $bc->customer = null;
        }
        return $bc;
    });

    // City & district names
    $city_names = City::whereIn('id', explode(',', $beats->city_id))->pluck('city_name')->toArray();
    $beats['city_name'] = implode(',', $city_names);

    $district_names = District::whereIn('id', explode(',', $beats->district_id))->pluck('district_name')->toArray();
    $beats['district_name'] = implode(',', $district_names);

    $schedules = BeatSchedule::where('beat_id', $id)->get();

    $customers = BeatCustomer::with(['retailer','distributor'])
    ->where('beat_id', $id)
    ->get();

    // Frontend me ab $beat->beatcustomers[*]->customer use kar sakte ho
    return view('beats.show', compact('schedules','customers'))->with('beats', $beats);
}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\beats  $beats
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      ////abort_if(Gate::denies('beat_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      try {
        $id = decrypt($id);
        // $beats = Beat::with('beatcustomers.customers', 'beatusers.users')->find($id);
$beats = Beat::with([
    'beatcustomers.retailer',
    'beatcustomers.distributor',
    'beatusers.users'
])->find($id);

// Unified customer object + type for frontend
$beats->beatcustomers->transform(function ($bc) {
  // dd($bc);
    if ($bc->customer_type === 'master') {
        $bc->customer = $bc->distributor;
    } elseif ($bc->customer_type === 'secondary') {
        $bc->customer = $bc->retailer;
    } else {
        $bc->customer = null;
    }

    // Add customer_type so frontend can access it
    $bc->customer_type_for_frontend = $bc->customer_type;

    return $bc;
});
        $users = User::whereDoesntHave('roles', function ($query) {
          $query->whereIn('id', config('constants.customer_roles'));
        })->select('id', 'name', 'mobile')->get();
        // $customers = Customers::where('active', '=', 'Y')->select('id', 'name', 'mobile')->get();
        $retailers = SecondaryCustomer::select(
    'id',
    DB::raw("'retailer' as type"),
    'shop_name as name',
    'mobile_number as mobile'
);

$distributors = MasterDistributor::select(
    'id',
    DB::raw("'distributor' as type"),
    'trade_name as name',
    'mobile'
);

$customers = $retailers
    ->unionAll($distributors)
    ->orderBy('name')
    ->get();
        $states = State::where('active', '=', 'Y')->select('id', 'state_name')->get();
        $districts = District::where('active', '=', 'Y')->where('state_id', $beats['state_id'])->select('district_name', 'id')->get();
        $cities = City::where('active', '=', 'Y')->where('district_id', $beats['district_id'])->select('city_name', 'id')->get();
        $beatsList = Beat::select('id','beat_name')
                  ->orderBy('beat_name')
                  ->get();
  return view('beats.create',
      compact('users', 'customers', 'states', 'districts', 'cities', 'beatsList')
  )->with('beats', $beats);

  } catch (\Exception $e) {
        return redirect()->back()->withErrors($e->getMessage())->withInput();
      }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\beats  $beats
     * @return \Illuminate\Http\Response
     */
    public function update(BeatRequest $request, $id)
    {
      try {
        ////abort_if(Gate::denies('beat_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request['beat_id'] = decrypt($id);
        $response = $this->beats->update_data($request);
        if ($response['status'] == 'success') {
          if ($request['customers']) {
            foreach ($request['customers'] as $key => $value) {
              BeatCustomer::updateOrCreate(['distributor_id' => $value], [
                'active' => 'Y',
                'beat_id' => $request['beat_id'],
                'distributor_id' => $value,
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);
            }
          }
          if ($request['beatdetail']) {
            foreach ($request['beatdetail'] as $key => $rows) {
              BeatSchedule::updateOrCreate(['beat_id' => $request['beat_id'], 'user_id' => $rows['user_id'], 'beat_date' => $rows['beat_date']], [
                'active' => 'Y',
                'beat_id' => $request['beat_id'],
                'user_id' => $rows['user_id'],
                'beat_date' => $rows['beat_date'],
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);

              BeatUser::updateOrCreate(['beat_id' => $request['beat_id'], 'user_id' => $rows['user_id']], [
                'active' => 'Y',
                'beat_id' => $request['beat_id'],
                'user_id' => $rows['user_id'],
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);
            }
          }
          $beatusers = collect([]);
          if ($request['users']) {
            foreach ($request['users'] as $key => $index) {
              BeatUser::updateOrCreate(['beat_id' => $request['beat_id'], 'user_id' => $index], [
                'active' => 'Y',
                'beat_id' => $request['beat_id'],
                'user_id' => $index,
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);
            }
          }
          return Redirect::to('beats')->with('message_success', 'beats Update Successfully');
        }
        return redirect()->back()->with('message_danger', 'Error in beats Update')->withInput();
      } catch (\Exception $e) {
        return redirect()->back()->withErrors($e->getMessage())->withInput();
      }
    }

    // public function beatScheduleUpdate(Request $request)
    // {
    //   try {
    //     if ($request['beatdetail']) {
    //       foreach ($request['beatdetail'] as $key => $rows) {

    //         BeatSchedule::updateOrCreate(['beat_id' => $request['beat_id'], 'user_id' => $rows['user_id'], 'beat_date' => $rows['beat_date']], [
    //           'active' => 'Y',
    //           'beat_id' => $request['beat_id'],
    //           'user_id' => $rows['user_id'],
    //           'beat_date' => $rows['beat_date'],
    //           'created_at' => getcurentDateTime(),
    //           'updated_at' => getcurentDateTime(),
    //         ]);
    //       }
    //       return redirect()->back()->with('message_success', 'Schedule Update Successfully');
    //     }
    //     return redirect()->back()->with('message_danger', 'Error in beats Schedule')->withInput();
    //   } catch (\Exception $e) {
    //     return redirect()->back()->withErrors($e->getMessage())->withInput();
    //   }
    // }

  public function beatScheduleUpdate(Request $request)
  {
      try {

          DB::beginTransaction();

          if (!empty($request->users)) {

              foreach ($request->users as $index => $userId) {

                  $beatId = $request->beats[$index] ?? null;
                  $type   = $request->schedule_type[$index] ?? null;
                  $start  = $request->start_date[$index] ?? null;
                  $end    = $request->end_date[$index] ?? null;
                  $multi  = $request->multiple_dates[$index] ?? null;

                  if (!$userId || !$beatId || !$type) {
                      continue;
                  }

                  $generatedDates = [];

                  switch ($type) {

                      case 'single':
                          if ($start) {
                              $generatedDates[] = $start;
                          }
                          break;

                      case 'multiple':
                          if ($multi) {
                              $dates = explode(',', $multi);
                              foreach ($dates as $d) {
                                  $generatedDates[] = trim($d);
                              }
                          }
                          break;

                      case 'weekly':
                          if ($start && $end) {
                              $s = Carbon::parse($start);
                              $e = Carbon::parse($end);

                              while ($s->lte($e)) {
                                  $generatedDates[] = $s->format('Y-m-d');
                                  $s->addWeek();
                              }
                          }
                          break;

case 'monthly':

if (!empty($start) && !empty($end)) {

    $startDate = Carbon::parse($start);
    $endDate   = Carbon::parse($end);

    $targetWeekday = $startDate->dayOfWeek;
    $weekOfMonth   = ceil($startDate->day / 7);

    $currentMonth = $startDate->copy()->startOfMonth();

    while ($currentMonth->lte($endDate)) {

        if ($weekOfMonth == 5) {

            // last weekday of month
            $targetDate = $currentMonth->copy()->lastOfMonth($targetWeekday);

        } else {

            $firstDayOfMonth = $currentMonth->copy();
            $firstWeekday = $firstDayOfMonth->dayOfWeek;

            $diff = ($targetWeekday - $firstWeekday + 7) % 7;

            $firstTargetWeekday = $firstDayOfMonth->copy()->addDays($diff);

            $targetDate = $firstTargetWeekday->copy()->addWeeks($weekOfMonth - 1);

        }

        if ($targetDate->between($startDate, $endDate)) {
            $generatedDates[] = $targetDate->format('Y-m-d');
        }

        $currentMonth->addMonth()->startOfMonth();
    }
}

break;
                  }

                  foreach ($generatedDates as $date) {

                      BeatSchedule::updateOrCreate(
                          [
                              'beat_id'   => $beatId,
                              'user_id'   => $userId,
                              'beat_date' => $date
                          ],
                          [
                              'active'     => 'Y',
                              'created_at' => now(),
                              'updated_at' => now(),
                          ]
                      );
                  }
              }
          }

          DB::commit();

          return response()->json([
              'status' => true,
              'message' => 'Schedule Saved Successfully'
          ]);

      } catch (\Exception $e) {

          DB::rollback();

          return response()->json([
              'status' => false,
              'message' => $e->getMessage()
          ], 500);
      }
  }


  public function saveIndividualSchedule(Request $request)
  {
      try {

          DB::beginTransaction();

          foreach ($request->beatdetail as $detail) {

              $beatId = $request->beat_id;
              $userId = $detail['user_id'] ?? null;
              $type   = $detail['schedule_type'] ?? null;
              $start  = $detail['start_date'] ?? null;
              $end    = $detail['end_date'] ?? null;
              $multi  = $detail['multiple_dates'] ?? null;

              if (!$beatId || !$userId || !$type) {
                  continue;
              }

              $generatedDates = [];

              switch ($type) {

                  // 🔹 1️⃣ SINGLE
                  case 'single':
                      if (!empty($start)) {
                          $generatedDates[] = Carbon::parse($start)->format('Y-m-d');
                      }
                      break;

                  // 🔹 2️⃣ MULTIPLE
  case 'multiple':

      if (!empty($multi)) {

          $dates = explode(',', $multi);

          foreach ($dates as $d) {

              $d = trim($d);

              if (!empty($d) && $d !== 'Multiple') {
                  try {
                      $generatedDates[] = Carbon::parse($d)->format('Y-m-d');
                  } catch (\Exception $e) {
                      continue; // skip invalid date
                  }
              }
          }
      }

      break;

                  // 🔹 3️⃣ WEEKLY
                  case 'weekly':
                      if (!empty($start) && !empty($end)) {

                          $current = Carbon::parse($start);
                          $endDate = Carbon::parse($end);

                          while ($current->lte($endDate)) {
                              $generatedDates[] = $current->format('Y-m-d');
                              $current->addWeek();
                          }
                      }
                      break;

                  // 🔹 4️⃣ MONTHLY
case 'monthly':

if (!empty($start) && !empty($end)) {

    $startDate = Carbon::parse($start);
    $endDate   = Carbon::parse($end);

    $targetWeekday = $startDate->dayOfWeek;
    $weekOfMonth   = ceil($startDate->day / 7);

    $currentMonth = $startDate->copy()->startOfMonth();

    while ($currentMonth->lte($endDate)) {

        if ($weekOfMonth == 5) {

            // last weekday of month
            $targetDate = $currentMonth->copy()->lastOfMonth($targetWeekday);

        } else {

            $firstDayOfMonth = $currentMonth->copy();
            $firstWeekday = $firstDayOfMonth->dayOfWeek;

            $diff = ($targetWeekday - $firstWeekday + 7) % 7;

            $firstTargetWeekday = $firstDayOfMonth->copy()->addDays($diff);

            $targetDate = $firstTargetWeekday->copy()->addWeeks($weekOfMonth - 1);
        }

        if ($targetDate->between($startDate, $endDate)) {
            $generatedDates[] = $targetDate->format('Y-m-d');
        }

        $currentMonth->addMonth()->startOfMonth();
    }
}

break;
              }

              // 🔥 INSERT GENERATED DATES
              foreach ($generatedDates as $date) {

                  BeatSchedule::updateOrCreate(
                      [
                          'beat_id'   => $beatId,
                          'user_id'   => $userId,
                          'beat_date' => $date,
                      ],
                      [
                          'active'     => 1,
                          'created_at' => now(),
                          'updated_at' => now(),
                      ]
                  );
              }
          }

          DB::commit();
  return response()->json([
      'status'  => true,
      'message' => 'Schedule Saved Successfully'
  ]);

      } catch (\Exception $e) {

          DB::rollback();

  return redirect()->back()
      ->withErrors($e->getMessage())
      ->withInput();
      }
  }


    public function addBeatUsers(Request $request)
    {
      try {
        if ($request['users']) {
          foreach ($request['users'] as $key => $value) {
            if (!empty($value)) {
              BeatUser::updateOrCreate(['beat_id' => $request['beat_id'], 'user_id' => $value], [
                'active' => 'Y',
                'beat_id' => $request['beat_id'],
                'user_id' => $value,
                'created_at' => getcurentDateTime(),
                'updated_at' => getcurentDateTime(),
              ]);
            }
          }
          return redirect()->back()->with('message_success', 'User Update Successfully');
        }
        return redirect()->back()->with('message_danger', 'Error in beats User')->withInput();
      } catch (\Exception $e) {
        return redirect()->back()->withErrors($e->getMessage())->withInput();
      }
    }

    public function addBeatCustomer(Request $request)
    {
      try {
                    // dd($request);
               
      if ($request->customers) {
            foreach ($request->customers as $key => $customer) {
                if (empty($customer)) continue;

                // Get type from separate array
                $type = $request->customer_type[$key] ?? null;

                // Map type
    if($type == 'distributor') $type = 'master';
    elseif($type == 'retailer') $type = 'secondary';

    // ✅ Validate existence in DB
    if ($type == 'master' && !\App\Models\MasterDistributor::find($customer)) {
        return redirect()->back()->with('message_danger','Distributor not found')->withInput();
    }
    if ($type == 'secondary' && !\App\Models\SecondaryCustomer::find($customer)) {
        return redirect()->back()->with('message_danger','Retailer not found')->withInput();
    }

    // Insert/update DB
    BeatCustomer::updateOrCreate(
        [
            'beat_id' => $request->beat_id,
            'distributor_id' => $customer,
            'customer_type' => $type
        ],
        [
            'active' => 'Y',
            'created_at' => getcurentDateTime(),
            'updated_at' => getcurentDateTime(),
        ]
    );
                // if($type == 'distributor'){
                //     $type = 'master';
                // } elseif($type == 'retailer'){
                //     $type = 'secondary';
                // }

                // // Insert / update DB
                // BeatCustomer::updateOrCreate(
                //     [
                //         'beat_id' => $request->beat_id,
                //         'customer_id' => $customer,
                //         'customer_type' => $type
                //     ],
                //     [
                //         'active' => 'Y',
                //         'created_at' => getcurentDateTime(),
                //         'updated_at' => getcurentDateTime(),
                //     ]
                // );
            }

            return redirect()->back()->with('message_success', 'Customer Update Successfully');
        }

      // dd($response);
      // dd($request);
    //     if ($request['customers']) {
    //       foreach ($request['customers'] as $key => $value) {

    //           if(!$customer) continue;

    // // Get type from separate array
    // $type = $request->customer_type[$key] ?? null;
    // $id = $customer;

    // // Map type
    // if($type == 'distributor'){
    //     $type = 'master';
    // } elseif($type == 'retailer'){
    //     $type = 'secondary';
    // }

    // BeatCustomer::updateOrCreate(
    //     [
    //         'beat_id' => $request->beat_id,
    //         'customer_id' => $id,
    //         'customer_type' => $type
    //     ],
    //     [
    //         'active' => 'Y',
    //         'created_at' => getcurentDateTime(),
    //         'updated_at' => getcurentDateTime(),
    //     ]
    // );
            // if (!empty($value)) {

                    // list($type,$id) = explode('_',$value);

                    //    // TYPE FIX
                    // if($type == 'distributor'){
                    //     $type = 'master';
                    // }

                    // if($type == 'retailer'){
                    //     $type = 'secondary';
                    // }

                    // BeatCustomer::updateOrCreate(
                    // [
                    //     'beat_id' => $request->beat_id,
                    //     'customer_id' => $id,
                    //     'customer_type' => $type
                    // ],
                    // [
                    //     'active' => 'Y',
                    //     'created_at' => getcurentDateTime(),
                    //     'updated_at' => getcurentDateTime(),
                    // ]);

              // BeatCustomer::updateOrCreate(['customer_id' => $value], [
              //   'active' => 'Y',
              //   'beat_id' => $request['beat_id'],
              //   'customer_id' => $value,
              //   'created_at' => getcurentDateTime(),
              //   'updated_at' => getcurentDateTime(),
              // ]);
            // }
        //   }
        //   return redirect()->back()->with('message_success', 'Customer Update Successfully');
        // }
        return redirect()->back()->with('message_danger', 'Error in beats User')->withInput();
      } catch (\Exception $e) {
        dd($e->getMessage()); // debugging
        return redirect()->back()->withErrors($e->getMessage())->withInput();
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\beats  $beats
     * @return \Illuminate\Http\Response
     */
    // public function destroy(beats $beats)
    // {
    //   ////abort_if(Gate::denies('beat_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
    // }

  public function beatScheduleDelete($id)
  {
      try {

          $schedule = BeatSchedule::with([
              'beatcheckininfo',
              'beatschedulecustomer',
              'beatscheduleorders'
          ])->findOrFail($id);

          // manually delete relations
          $schedule->beatcheckininfo()->delete();
          $schedule->beatschedulecustomer()->delete();
          $schedule->beatscheduleorders()->delete();

          $schedule->delete();

          return response()->json([
              'status' => true,
              'message' => 'Schedule deleted successfully'
          ]);

      } catch (\Exception $e) {

          return response()->json([
              'status' => false,
              'message' => $e->getMessage()
          ], 500);
      }
  }

    public function beatCustomerDelete($id)
    {
      return json_encode(BeatCustomer::find($id)->delete());
    }
    public function beatUserDelete($id)
    {
      return json_encode(BeatUser::find($id)->delete());
    }

    public function beatdetail(Request $request)
    {
      ////abort_if(Gate::denies('beatdetail_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

      if ($request->ajax()) {

      $data = BeatSchedule::with(
    'beats',
    'users',
    'beatcustomers.retailer',
    'beatcustomers.distributor',
    'beats.createdbyname'
)->latest();
        // $data = BeatSchedule::with('beats', 'users', 'beatcustomers.customers', 'beats.createdbyname')->latest();
        return Datatables::of($data)
          ->addIndexColumn()
          ->editColumn('created_at', function ($data) {
            return isset($data->created_at) ? showdatetimeformat($data->created_at) : '';
          })
          ->addColumn('customers', function ($query) {
            $customers = array();

            foreach ($query['beatcustomers'] as $customer) {

    if($customer->retailer){
        $customers[] = $customer->retailer->name;
    }

    if($customer->distributor){
        $customers[] = $customer->distributor->name;
    }

}

            // foreach ($query['beatcustomers'] as $key => $customer) {
            //   array_push($customers, $customer['customers']['name']);
            // }
            return !empty($customers) ? implode(', ', $customers) : '';
          })
  ->addColumn('action', function ($query) {

      $editUrl = route('beats.edit', encrypt($query->beat_id));
      $deleteUrl = url('schedule-delete/' . $query->id);

      return '
      <div class="btn-group btn-group-sm">
          <a href="'.$editUrl.'" 
            class="btn btn-info btn-sm">
              <i class="material-icons">edit</i>
          </a>


      </div>';
  })
          ->rawColumns(['action', 'customers'])
          ->make(true);
      }
      return view('beats.beatdetail');
    }

  public function upload(Request $request)
  {
      try {

          Excel::import(new BeatImport, $request->file('import_file'));

          return back()->with('message_success', 'Import Successful');

      } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

          $failures = $e->failures();

          $errorMessages = [];

          foreach ($failures as $failure) {

              $errorMessages[] =
                  'Row ' . $failure->row() . ' - ' .
                  implode(', ', $failure->errors());
          }

          return back()->withErrors($errorMessages);
      }
  }
    public function download()
    {
      ////abort_if(Gate::denies('beat_download'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      if (ob_get_contents()) ob_end_clean();
      ob_start();
      return Excel::download(new BeatExport, 'beats.xlsx');
    }
    public function template()
    {
      ////abort_if(Gate::denies('beat_template'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      if (ob_get_contents()) ob_end_clean();
      ob_start();
      return Excel::download(new BeatTemplate, 'beats.xlsx');
    }
    public function beatsSchedule($id)
    {
      ////abort_if(Gate::denies('beat_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $id = decrypt($id);
      $beats = Beat::find($id);
      return view('beats.schedule')->with('beats', $beats);
    }

    // public function livelocation(Request $request)
    // {
    //   $search_branches = $request->input('search_branches');
    //   $user_id = auth()->user()->id;
    //   $all_reporting_user_ids = getUsersReportingToAuth($user_id);
    //   $all_user_branches = User::with('getbranch')->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
    //   $all_user_divisions = User::with('getdivision')->orderBy('division_id')->get();
    //   $all_user_departments = User::with('getdepartment')->orderBy('department_id')->get();
    //     $branches= array();
    //     $all_branch= array();
    //     $bkey = 0;
    //     foreach ($all_user_branches as $k => $val) {
    //       if($val->getbranch){
    //         if(!in_array($val->getbranch->id, $all_branch)){
    //             array_push($all_branch, $val->getbranch->id);
    //             $branches[$bkey]['id'] = $val->getbranch->id;
    //             $branches[$bkey]['name'] = $val->getbranch->branch_name;
    //             $bkey++;
    //         }
    //       }
    //     }

    //     $divisions = array();
    //     $all_division = array();
    //     $dkey = 0;

    //     foreach ($all_user_divisions as $k => $val) {

    //       if($val->getdivision){
    //         if(!in_array($val->getdivision->id, $all_division)){
    //             array_push($all_division, $val->getdivision->id);
    //             $divisions[$dkey]['id'] = $val->getdivision->id;
    //             $divisions[$dkey]['name'] = $val->getdivision->division_name;
    //             $dkey++;
    //         }
    //       }
    //     }


    //     $departments = array();
    //     $all_department = array();
    //     $dp_key = 0;

    //     foreach($all_user_departments as $k=>$val) {
    //       if($val->getdepartment){
    //         if(!in_array($val->getdepartment->id, $all_department)){
    //             array_push($all_department, $val->getdepartment->id);
    //             $departments[$dp_key]['id'] = $val->getdepartment->id;
    //             $departments[$dp_key]['name'] = $val->getdepartment->name;
    //             $dp_key++;
    //         }
    //       }
    //     }

    //     if($search_branches && count($search_branches) > 0 && $search_branches[0] != null){
    //       $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)->whereIn('branch_id', $search_branches)->pluck('id')->toArray();
    //     }

    //     if(!empty($search_divisions) && count($search_divisions) > 0 && $search_divisions[0] != null){
    //       $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)->whereIn('division_id', $search_divisions)->pluck('id')->toArray();
    //     }

    //     if(!empty($search_departments) && count($search_departments) > 0 && $search_departments[0] != null){
    //       $all_reporting_user_ids = User::whereIn('id', $all_reporting_user_ids)->whereIn('department_id', $search_departments)->pluck('id')->toArray();
    //     }

    //     $all_user_details = User::with('getbranch','getdivision','getdepartment')->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
    //     $all_users= array();
    //     foreach ($all_user_details as $k => $val) {
    //         $users[$k]['id'] = $val->id;
    //         $users[$k]['name'] = $val->name;
    //     }
    //     if($request->ajax()){
    //         $response = ["users"=>$users, "status"=>true];
    //         return response()->json($response);
    //     }
    //   return view('beats.livelocation',compact('users', 'branches','divisions','departments'));
    // }

    public function livelocation(Request $request)
    {
      $search_branches = $request->input('search_branches');
      $search_divisions = $request->input('search_divisions');
      $search_departments = $request->input('search_departments');
      $user_id = auth()->user()->id;
      $all_reporting_user_ids = getUsersReportingToAuth($user_id);
      $all_user_divisions = User::whereDoesntHave('roles', function ($query) {
        $query->whereIn('id', config('constants.customer_roles'));
      })->with('getdivision')->orderBy('division_id')->get();
      $all_user_departments = User::whereDoesntHave('roles', function ($query) {
        $query->whereIn('id', config('constants.customer_roles'));
      })->with('getdepartment')->orderBy('department_id')->get();

      $all_user_branches = User::whereDoesntHave('roles', function ($query) {
        $query->whereIn('id', config('constants.customer_roles'));
      })->with('getbranch')->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
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

      $divisions = array();
      $all_division = array();
      $dkey = 0;

      foreach ($all_user_divisions as $k => $val) {

        if ($val->getdivision) {
          if (!in_array($val->getdivision->id, $all_division)) {
            array_push($all_division, $val->getdivision->id);
            $divisions[$dkey]['id'] = $val->getdivision->id;
            $divisions[$dkey]['name'] = $val->getdivision->division_name;
            $dkey++;
          }
        }
      }


      $departments = array();
      $all_department = array();
      $dp_key = 0;

      foreach ($all_user_departments as $k => $val) {
        if ($val->getdepartment) {
          if (!in_array($val->getdepartment->id, $all_department)) {
            array_push($all_department, $val->getdepartment->id);
            $departments[$dp_key]['id'] = $val->getdepartment->id;
            $departments[$dp_key]['name'] = $val->getdepartment->name;
            $dp_key++;
          }
        }
      }

      if ($search_branches && count($search_branches) > 0 && $search_branches[0] != null) {
        $all_reporting_user_ids = User::whereDoesntHave('roles', function ($query) {
          $query->whereIn('id', config('constants.customer_roles'));
        })->whereIn('id', $all_reporting_user_ids)->whereIn('branch_id', $search_branches)->pluck('id')->toArray();
      }

      if (!empty($search_divisions) && count($search_divisions) > 0 && $search_divisions[0] != null) {
        $all_reporting_user_ids = User::whereDoesntHave('roles', function ($query) {
          $query->whereIn('id', config('constants.customer_roles'));
        })->whereIn('id', $all_reporting_user_ids)->whereIn('division_id', $search_divisions)->pluck('id')->toArray();
      }

      if (!empty($search_departments) && count($search_departments) > 0 && $search_departments[0] != null) {
        $all_reporting_user_ids = User::whereDoesntHave('roles', function ($query) {
          $query->whereIn('id', config('constants.customer_roles'));
        })->whereIn('id', $all_reporting_user_ids)->whereIn('department_id', $search_departments)->pluck('id')->toArray();
      }

      $all_user_details = User::whereDoesntHave('roles', function ($query) {
        $query->whereIn('id', config('constants.customer_roles'));
      })->with('getbranch', 'getdivision', 'getdepartment')->whereIn('id', $all_reporting_user_ids)->orderBy('branch_id')->get();
      $all_users = array();
      foreach ($all_user_details as $k => $val) {
        $users[$k]['id'] = $val->id;
        $users[$k]['name'] = $val->name;
      }
      if ($request->ajax()) {
        $response = ["users" => $users, "status" => true];
        return response()->json($response);
      }

      if ($request->user_id) {
        $user_id = $request->user_id;
      } else {
        $user_id = NULL;
      }

      if ($request->date) {
        $date = $request->date;
      } else {
        $date = NULL;
      }

      return view('beats.livelocation', compact('users', 'branches', 'divisions', 'departments', 'date', 'user_id'));
    }

  public function globalScheduleForm()
  {
      $users = User::where('status',1)->get();
      $beats = Beat::all();

      return view('beats.global_schedule_form', compact('users','beats'));
  }
  }
