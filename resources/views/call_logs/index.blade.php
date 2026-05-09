<x-app-layout>
  <style>
    .card {
      transition: transform 0.2s ease;
    }

    .active-card {
      transform: scale(1.06);
    }
  </style>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title">Lead Call Logs
            <span class="">
              <button class="btn btn-info mb-3 float-right" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="false" aria-controls="filterSection">
                <i class="material-icons">tune</i> Filters
              </button>
              <div class="collapse" id="filterSection">
                <form method="GET" action="{{ URL::to('call-log-download') }}">
                  <div class="d-flex flex-wrap flex-row">
                    <div class="p-2" style="width:200px;">
                      <select class="select2" name="executive_id" id="executive_id" data-style="select-with-transition" title="Select User">
                        <option value="">Select User</option>
                        @if(@isset($users ))
                        @foreach($users as $user)
                        <option value="{!! $user['id'] !!}" {{ old( 'executive_id') == $user->id ? 'selected' : '' }}>{!! $user['name'] !!}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>
                    <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off" readonly></div>
                    <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly></div>
                    @if(auth()->user()->can(['call_log_download']))
                    <div class="p-2"><button class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.download') !!}  Call Logs"><i class="material-icons">cloud_download</i></button></div>
                    @endif
                  </div>
                </form>
                <div class="next-btn">

                </div>
              </div>
            </span>
          </h4>
        </div>
        <div class="card-body">
          <div class="col-md-12 p-3">
            <div class="row">
              <div class="col-sm" onclick="filterByStatus('', this)" style="cursor:pointer">
                <div class="card text-center m-1 hover-effect">
                  <div class="card-body">
                    <h4 class="card-text">Dialed Calls</h4>
                    <h5 class="card-title" id="dialed_calls"></h5>
                  </div>
                </div>
              </div>
              <div class="col-sm" onclick="filterByStatus('Connected', this)" style="cursor:pointer">
                <div class="card text-center m-1 hover-effect">
                  <div class="card-body">
                    <h4 class="card-text">Connected Calls</h4>
                    <h5 class="card-title" id="connected_calls"></h5>
                  </div>
                </div>
              </div>
              <div class="col-sm" onclick="filterByStatus('No Response', this)" style="cursor:pointer">
                <div class="card text-center m-1 hover-effect">
                  <div class="card-body">
                    <h4 class="card-text">No Response </h4>
                    <h5 class="card-title" id="no_response"></h5>
                  </div>
                </div>
              </div>
              <div class="col-sm">
                <div class="card text-center m-1 hover-effect">
                  <div class="card-body">
                    <h4 class="card-text">Total Call Duration</h4>
                    <h5 class="card-title" id="call_duration"></h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
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
            <table id="getleadcalllogs" class="table table-striped- table-bordered table-hover table-checkable no-wrap">
              <thead class=" text-primary">
                <th>User Name</th>
                <th>Customer Name</th>
                <th>Contact Number</th>
                <th>Date & Time</th>
                <th>Call Duration</th>
                <th>Call Status</th>
                <th>Lead Status</th>
                <th>Remark</th>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    var table;
    $(function() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      table = $('#getleadcalllogs').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        columnDefs: [{
          className: 'control',
          orderable: false,
          targets: -1
        }],
        "order": [
          [0, 'desc']
        ],
        "retrieve": true,
        ajax: {
          url: "{{ route('lead-call-log') }}",
          data: function(d) {
            d.user_id = $('#executive_id').val(),
              d.start_date = $('#start_date').val(),
              d.end_date = $('#end_date').val()
          },
          dataSrc: function(json) {
            // ✅ Set summary values
            if (json.summary) {
              $('#dialed_calls').text(json.summary.total);
              $('#connected_calls').text(json.summary.connected);
              $('#no_response').text(json.summary.no_response);
              $('#call_duration').text(json.summary.total_duration);
            }
            return json.data;
          }
        },
        columns: [{
            data: 'user.name',
            name: 'user.name',
            "defaultContent": '',
            orderable: false,
          },
          {
            data: 'lead.company_name',
            name: 'lead.company_name',
            "defaultContent": '-',
            orderable: false,
          },
          {
            data: 'number',
            name: 'number',
            "defaultContent": '',
            orderable: false,
          },
          {
            data: 'started_at',
            name: 'started_at',
            "defaultContent": '',
            orderable: false,
          },
          {
            data: 'duration',
            name: 'duration',
            "defaultContent": '',
            orderable: false
          },
          {
            data: 'status',
            name: 'status',
            "defaultContent": '',
            orderable: false
          },
          {
            data: 'lead_status',
            name: 'lead_status',
            "defaultContent": 'Not Found',
            orderable: false
          },
          {
            data: 'remark',
            name: 'remark',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },

        ],
      });

      $('#executive_id').change(function() {
        table.draw();
      });
      $('#end_date').change(function() {
        table.draw();
      });
      $('#start_date').change(function() {
        var selectedStartDate = $('#start_date').datepicker('getDate');
        $('#end_date').datepicker("option", "minDate", selectedStartDate);
        table.draw();
      });


    });

    function filterByStatus(status, div) {
      // Remove border from all cards first
      $('.card').removeClass('active-card'); // remove active class from all
      $('.card-text').css('font-weight', '300');
      $(div).find('.card').fadeOut(100).fadeIn(200).addClass('active-card');
      $(div).find('.card-text').css('font-weight', '600');
      // Filter table data
      table.column(5).search(status).draw();
    }
  </script>
</x-app-layout>