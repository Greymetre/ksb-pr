<x-app-layout>
   <style>
        .select2{
            z-index: 50 !important;
        }
        #ui-datepicker-div {
            z-index: 10000 !important;
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
                        <span id="retailerTitle">
                            {{ $typeTitle ?? 'Retailers List' }} (<span id="retailerCount">0</span>)
                        </span> <span class="float-right">
                            <input type="text" id="global_search" class="form-control d-inline-block"
                                style="width: 250px;" placeholder="Search anything...">

                            <button class="btn btn-info" type="button" data-toggle="collapse"
                                data-target="#advanceFilter">
                                <i class="material-icons">tune</i> Filter
                            </button>
                             @if(auth()->user()->can(['retailer_create']))
                            <a href="{{ route('retailers.create') }}" class="btn btn-theme">
                                <i class="material-icons">add_circle</i> Add New Retailer
                            </a>
                            @endif
                        </span>
                    </h4>
                </div>

                <!-- Filters -->
                <div class="collapse" id="advanceFilter">
                    <!-- @if(auth()->user()->can(['customer_download'])) -->
                    <form method="GET" action="{{ $downloadRoute }}" id="downloadForm" title="Download Excel">
                        <div class="d-flex flex-wrap flex-row">
                            @if(!isCustomerUser())
                            <!-- Owner Name -->
                            <div class="col-md-3">
                                <label>Owner Name</label>
                                {!! Form::select('owner_name',
                                ['' => 'All Owners'] + $ownerNames->mapWithKeys(function ($item) {
                                return [$item => $item];
                                })->toArray(),
                                null,
                                ['class' => 'form-control select2', 'id' => 'owner_name', ]) !!}
                            </div>

                            <!-- Shop Name -->
                            <div class="col-md-3">
                                <label>Shop Name</label>
                                {!! Form::select('shop_name',
                                ['' => 'All Shops'] + $shopNames->mapWithKeys(function ($item) {
                                return [$item => $item];
                                })->toArray(),
                                null,
                                ['class' => 'form-control select2', 'id' => 'shop_name']) !!}
                            </div>

                            <!-- Mobile Number -->
                            <div class="col-md-3">
                                <label>Mobile Number</label>
                                {!! Form::select('mobile',
                                ['' => 'All Mobiles'] + $mobiles->mapWithKeys(function ($item) {
                                return [$item => $item];
                                })->toArray(),
                                null,
                                ['class' => 'form-control select2', 'id' => 'mobile', ]) !!}
                            </div>

                            <!-- Beat -->
                            <div class="col-md-3">
                                <label>Beat</label>
                                {!! Form::select('beat_id', ['' => 'All Beats'] + \App\Models\Beat::where('active',
                                'Y')->orderBy('beat_name')->pluck('beat_name', 'id')->toArray(), null,
                                ['class' => 'form-control select2', 'id' => 'beat_id', ]) !!}
                            </div>

                            <!-- State -->
                            <div class="col-md-3 mt-3">
                                <label>State</label>
                                {!! Form::select('state_id', ['' => 'All States'] + $states->pluck('state_name',
                                'id')->toArray(), null,
                                ['class' => 'form-control select2', 'id' => 'state_id', ]) !!}
                            </div>

                            <!-- City (Dynamic - initially empty except All) -->
                            <div class="col-md-3 mt-3">
                                <label>City</label>
                                {!! Form::select('city_id', ['' => 'All Cities'], null,
                                ['class' => 'form-control select2', 'id' => 'city_id', ]) !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                <label>Status</label>
                                {!! Form::select('status',
                                ['' => 'All', 'APPROVED' => 'Approved', 'REJECTED' => 'Rejected', 'PENDING' =>
                                'Pending'],
                                null,
                                ['class' => 'form-control select2', 'id' => 'status']) !!}
                            </div>

                            <div class="col-md-3 mt-3">
                                <label>Active</label>
                                {!! Form::select('active',
                                ['' => 'All', 'Y' => 'Active', 'N' => 'Inactive'],
                                null,
                                ['class' => 'form-control select2', 'id' => 'active']) !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                <label>Designation</label>
                                
                                <select class="form-control selectpicker" 
                                    id="designation_id" 
                                    name="designation_id[]" 
                                    multiple
                                    data-style="select-with-transition"
                                    data-live-search="true"
                                    title="Select Designation">
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                <label>Start Date</label>
                                <input type="text" 
                                    class="form-control datepicker" 
                                    id="start_date" 
                                    name="start_date" 
                                    placeholder="Start Date" 
                                    autocomplete="off" 
                                    readonly>
                            </div>

                            <div class="col-md-3 mt-3">
                                <label>End Date</label>
                                <input type="text" 
                                    class="form-control datepicker" 
                                    id="end_date" 
                                    name="end_date" 
                                    placeholder="End Date" 
                                    autocomplete="off" 
                                    readonly>
                            </div>

                            
                            <!-- <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="start_date"
                    name="start_date" placeholder="Start Date" autocomplete="off" readonly></div>
            <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="end_date"
                    name="end_date" placeholder="End Date" autocomplete="off" readonly></div> -->
                        @if(auth()->user()->can(['retailer_report']))
                            <div class="p-2"><button class="btn btn-just-icon btn-theme"
                                    title="{!!  trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}"><i
                                        class="material-icons">cloud_download</i></button></div>
                            @endif
                            @endif
                        </div>
                    </form>
                    <!-- @endif -->
                    <div class="next-btn">
                        @if(auth()->user()->can(['retailer_upload']))
                        <form action="{{ route(strtolower($type).'s.import') }}" method="POST"
                            enctype="multipart/form-data">

                            {{ csrf_field() }}

                            <div class="input-group">
                                <div class="fileinput fileinput-new text-center">
                                    <span class="btn btn-just-icon btn-theme btn-file">
                                        <span class="fileinput-new">
                                            <i class="material-icons">attach_file</i>
                                        </span>

                                        <input type="file" name="import_file" required accept=".xls,.xlsx" />
                                    </span>
                                </div>

                                <div class="input-group-append">
                                    <button class="btn btn-just-icon btn-theme">
                                        <i class="material-icons">cloud_upload</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        @endif
                        @if(auth()->user()->can(['retailer_template']))
                        <a href="{{ $templateRoute }}" class="btn btn-just-icon btn-theme"
                            title="{!!  trans('panel.global.template') !!} {!! trans('panel.customers.title_singular') !!}"><i
                                class="material-icons">text_snippet</i></a>
                        @endif
                        <!-- @if(auth()->user()->can(['customer_create']))
                        <a href="{{ route('customers.create') }}" class="btn btn-just-icon btn-theme"
                            title="{!!  trans('panel.global.add') !!} {!! trans('panel.customers.title_singular') !!}"><i
                                class="material-icons">add_circle</i></a>
                        @endif -->
                    </div>
                </div>
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('importErrors'))
                <div class="alert alert-danger">

                    <strong>{{ count(session('importErrors')) }} Errors Found</strong>

                    <ul>
                        @foreach(session('importErrors') as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
                @endif
                <!-- Table -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="retailersTable" class="table table-striped table-bordered table-hover w-100">
                            <thead class="text-primary">
                                <tr>
                                    <th width="100">Action</th>
                                    <th>Owner Name</th>
                                    <th>Shop Name</th>
                                    <th>Mobile</th>
                                    <th>Beat</th>
                                    <th>State</th>
                                    <th>District</th>
                                    <th>Status</th>
                                    <th>Active</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will fill this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Reject Remark Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Customer</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" id="reject_customer_id">

                            <div class="form-group">
                                <label>Remark</label>
                                <textarea id="reject_remark" class="form-control" rows="3" placeholder="Enter remark..."
                                    required></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" id="submitReject">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('.select2').each(function() {
            var placeholderText = $(this).data('placeholder') || 'Select an option';
            $(this).select2({
                placeholder: placeholderText,
                allowClear: true,
                width: '100%'
            });
        });

        $.get("{{ url('getDesignations') }}", function(data) {

        let options = `<option value="" disabled>Select Designation</option>`;

        data.forEach(function(item) {

            let selected = (item.designation_name === 'ASR' || item.designation_name === 'DSR') 
                ? 'selected' 
                : '';

            options += `<option value="${item.id}" ${selected}>
                            ${item.designation_name}
                        </option>`;
        });

        $('#designation_id').html(options);

        // refresh selectpicker
        $('#designation_id').selectpicker('refresh');
    });

        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom'
        });

        let table = $('#retailersTable').DataTable({
            processing: true,
            serverSide: true,
            drawCallback: function(settings) {

                let api = this.api();
                let count = api.page.info().recordsDisplay;

                $('#retailerCount').text(count);

            },
            searching: false,
            ordering: true,
            order: [
                [9, 'desc']
            ], // Latest first
            ajax: {
                url: "{{ route('retailers.index') }}",
                type: "GET",
                data: function(d) {
                    d.owner_name = $('#owner_name').val();
                    d.mobile = $('#mobile').val();
                    d.shop_name = $('#shop_name').val();
                    d.status = $('#status').val();
                    d.beat_id = $('#beat_id').val() || '';
                    d.state_id = $('#state_id').val() || '';
                    d.city_id = $('#city_id').val() || '';
                    d.status = $('#status').val();
                    d.active = $('#active').val();
                    d.global_search = $('#global_search').val() || '';
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.designation_id = $('#designation_id').val();
                }
            },

            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'owner_name',
                    name: 'owner_name'
                },
                {
                    data: 'shop_name',
                    name: 'shop_name'
                },
                {
                    data: 'mobile_number',
                    name: 'mobile_number'
                },
                {
                    data: 'beat_id',
                    name: 'beat_id'
                },
                {
                    data: 'state_id',
                    name: 'state_id'
                },
                {
                    data: 'district_id',
                    name: 'district_id'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false
                },
                {
                    data: 'active',
                    name: 'active',
                    orderable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                }
            ],

            dom: 't<"row mt-3"<"col-md-6"i><"col-md-6"p>>',
            language: {
                processing: "Loading retailers...",
                zeroRecords: "No retailers found",
                info: "Showing _START_ to _END_ of _TOTAL_ retailers",
                infoEmpty: "No records available",
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            }
        });

        $('#state_id').on('change', function() {
            let stateId = $(this).val();

            // City dropdown reset karo
            $('#city_id').empty().append('<option value="">All Cities</option>').val('').trigger(
                'change');

            if (stateId) {
                $.ajax({
                    url: '{{ route("secondary-customers.get-cities") }}', // Route name update karo apne hisab se
                    type: 'GET',
                    data: {
                        state_id: stateId
                    },
                    success: function(data) {
                        $.each(data, function(key, city) {
                            $('#city_id').append('<option value="' + city.id +
                                '">' + city.city_name + '</option>');
                        });
                        $('#city_id').trigger('change'); // select2 refresh
                    },
                    error: function() {
                        alert('Failed to load cities');
                    }
                });
            }

            // State change hone par table reload
            table.draw();
        });

        // City change par bhi table reload
        $('#city_id').on('change', function() {
            table.draw();
        });

        // Normal change events (text inputs aur normal selects)
        $(document).on('change',
            '#owner_name, #shop_name, #mobile, #beat_id, #state_id, #city_id, #status, #active, #designation_id',
            function() {
                table.draw();
            });

            $('#start_date, #end_date').on('change', function() {
    table.draw();
});

        // Select2 ke special events (jab clear ya select kare)
        $(document).on('select2:select select2:clear',
            '#owner_name, #shop_name, #mobile, #beat_id, #state_id, #city_id, #status, #active,#designation_id',
            function() {
                table.draw();
            });

        // ====== STATE CHANGE → CITY LOAD + TABLE FILTER ======


        // Global search with debounce
        let searchTimeout;
        $('#global_search').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                table.draw();
            }, 600);
        });

        $(document).on('click', '.deleteCustomer', function() {

            if (!confirm('Delete this customer?')) return;

            let url = $(this).data('url');

            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    $('#retailersTable').DataTable().draw(false);
                }
            });

        });

        $('#submitReject').on('click', function() {

            let id = $('#reject_customer_id').val();
            let remark = $('#reject_remark').val();

            if (!remark) {
                alert('Please enter remark');
                return;
            }

            updateStatus(id, 'REJECTED', remark);

            $('#rejectModal').modal('hide');
        });


        // STATUS CHANGE
        $(document).on('click', '.changeStatus', function() {

            let id = $(this).data('id');
            let status = $(this).data('status');

            if (!confirm('Change status to ' + status + '?')) {
                return;
            }

            // 👉 AGAR REJECT HAI → MODAL OPEN
            if (status === 'REJECTED') {
                $('#reject_customer_id').val(id);
                $('#reject_remark').val('');
                $('#rejectModal').modal('show');
                return;
            }

            // NORMAL STATUS CHANGE
            if (!confirm('Change status to ' + status + '?')) return;

            updateStatus(id, status);

            $.ajax({
                url: "{{ route('secondary-customers.change-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                },
                success: function(response) {

                    if (response.success) {

                        // table reload
                        $('#retailersTable').DataTable().draw(false);

                    }

                },
                error: function() {
                    alert('Status update failed');
                }

            });

        });

        // ACTIVE / INACTIVE
        $(document).on('change', '.distributor-status-toggle', function() {
            var toggle = $(this);
            let id = toggle.data('id');
            let isActive = toggle.is(':checked') ? 'Y' : 'N'; // ← important

            if (!confirm('Are you sure you want to ' + (isActive === 'Y' ? 'ACTIVATE' : 'DEACTIVATE') +
                    ' this customer?')) {
                toggle.prop('checked', !toggle.prop('checked')); // revert toggle
                return;
            }

            $.ajax({
                url: "{{ route('secondary-customers.toggle-active') }}", // better to use route name
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    active: isActive // ← send Y or N
                },
                success: function(response) {
                    if (response.success) {
                        // Optional: show small toast / alert
                        console.log("Status updated to " + isActive);
                    }
                },
                error: function(xhr) {
                    alert("Failed to update status");
                    toggle.prop('checked', !toggle.prop('checked')); // revert on error
                }
            });
        });

        function updateStatus(id, status, remark = '') {

            $.ajax({
                url: "{{ route('secondary-customers.change-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status,
                    remark: remark // 👈 important
                },
                success: function(response) {
                    if (response.success) {
                        $('#retailersTable').DataTable().draw(false);
                    }
                },
                error: function() {
                    alert('Status update failed');
                }
            });
        }


    });
    </script>
</x-app-layout>