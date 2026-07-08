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
