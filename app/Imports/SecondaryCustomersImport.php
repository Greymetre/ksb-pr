<?php

namespace App\Imports;

use App\Models\SecondaryCustomer;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\City;
use App\Models\Pincode;
use App\Models\Beat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SecondaryCustomersImport implements ToCollection, WithHeadingRow
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type ;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            $rowNumber = $index + 2; // because heading row

            try {

                // Country
                $countryValue = trim($row['country']);

                if (is_numeric($countryValue)) {
                    $country = Country::find($countryValue);
                } else {
                    $country = Country::where('country_name', $countryValue)->first();
                }

                if (!$country) {
                    throw new \Exception("Invalid country");
                }

                // State
                $stateValue = trim($row['state']);

                $state = is_numeric($stateValue)
                    ? State::find($stateValue)
                    : State::where('state_name', $stateValue)
                        ->where('country_id', $country->id)
                        ->first();

                if (!$state) {
                    throw new \Exception("Invalid state");
                }

                // District
                $districtValue = trim($row['district']);

                $district = is_numeric($districtValue)
                    ? District::find($districtValue)
                    : District::where('district_name', $districtValue)
                        ->where('state_id', $state->id)
                        ->first();

                if (!$district) {
                    throw new \Exception("Invalid district");
                }

                // City
                $cityValue = trim($row['city']);

                $city = is_numeric($cityValue)
                    ? City::find($cityValue)
                    : City::where('city_name', $cityValue)
                        ->where('district_id', $district->id)
                        ->first();

                if (!$city) {
                    throw new \Exception("Invalid city");
                }

                // Duplicate mobile check
                if (SecondaryCustomer::where('mobile_number', $row['mobile_number'])->exists()) {
                    throw new \Exception("Duplicate mobile");
                }

                // Pincode
                $pincodeValue = trim($row['pincode']);

                $pincode = is_numeric($pincodeValue)
                    ? Pincode::find($pincodeValue)
                    : Pincode::where('pincode', $pincodeValue)
                        ->where('city_id', $city->id)
                        ->first();

                if (!$pincode) {
                    throw new \Exception("Invalid pincode");
                }

                $beat = Beat::find(trim($row['beat']));

if (!$beat) {
    throw new \Exception('Invalid beat id: ' . $row['beat']);
}
                $data = [
                    'type' => $this->type,
                    'sub_type' => $row['sub_type'] ?? null,
                    'owner_name' => $row['owner_name'],
                    'shop_name' => $row['shop_name'],
                    'mobile_number' => $row['mobile_number'],
                    'whatsapp_number' => $row['whatsapp_number'] ?? null,
                    'vehicle_segment' => $row['vehicle_segment'] ?? null,
                    'address_line' => $row['address_line'],
                    'belt_area_market_name' => $row['belt_area_market_name'] ?? null,
                    'gps_location' => $row['gps_location'] ?? null,
                    'country_id' => $country?->id,
                    'state_id' => $state?->id,
                    'district_id' => $district?->id,
                    'city_id' => $city?->id,
                    'pincode_id' => $pincode?->id,
                    'beat_id' => $beat?->id,
                    'opportunity_status' => strtoupper($row['opportunity_status']),
                ];

                if (in_array($this->type, ['RETAILER', 'WORKSHOP'])) {
                    $data['nistha_awareness_status'] = $row['awareness_status'];
                } else {
                    $data['saathi_awareness_status'] = $row['awareness_status'];
                }

                SecondaryCustomer::create($data);

            } catch (\Exception $e) {

                $this->errors[] = "Row $rowNumber → ".$e->getMessage();
            }
        }
    }
}