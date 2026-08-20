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
  use App\Models\UserLiveLocation;
  use App\Models\CheckIn;
  use App\Models\Order;
  use App\Models\Attendance;

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

              // the dropdown posts the id in customers[] and the master it came
              // from in the matching customer_type[] row
              $parsed = $this->parseBeatCustomerSelection($value, $request['customer_type'][$key] ?? null);
              if (!$parsed) {
                continue;
              }
              list($type, $id) = $parsed;

        BeatCustomer::updateOrCreate(
        array_merge(
          [
            'beat_id' => $response['id'],
            'customer_type' => $type
          ],
          $type === 'customer' ? ['customer_id' => $id] : ['distributor_id' => $id]
        ),
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
              $parsed = $this->parseBeatCustomerSelection($value, $request['customer_type'][$key] ?? null);
              if (!$parsed) {
                continue;
              }
              list($type, $id) = $parsed;

              BeatCustomer::updateOrCreate(
                array_merge(
                  [
                    'beat_id' => $request['beat_id'],
                    'customer_type' => $type
                  ],
                  $type === 'customer' ? ['customer_id' => $id] : ['distributor_id' => $id]
                ),
                [
                  'active' => 'Y',
                  'created_at' => getcurentDateTime(),
                  'updated_at' => getcurentDateTime(),
                ]
              );
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

                $parsed = $this->parseBeatCustomerSelection($customer, $type);
                if (!$parsed) {
                    continue;
                }
                list($type, $customer) = $parsed;

    // ✅ Validate existence in DB
    if ($type == 'master' && !\App\Models\MasterDistributor::find($customer)) {
        return redirect()->back()->with('message_danger','Distributor not found')->withInput();
    }
    if ($type == 'secondary' && !\App\Models\SecondaryCustomer::find($customer)) {
        return redirect()->back()->with('message_danger','Retailer not found')->withInput();
    }
    if ($type == 'customer' && !\App\Models\Customers::find($customer)) {
        return redirect()->back()->with('message_danger','Customer not found')->withInput();
    }

    // Insert/update DB
    BeatCustomer::updateOrCreate(
        array_merge(
            [
                'beat_id' => $request->beat_id,
                'customer_type' => $type
            ],
            $type === 'customer' ? ['customer_id' => $customer] : ['distributor_id' => $customer]
        ),
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

      $locationMode = $request->routeIs('location.geolocator') ? 'geolocator' : 'live';

      return view('beats.livelocation', compact('users', 'branches', 'divisions', 'departments', 'date', 'user_id', 'locationMode'));
    }

    public function allUsersLiveLocations()
    {
      $accessibleUserIds = getUsersReportingToAuth();
      $latestLocationIds = UserLiveLocation::query()
        ->whereIn('userid', $accessibleUserIds)
        ->whereDate('created_at', Carbon::today())
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->selectRaw('MAX(id) as id')
        ->groupBy('userid')
        ->pluck('id');

      $users = User::with(['getdesignation', 'getbranch', 'getdivision'])
        ->whereIn('id', $accessibleUserIds)
        ->get()
        ->keyBy('id');
      $latestLocations = UserLiveLocation::whereIn('id', $latestLocationIds)->get()->keyBy('userid');
      $todayLocations = UserLiveLocation::whereIn('userid', $accessibleUserIds)
        ->whereDate('created_at', Carbon::today())
        ->orderBy('id')
        ->get()
        ->groupBy('userid');
      $todayVisits = CheckIn::whereIn('user_id', $accessibleUserIds)
        ->whereDate('checkin_date', Carbon::today())
        ->selectRaw('user_id, COUNT(*) as total')
        ->groupBy('user_id')
        ->pluck('total', 'user_id');
      $todayOrders = Order::whereIn('created_by', $accessibleUserIds)
        ->whereDate('created_at', Carbon::today())
        ->selectRaw('created_by, COALESCE(SUM(grand_total), 0) as total')
        ->groupBy('created_by')
        ->pluck('total', 'created_by');
      $todayPlans = BeatSchedule::with('beats')
        ->whereIn('user_id', $accessibleUserIds)
        ->whereDate('beat_date', Carbon::today())
        ->get()
        ->groupBy('user_id');

      $locations = $users->values()
        ->map(function ($user) use ($latestLocations, $todayLocations, $todayVisits, $todayOrders, $todayPlans) {
          $location = $latestLocations->get($user->id);
          $reportedAt = $location?->created_at ? Carbon::parse($location->created_at) : null;
          $status = !$location ? 'GPS Off' : (($reportedAt && $reportedAt->diffInMinutes(Carbon::now()) <= 15) ? 'Online' : 'Offline');
          $distance = 0;
          $points = $todayLocations->get($user->id, collect())->values();
          for ($index = 1; $index < $points->count(); $index++) {
            $previous = $points[$index - 1];
            $current = $points[$index];
            if (is_numeric($previous->latitude) && is_numeric($previous->longitude) && is_numeric($current->latitude) && is_numeric($current->longitude)) {
              $distance += haversineGreatCircleDistance($previous->latitude, $previous->longitude, $current->latitude, $current->longitude);
            }
          }
          $plan = $todayPlans->get($user->id, collect())
            ->pluck('beats.beat_name')
            ->filter()
            ->unique()
            ->implode(', ');

          return [
            'user_id' => $user->id,
            'name' => $user->name ?: 'Unknown user',
            'employee_code' => $user->employee_codes ?? '',
            'designation' => optional($user->getdesignation)->designation_name ?? 'Field employee',
            'branch' => optional($user->getbranch)->branch_name ?? '',
            'division' => optional($user->getdivision)->division_name ?? '',
            'mobile' => $user->mobile ?? '',
            'today_plan' => $plan ?: 'No plan assigned',
            'distance_km' => round($distance, 1),
            'visits_today' => (int) ($todayVisits[$user->id] ?? 0),
            'order_value' => (float) ($todayOrders[$user->id] ?? 0),
            'latitude' => $location?->latitude,
            'longitude' => $location?->longitude,
            'address' => $location?->address ?: 'Address unavailable',
            'time' => $location?->time ?: optional($reportedAt)->format('h:i A'),
            'reported_at' => optional($reportedAt)->toIso8601String(),
            'status' => $status,
          ];
        })
        ->values();

      return response()->json(['status' => true, 'locations' => $locations]);
    }

    public function punchInLocator()
    {
      $accessibleUserIds = getUsersReportingToAuth();
      $punchIns = Attendance::with(['users.getdesignation', 'users.getdivision'])
        ->whereIn('user_id', $accessibleUserIds)
        ->whereDate('punchin_date', Carbon::today())
        ->whereNotNull('punchin_latitude')
        ->whereNotNull('punchin_longitude')
        ->orderBy('punchin_time')
        ->get()
        ->map(function ($attendance) {
          $user = $attendance->users;
          if (!is_numeric($attendance->punchin_latitude) || !is_numeric($attendance->punchin_longitude)) {
            return null;
          }

          $latitude = (float) $attendance->punchin_latitude;
          $longitude = (float) $attendance->punchin_longitude;

          // The mobile punch-in API stores request latitude in punchin_longitude and
          // request longitude in punchin_latitude. Reverse that mapping for App records.
          if (strcasecmp((string) $attendance->punchin_from, 'App') === 0) {
            [$latitude, $longitude] = [$longitude, $latitude];
          } else {
            $directIsIndia = $latitude >= 6 && $latitude <= 38 && $longitude >= 68 && $longitude <= 98;
            $swappedIsIndia = $longitude >= 6 && $longitude <= 38 && $latitude >= 68 && $latitude <= 98;
            if (!$directIsIndia && $swappedIsIndia) {
              [$latitude, $longitude] = [$longitude, $latitude];
            }
          }

          if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ||
              (abs($latitude) < 0.000001 && abs($longitude) < 0.000001)) {
            return null;
          }

          return [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'name' => optional($user)->name ?: 'Unknown user',
            'employee_code' => optional($user)->employee_codes ?: '',
            'designation' => optional(optional($user)->getdesignation)->designation_name ?: 'Field employee',
            'zone' => optional(optional($user)->getdivision)->division_name ?: '',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $attendance->punchin_address ?: 'Address unavailable',
            'time' => $attendance->punchin_time ? Carbon::parse($attendance->punchin_time)->format('h:i A') : '--',
          ];
        })
        ->filter()
        ->values();

      return view('beats.punchin_locator', compact('punchIns'));
    }

    public function customerLocator()
    {
      $accessibleUserIds = getUsersReportingToAuth();
      $punchIns = CheckIn::with([
          'customer.customertypes',
          'user.getdesignation',
          'user.getdivision',
        ])
        ->whereIn('user_id', $accessibleUserIds)
        ->whereDate('checkin_date', Carbon::today())
        ->where(function ($query) {
          $query->whereNull('entity_type')->orWhere('entity_type', 'customer');
        })
        ->whereNotNull('customer_id')
        ->whereNotNull('checkin_latitude')
        ->whereNotNull('checkin_longitude')
        ->orderBy('checkin_time')
        ->get()
        ->map(function ($checkIn) {
          if (!is_numeric($checkIn->checkin_latitude) || !is_numeric($checkIn->checkin_longitude)) {
            return null;
          }

          $latitude = (float) $checkIn->checkin_latitude;
          $longitude = (float) $checkIn->checkin_longitude;
          if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ||
              (abs($latitude) < 0.000001 && abs($longitude) < 0.000001)) {
            return null;
          }

          $customer = $checkIn->customer;
          $user = $checkIn->user;
          $customerName = optional($customer)->name ?: trim((optional($customer)->first_name ?? '') . ' ' . (optional($customer)->last_name ?? ''));

          return [
            'attendance_id' => $checkIn->id,
            'name' => $customerName ?: 'Unknown customer',
            'employee_code' => optional($customer)->customer_code ?: '',
            'designation' => optional(optional($customer)->customertypes)->customertype_name ?: 'Customer',
            'zone' => optional(optional($user)->getdivision)->division_name ?: '',
            'representative' => optional($user)->name ?: 'Unknown employee',
            'representative_role' => optional(optional($user)->getdesignation)->designation_name ?: 'Field employee',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $checkIn->checkin_address ?: 'Address unavailable',
            'time' => $checkIn->checkin_time ? Carbon::parse($checkIn->checkin_time)->format('h:i A') : '--',
          ];
        })
        ->filter()
        ->values();

      $locatorMode = 'customer';
      return view('beats.punchin_locator', compact('punchIns', 'locatorMode'));
    }

  public function globalScheduleForm()
  {
      $users = User::where('status',1)->get();
      $beats = Beat::all();

      return view('beats.global_schedule_form', compact('users','beats'));
  }

  /**
   * The Beat Customer dropdown posts the counter id plus the master it came
   * from ("retailer" / "distributor" / "customer"). Older markup posted a
   * combined "type_id" value, so both shapes are accepted here.
   *
   * @return array|null  [customer_type, id]
   */
  private function parseBeatCustomerSelection($value, $type = null)
  {
    $value = trim((string) $value);
    if ($value === '') {
      return null;
    }

    if (!is_numeric($value) && strpos($value, '_') !== false) {
      list($type, $value) = explode('_', $value, 2);
    }

    $type = strtolower(trim((string) $type));
    $map = [
      'distributor' => 'master',
      'master' => 'master',
      'retailer' => 'secondary',
      'secondary' => 'secondary',
      'customer' => 'customer',
    ];

    if (!isset($map[$type]) || !is_numeric($value)) {
      return null;   // never store a row we cannot resolve back to a counter
    }

    return [$map[$type], (int) $value];
  }

  /**
   * Beat Route Optimized – filter screen. The map stays blank until a user and
   * date are chosen, exactly like the geolocator screen.
   */
  public function beatRouteOptimizer()
  {
    $accessibleUserIds = getUsersReportingToAuth();

    // only users who actually carry a beat: assigned via beat_users or scheduled
    $beatUserIds = BeatUser::whereIn('user_id', $accessibleUserIds)->pluck('user_id')
      ->merge(BeatSchedule::whereIn('user_id', $accessibleUserIds)->pluck('user_id'))
      ->unique()
      ->values();

    $users = User::whereDoesntHave('roles', function ($query) {
      $query->whereIn('id', config('constants.customer_roles'));
    })
      ->whereIn('id', $beatUserIds)
      ->orderBy('name')
      ->get(['id', 'name', 'employee_codes'])
      ->map(function ($user) {
        return [
          'id' => $user->id,
          'name' => $user->employee_codes ? $user->name . ' (' . $user->employee_codes . ')' : $user->name,
        ];
      })
      ->values();

    return view('beats.routeoptimizer', compact('users'));
  }

  /**
   * Builds the optimized visit sequence for one user on one date:
   * beat of the day -> aligned counters -> nearest neighbour ordering from the
   * user's start point -> per stop distance, ETA and visit priority.
   */
  public function beatRouteOptimizerData(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
      'date' => 'required|date',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 200);
    }

    $userId = (int) $request->user_id;
    $date = Carbon::parse($request->date)->format('Y-m-d');

    $accessibleUserIds = getUsersReportingToAuth();
    if (!in_array($userId, (array) $accessibleUserIds)) {
      return response()->json(['status' => 'error', 'message' => 'You are not allowed to view this user.'], 200);
    }

    $user = User::find($userId);
    if (!$user) {
      return response()->json(['status' => 'error', 'message' => 'User not found.'], 200);
    }

    // ---- beat of the day (fall back to the user's assigned beats) ----
    $schedules = BeatSchedule::with('beats')
      ->where('user_id', $userId)
      ->whereDate('beat_date', $date)
      ->get();

    $planSource = 'scheduled';
    $beatIds = $schedules->pluck('beat_id')->filter()->unique()->values();

    if ($beatIds->isEmpty()) {
      $planSource = 'assigned';
      $beatIds = BeatUser::where('user_id', $userId)->pluck('beat_id')->filter()->unique()->values();
    }

    if ($beatIds->isEmpty()) {
      return response()->json([
        'status' => 'empty',
        'message' => 'No beat is scheduled or assigned to ' . $user->name . ' for ' . Carbon::parse($date)->format('d M Y') . '.',
      ], 200);
    }

    $beatNames = Beat::whereIn('id', $beatIds)->pluck('beat_name', 'id');

    // ---- counters aligned to those beats ----
    $beatCustomers = BeatCustomer::with(['retailerFull.city', 'distributorFull'])
      ->whereIn('beat_id', $beatIds)
      ->get();

    $stops = [];
    foreach ($beatCustomers as $beatCustomer) {
      $stop = $this->mapBeatCustomerToStop($beatCustomer, $beatNames);
      if ($stop) {
        $stops[$stop['key']] = $stop;   // key dedupes a counter aligned to two beats
      }
    }
    $stops = array_values($stops);

    if (empty($stops)) {
      return response()->json([
        'status' => 'empty',
        'message' => 'No counter is aligned to ' . implode(', ', $beatNames->toArray()) . '.',
      ], 200);
    }

    // ---- visit history for priority + today's progress ----
    $this->attachVisitHistory($stops, $userId, $date);

    // ---- start point: punch in, else first GPS ping of the day ----
    $start = $this->resolveRouteStartPoint($userId, $date);

    // ---- nearest neighbour ordering ----
    $mapped = array_values(array_filter($stops, function ($stop) {
      return $stop['latitude'] !== null && $stop['longitude'] !== null;
    }));
    $unmapped = array_values(array_filter($stops, function ($stop) {
      return $stop['latitude'] === null || $stop['longitude'] === null;
    }));

    $ordered = $this->orderStopsByNearestNeighbour($mapped, $start);

    // ---- distance, ETA and sequence numbers ----
    $startTime = Carbon::parse($date . ' ' . ($start['time'] ?? '09:00:00'));
    $clock = $startTime->copy();
    $totalKm = 0;
    $previous = $start && $start['latitude'] !== null ? $start : null;

    foreach ($ordered as $index => &$stop) {
      $legKm = $previous
        ? round(haversineGreatCircleDistance($previous['latitude'], $previous['longitude'], $stop['latitude'], $stop['longitude']), 1)
        : 0;

      $totalKm += $legKm;
      $clock->addMinutes((int) round(($legKm / self::ROUTE_AVG_SPEED_KMPH) * 60));

      $stop['sequence'] = $index + 1;
      $stop['leg_km'] = $legKm;
      $stop['eta'] = $clock->format('g:i A');

      $clock->addMinutes(self::ROUTE_STOP_MINUTES);
      $previous = $stop;
    }
    unset($stop);

    foreach ($unmapped as $index => &$stop) {
      $stop['sequence'] = count($ordered) + $index + 1;
      $stop['leg_km'] = null;
      $stop['eta'] = null;
    }
    unset($stop);

    $route = array_merge($ordered, $unmapped);

    return response()->json([
      'status' => 'success',
      'plan_source' => $planSource,
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'designation' => optional($user->getdesignation)->designation_name ?? 'Field employee',
      ],
      'beat_name' => implode(', ', $beatNames->toArray()),
      'date' => Carbon::parse($date)->format('d M Y'),
      'start' => $start,
      'summary' => [
        'stops' => count($route),
        'mapped_stops' => count($ordered),
        'unmapped_stops' => count($unmapped),
        'visited' => count(array_filter($route, function ($stop) { return $stop['visited']; })),
        'route_km' => round($totalKm, 1),
        'start_time' => $startTime->format('g:i A'),
        'finish_time' => count($ordered) ? $clock->format('g:i A') : null,
      ],
      'stops' => $route,
    ], 200);
  }

  const ROUTE_AVG_SPEED_KMPH = 20;   // city running average used for the ETA
  const ROUTE_STOP_MINUTES = 15;     // assumed time spent at each counter

  /**
   * beat_customers row -> map stop. Coordinates live in gps_location ("lat,lng")
   * for master/secondary counters and in lat/long columns for legacy customers.
   */
  private function mapBeatCustomerToStop($beatCustomer, $beatNames)
  {
    $beatName = $beatNames[$beatCustomer->beat_id] ?? '';

    if ($beatCustomer->customer_type === 'master' && $beatCustomer->distributorFull) {
      $entity = $beatCustomer->distributorFull;
      [$latitude, $longitude] = $this->splitGpsLocation($entity->gps_location);

      return [
        'key' => 'distributor:' . $entity->id,
        'entity_type' => 'distributor',
        'entity_id' => $entity->id,
        'name' => $entity->trade_name ?: $entity->legal_name ?: 'Unnamed distributor',
        'code' => $entity->distributor_code ?: '',
        'category' => $entity->category ?: 'Distributor',
        'mobile' => $entity->mobile ?: '',
        'address' => trim(($entity->billing_address ?? '') . ' ' . ($entity->billing_city ?? '')),
        'city' => $entity->billing_city ?: '',
        'beat_name' => $beatName,
        'latitude' => $latitude,
        'longitude' => $longitude,
      ];
    }

    if ($beatCustomer->customer_type === 'secondary' && $beatCustomer->retailerFull) {
      $entity = $beatCustomer->retailerFull;
      [$latitude, $longitude] = $this->splitGpsLocation($entity->gps_location);

      return [
        'key' => 'secondary_customer:' . $entity->id,
        'entity_type' => 'secondary_customer',
        'entity_id' => $entity->id,
        'name' => $entity->shop_name ?: $entity->owner_name ?: 'Unnamed counter',
        'code' => $entity->owner_name ?: '',
        'category' => $entity->sub_type ?: ($entity->type ?: 'Retailer'),
        'mobile' => $entity->mobile_number ?: '',
        'address' => trim(($entity->address_line ?? '') . ' ' . (optional($entity->city)->city_name ?? '')),
        'city' => optional($entity->city)->city_name ?: '',
        'beat_name' => $beatName,
        'latitude' => $latitude,
        'longitude' => $longitude,
      ];
    }

    if ($beatCustomer->customer_id) {
      $entity = \App\Models\Customers::find($beatCustomer->customer_id);
      if (!$entity) {
        return null;
      }

      return [
        'key' => 'customer:' . $entity->id,
        'entity_type' => 'customer',
        'entity_id' => $entity->id,
        'name' => $entity->name ?: trim(($entity->first_name ?? '') . ' ' . ($entity->last_name ?? '')),
        'code' => $entity->customer_code ?: '',
        'category' => 'Customer',
        'mobile' => $entity->mobile ?: ($entity->contact_number ?: ''),
        'address' => '',
        'city' => '',
        'beat_name' => $beatName,
        'latitude' => is_numeric($entity->latitude) ? (float) $entity->latitude : null,
        'longitude' => is_numeric($entity->longitude) ? (float) $entity->longitude : null,
      ];
    }

    return null;
  }

  private function splitGpsLocation($gpsLocation)
  {
    if (empty($gpsLocation) || strpos($gpsLocation, ',') === false) {
      return [null, null];
    }

    $parts = array_map('trim', explode(',', $gpsLocation));
    if (count($parts) < 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
      return [null, null];
    }

    return [(float) $parts[0], (float) $parts[1]];
  }

  /**
   * Marks which counters are already done on the selected date and how long it
   * has been since the last visit, which drives the priority badge.
   */
  private function attachVisitHistory(&$stops, $userId, $date)
  {
    $entityIds = array_column($stops, 'entity_id');

    $todaysCheckIns = CheckIn::where('user_id', $userId)
      ->whereDate('checkin_date', $date)
      ->get(['entity_type', 'entity_id', 'customer_id', 'checkin_time']);

    $lastVisits = CheckIn::selectRaw('entity_type, entity_id, customer_id, MAX(checkin_date) as last_date')
      ->where(function ($query) use ($entityIds) {
        $query->whereIn('entity_id', $entityIds)->orWhereIn('customer_id', $entityIds);
      })
      ->whereDate('checkin_date', '<', $date)
      ->groupBy('entity_type', 'entity_id', 'customer_id')
      ->get();

    foreach ($stops as &$stop) {
      $done = $todaysCheckIns->first(function ($checkIn) use ($stop) {
        return ($checkIn->entity_type === $stop['entity_type'] && (int) $checkIn->entity_id === (int) $stop['entity_id'])
          || ($stop['entity_type'] === 'customer' && (int) $checkIn->customer_id === (int) $stop['entity_id']);
      });

      $last = $lastVisits
        ->filter(function ($row) use ($stop) {
          return ($row->entity_type === $stop['entity_type'] && (int) $row->entity_id === (int) $stop['entity_id'])
            || ($stop['entity_type'] === 'customer' && (int) $row->customer_id === (int) $stop['entity_id']);
        })
        ->max('last_date');

      $daysSince = $last ? (int) abs(Carbon::parse($last)->diffInDays(Carbon::parse($date))) : null;

      $stop['visited'] = (bool) $done;
      $stop['visited_at'] = $done && $done->checkin_time ? date('g:i A', strtotime($done->checkin_time)) : null;
      $stop['last_visit'] = $last ? Carbon::parse($last)->format('d M Y') : null;
      $stop['days_since_visit'] = $daysSince;

      if ($done) {
        $stop['priority'] = 'visited';
        $stop['priority_label'] = 'VISITED';
      } elseif ($daysSince === null) {
        $stop['priority'] = 'new';
        $stop['priority_label'] = 'NEW COUNTER';
      } elseif ($daysSince > 30) {
        $stop['priority'] = 'overdue';
        $stop['priority_label'] = 'OVERDUE VISIT';
      } elseif ($daysSince >= 15) {
        $stop['priority'] = 'followup';
        $stop['priority_label'] = 'FOLLOW-UP DUE';
      } else {
        $stop['priority'] = 'routine';
        $stop['priority_label'] = 'ROUTINE CHECK-IN';
      }
    }
    unset($stop);
  }

  /**
   * Route origin: punch-in location of the day, else the first GPS ping.
   * Punch-in lat/long are stored in swapped columns by AttendanceController.
   */
  private function resolveRouteStartPoint($userId, $date)
  {
    $attendance = Attendance::where('user_id', $userId)
      ->where('punchin_date', $date)
      ->first();

    if ($attendance && is_numeric($attendance->punchin_longitude) && is_numeric($attendance->punchin_latitude)) {
      return [
        'latitude' => (float) $attendance->punchin_longitude,
        'longitude' => (float) $attendance->punchin_latitude,
        'label' => 'Punch In',
        'address' => $attendance->punchin_address ?: '',
        'time' => $attendance->punchin_time ?: '09:00:00',
      ];
    }

    $firstPing = UserLiveLocation::where('userid', $userId)
      ->whereDate('created_at', $date)
      ->whereNotNull('latitude')
      ->whereNotNull('longitude')
      ->orderBy('id')
      ->first();

    if ($firstPing && is_numeric($firstPing->latitude) && is_numeric($firstPing->longitude)) {
      return [
        'latitude' => (float) $firstPing->latitude,
        'longitude' => (float) $firstPing->longitude,
        'label' => 'Day start',
        'address' => $firstPing->address ?: '',
        'time' => $firstPing->time ? date('H:i:s', strtotime($firstPing->time)) : '09:00:00',
      ];
    }

    return [
      'latitude' => null,
      'longitude' => null,
      'label' => 'Beat start',
      'address' => '',
      'time' => '09:00:00',
    ];
  }

  /**
   * Greedy nearest neighbour: from the start point always hop to the closest
   * counter that has not been visited by the route yet.
   */
  private function orderStopsByNearestNeighbour($stops, $start)
  {
    if (count($stops) < 2) {
      return $stops;
    }

    $remaining = $stops;
    $ordered = [];

    $currentLat = $start['latitude'];
    $currentLng = $start['longitude'];

    if ($currentLat === null || $currentLng === null) {
      $first = array_shift($remaining);
      $ordered[] = $first;
      $currentLat = $first['latitude'];
      $currentLng = $first['longitude'];
    }

    while (!empty($remaining)) {
      $nearestIndex = 0;
      $nearestDistance = null;

      foreach ($remaining as $index => $candidate) {
        $distance = haversineGreatCircleDistance($currentLat, $currentLng, $candidate['latitude'], $candidate['longitude']);
        if ($nearestDistance === null || $distance < $nearestDistance) {
          $nearestDistance = $distance;
          $nearestIndex = $index;
        }
      }

      $next = $remaining[$nearestIndex];
      unset($remaining[$nearestIndex]);
      $remaining = array_values($remaining);

      $ordered[] = $next;
      $currentLat = $next['latitude'];
      $currentLng = $next['longitude'];
    }

    return $ordered;
  }
  }
