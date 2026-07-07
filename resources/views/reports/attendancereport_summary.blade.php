<x-app-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title">Attendance Summary Report
                        <span class="">
                            <div class="btn-group header-frm-btn">
                                @if(auth()->user()->can(['attendance_summary_download']))
                                <form method="GET" action="{{ URL::to('attendancesummary-download') }}">
                                    <div class="d-flex flex-wrap flex-row">

                                        <div class="p-2" style="width: 250px;">
                                            <select class="selectpicker" multiple name="branch_id" id="branch_id"
                                                data-style="select-with-transition" title="Select Branch">
                                                <option value="">Select Branch</option>
                                                @if(@isset($branches ))
                                                @foreach($branches as $branche)
                                                <option value="{!! $branche['id'] !!}"
                                                    {{ old( 'branch_id') == $branche['id'] ? 'selected' : '' }}>{!!
                                                    $branche['name'] !!}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="p-2" style="width: 250px;">
                                            <select class="selectpicker" name="division_id" id="division_id"
                                                data-style="select-with-transition" title="Select Zone">
                                                <option value="">Select Zone</option>
                                                @if(@isset($divisions ))
                                                @foreach($divisions as $division)
                                                <option value="{!! $division['id'] !!}"
                                                    {{ old( 'division') == $division['id'] ? 'selected' : '' }}>{!!
                                                    $division['division_name'] !!}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="p-2" style="width: 250px;">
                                            <select class="selectpicker" name="department_id" id="department_id"
                                                data-style="select-with-transition" title="Select Department">
                                                <option value="">Select Department</option>
                                                @if(@isset($all_user_departments ))
                                                @foreach($all_user_departments as $department)
                                                <option value="{!! $department['id'] !!}"
                                                    {{ old( 'department') == $department['id'] ? 'selected' : '' }}>{!!
                                                    $department['name'] !!}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="p-2" style="width: 250px;">
                                            <select class="select2" name="executive_id" id="executive_id"
                                                data-style="select-with-transition" title="Select User">
                                                <option value="">Select User</option>
                                                @if(@isset($users ))
                                                @foreach($users as $user)
                                                <option value="{!! $user['id'] !!}"
                                                    {{ old( 'executive_id') == $user['id'] ? 'selected' : '' }}>{!!
                                                    $user['name'] !!}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="p-2" style="width: 250px;">
                                            <select class="select2" name="status" id="status"
                                                data-style="select-with-transition" title="Select User">
                                                <option value="">Select Status</option>
                                                <option value="0">Pending</option>
                                                <option value="1">Approved</option>
                                                <option value="2">Rejected</option>
                                            </select>
                                        </div>
                                        
                                        <div class="p-2" style="width: 250px;">
                                            <select class="selectpicker" multiple name="designation_id[]" id="designation_id"
                                                data-style="select-with-transition" title="Select Designation">

                                                @foreach($designations as $designation)
                                                    <option value="{{ $designation->id }}"
                                                        {{ (collect(request('designation_id'))->contains($designation->id) || in_array($designation->designation_name, ['ASR','DSR'])) ? 'selected' : '' }}>
                                                        {{ $designation->designation_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="p-2">
                                            <input type="text" class="form-control datepicker" id="start_date"
                                                name="start_date" placeholder="Start Date" autocomplete="off" readonly>
                                        </div>
                                        <div class="p-2">
                                            <input type="text" class="form-control datepicker" id="end_date"
                                                name="end_date" placeholder="End Date" autocomplete="off" readonly>
                                        </div>
                                        <div class="p-2">
                                            <button class="btn btn-just-icon btn-theme"
                                                title="{!!  trans('panel.global.download') !!} Attendance">
                                                <i class="material-icons">cloud_download</i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                @endif
                                <div class="next-btn">
                                    @if(auth()->user()->can(['attendance_create']))
                                    <a data-toggle="modal" data-target="#submitAttendance"
                                        class="btn btn-just-icon btn-theme create d-none" title="Submit Attendance">
                                        <i class="material-icons">add_circle</i>
                                    </a>

                                    @endif
                                    <a href="{{ URL::to('attendance-location') }}"
                                        class="btn btn-just-icon btn-theme  d-none" title="Update Location"><i
                                            class="material-icons">add_location</i></a>
                                </div>
                            </div>
                        </span>
                    </h4>
                </div>
                <div class="card-body">
                    @if(count($errors) > 0)
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        <span>
                            @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </span>
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table id="getattendance"
                            class="table table-striped table-bordered table-hover w-100">
                            <thead class=" text-primary">
                                <th>No</th>
                                <th>User ID</th>
                                <th>Status</th>
                                <th>Employee Code </th>
                                <th>User Name</th>
                                <th>Punch in Date</th>
                                <th>Punch In Time</th>
                                <th>Punch In Address</th>
                                <!-- <th>Punch Image</th>
              <th>Punch Out Date</th> -->
                                <th>Punch Out Time</th>
                                <th>Punch Out Address</th>
                                <th>Working Time</th>
                                <!-- <th>Status</th> -->
                                <!--  <th>Punch In Longitude</th>
              <th>Punch In Letitude</th>
              <th>Punch Out Longitude</th>
              <th>Punch Out Letitude</th> -->
                                <th>Punch In summary</th>
                                <!-- <th>Punch Out summary</th> -->
                                <th>Working Type</th>
                                <th>Attendance Status</th>
                                <th>Remark</th>
                                <th>Action</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="submitAttendance" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title">
                        <span class="modal-title">Submit </span> Attendance <span class="pull-right">
                            <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal">
                                <i class="material-icons">clear</i>
                            </a>
                        </span>
                    </h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('submitAttendances') }}" enctype="multipart/form-data"
                        id="createleadstagesForm"> @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-section">
                                    <label class="form-label">User</label>
                                    <select class="form-control select2" name="user_id" id="user_id"
                                        style="width: 100%;" required>
                                        <option value="">Select User</option>
                                        @if(@isset($users))
                                        @foreach($users as $user)
                                        <option value="{!! $user['id'] !!}">{!! $user['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-section">
                                    <label class="form-label">Working Type</label>
                                    <select class="form-control select2" name="working_type" id="working_type"
                                        style="width: 100%;" required>
                                        <option value="">Select Working Type</option>
                                        <option value="Tour">Tour</option>
                                        <option value="Office">Office</option>
                                        <option value="Local Market Visit">Local Market Visit</option>
                                        <option value="Customer Visit">Customer Visit</option>
                                        <option value="Other">Other</option>
                                        <!-- <option value="Holiday">Holiday</option>
                  <option value="Leave">Leave</option> -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-section">
                                    <label class="form-label">Punchin</label>
                                    <input type="text" name="punchin_date" id="punchin_date"
                                        class="form-control datetimepicker" value="{!! old( 'punchin_date') !!}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-section">
                                    <label class="form-label">Punch Out</label>
                                    <input type="text" name="punchout_date" id="submit_punchout_date"
                                        class="form-control datetimepicker" value="{!! old( 'punchout_date') !!}">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-info save"> Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Punch Out Modal -->
    <div class="modal fade" id="punchOutModal" tabindex="-1" role="dialog" aria-labelledby="punchOutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">schedule</i>
                    </div>
                    <h4 class="card-title">
                        Punch Out
                        <span class="pull-right">
                            <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal">
                                <i class="material-icons">clear</i>
                            </a>
                        </span>
                    </h4>
                </div>
                <div class="modal-body">
                    <form id="punchOutForm" method="POST">
                        @csrf
                        <input type="hidden" name="attendance_id" id="punchout_attendance_id">

                        <div class="row">
                            <!-- Date - separate field -->
                            <!-- <div class="col-md-12">
                                <div class="input_section">
                                    <label class="col-form-label">Punch Out Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker" id="punchout_date"
                                        name="punchout_date" value="{{ date('Y-m-d') }}" placeholder="Select Date"
                                        readonly>
                                </div>
                            </div> -->

                            <!-- Time - separate field -->
                            <div class="col-md-12 mt-3">
                                <div class="input_section">
                                    <label class="col-form-label">Punch Out Time <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control timepicker" id="punchout_time"
                                        name="punchout_time" placeholder="Select Time" required>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="col-md-12 mt-3">
                                <div class="input_section">
                                    <label class="col-form-label">Summary / Remarks (Optional)</label>
                                    <textarea class="form-control" name="punchout_summary" id="punchout_summary"
                                        rows="3" placeholder="Any remarks..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <button type="submit" id="submitPunchOut" class="btn btn-info">
                                Submit Punch Out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- new model for reject attendance -->


    <div class="modal fade bd-example-modal-lg" id="rejec_attendance" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title">
                        <span class="modal-title">Submit </span> Remark <span class="pull-right">
                            <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal">
                                <i class="material-icons">clear</i>
                            </a>
                        </span>
                    </h4>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('rejectAttendance') }}" enctype="multipart/form-data"
                        id="createleadstagesForm_new"> @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-section">
                                    <label class="form-label">Remark</label>
                                    <input type="text" name="remark_status" id="remark_status" class="form-control"
                                        value="{!! old( 'remark_status') !!}" required> <br><br>
                                    <input type="text" name="attendance_id" id="attendance_id" class="form-control"
                                        hidden>

                                </div>
                            </div>
                        </div>
                        <button class="btn btn-info save"> Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- end model for attendance -->



    <script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select User",
            allowClear: true,
            width: '100%'
        });

        // removed empty selector that caused JS errors

        // =============================================
        // PUNCH OUT – Clean & Fully Working Version
        // =============================================

        // Initialize DATE picker
        $('#punchin_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            maxDate: moment(),
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        });

        // Initialize TIME picker
        $('#punchin_time').datetimepicker({
            format: 'HH:mm',
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        });

        // Initialize Punch Out modal date/time pickers (avoid conflicts)
        $('#punchout_date').off('focus').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            maxDate: moment(),
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        });

        $('#punchout_time').datetimepicker({
            format: 'HH:mm',
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        });

        // When user clicks "Punch Out Now" icon → open modal directly (no confirm)
        $('body').on('click', '.punchoutnow', function() {
            var id = $(this).attr("value");

            // Set attendance ID
            $('#punchout_attendance_id').val(id);

            // Pre-fill current time (date already defaults to today)
            let now = moment().format('HH:mm');
            $('#punchout_time').val(now);

            // Open modal
            $('#punchOutModal').modal('show');
        });

        // Handle Punch Out form submit
        $('#punchOutForm').on('submit', function(e) {
            e.preventDefault();

            // Basic client-side validation
            if (!$('#punchout_date').val() || !$('#punchout_time').val()) {
                alert('Please select both date and time');
                return;
            }

            var formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: $('#punchout_attendance_id').val(),
                punchout_date: $('#punchout_date').val(),
                punchout_time: $('#punchout_time').val(),
                punchout_summary: $('#punchout_summary').val()
            };

            // Show loading state
            $('#submitPunchOut')
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ url('punchoutnow') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        // Auto-close modal + refresh table
                        $('#punchOutModal').modal('hide');
                        $('#getattendance').DataTable().draw();

                        // Reset form
                        $('#punchOutForm')[0].reset();
                    } else {
                        // Show error inside modal
                        $('#punchOutForm').prepend(
                            '<div class="alert alert-danger mt-3">' +
                            (response.message || 'Failed to punch out') +
                            '</div>'
                        );
                    }
                },
                error: function() {
                    $('#punchOutForm').prepend(
                        '<div class="alert alert-danger mt-3">Server error. Try again.</div>'
                    );
                },
                complete: function() {
                    // Reset button
                    $('#submitPunchOut')
                        .prop('disabled', false)
                        .html('Submit Punch Out');
                }
            });
        });

        // Clear form & remove any error messages when modal is closed
        $('#punchOutModal').on('hidden.bs.modal', function() {
            $('#punchOutForm')[0].reset();
            $('.alert').remove();
        });

        // =============================================
        // BRANCH FILTER AJAX (unchanged)
        // =============================================
        $("#branch_id").on('change', function() {
            var search_branches = $(this).val();
            $.ajax({
                url: "{{ url('reports/attendancereport') }}",
                data: {
                    "search_branches": search_branches
                },
                success: function(res) {
                    if (res.status == true) {
                        var select = $('#executive_id');
                        select.empty();
                        select.append('<option>Select User</option>');
                        $.each(res.users, function(k, v) {
                            select.append('<option value="' + v.id + '">' + v.name +
                                '</option>');
                        });
                        select.selectpicker('refresh');
                    }
                }
            });
        });

        // =============================================
        // Your existing DataTable + other handlers
        // =============================================
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table = $('#getattendance').DataTable({
            processing: true,
            serverSide: true,
            "order": [
                [0, 'desc']
            ],
            "retrieve": true,
            ajax: {
                url: "{{url('reports/attendancereport')}}",
                data: function(d) {
                    d.executive_id = $('#executive_id').val(),
                        d.start_date = $('#start_date').val(),
                        d.division_id = $('#division_id').val(),
                        d.department_id = $('#department_id').val(),
                        d.end_date = $('#end_date').val(),
                        d.status = $('#status').val(),
                        d.designation_id = $('#designation_id').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'user_id',
                    name: 'user_id',
                    "defaultContent": ''
                },
                {
                    data: 'action_status',
                    name: 'action_status',
                    "defaultContent": '',
                    className: 'td-actions text-center',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'users.employee_codes',
                    name: 'users.employee_codes',
                    "defaultContent": '-'
                },
                {
                    data: 'users.name',
                    name: 'users.name',
                    "defaultContent": '',
                    orderable: false
                },
                {
                    data: 'punchin_date',
                    name: 'punchin_date',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'punchin_time',
                    name: 'punchin_time',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'punchin_address',
                    name: 'punchin_address',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'punchout_time',
                    name: 'punchout_time',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'punchout_address',
                    name: 'punchout_address',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'worked_time',
                    name: 'worked_time',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'punchin_summary',
                    name: 'punchin_summary',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'working_type',
                    name: 'working_type',
                    "defaultContent": '',
                    orderable: false
                },
                {
                    data: 'current_status',
                    name: 'current_status',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'remark_status',
                    name: 'remark_status',
                    "defaultContent": '',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    "defaultContent": '',
                    className: 'td-actions text-center',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Filter changes
        $('#executive_id, #division_id, #department_id, #status, #start_date, #end_date, #designation_id').change(function() {
            table.draw();
        });
        // $('#executive_id').change(function(){
        //     table.draw();
        // });
        // $('#division_id').change(function(){
        //     table.draw();
        // });
        // $('#department_id').change(function(){
        //     table.draw();
        // });
        // $('#status').change(function(){
        //     table.draw();
        // });
        // $('#start_date').change(function(){
        //     table.draw();
        // });
        // $('#end_date').change(function(){
        //     table.draw();
        // });


        //new user filters end



        $('body').on('click', '.removePunchout', function() {
            var id = $(this).attr("value");
            var token = $("meta[name='csrf-token']").attr("content");
            if (!confirm("Are You sure want Punchout")) {
                return false;
            }
            $.ajax({
                url: "{{ url('removePunchout') }}",
                type: 'POST',
                data: {
                    _token: token,
                    id: id
                },
                success: function(data) {
                    $('.message').empty();
                    $('.alert').show();
                    if (data.status == 'success') {
                        $('.alert').addClass("alert-success");
                    } else {
                        $('.alert').addClass("alert-danger");
                    }
                    $('.message').append(data.message);
                    //oTable.draw();
                    table.draw();
                },
            });
        });

        $('body').on('click', '.deleteAttendance', function() {
            var id = $(this).attr("value");
            var token = $("meta[name='csrf-token']").attr("content");
            if (!confirm("Are You sure want to delete ?")) {
                return false;
            }
            $.ajax({
                url: "{{ url('attendances') }}" + '/' + id,
                type: 'DELETE',
                data: {
                    _token: token,
                    id: id
                },
                success: function(data) {
                    $('.alert').show();
                    if (data.status == 'success') {
                        $('.alert').addClass("alert-success");
                    } else {
                        $('.alert').addClass("alert-danger");
                    }
                    $('.message').append(data.message);
                    //oTable.draw();
                    table.draw();
                },
            });
        });



        //approve
        $('body').on('click', '.approve_status', function() {
            var id = $(this).attr("value");
            var token = $("meta[name='csrf-token']").attr("content");
            if (!confirm("Are You sure want Approve Attendance")) {
                return false;
            }

            $.ajax({
                url: "{{ url('approveAttendance')}}",
                type: 'POST',
                data: {
                    _token: token,
                    id: id
                },
                success: function(data) {
                    $('.message').empty();
                    $('.alert').show();
                    if (data.status == 'success') {
                        $('.alert').addClass("alert-success");
                    } else {
                        $('.alert').addClass("alert-danger");
                    }
                    $('.message').append(data.message);
                    //oTable.draw();
                    table.draw();
                },
            });
        });


        $('body').on('click', '.reject_status', function() {
            var id = $(this).attr("value");
            var token = $("meta[name='csrf-token']").attr("content");
            if (!confirm("Are You sure want reject Attendance")) {
                return false;
            } else {
                $('#attendance_id').val(id);
                $("#rejec_attendance").modal();
            }

        });
    })
    </script>
</x-app-layout>