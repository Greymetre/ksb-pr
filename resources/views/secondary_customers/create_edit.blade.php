<x-app-layout>

    <style>
        /* Custom styling for tabs with bottom indicator line */
        .custom-nav-tabs {
            border-bottom: 2px solid #eee;
            /* faint full bottom line */
            padding-left: 0;
            margin-bottom: 20px;
        }

        .custom-nav-tabs .nav-item {
            margin-bottom: 0;
        }

        .custom-nav-tabs .nav-link {
            border: none !important;
            border-bottom: 4px solid transparent !important;
            border-radius: 0;
            padding: 14px 20px;
            color: #666 !important;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-nav-tabs .nav-link i.material-icons {
            font-size: 22px;
            vertical-align: middle;
        }

        .custom-nav-tabs .nav-link:hover {
            color: #333 !important;
            border-bottom: 4px solid #ddd !important;
            background-color: transparent;
        }

        .custom-nav-tabs .nav-link.active {
            color: #333 !important;
            font-weight: 600;
            background-color: transparent !important;
            border-bottom: 4px solid #9c27b0 !important;
            /* Change this to your theme color */
            /* Common theme colors: 
           Bootstrap primary: #007bff
           Material Purple: #9c27b0
           Material Deep Purple: #673ab7
           Material Teal: #009688
        */
        }
    </style>

    <div class="row">
        <div class="col-md-12">
            <div class="card">

                {{-- ================= CARD HEADER ================= --}}
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">build_circle</i>
                    </div>
                    <h4 class="card-title">
                        {{ isset($customer) && $customer->exists ? 'Edit' : 'Add New' }} Secondary Customer
                        <span class="pull-right">
                            <a href="{{ route('secondary-customers.index') }}" class="btn btn-just-icon btn-theme">
                                <i class="material-icons">next_plan</i>
                            </a>
                        </span>
                    </h4>
                </div>

                {{-- ================= CUSTOM TABS WITH BOTTOM INDICATOR ================= --}}
                <div class="card-body pt-0 border-bottom">
                    <ul class="nav custom-nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#mechanic" role="tab">
                                <i class="material-icons">build</i> Mechanic
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#garage" role="tab">
                                <i class="material-icons">home_repair_service</i> Garage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#retailer" role="tab">
                                <i class="material-icons">storefront</i> Retailer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#workshop" role="tab">
                                <i class="material-icons">engineering</i> Workshop
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">

                    @if($errors->any())
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
                    @endif

                    {!! Form::model($customer ?? null, [
                    'route' => ($customer->exists ?? false)
                    ? ['secondary-customers.update', $customer]
                    : 'secondary-customers.store',
                    'method' => ($customer->exists ?? false) ? 'PUT' : 'POST',
                    'files' => true,
                    'id' => 'secondaryCustomerForm'
                    ]) !!}

                    <div class="tab-content mt-4">

                        {{-- ================= MECHANIC TAB ================= --}}
                        <div class="tab-pane active" id="mechanic" role="tabpanel">
                            {!! Form::hidden('type', 'MECHANIC') !!}

                            <div class="row">
                                <div class="col-md-4">
                                    <label>Type</label>
                                    <input type="text" class="form-control" value="MECHANIC" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label>Select Sub Type <span class="text-danger">*</span></label>
                                    {!! Form::select('sub_type', [
                                    '' => 'Select Sub Type',
                                    'Two-Wheeler Mechanic' => 'Two-Wheeler Mechanic',
                                    'Car / 4W Mechanic' => 'Car / 4W Mechanic',
                                    'HCV-LCV Mechanic' => 'HCV-LCV Mechanic',
                                    'Tractor / Agri Machine' => 'Tractor / Agri Machine',
                                    'Diesel/FIP Mechanic' => 'Diesel/FIP Mechanic'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label>Owner Name <span class="text-danger">*</span></label>
                                    {!! Form::text('owner_name', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                            </div>

                            @include('secondary_customers.partials.common_fields')
                        </div>

                        {{-- ================= GARAGE TAB ================= --}}
                        <div class="tab-pane" id="garage" role="tabpanel">
                            {!! Form::hidden('type', 'GARAGE') !!}

                            <div class="row">
                                <div class="col-md-4">
                                    <label>Type</label>
                                    <input type="text" class="form-control" value="GARAGE" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label>Select Sub Type <span class="text-danger">*</span></label>
                                    {!! Form::select('sub_type', [
                                    '' => 'Select Sub Type',
                                    'ROADSIDE GARAGE' => 'ROADSIDE GARAGE',
                                    'MULTI EMPLOYEE GARAGE' => 'MULTI EMPLOYEE GARAGE',
                                    'ONE-MAN GARAGE' => 'ONE-MAN GARAGE'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label>Owner Name <span class="text-danger">*</span></label>
                                    {!! Form::text('owner_name', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                            </div>

                            @include('secondary_customers.partials.common_fields')
                        </div>

                        {{-- ================= RETAILER TAB ================= --}}
                        <div class="tab-pane" id="retailer" role="tabpanel">
                            {!! Form::hidden('type', 'RETAILER') !!}

                            <div class="row">
                                <div class="col-md-4">
                                    <label>Type</label>
                                    <input type="text" class="form-control" value="RETAILER" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label>Select Sub Type <span class="text-danger">*</span></label>
                                    {!! Form::select('sub_type', [
                                    '' => 'Select Sub Type',
                                    'AUTO SPARE PARTS RETAILER' => 'AUTO SPARE PARTS RETAILER',
                                    'LUBRICANT RETAILER' => 'LUBRICANT RETAILER',
                                    'TWO WHEELER PARTS SHOP' => 'TWO WHEELER PARTS SHOP',
                                    'CAR ACCESSORIES & PARTS SHOP' => 'CAR ACCESSORIES & PARTS SHOP',
                                    'TRACTOR PARTS SHOP' => 'TRACTOR PARTS SHOP',
                                    'HCV-LCV SHOP' => 'HCV-LCV SHOP'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label>Owner Name <span class="text-danger">*</span></label>
                                    {!! Form::text('owner_name', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Nistha Awareness + Registered Status <span class="text-danger">*</span></label>
                                    {!! Form::select('saathi_awareness_status', [
                                    'Not Done' => 'Not Done',
                                    'Done' => 'Done'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    <label>Distributor Name <span class="text-danger">*</span></label>
                                    {!! Form::select('distributor_name', [
                                    '' => 'Select Distributor',
                                    '200295 - SHIVAM ENTERPRISES' => '200295 - SHIVAM ENTERPRISES',
                                    '200217 - CHOPRA MOTOR STORE' => '200217 - CHOPRA MOTOR STORE',
                                    '200198 - BANKA DISTRIBUTORS' => '200198 - BANKA DISTRIBUTORS',
                                    '200035 - L.B.DISTRIBUTOR' => '200035 - L.B.DISTRIBUTOR',
                                    '201405 - GUPTA AUTO STORE' => '201405 - GUPTA AUTO STORE'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                            </div>

                            @include('secondary_customers.partials.common_fields')
                        </div>

                        {{-- ================= WORKSHOP TAB ================= --}}
                        <div class="tab-pane" id="workshop" role="tabpanel">
                            {!! Form::hidden('type', 'WORKSHOP') !!}

                            <div class="row">
                                <div class="col-md-4">
                                    <label>Type</label>
                                    <input type="text" class="form-control" value="WORKSHOP" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label>Select Sub Type <span class="text-danger">*</span></label>
                                    {!! Form::select('sub_type', [
                                    '' => 'Select Sub Type',
                                    'Lube & Filter Change Workshop' => 'Lube & Filter Change Workshop',
                                    'Two-Wheeler Service Workshop' => 'Two-Wheeler Service Workshop',
                                    'Car Service Workshop' => 'Car Service Workshop',
                                    'HCV - LCV Workshop' => 'HCV - LCV Workshop'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                                <div class="col-md-4">
                                    <label>Owner Name <span class="text-danger">*</span></label>
                                    {!! Form::text('owner_name', null, ['class' => 'form-control', 'required']) !!}
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Nistha Awareness + Registered Status <span class="text-danger">*</span></label>
                                    {!! Form::select('saathi_awareness_status', [
                                    'Not Done' => 'Not Done',
                                    'Done' => 'Done'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    <label>Distributor Name <span class="text-danger">*</span></label>
                                    {!! Form::select('distributor_name', [
                                    '' => 'Select Distributor',
                                    '200295 - SHIVAM ENTERPRISES' => '200295 - SHIVAM ENTERPRISES',
                                    '200217 - CHOPRA MOTOR STORE' => '200217 - CHOPRA MOTOR STORE',
                                    '200198 - BANKA DISTRIBUTORS' => '200198 - BANKA DISTRIBUTORS',
                                    '200035 - L.B.DISTRIBUTOR' => '200035 - L.B.DISTRIBUTOR',
                                    '201405 - GUPTA AUTO STORE' => '201405 - GUPTA AUTO STORE'
                                    ], null, ['class' => 'form-control select2', 'required']) !!}
                                </div>
                            </div>

                            @include('secondary_customers.partials.common_fields')
                        </div>

                    </div>

                    <div class="card-footer text-right mt-5">
                        <button type="submit" class="btn btn-theme">Save</button>
                        <a href="{{ route('secondary-customers.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $('.select2').select2();

            // Auto open correct tab when editing existing customer
            @if(isset($customer) && $customer->exists)
            var type = '{{ strtolower($customer->type) }}';
            $('.custom-nav-tabs a[href="#' + type + '"]').tab('show');
            @endif
        });
        $(document).ready(function() {

            $('.select2').select2({
                width: '100%'
            });

            $('#country_id').on('change', function() {
                let countryId = $(this).val();
                $('#state_id').html('<option value="">Loading...</option>');
                $('#district_id, #city_id, #pincode_id').html('<option value="">Select</option>');

                if (countryId) {
                    $.get('/get-states/' + countryId, function(data) {
                        let options = '<option value="">Select State</option>';
                        data.forEach(item => {
                            options += `<option value="${item.id}">${item.state_name}</option>`;
                        });
                        $('#state_id').html(options);
                    });
                }
            });

            $('#state_id').on('change', function() {
                let stateId = $(this).val();
                $('#district_id').html('<option value="">Loading...</option>');
                $('#city_id, #pincode_id').html('<option value="">Select</option>');

                if (stateId) {
                    $.get('/get-districts/' + stateId, function(data) {
                        let options = '<option value="">Select District</option>';
                        data.forEach(item => {
                            options += `<option value="${item.id}">${item.district_name}</option>`;
                        });
                        $('#district_id').html(options);
                    });
                }
            });

            $('#district_id').on('change', function() {
                let districtId = $(this).val();
                $('#city_id').html('<option value="">Loading...</option>');
                $('#pincode_id').html('<option value="">Select</option>');

                if (districtId) {
                    $.get('/get-cities/' + districtId, function(data) {
                        let options = '<option value="">Select City</option>';
                        data.forEach(item => {
                            options += `<option value="${item.id}">${item.city_name}</option>`;
                        });
                        $('#city_id').html(options);
                    });
                }
            });

            $('#city_id').on('change', function() {
                let cityId = $(this).val();
                $('#pincode_id').html('<option value="">Loading...</option>');

                if (cityId) {
                    $.get('/get-pincodes/' + cityId, function(data) {
                        let options = '<option value="">Select Pincode</option>';
                        data.forEach(item => {
                            options += `<option value="${item.id}">${item.pincode}</option>`;
                        });
                        $('#pincode_id').html(options);
                    });
                }
            });

        });
    </script>



</x-app-layout>