<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Validator;
use Gate;
use App\Models\Customers;
use App\Models\CustomerType;
use App\Models\Beat;
use App\Models\BeatSchedule;
use App\Models\BeatCustomer;
use App\Models\Attachment;
use App\Models\Address;
use App\Models\State;
use App\Models\CustomerDetails;
use App\Models\Pincode;
use App\Models\SurveyData;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Sales;
use App\Models\CheckIn;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Tasks;
use App\Models\Wallet;
use App\Models\DealIn;
use App\Models\OrderDetails;
use App\Models\ParentDetail;
use App\Models\EmployeeDetail;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->customers = new Customers();
        $this->address = new Address();
        $this->successStatus = 200;
        $this->created = 201;
        $this->accepted = 202;
        $this->noContent = 204;
        $this->badrequest = 400;
        $this->unauthorized = 401;
        $this->notFound = 404;
        $this->notactive = 406;
        $this->internalError = 500;
        $this->path = 'customers';
    }

    private function requestFileByAnyKey(Request $request, array $keys)
    {
        foreach ($keys as $key) {
            if ($request->hasFile($key)) {
                return $request->file($key);
            }
        }

        return null;
    }

    private function saveCustomerAttachment($customerId, string $documentName, $file): string
    {
        $filePath = fileupload($file, $this->path, $documentName . '_');

        Attachment::updateOrCreate([
            'customer_id'   => $customerId,
            'document_name' => $documentName,
        ], [
            'active'        => 'Y',
            'file_path'     => $filePath,
            'document_name' => $documentName,
            'customer_id'   => $customerId,
            'created_at'    => getcurentDateTime(),
            'updated_at'    => getcurentDateTime(),
        ]);

        return $filePath;
    }

    private function normalizeCustomerMobile($mobile): ?string
    {
        if ($mobile === null || $mobile === '') {
            return null;
        }

        $mobile = preg_replace('/[^0-9]/', '', (string) $mobile);

        return strlen($mobile) === 10 ? '91' . $mobile : $mobile;
    }

    private function requestHasAny(Request $request, array $keys): bool
    {
        foreach ($keys as $key) {
            if ($request->exists($key) || $request->hasFile($key)) {
                return true;
            }
        }

        return false;
    }

    public function storeCustomer(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated - please provide valid bearer token.',
                ], $this->unauthorized);
            }

            $validator = Validator::make($request->all(), [
                'address' => 'nullable|min:2|max:100|string|regex:/[a-zA-Z0-9\s]+/',
                'mobile'  => 'required|numeric|unique:customers,mobile',
                // 'email'  => 'email|unique:customers,email',
                'customertype'       => 'nullable|exists:customer_types,id',
                'bank_account_type' => 'nullable|in:Savings,Current,savings,current',
                'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'shopimage' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'visiting_card' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'gstin_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'pan_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'imgaadhar' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhar' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhar_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadharImage' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhar_attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadharAttachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhaar' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhaar_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhaarImage' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'other_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ], [
                'image.file' => 'Profile image must be a valid uploaded file.',
                'image.mimes' => 'Profile image must be jpg, jpeg, png, webp, or pdf.',
                'image.max' => 'Profile image size must not be greater than 5 MB.',
                'shopimage.file' => 'Shop image must be a valid uploaded file.',
                'shopimage.mimes' => 'Shop image must be jpg, jpeg, png, webp, or pdf.',
                'shopimage.max' => 'Shop image size must not be greater than 5 MB.',
                'visiting_card.file' => 'Visiting card must be a valid uploaded file.',
                'visiting_card.mimes' => 'Visiting card must be jpg, jpeg, png, webp, or pdf.',
                'visiting_card.max' => 'Visiting card size must not be greater than 5 MB.',
                'gstin_image.file' => 'GST document must be a valid uploaded file.',
                'gstin_image.mimes' => 'GST document must be jpg, jpeg, png, webp, or pdf.',
                'gstin_image.max' => 'GST document size must not be greater than 5 MB.',
                'pan_image.file' => 'PAN document must be a valid uploaded file.',
                'pan_image.mimes' => 'PAN document must be jpg, jpeg, png, webp, or pdf.',
                'pan_image.max' => 'PAN document size must not be greater than 5 MB.',
                'imgaadhar.file' => 'Aadhaar document must be a valid uploaded file.',
                'imgaadhar.mimes' => 'Aadhaar document must be jpg, jpeg, png, webp, or pdf.',
                'imgaadhar.max' => 'Aadhaar document size must not be greater than 5 MB.',
                'other_image.file' => 'Bank passbook must be a valid uploaded file.',
                'other_image.mimes' => 'Bank passbook must be jpg, jpeg, png, webp, or pdf.',
                'other_image.max' => 'Bank passbook size must not be greater than 5 MB.',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->messages()->all()], $this->badrequest);
            }
            //$request['fordelete'] = implode(',', $request->getContent());
            $request['mobile'] = preg_replace("/[^0-9]/", "", $request['mobile']);
            if (strlen(preg_replace('/\s+/', '', $request['mobile'])) == 10) {
                $request['mobile'] = '91' . preg_replace('/\s+/', '', $request['mobile']);
            }

            $customerdetails = Customers::where('mobile', $request['mobile'])->first();
            if (!empty($customerdetails)) {
                return response(['status' => 'error', 'message' => 'Mobile Number Already Exist'], 400);
            } else {

                $customergst = null;
                if ($request->filled('gstin_no')) {
                    $customergst = CustomerDetails::where('gstin_no', '=', $request['gstin_no'])->whereNotNull('gstin_no')->first();
                }

                if (!empty($customergst)) {
                    return response(['status' => 'error', 'message' => 'GST Number Already Exist'], 400);
                } else {

                    $name = explode(" ", trim((string) $request->input('full_name', '')));
                    $request['last_name'] = isset($request['last_name']) ? $request['last_name'] : array_pop($name);
                    $request['first_name'] = isset($request['first_name']) ? $request['first_name'] : implode(" ", $name);
                    $request['created_by'] = $user->id;
                    $parentData = [];
                    if (!empty($request['parent_id'])) {
                        $parentData = array_filter(array_map('trim', explode(",", $request['parent_id'])), function ($value) {
                            return is_numeric($value);
                        });
                    }
                    $customFields = $request->input('custom_fields');
                    if (is_array($customFields)) {
                        $customFields = json_encode($customFields);
                    }

                    $customertype = CustomerType::where('type_name', '=', 'retailer')->pluck('id')->first();
                    $request['customertype'] = isset($request['customertype']) ? $request['customertype'] : $customertype;
                    //$response =  $this->customers->save_data($request);

                    if ($image = $this->requestFileByAnyKey($request, ['image'])) {
                        // $filename = 'punchin_'.autoIncrementId('Attendance', 'id');
                        $filename = 'profile_';
                        $request['profile_image'] = fileupload($image, $this->path, $filename);
                    }

                    if ($image = $this->requestFileByAnyKey($request, ['shopimage'])) {
                        $filename = 'shop_';
                        $request['shop_image'] = fileupload($image, $this->path, $filename);
                    }

                    $employeeInput = $request->input(
                        'assigned_user_ids',
                        $request->input('employee_id', $request->input('executive_id', $request->input('user_id', $user->id)))
                    );
                    $employeeIds = collect(is_array($employeeInput) ? $employeeInput : explode(',', (string) $employeeInput))
                        ->filter(fn($value) => is_numeric($value))
                        ->map(fn($value) => (int) $value)
                        ->unique()
                        ->values();

                    if ($customer = Customers::updateOrCreate(['mobile' => $request['mobile']], [
                        'active' => 'Y',
                        'name' => !empty($request['name']) ? ucfirst($request['name']) : '',
                        'first_name' => !empty($request['first_name']) ? ucfirst($request['first_name']) : '',
                        'last_name' => !empty($request['last_name']) ? ucfirst($request['last_name']) : '',
                        'mobile' => $request['mobile'],
                        'email' => !empty($request['email']) ? $request['email'] : null,
                        'password' => !empty($request['password']) ? Hash::make($request['password']) : '',
                        'notification_id' => !empty($request['notification_id']) ? $request['notification_id'] : '',
                        'latitude' => !empty($request['latitude']) ? $request['latitude'] : null,
                        'longitude' => !empty($request['longitude']) ? $request['longitude'] : null,
                        'device_type' => !empty($request['device_type']) ? ucfirst($request['device_type']) : '',
                        'gender' => !empty($request['gender']) ? ucfirst($request['gender']) : '',
                        'customer_code' => !empty($request['customer_code']) ? $request['customer_code'] : '',
                        'profile_image' =>  !empty($request['profile_image']) ? $request['profile_image'] : '',
                        'shop_image' =>  !empty($request['shop_image']) ? $request['shop_image'] : '',
                        'status_id' =>  !empty($request['status_id']) ? $request['status_id'] : 2,
                        'customertype' =>  !empty($request['customertype']) ? $request['customertype'] : 1,
                        'firmtype' =>  (!empty($request['firmtype']) && is_numeric($request['firmtype'])) ? $request['firmtype'] : null,
                        'executive_id' => $employeeIds->first(),
                        'created_by' =>  !empty($request['created_by']) ? $request['created_by'] : null,
                        'manager_name' => !empty($request['manager_name']) ? $request['manager_name'] : '',
                        'manager_phone' => !empty($request['manager_phone']) ? $request['manager_phone'] : '',
                        'contact_number' => !empty($request['contact_number']) ? $request['contact_number'] : '',
                        'same_address' => !empty($request['same_address']) ? 1 : 0,
                        'custom_fields' => !empty($customFields) ? $customFields : null,
                        'working_status' => !empty($request['working_status']) ? $request['working_status'] : null,
                        'creation_date' => !empty($request['creation_date']) ? $request['creation_date'] : null,
                        'sap_code' => !empty($request['sap_code']) ? $request['sap_code'] : null,
                        'parent_id' => !empty($parentData) ? reset($parentData) : null,
                        'created_at' => getcurentDateTime(),
                        'updated_at' => getcurentDateTime()
                    ])) {

                        //parent start

                        // if(!empty($request['parent_id']))
                        // {
                        //     foreach($request['parent_id'] as $key => $rows) {
                        // $parentDetail = ParentDetail::create(
                        //   [ 
                        //     'customer_id' => $customer->id,
                        //     'parent_id' => $rows,
                        //     'created_by' => Auth::user()->id,
                        //   ]
                        //  );
                        //  }
                        // }

                        if (!empty($parentData)) {
                            foreach ($parentData as $row_parent) {
                                $parentDetail = ParentDetail::create(
                                    [
                                        'customer_id' => $customer->id,
                                        'parent_id' => $row_parent,
                                        'created_by' => Auth::user()->id,
                                    ]
                                );
                            }
                        }

                        // parent end

                        //employee start

                        foreach ($employeeIds as $employeeId) {
                            EmployeeDetail::create(
                                [
                                    'customer_id' => $customer->id,
                                    'user_id' => $employeeId,
                                    'created_by' => Auth::user()->id,
                                ]
                            );
                        }

                        // employee end  

                        // $useractivity = array(
                        //     'userid' => $user->id, 
                        //     'customer_id' => $customer->id,
                        //     'latitude' => $request['latitude'], 
                        //     'longitude' => $request['longitude'], 
                        //     'type' => 'Counter Created',
                        //     'description' => $user->name.' Created to '.$request['name'],
                        // );
                        // submitUserActivity($useractivity);

                        $useractivity = array(
                            'userid' => $user->id,
                            'customer_id' => $customer->id,
                            'latitude' => $request['latitude'] ?? NULL,
                            'longitude' => $request['longitude'] ?? NULL,
                            //'type' => 'Counter Created',
                            //'description' => $user->name.' Created to '.$request['name'],
                        );
                        submitUserActivity($useractivity);


                        $request['customer_id'] = $customer->id;
                        $pincodes = Pincode::with('cityname', 'cityname.districtname')->where('pincode', '=', $request['zipcode'])->first();
                        $request['state_id'] = !empty($pincodes['cityname']['districtname']['state_id']) ? $pincodes['cityname']['districtname']['state_id'] : $request['state_id'];
                        $request['district_id'] = !empty($pincodes['cityname']['district_id']) ? $pincodes['cityname']['district_id'] : $request['district_id'];
                        $request['city_id'] = !empty($pincodes['city_id']) ? $pincodes['city_id'] : $request['city_id'];
                        $request['pincode_id'] = !empty($pincodes['id']) ? $pincodes['id'] : $request['pincode_id'];

                        $request['country_id'] = !empty($request['country_id']) ? $request['country_id'] : State::where('id', $request['state_id'])->pluck('country_id')->first();
                        $request['landmark'] = !empty($request['landmark']) ? $request['landmark'] : '';
                        Address::updateOrCreate(['customer_id' => $request['customer_id']], [
                            'active'    => 'Y',
                            'customer_id'   =>  $request['customer_id'],
                            'address1' => !empty($request['address1']) ? $request['address1'] : '',
                            'address2' => !empty($request['address2']) ? $request['address2'] : '',
                            'landmark' => !empty($request['landmark']) ? $request['landmark'] : '',
                            'locality' => !empty($request['locality']) ? $request['locality'] : $request['landmark'],
                            'country_id' => !empty($request['country_id']) ? $request['country_id'] : null,
                            'state_id' => !empty($request['state_id']) ? $request['state_id'] : null,
                            'district_id' => !empty($request['district_id']) ? $request['district_id'] : null,
                            'city_id' => !empty($request['city_id']) ? $request['city_id'] : null,
                            'pincode_id' => !empty($request['pincode_id']) ? $request['pincode_id'] : null,
                            'zipcode' => !empty($request['zipcode']) ? $request['zipcode'] : '',
                            'created_by' => !empty($request['created_by']) ? $request['created_by'] : Auth::user()->id,
                            'created_at' => getcurentDateTime(),
                            'updated_at' => getcurentDateTime()
                        ]);

                        // if($request->file('image')){
                        //    $image = $request->file('image');
                        //    $filename = 'customer';
                        //    $request['shop_image'] = fileupload($image, $this->path.'/shopimage', $filename);
                        // }


                        //  if($request->file('image')){
                        // $image = $request->file('image');
                        // // $filename = 'punchin_'.autoIncrementId('Attendance', 'id');
                        // $filename = 'customer';
                        // $request['profile_image'] = fileupload($image, $this->path, $filename);
                        // }







                        if ($image = $this->requestFileByAnyKey($request, ['visiting_card'])) {
                            $filename = 'visiting_card_';
                            $request['visiting_image'] = fileupload($image, $this->path, $filename);
                        }

                        if ($image = $this->requestFileByAnyKey($request, ['gstin_image'])) {
                            $this->saveCustomerAttachment($request['customer_id'], 'gstin', $image);
                        }

                        if ($image = $this->requestFileByAnyKey($request, ['pan_image'])) {
                            $this->saveCustomerAttachment($request['customer_id'], 'pan', $image);
                        }

                        if ($image = $this->requestFileByAnyKey($request, [
                            'imgaadhar',
                            'aadhar',
                            'aadhar_image',
                            'aadharImage',
                            'aadhar_attachment',
                            'aadharAttachment',
                            'aadhaar',
                            'aadhaar_image',
                            'aadhaarImage',
                        ])) {
                            $this->saveCustomerAttachment($request['customer_id'], 'aadhar', $image);
                        }

                        if ($image = $this->requestFileByAnyKey($request, ['other_image'])) {
                            $this->saveCustomerAttachment($request['customer_id'], 'bankpass', $image);
                        }

                        CustomerDetails::updateOrCreate(['customer_id' => $request['customer_id']], [
                            'active'        => 'Y',
                            'customer_id'   => isset($request['customer_id']) ? $request['customer_id'] : null,
                            'gstin_no'      => isset($request['gstin_no']) ? ucfirst($request['gstin_no']) : '',
                            'pan_no'        => isset($request['pan_no']) ? ucfirst($request['pan_no']) : '',
                            'aadhar_no'     => isset($request['aadhar_no']) ? ucfirst($request['aadhar_no']) : '',
                            'account_holder' => isset($request['account_holder']) ? ucfirst($request['account_holder']) : '',
                            'bank_account_type' => !empty($request['bank_account_type']) ? ucfirst(strtolower($request['bank_account_type'])) : null,
                            'account_number' => isset($request['account_number']) ? $request['account_number'] : '',
                            'bank_name' => isset($request['bank_name']) ? $request['bank_name'] : '',
                            'ifsc_code' => isset($request['ifsc_code']) ? $request['ifsc_code'] : '',
                            'otherid_no'    => isset($request['otherid_no']) ? ucfirst($request['otherid_no']) : '',
                            'enrollment_date' => isset($request['enrollment_date']) ? $request['enrollment_date'] : null,
                            'approval_date'  => isset($request['approval_date']) ? $request['approval_date'] : null,
                            'shop_image' => isset($request['shop_image']) ? $request['shop_image'] : '',
                            //'shop_image' => isset($request['image'])? $request['image']:'',
                            'visiting_card' => isset($request['visiting_image']) ? $request['visiting_image'] : '',
                            'grade'     => isset($request['grade']) ? $request['grade'] : '',
                            'visit_status'     => isset($request['status_type']) ? $request['status_type'] : '',
                            'created_at'    => getcurentDateTime(),
                        ]);


                        if ($request['beat_id']) {
                            $beats = BeatCustomer::updateOrCreate(['customer_id' => $request['customer_id']], [
                                'active' => 'Y',
                                'beat_id' => $request['beat_id'],
                                'customer_id' => $request['customer_id'],
                                'created_at' => getcurentDateTime(),
                            ]);
                        }
                        if ($request['survey']) {
                            $surveydetail = collect([]);
                            $surveyqus = json_decode($request['survey'], true);
                            foreach ($surveyqus as $key => $rows) {
                                SurveyData::updateOrCreate([
                                    'customer_id' => $request['customer_id'],
                                    'field_id' => $rows['field_id']
                                ], [
                                    'customer_id'   => isset($request['customer_id']) ? $request['customer_id'] : null,
                                    'field_id' => isset($rows['field_id']) ? $rows['field_id'] : null,
                                    'value' => isset($rows['value']) ? $rows['value'] : '',
                                    'created_by' => isset($request['created_by']) ? $request['created_by'] : Auth::user()->id,
                                    'created_at' => getcurentDateTime(),
                                ]);
                            }

                            // if($surveydetail->isNotEmpty())
                            // {
                            //     SurveyData::insert($surveydetail->toArray());
                            // }
                        }
                        if ($request['dealing']) {
                            $dealings = json_decode($request['dealing'], true);
                            foreach ($dealings as $key => $deal) {
                                DealIn::updateOrCreate([
                                    'customer_id' => $request['customer_id'],
                                    'types' => $deal['types']
                                ], [
                                    'customer_id'   => !empty($request['customer_id']) ? $request['customer_id'] : null,
                                    'types' => !empty($deal['types']) ? $deal['types'] : '',
                                    'hcv' => isset($deal['hcv']) ? $deal['hcv'] : false,
                                    'mav' => isset($deal['mav']) ? $deal['mav'] : false,
                                    'lmv' => isset($deal['lmv']) ? $deal['lmv'] : false,
                                    'lcv' => isset($deal['lcv']) ? $deal['lcv'] : false,
                                    'other' => isset($deal['other']) ? $deal['other'] : false,
                                    'tractor' => isset($deal['tractor']) ? $deal['tractor'] : false,
                                ]);
                            }
                        }
                        // if ($request['customertype'] == '1' || $request['customertype'] == '3') {
                        //     $passis = generatePassword();
                        //     if (strlen($request['mobile']) > 10 && substr($request['mobile'], 0, 2) === '91') {
                        //         $request['mobile'] = substr($request['mobile'], 2);
                        //     }
                        //     $user = User::create([
                        //         'active'   =>  isset($request['active']) ? $request['active'] : 'Y',
                        //         'name'   =>  isset($request['name']) ? $request['name'] : $request['first_name'] . ' ' . $request['last_name'],
                        //         'first_name'   =>  isset($request['first_name']) ? $request['first_name'] : '',
                        //         'last_name'   =>  isset($request['last_name']) ? $request['last_name'] : '',
                        //         'mobile'   =>  isset($request['mobile']) ? $request['mobile'] : null,
                        //         'email'   =>  isset($request['email']) ? $request['email'] : 'customer'.$customer->id.'@gmail.com',
                        //         'password'   =>  Hash::make($passis),
                        //         'reportingid' => !empty($request['created_by']) ? $request['created_by'] : null,
                        //         'password_string'   =>  $passis,
                        //         'customerid' => $customer->id,
                        //     ]);
                        //     $user->roles()->sync(['29']);
                        //     $permissions = $user->getPermissionsViaRoles()->pluck('name');
                        //     $user->givePermissionTo($permissions);
                        // }
                        // if ($request['customertype'] == '4') {
                        //     $passis = generatePassword();
                        //     if (strlen($request['mobile']) > 10 && substr($request['mobile'], 0, 2) === '91') {
                        //         $request['mobile'] = substr($request['mobile'], 2);
                        //     }
                        //     $user = User::create([
                        //         'active'   =>  isset($request['active']) ? $request['active'] : 'Y',
                        //         'name'   =>  isset($request['name']) ? $request['name'] : $request['first_name'] . ' ' . $request['last_name'],
                        //         'first_name'   =>  isset($request['first_name']) ? $request['first_name'] : '',
                        //         'last_name'   =>  isset($request['last_name']) ? $request['last_name'] : '',
                        //         'mobile'   =>  isset($request['mobile']) ? $request['mobile'] : null,
                        //         'email'   =>  isset($request['email']) ? $request['email'] : 'customer'.$customer->id.'@gmail.com',
                        //         'password'   =>  Hash::make($passis),
                        //         'reportingid' => !empty($request['created_by']) ? $request['created_by'] : null,
                        //         'password_string'   =>  $passis,
                        //         'customerid' => $customer->id,
                        //     ]);
                        //     $user->roles()->sync(['40']);
                        //     $permissions = $user->getPermissionsViaRoles()->pluck('name');
                        //     $user->givePermissionTo($permissions);
                        // }
                        // $asmnotify = collect([
                        //     'title' => 'Successfully added',
                        //     'body' =>  'You have successfully added ' . $request['name']
                        // ]);
                        // sendNotification($user->id, $asmnotify);
                        return response()->json(['status' => 'success', 'message' => 'Data inserted successfully.'], $this->successStatus);
                    }
                    return response(['status' => 'error', 'message' => 'No Record inserted.'], 200);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function updateCustomerLocation(Request $request)
    {
        try {
            $user = $request->user();
            $user_id = $user->id;

            if (Customers::where('id', $request['customer_id'])->update([
                'latitude' => isset($request['latitude']) ? $request['latitude'] : null,
                'longitude' => isset($request['longitude']) ? $request['longitude'] : null,
            ])) {
                return response()->json(['status' => 'success', 'message' => 'Data updated successfully.'], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Updated.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getRetailers(Request $request)
    {

        $cityid = $request->city_id;
        $customertype = $request->customertype;
        $customer_id = array();

        if (!empty($cityid) && $cityid[0] != null) {
            $customer_id = Address::whereIn('city_id', $cityid)->pluck('customer_id')->toArray();
        }

        $customerTypes = CustomerType::select('id', 'customertype_name')->get();
        $branch_id = $request->branch_id;
        $branch_user_id = array();

        if (!empty($branch_id) && $branch_id[0] != null) {
            $branch_user_id = User::whereIn('branch_id', $branch_id)->pluck('id')->toArray();
        }

        try {
            $user = $request->user();
            $userids = getUsersReportingToAuth($user->id); // Get users reporting to the authenticated user
            $customer_ids_assign = EmployeeDetail::whereIn('user_id', $userids)->distinct('customer_id')->pluck('customer_id')->toArray();

            $pageSize = $request->input('pageSize', 10000); // Default to 10000 if pageSize is not provided
            $search = $request->input('search');
            // $chunkSize = 10000;
            // $customerIdChunks = array_chunk($customer_ids_assign, $chunkSize);

            // foreach ($customerIdChunks as $chunk) {
                $query = $this->customers->with('customeraddress', 'customerdetails', 'customertypes')
                    ->where('active', 'Y')
                    // ->whereNotNull('sap_code')
                    ->where(function ($query) use ($search, $customer_id, $branch_user_id, $customertype) {
                        if (!empty($search)) {
                            $query->where(function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%");
                            });
                        }
                        if (!empty($customer_id)) {
                            $query->whereIn('id', $customer_id);
                        }
                        if (!empty($customertype)) {
                            $query->where('customertype', $customertype);
                        }

                        // Filter by branch_user_id
                        if (!empty($branch_user_id)) {
                            $query->whereHas('getemployeedetail', function ($query) use ($branch_user_id) {
                                $query->whereIn('user_id', $branch_user_id);
                            });
                        }
                    })
                    ->whereHas('getemployeedetail', function ($querys) use ($userids, $user) {
                        if (!$user->hasRole('superadmin') && !$user->hasRole('Admin') && !$user->hasRole('Sub_Admin') && !$user->hasRole('HR_Admin') && !$user->hasRole('HO_Account')) {
                            $querys->whereIn('user_id', $userids);
                        }
                    })
                    ->whereIn('id', $customer_ids_assign)
                    ->select('id', 'name', 'first_name', 'last_name', 'mobile', 'email', 'profile_image', 'customer_code', 'latitude', 'longitude', 'customertype','sap_code')
                    // dd($query->toSql());
                    ->orderBy('name', 'asc')
                    ->paginate($pageSize);
            // }


            $db_data = $query;

            // dd($db_data);
            $data = collect([]);
            if ($db_data->isNotEmpty()) {
                foreach ($db_data as $key => $value) {
                    $data->push([
                        'customer_id' => isset($value['id']) ? $value['id'] : 0,
                        'name' => isset($value['name']) ? $value['name'].' ('.$value['sap_code'].')': '',
                        //'first_name' => isset($value['first_name']) ? $value['first_name'] : '',
                        //'last_name' => isset($value['last_name']) ? $value['last_name'] : '',
                        'mobile' => isset($value['mobile']) ? $value['mobile'] : '',
                        'email' => isset($value['email']) ? $value['email'] : '',
                        'profile_image' => isset($value['profile_image']) ? $value['profile_image'] : '',
                        //'customer_code' => isset($value['customer_code']) ? $value['customer_code'] : '',
                        //'totalamount' => isset($value['totalamount']) ? $value['totalamount'] : '',
                        //'totalpaid' => isset($value['totalpaid']) ? $value['totalpaid'] : '',
                        //'outstanding' => $value['totalamount']-$value['totalpaid'],
                        'address1' => isset($value['customeraddress']['address1']) ? $value['customeraddress']['address1'] : '',
                        'address2' => isset($value['customeraddress']['address2']) ? $value['customeraddress']['address2'] : '',
                        'latitude' => isset($value['latitude']) ? $value['latitude'] : '',
                        'longitude' => isset($value['longitude']) ? $value['longitude'] : '',
                        //'shop_image' => isset($value['customerdetails']['shop_image']) ? $value['customerdetails']['shop_image'] : '',
                        //'visiting_card' => isset($value['customerdetails']['visiting_card']) ? $value['customerdetails']['visiting_card'] : '',
                        'grade' => isset($value['customerdetails']['grade']) ? $value['customerdetails']['grade'] : '',
                        'visit_status' => isset($value['customerdetails']['visit_status']) ? $value['customerdetails']['visit_status'] : '',
                        'customer_type' => isset($value['customertypes']['customertype_name']) ? $value['customertypes']['customertype_name'] : '',
                        'distance' => '',
                    ]);
                }
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'customerTypes' => $customerTypes, 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'success', 'message' => 'Data retrieved successfully.', 'customerTypes' => $customerTypes, 'data' => $data], $this->successStatus);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getDistributors(Request $request)
    {
        try {
            $user = $request->user();
            $userids = getUsersReportingToAuth($user->id);
            $customer_ids_assign = EmployeeDetail::whereIn('user_id', $userids)->pluck('customer_id')->toArray();
            $pageSize = $request->input('pageSize');
            $search = $request->input('search');
            $query = $this->customers
                // ->whereNotNull('sap_code')
                ->with('customertypes', 'firmtypes')
                ->whereHas('customertypes', function ($query) {
                    $query->where('type_name', '=', 'distributor')->orWhere('type_name', '=', 'Dealer');
                })
                ->whereNotNull('sap_code')
                ->whereIn('customertype', ['1', '3']);
                if(!$user->hasRole('superadmin') && !$user->hasRole('Admin') && !$user->hasRole('Sub_Admin')){
                    $query = $query->whereIn('id', $customer_ids_assign);
                }
                if (!empty($search)) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
                }
                $query = $query->select('id', 'name', 'first_name', 'last_name', 'mobile', 'email', 'profile_image', 'customer_code', 'sap_code')->orderBy('name', 'asc');
            $db_data = (!empty($pageSize)) ? $query->paginate($pageSize) : $query->get();
            $data = collect([]);
            if ($db_data->isNotEmpty()) {
                foreach ($db_data as $key => $value) {
                    $data->push([
                        'customer_id' => isset($value['id']) ? $value['id'] : 0,
                        'name' => isset($value['name']) ? $value['name'].' ('.$value['sap_code'].')': '',
                        'first_name' => isset($value['first_name']) ? $value['first_name'] : '',
                        'last_name' => isset($value['last_name']) ? $value['last_name'] : '',
                        'mobile' => isset($value['mobile']) ? $value['mobile'] : '',
                        'email' => isset($value['email']) ? $value['email'] : '',
                        'profile_image' => isset($value['profile_image']) ? $value['profile_image'] : '',
                        'customer_code' => isset($value['customer_code']) ? $value['customer_code'] : '',
                    ]);
                }
                return response()->json(['status' => 'success', 'message' => 'Data retrieved successfully.', 'data' => $data], $this->successStatus);
            }
            return response(['status' => 'error', 'message' => 'No Record Found.', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function getCustomerList(Request $request)
    {
        try {
            $authUser = $request->user();

            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated - please provide valid token',
                ], $this->unauthorized);
            }

            $validator = Validator::make($request->all(), [
                'customer_type_id' => 'required|integer|exists:customer_types,id',
                'global_search'    => 'nullable|string|max:255',
                'for_user_id'      => 'nullable|integer|exists:users,id',
                'city_name'        => 'nullable|string|max:255',
                'page'             => 'nullable|integer|min:1',
                'pageSize'         => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], $this->badrequest);
            }

            $customerTypeId = $this->resolveCustomerListTypeId($request->query('customer_type_id'));
            if (!$customerTypeId) {
                return response()->json([
                    'status'  => false,
                    'message' => 'The selected customer type is inactive or invalid.',
                ], $this->badrequest);
            }

            $today = now()->startOfDay()->toDateString();
            $query = Customers::with([
                'customeraddress.countryname',
                'customeraddress.statename',
                'customeraddress.districtname',
                'customeraddress.cityname',
                'customeraddress.pincodename',
                'customerdetails',
                'customertypes',
                'beatdetails.beats',
                'getemployeedetail.employee_detail',
                'customerdocuments',
            ])->where('customers.active', 'Y')->select('customers.*');

            $this->applyCustomerListAccessScope($query, $authUser, $request);

            $this->applyCustomerTypeFilter($query, $customerTypeId);

            if ($request->filled('global_search')) {
                $search = trim($request->query('global_search'));
                $query->where(function ($q) use ($search) {
                    $q->where('customers.name', 'like', "%{$search}%")
                        ->orWhere('customers.first_name', 'like', "%{$search}%")
                        ->orWhere('customers.last_name', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(COALESCE(customers.first_name, ''), ' ', COALESCE(customers.last_name, '')) like ?", ["%{$search}%"])
                        ->orWhere('customers.customer_code', 'like', "%{$search}%")
                        ->orWhere('customers.sap_code', 'like', "%{$search}%")
                        ->orWhere('customers.mobile', 'like', "%{$search}%")
                        ->orWhere('customers.contact_number', 'like', "%{$search}%")
                        ->orWhere('customers.email', 'like', "%{$search}%")
                        ->orWhere('customers.manager_name', 'like', "%{$search}%")
                        ->orWhere('customers.manager_phone', 'like', "%{$search}%")
                        ->orWhereHas('customerdetails', function ($details) use ($search) {
                            $details->where('gstin_no', 'like', "%{$search}%")
                                ->orWhere('pan_no', 'like', "%{$search}%")
                                ->orWhere('aadhar_no', 'like', "%{$search}%");
                        })
                        ->orWhereHas('customeraddress', function ($address) use ($search) {
                            $address->where('address1', 'like', "%{$search}%")
                                ->orWhere('address2', 'like', "%{$search}%")
                                ->orWhere('landmark', 'like', "%{$search}%")
                                ->orWhere('locality', 'like', "%{$search}%")
                                ->orWhere('zipcode', 'like', "%{$search}%")
                                ->orWhereHas('cityname', function ($city) use ($search) {
                                    $city->where('city_name', 'like', "%{$search}%");
                                });
                        });
                });
            }
            if ($request->filled('status')) {
                $query->where('status_id', $request->status);
            }
            if ($request->filled('city_name')) {
                $cityName = trim($request->query('city_name'));
                $query->whereHas('customeraddress.cityname', function ($q) use ($cityName) {
                    $q->where('city_name', $cityName);
                });
            }
            if ($request->filled('owner_name')) {
                $ownerName = $request->owner_name;
                $query->where(function ($q) use ($ownerName) {
                    $q->where('first_name', 'like', "%{$ownerName}%")
                        ->orWhere('last_name', 'like', "%{$ownerName}%")
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$ownerName}%"]);
                });
            }
            if ($request->filled('shop_name')) {
                $query->where('name', 'like', "%{$request->shop_name}%");
            }
            if ($request->filled('mobile')) {
                $query->where(function ($q) use ($request) {
                    $q->where('mobile', 'like', "%{$request->mobile}%")
                        ->orWhere('contact_number', 'like', "%{$request->mobile}%");
                });
            }
            if ($request->filled('beat_id')) {
                $query->whereHas('beatdetails', function ($q) use ($request) {
                    $q->where('beat_id', $request->beat_id);
                });
            }
            if ($request->filled('state_id')) {
                $query->whereHas('customeraddress', function ($q) use ($request) {
                    $q->where('state_id', $request->state_id);
                });
            }
            if ($request->filled('city_id')) {
                $query->whereHas('customeraddress', function ($q) use ($request) {
                    $q->where('city_id', $request->city_id);
                });
            }
            if ($request->filled('opportunity_status') && \Illuminate\Support\Facades\Schema::hasColumn('customers', 'custom_fields')) {
                $query->where('custom_fields', 'like', '%"opportunity_status"%')
                    ->where('custom_fields', 'like', '%' . $request->opportunity_status . '%');
            }
            if ($request->filled('awareness_status')) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('customerdetails', function ($detail) use ($request) {
                        $detail->where('visit_status', $request->awareness_status);
                    });

                    if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'custom_fields')) {
                        $q->orWhere('custom_fields', 'like', '%"awareness_status"%')
                            ->where('custom_fields', 'like', '%' . $request->awareness_status . '%');
                    }
                });
            }

            $query->addSelect([
                'last_checkin_date' => CheckIn::select('checkin_date')
                    ->where(function ($q) {
                        $q->where(function ($legacy) {
                            $legacy->whereColumn('customer_id', 'customers.id');
                        })->orWhere(function ($entity) {
                            $entity->where('entity_type', 'customer')
                                ->whereColumn('entity_id', 'customers.id');
                        });
                    })
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
                'last_checkin_time' => CheckIn::select('checkin_time')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
                'has_checked_in_today' => CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->whereDate('checkin_date', $today),
                'last_checkout_date' => CheckIn::select('checkout_date')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
                'current_visit_is_open' => CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->whereNull('checkout_date')
                    ->whereDate('checkin_date', $today),
                'last_checkout_time' => CheckIn::select('checkout_time')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->whereNotNull('checkout_date')
                    ->orderByDesc('checkout_date')
                    ->orderByDesc('checkout_time')
                    ->limit(1),
                'has_checked_out_today' => CheckIn::selectRaw('IF(COUNT(*) > 0, 1, 0)')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->whereDate('checkout_date', $today),
                'last_checkin_id' => CheckIn::select('id')
                    ->where(function ($q) {
                        $q->whereColumn('customer_id', 'customers.id')
                            ->orWhere(function ($entity) {
                                $entity->where('entity_type', 'customer')
                                    ->whereColumn('entity_id', 'customers.id');
                            });
                    })
                    ->where('user_id', $authUser->id)
                    ->orderByDesc('checkin_date')
                    ->orderByDesc('checkin_time')
                    ->limit(1),
            ]);

            $perPage = (int) $request->query('pageSize', 5);
            $customers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $cleanData = [
                'current_page' => $customers->currentPage(),
                'data'         => collect($customers->items())->map(function ($customer) {
                    return $this->formatCustomerListItem($customer);
                })->values(),
                'from'         => $customers->firstItem(),
                'to'           => $customers->lastItem(),
                'per_page'     => $customers->perPage(),
                'total'        => $customers->total(),
                'last_page'    => $customers->lastPage(),
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Customers retrieved successfully',
                'data'    => $cleanData,
            ], $this->successStatus);
        } catch (\Exception $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage(),
                ], $e->getStatusCode());
            }

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch customers',
                'error'   => $e->getMessage(),
            ], $this->internalError);
        }
    }

    private function resolveCustomerListTypeId($type): ?int
    {
        if (empty($type)) {
            return null;
        }

        if (is_numeric($type)) {
            return CustomerType::where('active', 'Y')->where('id', $type)->exists()
                ? (int) $type
                : null;
        }

        $type = trim((string) $type);

        return CustomerType::where('active', 'Y')
            ->where(function ($query) use ($type) {
                $query->where('customertype_name', $type)
                    ->orWhere('type_name', $type);
            })
            ->value('id');
    }

    private function customerListHasAnyRole($user, array $roles): bool
    {
        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        if ($user->relationLoaded('roles')) {
            return $user->roles->pluck('name')->intersect($roles)->isNotEmpty();
        }

        if (!empty($user->user_type)) {
            $userTypes = is_string($user->user_type)
                ? (json_decode($user->user_type, true) ?? [])
                : (array) $user->user_type;

            return !empty(array_intersect($roles, $userTypes));
        }

        return false;
    }

    private function applyCustomerListAccessScope($query, $authUser, Request $request): void
    {
        if ($authUser instanceof Customers) {
            $query->where('customers.id', $authUser->id);
            return;
        }

        $isSuperAdmin = $this->customerListHasAnyRole($authUser, ['superadmin', 'Admin', 'subAdmin', 'Sub_Admin']);

        if ($isSuperAdmin) {
            if ($request->filled('for_user_id')) {
                $targetUserId = $request->for_user_id;
                $query->where(function ($q) use ($targetUserId) {
                    $q->where('created_by', $targetUserId)
                        ->orWhere('executive_id', $targetUserId)
                        ->orWhereHas('getemployeedetail', function ($employee) use ($targetUserId) {
                            $employee->where('user_id', $targetUserId);
                        });
                });
            }

            return;
        }

        if ($request->filled('for_user_id')) {
            $targetUserId = (int) $request->for_user_id;
            $visibleUserIds = getUsersReportingToAuth($authUser->id);
            $visibleUserIds[] = $authUser->id;
            $visibleUserIds = array_unique($visibleUserIds);

            if (!in_array($targetUserId, $visibleUserIds)) {
                abort(403, 'You do not have permission to view this user\'s customers');
            }

            $visibleUserIds = [$targetUserId];
        } else {
            $visibleUserIds = getUsersReportingToAuth($authUser->id);
            $visibleUserIds[] = $authUser->id;
            $visibleUserIds = array_unique($visibleUserIds);
        }

        $query->where(function ($q) use ($visibleUserIds) {
            $q->whereIn('created_by', $visibleUserIds)
                ->orWhereIn('executive_id', $visibleUserIds)
                ->orWhereHas('getemployeedetail', function ($employee) use ($visibleUserIds) {
                    $employee->whereIn('user_id', $visibleUserIds);
                });
        });
    }

    private function applyCustomerTypeFilter($query, int $customerTypeId): void
    {
        $query->where('customertype', $customerTypeId);
    }

    private function formatCustomerListItem(Customers $customer): array
    {
        $address = $customer->customeraddress;
        $details = $customer->customerdetails;
        $beat = optional($customer->beatdetails)->beats;
        $customFields = is_string($customer->custom_fields ?? null)
            ? (json_decode($customer->custom_fields, true) ?? [])
            : (array) ($customer->custom_fields ?? []);
        $employeeIds = $customer->getemployeedetail
            ? $customer->getemployeedetail->pluck('user_id')->filter()->values()->implode(',')
            : '';
        $employeeNames = $customer->getemployeedetail
            ? $customer->getemployeedetail->pluck('employee_detail.name')->filter()->values()->implode(', ')
            : '';
        $documents = $customer->customerdocuments ?? collect();
        $aadharAttachment = optional($documents->firstWhere('document_name', 'aadhar'))->file_path;

        return [
            'id' => $customer->id,
            'customer_id' => $customer->id,
            'type' => optional($customer->customertypes)->customertype_name,
            'customertype' => $customer->customertype,
            'customer_type_id' => $customer->customertype,
            'customer_type' => optional($customer->customertypes)->customertype_name,
            'sub_type' => $customFields['sub_type'] ?? null,
            'owner_name' => trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')),
            'shop_name' => $customer->name ?? '',
            'mobile_number' => $customer->mobile ?? '',
            'whatsapp_number' => $customer->contact_number ?? '',
            'owner_photo' => $customer->profile_image ?? '',
            'shop_photo' => optional($details)->shop_image ?? $customer->shop_image ?? '',
            'vehicle_segment' => $customFields['vehicle_segment'] ?? null,
            'address_line' => optional($address)->full_address ?? trim((optional($address)->address1 ?? '') . ' ' . (optional($address)->address2 ?? '')),
            'belt_area_market_name' => optional($address)->locality ?? optional($address)->landmark ?? null,
            'saathi_awareness_status' => $customFields['saathi_awareness_status'] ?? $customFields['awareness_status'] ?? optional($details)->visit_status ?? null,
            'nistha_awareness_status' => $customFields['nistha_awareness_status'] ?? $customFields['awareness_status'] ?? optional($details)->visit_status ?? null,
            'opportunity_status' => $customFields['opportunity_status'] ?? optional($details)->grade ?? null,
            'gps_location' => ($customer->latitude && $customer->longitude) ? $customer->latitude . ',' . $customer->longitude : null,
            'country_id' => optional($address)->country_id,
            'state_id' => optional($address)->state_id,
            'district_id' => optional($address)->district_id,
            'city_id' => optional($address)->city_id,
            'pincode_id' => optional($address)->pincode_id,
            'beat_id' => optional($customer->beatdetails)->beat_id,
            'distributor_name' => $customer->parent_id ?? null,
            'gst_number' => optional($details)->gstin_no ?? '',
            'pan_number' => optional($details)->pan_no ?? '',
            'gst_attachment' => optional($documents->firstWhere('document_name', 'gstin'))->file_path,
            'pan_attachment' => optional($documents->firstWhere('document_name', 'pan'))->file_path,
            'aadhar' => $aadharAttachment,
            'aadhar_attachment' => $aadharAttachment,
            'aadhar_image' => $aadharAttachment,
            'bank_proof' => optional($documents->firstWhere('document_name', 'bankpass'))->file_path,
            'bank_account_type' => optional($details)->bank_account_type,
            'bank_account_number' => optional($details)->account_number,
            'bank_name' => optional($details)->bank_name,
            'ifsc_code' => optional($details)->ifsc_code,
            'account_holder_name' => optional($details)->account_holder,
            'status' => $customer->status_id,
            'active' => $customer->active,
            'employee_id' => $employeeIds ?: $customer->executive_id,
            'employee_names' => $employeeNames,
            'created_by' => $customer->created_by,
            'customer_code' => $customer->customer_code,
            'sap_code' => $customer->sap_code,
            'created_at' => $customer->created_at,
            'updated_at' => $customer->updated_at,
            'country' => optional($address)->countryname,
            'state' => optional($address)->statename,
            'district' => optional($address)->districtname,
            'city' => optional($address)->cityname,
            'pincode' => optional($address)->pincodename,
            'beat' => $beat,
            'distributor' => null,
            'last_checkin_date' => $customer->last_checkin_date,
            'last_checkin_time' => $customer->last_checkin_time,
            'has_checked_in_today' => (int) $customer->has_checked_in_today,
            'last_checkout_date' => $customer->last_checkout_date,
            'current_visit_is_open' => (int) $customer->current_visit_is_open,
            'last_checkout_time' => $customer->last_checkout_time,
            'has_checked_out_today' => (int) $customer->has_checked_out_today,
            'last_checkin_id' => $customer->last_checkin_id,
        ];
    }

    // public function getCustomerInfo(Request $request)
    // {

    //      try
    //     { 
    //         $validator = Validator::make($request->all(), [
    //              'customer_id' => 'nullable|exists:customers,id',
    //         ]); 
    //         if ($validator->fails()) {
    //             return response()->json(['status' => 'error','message' => $validator->messages()->all()],$this->badrequest);
    //         }
    //         $user = $request->user();
    //         $user_id = $user->id;
    //         $fromdate = isset($request->fromDate) ? $request->fromDate : null;
    //         $todate = isset($request->toDate) ? $request->toDate :null;

    //         $customer_id = $request->input('customer_id');

    //         $orders = Order::where(function ($query) use($customer_id, $fromdate, $todate){

    //             $query->where('buyer_id', '=', $customer_id);

    //             if(!empty($fromdate) && !empty($todate))
    //             {
    //                 $query->whereBetween('order_date', [$fromdate, $todate]);
    //             }

    //         })
    //         ->select('grand_total','id','total_qty')->get();

    //         //nnn

    //             $sum_quantity = 0;
    //            foreach($orders as $order){
    //              $sum_quantity = OrderDetails::where('order_id',$order->id)->sum('quantity')??0;
    //              }

    //             $sum_quantity = (int)$sum_quantity;


    //        ///nnn

    //         $sales = Sales::where(function ($query) use($customer_id, $fromdate, $todate){
    //             $query->where('buyer_id', '=', $customer_id);
    //             if(!empty($fromdate) && !empty($todate))
    //             {
    //                 $query->whereBetween('invoice_date', [$fromdate, $todate]);
    //             }
    //         })->select('grand_total')->get();

    //         $checkins = CheckIn::with('visitreports')->where('customer_id', '=', $customer_id)->select('checkin_date','checkin_time')->latest()->limit(10)->get();
    //         $last_order_date = Order::where('buyer_id', '=', $customer_id)->latest()->pluck('order_date')->first();
    //         $data = $this->customers->with('customerdetails','getparentdetail','parentdetail','customeraddress','customerdocuments','surveys','surveys.fields','customeraddress.cityname','customeraddress.districtname','customeraddress.statename','customeraddress.pincodename','customertypes','customerdeals')->where('id', $customer_id)->select('id','name','first_name','last_name','mobile','email','profile_image','customer_code','customertype','contact_number', 'latitude' , 'longitude',
    //                DB::raw('(SELECT SUM(grand_total) FROM sales WHERE sales.buyer_id = customers.id) as totalamount'), 
    //                DB::raw('(SELECT SUM(paid_amount) FROM sales WHERE sales.buyer_id = id) as totalpaid'))->first();

    //         $total_value = $orders->sum('grand_total');
    //         $total_qty = $orders->sum('total_qty');
    //         $beatinfo = Beat::whereHas('beatcustomers', function ($query) use($customer_id){
    //                             $query->where('customer_id','=',$customer_id);
    //                         })
    //                         ->select('beat_name','id')->first();


    //          //$data['parent_name'] = $data->parentdetail->name??'';

    //          $parent = array();
    //          $parent_id = array();
    //          if(!empty($data['getparentdetail']))
    //         {  

    //             foreach($data['getparentdetail'] as $key => $parent_data) {
    //                 $parent[] = isset($parent_data->parent_detail->name) ? $parent_data->parent_detail->name: '';
    //                 $parent_id[] = isset($parent_data->parent_id) ? $parent_data->parent_id: '';
    //             }

    //         }

    //         $data['parent_id'] = implode(',', $parent_id);
    //         $data['parent_name'] = implode(',', $parent);                
    //         $data['beat_name'] = isset($beatinfo['beat_name']) ? $beatinfo['beat_name'] : '';
    //         $data['beat_id'] = isset($beatinfo['id']) ? $beatinfo['id'] : null;
    //         $data['outstanding'] = $data['totalamount']-$data['totalpaid'];
    //         $data['total_order_value'] = $total_value;
    //         // $data['total_order_quantity'] = $total_qty; 
    //         $data['total_order_quantity'] = $sum_quantity; 

    //         $data['avg_order_value'] = ($total_value >= 1) ? number_format((float)$total_value/$orders->count(), 1, '.', '').' %'  : '';
    //         $data['avg_order_quantity'] = ($total_qty >= 1) ? number_format((float)$total_qty/$orders->count(), 1, '.', '').' %'  : '';
    //         $data['total_sales_value'] = $sales->sum('grand_total');
    //         $data['last_visited'] = (string)$checkins->pluck('checkin_date')->first();
    //         $data['last_order_date'] = isset($last_order_date) ? $last_order_date : '';
    //         $data['visited'] = $checkins;
    //         $data['email'] = isset($data['email']) ? $data['email'] : '';
    //         $data['customer_code'] = isset($data['customer_code']) ? $data['customer_code'] : '';
    //         $data['activities']= UserActivity::with('users')->where('customerid','=',$customer_id)->select('userid','time','description','type')->latest()->limit(5)->get();
    //         $data['tasks']= Tasks::with('users')->where('completed','=',0)->where('customer_id','=',$customer_id)->select('user_id','title','descriptions','datetime')->orderBy('datetime','asc')->limit(5)->get();
    //         unset($data['totalamount'] , $data['totalpaid']);
    //         $data['total_points'] = Wallet::where('customer_id','=',$customer_id)->where('transaction_type','=','Cr')->sum('points');
    //         $data['total_coupon_scan'] = Wallet::where('customer_id','=',$customer_id)->where('transaction_type','=','Cr')->sum('quantity');
    //         return response()->json(['status' => 'success','message' => 'Data retrieved successfully.','data' => $data ], $this->successStatus);
    //     }
    //     catch(\Exception $e)
    //     {
    //         return response()->json(['status' => 'error','message' => $e->getMessage() ], $this->internalError);
    //     }   
    // }

    public function getCustomerInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'id' => 'nullable|exists:customers,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->messages()->all()], $this->badrequest);
            }
            $authUser = $request->user();
            if (!$authUser) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated - please provide valid token',
                ], $this->unauthorized);
            }

            $fromdate = isset($request->fromDate) ? $request->fromDate : null;
            $todate = isset($request->toDate) ? $request->toDate : null;

            $customer_id = $request->input('customer_id') ?? $request->input('id');

            if (empty($customer_id) && $authUser instanceof Customers) {
                $customer_id = $authUser->id;
            }

            if (empty($customer_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer id is required.',
                ], $this->badrequest);
            }

            $orders = Order::where(function ($query) use ($customer_id, $fromdate, $todate) {

                $query->where('buyer_id', '=', $customer_id);

                if (!empty($fromdate) && !empty($todate)) {
                    $query->whereBetween('order_date', [$fromdate, $todate]);
                } else {
                    $cdate = Carbon::now();
                    $firstDateOfCurrentMonth = $cdate->startOfMonth()->toDateString();
                    $query->where('order_date', '>=', $firstDateOfCurrentMonth);
                }
            })
                ->select('sub_total', 'id', 'total_qty')->get();

            //nnn

            $sum_quantity = 0;
            foreach ($orders as $order) {
                $sum_quantity = OrderDetails::where('order_id', $order->id)->sum('quantity') ?? 0;
            }

            $sum_quantity = (int)$sum_quantity;


            ///nnn

            $sales = Sales::where(function ($query) use ($customer_id, $fromdate, $todate) {
                $query->where('buyer_id', '=', $customer_id);
                if (!empty($fromdate) && !empty($todate)) {
                    $query->whereBetween('invoice_date', [$fromdate, $todate]);
                }
            })->select('grand_total')->get();

            $checkins = CheckIn::where(function ($query) use ($customer_id) {
                $query->where('customer_id', '=', $customer_id)
                    ->orWhere(function ($entity) use ($customer_id) {
                        $entity->where('entity_type', 'customer')
                            ->where('entity_id', $customer_id);
                    });
            })->select('id', 'checkin_date', 'checkin_time', 'checkin_address', 'checkout_date', 'checkout_time', 'checkout_address', 'time_interval')->latest()->limit(10)->get();
            $last_order_date = Order::where('buyer_id', '=', $customer_id)->latest()->pluck('order_date')->first();
            $query = Customers::with(
                'customerdetails',
                'visitsinfo',
                'getparentdetail.parent_detail',
                'parentdetail',
                'customeraddress.countryname',
                'customeraddress.cityname',
                'customeraddress.districtname',
                'customeraddress.statename',
                'customeraddress.pincodename',
                'customerdocuments',
                'surveys',
                'surveys.fields',
                'customertypes',
                'customerdeals',
                'beatdetails.beats',
                'getemployeedetail.employee_detail',
                'createdbyname'
            )->where('id', $customer_id)->select(
                'customers.*',
                DB::raw('(SELECT SUM(grand_total) FROM sales WHERE sales.buyer_id = customers.id) as totalamount'),
                DB::raw('(SELECT SUM(paid_amount) FROM sales WHERE sales.buyer_id = customers.id) as totalpaid')
            );

            $this->applyCustomerListAccessScope($query, $authUser, $request);
            $customer = $query->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer not found',
                ], $this->notFound);
            }

            $total_value = $orders->sum('sub_total');
            $total_qty = $orders->sum('total_qty');
            $beatinfo = Beat::whereHas('beatcustomers', function ($query) use ($customer_id) {
                $query->where('customer_id', '=', $customer_id);
            })
                ->select('beat_name', 'id')->first();


            //$data['parent_name'] = $data->parentdetail->name??'';

            $parent = array();
            $parent_id = array();
            $activity = [];
            if (!empty($customer['visitsinfo'])) {
                foreach ($customer['visitsinfo'] as $key => $visit) {
                    $activity[] = [
                        "id" => $visit->id,
                        "customer_id" => $visit->customer_id,
                        "description" => $visit->description,
                        "report_title" => $visit->visittypename ? $visit->visittypename->type_name : '-',
                        "visit_image" => $visit->visit_image,
                        "user_id" => $visit->user_id,
                        "user_name" => $visit['users']->name ?? '',
                        "created_at" => Carbon::parse($visit->created_at)->format('d-m-Y'),
                    ];
                }

                // If you need to store this result back into $data, you can do so

            }

            if (!empty($customer['getparentdetail'])) {

                foreach ($customer['getparentdetail'] as $key => $parent_data) {
                    $parent[] = isset($parent_data->parent_detail->name) ? $parent_data->parent_detail->name : '';
                    $parent_id[] = isset($parent_data->parent_id) ? $parent_data->parent_id : '';
                }
            }

            $data = $this->formatCustomerListItem($customer);
            $data['email'] = isset($customer['email']) ? $customer['email'] : '';
            $data['profile_image'] = isset($customer['profile_image']) ? $customer['profile_image'] : '';
            $data['first_name'] = isset($customer['first_name']) ? $customer['first_name'] : '';
            $data['last_name'] = isset($customer['last_name']) ? $customer['last_name'] : '';
            $data['name'] = isset($customer['name']) ? $customer['name'] : '';
            $data['mobile'] = isset($customer['mobile']) ? $customer['mobile'] : '';
            $data['contact_number'] = isset($customer['contact_number']) ? $customer['contact_number'] : '';
            $data['latitude'] = isset($customer['latitude']) ? $customer['latitude'] : '';
            $data['longitude'] = isset($customer['longitude']) ? $customer['longitude'] : '';
            $data['activity'] = $activity;
            $data['parent_id'] = implode(',', $parent_id);
            $data['parent_name'] = implode(',', $parent);
            $data['beat_name'] = isset($beatinfo['beat_name']) ? $beatinfo['beat_name'] : '';
            $data['beat_id'] = isset($beatinfo['id']) ? $beatinfo['id'] : null;
            $data['outstanding'] = ($customer['totalamount'] ?? 0) - ($customer['totalpaid'] ?? 0);
            $data['total_order_value'] = $total_value;
            $data['total_order_quantity'] = $sum_quantity;

            $data['avg_order_value'] = ($total_value >= 1 && $orders->count() > 0) ? number_format((float)$total_value / $orders->count(), 1, '.', '') . ' %'  : '';
            $data['avg_order_quantity'] = ($total_qty >= 1 && $orders->count() > 0) ? number_format((float)$total_qty / $orders->count(), 1, '.', '') . ' %'  : '';
            $data['total_sales_value'] = $sales->sum('grand_total');
            if (!empty($last_order_date) && isset($last_order_date)) {
                $dateTime1 = Carbon::parse($last_order_date);
                $data['last_order_date'] = $dateTime1->format('d-m-Y');
            } else {
                $data['last_order_date'] = "";
            }
            if (!empty($customer['visitsinfo']) && isset($customer['visitsinfo'][0]->created_at)) {
                $dateTime = Carbon::parse($customer['visitsinfo'][0]->created_at);
                $data['last_visited'] = $dateTime->format('d-m-Y');
            } else {
                $data['last_visited'] = "";
            }

            // $data['last_visited'] = isset($data['visitsinfo']) ? $data['visitsinfo'][0]->created_at : '';
            // $data['last_order_date'] = isset($last_order_date) ? $last_order_date : '';
            $data['visited'] = $checkins;
            $data['activities'] = UserActivity::with('users')->where('customerid', '=', $customer_id)->select('userid', 'time', 'description', 'type')->latest()->limit(5)->get();
            $data['tasks'] = Tasks::with('users')->where('completed', '=', 0)->where('customer_id', '=', $customer_id)->select('user_id', 'title', 'descriptions', 'datetime')->orderBy('datetime', 'asc')->limit(5)->get();
            $data['total_points'] = Wallet::where('customer_id', '=', $customer_id)->where('transaction_type', '=', 'Cr')->sum('points');
            $data['total_coupon_scan'] = Wallet::where('customer_id', '=', $customer_id)->where('transaction_type', '=', 'Cr')->sum('quantity');
            $data['customerdetails'] = $customer->customerdetails;
            $data['customeraddress'] = $customer->customeraddress;
            $data['customerdocuments'] = $customer->customerdocuments;
            $data['surveys'] = $customer->surveys;
            $data['customerdeals'] = $customer->customerdeals;
            $data['visitsinfo'] = $customer->visitsinfo;
            $data['creator'] = $customer->createdbyname;

            $createdById = $customer->created_by;
            $hierarchy_level = 0;
            $hierarchy_label = 'Self';

            if (!($authUser instanceof Customers) && $createdById && $createdById != $authUser->id) {
                $hierarchy_level = getHierarchyLevel($createdById, $authUser->id);
                $hierarchy_label = match ($hierarchy_level) {
                    0   => 'Self',
                    -1  => 'Not in Hierarchy',
                    default => 'Level ' . $hierarchy_level
                };
            }

            $today = now()->startOfDay()->toDateString();
            $checkInQuery = CheckIn::where(function ($query) use ($customer_id) {
                $query->where('customer_id', '=', $customer_id)
                    ->orWhere(function ($entity) use ($customer_id) {
                        $entity->where('entity_type', 'customer')
                            ->where('entity_id', $customer_id);
                    });
            })->where('user_id', $authUser->id);

            $lastCheckIn = (clone $checkInQuery)
                ->orderByDesc('checkin_date')
                ->orderByDesc('checkin_time')
                ->first([
                    'id',
                    'checkin_date',
                    'checkin_time',
                    'checkin_address',
                    'checkout_date',
                    'checkout_time',
                    'checkout_address',
                    'time_interval'
                ]);

            $lastCheckOut = (clone $checkInQuery)
                ->whereNotNull('checkout_date')
                ->orderByDesc('checkout_date')
                ->orderByDesc('checkout_time')
                ->first(['checkout_date', 'checkout_time', 'checkout_address']);

            $checkData = [
                'last_checkin' => $lastCheckIn ? [
                    'checkin_id'       => $lastCheckIn->id,
                    'checkin_datetime' => $lastCheckIn->checkin_date . ' ' . $lastCheckIn->checkin_time,
                    'checkin_address'  => $lastCheckIn->checkin_address,
                    'checkout_datetime' => $lastCheckIn->checkout_date
                        ? $lastCheckIn->checkout_date . ' ' . $lastCheckIn->checkout_time
                        : null,
                    'checkout_address' => $lastCheckIn->checkout_address,
                    'duration'         => $lastCheckIn->time_interval ?? null,
                ] : null,

                'last_checkout' => $lastCheckOut ? [
                    'checkout_datetime' => $lastCheckOut->checkout_date . ' ' . $lastCheckOut->checkout_time,
                    'checkout_address'  => $lastCheckOut->checkout_address,
                ] : null,

                'today' => [
                    'has_checked_in'  => (clone $checkInQuery)->whereDate('checkin_date', $today)->exists(),
                    'has_checked_out' => (clone $checkInQuery)->whereDate('checkout_date', $today)->exists(),
                ],
            ];

            $linkedDistributors = collect($customer->getparentdetail ?? [])
                ->filter(fn($parentDetail) => !empty($parentDetail->parent_detail))
                ->map(fn($parentDetail) => [
                    'id' => $parentDetail->parent_id,
                    'shop_name' => $parentDetail->parent_detail->name,
                ])
                ->values();

            return response()->json([
                'status'      => true,
                'message'     => 'Customer retrieved successfully',
                'hierarchy_level' => $hierarchy_level,
                'hierarchy_label' => $hierarchy_label,
                'data'        => $data,
                'check_status' => $checkData,
                'distributors' => $linkedDistributors,
            ], $this->successStatus);
        } catch (\Exception $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage(),
                ], $e->getStatusCode());
            }

            return response()->json(['status' => false, 'message' => 'Failed to retrieve customer', 'error' => $e->getMessage()], $this->internalError);
        }
    }

    public function updateCustomerProfile(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated - please provide valid bearer token.',
                ], $this->unauthorized);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'mobile' => 'nullable|numeric',
                'customertype' => 'nullable|exists:customer_types,id',
                'bank_account_type' => 'nullable|in:Savings,Current,savings,current',
                'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'shopimage' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'shop_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'visiting_card' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'gstin_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'pan_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'imgaadhar' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhar' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhar_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadharImage' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhar_attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadharAttachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhaar' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhaar_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'aadhaarImage' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'other_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'bank_proof' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->messages()->all()], $this->badrequest);
            }

            $customer = Customers::find($request->customer_id);
            $mobile = $this->normalizeCustomerMobile($request->input('mobile'));

            if ($mobile && Customers::where('mobile', $mobile)->where('id', '!=', $customer->id)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Mobile Number Already Exist'], $this->badrequest);
            }

            if ($request->filled('gstin_no') && CustomerDetails::where('gstin_no', $request->gstin_no)
                ->where('customer_id', '!=', $customer->id)
                ->whereNotNull('gstin_no')
                ->exists()) {
                return response()->json(['status' => 'error', 'message' => 'GST Number Already Exist'], $this->badrequest);
            }

            if ($request->filled('full_name') && (!$request->filled('first_name') || !$request->filled('last_name'))) {
                $name = explode(' ', trim((string) $request->input('full_name')));
                $request->merge([
                    'last_name' => $request->filled('last_name') ? $request->last_name : array_pop($name),
                    'first_name' => $request->filled('first_name') ? $request->first_name : implode(' ', $name),
                ]);
            }

            if ($image = $this->requestFileByAnyKey($request, ['image', 'profileImage', 'profile_image', 'owner_photo', 'ownerPhoto'])) {
                $request->merge(['profile_image' => fileupload($image, $this->path, 'profile_')]);
            }

            if ($image = $this->requestFileByAnyKey($request, ['shopimage', 'shopImage', 'shop_image', 'shop_photo', 'shopPhoto'])) {
                $request->merge(['shop_image' => fileupload($image, $this->path, 'shop_')]);
            }

            $customerUpdates = ['updated_by' => $user->id, 'updated_at' => getcurentDateTime()];
            $customerFieldMap = [
                'name' => 'name',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'email' => 'email',
                'notification_id' => 'notification_id',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
                'device_type' => 'device_type',
                'gender' => 'gender',
                'customer_code' => 'customer_code',
                'status_id' => 'status_id',
                'customertype' => 'customertype',
                'firmtype' => 'firmtype',
                'manager_name' => 'manager_name',
                'manager_phone' => 'manager_phone',
                'contact_number' => 'contact_number',
                'same_address' => 'same_address',
                'custom_fields' => 'custom_fields',
                'working_status' => 'working_status',
                'creation_date' => 'creation_date',
                'sap_code' => 'sap_code',
                'profile_image' => 'profile_image',
                'shop_image' => 'shop_image',
            ];

            foreach ($customerFieldMap as $requestKey => $column) {
                if ($request->exists($requestKey)) {
                    $value = $request->input($requestKey);
                    if ($requestKey === 'custom_fields' && is_array($value)) {
                        $value = json_encode($value);
                    }
                    if (in_array($requestKey, ['name', 'first_name', 'last_name', 'device_type', 'gender'], true) && $value !== null && $value !== '') {
                        $value = ucfirst($value);
                    }
                    if ($requestKey === 'firmtype' && $value !== null && $value !== '' && !is_numeric($value)) {
                        $value = null;
                    }
                    if ($requestKey === 'same_address') {
                        $value = (int) (bool) $value;
                    }
                    $customerUpdates[$column] = $value;
                }
            }

            if ($mobile) {
                $customerUpdates['mobile'] = $mobile;
            }
            if ($request->filled('password')) {
                $customerUpdates['password'] = Hash::make($request->password);
            }

            $customer->update($customerUpdates);

            if ($request->exists('parent_id')) {
                ParentDetail::where('customer_id', $customer->id)->delete();
                $parentData = array_filter(array_map('trim', explode(',', (string) $request->parent_id)), function ($value) {
                    return is_numeric($value);
                });

                foreach ($parentData as $parentId) {
                    ParentDetail::create([
                        'customer_id' => $customer->id,
                        'parent_id' => $parentId,
                        'created_by' => $user->id,
                    ]);
                }

                $customer->update(['parent_id' => !empty($parentData) ? reset($parentData) : null]);
            }

            $assignmentKeys = ['assigned_user_ids', 'employee_id', 'executive_id', 'user_id'];
            if ($this->requestHasAny($request, $assignmentKeys)) {
                $employeeInput = $request->input(
                    'assigned_user_ids',
                    $request->input('employee_id', $request->input('executive_id', $request->input('user_id')))
                );
                EmployeeDetail::where('customer_id', $customer->id)->delete();
                $employeeIds = collect(is_array($employeeInput) ? $employeeInput : explode(',', (string) $employeeInput))
                    ->filter(fn($value) => is_numeric($value))
                    ->map(fn($value) => (int) $value)
                    ->unique()
                    ->values();

                foreach ($employeeIds as $employeeId) {
                    EmployeeDetail::create([
                        'customer_id' => $customer->id,
                        'user_id' => $employeeId,
                        'created_by' => $user->id,
                    ]);
                }

                $customer->update(['executive_id' => $employeeIds->first()]);
            }

            if ($this->requestHasAny($request, ['address_id', 'address1', 'address2', 'address', 'landmark', 'locality', 'country_id', 'state_id', 'district_id', 'city_id', 'pincode_id', 'zipcode'])) {
                $pincode = $request->filled('zipcode') ? Pincode::with('cityname', 'cityname.districtname')->where('pincode', $request->zipcode)->first() : null;
                $stateId = !empty($pincode['cityname']['districtname']['state_id']) ? $pincode['cityname']['districtname']['state_id'] : $request->input('state_id');
                $districtId = !empty($pincode['cityname']['district_id']) ? $pincode['cityname']['district_id'] : $request->input('district_id');
                $cityId = !empty($pincode['city_id']) ? $pincode['city_id'] : $request->input('city_id');
                $pincodeId = !empty($pincode['id']) ? $pincode['id'] : $request->input('pincode_id');
                $countryId = $request->input('country_id') ?: ($stateId ? State::where('id', $stateId)->pluck('country_id')->first() : null);
                $addressMatch = ['customer_id' => $customer->id];
                if ($request->filled('address_id')) {
                    $addressMatch['id'] = $request->address_id;
                }

                Address::updateOrCreate($addressMatch, [
                    'active'    => 'Y',
                    'customer_id' => $customer->id,
                    'address1' => $request->input('address1', $request->input('address', '')),
                    'address2' => $request->input('address2', ''),
                    'landmark' => $request->input('landmark', ''),
                    'locality' => $request->input('locality', $request->input('landmark', '')),
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'district_id' => $districtId,
                    'city_id' => $cityId,
                    'pincode_id' => $pincodeId,
                    'zipcode' => $request->input('zipcode', ''),
                    'created_by' => $user->id,
                    'updated_at' => getcurentDateTime(),
                ]);
            }

            if ($image = $this->requestFileByAnyKey($request, ['visiting_card', 'visitingCard'])) {
                $request->merge(['visiting_image' => fileupload($image, $this->path, 'visiting_card_')]);
            }
            if ($image = $this->requestFileByAnyKey($request, ['gstin_image', 'gstinImage', 'gst_attachment', 'gstAttachment'])) {
                $this->saveCustomerAttachment($customer->id, 'gstin', $image);
            }
            if ($image = $this->requestFileByAnyKey($request, ['pan_image', 'panImage', 'pan_attachment', 'panAttachment'])) {
                $this->saveCustomerAttachment($customer->id, 'pan', $image);
            }
            if ($image = $this->requestFileByAnyKey($request, [
                'imgaadhar',
                'aadhar',
                'aadhar_image',
                'aadharImage',
                'aadhar_attachment',
                'aadharAttachment',
                'aadhaar',
                'aadhaar_image',
                'aadhaarImage',
            ])) {
                $this->saveCustomerAttachment($customer->id, 'aadhar', $image);
            }
            if ($image = $this->requestFileByAnyKey($request, ['other_image', 'otherImage', 'additionalDocument', 'mouDocument'])) {
                $this->saveCustomerAttachment($customer->id, 'other', $image);
            }
            if ($image = $this->requestFileByAnyKey($request, ['bank_proof', 'bankProof', 'bankProofImage', 'imgbankpass', 'cancelledCheque', 'cancelled_cheque'])) {
                $this->saveCustomerAttachment($customer->id, 'bankpass', $image);
            }

            $detailUpdates = ['active' => 'Y', 'customer_id' => $customer->id, 'updated_at' => getcurentDateTime()];
            $detailFieldMap = [
                'gstin_no' => 'gstin_no',
                'pan_no' => 'pan_no',
                'aadhar_no' => 'aadhar_no',
                'account_holder' => 'account_holder',
                'bank_account_type' => 'bank_account_type',
                'account_number' => 'account_number',
                'bank_name' => 'bank_name',
                'ifsc_code' => 'ifsc_code',
                'otherid_no' => 'otherid_no',
                'enrollment_date' => 'enrollment_date',
                'approval_date' => 'approval_date',
                'grade' => 'grade',
                'status_type' => 'visit_status',
                'visiting_image' => 'visiting_card',
                'shop_image' => 'shop_image',
            ];

            foreach ($detailFieldMap as $requestKey => $column) {
                if ($request->exists($requestKey)) {
                    $value = $request->input($requestKey);
                    if (in_array($requestKey, ['gstin_no', 'pan_no', 'aadhar_no', 'account_holder', 'otherid_no'], true) && $value !== null && $value !== '') {
                        $value = ucfirst($value);
                    }
                    if ($requestKey === 'bank_account_type' && $value !== null && $value !== '') {
                        $value = ucfirst(strtolower($value));
                    }
                    $detailUpdates[$column] = $value;
                }
            }

            if (count($detailUpdates) > 3) {
                CustomerDetails::updateOrCreate(['customer_id' => $customer->id], $detailUpdates);
            }

            if ($request->exists('beat_id')) {
                BeatCustomer::updateOrCreate(['customer_id' => $customer->id], [
                    'active' => 'Y',
                    'beat_id' => $request->beat_id,
                    'customer_id' => $customer->id,
                    'updated_at' => getcurentDateTime(),
                ]);
            }

            if ($request->filled('survey')) {
                $surveyQuestions = json_decode($request->survey, true);
                if (is_array($surveyQuestions)) {
                    foreach ($surveyQuestions as $row) {
                        if (empty($row['field_id'])) {
                            continue;
                        }

                        SurveyData::updateOrCreate([
                            'customer_id' => $customer->id,
                            'field_id' => $row['field_id'],
                        ], [
                            'customer_id' => $customer->id,
                            'field_id' => $row['field_id'],
                            'value' => $row['value'] ?? '',
                            'created_by' => $user->id,
                            'updated_at' => getcurentDateTime(),
                        ]);
                    }
                }
            }

            if ($request->filled('dealing')) {
                $dealings = json_decode($request->dealing, true);
                if (is_array($dealings)) {
                    foreach ($dealings as $deal) {
                        if (empty($deal['types'])) {
                            continue;
                        }

                        DealIn::updateOrCreate([
                            'customer_id' => $customer->id,
                            'types' => $deal['types'],
                        ], [
                            'customer_id' => $customer->id,
                            'types' => $deal['types'],
                            'hcv' => $deal['hcv'] ?? false,
                            'mav' => $deal['mav'] ?? false,
                            'lmv' => $deal['lmv'] ?? false,
                            'lcv' => $deal['lcv'] ?? false,
                            'other' => $deal['other'] ?? false,
                            'tractor' => $deal['tractor'] ?? false,
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 200,
                'msg' => 'Customer Update Successfully',
                'message' => 'Customer Update Successfully',
                'customer_id' => $customer->id,
                'data' => $customer->fresh(['customerdetails', 'customeraddress', 'customerdocuments']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $this->internalError);
        }
    }

    public function active(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->all()], $this->badrequest);
        }
        if (Customers::where('id', $request['id'])->update(['active' => ($request['active'] == 'Y') ? 'Y' : 'N'])) {
            $message = ($request['active'] == 'N') ? 'Inactive' : 'Active';
            return response()->json(['status' => 'success', 'message' => 'Customer ' . $message . ' Successfully!']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error in Status Update']);
    }
}
