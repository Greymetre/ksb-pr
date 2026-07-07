<x-app-layout>
  
  <style>
    .selection{
        z-index: 999 !important; 
    }
    a.custom-btn.create {
      background-color: #00aadb !important;
      font-size: 12px;
      padding: 13px 7px;
      font-weight: 500;
      border-radius: 5px;
      color: #000 !important;
      margin: 2px;
      text-align: center;
      line-height: normal;
      cursor: pointer;
      height: 42px;
      margin-top: 7px;
    }
.tour-card {
    background-color: #90caf9 !important;   /* clearer blue */
}
.beat-card {
    background-color: #a5d6a7 !important;   /* clearer green */
}
/* .objective-pill {
    background-color: #1976d2;
    color: #fff;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    margin: 2px;
    display: inline-block;
} */

.objective-pill {
    background-color: #1976d2;
    color: #fff;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    margin: 3px;
    display: inline-flex;
    align-items: center;
}

.objective-pill .remove-pill {
    margin-left: 6px;
    cursor: pointer;
    font-weight: bold;
}
    /* div#submitAttendance{
      z-index: 9;
    }*/
  </style>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title">Attendance Report
            <span class="">
              <div class="btn-group header-frm-btn">
                @if(auth()->user()->can(['attendance_download']))
                <form method="GET" action="{{ URL::to('attendance-download') }}">
                  <div class="d-flex flex-row">

                    <div class="p-2" style="width: 250px;">
                      <select class="selectpicker" multiple name="branch_id[]" id="branch_id" data-style="select-with-transition" title="Select Branch">
                        <option value="">Select Branch</option>
                        @if(@isset($branches ))
                        @foreach($branches as $branche)
                        <option value="{!! $branche['id'] !!}" {{ old( 'branch_id') == $branche['id'] ? 'selected' : '' }}>{!! $branche['name'] !!}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>

                    <div class="p-2" style="width: 250px;">
                    <select class="selectpicker" name="division_id" id="division_id" data-style="select-with-transition" title="Select Zone">
                     <option value="">Select Zone</option>
                    @if(@isset($divisions ))
                    @foreach($divisions as $division)
                     <option value="{!! $division['id'] !!}" {{ old( 'division') == $division['id'] ? 'selected' : '' }}>{!! $division['division_name'] !!}</option>
                    @endforeach
                    @endif
                   </select>
                  </div>

                    <div class="p-2" style="width: 250px;">
                      <select class="selectpicker" name="executive_id" id="executive_id" data-style="select-with-transition" title="Select User">
                        <option value="">Select User</option>
                        @if(@isset($users ))
                        @foreach($users as $user)
                        <option value="{!! $user['id'] !!}" {{ old( 'executive_id') == $user['id'] ? 'selected' : '' }}>{!! $user['name'] !!}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>

                    <div class="p-2" style="width: 250px;">
                      <select class="selectpicker" name="status" id="status" data-style="select-with-transition" title="Select User">
                        <option value="">Select Status</option>
                        <option value="0">Pending</option>
                        <option value="1">Approved</option>
                        <option value="2">Rejected</option>
                      </select>
                    </div>
                    <div class="p-2" style="width: 200px;">
                      <select class="selectpicker" name="type" id="type" data-style="select-with-transition" title="Select Type">
                        <option value="">All</option>
                        <option value="attendance">Attendance</option>
                        <option value="leave">Leave</option>
                      </select>
                    </div>

                    <div class="p-2" style="width: 250px;">
                      <select class="selectpicker"
                              name="designation_id[]"
                              id="designation_id"
                              multiple
                              data-style="select-with-transition"                              
                              data-live-search="true"
                              title="Select Designation">
                        @foreach($designations as $designation)
                          <option value="{{ $designation->id }}"
                            {{ in_array($designation->designation_name, ['ASR','DSR']) ? 'selected' : '' }}>
                            {{ $designation->designation_name }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="p-2">
                      <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off" readonly>
                    </div>
                    <div class="p-2">
                      <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly>
                    </div>
                    <div class="p-2">
                      <button class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.download') !!} Attendance">
                        <i class="material-icons">cloud_download</i>
                      </button>
                    </div>
                  </div>
                </form>
                @endif
                <div class="next-btn">
                  <div class="btn-group multi-a-r d-none">
                    <button class="btn btn-success btn-sm multiChange mr-1" data-status="1"  title="Approve">Approve</button>
                    <button class="btn btn-danger btn-sm multiChange mr-2" data-status="2" title="Reject">Reject</button>
                  </div>
                  @if(auth()->user()->can(['attendance_create']))

                  <a data-toggle="modal" data-target="#submitAttendance" class="custom-btn create" title="Punch In">
                    Punch In
                  </a>
                  @endif
                  <a href="{{ URL::to('attendance-location') }}" class="btn btn-just-icon btn-theme  d-none" title="Update Location"><i class="material-icons">add_location</i></a>
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
          <div class="alert " style="display: none;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span class="message"></span>
          </div>
          <div class="table-responsive">
            <table id="getattendance" class="table table-striped table-bordered table-hover w-100">
              <thead class=" text-primary">
                <th>No</th>
                <th>#</th>
                <th>User ID</th>
                <th>Status</th>
                <th>Employee Code</th>
                <th>User Name</th>
                <th>Punch in Date</th>
                <th>Punch In Time</th>
                <th>Punch In Address</th>
                <th>Punch Out Time</th>
                <th>Punch Out Address</th>
                <th>Working Time</th>
                <th>Punch In summary</th>
                <th>Working Type</th>
                <th>Attendance Status</th>
                <th>Remark</th>
                <th>Action</th>
                <th>From</th>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal -->
  <div class="modal fade bd-example-modal-lg" id="submitAttendance" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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


 <div class="row">

        <div class="col-md-12 d-none" id="no_plan_message">
    <div class="alert alert-warning text-center">
        No plan created for today
    </div>
</div>

        <!-- Tour Card (left) -->
        <div class="col-md-6 mb-3 d-none" id="tour_card">
            <div class="card tour-card border-info shadow-sm h-55">
                <div class="card-header bg-info text-white">
                    <i class="material-icons mr-2">card_travel</i> Today's Tour Plan
                </div>
                <div class="card-body text-dark">
                    <div class="row mb-2">
                        <div class="col-5"><strong>Tour / Plan:</strong></div>
                        <div class="col-7"><span id="tour_plan_display">-</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>City:</strong></div>
                        <div class="col-7"><span id="tour_city_display">-</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Objective:</strong></div>
                        <div class="col-7"><div id="tour_objective_display" class="d-flex flex-wrap gap-1"></div></div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Beat Card (right) -->
        <div class="col-md-6 mb-3 d-none" id="beat_card">
            <div class="card beat-card  border-primary shadow-sm h-55">
                <div class="card-header bg-primary text-white">
                    <i class="material-icons mr-2">location_city</i> Today's Beat / Area
                </div>
                <div class="card-body text-dark">
                    <div class="row mb-2">
                        <div class="col-5"><strong>Beat Name:</strong></div>
                        <div class="col-7"><span id="beat_name_display">-</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Area / Town:</strong></div>
                        <div class="col-7"><span id="beat_area_display">-</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5"><strong>Description:</strong></div>
                        <div class="col-7"><span id="beat_desc_display">-</span></div>
                    </div>

                </div>
            </div>
        </div>

    </div>
          <form method="POST" action="{{ route('submitAttendances') }}" enctype="multipart/form-data" id="createleadstagesForm"> @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">User</label>
                  <select class="form-control " name="user_id" id="user_id" style="width: 100%;" required>
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
        <div class="input_section">
          <label class="col-form-label">Punch In Date</label>
          <input type="text" name="punchin_date" id="punchin_date" 
                 class="form-control datepicker" 
                 value="{{ old('punchin_date') }}" 
                 placeholder="Select Date" 
                 autocomplete="off" readonly required>
        </div>
      </div>

      <div class="col-md-6">
        <div class="input_section">
          <label class="col-form-label">Punch In Time</label>
          <input type="text" name="punchin_time" id="punchin_time" 
                 class="form-control timepicker" 
                 value="{{ old('punchin_time') }}" 
                 placeholder="Select Time" 
                 autocomplete="off" required>
        </div>
      </div>
<div class="col-md-6">
    <div class="input_section">
        <label class="col-form-label">Tour Plan</label>
        
        <!-- Main readonly display field – shows both name + city -->
        <input type="text"
               readonly
               class="form-control bg-light"
               id="tour_display_input"
               name="tour_display"               
               value="{{ old('tour_name') ?  old('tour_city') : '' }}"
               placeholder="No tour planned"
               style="cursor: not-allowed; background-color: #f8f9fa;">

        <!-- Hidden fields that actually go to backend -->
        <input type="hidden" name="tour_id"   id="tourid"   value="{{ old('tourid') }}">
        <input type="hidden" name="tour_name" id="tour_name_hidden" value="{{ old('tour_name') }}">
        <!-- Optional: also send city if your backend needs it -->
        <input type="hidden" name="tour_city" id="tour_city_hidden" value="{{ old('tour_city') }}">
    </div>
</div>
              <!-- <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Working Type</label>
                  <select class="form-control " name="working_type" id="working_type" style="width: 100%;" required>
                    <option value="">Select Working Type</option>
                    <option value="Office Meeting" data-is-city="true">Office</option>
                    <option value="Local Market Visit" data-is-city="true">Local Market Visit</option>
                    <option value="Tour" data-is-city="true">Tour</option>
                    <option value="Retailer Visit" data-is-city="true">Retailer Visit</option>
                    <option value="Nukkad Meet" data-is-city="true">Nukkad Meet</option>
                    <option value="Field Demo" data-is-city="true">Field Demo</option>
                  </select>
                </div>
              </div> -->

              <div class="col-md-6">
    <div class="input_section">
        <label class="col-form-label">Objective</label>

        <!-- Pills container -->
        <div id="working_type_container" class="d-flex flex-wrap mb-2"></div>

        <!-- Add new -->
        <div class="d-flex">
            <input type="text" id="working_type_input" class="form-control mr-2" placeholder="Add Objective">
            <button type="button" id="add_working_type" class="btn btn-info btn-sm">Add</button>
        </div>

        <!-- Hidden input for backend -->
        <input type="hidden" name="working_type" id="working_type_hidden">
    </div>
</div>


              <div class="col-md-6" id="city_div" style="display: none;">
                <label class="col-form-label">Select City</label>
                <select class="form-control " name="city" id="city">

                </select>
              </div>
              <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Plan for the day</label>
                  <input type="text" name="punchin_summary" id="punchin_summary" class="form-control" value="{!! old( 'punchin_summary') !!}">
                </div>
              </div>
              <span id="tour_error" class="alert alert-danger d-none">No tour added for selected date</span>
              <span id="date_error" class="alert alert-danger d-none">You can punch in only today date.</span>
            </div>
            <button id="add_attend" class="btn btn-info save pull-right mt-2"> Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Punch Out Modal -->
<div class="modal fade" id="punchOutModal" tabindex="-1" role="dialog" aria-labelledby="punchOutModalLabel" aria-hidden="true">
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
                        <!-- <div class="col-md-12">
                            <div class="input_section">
                                <label class="col-form-label">Punch Out Date</label>
                                <input type="text" class="form-control datepicker" 
                                       id="punchout_date" name="punchout_date" 
                                       value="{{ date('Y-m-d') }}" readonly>
                            </div>
                        </div> -->

                        <div class="col-md-12 mt-3">
                            <div class="input_section">
                                <label class="col-form-label">Punch Out Time <span class="text-danger">*</span></label>
                                <input type="text" class="form-control timepicker" 
                                       id="punchout_time" name="punchout_time" 
                                       placeholder="Select Punch Out Time" required>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <div class="input_section">
                                <label class="col-form-label">Summary / Remarks (Optional)</label>
                                <textarea class="form-control" name="punchout_summary" 
                                          id="punchout_summary" rows="3" 
                                          placeholder="Any remarks..."></textarea>
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


  <div class="modal fade bd-example-modal-lg" id="rejec_attendance" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
          <form method="POST" action="{{ route('rejectAttendance') }}" enctype="multipart/form-data" id="createleadstagesForm_new"> @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Remark</label>
                  <input type="text" name="remark_status" id="remark_status" class="form-control" value="{!! old( 'remark_status') !!}" required> <br><br>
                  <input type="text" name="attendance_id" id="attendance_id" class="form-control" hidden>

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

    $('').on('shown.bs.modal', function () {
        $('#user_id_c').select2('destroy').select2();  // re-init
         $('#tour_card').addClass('d-none');
    $('#beat_card').addClass('d-none');
    $('#no_plan_message').addClass('d-none');
    });

      //new user filters  starts

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
      var isSuperAdmin = @json(Auth::user()->hasRole('subAdmin'));

      console.log(isSuperAdmin);

      var columns = [
        {
          data: 'DT_RowIndex',
          name: 'DT_RowIndex',
          orderable: false,
          searchable: false
        },
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
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
        data: 'users.employee_codes',           // ← NEW
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
      ];

      

      if (isSuperAdmin) {
        columns.push({
          data: 'punchin_from',
          name: 'punchin_from',
          defaultContent: '',
          className: 'td-actions text-center',
          orderable: false,
          searchable: false
        });
      }

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
            d.branch_id = $('#branch_id').val(); 
            d.division_id = $('#division_id').val(),
            d.executive_id = $('#executive_id').val(),
            d.designation_id = $('#designation_id').val();
              d.active = $('#active').val(),
              d.start_date = $('#start_date').val(),
              d.end_date = $('#end_date').val(),
              d.status = $('#status').val(),
              d.type = $('#type').val(); 
          }
        },
        columns: columns

      });


      $('#designation_id').on('change', function () {
          let designation_id = $(this).val();

          $.ajax({
              url: "{{ url('getUsersByDesignation') }}",
              type: "GET",
              data: { designation_id: designation_id },
              success: function(res) {
                  let select = $('#executive_id');
                  select.empty();
                  select.append('<option value="">Select User</option>');

                  res.users.forEach(function(user) {
                      select.append(`<option value="${user.id}">${user.name}</option>`);
                  });

                  select.selectpicker('refresh');
              }
          });
      });
      $('#branch_id').change(function() {
          table.draw();   // ✅ ADD THIS
      });
      $('#type').change(function() {
          table.draw();
      });
      

      $('#division_id').change(function() {
        table.draw();
      });
      $('#executive_id').change(function() {
        table.draw();
      });
      $('#active').change(function() {
        table.draw();
      });
      $('#status').change(function() {
        table.draw();
      });
      $('#designation_id').change(function() {
          table.draw();
      });

      $('#start_date').change(function() {
        table.draw();
      });
      $('#end_date').change(function() {
        table.draw();
      });


      //new user filters end



      $('body').on('click', '.removePunchout', function() {
        var id = $(this).attr("value");
        var token = $("meta[name='csrf-token']").attr("content");
        if (!confirm("Are You sure want to remove Punchout")) {
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

      // $('body').on('click', '.punchoutnow', function() {
      //   var id = $(this).attr("value");
      //   var token = $("meta[name='csrf-token']").attr("content");
      //   if (!confirm("Are You sure want Punchout Now?")) {
      //     return false;
      //   }
      //   $.ajax({
      //     url: "{{ url('punchoutnow') }}",
      //     type: 'POST',
      //     data: {
      //       _token: token,
      //       id: id
      //     },
      //     success: function(data) {
      //       $('.message').empty();
      //       $('.alert').show();
      //       if (data.status == 'success') {
      //         $('.alert').addClass("alert-success");
      //       } else {
      //         $('.alert').addClass("alert-danger");
      //       }
      //       $('.message').append(data.message);
      //       //oTable.draw();
      //       table.draw();
      //     },
      //   });
      // });


      // When user clicks "Punch Out Now" icon → open modal directly (no confirm)
$('body').on('click', '.punchoutnow', function () {
    var id = $(this).attr("value");

    // Set the attendance ID in hidden field
    $('#punchout_attendance_id').val(id);

    // Optional: pre-fill punch out time with current time
    let now = moment().format('HH:mm');
    $('#punchout_time').val(now);

    // Open the modal immediately — no confirmation
    $('#punchOutModal').modal('show');
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

      $('body').on('click', '.multiChange', function () {
      const selectedValues = [];
        $('.row-checkbox:checked').each(function () {
            selectedValues.push($(this).val());
        });
        if(selectedValues.length == 0){
          alert("Please select at least one record");
          return false;
        }
        const status = $(this).data('status');

        var token = $("meta[name='csrf-token']").attr("content");
        if(status == 1){
          if(!confirm("Are You sure want to approve "+selectedValues.length+" attaendance?")) {
             return false;
          }
          $.ajax({
            url: "{{ url('approveAttendance')}}",
            type: 'POST',
            data: {
              _token: token,
              id: selectedValues.toString()
            },
            success: function(data) {
              table.draw();
              $('.message').empty();
              $('.alert').show();
              if (data.status == 'success') {
                $('.alert').addClass("alert-success");
              } else {
                $('.alert').addClass("alert-danger");
              }
              $('.message').append(data.message);
            },
          });
        }else{
          if(!confirm("Are You sure want to reject "+selectedValues.length+" attaendance?")) {
             return false;
          }
          $('#attendance_id').val(selectedValues);
          $("#rejec_attendance").modal();
        }        
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
            console.log(data);
            table.draw();
            $('.message').empty();
            $('.alert').show();
            if (data.status == 'success') {
              $('.alert').addClass("alert-success");
            } else {
              $('.alert').addClass("alert-danger");
            }
            $('.message').append(data.message);
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

$('#punchout_time').datetimepicker({
        format: 'HH:mm',
        useCurrent: false,
        icons: {
            time: "fa fa-clock-o",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });

    // When "Punch Out Now" button is clicked → open modal
$('body').on('click', '.punchoutnow', function () {
    var id = $(this).attr("value");

    // Set attendance ID
    $('#punchout_attendance_id').val(id);

    // Optional: pre-fill with current time
    let now = moment().format('HH:mm');
    $('#punchout_time').val(now);

    // Open modal
    $('#punchOutModal').modal('show');
});

    // Handle Punch Out form submit
$('#punchOutForm').on('submit', function (e) {
    e.preventDefault();

    var formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        id: $('#punchout_attendance_id').val(),
        punchout_date: $('#punchout_date').val(),
        punchout_time: $('#punchout_time').val(),
        punchout_summary: $('#punchout_summary').val()
    };

    $('#submitPunchOut')
        .prop('disabled', true)
        .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: "{{ url('punchoutnow') }}",
        type: 'POST',
        data: formData,
        success: function (response) {
            if (response.status === 'success') {
                $('#punchOutModal').modal('hide');           // ← closes modal
                $('#getattendance').DataTable().draw();      // ← refreshes table
                $('#punchOutForm')[0].reset();               // ← clears form
            } else {
                $('#punchOutForm').prepend(
                    '<div class="alert alert-danger mt-3">' +
                    (response.message || 'Failed to punch out') +
                    '</div>'
                );
            }
        },
        error: function () {
            $('#punchOutForm').prepend(
                '<div class="alert alert-danger mt-3">Server error. Try again.</div>'
            );
        },
        complete: function () {
            $('#submitPunchOut')
                .prop('disabled', false)
                .html('Submit Punch Out');
        }
    });
});

// Clear form & errors when modal closes
$('#punchOutModal').on('hidden.bs.modal', function () {
    $('#punchOutForm')[0].reset();
    $('.alert').remove(); // remove any error messages inside modal
});

    });

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
              select.append('<option value="' + v.id + '" >' + v.name + '</option>');
            });
            select.selectpicker('refresh');
          }
        }
      });

    })

    // $(document).on("dp.change", "#punchin_date", function(e) {
    //   var formatedValue = moment(e.date).format('YYYY-MM-DD');
    //   var todayDate = moment().format('YYYY-MM-DD');

    //   var user_id = $("#user_id").val();
    //   if (user_id && user_id != null && user_id != '') {
    //     if (user_id == '{{auth()->user()->id}}') {
    //       if (formatedValue === todayDate) {
    //         $("#date_error").addClass('d-none');
    //         $("#add_attend").prop('disabled', false);
    //       } else {
    //         $("#date_error").removeClass('d-none');
    //         $("#add_attend").prop('disabled', true);
    //         return false;
    //       }
    //     }
    //     var tour_plan = getTourPlanByUserAndDate(formatedValue, user_id);
    //   }
    // });

    // $(document).on("change", "#user_id", function(e) {
    //   var selectedDate = $('#punchin_date').val();
    //   if (selectedDate && selectedDate != null && selectedDate != '') {
    //     var formatedValue = moment(selectedDate).format('YYYY-MM-DD');
    //     console.log(formatedValue);
    //     var todayDate = moment().format('YYYY-MM-DD');

    //     var user_id = $(this).val();
    //     if (user_id == '{{auth()->user()->id}}') {
    //       if (formatedValue === todayDate) {
    //         $("#date_error").addClass('d-none');
    //         $("#add_attend").prop('disabled', false);
    //       } else {
    //         $("#date_error").removeClass('d-none');
    //         $("#add_attend").prop('disabled', true);
    //         return false;
    //       }
    //     }
    //     var tour_plan = getTourPlanByUserAndDate(formatedValue, user_id);
    //   }
    // });

  //   $("#punchin_date, #user_id").on("change dp.change", function() {
  //   let selectedDate = $('#punchin_date').val();   // YYYY-MM-DD
  //   let today      = moment().format('YYYY-MM-DD');
  //   let user_id    = $("#user_id").val();

  //   if (!selectedDate) return;

  //   if (user_id == '{{ auth()->user()->id }}') {
  //     if (selectedDate === today) {
  //       $("#date_error").addClass('d-none');
  //       $("#add_attend").prop('disabled', false);
  //     } else {
  //       $("#date_error").removeClass('d-none');
  //       $("#add_attend").prop('disabled', true);
  //       return;
  //     }
  //   }

  //   // Tour plan check (date only भेज रहे हैं)
  //   getTourPlanByUserAndDate(selectedDate, user_id);
  // });



    $("#working_type").on("change", function() {
      var selectedOption = $(this).find('option:selected');
      var is_city = selectedOption.data('is-city');

      if (is_city == true) {
        var user_id = $("#user_id").val();
        if (user_id && user_id != null && user_id != '') {
          $.ajax({
            url: "{{ url('userCityList') }}",
            dataType: "json",
            type: "POST",
            data: {
              _token: "{{csrf_token()}}",
              user_id: user_id
            },
            success: function(res) {
              if (res.status == 'success') {
                $("#city_div").show();
                var html = '<option value="">Select City</option>';
                $.each(res.data, function(index, element) {
                  console.log(element);
                  html += '<option value="' + element.id + '">' + element.city_name + '</option>';
                });
                $("#city").html(html);
              }

            }
          });
        }
      }
    });
    


function loadTourAndBeat(date, user_id) {
    if (!date || !user_id) {
        // resetCards();
        return;
    }

    $.ajax({
        url: "{{ url('get-tour-and-beat-plan') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            date: date,
            user_id: user_id
        },


        success: function(res) {

            // hide everything first
            $('#tour_card').addClass('d-none');
            $('#beat_card').addClass('d-none');
            $('#no_plan_message').addClass('d-none');

            let hasTour = res.tour && res.tour.exists;
            let hasBeat = res.beat && res.beat.exists;

            // TOUR CARD
            if (hasTour) {

    // hidden fields
    $('#tourid').val(res.tour.data.id || '');
    $('#tour_name_hidden').val(res.tour.data.name || '');
    $('#tour_city_hidden').val(res.tour.data.city_name || '');

    // card display
    $('#tour_plan_display').text(res.tour.data.name || '-');
    $('#tour_city_display').text(res.tour.data.city_name || '-');
    let objectives = res.tour.data.objectives;
let objectiveContainer = $('#tour_objective_display');
objectiveContainer.empty();

if (objectives) {
    let items = objectives.split(',');

    workingTypes = []; // reset

    items.forEach(function(item) {
        let trimmed = item.trim();

        if (trimmed !== '') {
            // Show in TOUR CARD
            objectiveContainer.append(
                `<span class="objective-pill">${trimmed}</span>`
            );

            // Add to WORKING TYPE
            workingTypes.push(trimmed);
        }
    });

    renderWorkingTypes();
} else {
    objectiveContainer.html('<span>-</span>');
}

    // form input display (city name like before)
    $('#tour_display_input').val(res.tour.data.city_name || 'Planned Tour');

    $('#tour_card').removeClass('d-none');
            }else {

    // jab tour nahi mile tab bhi input update karo
    $('#tourid').val('');
    $('#tour_name_hidden').val('');
    $('#tour_city_hidden').val('');

    $('#tour_display_input').val('No Tour Planned');
}

            // BEAT CARD
            if (hasBeat) {

                $('#beat_name_display').text(res.beat.data.beat_name || '-');
                $('#beat_area_display').text(res.beat.data.area_town || '-');
                $('#beat_desc_display').text(res.beat.data.description || '-');

                $('#beat_card').removeClass('d-none');
            }

            // NO PLAN
            if (!hasTour && !hasBeat) {
                $('#no_plan_message').removeClass('d-none');
            }

        }
    //     success: function(res) {

       
    //        // ── Tour Card ───────────────────────────────────
    // if (res.tour.exists && res.tour.data) {
    //     // Hidden fields for form submission
    //     $('#tourid').val(res.tour.data.id || '');
    //     $('#tour_name_hidden').val(res.tour.data.name || '');
    //     $('#tour_city_hidden').val(res.tour.data.city_name || '');

    //     // Card display
    //     $('#tour_plan_display').text(res.tour.data.name || 'Planned Tour');
    //     $('#tour_city_display').text(res.tour.data.city_name || '-');
    //     $('#tour_objective_display').text(res.tour.data.objectives || '-');
    //     $('#tour_not_found').addClass('d-none');

    //     // Form visible field – shows name + city
    //     let displayText = res.tour.data.city_name || 'Planned Tour';
    //     if (res.tour.data.city_name) {
    //        res.tour.data.city_name;
    //     }
      
    //     $('#tour_display_input').val(displayText);
    // } 
    // else {
    //     // No tour
    //     $('#tourid').val('');
    //     $('#tour_name_hidden').val('');
    //     $('#tour_city_hidden').val('');

    //     $('#tour_plan_display').text('No tour planned');
    //     $('#tour_city_display').text('-');
    //     $('#tour_objective_display').text('-');
    //     $('#tour_not_found').removeClass('d-none');

    //     $('#tour_display_input').val('No tour planned');
    // }

    //         // ── Beat Card ───────────────────────────────────
    //         if (res.beat.exists && res.beat.data) {
    //             $('#beat_name_display').text(res.beat.data.beat_name || '-');
    //             $('#beat_area_display').text(res.beat.data.area_town || '-');
    //             $('#beat_desc_display').text(res.beat.data.description || '-');
    //             $('#beat_not_found').addClass('d-none');
    //         } else {
    //             $('#beat_name_display').text('-');
    //             $('#beat_area_display').text('-');
    //             $('#beat_desc_display').text('-');
    //             $('#beat_not_found').removeClass('d-none');
    //         }
    //     },
    //     error: function() {
    //         resetCards();
    //     }
    });
}

let workingTypes = [];

// Render pills
function renderWorkingTypes() {
    let container = $('#working_type_container');
    container.empty();

    workingTypes.forEach(function(item, index) {
        container.append(`
            <span class="objective-pill">
                ${item}
                <span class="remove-pill" data-index="${index}">&times;</span>
            </span>
        `);
    });

    // Update hidden input (comma separated)
    $('#working_type_hidden').val(workingTypes.join(','));
}

function resetCards() {
    $('#tourid').val('');
    $('#tour_plan_display').text('No tour planned');
    $('#tour_city_display').text('-');
    $('#tour_objective_display').text('-');
    $('#tour_not_found').removeClass('d-none');

    $('#beat_name_display').text('-');
    $('#beat_area_display').text('-');
    $('#beat_desc_display').text('-');
    $('#beat_not_found').removeClass('d-none');
}

$('#add_working_type').on('click', function () {
    let value = $('#working_type_input').val().trim();

    if (value !== '') {
        workingTypes.push(value);
        $('#working_type_input').val('');
        renderWorkingTypes();
    }
});

$(document).on('click', '.remove-pill', function () {
    let index = $(this).data('index');
    workingTypes.splice(index, 1);
    renderWorkingTypes();
});

// Trigger when date or user changes
$("#punchin_date, #user_id").on("change dp.change", function() {
    let selectedDate = $('#punchin_date').val();
    let today        = moment().format('YYYY-MM-DD');
    let user_id      = $("#user_id").val();

    if (!selectedDate || !user_id) {
        resetCards();
        return;
    }

    // Only allow today for current user (your existing logic)
    if (user_id == '{{ auth()->user()->id }}') {
        if (selectedDate !== today) {
            $("#date_error").removeClass('d-none');
            $("#add_attend").prop('disabled', true);
            resetCards();
            return;
        }
        $("#date_error").addClass('d-none');
        $("#add_attend").prop('disabled', false);
    }

    loadTourAndBeat(selectedDate, user_id);
});

    $(document).on('click', '.row-checkbox', function () {
        
        const selectedValues = [];
        $('.row-checkbox:checked').each(function () {
            selectedValues.push($(this).val());
        });

        if(selectedValues.length > 0){
          $(".multi-a-r").removeClass('d-none');
        }else{
          $(".multi-a-r").addClass('d-none');
        }
    });

    $("#checkAll").on("click", function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
    $(".multi-a-r").toggleClass('d-none');
});


  </script>
  
</x-app-layout>