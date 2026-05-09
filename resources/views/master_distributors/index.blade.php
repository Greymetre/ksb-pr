<x-app-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                {{-- ================= HEADER ================= --}}
<div class="card-header card-header-icon card-header-theme">
    <div class="card-icon">
        <i class="material-icons">store</i>
    </div>

    <h4 class="card-title w-100 d-flex flex-column flex-md-row  align-items-center justify-content-between"> {{-- w-100 to ensure full width for proper wrapping --}}

        {{-- Title on its own line on mobile --}}
        <div class=" text-md-start mb-2 mb-md-0">
            Master Distributors List
        </div>

        {{-- Controls section: wraps to new lines on mobile --}}
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-md-end gap-2">
            {{-- Global Search Input --}}
            <input type="text" id="globalSearch" class="form-control mr-2 " placeholder="Search..." style="width: 250px;">

            {{-- Buttons group --}}
            <div class="d-flex gap-2 w-100 w-md-auto justify-content-center justify-content-md-end">
                <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#filterSection">
                    <i class="material-icons">tune</i>
                </button>
                @if(auth()->user()->can(['master_distributor_create']))
                <a href="{{ route('master-distributors.create') }}" class="btn btn-theme">
                    <i class="material-icons">add_circle</i>
                </a>
                @endif
            </div>
        </div>

    </h4>
</div>
@if (session('debug_message'))
    <div class="alert alert-info">
        {{ session('debug_message') }}
    </div>
@endif

<!-- @if(session('importErrors'))
<div class="card border-danger shadow-sm mt-3">
    <div class="card-header bg-danger text-white">
        <strong>Import Errors Found</strong>
    </div>

    <div class="card-body p-2" style="max-height:300px; overflow:auto;">
        @foreach(session('importErrors') as $error)

        <div class="d-flex align-items-start border-bottom py-2">
            
            <div class="me-3">
                <span class="badge bg-danger">
                    Row {{ $error['row'] }}
                </span>
            </div>

            <div>
                <div>
                    <strong>{{ ucfirst($error['column']) }}</strong>
                </div>

                <div class="text-muted small">
                    Value: <code>{{ $error['value'] ?? '-' }}</code>
                </div>

                <div class="text-danger small">
                    {{ $error['errors'] }}
                </div>
            </div>

        </div>

        @endforeach
    </div>

    <div class="card-footer text-muted small">
        Total Errors: {{ count(session('importErrors')) }}
    </div>
</div>
@endif -->
@if(session('validationErrors') || session('customErrors'))
<div class="card border-danger shadow-sm mt-3">
    <div class="card-header bg-danger text-white">
        <strong>Import Errors Found</strong>
    </div>

    <div class="card-body p-2" style="max-height:300px; overflow:auto;">

        {{-- Validation Errors --}}
        @if(session('validationErrors'))
            @foreach(session('validationErrors') as $error)
                <div class="border-bottom py-2">
                    <span class="badge bg-danger">Row {{ $error['row'] }}</span>
                    <strong>{{ $error['column'] }}</strong> :
                    <span class="text-danger">{{ $error['errors'] }}</span>
                </div>
            @endforeach
        @endif

        {{-- Custom Errors --}}
        @if(session('customErrors'))
            @foreach(session('customErrors') as $error)
                <div class="border-bottom py-2">
                    <span class="badge bg-warning">Error</span>
                    <span class="text-danger">{{ $error['message'] }}</span>
                </div>
            @endforeach
        @endif

    </div>
</div>
@endif

<!-- @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif -->

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

                {{-- ================= FILTERS ================= --}}
                <!-- <div class="collapse" id="filterSection">
    <div class="card-body">

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    <label>Distributor Code</label>
                    <input type="text" id="code"
                           class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Legal Name</label>
                    <input type="text" id="name"
                           class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Status</label>
                    <select id="status" class="form-control">
                        <option value="">Select</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

        </div>

    </div>
</div> -->

                <div class="collapse" id="filterSection">
                    
                    <form action="{{ route('master-distributors.export') }}" method="GET" id="exportForm">
                        <input type="hidden" name="global_search" id="export_global_search" value="">
                        <div class="d-flex flex-wrap flex-row">

                            @if(!isCustomerUser())
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="code">
                                    <option value="">Select Codes</option>
                                    @foreach($filters['distributor_codes'] as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="name">
                                    <option value="">Select Firm Names</option>
                                    @foreach($filters['legal_names'] as $name)
                                    <option value="{{ $name }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="trade_name">
                                    <option value="">Select Trade Names</option>
                                    @foreach($filters['trade_names'] as $trade)
                                    <option value="{{ $trade }}">{{ $trade }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="contact_person">
                                    <option value="">Select Persons</option>
                                    @foreach($filters['contact_persons'] as $person)
                                    <option value="{{ $person }}">{{ $person }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="mobile">
                                    <option value="">Select Mobiles</option>
                                    @foreach($filters['mobiles'] as $mobile)
                                    <option value="{{ $mobile }}">{{ $mobile }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="billing_state" id="filter_billing_state">
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <!-- @if(!isCustomerUser()) -->
                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="billing_city" id="filter_billing_city">
                                    <option value="">Select City</option>
                                </select>
                            </div>
                            <!-- @endif -->


                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="status">
                                    <option value="">Select Status</option>
                                    @foreach($filters['business_statuses'] as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="created_by" id="created_by"
                                    data-style="select-with-transition" title="Created By">
                                    <option value="">Select Created By</option>
                                    <option value="other">Others</option>
                                    <option value="self">Self</option>
                                </select>
                            </div>

                            <div class="p-2" style="width:200px;">
                                <select class="form-control select2" name="active" id="active"
                                    data-style="select-with-transition" title="Status">
                                    <option value="">Select Status</option>
                                    <option value="Y">Active</option>
                                    <option value="N">Inactive</option>
                                </select>
                            </div> -->

                            <!-- <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker"
                                    id="start_date" name="start_date" placeholder="Start Date" autocomplete="off"
                                    readonly></div>
                            <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker"
                                    id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly>
                            </div> -->
                            @if(auth()->user()->can(['master_distributor_report']))
                            <div class="p-1"><button class="btn btn-just-icon btn-theme"
                                    title="{!!  trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}"><i
                                        class="material-icons">cloud_download</i></button></div>
                            @endif
                        </div>
                    </form>
                    <!-- <div class="p-1">
                        <button type="button" id="exportBtn" class="btn btn-just-icon btn-theme"
                            title="{!! trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}">
                            <i class="material-icons">cloud_download</i>
                        </button>
                    </div> -->
                    
                    <div class="next-btn">
                        @if(auth()->user()->can(['master_distributor_import']))
                        <form action="{{ route('master-distributors.import') }}" method="POST" class="form-horizontal"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                    <span class="btn btn-just-icon btn-theme btn-file">
                                        <span class="fileinput-new"><i class="material-icons">attach_file</i></span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="hidden">
                                        <input type="file" name="import_file" required accept=".xls,.xlsx" />
                                    </span>
                                </div>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-just-icon btn-theme"
                                            title="Upload Master Distributors">
                                        <i class="material-icons">cloud_upload</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        @endif
                        <div class="input-group-append">
                        @if(auth()->user()->can(['master_distributor_template']))
                        <!-- <a href="{{ route('master-distributors.template') }}" 
                            class="btn btn-just-icon btn-theme"
                            title="Download Master Distributor Template (Headers Only)">
                                <i class="material-icons">text_snippet</i>
                            </a> -->

                        <div class='mr-2'>
                            <button type="button" id="templateDownloadBtn" class="btn btn-just-icon btn-theme"
                                title="{!! trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}">
                                <i class="material-icons">text_snippet</i>
                            </button>
                        </div>
                        @endif
                        
</div>
                    </div>
                </div>

                {{-- ================= BODY ================= --}}
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="masterDistributorsTable"
                            class="table table-striped table-bordered table-hover w-100">

                            <thead class="text-primary">
                                <tr>
                                    <th width="80">Action</th>
                                    <th>Distributor Code</th>
                                    <th>Legal Name</th>
                                    <th>Trade Name</th>
                                    <th>Contact Person</th>
                                    <th>Mobile</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>

                            <tbody></tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- ================= DATATABLE SCRIPT ================= --}}

    <script>
    let cityMapping = @json(\App\Models\City::pluck('city_name','id'));
    let stateMapping = @json(\App\Models\State::pluck('state_name','id'));
</script>

<script>
$(document).ready(function() {

    // ==== Mapping Objects ====


    const COUNTRY_ID = 1;

    // Load States mapping on Page Load
    $.ajax({
        url: "{{ route('master-distributors.get-states', '') }}/" + COUNTRY_ID,
        type: "GET",
        dataType: "json",
        success: function(data) {
            $('#filter_billing_state').empty().append('<option value="">Select State</option>');
            $.each(data, function(index, state) {
                $('#filter_billing_state').append(
                    '<option value="' + state.id + '">' + state.state_name + '</option>'
                );

                // Update mapping
                stateMapping[state.id] = state.state_name;
            });
        }
    });

    // State Change → Load Cities & mapping
    $('#filter_billing_state').on('change', function() {
        let state_id = $(this).val();
        let citySelect = $('#filter_billing_city');
        citySelect.empty().append('<option value="">Select City</option>');

        if (!state_id) {
            table.draw();
            return;
        }

        citySelect.append('<option value="">Loading...</option>');

        $.ajax({
            url: "{{ route('master-distributors.cities-for-state', '') }}/" + state_id,
            type: "GET",
            dataType: "json",
            success: function(data) {
                console.log("Cities loaded for state", state_id, ":", data);
                citySelect.empty().append('<option value="">All Cities</option>');
                cityMapping = {}; // Reset mapping
                if (data.length === 0) {
                    citySelect.append('<option value="">No cities found</option>');
                } else {
                    $.each(data, function(index, city_name) {
                        // Assuming backend sends just names in array
                        citySelect.append('<option value="' + city_name + '">' + city_name + '</option>');
                        // Mapping: use city_name itself as key
                        cityMapping[city_name] = city_name;
                    });
                }
                citySelect.trigger('change.select2');
                table.draw();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.error("Response:", xhr.responseText);
                citySelect.empty().append('<option value="">Error: ' + error + '</option>');
            }
        });
    });

    // ==== DataTable ====
    let table = $('#masterDistributorsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        order: [[9, 'desc']],
        ajax: {
            url: "{{ route('master-distributors.index') }}",
            type: "GET",
            data: function(d) {
                d.code = $('select[name="code"]').val();
                d.name = $('select[name="name"]').val();
                d.trade_name = $('select[name="trade_name"]').val();
                d.contact_person = $('select[name="contact_person"]').val();
                d.mobile = $('select[name="mobile"]').val();
                d.billing_state_id = $('#filter_billing_state').val() || '';
                d.billing_city = $('#filter_billing_city').val() ? $('#filter_billing_city').val().trim() : '';
                d.status = $('select[name="status"]').val();
                d.global_search = $('#globalSearch').val();
            }
        },
        columns: [
            { data: 'action', orderable: false, searchable: false },
            { data: 'distributor_code' },
            { data: 'legal_name' },
            { data: 'trade_name' },
            { data: 'contact_person' },
            { data: 'mobile' },
            { 
                data: 'billing_city',
                render: function(data, type, row) {
                    return $.isNumeric(data) ? (cityMapping[data] || 'Unknown City') : data;
                }
            },
            { 
                data: 'billing_state',
                render: function(data, type, row) {
                    return $.isNumeric(data) ? (stateMapping[data] || 'Unknown State') : data;
                }
            },
            { data: 'business_status', orderable: false },
            { data: 'created_at' }
        ],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>><"row"<"col-md-12"t>><"row"<"col-md-6"i><"col-md-6"p>>',
    });

    // ==== Filters & Search ====
    $('#filter_billing_city, #filter_billing_state').on('change', function() { table.draw(); });
    $('select[name="code"], select[name="name"], select[name="trade_name"], select[name="contact_person"], select[name="mobile"], select[name="status"]').on('change', function() { table.draw(); });

    let searchTimeout;
    $('#globalSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.draw();
        }, 500);
    });

    // ==== Export Button ====
    $('#exportBtn').on('click', function() {
        let url = "{{ route('master-distributors.export') }}?";
        url += "code=" + encodeURIComponent($('select[name="code"]').val() || '') + "&";
        url += "name=" + encodeURIComponent($('select[name="name"]').val() || '') + "&";
        url += "trade_name=" + encodeURIComponent($('select[name="trade_name"]').val() || '') + "&";
        url += "contact_person=" + encodeURIComponent($('select[name="contact_person"]').val() || '') + "&";
        url += "mobile=" + encodeURIComponent($('select[name="mobile"]').val() || '') + "&";
        url += "billing_state_id=" + encodeURIComponent($('#filter_billing_state').val() || '') + "&";
        url += "billing_city=" + encodeURIComponent($('#filter_billing_city').val() || '') + "&";
        url += "status=" + encodeURIComponent($('select[name="status"]').val() || '') + "&";
        url += "global_search=" + encodeURIComponent($('#globalSearch').val().trim() || '');
        window.location.href = url;
    });

    $('#templateDownloadBtn').on('click', function() {
        window.location.href = "{{ route('master-distributors.template') }}";
    });

    // Initial Draw
    table.draw();

    // ==== Status Toggle ====
$(document).on('change', '.distributor-status-toggle', function() {

    var toggle = $(this);
    var id = toggle.data('id');
    var isChecked = toggle.is(':checked');

    $.ajax({
        url: "{{ route('master-distributors.toggle-status') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id
        },
        success: function(response) {
            table.draw(false);
        },
        error: function() {
            toggle.prop('checked', !isChecked);
            alert('Status update failed');
        }
    });

});
});
</script>    

    <!-- <script>
    $(document).ready(function() {

        let table = $('#masterDistributorsTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,

            language: {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
    },
            order: [
                [9, 'desc']
            ],

            ajax: {
                url: "{{ route('master-distributors.index') }}",
                type: "GET",
                data: function(d) {
                    d.code = $('select[name="code"]').val();
                    d.name = $('select[name="name"]').val();
                    d.trade_name = $('select[name="trade_name"]').val();
                    d.contact_person = $('select[name="contact_person"]').val();
                    d.mobile = $('select[name="mobile"]').val();
                    d.billing_state_id = $('#filter_billing_state').val() || '';
d.billing_city = $('#filter_billing_city').val() ? $('#filter_billing_city').val().trim() : '';
                    d.status = $('select[name="status"]').val();
                    d.global_search = $('#globalSearch').val();
                }
            },

            columns: [{
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'distributor_code'
                },
                {
                    data: 'legal_name'
                },
                {
                    data: 'trade_name'
                },
                {
                    data: 'contact_person'
                },
                {
                    data: 'mobile'
                },
                {
                    data: 'billing_city'
                },
                {
                    data: 'billing_state'
                },
                {
                    data: 'business_status',
                    orderable: false
                },
                {
                    data: 'created_at'
                }
            ],

            dom: '<"row"<"col-md-6"l><"col-md-6"f>><"row"<"col-md-12"t>><"row"<"col-md-6"i><"col-md-6"p>>',
        });

        // Load States on Page Load
        const COUNTRY_ID = 1
        
     $.ajax({
        url: "{{ route('master-distributors.get-states', '') }}/" + COUNTRY_ID,
        type: "GET",
        dataType: "json",
        success: function(data) {
            $('#filter_billing_state').empty().append('<option value="">Select State</option>');
            $.each(data, function(index, state) {
                $('#filter_billing_state').append(
                    '<option value="' + state.id + '">' + state.state_name + '</option>'
                );
            });
            $('#filter_billing_state').trigger('change.select2');
        },
        error: function() {
            console.error("Failed to load states");
        }
    });

// State Change → Load Cities
$('#filter_billing_state').on('change', function() {
    let state_id = $(this).val();
    let citySelect = $('#filter_billing_city');

    citySelect.empty().append('<option value="">Select City</option>');

    if (!state_id) {
        table.draw();
        return;
    }

    citySelect.append('<option value="">Loading...</option>');

    $.ajax({
        url: "{{ route('master-distributors.cities-for-state', '') }}/" + state_id,
        type: "GET",
        dataType: "json",
        success: function(data) {
            citySelect.empty().append('<option value="">All Cities</option>');
            if (data.length === 0) {
                citySelect.append('<option value="">No cities found</option>');
            } else {
                $.each(data, function(index, city_name) {
                    citySelect.append('<option value="' + city_name + '">' + city_name + '</option>');
                });
            }
            citySelect.trigger('change.select2');
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response:", xhr.responseText);
            citySelect.empty().append('<option value="">Error: ' + error + '</option>');
        }
    });

    table.draw();
});

        // City Change → Redraw Table
       $('#filter_billing_city').on('change', function() {
        table.draw();
    });

        // Other Filters Change
        $('select[name="code"], select[name="name"], select[name="trade_name"], ' +
            'select[name="contact_person"], select[name="mobile"], select[name="status"]').on('change',
            function() {
                table.draw();
            });

        // Global Search
        let searchTimeout;
        $('#globalSearch').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                table.draw();
            }, 500);
        });

        // Export Button
        $('#exportBtn').on('click', function() {
        let url = "{{ route('master-distributors.export') }}?";
        url += "code=" + encodeURIComponent($('select[name="code"]').val() || '') + "&";
        url += "name=" + encodeURIComponent($('select[name="name"]').val() || '') + "&";
        url += "trade_name=" + encodeURIComponent($('select[name="trade_name"]').val() || '') + "&";
        url += "contact_person=" + encodeURIComponent($('select[name="contact_person"]').val() || '') + "&";
        url += "mobile=" + encodeURIComponent($('select[name="mobile"]').val() || '') + "&";
        url += "billing_state_id=" + encodeURIComponent($('#filter_billing_state').val() || '') + "&";  // ID भेजें
        url += "billing_city=" + encodeURIComponent($('#filter_billing_city').val() || '') + "&";
        url += "status=" + encodeURIComponent($('select[name="status"]').val() || '') + "&";
        url += "global_search=" + encodeURIComponent($('#globalSearch').val().trim() || '');

        window.location.href = url;
    });

    $('#templateDownloadBtn').on('click', function() {
    window.location.href = "{{ route('master-distributors.template') }}";
});

        // Initial draw
        table.draw();

        $(document).on('change', '.distributor-status-toggle', function() {
    var toggle = $(this);
    var id = toggle.data('id'); // data-id se ID le rahe hain
    var isChecked = toggle.is(':checked');

    // Optimistic UI update (turant badge change kar do)
    var badge = toggle.closest('tr').find('.badge');
    if (isChecked) {
        badge.removeClass('badge-danger').addClass('badge-success').text('ACTIVE');
    } else {
        badge.removeClass('badge-success').addClass('badge-danger').text('INACTIVE');
    }

    // AJAX call to update in database
    $.ajax({
        url: "{{ route('master-distributors.toggle-status') }}",
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}',
            id: id
        },
        success: function(response) {
            // Success – kuch karna nahi, already UI update kar diya
            console.log('Status updated to: ' + response.new_status);
        },
        error: function() {
            // Agar fail ho gaya to wapas purana status kar do
            toggle.prop('checked', !isChecked);
            if (!isChecked) {
                badge.removeClass('badge-danger').addClass('badge-success').text('ACTIVE');
            } else {
                badge.removeClass('badge-success').addClass('badge-danger').text('INACTIVE');
            }
            alert('Status update failed. Please try again.');
        }
    });
});

$('#exportForm').on('submit', function() {
    // Global search ki current value hidden input mein daal do
    $('#export_global_search').val($('#globalSearch').val().trim());
});
    });
    </script> -->


</x-app-layout>