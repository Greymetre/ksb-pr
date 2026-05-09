<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getStates($country_id)
    {
        // Safety check
        if (!is_numeric($country_id)) {
            return response()->json([], 200);
        }

        $states = State::where('country_id', $country_id)
            ->where('active', 'Y')
            ->orderBy('state_name', 'asc')
            ->get(['id', 'state_name']);

        return response()->json($states);
    }

    //district controller
    public function getDistricts($state_id)
    {
        if (!is_numeric($state_id)) {
            return response()->json([], 200);
        }

        $districts = \App\Models\District::where('state_id', $state_id)
            ->where('active', 'Y')
            ->orderBy('district_name', 'asc')
            ->get(['id', 'district_name']);

        return response()->json($districts);
    }

    //city controller
    public function getCities($district_id)
    {
        if (!is_numeric($district_id)) {
            return response()->json([], 200);
        }

        $cities = \App\Models\City::where('district_id', $district_id)
            ->where('active', 'Y')
            ->orderBy('city_name', 'asc')
            ->get(['id', 'city_name']);

        return response()->json($cities);
    }

    //pincode controller
    public function getPincodes($city_id)
    {
        if (!is_numeric($city_id)) {
            return response()->json([], 200);
        }

        $pincodes = \App\Models\Pincode::where('city_id', $city_id)
            ->where('active', 'Y')
            ->orderBy('pincode', 'asc')
            ->get(['id', 'pincode']);

        return response()->json($pincodes);
    }
}
