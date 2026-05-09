<x-app-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">build_circle</i>
                    </div>
                    <h4 class="card-title">
                        {{ $typeTitle ?? 'Mechanics List' }}
                        <span class="float-right">
                            <!-- Global Search Box -->
                            <input type="text" id="global_search" class="form-control d-inline-block"
                                style="width: 250px;" placeholder="Search anything...">

                            <button class="btn btn-info" type="button" data-toggle="collapse"
                                data-target="#advanceFilter">
                                <i class="material-icons">filter_list</i> Filters
                            </button>

                            <a href="{{ route('mechanics.create') }}" class="btn btn-theme">
                                <i class="material-icons">add_circle</i> Add New Mechanic
                            </a>
                        </span>
                    </h4>
                </div>

                <!-- Advanced Filters -->
                <div class="collapse" id="advanceFilter">
                    @if(auth()->user()->can(['customer_download']))
                    <form method="GET" action="{{ $downloadRoute }}" id="downloadForm">
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

                            <!-- Opportunity Status -->
                            <div class="col-md-3 mt-3">
                                <label>Opportunity Status</label>
                                {!! Form::select('opportunity_status',
                                ['' => 'All', 'HOT' => 'HOT', 'WARM' => 'WARM', 'COLD' => 'COLD', 'LOST' => 'LOST'],
                                null,
                                ['class' => 'form-control select2', 'id' => 'opportunity_status']) !!}
                            </div>

                            <!-- Awareness Status -->
                            <div class="col-md-3 mt-3">
                                <label id="awareness_label">
                                    {{ in_array($type ?? '', ['RETAILER', 'WORKSHOP']) ? 'Nistha' : 'Saathi' }}
                                    Awareness Status
                                </label>
                                {!! Form::select('awareness_status',
                                ['' => 'All', 'Done' => 'Done', 'Not Done' => 'Not Done'],
                                null,
                                ['class' => 'form-control select2', 'id' => 'awareness_status']) !!}
                            </div>
                            <!-- <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="start_date"
                    name="start_date" placeholder="Start Date" autocomplete="off" readonly></div>
            <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="end_date"
                    name="end_date" placeholder="End Date" autocomplete="off" readonly></div> -->
                            <div class="p-2"><button class="btn btn-just-icon btn-theme"
                                    title="{!!  trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}"><i
                                        class="material-icons">cloud_download</i></button></div>
                            @endif
                        </div>
                    </form>
                    @endif
                    <div class="next-btn">
                        @if(auth()->user()->can(['customer_upload']))
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
                        <!-- <form action="" class="form-horizontal" method="post"
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
        </form> -->
                        @endif
                        @if(auth()->user()->can(['customer_template']))
                        <a href="{{ $templateRoute }}" class="btn btn-just-icon btn-theme"
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
                    <table id="mechanicsTable" class="table table-striped table-bordered table-hover w-100">
                        <thead class="text-primary">
                            <tr>
                                <th width="100">Action</th>
                                <th>Owner Name</th>
                                <th>Shop Name</th>
                                <th>Mobile</th>
                                <th>Beat</th>
                                <th>State</th>
                                <th>City</th>
                                <th>Opportunity</th>
                                <th>Saathi Status</th>
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
    </div>
    </div>

    <script>
    $(document).ready(function() {
        // Sab select2 elements ko initialize karo
        $('.select2').each(function() {
            var placeholderText = $(this).data('placeholder') || 'Select an option';
            $(this).select2({
                placeholder: placeholderText,
                allowClear: true,
                width: '100%'
            });
        });

        // City dropdown ko bhi alag se initialize karo kyunki ye dynamic hai
        // $('#city_id').select2({
        //     placeholder: "All Cities",
        //     allowClear: true,
        //     width: '100%'
        // });

        // DataTable initialize
        let table = $('#mechanicsTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: true,
            order: [
                [9, 'desc']
            ], // Created At descending
            ajax: {
                url: "{{ route('mechanics.index') }}",
                type: "GET",
                data: function(d) {
                    d.owner_name = $('#owner_name').val() || '';
                    d.shop_name = $('#shop_name').val() || '';
                    d.mobile = $('#mobile').val() || '';
                    d.beat_id = $('#beat_id').val() || '';
                    d.state_id = $('#state_id').val() || '';
                    d.city_id = $('#city_id').val() || '';
                    d.opportunity_status = $('#opportunity_status').val() || '';
                    d.awareness_status = $('#awareness_status').val() || '';
                    d.global_search = $('#global_search').val() || '';
                }
            },
            columns: [{
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'owner_name'
                },
                {
                    data: 'shop_name'
                },
                {
                    data: 'mobile_number'
                },
                {
                    data: 'beat_id'
                },
                {
                    data: 'state_id'
                },
                {
                    data: 'city_id'
                },
                {
                    data: 'opportunity_status'
                },
                {
                    data: 'awareness_status'
                },
                {
                    data: 'created_at'
                }
            ],
            dom: 't<"row mt-3"<"col-md-6"i><"col-md-6"p>>',
            language: {
                processing: "Loading data...",
                zeroRecords: "No mechanics found",
                info: "Showing _START_ to _END_ of _TOTAL_ mechanics",
                infoEmpty: "Showing 0 to 0 of 0 mechanics",
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
            '#owner_name, #shop_name, #mobile, #beat_id, #state_id, #city_id, #opportunity_status, #awareness_status',
            function() {
                table.draw();
            });

        // Select2 ke special events (jab clear ya select kare)
        $(document).on('select2:select select2:clear',
            '#owner_name, #shop_name, #mobile, #beat_id, #state_id, #city_id, #opportunity_status, #awareness_status',
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
    });
    </script>
</x-app-layout>