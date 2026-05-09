<x-app-layout>
    <style>
    /* ============== IMAGE PREVIEW STYLE FOR BOTH UPLOAD BOXES ============== */
    /* Default: Warning icon hide, badge gray */

    /* .clickable-upload-text {
    color: #007bff;             
    cursor: pointer;
    text-decoration: underline;   
    font-weight: 500;
}

.clickable-upload-text:hover {
    color: #0056b3;
    text-decoration: underline;
} */


.documents-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.preview-item {
    position: relative;
    width: 140px;
    height: 140px;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    background: #f8f9fa;
}

.preview-item img,
.preview-item .pdf-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-item .pdf-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #6c757d;
    background: #e9ecef;
}

.remove-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    border: none;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.file-name {
    font-size: 0.75rem;
    text-align: center;
    margin-top: 4px;
    color: #555;
    word-break: break-all;
}


    /* -------- */
     #business_start_date { z-index: 20 !important;  }

    #selection3{
      z-index: 10 !important; 
    }
    .accordion-header .material-icons.fs-4.text-danger {
        display: none !important;
    }

    .accordion-header .badge {
        background: transparent !important;
        color: #000 !important;
        border: none !important;
        box-shadow: none !important;
        font-weight: 600;
    }

    .accordion-item .badge {
        background: transparent !important;
        color: #000 !important;
    }

    /* Jab section invalid ho tab warning dikhao */
    .accordion-item.invalid-section .material-icons.fs-4.text-danger {
        display: inline-block !important;
    }

    /* .accordion-item.invalid-section .badge {
        background-color: #dc3545 !important;
        color: #fff !important;
    } */

    /* Jab valid ho tab green */
    /* .accordion-item.valid-section .badge {
        background-color: #28a745 !important;
        color: #fff !important;
    } */

    /* .custom-preview-container {
        position: relative;
        width: 100%;
        min-height: 180px;
        border: 2px dashed #bfbfbf;
        border-radius: 12px;
        overflow: hidden;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .custom-preview-container:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }

    .custom-preview-container input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .custom-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #777;
        pointer-events: none;
        z-index: 1;
    }

    .custom-placeholder i {
        font-size: 60px;
        margin-bottom: 10px;
        color: #aaa;
    }

    .custom-preview-img {
        width: 100%;
        height: 140px;
        object-fit: contain;
        background: #fff;
        display: none;
    }

    .custom-file-name {
        height: 40px;
        padding: 8px;
        text-align: center;
        background: #f1f1f1;
        border-top: 1px solid #ddd;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: none;
    }

    /* Multiple files ke liye list style (KYC Documents) */
    .custom-files-list {
        padding: 10px;
        max-height: 300px;
        overflow-y: auto;
    }

    .custom-files-list>div {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .custom-files-list img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .custom-file-upload-box {
        border: 3px dashed #ddd;
        border-radius: 15px;
        color: black padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .custom-file-upload-box:hover {
        border-color: gray;

    }

    .custom-file-upload-box.dragover {
        border-color: #007bff;
        background-color: #e3f2fd;
    }

    .selected-files-list {
        text-align: left;
        max-height: 150px;
        overflow-y: auto;
    }

    .selected-files-list p {
        margin: 8px 0;
        padding: 10px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-weight: 500;
        color: #000;
    }

    .text_name {
        color: black;
        background-color: white
    } */

    .accordion-item {
        border: 1px solid #ddd;
        margin-bottom: 10px;
        border-radius: 6px;
        background: #fff;
    }

    /* hide checkbox */
    .accordion-toggle {
        display: none;
    }

    .accordion-header {
        padding: 14px 18px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }

    /* arrow */
    .accordion-header .arrow {
        width: 10px;
        height: 10px;
        border-right: 2px solid #333;
        border-bottom: 2px solid #333;
        transform: rotate(45deg);
        transition: transform 0.3s ease;
    }

    /* body */
    .accordion-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
        padding: 0 18px;
    }

    /* when checked */
    .accordion-toggle:checked+.accordion-header .arrow {
        transform: rotate(-135deg);
    }

    .accordion-toggle:checked+.accordion-header+.accordion-body {
        max-height: 2000px;
        /* large enough */
        padding: 18px;
    }

    /* Static Light Gray Color for Count Badges */
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                {{-- ================= HEADER ================= --}}
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">store</i>
                    </div>

                    <h4 class="card-title">
                        {!! trans('panel.master_distributor.create_title') !!}
                        <span class="pull-right">
                            <a href="{{ route('master-distributors.index') }}" class="btn btn-just-icon btn-theme">
                                <i class="material-icons">next_plan</i>
                            </a>
                        </span>
                    </h4>
                </div>

                <div class="card-body">

                    {{-- ================= ERRORS ================= --}}
                    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif 
                    <!-- @if($errors->any())
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert">
                            <i class="material-icons">close</i>
                        </button>
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif -->

                    {!! Form::model($distributor,[
                    'route' => $distributor->exists
                    ? ['master-distributors.update',$distributor->id]
                    : 'master-distributors.store',
                    'method' => $distributor->exists ? 'PUT' : 'POST',
                    'files' => true,
                    'id' => 'storeMasterDistributor'
                    ]) !!}

                    <input type="hidden" name="id" value="{{ $distributor->id }}">

                    {{-- ================= IMAGES ================= --}}
                    <div class="first-box">
                        <div class="row">

                            {{-- SHOP IMAGE --}}
                            <div class="col-md-3 ml-auto mr-auto">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail">
                                        <img src="{{ $distributor->shop_image ? asset('storage/' . $distributor->shop_image) : asset('assets/img/placeholder.jpg') }}"
                                            class="imagepreview1">
                                        <div class="selectThumbnail">
                                            <span class="btn btn-just-icon btn-round btn-file">
                                                <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                                                <span class="fileinput-exists">Change</span>
                                                <input type="file" name="shop_image" class="getimage1" accept="image/*">
                                            </span>
                                            <br>
                                            <a href="#" class="btn btn-danger btn-round fileinput-exists"
                                                data-dismiss="fileinput">
                                                <i class="fa fa-times"></i> Remove
                                            </a>
                                        </div>
                                    </div>
                                    <label class="bmd-label-floating">
                                        {!! trans('panel.master_distributor.fields.shop_image') !!}
                                    </label>
                                </div>
                            </div>

                            {{-- PROFILE IMAGE --}}
                            <div class="col-md-3 ml-auto mr-auto">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail">


                                        <img src="{{ $distributor->profile_image ? asset('storage/' . $distributor->profile_image) : asset('assets/img/placeholder.jpg') }}"
                                            class="imagepreview2">
                                        <div class="selectThumbnail">
                                            <span class="btn btn-just-icon btn-round btn-file">
                                                <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                                                <span class="fileinput-exists">Change</span>
                                                <input type="file" name="profile_image" class="getimage2"
                                                    accept="image/*">
                                            </span>
                                            <br>
                                            <a href="#" class="btn btn-danger btn-round fileinput-exists"
                                                data-dismiss="fileinput">
                                                <i class="fa fa-times"></i> Remove
                                            </a>
                                        </div>
                                    </div>
                                    <label class="bmd-label-floating">
                                        {!! trans('panel.master_distributor.fields.profile_image') !!}
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>


                    {{-- ================= ACCORDION WRAPPER ================= --}}
                    <div class="accordion">

                        {{-- ================= BASIC INFO ================= --}}
                        <div class="accordion-item">

                            {{-- Toggle --}}
                            <input type="checkbox" id="basicInfo" class="accordion-toggle"
                                {{ old('open_section', 'basic') == 'basic' ? 'checked' : '' }}>

                            {{-- Header with Total Filled Count & Status Icon --}}
                            <label for="basicInfo"
                                class="accordion-header d-flex align-items-center justify-content-between w-100">
                                <!-- Left: Title only -->
                                <div class="d-flex align-items-center">
                                    <span class="me-3 fw-600">Basic Distributor Information</span>
                                </div>

                                <!-- Right: Count Badge + Status Icon + Arrow with 10px gaps -->
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <span id="basic-info-status" class="material-icons fs-4 text-danger">
                                        warning
                                    </span>
                                    <span id="basic-info-counter" class="badge bg-secondary text-white fs-6 px-3 py-2">
                                        0 / 7
                                    </span>

                                    <span class="arrow"></span>
                                </div>
                            </label>

                            {{-- Body --}}
                            <div class="accordion-body">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Distributor Legal Name <span class="text-danger">*</span>
                                        </label>
                                        {!! Form::text('legal_name', old('legal_name', $distributor->legal_name ?? ''),
                                        ['class'=>'form-control fillable-field mandatory-field']) !!}
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">Trade / Business Name</label>
                                        {!! Form::text('trade_name', old('trade_name', $distributor->trade_name ?? ''),
                                        ['class'=>'form-control fillable-field']) !!}
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">Distributor Code <span
                                                class="text-danger">*</span></label>
                                        {!! Form::text('distributor_code', old('distributor_code',
                                        $distributor->distributor_code ?? ''), ['class'=>'form-control fillable-field
                                        mandatory-field']) !!}
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Business Status <span class="text-danger">*</span>
                                        </label>
                                        <div class="form-group has-default bmd-form-group">
                                            {!! Form::select(
                                            'business_status',
                                            ['' => 'Select Status', 'Active'=>'Active', 'Inactive'=>'Inactive', 'On
                                            Hold'=>'On Hold'],
                                            old('business_status', $distributor->business_status ?? null),
                                            ['class' => 'form-control select2 fillable-field mandatory-field'
                                            
                                            ]
                                            ) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Business Start Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="form-group has-default bmd-form-group" id="selection3">

                                            <input type="text" name="business_start_date"
                                                class="form-control datepicker fillable-field mandatory-field"
                                                value="{{ old('business_start_date', $distributor->business_start_date ?? '') }}"
                                                id="business_start_date"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- ================= CONTACT INFO ================= --}}
                        <div class="accordion-item">

                            {{-- Toggle --}}
                            <input type="checkbox" id="contactInfo" class="accordion-toggle"
                                {{ old('open_section') == 'contact' ? 'checked' : '' }}>

                            {{-- Header with Counter & Material Icon --}}
                            <label for="contactInfo"
                                class="accordion-header d-flex align-items-center justify-content-between w-100">
                                <!-- Left: Title only -->
                                <div class="d-flex align-items-center">
                                    <span class="me-3 fw-600">Contact & Communication</span>
                                </div>

                                <!-- Right: Count Badge + Status Icon + Arrow with 10px gaps -->
                                <div class="d-flex align-items-center" style="gap: 10px;">

                                    <span id="contact-info-status" class="material-icons fs-4 text-danger">
                                        warning
                                    </span>
                                    <span id="contact-info-counter"
                                        class="badge bg-secondary text-white fs-6 px-3 py-2">
                                        0 / 6
                                    </span>

                                    <span class="arrow"></span>
                                </div>
                            </label>

                            {{-- Body --}}
                            <div class="accordion-body">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Primary Contact Person <span class="text-danger">*</span>
                                        </label>
                                        {!! Form::text('contact_person', old('contact_person',
                                        $distributor->contact_person ?? ''), ['class'=>'form-control fillable-field
                                        mandatory-field']) !!}
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Primary Mobile <span class="text-danger">*</span>
                                        </label>
                                        {!! Form::text('mobile', old('mobile', $distributor->mobile ?? ''), [
                                            'class' => 'form-control fillable-field mandatory-field',
                                            'id' => 'mobile',
                                            'maxlength' => '10',
                                            'pattern' => '[0-9]{10}',
                                            'title' => 'Please enter exactly 10 digits (0-9)',
                                            'placeholder' => 'Enter 10-digit mobile number',
                                            'required' => true
                                        ]) !!}
                                        <div class="invalid-feedback">
                                            Mobile number must be exactly 10 digits (0–9 only).
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">Alternate Mobile</label>
                                        {!! Form::text('alternate_mobile', old('alternate_mobile', $distributor->alternate_mobile ?? ''), [
                                            'class' => 'form-control fillable-field',
                                            'id' => 'alternate_mobile',
                                            'maxlength' => '10',
                                            'pattern' => '[0-9]{10}',
                                            'title' => 'Enter exactly 10 digits or leave blank',
                                            'placeholder' => 'Optional – 10-digit number'
                                        ]) !!}
                                        <div class="invalid-feedback">
                                            Alternate mobile must be exactly 10 digits (if filled).
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Primary Email <span class="text-danger">*</span>
                                        </label>
                                        {!! Form::email('email', old('email', $distributor->email ?? ''),
                                        ['class'=>'form-control fillable-field mandatory-field']) !!}
                                    </div>

                                   
                                </div>

                            </div>
                        </div>
                        {{-- ================= ADDRESS & LOCATION ================= --}}
                        <div class="accordion-item">
                            <input type="checkbox" id="addressInfo" class="accordion-toggle"
                                {{ old('open_section') == 'address' ? 'checked' : '' }}>
                            <label for="addressInfo"
                                class="accordion-header d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <span class="me-3 fw-600">Address & Location Information</span>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <span id="address-info-status"
                                        class="material-icons fs-4 text-danger">warning</span>
                                    <span id="address-info-counter"
                                        class="badge bg-secondary text-white fs-6 px-3 py-2">
                                        0 / 12
                                    </span>
                                    <span class="arrow"></span>
                                </div>
                            </label>

                            <div class="accordion-body">
                                @php
                                $countries = \Cache::remember('active_countries_list', 1440, function () {
                                return \App\Models\Country::where('active', 'Y')
                                ->orderBy('country_name', 'asc')
                                ->get(['id', 'country_name']);
                                });
                                @endphp

                                {{-- ==================== BILLING ADDRESS ==================== --}}
                                <div class="card mt-3 billing-fields">
                                    <div
                                        class="card-header text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Billing Address</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="col-form-label">Address Line 1 <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="address1" class="form-control mandatory-field"
                                                    value="{{ old('address1', $distributor->billing_address ?? '') }}"
                                                    maxlength="200" required>
                                            </div>

                                            <!-- Country -->
                                            <div class="col-md-6 mb-3">
                                                <label class="col-form-label">Country <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2 country mandatory-field"
                                                    name="country_id" id="country_id">
                                                    <option value="">Select Country</option>
                                                    @foreach($countries as $country)
                                                    <option value="{{ $country->id }}"
    {{ old('country_id', $distributor->billing_country ?? '') == $country->id ? 'selected' : '' }}>
    {{ $country->country_name }}
</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- State -->
                                            <div class="col-md-6 mb-3">
                                                <label class="col-form-label">State <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2 mandatory-field" name="state_id"
                                                    id="state_id">
                                                    <option value="">Select State</option>
                                                    @if(old('country_id') || ($distributor->billing_country ?? null))
                                                    @php
                                                    $states = \App\Models\State::where('country_id', old('country_id',
                                                    $distributor->billing_country ?? ''))
                                                    ->where('active', 'Y')
                                                    ->orderBy('state_name')
                                                    ->get();
                                                    @endphp
                                                    @foreach($states as $state)
                                                    <option value="{{ $state->id }}"
{{ old('state_id', $distributor->billing_state ?? '') == $state->id ? 'selected' : '' }}                                                        {{ $state->state_name }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <!-- District -->
                                            <div class="col-md-6 mb-3">
                                                <label class="col-form-label">District <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2 mandatory-field" name="district_id"
                                                    id="district_id">
                                                    <option value="">Select District</option>
                                                    @if(old('state_id') || ($distributor->state_id ?? null))
                                                    @php
                                                    $districts = \App\Models\District::where('state_id', old('state_id',
                                                    $distributor->state_id ?? ''))
                                                    ->where('active', 'Y')
                                                    ->orderBy('district_name')
                                                    ->get();
                                                    @endphp
                                                    @foreach($districts as $district)
                                                    <option value="{{ $district->id }}"
{{ old('district_id', $distributor->billing_district ?? '') == $district->id ? 'selected' : '' }}                                                        {{ $district->district_name }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <!-- City -->
                                            <div class="col-md-6 mb-3">
                                                <label class="col-form-label">City <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2 mandatory-field" name="city_id"
                                                    id="city_id">
                                                    <option value="">Select City</option>
                                                    @if(old('district_id') || ($distributor->district_id ?? null))
                                                    @php
                                                    $cities = \App\Models\City::where('district_id', old('district_id',
                                                    $distributor->district_id ?? ''))
                                                    ->where('active', 'Y')
                                                    ->orderBy('city_name')
                                                    ->get();
                                                    @endphp
                                                    @foreach($cities as $city)
                                                    <option value="{{ $city->id }}"
{{ old('city_id', $distributor->billing_city ?? '') == $city->id ? 'selected' : '' }}                                                        {{ $city->city_name }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <!-- Pincode -->
                                            <div class="col-md-6 mb-3">
                                                <label class="col-form-label">Pincode <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2 mandatory-field" name="pincode_id"
                                                    id="pincode_id">
                                                    <option value="">Select Pincode</option>
                                                    @if(old('city_id') || ($distributor->city_id ?? null))
                                                    @php
                                                    $pincodes = \App\Models\Pincode::where('city_id', old('city_id',
                                                    $distributor->city_id ?? ''))
                                                    ->where('active', 'Y')
                                                    ->orderBy('pincode')
                                                    ->get();
                                                    @endphp
                                                    @foreach($pincodes as $pincode)
                                                    <option value="{{ $pincode->id }}"
{{ old('pincode_id', $distributor->billing_pincode ?? '') == $pincode->id ? 'selected' : '' }}                                                        {{ $pincode->pincode }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">Shipping Address</h5>
                                    </div>

                                    <div class="card-body">
                                        <div class="col-md-12 mb-3">
                                            <label class="col-form-label">
                                                Full Shipping Address <span class="text-danger">*</span>
                                            </label>

                                            <textarea 
                                                name="shipping_address"
                                                class="form-control mandatory-field"
                                                rows="3"
                                                placeholder="Enter full shipping address"
                                            >{{ old('shipping_address', $distributor->shipping_address ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                {{-- Hidden fields jo controller mein use ho rahe hain --}}
                                <input type="hidden" name="billing_country_id" id="billing_country_id"
                                    value="{{ old('billing_country_id', $distributor->country_id ?? '') }}">

                                <input type="hidden" name="billing_state_id" id="billing_state_id"
                                    value="{{ old('billing_state_id', $distributor->state_id ?? '') }}">

                                <input type="hidden" name="billing_district_id" id="billing_district_id"
                                    value="{{ old('billing_district_id', $distributor->district_id ?? '') }}">

                                <input type="hidden" name="billing_city_id" id="billing_city_id"
                                    value="{{ old('billing_city_id', $distributor->city_id ?? '') }}">

                                <input type="hidden" name="billing_pincode_id" id="billing_pincode_id"
                                    value="{{ old('billing_pincode_id', $distributor->pincode_id ?? '') }}">
                            </div>
                        </div>
                        {{-- ================= BUSINESS & OPERATIONAL INFO ================= --}}
                        <div class="accordion-item">
                            {{-- Toggle --}}
                            <input type="checkbox" id="businessInfo" class="accordion-toggle"
                                {{ old('open_section') == 'business' ? 'checked' : '' }}>

                            {{-- Header --}}
                            <label for="businessInfo"
                                class="accordion-header d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <span class="me-3 fw-600">Business & Operational Information</span>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <span id="business-info-status" class="material-icons fs-4 text-danger">
                                        warning
                                    </span>
                                    <span id="business-info-counter"
                                        class="badge bg-secondary text-white fs-6 px-3 py-2">
                                        0 / 5
                                    </span>

                                    <span class="arrow"></span>
                                </div>
                            </label>

                            {{-- Body --}}
                            <div class="accordion-body">
                                <div class="row">
                                    
                                    <!-- <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="beat_route">Beat Route <span class="text-danger">*</span></label>
                                            <select name="beat_route" id="beat_route" class="form-control select2" required>
                                                <option value="">Select Beat</option>
                                                @foreach($beats as $id => $beat_name)
                                                    <option value="{{ $beat_name }}"
                                                        {{ old('beat_route', $distributor->beat_route) == $beat_name ? 'selected' : '' }}>
                                                        {{ $beat_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Select the primary beat/route for this distributor</small>
                                        </div>
                                    </div> -->

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="beat_id">Beat Route <span class="text-danger">*</span></label>

                                            <select name="beat_id" id="beat_id" class="form-control select2" required>
                                                <option value="">Select Beat</option>
                                                @foreach($beats as $id => $beat_name)
                                                    <option value="{{ $id }}"
                                                        data-route="{{ $beat_name }}"
                                                        {{ old('beat_id', $distributor->beat_id) == $id ? 'selected' : '' }}>
                                                        {{ $beat_name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            {{-- Hidden field for beat route --}}
                                            <input type="hidden" name="beat_route" id="beat_route"
                                                value="{{ old('beat_route', $distributor->beat_route) }}">

                                            <small class="text-muted">
                                                Select the primary beat/route for this distributor
                                            </small>
                                        </div>
                                    </div>

                                    
                                </div>
                            </div>
                        </div>

                        {{-- ================= COMPLIANCE & LEGAL (KYC) ================= --}}
                        <div class="accordion-item">

                            {{-- Toggle --}}
                            <input type="checkbox" id="kycInfo" class="accordion-toggle"
                                {{ old('open_section') == 'kyc' ? 'checked' : '' }}>

                            {{-- Header with Total Filled Count & Status Icon --}}
                            <label for="kycInfo"
                                class="accordion-header d-flex align-items-center justify-content-between w-100">
                                <!-- Left: Title only -->
                                <div class="d-flex align-items-center">
                                    <span class="me-3 fw-600">Compliance & Legal</span>
                                </div>

                                <!-- Right: Count Badge + Status Icon + Arrow with 10px gaps -->
                                <div class="d-flex align-items-center" style="gap: 10px;">

                                    <span id="kyc-info-status" class="material-icons fs-4 text-danger">
                                        warning
                                    </span>
                                    <span id="kyc-info-counter" class="badge bg-secondary text-white fs-6 px-3 py-2">
                                        0 / 7
                                    </span>
                                    <span class="arrow"></span>
                                </div>
                            </label>

                            {{-- Body --}}
                            <div class="accordion-body">

                                <div class="row">
                                    

                                    

                                    <div class="col-md-6 mb-3">
                                        <label class=" col-form-label">Business Registration Type<span
                                                class="text-danger">*</span></label>
                                        <div class="form-group has-default bmd-form-group">

                                            {!! Form::select(
                                            'registration_type',
                                            ['Proprietorship' =>'Proprietorship',
                                            'Partnership'=>'Partnership', 'Pvt Ltd'=>'Pvt Ltd', 'LLP'=>'LLP'],
                                            null,
                                            ['class'=>'form-control select2
                                            mandatory-field','placeholder'=>'Registration Type ']
                                            ) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input_section">
                                            


                                            <!-- In your form – around the documents field -->

                                            <div class="form-group">
                                                <label for="documents">Upload Additional Documents (multiple allowed)</label>
                                                
                                                <div class="custom-file-upload" style="border: 2px dashed #ccc; padding: 5px; text-align: center; margin-top: 5px; border-radius: 5px">
                                                    <input type="file" 
                                                        name="documents[]" 
                                                        id="documents" 
                                                        multiple 
                                                        accept="image/*,.pdf" 
                                                        style="display: none;">

                                                    <div class="upload-instruction">
                                                        <!-- <p>Click the line below to select files</p> -->
                                                        <small class="form-text text-muted" 
                                                            id="trigger-upload" 
                                                            style="cursor: pointer; color: #007bff; text-decoration: underline;">
                                                            Allowed: jpg, jpeg, png, pdf | Max 5 files | Total size ≤ 5MB
                                                        </small>
                                                    </div>
                                                </div>

                                                
                                            </div>

                                            <!-- Existing Documents -->
                                            @if($distributor->exists && $distributor->documents)
                                            <div class="mt-2 p-4 bg-light border rounded">
                                                <p class="font-weight-bold text-primary mb-3">Existing Documents:</p>

                                                @foreach(json_decode($distributor->documents, true) as $doc)
                                                <a href="{{ asset('storage/' . $doc) }}" target="_blank"
                                                    class="btn btn-sm btn-info">
                                                    <!-- {{ basename($doc) }} -->
                                                    <i class="material-icons" style="font-size: 16px;">visibility</i>
                                                    View Current Document
                                                </a>
                                                @endforeach

                                            </div>
                                            @endif

                                            @error('documents')
                                            <p class="text-danger mt-3">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="input_section">
                                            <div class="form-group">
                                                <label for="mou_file">Upload MOU (Memorandum of Understanding)</label>
            
                                                    <div class="custom-file-upload">
                                                        <input type="file" 
                                                            name="mou_file" 
                                                            id="mou_file" 
                                                            accept=".pdf,.jpg,.jpeg,.png" 
                                                            style="display: none;">

                                                        <div class="upload-instruction">
                                                            <!-- <p>Click below to upload MOU (single file only)</p> -->
                                                            <small class="form-text text-muted" 
                                                                id="trigger-mou-upload" 
                                                                style="border: 2px dashed #ccc; padding: 5px; text-align: center; border-radius: 5px;">
                                                                Allowed: PDF, JPG, JPEG, PNG | Max size 5MB | Single file only
                                                            </small>
                                                        </div>
                                                    </div>
                                                <!-- Preview area -->
                                                <div id="mou-preview" class="mt-3" style="min-height: 120px;"></div>
                                            </div>

                                            <!-- Existing MOU (edit mode ke liye) -->
                                            @if($distributor->exists && $distributor->mou_file)
                                            <div class="mt-2">
                                                <p class="text-muted">Current MOU:</p>
                                                <a href="{{ asset('storage/' . $distributor->mou_file) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="material-icons">visibility</i> View Current MOU
                                                </a>
                                            </div>
                                            @endif

                                            @error('mou_file')
                                            <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>                                                            
                                <div id="documents-preview" class="documents-preview mt-3 ml-1 row"></div>
                            </div>
                        </div>                        
                        {{-- ================= SALES & PERFORMANCE INFO ================= --}}
                        <div class="accordion-item">
                            {{-- Toggle --}}
                            <input type="checkbox" id="salesInfo" class="accordion-toggle"
                                {{ old('open_section') == 'sales' ? 'checked' : '' }}>

                            {{-- Header with Count Badge + Status Icon + Arrow --}}
                            <label for="salesInfo"
                                class="accordion-header d-flex align-items-center justify-content-between w-100">
                                <!-- Left: Title only -->
                                <div class="d-flex align-items-center">
                                    <span class="me-3 fw-600">Sales & Performance Information</span>
                                </div>

                                <!-- Right: Count Badge + Status Icon + Arrow with 10px gaps -->
                                <div class="d-flex align-items-center" style="gap: 10px;">

                                    <span id="sales-info-status" class="material-icons fs-4 text-danger">
                                        warning
                                    </span>
                                    <span id="sales-info-counter" class="badge bg-secondary text-white fs-6 px-3 py-2">
                                        0 / 7
                                    </span>
                                    <span class="arrow"></span>
                                </div>
                            </label>

                            {{-- Body --}}
                            <div class="accordion-body">
                                <div class="row">

                                    {{-- Assigned Sales Executive (Mandatory - Multiple) --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Assigned Sales Executive <span class="text-danger">*</span>
                                        </label>
                                        <div class="form-group has-default bmd-form-group">
                                            {!! Form::select(
                                            'sales_executive_id[]',
                                            $users ?? [] ,
                                            old('sales_executive_id', $distributor->sales_executive_ids),
                                            [
                                            'class' => 'form-control select2 fillable-field mandatory-field',
                                            'multiple' => 'multiple',

                                            ]
                                            ) !!}
                                        </div>
                                    </div>

                                    {{-- Assigned Supervisor (Mandatory) --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Assigned Supervisor / ASM / RSM <span class="text-danger">*</span>
                                        </label>
                                        <div class="form-group has-default bmd-form-group">
                                            {!! Form::select(
    'supervisor_id',
    $users,
    $distributor->supervisor_id,
    [
        'class' => 'form-control select2 fillable-field mandatory-field',
        'id' => 'supervisor_id'
    ]
) !!}
                                        </div>
                                    </div>

                                    {{-- Customer Segment (Mandatory) --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="col-form-label">
                                            Customer Segment <span class="text-danger">*</span>
                                        </label>
                                        <div class="form-group has-default bmd-form-group">
                                            {!! Form::select(
                                            'customer_segment',
                                            ['' => 'Select Segment'] + [
                                            'AGRI' => 'AGRI',
                                            'DOMESTIC' => 'DOMESTIC',
                                          
                                            ],
                                            old('customer_segment', $distributor->customer_segment ?? null),
                                            ['class' => 'form-control select2 fillable-field mandatory-field']
                                            ) !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer pull-right">
                        <button class="btn btn-theme">Save</button>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {

        // Helper function to validate exactly 10 digits
    function validateMobile(input) {
        const value = input.value.trim();
        // Remove any previous custom error
        input.setCustomValidity('');

        if (value === '') {
            // For alternate mobile → allowed to be empty
            if (input.id === 'alternate_mobile') {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                return;
            }
            // For primary mobile → required
            input.setCustomValidity('Mobile number is required.');
            return;
        }

        // Must be exactly 10 digits
        if (!/^\d{10}$/.test(value)) {
            if (value.length !== 10) {
                input.setCustomValidity(`Must be exactly 10 digits (currently ${value.length}).`);
            } else {
                input.setCustomValidity('Only numbers 0-9 are allowed.');
            }
        } else {
            input.setCustomValidity(''); // valid
        }
    }

    const mobileInput = document.getElementById('mobile');
    const altMobileInput = document.getElementById('alternate_mobile');

    if (mobileInput) {
        // Real-time validation
        mobileInput.addEventListener('input', () => validateMobile(mobileInput));
        // Also validate on blur (when user leaves the field)
        mobileInput.addEventListener('blur', () => {
            mobileInput.reportValidity();
            validateMobile(mobileInput);
        });
    }

    if (altMobileInput) {
        altMobileInput.addEventListener('input', () => validateMobile(altMobileInput));
        altMobileInput.addEventListener('blur', () => {
            altMobileInput.reportValidity();
            validateMobile(altMobileInput);
        });
    }

    // Optional: Prevent non-numeric input right away
    [mobileInput, altMobileInput].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }
    });

            const fileInput = document.getElementById('documents');
    const previewContainer = document.getElementById('documents-preview');

    if (!fileInput || !previewContainer) return;

    const MAX_FILES = 5;
    const MAX_TOTAL_SIZE_BYTES = 5 * 1024 * 1024; // 5MB

    let allFiles = [];  // saari files yahan store honge

    function renderPreviews() {
        previewContainer.innerHTML = '';

        allFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'preview-item';
                item.dataset.index = index;

                let content = '';
                if (file.type.startsWith('image/')) {
                    content = `<img src="${e.target.result}" alt="Preview">`;
                } else {
                    content = `
                        <div class="pdf-placeholder">
                            PDF<br><small>${file.name.slice(0,15)}${file.name.length > 15 ? '...' : ''}</small>
                        </div>
                    `;
                }

                item.innerHTML = `
                    ${content}
                    <button type="button" class="remove-btn" data-index="${index}">×</button>
                    <div class="file-name">${file.name}</div>
                `;

                previewContainer.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    }

    function showError(message) {
        alert(message);
        // ya better UX ke liye: ek red message div bana sakte ho
    }

    function checkLimits(newFiles) {
        // 1. File count check
        if (allFiles.length + newFiles.length > MAX_FILES) {
            showError(`Maximum ${MAX_FILES} files allowed. You already have ${allFiles.length} file(s).`);
            return false;
        }

        // 2. Total size check
        let currentTotalSize = allFiles.reduce((sum, f) => sum + f.size, 0);
        let newFilesSize = newFiles.reduce((sum, f) => sum + f.size, 0);
        let wouldBeTotal = currentTotalSize + newFilesSize;

        if (wouldBeTotal > MAX_TOTAL_SIZE_BYTES) {
            showError(`Total size would exceed 5MB limit.\nCurrent: ${(currentTotalSize / 1024 / 1024).toFixed(2)}MB\nNew files: ${(newFilesSize / 1024 / 1024).toFixed(2)}MB`);
            return false;
        }

        return true;
    }

    fileInput.addEventListener('change', function(e) {
        const newFiles = Array.from(e.target.files || []);

        if (newFiles.length === 0) return;

        // Limits check
        if (!checkLimits(newFiles)) {
            e.target.value = ''; // selection clear kar do
            return;
        }

        // Add new files to collection
        allFiles = [...allFiles, ...newFiles];

        // Update actual <input> files (form submit ke liye zaroori)
        const dt = new DataTransfer();
        allFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;

        // Previews refresh
        renderPreviews();
    });

    // Remove file on click
    previewContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            const index = parseInt(e.target.dataset.index);
            allFiles.splice(index, 1);

            // Update input files
            const dt = new DataTransfer();
            allFiles.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;

            renderPreviews();
        }
    });

            // Prevent multiple attachments
            
            document.getElementById('trigger-upload').addEventListener('click', function(e) {
    e.preventDefault();           // unnecessary propagation rokne ke liye
    document.getElementById('documents').click();
});



const mouInput     = document.getElementById('mou_file');
    const mouPreview   = document.getElementById('mou-preview');
    const mouTrigger   = document.getElementById('trigger-mou-upload');

    if (!mouInput || !mouPreview || !mouTrigger) return;

    const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5MB

    // Click on instruction text → open file picker
    mouTrigger.addEventListener('click', () => {
        mouInput.click();
    });

    mouInput.addEventListener('change', function(e) {
        const file = this.files[0];
        if (!file) return;

        // Size check
        if (file.size > MAX_SIZE_BYTES) {
            alert(`File size exceeds 5MB limit.\nSelected file: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
            this.value = ''; // clear input
            mouPreview.innerHTML = '';
            return;
        }

        // Clear previous preview
        mouPreview.innerHTML = '';

        const reader = new FileReader();
        reader.onload = function(event) {
            let content = '';

            if (file.type.startsWith('image/')) {
                content = `<img src="${event.target.result}" alt="MOU Preview" style="max-width:100%; max-height:200px; object-fit:contain; border:1px solid #ddd; border-radius:6px;">`;
            } else {
                // PDF or others
                content = `
                    <div class="pdf-placeholder" style="width:140px; height:180px; margin:0 auto; background:#f8f9fa; border:1px solid #ddd; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                        <i class="material-icons" style="font-size:60px; color:#e74c3c;">picture_as_pdf</i>
                        <small style="margin-top:8px; text-align:center;">${file.name.slice(0,20)}${file.name.length > 20 ? '...' : ''}</small>
                    </div>
                `;
            }

            const item = document.createElement('div');
            item.style.position = 'relative';
            item.style.display = 'inline-block';

            item.innerHTML = `
                ${content}
                <button type="button" class="remove-btn" style="position:absolute; top:-8px; right:-8px; width:24px; height:24px; background:#dc3545; color:white; border:none; border-radius:50%; cursor:pointer; font-size:14px; line-height:1; box-shadow:0 2px 4px rgba(0,0,0,0.2);">×</button>
                <div class="file-name" style="font-size:0.8rem; text-align:center; margin-top:4px; color:#555; word-break:break-all;">${file.name}</div>
            `;

            mouPreview.appendChild(item);
        };

        reader.readAsDataURL(file);
    });

    // Remove button click
    mouPreview.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            mouInput.value = '';           // clear input
            mouPreview.innerHTML = '';     // remove preview
        }
    });


// -----------------------------------


// ── Cancelled Cheque Upload Logic ───────────────────────────────────────
   // ============================================================================
// CANCELLED CHEQUE UPLOAD – ISOLATED SCRIPT (double open fix)
// ============================================================================


        });
        </script>
        <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2();

            $('.getimage1, .getimage2').on('change', function() {
                const input = this;
                const imgClass = input.classList.contains('getimage1') ? '.imagepreview1' :
                    '.imagepreview2';
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(imgClass).attr('src', e.target.result);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });



            // Optional: Remove karne par placeholder wapas lao
            $('a[data-dismiss="fileinput"]').on('click', function() {
                $('.imagepreview1, .imagepreview2').attr('src', '{{ asset('assets/img/placeholder.jpg') }}');
            });


            $('#beat_id').on('change', function () {
    let routeName = $(this).find(':selected').data('route') || '';
    $('#beat_route').val(routeName);
});

// edit mode ke liye page load par bhi set
$('#beat_id').trigger('change');

            



            // ==================== ACCORDION FILLED COUNT & STATUS ICONS ====================
            const accordions = [{
                    toggleId: 'basicInfo',
                    counterId: 'basic-info-counter',
                    statusId: 'basic-info-status',
                    container: '#basicInfo ~ .accordion-body',
                    total: 6
                },
                {
                    toggleId: 'contactInfo',
                    counterId: 'contact-info-counter',
                    statusId: 'contact-info-status',
                    container: '#contactInfo ~ .accordion-body',
                    total: 6
                },

                {
                    toggleId: 'businessInfo',
                    counterId: 'business-info-counter',
                    statusId: 'business-info-status',
                    container: '#businessInfo ~ .accordion-body',
                    total: 5
                },
                {
                    toggleId: 'kycInfo',
                    counterId: 'kyc-info-counter',
                    statusId: 'kyc-info-status',
                    container: '#kycInfo ~ .accordion-body',
                    total: 4
                },
                {
                    toggleId: 'salesInfo',
                    counterId: 'sales-info-counter',
                    statusId: 'sales-info-status',
                    container: '#salesInfo ~ .accordion-body',
                    total: 7
                },

            ];


            function updateAccordion(config) {



                const container = document.querySelector(config.container);
                if (!container) return;

                const allFields = container.querySelectorAll(
                    'input:not([type="checkbox"]):not([type="file"]), select, textarea');
                const mandatoryFields = container.querySelectorAll(
                    '.mandatory-field, input[required], select[required]');

                let filledCount = 0;
                allFields.forEach(field => {
                    if (field.value && field.value.trim() !== '' && field.value !== null)
                        filledCount++;
                });

                // File fields count as filled if any file selected
                container.querySelectorAll('input[type="file"]').forEach(fileInput => {
                    if (fileInput.files.length > 0) filledCount++;
                });

                let allMandatoryFilled = true;
                mandatoryFields.forEach(field => {
                    let hasValue = (field.value && field.value.trim() !== '' && field
                        .value !== null);
                    if (field.type === 'file') hasValue = field.files.length > 0;
                    if (!hasValue) allMandatoryFilled = false;
                });

                $(`#${config.counterId}`).text(filledCount + ' / ' + config.total);

                const statusEl = $(`#${config.statusId}`);
                const counterEl = $(`#${config.counterId}`);

                if (allMandatoryFilled) {
                    statusEl.text('check_circle').removeClass('text-danger').addClass(
                        'text-success');
                    counterEl.removeClass('bg-danger bg-secondary').addClass('bg-success');
                } else {
                    statusEl.text('warning').removeClass('text-success').addClass('text-danger');
                    counterEl.removeClass('bg-success').addClass('bg-danger');
                }
            }

            accordions.forEach(config => {
                updateAccordion(config);
                const container = document.querySelector(config.container);
                if (container) {
                    container.querySelectorAll('input, select, textarea').forEach(el => {
                        el.addEventListener('input', () => updateAccordion(config));
                        el.addEventListener('change', () => updateAccordion(config));
                    });
                }
                $(`#${config.toggleId}`).on('change', () => setTimeout(() => updateAccordion(config),
                    200));
            });

            $('#country_id').on('change', function() {
                var country_id = $(this).val();
                $('#state_id, #district_id, #city_id, #pincode_id').html(
                    '<option value="">Select State</option><option value="">Select District</option><option value="">Select City</option><option value="">Select Pincode</option>'
                );
                if (country_id) {
                    $('#state_id').html('<option value="">Loading states...</option>');
                    $.ajax({
                        url: '{{ route("get.states", "") }}/' + country_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
    var options = '<option value="">Select State</option>';
    if (Array.isArray(data)) {
        data.forEach(function(item) {
            options += `<option value="${item.id}">${item.state_name}</option>`;
        });
    }
    $('#state_id').html(options).trigger('change');
}
                    });
                }
            });

            $('#state_id').on('change', function() {
                var state_id = $(this).val();
                $('#district_id, #city_id, #pincode_id').html(
                    '<option value="">Select District</option><option value="">Select City</option><option value="">Select Pincode</option>'
                );
                if (state_id) {
                    $('#district_id').html(
                        '<option value="">Loading districts...</option>');
                    $.ajax({
                        url: '{{ route("get.districts", "") }}/' + state_id,
                        success: function(data) {
    var options = '<option value="">Select District</option>';
    if (Array.isArray(data)) {
        data.forEach(function(item) {
            options += `<option value="${item.id}">${item.district_name}</option>`;
        });
    }
    $('#district_id').html(options).trigger('change');
}
                    });
                }
            });

            $('#district_id').on('change', function() {
                var district_id = $(this).val();
                $('#city_id, #pincode_id').html(
                    '<option value="">Select City</option><option value="">Select Pincode</option>'
                );
                if (district_id) {
                    $('#city_id').html('<option value="">Loading cities...</option>');
                    $.ajax({
                        url: '{{ route("get.cities", "") }}/' + district_id,
                        success: function(data) {
    var options = '<option value="">Select City</option>';
    if (Array.isArray(data)) {
        data.forEach(function(item) {
            options += `<option value="${item.id}">${item.city_name}</option>`;
        });
    }
    $('#city_id').html(options).trigger('change');
}
                    });
                }
            });

            $('#city_id').on('change', function() {
                var city_id = $(this).val();
                $('#pincode_id').html('<option value="">Select Pincode</option>');
                if (city_id) {
                    $('#pincode_id').html('<option value="">Loading pincodes...</option>');
                    $.ajax({
                        url: '{{ route("get.pincodes", "") }}/' + city_id,
                        success: function(data) {
    var options = '<option value="">Select Pincode</option>';
    if (Array.isArray(data)) {
        data.forEach(function(item) {
            options += `<option value="${item.id}">${item.pincode}</option>`;
        });
    }
    $('#pincode_id').html(options).trigger('change');
}
                    });
                }
            });

            // ==================== SHIPPING ADDRESS AJAX CHAINING ====================
 
         


   // EDIT MODE MEIN SELECTED VALUES DIKHANE KA SAFE FIX
setTimeout(function() {
    $('.select2').each(function() {
        const $this = $(this);
        const currentValue = $this.val();
        if (currentValue) {
            // Sirf value set karo, change event mat trigger karo
            $this.val(currentValue);
            // Select2 ko manually refresh karo (without triggering change)
            if ($this.data('select2')) {
                $this.trigger('change.select2');
            }
        }
    });
}, 1000);
            // ==================== ADDRESS COUNTER FOR ACCORDION HEADER (0/12 Logic) ====================
            function updateAddressAccordionCounter() {
                const billingFields = [
                    'input[name="address1"]',
                    '#country_id',
                    '#state_id',
                    '#district_id',
                    '#city_id',
                    '#pincode_id'
                ];

                let filled = 0;

                billingFields.forEach(selector => {
                    const $field = $(selector);
                    if ($field.length && $field.val() && $field.val().toString().trim() !== '') {
                        filled++;
                    }
                });

                $('#address-info-counter').text(filled + ' / 6');

                const $badge = $('#address-info-counter');
                const $icon = $('#address-info-status');

                if (filled === 6) {
                    $badge.removeClass('bg-secondary bg-danger').addClass('bg-success');
                    $icon.text('check_circle').removeClass('text-danger').addClass('text-success');
                } else {
                    $badge.removeClass('bg-success').addClass('bg-danger');
                    $icon.text('warning').removeClass('text-success').addClass('text-danger');
                }
            }
            

          
           $(document).on('change input',
                '.billing-fields input, .billing-fields select',
                updateAddressAccordionCounter
            );
          

            // Initial call
            updateAddressAccordionCounter()
            function loadStates(country_id, targetSelect, selectedStateId = null) {
        if (!country_id) return;
        $.ajax({
            url: '{{ route("get.states", "") }}/' + country_id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let options = '<option value="">Select State</option>';
                $.each(data, function(i, item) {
                    let selected = (selectedStateId && selectedStateId == item.id) ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.state_name}</option>`;
                });
                $(targetSelect).html(options).trigger('change.select2');
            }
        });
    }

    function loadDistricts(state_id, targetSelect, selectedDistrictId = null) {
        if (!state_id) return;
        $.ajax({
            url: '{{ route("get.districts", "") }}/' + state_id,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Select District</option>';
                $.each(data, function(i, item) {
                    let selected = (selectedDistrictId && selectedDistrictId == item.id) ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.district_name}</option>`;
                });
                $(targetSelect).html(options).trigger('change.select2');
            }
        });
    }

    function loadCities(district_id, targetSelect, selectedCityId = null) {
        if (!district_id) return;
        $.ajax({
            url: '{{ route("get.cities", "") }}/' + district_id,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Select City</option>';
                $.each(data, function(i, item) {
                    let selected = (selectedCityId && selectedCityId == item.id) ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.city_name}</option>`;
                });
                $(targetSelect).html(options).trigger('change.select2');
            }
        });
    }

    function loadPincodes(city_id, targetSelect, selectedPincodeId = null) {
        if (!city_id) return;
        $.ajax({
            url: '{{ route("get.pincodes", "") }}/' + city_id,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Select Pincode</option>';
                $.each(data, function(i, item) {
                    let selected = (selectedPincodeId && selectedPincodeId == item.id) ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.pincode}</option>`;
                });
                $(targetSelect).html(options).trigger('change.select2');
            }
        });
    }


           
            $('#country_id').on('change', function() {
                $('#billing_country_id').val($(this).val());
            });

            $('#state_id').on('change', function() {
                $('#billing_state_id').val($(this).val());
            });

            $('#district_id').on('change', function() {
                $('#billing_district_id').val($(this).val());
            });

            $('#city_id').on('change', function() {
                $('#billing_city_id').val($(this).val());
            });

            $('#pincode_id').on('change', function() {
                $('#billing_pincode_id').val($(this).val());
            });

            

            // Address Line 1 (manual text input) ko hidden field mein copy karo
            $('input[name="address1"]').on('input', function() {
                $('input[name="billing_address"]').val($(this).val());
            });



            // ==================== ON CHANGE HANDLERS (NORMAL USER INTERACTION) ====================
    $('#country_id').on('change', function() {
        loadStates($(this).val(), '#state_id');
        $('#district_id, #city_id, #pincode_id').html('<option value="">--</option>').trigger('change.select2');
    });

    $('#state_id').on('change', function() {
        loadDistricts($(this).val(), '#district_id');
        $('#city_id, #pincode_id').html('<option value="">--</option>').trigger('change.select2');
    });

    $('#district_id').on('change', function() {
        loadCities($(this).val(), '#city_id');
        $('#pincode_id').html('<option value="">--</option>').trigger('change.select2');
    });

    $('#city_id').on('change', function() {
        loadPincodes($(this).val(), '#pincode_id');
    });


    // ==================== EDIT MODE: LOAD EXISTING DATA WITHOUT TRIGGERING CHANGE ====================
@if($distributor->exists)
    @if($distributor->billing_country)
        loadStates({{ $distributor->billing_country }}, '#state_id', {{ $distributor->billing_state ?? 'null' }});

        @if($distributor->billing_state)
            loadDistricts({{ $distributor->billing_state }}, '#district_id', {{ $distributor->billing_district ?? 'null' }});

            @if($distributor->billing_district)
                loadCities({{ $distributor->billing_district }}, '#city_id', {{ $distributor->billing_city ?? 'null' }});

                @if($distributor->billing_city)
                    loadPincodes({{ $distributor->billing_city }}, '#pincode_id', {{ $distributor->billing_pincode ?? 'null' }});
                @endif
            @endif
        @endif
    @endif
@endif

    // ==================== TEXT FIELDS UPDATE (Country, State, etc. name fields) ====================
    function updateHiddenTextFields() {
        // Billing
        $('input[name="billing_country"]').val($('#country_id option:selected').text() || '');
        $('input[name="billing_state"]').val($('#state_id option:selected').text() || '');
        $('input[name="billing_district"]').val($('#district_id option:selected').text() || '');
        $('input[name="billing_city"]').val($('#city_id option:selected').text() || '');
        $('input[name="billing_pincode"]').val($('#pincode_id option:selected').text() || '');

    }

    // Run on change of any dropdown
    $(document).on('change', '#country_id, #state_id, #district_id, #city_id, #pincode_id, ' +
        '#shipping_country_id, #shipping_state_id, #shipping_district_id, #shipping_city_id, #shipping_pincode_id', updateHiddenTextFields);

    // Initial run after load
    setTimeout(updateHiddenTextFields, 1500);

            @if($errors->any())
            // List of accordions with their field names that can have errors
            const errorMapping = {
                'basicInfo': ['legal_name', 'trade_name', 'distributor_code', 'category', 'business_status',
                    'business_start_date'
                ],
                'contactInfo': ['contact_person', 'designation', 'mobile', 'alternate_mobile', 'email',
                    'secondary_email'
                ],
                'addressInfo': ['address1', 'country_id', 'state_id', 'district_id', 'city_id',
                    'pincode_id',
                ],
                'businessInfo': ['sales_zone', 'area_territory', 'beat_route', 'market_classification',
                    'competitor_brands'
                ],
                'kycInfo': [ 'registration_type', 'documents'],
                
                'salesInfo': ['monthly_sales', 'product_categories', 'secondary_sales_required',
                    'last_12_months_sales', 'sales_executive_id', 'supervisor_id', 'customer_segment'
                ],

            };

            const errorFields = @json(array_keys($errors->messages()));

            // Loop through each accordion
            Object.keys(errorMapping).forEach(accordionId => {
                const fields = errorMapping[accordionId];
                const hasError = fields.some(field => errorFields.includes(field));

                if (hasError) {
                    // 1. Open the accordion
                    $(`#${accordionId}`).prop('checked', true);

                    // 2. Show red warning icon
                    $(`#${accordionId}-status`)
                        .text('warning')
                        .removeClass('text-success')
                        .addClass('text-danger');

                    // 3. Make badge red
                    $(`#${accordionId}-counter`)
                        .removeClass('bg-success bg-secondary')
                        .addClass('bg-danger');
                }
            });
            @endif

        });        
        </script>
</x-app-layout>