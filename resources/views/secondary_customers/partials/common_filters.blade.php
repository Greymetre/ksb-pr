<div>
    @if(auth()->user()->can(['customer_download']))
    <form method="GET" action="{{ URL::to('customers-download') }}">
        <div class="d-flex flex-wrap flex-row">
            @if(!isCustomerUser())
            <div class="col-md-3">
                <label>Owner Name</label>
                <select id="owner_name" class="form-control select2" data-placeholder="All Owners">
                    <option value="">All Owners</option>
                    @foreach($ownerNames as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Shop Name Dropdown -->
            <div class="col-md-3">
                <label>Shop Name</label>
                <select id="shop_name" class="form-control select2" data-placeholder="All Shops">
                    <option value="">All Shops</option>
                    @foreach($shopNames as $shop)
                    <option value="{{ $shop }}">{{ $shop }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mobile Number Dropdown -->
            <div class="col-md-3">
                <label>Mobile Number</label>
                <select id="mobile" class="form-control select2" data-placeholder="All Mobiles">
                    <option value="">All Mobiles</option>
                    @foreach($mobiles as $mobile)
                    <option value="{{ $mobile }}">{{ $mobile }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Beat -->
            <div class="col-md-3">
                <label>Beat</label>
                <select id="beat_id" class="form-control select2">
                    <option value="">All Beats</option>
                    @foreach(\App\Models\Beat::where('active', 'Y')->orderBy('beat_name')->get() as $beat)
                    <option value="{{ $beat->id }}">{{ $beat->beat_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- State -->
            <div class="col-md-3 mt-3">
                <label>State</label>
                <select id="state_id" class="form-control select2">
                    <option value="">All States</option>
                    @foreach(\App\Models\State::orderBy('state_name')->get() as $state)
                    <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- City (AJAX load) -->
            <div class="col-md-3 mt-3">
                <label>City</label>
                <select id="city_id" class="form-control select2">
                    <option value="">All Cities</option>
                </select>
            </div>

            <!-- Opportunity Status -->
            <div class="col-md-3 mt-3">
                <label>Opportunity Status</label>
                <select id="opportunity_status" class="form-control">
                    <option value="">All</option>
                    <option value="HOT">HOT</option>
                    <option value="WARM">WARM</option>
                    <option value="COLD">COLD</option>
                    <option value="LOST">LOST</option>
                </select>
            </div>

            <!-- Awareness Status (Dynamic Label) -->
            <div class="col-md-3 mt-3">
                <label id="awareness_label">
                    {{ in_array($type ?? '', ['RETAILER', 'WORKSHOP']) ? 'Nistha' : 'Saathi' }} Awareness Status
                </label>
                <select id="awareness_status" class="form-control">
                    <option value="">All</option>
                    <option value="Done">Done</option>
                    <option value="Not Done">Not Done</option>
                </select>
            </div>

            <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="start_date"
                    name="start_date" placeholder="Start Date" autocomplete="off" readonly></div>
            <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="end_date"
                    name="end_date" placeholder="End Date" autocomplete="off" readonly></div>
            <div class="p-2"><button class="btn btn-just-icon btn-theme"
                    title="{!!  trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}"><i
                        class="material-icons">cloud_download</i></button></div>
                         @endif
        </div>
    </form>
    @endif
    <div class="next-btn">
        @if(auth()->user()->can(['customer_upload']))
        <form action="{{ URL::to('customers-upload') }}" class="form-horizontal" method="post"
            enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="input-group">
                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                    <span class="btn btn-just-icon btn-theme btn-file">
                        <span class="fileinput-new"><i class="material-icons">attach_file</i></span>
                        <span class="fileinput-exists">Change</span>
                        <input type="hidden">
                        <input type="file" title="Select File" name="import_file" required accept=".xls,.xlsx" />
                    </span>
                </div>
                <div class="input-group-append">
                    <button class="btn btn-just-icon btn-theme"
                        title="{!!  trans('panel.global.upload') !!} {!! trans('panel.customers.title') !!}">
                        <i class="material-icons">cloud_upload</i>
                        <div class="ripple-container"></div>
                    </button>
                </div>
            </div>
        </form>
        @endif
        @if(auth()->user()->can(['customer_template']))
        <a href="{{ URL::to('customers-template') }}" class="btn btn-just-icon btn-theme"
            title="{!!  trans('panel.global.template') !!} {!! trans('panel.customers.title_singular') !!}"><i
                class="material-icons">text_snippet</i></a>
        @endif
        @if(auth()->user()->can(['customer_create']))
        <a href="{{ route('customers.create') }}" class="btn btn-just-icon btn-theme"
            title="{!!  trans('panel.global.add') !!} {!! trans('panel.customers.title_singular') !!}"><i
                class="material-icons">add_circle</i></a>
        @endif
    </div>
</div>

<!-- <script>
$('.select2').select2({
    placeholder: function() {
        return $(this).data('placeholder');
    },
    allowClear: true
});

// City load on State change
$('#state_id').on('change', function() {
    var state_id = $(this).val();
    $('#city_id').empty().append('<option value="">Loading...</option>').select2({data: [{id: '', text: 'Loading...'}]});

    if (state_id) {
        $.ajax({
            url: '{{ route("get.cities", "") }}/' + state_id,
            type: 'GET',
            success: function(data) {
                var options = '<option value="">All Cities</option>';
                $.each(data, function(i, city) {
                    options += `<option value="${city.id}">${city.city_name}</option>`;
                });
                $('#city_id').html(options).select2({placeholder: "All Cities", allowClear: true});
            }
        });
    } else {
        $('#city_id').html('<option value="">All Cities</option>').select2({placeholder: "All Cities", allowClear: true});
    }
});
</script> -->