<x-app-layout>
  <div class="row">
    <div class="col-md-12">
      <div class="fk-list-page-head">
        <div class="fk-list-heading-block">
          <div class="fk-list-breadcrumb">
            <span>User Management</span>
            <span>&rsaquo;</span>
            <span class="fk-current">{!! trans('panel.user.title_singular') !!} {!! trans('panel.global.list') !!}</span>
          </div>
          <div class="fk-list-title-row">
            <h1 class="fk-list-title">{!! trans('panel.user.title_singular') !!} {!! trans('panel.global.list') !!}</h1>
            <span class="fk-list-count" id="user-record-count"></span>
          </div>
        </div>
        <div class="fk-list-actions">
          @if(auth()->user()->can(['user_download']))
          <button class="btn fk-filter-trigger" type="button" data-filter-target="#user-filter-drawer">
            <span class="material-icons">tune</span>
            <span>Filters</span>
          </button>
          @endif
          @if(auth()->user()->can(['user_create']))
          <a href="{{ route('users.create') }}" class="btn fk-create-action" title="{!!  trans('panel.global.add') !!} {!! trans('panel.user.title_singular') !!}">
            <span class="material-icons">add_circle</span>
            <span>Add New {!! trans('panel.user.title_singular') !!}</span>
          </a>
          @endif
        </div>
      </div>
      <div class="card fk-listing-card fk-user-listing-card" data-fk-listing-ready="1">
      <div class="card-body">
        @if(auth()->user()->can(['user_download']))
        <aside class="fk-filter-drawer" id="user-filter-drawer">
          <div class="fk-filter-drawer-head">
            <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
            <div>
              <h3>Advanced Filters</h3>
              <p>Applied live to the directory</p>
            </div>
            <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
          </div>
          <div class="fk-filter-drawer-body">
            <form action="{{ URL::to('users-download') }}" method="get" enctype="multipart/form-data" id="user-filter-export-form">
              <div class="d-flex flex-wrap flex-row">
                <div class="p-2" data-label="Zone">
                  <div class="dropdown bootstrap-select">
                    <select class="select2" name="division_id" id="division_id" data-style="select-with-transition" title="Select Zone">
                      <option class="bs-title-option" value="">Zone</option>
                      @foreach($divisions as $division)
                      <option value="{{$division->id}}">{{$division->division_name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="p-2" data-label="Branch">
                  <div class="dropdown bootstrap-select">
                    <select class="select2" name="branch_id" id="branch_id" data-style="select-with-transition" title="Select Branch">
                      <option class="bs-title-option" value="">Branch</option>
                      @foreach($branches as $branche)
                      <option value="{{$branche->id}}">{{$branche->branch_name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="p-2" data-label="Department">
                  <div class="dropdown bootstrap-select">
                    <select class="select2" name="department_id" id="department_id" data-style="select-with-transition" title="Select Department">
                      <option class="bs-title-option" value="">department</option>
                      @foreach($departments as $department)
                      <option value="{{$department->id}}">{{$department->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="p-2" data-label="Employee">
                  <div class="dropdown bootstrap-select">
                    <select class="select2" name="user_type" id="user_type" data-style="select-with-transition" title="Select User type">
                      <option class="bs-title-option" value="employee">Employee</option>
                      <option class="bs-title-option" value="customer">Customer</option>
                    </select>
                  </div>
                </div>
                <div class="p-2" data-label="Status">
                  <select class="selectpicker" name="active" id="active" data-style="select-with-transition" title="Select User Status">
                    <option value="">Select User Status</option>
                    <option value="Y">Active</option>
                    <option value="N">Inactive</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="fk-filter-drawer-tools">
            @if(auth()->user()->can(['user_template']))
            <a href="{{ URL::to('users-template') }}" class="btn fk-tool-template" title="{!!  trans('panel.global.template') !!} {!! trans('panel.user.title_singular') !!}">
              <span class="material-icons">description</span>
              <span>Template</span>
            </a>
            @endif
            @if(auth()->user()->can(['user_upload']))
            <form action="{{ URL::to('users-upload') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
              {{ csrf_field() }}
              <label class="btn fk-upload-tool fk-tool-upload">
                <span class="material-icons">cloud_upload</span>
                <span>Import</span>
                <input type="file" name="import_file" required accept=".xls,.xlsx" />
              </label>
              <button type="submit" class="fk-hidden-submit">Upload</button>
            </form>
            @endif
            @if(auth()->user()->can(['user_download']))
            <button class="btn fk-tool-export" type="submit" form="user-filter-export-form">
              <span class="material-icons">cloud_download</span>
              <span>Export</span>
            </button>
            @endif
            @if(auth()->user()->can('user_feedback_calling'))
            <button class="btn fk-tool-calling" type="button" id="openCallingReport">
              <span class="material-icons">support_agent</span>
              <span>Calling Report</span>
            </button>
            @endif
          </div>
          <div class="fk-filter-drawer-foot">
            <button class="btn fk-filter-reset" type="button">Reset</button>
            <button class="btn fk-filter-apply" type="button">Apply Filters</button>
          </div>
        </aside>
        @endif
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
          <table id="getuser" class="table table-striped- table-bordered table-hover table-checkable no-wrap">
            <thead class=" text-primary">
              <th>{!! trans('panel.global.no') !!}</th>
              <th>{!! trans('panel.global.action') !!}</th>
              <th>{!! trans('panel.global.active') !!}</th>
              <th>Emp Code</th>
              <th>{!! trans('panel.global.name') !!}</th>
              <th>{!! trans('panel.sidemenu.branches') !!}</th>
              <th>Designation</th>
              <th>Zone</th>
              <th>Whats App</th>
              <th>{!! trans('panel.global.mobile') !!}</th>
              <th>{!! trans('panel.global.email') !!}</th>
              <th>Reporting Person</th>
              <th>Joining Date</th>
              <th>Roles</th>
              <th>Password</th>
              <th>{!! trans('panel.global.profile') !!}</th>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <style>
    /* Row action: same footprint as edit/delete, green "call" tint that works on both shells */
    .fk-call-user, .fk-call-user:hover, .fk-call-user:focus {
      background: rgba(34, 197, 94, .14) !important; color: #22c55e !important;
      border: 1px solid rgba(34, 197, 94, .32) !important; box-shadow: none !important;
    }

    /* Calling details popup — built on the CRM theme variables (fk-dark-shell / fk-light-shell) */
    .fk-call-modal .modal-dialog { max-width: 980px; margin-top: 28px; margin-bottom: 28px; }
    .fk-call-modal .modal-content { overflow: hidden; }
    .fk-call-modal .fk-call-head {
      display: flex; align-items: center; gap: 14px; padding: 18px 22px;
      border-bottom: 1px solid var(--fk-list-border, rgba(120,160,255,.14));
    }
    .fk-call-modal .fk-call-avatar {
      width: 46px; height: 46px; flex: 0 0 46px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, rgba(34,197,94,.22), rgba(34,211,238,.16));
      border: 1px solid rgba(34,197,94,.35); color: #22c55e;
    }
    .fk-call-modal .fk-call-title { flex: 1; min-width: 0; }
    .fk-call-modal .fk-call-title h4 {
      margin: 0; color: var(--fk-list-heading); font-family: 'Sora', 'Inter', sans-serif;
      font-size: 18px; font-weight: 700;
    }
    .fk-call-modal .fk-call-title p {
      margin: 3px 0 0; color: var(--fk-list-muted); font-size: 13px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .fk-call-modal .fk-call-title p b { color: var(--fk-list-accent); font-weight: 600; }
    .fk-call-modal .fk-call-close {
      width: 38px; height: 38px; flex: 0 0 38px; border-radius: 12px; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      background: var(--fk-form-bg); border: 1px solid var(--fk-form-border); color: var(--fk-list-soft);
      transition: all .18s ease;
    }
    .fk-call-modal .fk-call-close:hover { background: rgba(255,93,122,.14); border-color: rgba(255,93,122,.45); color: #ff5d7a; }

    .fk-call-modal .modal-body { padding: 18px 22px 6px; max-height: calc(100vh - 220px); overflow-y: auto; }
    .fk-call-modal .fk-call-toolbar {
      display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 18px;
    }
    .fk-call-modal .fk-call-toolbar .form-group { margin: 0 !important; width: 200px; }
    body.fk-dark-shell .fk-call-modal #callDate { color-scheme: dark; }
    .fk-call-modal .fk-call-status {
      display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 14px; border-radius: 999px;
      font-size: 12px; font-weight: 600; letter-spacing: .4px;
    }
    .fk-call-modal .fk-call-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .fk-call-modal .fk-call-status.is-present { color: #22c55e; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.32); }
    .fk-call-modal .fk-call-status.is-open { color: #f59e0b; background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.32); }
    .fk-call-modal .fk-call-status.is-absent { color: #ff5d7a; background: rgba(255,93,122,.12); border: 1px solid rgba(255,93,122,.32); }

    .fk-call-modal .fk-call-section {
      margin-bottom: 14px; padding: 14px 16px; border-radius: 14px;
      background: var(--fk-list-row); border: 1px solid var(--fk-list-border);
    }
    .fk-call-modal .fk-call-section-title {
      display: flex; align-items: center; gap: 8px; margin: 0 0 12px;
      color: var(--fk-form-label); font-family: 'Sora', 'Inter', sans-serif;
      font-size: 10px; font-weight: 600; letter-spacing: 1.8px; text-transform: uppercase;
    }
    .fk-call-modal .fk-call-section-title .material-icons { font-size: 16px; color: var(--fk-list-accent); }
    .fk-call-modal .fk-call-info { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px 18px; }
    .fk-call-modal .fk-call-info .is-wide { grid-column: span 2; }
    .fk-call-modal .fk-call-info span {
      display: block; color: var(--fk-list-muted); font-size: 11px; letter-spacing: .3px; margin-bottom: 3px;
    }
    .fk-call-modal .fk-call-info b {
      display: block; color: var(--fk-list-text); font-size: 14px; font-weight: 500; word-break: break-word;
    }

    .fk-call-modal .fk-call-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
    .fk-call-modal .fk-call-stat {
      position: relative; padding: 14px 16px; border-radius: 14px; overflow: hidden;
      background: var(--fk-list-row); border: 1px solid var(--fk-list-border);
    }
    .fk-call-modal .fk-call-stat::after {
      content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
      background: linear-gradient(180deg, var(--fk-list-accent), var(--fk-list-accent-2, #3b82f6));
    }
    .fk-call-modal .fk-call-stat .material-icons { float: right; font-size: 20px; color: var(--fk-list-dim); }
    .fk-call-modal .fk-call-stat strong {
      display: block; color: var(--fk-list-heading); font-family: 'Sora', 'Inter', sans-serif; font-size: 24px; font-weight: 700; line-height: 1.1;
    }
    .fk-call-modal .fk-call-stat span { display: block; margin-top: 4px; color: var(--fk-list-muted); font-size: 12px; }

    .fk-call-modal .fk-call-notes { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .fk-call-modal .fk-call-notes .form-group { margin-bottom: 8px !important; }
    .fk-call-modal .modal-footer {
      display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px 18px;
      border-top: 1px solid var(--fk-list-border);
    }
    .fk-call-modal .modal-footer .btn { margin: 0; }
    .fk-call-modal .fk-call-empty { padding: 30px 0; text-align: center; color: var(--fk-list-muted); }
    .fk-call-modal .fk-call-loading { opacity: .45; pointer-events: none; transition: opacity .15s ease; }

    /* Calling Report button sits on its own full-width row under Template / Import / Export */
    body.fk-shell .fk-filter-drawer-tools .fk-tool-calling { order: 4; grid-column: 1 / -1; }
    body.fk-shell .fk-filter-drawer-tools .fk-tool-calling .material-icons { color: #22c55e; }

    #callingReportModal .modal-dialog { max-width: 480px; }
    #callingReportModal .fk-call-range { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    #callingReportModal .fk-call-range .form-group { margin-bottom: 0 !important; }
    #callingReportModal .fk-call-hint { margin: 14px 0 8px; color: var(--fk-list-muted); font-size: 12px; line-height: 1.5; }
    body.fk-dark-shell #callingReportModal input[type="date"] { color-scheme: dark; }

    @media (max-width: 991px) {
      .fk-call-modal .fk-call-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .fk-call-modal .fk-call-info { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575px) {
      .fk-call-modal .fk-call-info, .fk-call-modal .fk-call-notes { grid-template-columns: 1fr; }
      .fk-call-modal .fk-call-info .is-wide { grid-column: auto; }
      .fk-call-modal .fk-call-toolbar .form-group { width: 100%; }
    }
  </style>

  <div class="modal fade fk-call-modal" id="callingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="callTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="fk-call-head">
          <div class="fk-call-avatar"><i class="material-icons">call</i></div>
          <div class="fk-call-title">
            <h4 id="callTitle">Calling Details</h4>
            <p id="callSubtitle"></p>
          </div>
          <button type="button" class="fk-call-close" data-dismiss="modal" aria-label="Close"><i class="material-icons">close</i></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="callUserId">
          <div class="fk-call-toolbar">
            <div class="form-group">
              <label for="callDate">Date</label>
              <input type="date" class="form-control" id="callDate" max="{{ date('Y-m-d') }}">
            </div>
            <span class="fk-call-status" id="callStatus" style="display:none"></span>
          </div>
          <div id="callContent"></div>
          <div class="fk-call-notes">
            <div class="form-group">
              <label for="callRemark">Calling Remark</label>
              <textarea class="form-control" id="callRemark" placeholder="What did the employee say on the call?"></textarea>
            </div>
            <div class="form-group">
              <label for="callIssue">Issue</label>
              <textarea class="form-control" id="callIssue" placeholder="Any issue raised by the employee"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="callSubmit">Save Remark</button>
        </div>
      </div>
    </div>
  </div>

  @if(auth()->user()->can('user_feedback_calling'))
  <div class="modal fade fk-call-modal" id="callingReportModal" tabindex="-1" role="dialog" aria-labelledby="callReportTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="{{ route('users.calling.report') }}" method="get" id="callingReportForm">
          <div class="fk-call-head">
            <div class="fk-call-avatar"><i class="material-icons">support_agent</i></div>
            <div class="fk-call-title">
              <h4 id="callReportTitle">Calling Report Export</h4>
              <p>Calling remarks with each employee's day summary</p>
            </div>
            <button type="button" class="fk-call-close" data-dismiss="modal" aria-label="Close"><i class="material-icons">close</i></button>
          </div>
          <div class="modal-body">
            <div class="fk-call-range">
              <div class="form-group">
                <label for="callReportStart">Start Date</label>
                <input type="date" class="form-control" name="start_date" id="callReportStart" required max="{{ date('Y-m-d') }}">
              </div>
              <div class="form-group">
                <label for="callReportEnd">End Date</label>
                <input type="date" class="form-control" name="end_date" id="callReportEnd" required max="{{ date('Y-m-d') }}">
              </div>
            </div>
            <p class="fk-call-hint">One row per saved calling remark in this date range.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="material-icons" style="font-size:18px;vertical-align:middle">download</i> Download
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
  <script type="text/javascript">
    $(function() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      var table = $('#getuser').DataTable({
        processing: true,
        serverSide: true,
        "order": [
          [0, 'desc']
        ],
        ajax: {
          url: "{{ route('users.index') }}",
          data: function (d) {
                d.user_type = $('#user_type').val()
                d.active = $('#active').val()
                d.division_id = $('#division_id').val()
                d.branch_id = $('#branch_id').val()
                d.department_id = $('#department_id').val()
            }
        },
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'action',
            name: 'action',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'active',
            name: 'active',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'employee_codes',
            name: 'employee_codes',
            "defaultContent": '',
            orderable: false,
          },
          {
            data: 'name',
            name: 'name',
            "defaultContent": '',
            orderable: false
          },
          {
            data: 'getBranchNames',
            name: 'getBranchNames',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'getdesignation.designation_name',
            name: 'getdesignation.designation_name',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'getdivision.division_name',
            name: 'getdivision.division_name',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'wahtsappmobile',
            name: 'wahtsappmobile',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'mobile',
            name: 'mobile',
            "defaultContent": '',
            orderable: false
          },
          {
            data: 'email',
            name: 'email',
            "defaultContent": '',
            orderable: false
          },
          {
            data: 'reportinginfo.name',
            name: 'reportinginfo.name',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'userinfo.date_of_joining',
            name: 'userinfo.date_of_joining',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'roles',
            name: 'roles',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'password_string',
            name: 'password_string',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'image',
            name: 'image',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
        ]
      });
      function updateUserRecordCount() {
        var info = $('#getuser_info').text() || '';
        var match = info.match(/\bof\s+([\d,]+)\b/i);
        if (match && match[1]) {
          $('#user-record-count').text(match[1].replace(/,/g, '') + ' records').addClass('is-visible');
        }
      }
      table.on('draw.dt', updateUserRecordCount);
      setTimeout(updateUserRecordCount, 600);
      setTimeout(updateUserRecordCount, 1600);
      $('#user_type').change(function(){
        table.draw();
      });
      $('#active').change(function(){
        table.draw();
      });
      $('#division_id').change(function(){
        table.draw();
      });
      $('#branch_id').change(function(){
        table.draw();
      });
      $('#department_id').change(function(){
        table.draw();
      });
      $('.fk-filter-apply').on('click', function() {
        table.draw();
        $('#user-filter-drawer').removeClass('is-open');
        $('body').removeClass('fk-filter-open');
      });
      $('.fk-filter-reset').on('click', function() {
        $('#division_id, #branch_id, #department_id, #active').val('').trigger('change');
        $('#user_type').val('employee').trigger('change');
        $('.selectpicker').selectpicker('refresh');
        table.draw();
      });

      $(document).on('click', '.edit', function() {
        var base_url = $('.baseurl').data('baseurl');
        var id = $(this).attr('id');
        $.ajax({
          url: base_url + '/users/' + id,
          dataType: "json",
          success: function(data) {
            console.log(data);
            $('#user_name').val(data.user_name);
            if (data.user_image) {
              var image = data.user_image;
            } else {
              var image = "{!! asset('assets/img/placeholder.jpg') !!}";
            }
            $("#user_image").attr({
              "src": image
            });
            $('#user_id').val(data.id);
            var title = '{!! trans('panel.global.edit ') !!}';
            $('.modal-title').text(title);
            $('#action_button').val('Edit');
            $('#createuser').modal('show');
          }
        })
      });

      // ---------- Calling details popup ----------
      var callSections = [
        { title: 'Employee', icon: 'badge', fields: [
          ['zone', 'Zone'], ['branch', 'Branch'], ['employee_code', 'Employee Code'],
          ['designation', 'Designation'], ['head_quarter', 'Head Quarters'], ['date_of_joining', 'Date Of Joining']
        ] },
        { title: 'Attendance', icon: 'schedule', fields: [
          ['punchin_time', 'Punch In'], ['punchout_time', 'Punch Out'], ['worked_time', 'Total Working Time'],
          ['working_type', 'Working Type', true], ['first_checkin_time', 'First Customer Check-in'],
          ['last_checkout_time', 'Last Customer Check-out']
        ] }
      ];
      var callStats = [
        ['total_visit', 'Total Visits', 'storefront'], ['total_order', 'Total Orders', 'shopping_cart'],
        ['retailer_registered', 'Retailers Registered', 'add_business'], ['influencer_registered', 'Influencers Registered', 'engineering']
      ];

      function escapeHtml(value) {
        return $('<div>').text(value).html();
      }

      function callValue(v) {
        return (v === null || v === undefined || v === '') ? '-' : escapeHtml(v);
      }

      function callNotify(type, message) {
        if (window.Swal && Swal.fire) {
          Swal.fire({ icon: type, title: message, timer: type === 'success' ? 1800 : undefined, showConfirmButton: type !== 'success' });
        } else {
          alert(message);
        }
      }

      function prettyDate(ymd) {
        if (!ymd) return '';
        var p = ymd.split('-');
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return p[2] + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + p[0];
      }

      function yesterday() {
        var d = new Date();
        d.setDate(d.getDate() - 1);
        return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
      }

      function loadCallingDetails() {
        var $modal = $('#callingDetailsModal .modal-body');
        $modal.addClass('fk-call-loading');
        $.ajax({
          url: "{{ route('users.calling.details') }}",
          data: { user_id: $('#callUserId').val(), date: $('#callDate').val() },
          dataType: 'json',
          success: function(res) {
            var d = res.data, html = '';

            $('#callSubtitle').html('<b>' + callValue(d.employee_name) + '</b>' +
              (d.employee_code ? ' &middot; ' + escapeHtml(d.employee_code) : '') +
              (d.designation ? ' &middot; ' + escapeHtml(d.designation) : '') +
              ' &middot; ' + prettyDate(d.date));

            var $status = $('#callStatus').removeClass('is-present is-open is-absent');
            if (d.punchin_time && d.punchout_time) {
              $status.addClass('is-present').text('Present');
            } else if (d.punchin_time) {
              $status.addClass('is-open').text('Punched in · not punched out');
            } else {
              $status.addClass('is-absent').text('No attendance');
            }
            $status.show();

            html += '<div class="fk-call-stats">';
            $.each(callStats, function(i, s) {
              html += '<div class="fk-call-stat"><i class="material-icons">' + s[2] + '</i><strong>' +
                (parseInt(d[s[0]], 10) || 0) + '</strong><span>' + s[1] + '</span></div>';
            });
            html += '</div>';

            $.each(callSections, function(i, section) {
              html += '<div class="fk-call-section"><div class="fk-call-section-title"><i class="material-icons">' +
                section.icon + '</i>' + section.title + '</div><div class="fk-call-info">';
              $.each(section.fields, function(j, f) {
                html += '<div' + (f[2] ? ' class="is-wide"' : '') + '><span>' + f[1] + '</span><b>' + callValue(d[f[0]]) + '</b></div>';
              });
              html += '</div></div>';
            });

            $('#callContent').html(html);
            $('#callRemark').val(d.calling_remark || '');
            $('#callIssue').val(d.issue || '');
          },
          error: function(xhr) {
            $('#callStatus').hide();
            $('#callContent').html('<div class="fk-call-empty">' + escapeHtml((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to load details.') + '</div>');
          },
          complete: function() { $modal.removeClass('fk-call-loading'); }
        });
      }

      $('body').on('click', '.fk-call-user', function(e) {
        e.preventDefault();
        $('#callUserId').val($(this).data('user-id'));
        $('#callSubtitle').text($(this).data('user-name'));
        $('#callDate').val(yesterday());
        $('#callStatus').hide();
        $('#callContent').html('<div class="fk-call-empty">Loading...</div>');
        $('#callRemark, #callIssue').val('');
        $('#callingDetailsModal').modal('show');
        loadCallingDetails();
      });

      // ---------- Calling report export ----------
      function ymd(d) {
        return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
      }

      $('#openCallingReport').on('click', function() {
        var end = new Date(), start = new Date();
        start.setMonth(start.getMonth() - 1);
        $('#callReportStart').val(ymd(start));
        $('#callReportEnd').val(ymd(end));
        $('#user-filter-drawer').removeClass('is-open');
        $('body').removeClass('fk-filter-open');
        $('#callingReportModal').modal('show');
      });

      $('#callingReportForm').on('submit', function(e) {
        if ($('#callReportStart').val() > $('#callReportEnd').val()) {
          e.preventDefault();
          callNotify('error', 'Start date cannot be after end date.');
          return;
        }
        // file download keeps the page; close the popup once the request has started
        setTimeout(function() { $('#callingReportModal').modal('hide'); }, 300);
      });

      $('#callDate').on('change', function() {
        if ($(this).val()) loadCallingDetails();
      });

      $('#callSubmit').on('click', function() {
        var $btn = $(this).prop('disabled', true);
        $.ajax({
          url: "{{ route('users.calling.store') }}",
          type: 'POST',
          data: {
            user_id: $('#callUserId').val(),
            date: $('#callDate').val(),
            calling_remark: $('#callRemark').val(),
            issue: $('#callIssue').val()
          },
          success: function(res) {
            $('#callingDetailsModal').modal('hide');
            callNotify('success', res.message);
          },
          error: function(xhr) {
            callNotify('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to save.');
          },
          complete: function() { $btn.prop('disabled', false); }
        });
      });

      $('body').on('click', '.activeRecord', function() {
        var id = $(this).attr("id");
        var active = $(this).attr("value");
        var status = '';
        if (active == 'Y') {
          status = 'Incative ?';
        } else {
          status = 'Ative ?';
        }
        var token = $("meta[name='csrf-token']").attr("content");
        if (!confirm("Are You sure want " + status)) {
          return false;
        }
        $.ajax({
          url: "{{ url('users-active') }}",
          type: 'POST',
          data: {
            _token: token,
            id: id,
            active: active
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
            table.draw();
          },
        });
      });

      $('.create').click(function() {
        $('#user_id').val('');
        $('#createuserForm').trigger("reset");
        $("#user_image").attr({"src": '{!! asset('assets / img / placeholder.jpg ') !!}'});
        $('.modal-title').text('{!! trans('panel.global.add ') !!}');
      });

      $('body').on('click', '.delete', function() {
        var id = $(this).attr("value");
        var token = $("meta[name='csrf-token']").attr("content");
        if (!confirm("Are You sure want to delete ?")) {
          return false;
        }
        $.ajax({
          url: "{{ url('users') }}" + '/' + id,
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
            table.draw();
          },
        });
      });

    });
  </script>
</x-app-layout>
