<x-app-layout>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">Lead Tasks <span class="brig ml-2"></span><br>
            <div class="btn-group kim" style="width: 120px;">
              <select name="status" id="status" class="form-control selectpicker">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="overdue">Overdue</option>
              </select>
            </div>
            <span class="">

              <div class="pream_entry">

                <div class="search">
                  <div class="search_inner">
                    <button type="button"> <img src="https://expertfromindia.in/bediya/public/assets/img/search.svg"></button>
                    <input type="search" class="searchbox" placeholder="Search Tasks" id="search_lead">
                  </div>
                </div>
                <div class="p-2"><input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off" readonly></div>
                <div class="p-2"><input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly></div>
                <div class="both_btn">

                  <div class="well mb-3 float-right" id="checkbox_option" style="display: none;">
                    {!! Form::open(['method' => 'POST','route' => ['lead-tasks.checkboxAction'], 'class' => 'form-inline', 'id' => 'frmAction']) !!}
                    <div class="form-group mr-sm-2 mb-2">
                      <input type="hidden" name="lead_ids" id="lead_ids">
                    </div>
                    <button type="submit" onclick="return confirm('Are you sure delete?')" class="btn  btn-responsive btn-primary mr-sm-2 mb-2">Delete</button>
                    {!! Form::close() !!}
                  </div>
                  {{--
                  <button type="button" data-toggle="modal" data-target="#addLeadModel" class="btn btn-primary btn-sm btn-icon-split float-right">
                    <span class="icon text-white-50">
                      <i class="material-icons">add_circle</i>
                    </span>
                    <span class="text">Add Contact</span>
                    <div class="ripple-container"></div>
                  </button>
                  --}}
                  <a href="{{route('tasks-exportTasks')}}" class="btn exportbtn btn-primary btn-sm btn-icon-split float-right" id="export_button">
                    <span class="icon text-white-50">
                      <i class="material-icons">cloud_download</i>
                    </span>
                    <span class="text">Export</span>
                  </a>
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
          @if(session()->has('message_success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span>
              {!!session()->get('message_success') !!}
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
            <table id="getLeadTasks" class="table">
              <thead class=" text-primary">
                <tr>
                  <th>#</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Firm Name</th>
                  <th>Description</th>
                  <th>Date</th>
                  <th>Assign To</th>
                  <!-- <th>Action</th> -->
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--  -->
  <div class="modal fade" id="addLeadModel" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">New Lead</h4>
        </div>

        <form method="POST"
          action="{{ route('leads.store') }}" class="form-horizontal" id="frmLeadsCreate" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 pr-1 pl-1">
                <div class="col-md-12 form-group">
                  <label for="company_name">Company Name <span style="color:red">*</span></label>
                  <input type="text" name="company_name" id="company_name" value="{{ old('company_name','') }}" class="form-control" placeholder="Company Name">
                  @if($errors->has('company_name'))
                  <p class="help-block">
                    <strong>{{ $errors->first('company_name') }}</strong>
                  </p>
                  @endif
                </div>
              </div>
              <div class="col-md-6 pr-1 pl-1">
                <div class="col-md-12 form-group">
                  <label for="contact_name">Contact Name <span style="color:red">*</span></label>
                  <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name','') }}" class="form-control" placeholder="Contact Name">
                  @if($errors->has('contact_name'))
                  <p class="help-block">
                    <strong>{{ $errors->first('contact_name') }}</strong>
                  </p>
                  @endif
                </div>
              </div>
              <div class="col-md-12" id="lead_exist_data">
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger mr-2" data-dismiss="modal">Canel</button>
            <button type="submit" class="btn btn-default">Create Lead</button>
          </div>
      </div>
      </form>
    </div>
  </div>
  <!--  -->
  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
  <!-- Load jQuery and moment.js -->

  <script>
    jQuery(document).ready(function() {
      getLeadTasks();



      jQuery('#frmLeadsCreate').validate({
        rules: {
          company_name: {
            required: true
          },
          contact_name: {
            required: true
          },
        }

      });
    });

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $('#company_name, #contact_name').on('keyup', function() {
      var company_name = $('#company_name').val();
      var contact_name = $('#contact_name').val();
      $.post("{{route('leads.searchExistsLead')}}", {
        company_name: company_name,
        contact_name: contact_name
      }, function(response) {
        $('#lead_exist_data').html(response);
      });
    });



    function getLeadTasks() {
      jQuery('#getLeadTasks').dataTable().fnDestroy();
      jQuery('#getLeadTasks tbody').empty();
      var status = jQuery('#status').val();
      var table = jQuery('#getLeadTasks').DataTable({
        processing: false,
        serverSide: true,
        searching: false,
        ajax: {
          url: "{{ route('lead-tasks.getLeadTasks') }}",
          method: 'POST',
          data: {
            search: jQuery('#search_lead').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: status,
          }
        },
        columns: [{
            data: 'checkbox',
            name: 'checkbox'
          },
          {
            data: 'priority',
            name: 'priority'
          },
          {
            data: 'status',
            name: 'status'
          },
          {
            data: 'lead.company_name',
            name: 'lead.company_name'
          },
          {
            data: 'description',
            name: 'description'
          },
          {
            data: 'date',
            name: 'date'
          },
          {
            data: 'assignUser.name',
            name: 'assignUser.name'
          },
          // {data: 'action', name: 'action'},
        ],
        order: [
          [3, 'desc']
        ],
        dom: 't<"bottom"lip>',
      });
      table.on('xhr', function(e, settings, json) {
        if (json && json.records_filtered_count !== undefined) {
          jQuery('.brig.ml-2').text(json.records_filtered_count + ' Tasks');
        }
      });
    }

    $('#search_lead').on('keyup', function() {
      getLeadTasks();
    });
    $('#status').on('change', function() {
      getLeadTasks();
    });
    $('#end_date').change(function() {
      getLeadTasks();
    });
    $('#start_date').change(function() {
      var selectedStartDate = $('#start_date').datepicker('getDate');
      $('#end_date').datepicker("option", "minDate", selectedStartDate);
      getLeadTasks();
    });



    function checkboxDelete(lead_id) {
      var checkboxes = document.querySelectorAll('.checkbox_cls');
      var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
      if (checkedOne == true) {
        jQuery("#checkbox_option").css("display", "block");
      } else {
        jQuery("#checkbox_option").css("display", "none");
      }


      var lead_ids = jQuery('#lead_ids').val();
      if (lead_ids.split(',').indexOf(lead_id) > -1) {
        var lead_idssArray = lead_ids.split(',');
        for (var i = 0; i < lead_idssArray.length; i++) {
          if (lead_idssArray[i] === lead_id) {
            lead_idssArray.splice(i, 1);
          }
        }
        jQuery('#lead_ids').val(lead_idssArray);

      } else {

        if (lead_ids == "") {
          var res = lead_ids.concat(lead_id);
        } else {
          var res = lead_ids.concat("," + lead_id);
        }

        jQuery('#lead_ids').val(res);
      }


    }

    $(document).on('click', '.change_status', function() {
      var lead_id = $(this).attr('data-id');
      var current_status = $(this).attr('data-status');

      Swal.fire({
        title: "ARE YOU SURE TO CHANGE THE STATUS?",
        html: `
            <select id="swal-status" class="swal2-input" required>
                <option value="">Select Status</option>
                <option value="pending" ${current_status == 'pending' ? 'selected' : ''}>Pending</option>
                <option value="open" ${current_status == 'open' ? 'selected' : ''}>Open</option>
                <option value="in_progress" ${current_status == 'in_progress' ? 'selected' : ''}>In Progress</option>
                <option value="completed" ${current_status == 'completed' ? 'selected' : ''}>Completed</option>
            </select>
            <textarea id="swal-remark" class="swal2-textarea" placeholder="Enter Remark" required></textarea>
        `,
        showCancelButton: true,
        confirmButtonText: 'Yes, Change It',
        closeOnConfirm: false
      }).then(function(result) {
        if (result.value || result.isConfirmed) {
          var status = $('#swal-status').val();
          var remark = $('#swal-remark').val().trim();

          if (!status || !remark) {
            Swal.fire('Required', 'Please select a status and enter a remark.', 'warning');
            return;
          }

          $.ajax({
            url: "{{ route('lead-tasks.change_status') }}",
            dataType: "json",
            type: "POST",
            data: {
              _token: "{{ csrf_token() }}",
              task_id: lead_id,
              status: status,
              remark: remark
            },
            success: function(response) {
              if (response.status == 'success') {
                getLeadTasks();
                Swal.fire('Success', 'Status changed successfully!', 'success');
              } else {
                Swal.fire('Error', 'Something went wrong.', 'error');
              }
            }
          });
        }
      });
    });
  </script>

</x-app-layout>