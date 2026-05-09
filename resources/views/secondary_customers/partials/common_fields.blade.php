{{-- Common fields for all tabs --}}
<div class="row mt-3">
    <div class="col-md-6">
        <label>Shop Name <span class="text-danger">*</span></label>
        {!! Form::text('shop_name', null, ['class' => 'form-control', 'required']) !!}
    </div>
    <div class="col-md-6">
        <label>Mobile Number <span class="text-danger">*</span></label>
        {!! Form::text('mobile_number', null, ['class' => 'form-control', 'required']) !!}
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-4">
        <label>WhatsApp / Alternate Number</label>
        {!! Form::text('whatsapp_number', null, ['class' => 'form-control']) !!}
    </div>
    <div class="col-md-4">
        <label>Sales Exception Assignment</label>
        {!! Form::text('sales_exception_assignment', null, ['class' => 'form-control']) !!}
    </div>
    <div class="col-md-4">
        <label>Select Vehicle Segment</label>
        {!! Form::select('vehicle_segment', [
        '' => 'Select Vehicle Segment',
        '2W' => '2W',
        '3W' => '3W',
        'AGRICULTURE – Tractor' => 'AGRICULTURE – Tractor',
        'COOLANT' => 'COOLANT',
        'EARTH MOVING EQUIPMENT' => 'EARTH MOVING EQUIPMENT',
        'HCV' => 'HCV',
        'LCV' => 'LCV',
        'LUBRICANT' => 'LUBRICANT',
        'PASSENGER VEHICLE (PV)' => 'PASSENGER VEHICLE (PV)'
        ], null, ['class' => 'form-control select2']) !!}
    </div>
</div>

{{-- Photos --}}
<div class="row mt-4 text-center">
    <div class="col-md-6">
        <label>ID / Owner Photo</label>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail">
                 <img id="owner_photo_preview" src="{{ isset($customer) && $customer->owner_photo ? asset('storage/'.$customer->owner_photo) : asset('assets/img/placeholder.jpg') }}" 
             class=" " style="width: 200px; height: 200px; object-fit: cover;" alt="Owner Photo">
       
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail"></div>
            <div>
                <span class="btn btn-just-icon btn-round btn-file">
                    <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                    <span class="fileinput-exists">Change</span>
                    <input type="file" name="owner_photo" id="owner_photo" accept="image/*" class="form-control">
                </span>
                <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                    <i class="fa fa-times"></i> Remove
                </a>
            </div>
        </div>
    </div>








    <div class="col-md-6">
        <label>Shop Photo</label>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail">
                 <img id="shop_photo_preview" src="{{ isset($customer) && $customer->shop_photo ? asset('storage/'.$customer->shop_photo) : asset('assets/img/placeholder.jpg') }}" 
             class="" style="width: 200px; height: 200px; object-fit: cover;" alt="Shop Photo">
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail"></div>
            <div>
                <span class="btn btn-just-icon btn-round btn-file">
                    <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                    <span class="fileinput-exists">Change</span>
        <input type="file" name="shop_photo" id="shop_photo" accept="image/*" class="form-control">
                </span>
                <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                    <i class="fa fa-times"></i> Remove
                </a>
            </div>
        </div>
    </div>




    
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <label>Address Line <span class="text-danger">*</span></label>
        {!! Form::textarea('address_line', null, ['class' => 'form-control', 'rows' => 3, 'required']) !!}
    </div>
</div>

<div class="row mt-3">

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
                {{ old('country_id', $customer->country_id ?? '') == $country->id ? 'selected' : '' }}>
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
            $states = \App\Models\State::where('country_id', old('country_id', $customer->country_id ?? ''))
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
            $districts = \App\Models\District::where('state_id', old('state_id', $customer->state_id ?? ''))
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
            $cities = \App\Models\City::where('district_id', old('district_id', $customer->district_id ?? ''))
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
    <div class="col-md-6 mb-3">
        <label class="col-form-label">
            Pincode <span class="text-danger">*</span>
        </label>

        <select class="form-control select2" name="pincode_id" id="pincode_id">
            <option value="">Select Pincode</option>

            @if(old('city_id') || ($customer->city_id ?? null))
            @php
            $pincodes = \App\Models\Pincode::where('city_id', old('city_id', $customer->city_id ?? ''))
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
    <div class="col-md-6 mb-3">
        <label class="col-form-label">
            Belt / Area / Market Name
        </label>
        {!! Form::text('belt_area_market_name', null, ['class' => 'form-control']) !!}
    </div>





</div>



<!-- <div class="row mt-3">
    <div class="col-md-4">
        <label>Saathi Awareness Status <span class="text-danger">*</span></label>
        {!! Form::select('saathi_awareness_status', ['Not Done' => 'Not Done', 'Done' => 'Done'], null, ['class' =>
        'form-control select2', 'required']) !!}
    </div>
    <div class="col-md-4">
        <label>Opportunity Status <span class="text-danger">*</span></label>
        {!! Form::select('opportunity_status', [
        'COLD' => 'COLD – Low interest / only enquiry',
        'WARM' => 'WARM – Interested but needs time',
        'HOT' => 'HOT – Very interested/almost confirm',
        'LOST' => 'LOST – Deal cancelled'
        ], null, ['class' => 'form-control select2', 'required']) !!}
    </div>

    <div class="col-md-4">
        <label for="beat">Beat <span class="text-danger">*</span></label>
        {!! Form::select('beat_id',
        ['' => 'Select Beat'] + $beats->toArray(),
        null,
        [
        'class' => 'form-control select2',

        ]
        ) !!}
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>GPS Location Updates</label>
        {!! Form::text('gps_location', null, ['class' => 'form-control', 'placeholder' => 'e.g., 17.385044,78.486671'])
        !!}
    </div>
</div> -->




<script>
$(document).ready(function() {

    // ==================== BILLING ADDRESS AJAX CHAINING ====================
    function resetLowerSelects(prefix) {
        var selects = {
            country: prefix + '_country_id',
            state: prefix + '_state_id',
            district: prefix + '_district_id',
            city: prefix + '_city_id',
            pincode: prefix + '_pincode_id'
        };

        var startReset = false;
        $.each(selects, function(key, id) {
            if (startReset) {
                var label = key.charAt(0).toUpperCase() + key.slice(1);
                if (key === 'pincode') label = 'Pincode';
                $(`#${id}`).html(`<option value="">Select ${label}</option>`);
            }
            if (key === 'country') startReset = true; // Start resetting from state onwards
        });
    }

    // Billing Country Change
    $('#country_id').on('change', function() {
        var country_id = $(this).val();
        resetLowerSelects(''); // billing has no prefix

        if (country_id) {
            $('#state_id').html('<option value="">Loading states...</option>');
            $.ajax({
                url: '{{ route("get.states", "") }}/' + country_id,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var options = '<option value="">Select State</option>';
                    $.each(data, function(i, state) {
                        options +=
                            `<option value="${state.id}">${state.state_name}</option>`;
                    });
                    $('#state_id').html(options);
                }
            });
        } else {
            $('#state_id').html('<option value="">Select State</option>');
        }
    });

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




    function previewImage(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            // Agar file remove kiya to placeholder dikhao
            preview.src = "{{ asset('assets/img/placeholder.jpg') }}";
        }
    });
}

// Dono photos ke liye preview enable karo
previewImage('owner_photo', 'owner_photo_preview');
previewImage('shop_photo', 'shop_photo_preview');



    // Same as Billing Checkbox Logic (already good)
    function toggleShippingPanel() {
        if ($('#same_as_billing').is(':checked')) {
            $('#shipping-panel').hide();
        } else {
            $('#shipping-panel').show();
        }
    }

    toggleShippingPanel();
    $('#same_as_billing').on('change', toggleShippingPanel);
    $('.fileinput').fileinput()
    // Auto-trigger on page load for edit form (billing part already there, add shipping if needed)
});
</script>