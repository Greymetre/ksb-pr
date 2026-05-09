<x-app-layout>
    <style>
    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    #bank-verification-message {
        transition: all 0.2s ease;
    }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">storefront</i>
                    </div>
                    <h4 class="card-title">
                        {{ isset($customer) && $customer->exists ? 'Edit' : 'Add New' }} Retailer
                        <span class="pull-right">
                            <a href="{{ route('retailers.index') }}" class="btn btn-just-icon btn-theme">
                                <i class="material-icons">next_plan</i>
                            </a>
                        </span>
                    </h4>
                </div>

                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert">
                            <i class="material-icons">close</i>
                        </button>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {!! Form::model($customer ?? new SecondaryCustomer, [
                    'route' => isset($customer) && $customer->exists
                    ? ['retailers.update', $customer]
                    : 'retailers.store',
                    'method' => isset($customer) && $customer->exists ? 'PUT' : 'POST',
                    'files' => true,
                    'id' => 'retailerForm'
                    ]) !!}

                    {{-- BEST: Dynamic type from controller (no typo risk) --}}
                    {!! Form::hidden('type', $type) !!}

                    <!-- <div class="row"> -->
                    <!-- <div class="col-md-6"> -->
                    <!-- <label>Type</label> -->
                    <input type="text" value="RETAILER" disabled hidden>
                    <!-- </div> -->

                    <!-- </div> -->
                    <div class="row">
                        <div class="col-md-6">
                            <label>Shop Name <span class="text-danger">*</span></label>
                            {!! Form::text('shop_name', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                        <div class="col-md-6">
                            <label>Owner Name <span class="text-danger">*</span></label>
                            {!! Form::text('owner_name', old('owner_name', $customer->owner_name ?? null), [
                            'class' => 'form-control',
                            'required'
                            ]) !!}
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">

                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="mb-0">
                                    Mobile Numbers <span class="text-danger">*</span>
                                </label>

                                <button type="button" class="btn btn-success btn-sm add-mobile">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>

                            <!-- Mobile Inputs -->
                            <div id="mobile_numbers_wrapper" class="row">

                                <div class="mobile-row col-md-6 mb-2 ">

                                    <div class="input-group">

                                        <input type="text" name="mobile_numbers[]" class="form-control mr-2"
                                            placeholder="Mobile Number 1" maxlength="10" pattern="[6-9]{1}[0-9]{9}"
                                            required>

                                        <div class="input-group-append">
                                            <button class="btn btn-danger remove-mobile btn-sm" type="button">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <small class="text-muted">You can add up to 5 mobile numbers.</small>

                        </div>
                    </div>
                    <!-- <div class="row mt-4">
                        <div class="col-md-6">
                            <label>Grade<span class="text-danger">*</span></label>
                            {!! Form::select('sub_type', [
                            '' => 'Select Grade',
                            'A' => 'A',
                            'B' => 'B',
                            'C' => 'C',
                            'D' => 'D',
                            ], old('sub_type', $customer->sub_type ?? null), [
                            'class' => 'form-control select2',
                            'required'
                            ]) !!}
                        </div>
                        <div class="col-md-6">
                            <label>Select Focus</label>
                            {!! Form::select('vehicle_segment', [
                            '' => 'Select Focus',
                            'Agri Focus' => 'Agri Focus',
                            'Domestic Focus' => 'Domestic Focus',
                            ], null, ['class' => 'form-control select2']) !!}
                        </div>
                        
                    </div> -->

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label>Distributor Name <span class="text-danger">*</span></label>
                            {!! Form::select('distributor_name', $distributorOptions ?? [],
                            old('distributor_name', $customer->distributor_name ?? null), [
                            'class' => 'form-control select2',
                            'id' => 'distributor_name',
                            'required',
                            'placeholder' => 'Select Domestic Distributor'
                            ]) !!}
                        </div>

                        <div class="col-md-6">
                            <label>Agri Distributor <span class="text-danger">*</span></label>

                            {!! Form::select('agri_distributor', $distributorOptions ?? [],
                            old('agri_distributor', $customer->agri_distributor ?? null), [
                            'class' => 'form-control select2',
                            'id' => 'agri_distributor',
                            'placeholder' => 'Select Agri Distributor'
                            ]) !!}
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label>Select Employee <span class="text-danger">*</span></label>
                           {!! Form::select('employee_id[]', $users ?? [],
    old('employee_id', isset($customer) ? explode(',', $customer->employee_id) : null), [
    'class' => 'form-control select2',
    'id' => 'employee_id',
    'multiple' => true
]) !!}
                        </div>
                        <div class="col-md-6">
                            <label>Beat <span class="text-danger">*</span></label>
                            {!! Form::select('beat_id', ['' => 'Select Beat'] + $beats->toArray(),
                            old('beat_id', $customer->beat_id ?? null), [
                            'class' => 'form-control select2',

                            ]) !!}
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label>Address Line <span class="text-danger">*</span></label>
                            {!! Form::textarea('address_line', null, ['class' => 'form-control', 'rows' => 3,
                            'required']) !!}
                        </div>
                    </div>

                    <div class="row mt-4">

                        <!-- Country -->
                        <div class="col-md-6 mb-3">
                            <label class="col-form-label">
                                Country <span class="text-danger">*</span>
                            </label>

                            <select class="form-control select2" name="country_id" id="country_id">
                                <option value="">Select Country</option>

                                @php
                                $countries = \Cache::remember('active_countries_list', 1440, function () {
                                return \App\Models\Country::where('active', 'Y')
                                ->orderBy('country_name', 'asc')
                                ->get(['id', 'country_name']);
                                });
                                @endphp

                                @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                    {{ old('country_id', $customer->country_id ?? 1) == $country->id ? 'selected' : '' }}>
                                    {{ $country->country_name }}
                                </option>
                                @endforeach
                            </select>

                            @error('country_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- State -->
                        <div class="col-md-6 mb-3">
                            <label class="col-form-label">
                                State <span class="text-danger">*</span>
                            </label>

                            <select class="form-control select2" name="state_id" id="state_id">
                                <option value="">Select State</option>

                                @if(old('country_id') || ($customer->country_id ?? null))
                                @php
                                $states = \App\Models\State::where('country_id', old('country_id', $customer->country_id
                                ?? ''))
                                ->where('active', 'Y')
                                ->orderBy('state_name')
                                ->get();
                                @endphp

                                @foreach($states as $state)
                                <option value="{{ $state->id }}"
                                    {{ old('state_id', $customer->state_id ?? '') == $state->id ? 'selected' : '' }}>
                                    {{ $state->state_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>

                            @error('state_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- District -->
                        <div class="col-md-6 mb-3">
                            <label class="col-form-label">
                                District <span class="text-danger">*</span>
                            </label>

                            <select class="form-control select2" name="district_id" id="district_id">
                                <option value="">Select District</option>

                                @if(old('state_id') || ($customer->state_id ?? null))
                                @php
                                $districts = \App\Models\District::where('state_id', old('state_id', $customer->state_id
                                ?? ''))
                                ->where('active', 'Y')
                                ->get();
                                @endphp

                                @foreach($districts as $district)
                                <option value="{{ $district->id }}"
                                    {{ old('district_id', $customer->district_id ?? '') == $district->id ? 'selected' : '' }}>
                                    {{ $district->district_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>

                            @error('district_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="col-md-6 mb-3">
                            <label class="col-form-label">
                                City <span class="text-danger">*</span>
                            </label>

                            <select class="form-control select2" name="city_id" id="city_id">
                                <option value="">Select City</option>

                                @if(old('district_id') || ($customer->district_id ?? null))
                                @php
                                $cities = \App\Models\City::where('district_id', old('district_id',
                                $customer->district_id ?? ''))
                                ->where('active', 'Y')
                                ->get();
                                @endphp

                                @foreach($cities as $city)
                                <option value="{{ $city->id }}"
                                    {{ old('city_id', $customer->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                    {{ $city->city_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>

                            @error('city_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Pincode -->
                        <div class="col-md-6 ">
                            <label class="col-form-label">
                                Pincode <span class="text-danger">*</span>
                            </label>

                            <select class="form-control select2" name="pincode_id" id="pincode_id">
                                <option value="">Select Pincode</option>

                                @if(old('city_id') || ($customer->city_id ?? null))
                                @php
                                $pincodes = \App\Models\Pincode::where('city_id', old('city_id', $customer->city_id ??
                                ''))
                                ->where('active', 'Y')
                                ->get();
                                @endphp

                                @foreach($pincodes as $pincode)
                                <option value="{{ $pincode->id }}"
                                    {{ old('pincode_id', $customer->pincode_id ?? '') == $pincode->id ? 'selected' : '' }}>
                                    {{ $pincode->pincode }}
                                </option>
                                @endforeach
                                @endif
                            </select>

                            @error('pincode_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Belt / Area -->
                        <div class="col-md-6 ">
                            <label class="col-form-label">
                                Belt / Area / Market Name
                            </label>
                            {!! Form::text('belt_area_market_name', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- GST Column -->
                        <div class="col-md-6">
                            <label>GST Number</label>
                            {!! Form::text('gst_number', null, [
                            'class' => 'form-control',
                            'placeholder' => '22AAAAA0000A1Z5',
                            'pattern' => '[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}',
                            'title' => 'Enter valid 15-character GSTIN'
                            ]) !!}
                            @error('gst_number')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <div class="mt-3">
                                <label>GST Attachment</label>
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail">
                                        <img id="gst_attachment_preview"
                                            src="{{ isset($customer) && $customer->gst_attachment ? asset('storage/'.$customer->gst_attachment) : asset('assets/img/placeholder.jpg') }}"
                                            style="width: 100%; max-width: 200px; height: 200px; object-fit: cover; border-radius: 4px;"
                                            alt="GST Attachment Preview">
                                    </div>
                                    <div class="fileinput-preview fileinput-exists thumbnail"></div>
                                    <div class="mt-2">
                                        <span class="btn btn-just-icon btn-round btn-file">
                                            <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                                            <span class="fileinput-exists">Change</span>
                                            <input type="file" name="gst_attachment" id="gst_attachment"
                                                accept="image/*,.pdf">
                                        </span>
                                        <a href="#" class="btn btn-danger btn-round btn-sm fileinput-exists"
                                            data-dismiss="fileinput">
                                            <i class="fa fa-times"></i> Remove
                                        </a>
                                    </div>
                                </div>
                                @error('gst_attachment')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- PAN Column -->
                        <div class="col-md-6">
                            <label>PAN Number</label>
                            {!! Form::text('pan_number', null, [
                            'class' => 'form-control',
                            'placeholder' => 'AAAAA0000A',
                            'pattern' => '[A-Z]{5}[0-9]{4}[A-Z]{1}',
                            'title' => 'Enter valid 10-character PAN'
                            ]) !!}
                            @error('pan_number')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <div class="mt-3">
                                <label>PAN Attachment</label>
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail">
                                        <img id="pan_attachment_preview"
                                            src="{{ isset($customer) && $customer->pan_attachment ? asset('storage/'.$customer->pan_attachment) : asset('assets/img/placeholder.jpg') }}"
                                            style="width: 100%; max-width: 200px; height: 200px; object-fit: cover; border-radius: 4px;"
                                            alt="PAN Attachment Preview">
                                    </div>
                                    <div class="fileinput-preview fileinput-exists thumbnail"></div>
                                    <div class="mt-2">
                                        <span class="btn btn-just-icon btn-round btn-file ">
                                            <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                                            <span class="fileinput-exists">Change</span>
                                            <input type="file" name="pan_attachment" id="pan_attachment"
                                                accept="image/*,.pdf">
                                        </span>
                                        <a href="#" class="btn btn-danger btn-round btn-sm fileinput-exists"
                                            data-dismiss="fileinput">
                                            <i class="fa fa-times"></i> Remove
                                        </a>
                                    </div>
                                </div>
                                @error('pan_attachment')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label>Bank Account Type</label>
                            {!! Form::select('bank_account_type', [
                            '' => 'Select Account Type',
                            'Savings' => 'Savings',
                            'Current' => 'Current'
                            ], old('bank_account_type', $customer->bank_account_type ?? null), [
                            'class' => 'form-control select2',

                            ]) !!}
                            @error('bank_account_type')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label>Bank Name</label>
                            {!! Form::text('bank_name', null, [
                            'class' => 'form-control',

                            'placeholder' => 'e.g. State Bank of India'
                            ]) !!}
                            @error('bank_name')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Bank Account Number <small class="text-muted">(optional)</small></label>
                            <input type="password" name="bank_account_number" id="bank_account_number"
                                class="form-control" placeholder="Enter Bank Account Number" autocomplete="new-password"
                                value="{{ old('bank_account_number', $customer->bank_account_number ?? '') }}">
                            @error('bank_account_number')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label>Confirm Bank Account Number <small id="confirm-label"
                                    class="text-muted">(optional)</small></label>
                            <input type="text" id="bank_account_number_confirm" class="form-control"
                                name="bank_account_number_confirm" placeholder="Re-type Bank Account Number"
                                autocomplete="off">

                            <div id="bank-verification-message" class="mt-1 small fw-bold" style="min-height: 1.4em;">
                            </div>
                        </div>
                    </div>


                    <div class="row mt-3">

                        <div class="col-md-6">
                            <label>IFSC Code</label>
                            {!! Form::text('ifsc_code', null, [
                            'class' => 'form-control',

                            'placeholder' => 'e.g. SBIN0001234',
                            'pattern' => '^[A-Z]{4}0[A-Z0-9]{6}$',
                            'title' => 'Enter valid 11-character IFSC code'
                            ]) !!}
                            @error('ifsc_code')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label>Account Holder Name</label>
                            {!! Form::text('account_holder_name', null, [
                            'class' => 'form-control',

                            'placeholder' => 'Name as per bank account'
                            ]) !!}
                            @error('account_holder_name')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>



                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Blank Cheque / Passbook Attachment</label>
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-new thumbnail">
                                    <img id="bank_proof_preview"
                                        src="{{ isset($customer) && $customer->bank_proof ? asset('storage/'.$customer->bank_proof) : asset('assets/img/placeholder.jpg') }}"
                                        style="width: 200px; height: 200px; object-fit: cover;" alt="Bank Proof">
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail"></div>
                                <div>
                                    <span class="btn btn-just-icon btn-round btn-file">
                                        <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file" name="bank_proof" id="bank_proof" accept="image/*,.pdf"
                                            class="form-control">
                                    </span>
                                    <a href="#" class="btn btn-danger btn-round fileinput-exists"
                                        data-dismiss="fileinput">
                                        <i class="fa fa-times"></i> Remove
                                    </a>
                                </div>
                            </div>
                            @error('bank_proof')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label>Shop Photo</label>
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                <div class="fileinput-new thumbnail">
                                    <img id="shop_photo_preview"
                                        src="{{ isset($customer) && $customer->shop_photo ? asset('storage/'.$customer->shop_photo) : asset('assets/img/placeholder.jpg') }}"
                                        class="" style="width: 200px; height: 200px; object-fit: cover;"
                                        alt="Shop Photo">
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail"></div>
                                <div>
                                    <span class="btn btn-just-icon btn-round btn-file">
                                        <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file" name="shop_photo" id="shop_photo" accept="image/*"
                                            class="form-control">
                                    </span>
                                    <a href="#" class="btn btn-danger btn-round fileinput-exists"
                                        data-dismiss="fileinput">
                                        <i class="fa fa-times"></i> Remove
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row mt-4">
                        <div class="col-md-4">
                            <label>Nistha Awareness Status <span class="text-danger">*</span></label>
                            {!! Form::select('nistha_awareness_status', [
                            'Not Done' => 'Not Done',
                            'Done' => 'Done'
                            ], old('nistha_awareness_status', $customer->nistha_awareness_status ?? null), [
                            'class' => 'form-control select2',
                            'required'
                            ]) !!}
                        </div>

                        <div class="col-md-4">
                            <label>Opportunity Status <span class="text-danger">*</span></label>
                            {!! Form::select('opportunity_status', [
                            'COLD' => 'COLD – Low interest / only enquiry',
                            'WARM' => 'WARM – Interested but needs time',
                            'HOT' => 'HOT – Very interested/almost confirm',
                            'LOST' => 'LOST – Deal cancelled'
                            ], old('opportunity_status', $customer->opportunity_status ?? null), [
                            'class' => 'form-control select2',
                            'required'
                            ]) !!}
                        </div>
                    </div> -->

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label>GPS Location Updates</label>
                            {!! Form::text('gps_location', old('gps_location', $customer->gps_location ?? null), [
                            'class' => 'form-control',
                            'placeholder' => 'e.g., 17.385044,78.486671'
                            ]) !!}
                        </div>
                    </div>

                    <div class="card-footer text-right mt-5">
                        <button type="submit" class="btn btn-theme">
                            {{ isset($customer) && $customer->exists ? 'Update' : 'Save' }} Retailer
                        </button>
                        <a href="{{ route('retailers.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
    $('.select2').select2();
    // Address AJAX chaining script (same as before)
    </script>
    <script>
    $(document).ready(function() {


        $('.select2').select2({
            placeholder: function() {
                return $(this).data('placeholder') || "Select option";
            },
            allowClear: true
        });


        function loadStates(country_id, selectedStateId = null) {
            if (!country_id) {
                $('#state_id').html('<option value="">Select State</option>').trigger('change');
                resetLowerSelects();
                return;
            }

            $('#state_id')
                .html('<option value="">Loading states...</option>')
                .trigger('change');

            const url = '{{ route("get.states", ":country_id") }}'.replace(':country_id', country_id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let options = '<option value="">Select State</option>';
                    $.each(data, function(i, state) {
                        let isSelected = (selectedStateId && state.id == selectedStateId) ?
                            'selected' : '';
                        options +=
                            `<option value="${state.id}" ${isSelected}>${state.state_name}</option>`;
                    });
                    $('#state_id').html(options).trigger('change');
                },
                error: function(xhr) {
                    console.error("States AJAX failed:", xhr.status, xhr.responseText);
                    alert("Failed to load states. Check console + network tab.");
                }
            });
        }


        function resetLowerSelects() {
            $('#district_id, #city_id, #pincode_id')
                .html('<option value="">Select...</option>')
                .trigger('change');
        }

        // ─── Country change ───
        $('#country_id').on('change', function() {
            let country_id = $(this).val();
            loadStates(country_id, null); // no pre-selected state on country change
        });


        setTimeout(function() {
            let initialCountry = $('#country_id').val() || 1; // fallback to India if empty

            // Force select2 to show correct value
            $('#country_id').val(initialCountry).trigger('change.select2');

            // Now load states (with pre-selected value in edit mode)
            // loadStates(initialCountry, {
            //     {
            //         old('state_id', $customer - > state_id ?? 'null')
            //     }
            // });
            loadStates(initialCountry, "{{ old('state_id', $customer->state_id ?? '') }}");

        }, 300);

        // ────────────────────────────────────────────────
        //   BANK ACCOUNT VERIFICATION – STRICT MATCH REQUIRED
        // ────────────────────────────────────────────────
        const $acct = $('#bank_account_number');
        const $confirm = $('#bank_account_number_confirm');
        const $msg = $('#bank-verification-message');
        const $label = $('#confirm-label');

        function checkBankMatch() {
            const val1 = $acct.val().trim();
            const val2 = $confirm.val().trim();

            // Reset message & styling
            $msg.html('').removeClass('text-success text-danger');

            // Case 1: both empty → optional, no message, allowed to submit
            if (!val1 && !val2) {
                $confirm.prop('required', false);
                $label.text('(optional)');
                return {
                    isValid: true,
                    shouldBlock: false
                };
            }

            // Case 2: account filled → confirmation becomes mandatory
            $confirm.prop('required', true);
            $label.text('(required if account number is entered)');

            // Still empty confirm → show nothing yet (but will block on submit)
            if (!val2) {
                return {
                    isValid: false,
                    shouldBlock: true
                };
            }

            // Both filled → compare
            if (val1 === val2) {
                $msg.html('✓ Bank account number verified').addClass('text-success');
                return {
                    isValid: true,
                    shouldBlock: false
                };
            } else {
                $msg.html('✗ Bank account numbers do not match').addClass('text-danger');
                return {
                    isValid: false,
                    shouldBlock: true
                };
            }
        }

        // Real-time feedback
        $acct.on('input paste change', checkBankMatch);
        $confirm.on('input paste change', checkBankMatch);

        // ────────────────────────────────────────────────
        //   Form Submit Protection
        // ────────────────────────────────────────────────

        // ==================== BILLING ADDRESS AJAX CHAINING ====================
        // function resetLowerSelects(prefix) {
        //     var selects = {
        //         country: prefix + '_country_id',
        //         state: prefix + '_state_id',
        //         district: prefix + '_district_id',
        //         city: prefix + '_city_id',
        //         pincode: prefix + '_pincode_id'
        //     };

        //     var startReset = false;
        //     $.each(selects, function(key, id) {
        //         if (startReset) {
        //             var label = key.charAt(0).toUpperCase() + key.slice(1);
        //             if (key === 'pincode') label = 'Pincode';
        //             $(`#${id}`).html(`<option value="">Select ${label}</option>`);
        //         }
        //         if (key === 'country') startReset = true; // Start resetting from state onwards
        //     });
        // }



        // Billing Country Change


        // $('#country_id').on('change', function() {

        //     var country_id = $(this).val();

        //     resetLowerSelects('');
        //     loadStates(country_id);

        // });



        // $('#country_id').on('change', function() {
        //     var country_id = $(this).val();
        //     resetLowerSelects(''); // billing has no prefix

        //     if (country_id) {
        //         $('#state_id').html('<option value="">Loading states...</option>');
        //         $.ajax({
        //             url: '{{ route("get.states", "") }}/' + country_id,
        //             type: 'GET',
        //             dataType: 'json',
        //             success: function(data) {
        //                 var options = '<option value="">Select State</option>';
        //                 $.each(data, function(i, state) {
        //                     options +=
        //                         `<option value="${state.id}">${state.state_name}</option>`;
        //                 });
        //                 $('#state_id').html(options);
        //             }
        //         });
        //     } else {
        //         $('#state_id').html('<option value="">Select State</option>');
        //     }
        // });

        // Billing State → District
        $('#state_id').on('change', function() {
            var state_id = $(this).val();
            if (state_id) {
                $('#district_id').html('<option value="">Loading districts...</option>');
                $.ajax({
                    url: '{{ route("get.districts", "") }}/' + state_id,
                    success: function(data) {
                        var options = '<option value="">Select District</option>';
                        $.each(data, function(i, d) {
                            options +=
                                `<option value="${d.id}">${d.district_name}</option>`;
                        });
                        $('#district_id').html(options);
                    }
                });
            } else {
                $('#district_id').html('<option value="">Select District</option>');
            }
            $('#city_id, #pincode_id').html(
                '<option value="">Select City</option><option value="">Select Pincode</option>'
                .split(''));
        });

        // Billing District → City
        $('#district_id').on('change', function() {
            var district_id = $(this).val();
            if (district_id) {
                $('#city_id').html('<option value="">Loading cities...</option>');
                $.ajax({
                    url: '{{ route("get.cities", "") }}/' + district_id,
                    success: function(data) {
                        var options = '<option value="">Select City</option>';
                        $.each(data, function(i, c) {
                            options +=
                                `<option value="${c.id}">${c.city_name}</option>`;
                        });
                        $('#city_id').html(options);
                    }
                });
            } else {
                $('#city_id').html('<option value="">Select City</option>');
            }
            $('#pincode_id').html('<option value="">Select Pincode</option>');
        });

        // Billing City → Pincode
        $('#city_id').on('change', function() {
            var city_id = $(this).val();
            if (city_id) {
                $('#pincode_id').html('<option value="">Loading pincodes...</option>');
                $.ajax({
                    url: '{{ route("get.pincodes", "") }}/' + city_id,
                    success: function(data) {
                        var options = '<option value="">Select Pincode</option>';
                        $.each(data, function(i, p) {
                            options +=
                                `<option value="${p.id}">${p.pincode}</option>`;
                        });
                        $('#pincode_id').html(options);
                    }
                });
            } else {
                $('#pincode_id').html('<option value="">Select Pincode</option>');
            }
        });

        let maxMobiles = 5;

        // ADD MOBILE
        $(document).on('click', '.add-mobile', function() {

            let total = $('.mobile-row').length;

            if (total >= maxMobiles) {
                alert('Maximum 5 mobile numbers allowed');
                return;
            }

            let nextNumber = total + 1;

            let html = `
    <div class="mobile-row col-md-6 mb-2 d-flex align-items-center">

        <input type="text"
               name="mobile_numbers[]"
               class="form-control mr-2"
               placeholder="Mobile Number ${nextNumber}"
               maxlength="10"
               pattern="[6-9]{1}[0-9]{9}"
               required>

        <button type="button" class="btn btn-danger btn-sm remove-mobile">
            <i class="fa fa-minus"></i>
        </button>

    </div>
    `;

            $('#mobile_numbers_wrapper').append(html);
        });


        // REMOVE MOBILE
        $(document).on('click', '.remove-mobile', function() {

            if ($('.mobile-row').length <= 1) {
                alert('At least one mobile number is required');
                return;
            }

            $(this).closest('.mobile-row').remove();

            // Reorder placeholders
            $('.mobile-row input').each(function(index) {
                $(this).attr('placeholder', 'Mobile Number ' + (index + 1));
            });

        });

        function previewImage(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            if (!input || !preview) return;

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.src = "{{ asset('assets/img/placeholder.jpg') }}";
                }
            });
        }

        // Dono photos ke liye preview enable karo
        previewImage('shop_photo', 'shop_photo_preview');
        previewImage('gst_attachment', 'gst_attachment_preview');
        previewImage('pan_attachment', 'pan_attachment_preview');
        previewImage('bank_proof', 'bank_proof_preview');

        $('.fileinput').fileinput()
        // Auto-trigger on page load for edit form (billing part already there, add shipping if needed)
        function showError(input, message) {
            $(input).addClass('is-invalid');
            if ($(input).next('.invalid-feedback').length == 0) {
                $(input).after('<div class="invalid-feedback">' + message + '</div>');
            }
        }

        function clearError(input) {
            $(input).removeClass('is-invalid');
            $(input).next('.invalid-feedback').remove();
        }

        $('#retailerForm').on('submit', function(e) {

            const acct = $('#bank_account_number').val().trim();
            const confirm = $('#bank_account_number_confirm').val().trim();
            const msg = $('#bank-verification-message');

            msg.removeClass('text-success text-danger').html('');

            // Case 1: Account filled but confirm empty
            if (acct && !confirm) {

                e.preventDefault();

                msg.html('✗ Confirmation is required').addClass('text-danger');
                $('#bank_account_number_confirm').focus();

                $('html, body').animate({
                    scrollTop: $('#bank_account_number').offset().top - 120
                }, 300);

                return false;
            }

            // Case 2: mismatch
            if (acct && confirm && acct !== confirm) {

                e.preventDefault();

                msg.html('✗ Bank account numbers do not match').addClass('text-danger');
                $('#bank_account_number_confirm').focus();

                $('html, body').animate({
                    scrollTop: $('#bank_account_number').offset().top - 120
                }, 300);

                return false;
            }

        });

        $('#retailerForm').on('submit', function(e) {



            let valid = true;
            // Mobile validation
            $('input[name="mobile_numbers[]"]').each(function() {

                let mobile = $(this).val();
                let mobileRegex = /^[6-9]\d{9}$/;

                if (!mobileRegex.test(mobile)) {
                    showError(this, 'Enter valid 10 digit mobile number');
                    valid = false;
                } else {
                    clearError(this);
                }

            });

            // Email validation
            let email = $('#email').val();
            if (email) {
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailRegex.test(email)) {
                    showError('#email', 'Enter valid email address');
                    valid = false;
                } else {
                    clearError('#email');
                }
            }

            // PAN validation
            let pan = $('input[name="pan_number"]').val();
            if (pan) {
                let panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;

                if (!panRegex.test(pan)) {
                    showError('input[name="pan_number"]', 'Enter valid PAN number');
                    valid = false;
                } else {
                    clearError('input[name="pan_number"]');
                }
            }

            // GST validation
            let gst = $('input[name="gst_number"]').val();
            if (gst) {
                let gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;

                if (!gstRegex.test(gst)) {
                    showError('input[name="gst_number"]', 'Enter valid GST number');
                    valid = false;
                } else {
                    clearError('input[name="gst_number"]');
                }
            }


            if (!valid) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
        checkBankMatch();


        // load states automatically if country already selected
        // Only auto load states when creating new record
        // @if(!isset($customer) || !$customer->exists)
        //     if (!$('#country_id').val()) {
        //         $('#country_id').val(1).trigger('change');
        //     } else {
        //         $('#country_id').trigger('change');
        //     }
        // @else
        //     // Edit mode — always trigger chain if something is selected
        //     if ($('#country_id').val()) {
        //         $('#country_id').trigger('change');
        //     }
        // @endif


    });
    </script>
</x-app-layout>