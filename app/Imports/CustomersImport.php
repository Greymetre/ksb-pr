<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Customers;
use App\Models\CustomerDetails;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\City;
use App\Models\Pincode;
use App\Models\UserDetails;
use App\Models\Beat;
use App\Models\BeatCustomer;
use App\Models\BeatSchedule;
use App\Models\EmployeeDetail;
use App\Models\ParentDetail;

use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomersImport implements ToCollection, WithValidation, WithHeadingRow, WithBatchInserts, WithChunkReading
{
  use Importable, SkipsFailures;

  public function model(array $row)
  {
    return new Customers([
      //
    ]);
  }

  public function collection(Collection $rows)
  {
    $customerdetails = collect([]);
    $addressdetails = collect([]);
    $attachments = collect([]);

    foreach ($rows as $ky=>$row) {
      if (isset($row['mobile']) && strlen(preg_replace('/\s+/', '', $row['mobile'])) == 10) {
        $row['mobile'] = '91' . preg_replace('/\s+/', '', $row['mobile']);
      }

      $row['creation_date'] = $this->parseDate($row['creation_date'] ?? null);


      if (!empty($row['customer_id'])) {

        if (!Customers::where('id', $row['customer_id'])->exists()) {
          Log::warning('Customer import: customer_id ' . $row['customer_id'] . ' not found, row skipped');
          continue;
        }

        // Only columns filled in the sheet are updated; blank cells keep the existing value.
        $customerData = $this->filled([
          'name' => $row['firm_name'] ?? null,
          'active' => $row['status'] ?? null,
          'first_name' => $row['first_name'] ?? null,
          'last_name' => $row['last_name'] ?? null,
          'contact_number' => $row['contact_number_2'] ?? null,
          'customer_code' => $row['customer_code'] ?? null,
          'working_status' => $row['working_status'] ?? null,
          'creation_date' => $row['creation_date'],
          'sap_code' => ($row['odoo_code'] ?? null) ?: ($row['sap_code'] ?? null),
          'customertype' => $row['customer_type_id'] ?? null,
          'latitude' => $row['latitude'] ?? null,
          'longitude' => $row['longitude'] ?? null,
        ]);

        // mobile and email are unique: skip them if another customer already uses the value.
        foreach (['mobile' => $row['mobile'] ?? null, 'email' => $row['email'] ?? null] as $field => $value) {
          $value = trim((string)$value);
          if ($value !== '' && !Customers::where($field, $value)->where('id', '!=', $row['customer_id'])->exists()) {
            $customerData[$field] = $value;
          }
        }

        if (!empty($customerData)) {
          Customers::where('id', '=', $row['customer_id'])->update($customerData);
        }

        $detailData = $this->filled([
          'gstin_no' => $row['gstin_no'] ?? null,
          'pan_no' => $row['pan_no'] ?? null,
          'aadhar_no' => $row['aadhar_no'] ?? null,
          'otherid_no' => $row['other_no'] ?? null,
          'grade' => $row['grade'] ?? null,
          'visit_status' => $row['visit_status'] ?? null,
        ]);
        if (!empty($detailData)) {
          CustomerDetails::updateOrCreate(['customer_id' => $row['customer_id']], $detailData);
        }

        $addressData = $this->filled([
          'pincode_id' => $row['pincode_id'] ?? null,
          'city_id' => $row['city_id'] ?? null,
          'district_id' => $row['district_id'] ?? null,
          'state_id' => $row['state_id'] ?? null,
          'country_id' => $row['country_id'] ?? null,
          'address1' => $row['address'] ?? null,
          'landmark' => $row['market_place'] ?? null,
        ]);
        if (!empty($addressData)) {
          if (Address::where('customer_id', $row['customer_id'])->exists()) {
            Address::where('customer_id', $row['customer_id'])->update($addressData);
          } else {
            Address::create($addressData + ['customer_id' => $row['customer_id'], 'created_by' => Auth::user()->id]);
          }
        }



        //employee start

        if (!empty($row['employee_id'])) {

          EmployeeDetail::where('customer_id', $row['customer_id'])->delete();
          //$row['employee_id'] = str_replace('[','',$row['employee_id']);
          //$row['employee_id'] = str_replace(']','',$row['employee_id']);
          $employee_data = explode(",", $row['employee_id']);

          foreach ($employee_data as $keys => $row_employee) {
            $employeeDetail = EmployeeDetail::updateOrCreate(
              [
                'customer_id' => $row['customer_id'],
                'user_id' => $row_employee,
                'created_by' => Auth::user()->id,
              ]

            );
          }
        }

        // employee end

        //parent start

        if (!empty($row['parent_id'])) {
          ParentDetail::where('customer_id', $row['customer_id'])->delete();
          //$row['parent_id'] = str_replace('[','',$row['parent_id']);
          //$row['parent_id'] = str_replace(']','',$row['parent_id']);

          $parent_data = explode(",", $row['parent_id']);

          foreach ($parent_data as $key => $row_parent) {
            $parentDetail = ParentDetail::updateOrCreate(
              [
                'customer_id' => $row['customer_id'],
                'parent_id' => $row_parent,
                'created_by' => Auth::user()->id,
              ]
            );
          }
        }
        // parent end  

      } else {

        $addressdetails = collect([]);
        $mobile = !empty($row['mobile']) ? (string)$row['mobile'] : null;
        $odooCode = ($row['odoo_code'] ?? null) ?: ($row['sap_code'] ?? null) ?: null;

        // Match an existing customer by mobile, then Odoo/SAP code, then customer code.
        // Rows without a mobile must not all collapse onto one customer with mobile ''.
        $existing = null;
        if ($mobile) {
          $existing = Customers::where('mobile', $mobile)->first();
        }
        if (!$existing && $odooCode) {
          $existing = Customers::where('sap_code', $odooCode)->first();
        }
        if (!$existing && !empty($row['customer_code'])) {
          $existing = Customers::where('customer_code', $row['customer_code'])->first();
        }

        $customer = $existing ?: new Customers();
        $customer->fill([
          'mobile' => $mobile ?? ($existing ? $existing->mobile : null),
          'active' => 'Y',
          'name' => !empty($row['firm_name']) ? ucfirst($row['firm_name']) : '',
          'first_name' => !empty($row['first_name']) ? ucfirst($row['first_name']) : '',
          'last_name' => !empty($row['last_name']) ? ucfirst($row['last_name']) : '',
          'email' => !empty($row['email']) ? $row['email'] : null,
          'working_status' => !empty($row['working_status']) ? $row['working_status'] : null,
          'creation_date' => !empty($row['creation_date']) ? $row['creation_date'] : null,
          'sap_code' => ($row['odoo_code'] ?? null) ?: ($row['sap_code'] ?? null) ?: null,
          'password' => !empty($row['password']) ? Hash::make($row['password']) : '',
          'notification_id' => !empty($row['notification_id']) ? $row['notification_id'] : '',
          'latitude' => !empty($row['latitude']) ? $row['latitude'] : '',
          'longitude' => !empty($row['longitude']) ? $row['longitude'] : '',
          'device_type' => !empty($row['device_type']) ? ucfirst($row['device_type']) : '',
          'gender' => !empty($row['gender']) ? ucfirst($row['gender']) : '',
          'customer_code' => !empty($row['customer_code']) ? $row['customer_code'] : '',
          'profile_image' =>  !empty($row['profile_image']) ? $row['profile_image'] : '',
          'status_id' =>  !empty($row['status_id']) ? $row['status_id'] : 2,
          'customertype' =>  !empty($row['customer_type_id']) ? $row['customer_type_id'] : 1,
          'firmtype' =>  !empty($row['firmtype']) ? $row['firmtype'] : null,
          // 'created_by' => $user_id,
          'created_by' => Auth::user()->id,
          //'executive_id' => $executive_id,
          //'parent_id' => $parent_id,
          'contact_number' => !empty($row['contact_number_2']) ? $row['contact_number_2'] : null,
          'created_at' => getcurentDateTime(),
          'updated_at' => getcurentDateTime()
        ]);

        if ($customer->save()) {

          //employee start
          if (!empty($row['employee_id'])) {
            //$row['employee_id'] = str_replace('[','',$row['employee_id']);
            //$row['employee_id'] = str_replace(']','',$row['employee_id']);
            $employee_data = explode(",", $row['employee_id']);

            foreach ($employee_data as $keys => $row_employee) {

              $employeeDetail = EmployeeDetail::updateOrCreate(
                [
                  'customer_id' => $customer['id'],
                  'user_id' => $row_employee,
                  'created_by' => Auth::user()->id,
                ]

              );
            }
          }

          //employee end



          //parent start

          if (!empty($row['parent_id'])) {
            //$row['parent_id'] = str_replace('[','',$row['parent_id']);
            //$row['parent_id'] = str_replace(']','',$row['parent_id']);

            $parent_data = explode(",", $row['parent_id']);

            foreach ($parent_data as $key => $row_parent) {
              $parentDetail = ParentDetail::updateOrCreate(
                [
                  'customer_id' => $customer['id'],
                  'parent_id' => $row_parent,
                  'created_by' => Auth::user()->id,
                ]
              );
            }
          }


          $pincode = Pincode::where('pincode', '=', $row['pincode_id'])->select('id', 'city_id')->first();
          $addressdetails->push([
            'active' => 'Y',
            'customer_id' => $customer['id'],
            'address1' => !empty($row['address']) ? $row['address'] : '',
            'address2' => !empty($row['address2']) ? $row['address2'] : '',
            'landmark' => !empty($row['market_place']) ? $row['market_place'] : '',
            'locality' => !empty($row['market_place']) ? $row['market_place'] : '',
            'country_id' => !empty($row['country_id']) ? $row['country_id'] : null,
            'state_id' => !empty($row['state_id']) ? $row['state_id'] : null,
            // 'district_id' => !empty($city['district_id'])? $city['district_id']:null,
            'district_id' => !empty($row['district_id']) ? $row['district_id'] : null,
            // 'city_id' => !empty($pincode['city_id'])? $pincode['city_id']:null,
            // 'pincode_id' => !empty($pincode['id'])? $pincode['id']:null,
            'city_id' => !empty($row['city_id']) ? $row['city_id'] : null,
            'pincode_id' => !empty($row['pincode_id']) ? $row['pincode_id'] : null,
            'created_by' => Auth::user()->id,
            'created_at' => getcurentDateTime(),
            'updated_at' => getcurentDateTime()
          ]);
          // dd($customerdetails, $customer);
          // if ($customerdetails->isNotEmpty()) {
            CustomerDetails::updateOrCreate(['customer_id' => $customer['id'],],[
              'active' => 'Y',
              // These string columns are NOT NULL (default ''), so blanks must be '' not null.
              'gstin_no' => !empty($row['gstin_no'])? $row['gstin_no']:'',
              'pan_no' => !empty($row['pan_no'])? $row['pan_no']:'',
              'aadhar_no' => !empty($row['aadhar_no'])? $row['aadhar_no']:'',
              'otherid_no' => !empty($row['other_no']) ? $row['other_no'] : '',
              'grade' => !empty($row['grade']) ? $row['grade'] : '',
              'visit_status' => !empty($row['visit_status']) ? $row['visit_status'] : '',
              'enrollment_date' => $this->parseDate($row['enrollment_date'] ?? null),
              'approval_date' => $this->parseDate($row['approval_date'] ?? null),
              'created_at' => getcurentDateTime(),
              'updated_at' => getcurentDateTime()
            ]);
          // }
          if ($addressdetails->isNotEmpty()) {
            $address = $addressdetails->first();
            Address::updateOrCreate(['customer_id' => $customer['id']], $address);
          }
          // if ($attachments->isNotEmpty()) {
          //   Attachment::insert($attachments->toArray());
          // }
        }
      }
    }
  }

  // Drop null / empty-string values so blank cells don't overwrite existing data.
  private function filled(array $data): array
  {
    return array_filter(array_map(function ($v) {
      return is_string($v) ? trim($v) : $v;
    }, $data), function ($v) {
      return $v !== null && $v !== '';
    });
  }

  // Accepts an Excel serial number, d-m-Y, d/m/Y or Y-m-d; anything else becomes null.
  private function parseDate($value)
  {
    if ($value === null || $value === '') {
      return null;
    }
    if (is_numeric($value)) {
      // Small serials are real Excel dates; large numbers (e.g. a phone number) are junk.
      if ($value > 0 && $value < 100000) {
        return Carbon::createFromTimestamp(((int)$value - 25569) * 86400)->toDateString();
      }
      return null;
    }
    foreach (['d-m-Y', 'd/m/Y', 'Y-m-d'] as $format) {
      try {
        $date = Carbon::createFromFormat('!' . $format, trim($value));
        if ($date && $date->format($format) === trim($value)) {
          return $date->toDateString();
        }
      } catch (\Throwable $e) {
      }
    }
    return null;
  }

  public function rules(): array
  {
    return [
      //'name' => 'required|string|regex:/[a-zA-Z0-9\s]+/',
      'password' => strongPasswordRules(null, false),
    ];
  }

  public function batchSize(): int
  {
    return 1000;
  }

  public function chunkSize(): int
  {
    return 1000;
  }

  public function onFailure(Failure ...$failures)
  {
    Log::stack(['import-failure-logs'])->info(json_encode($failures));
  }
}
