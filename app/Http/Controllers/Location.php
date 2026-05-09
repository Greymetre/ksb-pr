<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Location extends Controller
{
    class LocationController extends Controller
{
    public function getStates($countryId)
    {
        return State::where('country_id', $countryId)
            ->where('active','Y')
            ->orderBy('state_name')
            ->get();
    }

    public function getDistricts($stateId)
    {
        return District::where('state_id', $stateId)
            ->where('active','Y')
            ->orderBy('district_name')
            ->get();
    }

    public function getCities($districtId)
    {
        return City::where('district_id', $districtId)
            ->where('active','Y')
            ->orderBy('city_name')
            ->get();
    }

    public function getPincodes($cityId)
    {
        return Pincode::where('city_id', $cityId)
            ->where('active','Y')
            ->orderBy('pincode')
            ->get();
    }
}

}
