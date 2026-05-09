<?php

namespace App\Imports;

use App\Models\MasterDistributor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class MasterDistributorsImport implements
    OnEachRow,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

// class MasterDistributorsImport implements
//     OnEachRow,
//     WithHeadingRow,
//     WithValidation,
//     SkipsOnError
// {
// use SkipsErrors;

    // ────────────────────────────────────────────────
    // Add these three things
    protected $importedCount = 0;

    public function getImportedRowCount(): int
    {
        return $this->importedCount;
    }
    // ────────────────────────────────────────────────


  private function validateLocationHierarchy($row)
{
    $countryId   = !empty($row['billing_country_id'])   ? (int) $row['billing_country_id']   : null;
    $stateId     = !empty($row['billing_state_id'])     ? (int) $row['billing_state_id']     : null;
    $districtId  = !empty($row['billing_district_id'])  ? (int) $row['billing_district_id']  : null;
    $cityId      = !empty($row['billing_city_id'])      ? (int) $row['billing_city_id']      : null;
    $pincodeId   = !empty($row['billing_pincode_id'])   ? (int) $row['billing_pincode_id']   : null;

    // If ALL location fields are empty → Allow import (no validation)
    if (empty($countryId) && empty($stateId) && empty($districtId) && empty($cityId) && empty($pincodeId)) {
        return true;
    }

    // If some fields are filled, then validate the full hierarchy
    try {
        // Country check (must be provided if any other location is given)
        if (empty($countryId)) {
            throw new \Exception("Country ID is required when location details are provided.");
        }

        $country = \App\Models\Country::find($countryId);
        if (!$country) {
            throw new \Exception("Invalid Country ID: " . $countryId);
        }

        // State check
        if (empty($stateId)) {
            throw new \Exception("State ID is required when location details are provided.");
        }

        $state = \App\Models\State::where('id', $stateId)
                    ->where('country_id', $country->id)
                    ->first();

        if (!$state) {
            throw new \Exception("State does not belong to the selected Country.");
        }

        // District check
        if (empty($districtId)) {
            throw new \Exception("District ID is required when location details are provided.");
        }

        $district = \App\Models\District::where('id', $districtId)
                        ->where('state_id', $state->id)
                        ->first();

        if (!$district) {
            throw new \Exception("District does not belong to the selected State.");
        }

        // City check
        if (empty($cityId)) {
            throw new \Exception("City ID is required when location details are provided.");
        }

        $city = \App\Models\City::where('id', $cityId)
                    ->where('district_id', $district->id)
                    ->first();

        if (!$city) {
            throw new \Exception("City does not belong to the selected District.");
        }

        // Pincode check
        if (empty($pincodeId)) {
            throw new \Exception("Pincode ID is required when location details are provided.");
        }

        $pincode = \App\Models\Pincode::where('id', $pincodeId)
                       ->where('city_id', $city->id)
                       ->first();

        if (!$pincode) {
            throw new \Exception("Pincode does not belong to the selected City.");
        }

        return true;

    } catch (\Exception $e) {
        throw $e; // Let the caller handle it
    }
}
public function onRow(Row $row)
{
        $row = $row->toArray();
        // Extra safety: Check required fields manually if validation somehow fails
    if (empty($row['distributor_code'])) {
        $this->onError(new \Exception("Distributor Code is required."));
        return;
    }

    try {
        $this->validateLocationHierarchy($row);
    } catch (\Exception $e) {
        $this->onError($e); // ye SkipsErrors handle karega
        return;
    }

    $beatId = !empty($row['beat_id']) ? (int) $row['beat_id'] : null;
    
    $beatRoute = $row['beat_route'] ?? null;

    if ($beatId) {
        $beat = \App\Models\Beat::find($beatId);   // Change model name if different

        if ($beat) {
            $beatRoute = $beat->name ?? $beat->beat_name ?? $beat->route_name ?? $beatRoute;
        } else {
            $this->onError(new \Exception("Invalid Beat ID: " . $beatId));
            return;
        }
    }
    // dd($beatRoute);
    
    // Date fix
    $businessStartDate = null;

    if (!empty($row['business_start_date'])) {
        $businessStartDate = is_numeric($row['business_start_date'])
            ? Date::excelToDateTimeObject($row['business_start_date'])->format('Y-m-d')
            : $row['business_start_date'];
    }

    // Sales Executive ID fix
    $salesExecutiveIds = [];

    if (!empty($row['sales_executive_id_json'])) {
        $salesExecutiveIds = [(int) $row['sales_executive_id_json']];
    }

    // 🔥 CHECK: update or create
    $distributor = null;

    if (!empty($row['id'])) {
        $distributor = MasterDistributor::find($row['id']);
    }

    if (!$distributor) {
        $distributor = new MasterDistributor();
    }

    // ✅ Fill data
    $distributor->fill([
        'distributor_code' => $row['distributor_code'] ?? null,
        'legal_name'       => $row['legal_name'] ?? null,
        'trade_name'       => $row['trade_name'] ?? null,

        'business_status'     => $row['business_status'] ?? 'Active',
        'business_start_date' => $businessStartDate,

        'contact_person'   => $row['contact_person'] ?? null,
        'mobile'           => (string) $row['mobile']?? '',
        'alternate_mobile' => $row['alternate_mobile'] ?? null,
        'email'            => $row['email']?? '',

        // IDs store
        'billing_address'  => $row['billing_address'] ?? '',
        'billing_city'         => $row['billing_city_id'] ? (int)$row['billing_city_id'] : null,
        'billing_district'     => $row['billing_district_id'] ? (int)$row['billing_district_id'] : null,
        'billing_state'        => $row['billing_state_id'] ? (int)$row['billing_state_id'] : null,
        'billing_country'      => $row['billing_country_id'] ? (int)$row['billing_country_id'] : null,
        'billing_pincode'      => $row['billing_pincode_id'] ? (int)$row['billing_pincode_id'] : null,

        'shipping_address' => $row['shipping_address'] ?? null,

        'beat_id'              => $beatId,           // Save ID
        'beat_route'           => $beatRoute,

        // 'beat_route' => $row['beat_route'] ?? null,
        // 'beat_id'              => $row['beat_id'] ? (int)$row['beat_id'] : null,

        // 'gst_number'        => $row['gst_number'] ?? '',
        // 'pan_number'        => $row['pan_number'] ?? '',
        'registration_type' => $row['registration_type'] ?? '',

        'customer_segment' => $row['product_categories'] ?? '',

        'sales_executive_id' => $salesExecutiveIds,
    ]);

    $distributor->save();

    $this->importedCount++;
}

public function prepareForValidation($data, $index)
{
    return $data;
}

public function rules(): array
{
    return [
        // Required fields as per your request
        'distributor_code'   => 'required|string|max:100',
        'legal_name'         => 'required|string|max:255',
        'trade_name'         => 'required|string|max:255',
        'business_status'    => 'required|in:Active,Inactive,Suspended', // adjust values as needed
        // 'business_start_date'=> 'required|date_format:Y-m-d', // or 'date'
        'contact_person'     => 'required|string|max:255',
        'sales_executive_id_json' => 'required', // since you're using this column

        // Other fields
        'id'                  => 'nullable|exists:master_distributors,id',
        'mobile'              => 'required|digits:10',
        'alternate_mobile'    => 'nullable|digits:10',
        'email'               => 'nullable|email|max:255',
        'shipping_address'        => 'nullable|string|max:500',
        // 'gst_number'              => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        // 'pan_number'              => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'registration_type'       => 'nullable|string|max:100',
        'customer_segment'        => 'nullable|string|max:255',
        'billing_address'          => 'nullable|string|max:255',
        // GST & PAN
        // 'gst_number'          => 'nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        // 'pan_number'          => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        
        // Unique check
        'distributor_code'    => 'unique:master_distributors,distributor_code', // will be merged properly
        
        // Optional fields (you said these can be skipped)
        'beat_id'             => 'nullable|integer|exists:beats,id',
        'beat_route'          => 'nullable|string|max:255',
        
        // For array uniqueness if needed
        '*.distributor_code'  => 'distinct',
    ];
}

    /**
     * Optional - Custom error messages
     */
//     public function customValidationMessages()
//     {
//         return [
// 'distributor_code.required' => 'Distributor Code is required.',
//         'legal_name.required'       => 'Legal Name is required.',
//         'trade_name.required'       => 'Trade Name is required.',
//         'business_status.required'  => 'Business Status is required.',
//         'business_start_date.required' => 'Business Start Date is required.',
//         'contact_person.required'   => 'Contact Person is required.',
//         'sales_executive_id_json.required' => 'Sales Executive is required.',
//                     'distributor_code.unique'   => 'Distributor Code :input already exists.',
//             // 'Legal Name.required'       => 'Legal Name is required.',
//             'Mobile.digits'             => 'Mobile must be 10 digits.',
//             'Email.email'               => 'Please enter valid email.',
//             'gst_number.regex'          => 'Invalid GST Number format.',
//             'pan_number.regex'          => 'Invalid PAN Number format.',
//         ];
//     }

public function customValidationMessages()
{
    return [
        'distributor_code.required'       => 'Distributor Code is required.',
        'legal_name.required'             => 'Legal Name is required.',
        'trade_name.required'             => 'Trade Name is required.',
        'business_status.required'        => 'Business Status is required.',
        // 'business_start_date.required'    => 'Business Start Date is required.',
        'contact_person.required'         => 'Contact Person is required.',
        'sales_executive_id_json.required'=> 'Sales Executive ID is required.',

        'distributor_code.unique'         => 'Distributor Code already exists.',
        'mobile.digits'                   => 'Mobile must be 10 digits.',
        'email.email'                     => 'Please enter a valid email.',
        // 'gst_number.regex'                => 'Invalid GST Number format.',
        // 'pan_number.regex'                => 'Invalid PAN Number format.',
    ];
}
}