<x-app-layout>
  <div class="row">
    <div class="col-md-12">
      <div class="fk-list-page-head">
        <div class="fk-list-heading-block">
          <div class="fk-list-breadcrumb">
            <span>User Management</span>
            <span>&rsaquo;</span>
            <span class="fk-current">User City List</span>
          </div>
          <div class="fk-list-title-row">
            <h1 class="fk-list-title">User City List</h1>
            <span class="fk-list-count" id="user-city-record-count"></span>
          </div>
        </div>
        <div class="fk-list-actions">
          <button class="btn fk-filter-trigger" type="button" data-filter-target="#user-city-filter-drawer">
            <span class="material-icons">tune</span>
            <span>Filters</span>
          </button>
        </div>
      </div>
      <div class="card fk-listing-card" data-fk-listing-ready="1">
        <div class="card-body">
          <aside class="fk-filter-drawer" id="user-city-filter-drawer">
            <div class="fk-filter-drawer-head">
              <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
              <div>
                <h3>Advanced Filters</h3>
                <p>Filter the city assignments and export</p>
              </div>
              <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
            </div>
            <div class="fk-filter-drawer-body">
              <form method="GET" action="{{ URL::to('/usercity-download') }}" id="user-city-export-form">
                <div class="d-flex flex-wrap flex-row">
                  <div class="p-2" data-label="User">
                    <select name="user_id" id="filter_user" class="form-control select2" style="width: 100%;">
                  <option value="">All Users</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                  @endforeach
                    </select>
                  </div>
                  <div class="p-2" data-label="State">
                    <select name="state_id" id="filter_state" class="form-control select2" style="width: 100%;">
                  <option value="">All States</option>
                  @foreach($states as $state)
                    <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                  @endforeach
                    </select>
                  </div>
                  <div class="p-2" data-label="Page Number">
                    <input type="number" class="form-control" name="page_number" id="tableInfo" value="1" min="1">
                  </div>
                  <div class="p-2" data-label="Records">
                    <input type="number" class="form-control" name="page_length" id="page_length" value="100" min="1" max="5000" required>
                  </div>
                </div>
              </form>
            </div>
            <div class="fk-filter-drawer-tools">
              @if(auth()->user()->can(['user_upload']))
              <form action="{{ URL::to('/usercity-upload') }}" method="post" enctype="multipart/form-data">
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
              <button class="btn fk-tool-export" type="submit" form="user-city-export-form">
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
            <table id="getaward" class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
              <thead class=" text-primary">
                <th>{!! trans('panel.global.no') !!}</th>
                <th>{!! trans('panel.global.action') !!}</th>
                <th>User Name</th>
                <th>User Designation</th>
                <th>Reporting Name</th>
                <th>Reporting Designation</th>
                <th>City Name</th>
                <th>Grade</th>
                <th>District Name</th>
                <th>State Name</th>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="{{ asset('public/assets/js/jquery.hrms.js') }}"></script>
  <script type="text/javascript">
    $(function() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      var table = $('#getaward').DataTable({
        processing: true,
        serverSide: true,
        "order": [
          [0, 'desc']
        ],
        ajax: {
          url: "{{ route('users.usercity') }}",
          data: function(d) {
            d.user_id = $('#filter_user').val();
            d.state_id = $('#filter_state').val();
          }
        },
        lengthMenu: [
          [10, 25, 50, 100, 500, 1000, 5000],
          [10, 25, 50, 100, 500, 1000, 5000]
        ],
        pageLength: 100,

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
            data: 'userinfo.name',
            name: 'userinfo.name',
            "defaultContent": ''
          },
          {
            data: 'userinfo.getdesignation.designation_name',
            name: 'userinfo.getdesignation.designation_name',
            "defaultContent": ''
          },
          {
            data: 'reportinginfo.name',
            name: 'reportinginfo.name',
            "defaultContent": ''
          },
          {
            data: 'reportinginfo.getdesignation.designation_name',
            name: 'reportinginfo.getdesignation.designation_name',
            "defaultContent": ''
          },
          {
            data: 'cityname.city_name',
            name: 'cityname.city_name',
            "defaultContent": ''
          },
          {
            data: 'cityname.grade',
            name: 'cityname.grade',
            "defaultContent": ''
          },
          {
            data: 'cityname.districtname.district_name',
            name: 'cityname.districtname.district_name',
            "defaultContent": ''
          },
          {
            data: 'cityname.districtname.statename.state_name',
            name: 'cityname.districtname.statename.state_name',
            "defaultContent": ''
          },
        ]
      });

      function updateUserCityRecordCount() {
        var info = table.page.info();
        $('#user-city-record-count')
          .text(info.recordsDisplay + ' records')
          .addClass('is-visible');
      }

      table.on('draw.dt', updateUserCityRecordCount);

      $('#user-city-filter-drawer .fk-filter-apply').on('click', function() {
        $('#tableInfo').val(1);
        table.page(0).draw('page');
      });

      $('#user-city-filter-drawer .fk-filter-reset').on('click', function() {
        $('#filter_user, #filter_state').val('').trigger('change.select2');
        $('#tableInfo').val(1);
        table.page(0).draw('page');
      });
    });



    $(document).ready(function() {

      var table = $('#getaward').DataTable();
      var info = table.page.info();
      $('#getaward').on('page.dt', function() {
        var info = table.page.info();
        //$('#tableInfo').html(info.page+1);
        $('#tableInfo').val(info.page + 1);
      });

      table.on('length', function(e, settings, len) {
        table.ajax.reload(null, false); // user paging is not reset on reload
        $('#page_length').val(len);
      });
    });
  </script>
</x-app-layout>
