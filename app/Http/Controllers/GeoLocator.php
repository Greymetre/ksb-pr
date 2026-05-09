<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Branch;
use App\Models\City;
use App\Models\Country;
use App\Models\CustomerDetails;
use App\Models\Customers;
use App\Models\District;
use App\Models\EmployeeDetail;
use App\Models\GeoLocatorSetting;
use App\Models\Lead;
use App\Models\Pincode;
use App\Models\State;
use App\Models\Status;
use App\Models\CustomerCustomField;
use App\Models\User;
use Illuminate\Http\Request;

class GeoLocator extends Controller
{
    public function geo_locator_setting(Request $request)
    {
        $setting = GeoLocatorSetting::first();

        return view('geolocator.setting', compact('setting'));
    }

    public function geo_locator_setting_store(Request $request)
    {
        $geoLocator = GeoLocatorSetting::updateOrCreate(
            ['id' => 1], // always maintain single record
            [
                'customer_filter' => $request->customer_filter,
                'lead_filter'     => $request->lead_filter,
            ]
        );

        return redirect()->route('geo_locator_setting')->with('success', 'Geo Locator Setting Updated Successfully');
    }

    public function map()
    {
        $setting = GeoLocatorSetting::first();
        $cutom_fields = CustomerCustomField::all();
        return view('geolocator.map', compact('setting', 'cutom_fields'));
    }

    public function data(Request $request)
    {
        $customers = Customers::whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '')->select('id', 'name', 'first_name', 'last_name', 'mobile', 'latitude', 'longitude', 'customertype')->with('customeraddress', 'customertypes', 'customerdetails');
        if ($request->type == '1') {

            if (isset($request->filter_by) && !empty($request->filter_by)) {
                $custom_field = filter_var($request->custom_field, FILTER_VALIDATE_BOOLEAN);
                if ($custom_field) {
                    $customers->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$request->filter_by}\"')) = ?", [$request->filter]);
                } else {

                    if ($request->filter_by == 'City') {
                        $city_id = City::where('city_name', $request->filter)->pluck('id')->first();
                        $customers->whereHas('customeraddress', function ($query) use ($city_id) {
                            $query->where('city_id', $city_id);
                        });
                    }
                    if ($request->filter_by == 'Customer Type') {
                        $customers->whereHas('customertypes', function ($query) use ($request) {
                            $query->where('customertype_name', $request->filter);
                        });
                    }
                    if ($request->filter_by == 'State') {
                        $state_id = State::where('state_name', $request->filter)->pluck('id')->first();
                        $customers->whereHas('customeraddress', function ($query) use ($state_id) {
                            $query->where('state_id', $state_id);
                        });
                    }
                    if ($request->filter_by == 'Pincode') {
                        $pincode_id = Pincode::where('pincode', $request->filter)->pluck('id');
                        $customers->whereHas('customeraddress', function ($query) use ($pincode_id) {
                            $query->whereIn('pincode_id', $pincode_id);
                        });
                    }
                    if ($request->filter_by == 'District') {
                        $district_id = District::where('district_name', $request->filter)->pluck('id')->first();
                        $customers->whereHas('customeraddress', function ($query) use ($district_id) {
                            $query->where('district_id', $district_id);
                        });
                    }
                    if ($request->filter_by == 'Employee Name') {
                        $user_id = User::where('name', $request->filter)->pluck('id')->first();
                        $customer_ids = EmployeeDetail::where('user_id', $user_id)->pluck('customer_id');
                        $customers->whereIn('id', $customer_ids);
                    }
                    if ($request->filter_by == 'search') {
                        $customers->where(function ($query) use ($request) {
                            $query->where('name', 'like', '%' . $request->filter . '%')
                                ->orWhere('mobile', 'like', '%' . $request->filter . '%');
                        });
                    }
                    if ($request->filter_by == 'Grade') {
                        $customers->whereHas('customerdetails', function ($query) use ($request) {
                            $query->where('grade', $request->filter);
                        });
                    }
                    if ($request->filter_by == 'Branch Name') {
                        $branch_id = Branch::where('branch_name', $request->filter)->pluck('id')->first();
                        $user_id = User::where('branch_id', $branch_id)->pluck('id');
                        $customer_ids = EmployeeDetail::whereIn('user_id', $user_id)->pluck('customer_id');
                        $customers->whereIn('id', $customer_ids);
                    }
                }
            }
            $customers = $customers->get();
        } else {
            $customers = Lead::whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '')->select('id', 'company_name', 'lead_source', 'latitude', 'longitude', 'status', 'assign_to')->with('address', 'contacts', 'status_is', 'assign_user', 'opportunities.status_is');

            if ($request->filter_by == 'City') {
                $city_id = City::where('city_name', $request->filter)->pluck('id')->first();
                $customers->whereHas('address', function ($query) use ($city_id) {
                    $query->where('city_id', $city_id);
                });
            }
            if ($request->filter_by == 'Customer Type') {
                $customers->whereHas('customertypes', function ($query) use ($request) {
                    $query->where('customertype_name', $request->filter);
                });
            }
            if ($request->filter_by == 'State') {
                $state_id = State::where('state_name', $request->filter)->pluck('id')->first();
                $customers->whereHas('address', function ($query) use ($state_id) {
                    $query->where('state_id', $state_id);
                });
            }
            if ($request->filter_by == 'Pincode') {
                $pincode_id = Pincode::where('pincode', $request->filter)->pluck('id')->first();
                $customers->whereHas('address', function ($query) use ($pincode_id) {
                    $query->where('pincode_id', $pincode_id);
                });
            }
            if ($request->filter_by == 'District') {
                $district_id = District::where('district_name', $request->filter)->pluck('id')->first();
                $customers->whereHas('address', function ($query) use ($district_id) {
                    $query->where('district_id', $district_id);
                });
            }
            if ($request->filter_by == 'search') {
                $customers->where('company_name', 'like', '%' . $request->filter . '%');
            }
            if ($request->filter_by == 'Lead Source') {
                $customers->where('lead_source', $request->filter);
            }
            if ($request->filter_by == 'Lead Status') {
                $status_id = Status::where('status_name', $request->filter)->pluck('id')->first();
                $customers->where('status', $status_id);
            }
            if ($request->filter_by == 'Assignee') {
                $user_id = User::where('name', $request->filter)->pluck('id')->first();
                $customers->where('assign_to', $user_id);
            }
            $customers = $customers->get();
        }
        return response()->json($customers);
    }

    public function filter_data(Request $request)
    {
        $data = [];
        if ($request->type == '1') {
            $custom_field = filter_var($request->custom_field, FILTER_VALIDATE_BOOLEAN);
            if ($custom_field) {
                $fieldName = $request->filter;
                $data = Customers::whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->where('latitude', '!=', '')
                    ->where('longitude', '!=', '')
                    ->whereRaw("JSON_EXTRACT(custom_fields, '$.\"{$fieldName}\"') IS NOT NULL")
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$fieldName}\"')) != ''")
                    ->whereRaw("LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$fieldName}\"')))) != 'null'")
                    ->select(
                        \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$fieldName}\"')) as custom_value"),
                        \DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('custom_value')
                    ->pluck('total', 'custom_value')
                    ->toArray();
            } else {
                if ($request->filter == 'City') {
                    $data = Address::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                    })
                        ->whereNotNull('city_id')
                        ->select('city_id', \DB::raw('COUNT(*) as total'))
                        ->groupBy('city_id')
                        ->with('cityname:id,city_name')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [
                                $item->cityname->city_name ?? 'Unknown' => $item->total
                            ];
                        })
                        ->toArray();
                } elseif ($request->filter == 'Customer Type') {
                    $data = Customers::whereNotNull('latitude')->whereNotNull('longitude')->whereNotNull('customertype')->where('latitude', '!=', '')->where('longitude', '!=', '')
                        ->select('customertype', \DB::raw('COUNT(*) as total'))
                        ->groupBy('customertype')
                        ->with('customertypes:id,customertype_name')
                        ->get()
                        ->pluck('total', 'customertypes.customertype_name')
                        ->toArray();
                } elseif ($request->filter == 'State') {
                    $data = Address::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                    })
                        ->whereNotNull('state_id')
                        ->select('state_id', \DB::raw('COUNT(*) as total'))
                        ->groupBy('state_id')
                        ->with('statename:id,state_name')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [
                                $item->statename->state_name ?? 'Unknown' => $item->total
                            ];
                        })
                        ->toArray();
                } elseif ($request->filter == 'Pincode') {
                    $data = Address::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                    })
                        ->whereNotNull('pincode_id')
                        ->select('pincode_id', \DB::raw('COUNT(*) as total'))
                        ->groupBy('pincode_id')
                        ->with('pincodename:id,pincode')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [
                                $item->pincodename->pincode ?? 'Unknown' => $item->total
                            ];
                        })
                        ->toArray();
                } elseif ($request->filter == 'District') {
                    $data = Address::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                    })
                        ->whereNotNull('district_id')
                        ->select('district_id', \DB::raw('COUNT(*) as total'))
                        ->groupBy('district_id')
                        ->with('districtname:id,district_name')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [
                                $item->districtname->district_name ?? 'Unknown' => $item->total
                            ];
                        })
                        ->toArray();
                } elseif ($request->filter == 'Employee Name') {
                    $data = EmployeeDetail::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                    })
                        ->select('user_id', \DB::raw('COUNT(*) as total'))
                        ->groupBy('user_id')
                        ->with('employee_detail:id,name')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [
                                $item->employee_detail->name ?? 'Unknown' => $item->total
                            ];
                        })
                        ->toArray();
                } elseif ($request->filter == 'Grade') {
                    $data = CustomerDetails::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                    })
                        ->whereNotNull('grade')
                        ->where('grade', '!=', '')
                        ->select('grade', \DB::raw('COUNT(*) as total'))
                        ->groupBy('grade')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [
                                $item->grade ?? 'Unknown' => $item->total
                            ];
                        })
                        ->toArray();
                } elseif ($request->filter == 'Branch Name') {
                    $data = EmployeeDetail::whereHas('customer', function ($query) {
                        $query->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->where('latitude', '!=', '')
                            ->where('longitude', '!=', '');
                    })
                        ->whereHas('employee_detail', function ($query) {
                            $query->whereNotNull('branch_id');
                        })
                        ->with('employee_detail.getbranch:id,branch_name') // load branch via user
                        ->select('user_id', \DB::raw('COUNT(*) as total'))
                        ->groupBy('user_id')
                        ->get()
                        ->groupBy(fn($item) => $item->employee_detail->getbranch->branch_name ?? 'Unknown')
                        ->map(fn($group) => $group->sum('total'))
                        ->toArray();
                }
            }
        } else {
            if ($request->filter == 'City') {
                $data = Address::whereHas('lead', function ($query) {
                    $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                })
                    ->whereNotNull('city_id')
                    ->select('city_id', \DB::raw('COUNT(*) as total'))
                    ->groupBy('city_id')
                    ->with('cityname:id,city_name')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->cityname->city_name ?? 'Unknown' => $item->total
                        ];
                    })
                    ->toArray();
            } elseif ($request->filter == 'Customer Type') {
                $data = Customers::whereNotNull('latitude')->whereNotNull('longitude')->whereNotNull('customertype')->where('latitude', '!=', '')->where('longitude', '!=', '')
                    ->select('customertype', \DB::raw('COUNT(*) as total'))
                    ->groupBy('customertype')
                    ->with('customertypes:id,customertype_name')
                    ->get()
                    ->pluck('total', 'customertypes.customertype_name')
                    ->toArray();
            } elseif ($request->filter == 'State') {
                $data = Address::whereHas('lead', function ($query) {
                    $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                })
                    ->whereNotNull('state_id')
                    ->select('state_id', \DB::raw('COUNT(*) as total'))
                    ->groupBy('state_id')
                    ->with('statename:id,state_name')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->statename->state_name ?? 'Unknown' => $item->total
                        ];
                    })
                    ->toArray();
            } elseif ($request->filter == 'Pincode') {
                $data = Address::whereHas('lead', function ($query) {
                    $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                })
                    ->whereNotNull('pincode_id')
                    ->select('pincode_id', \DB::raw('COUNT(*) as total'))
                    ->groupBy('pincode_id')
                    ->with('pincodename:id,pincode')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->pincodename->pincode ?? 'Unknown' => $item->total
                        ];
                    })
                    ->toArray();
            } elseif ($request->filter == 'District') {
                $data = Address::whereHas('lead', function ($query) {
                    $query->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '');
                })
                    ->whereNotNull('district_id')
                    ->select('district_id', \DB::raw('COUNT(*) as total'))
                    ->groupBy('district_id')
                    ->with('districtname:id,district_name')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->districtname->district_name ?? 'Unknown' => $item->total
                        ];
                    })
                    ->toArray();
            } elseif ($request->filter == 'Lead Source') {
                $data = Lead::whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '')
                    ->select('lead_source', \DB::raw('COUNT(*) as total'))
                    ->groupBy('lead_source')
                    ->get()
                    ->pluck('total', 'lead_source')
                    ->toArray();
            } elseif ($request->filter == 'Lead Status') {
                $data = Lead::whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '')
                    ->whereNotNull('status')
                    ->select('status', \DB::raw('COUNT(*) as total'))
                    ->groupBy('status')
                    ->with('status_is:id,status_name')
                    ->get()
                    ->pluck('total', 'status_is.status_name')
                    ->toArray();
            } elseif ($request->filter == 'Assignee') {
                $data = Lead::whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', '')->where('longitude', '!=', '')
                    ->whereNotNull('assign_to')
                    ->select('assign_to', \DB::raw('COUNT(*) as total'))
                    ->groupBy('assign_to')
                    ->with('assign_user:id,name')
                    ->get()
                    ->pluck('total', 'assign_user.name')
                    ->toArray();
            }
        }
        return response()->json($data);
    }

    public function customerSuggestions(Request $request)
    {
        $type = $request->get('type');
        $search = $request->get('search');
        if ($type == '1') {
            $customers = Customers::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%');
            })
                ->whereNotNull('longitude')->where('latitude', '!=', '')
                ->take(10)
                ->get(['id', 'name']);
        } else if ($type == '2') {
            $customers = Lead::where('company_name', 'like', "%{$search}%")->whereNotNull('longitude')->where('latitude', '!=', '')
                ->take(10)
                ->get(['id', 'company_name  as name']);
        }
        return response()->json($customers);
    }
}
