<x-app-layout>
  <style>
    /* =======================================
   CUSTOM TAB STYLES — FieldKonnect Theme
   Works with Material Dashboard + Bootstrap 4
   ======================================= */

    /* Tabs container */
    .nav-tabs {
      border-bottom: 2px solid #e0e0e0;
      margin-bottom: 15px;
    }

    /* Default tab style */
    .nav-tabs .nav-link {
      color: #555 !important;
      font-weight: 500;
      border: none !important;
      border-radius: 0 !important;
      background: #f5f5f5;
      padding: 10px 20px;
      transition: all 0.3s ease;
      box-shadow: none;
    }

    /* Hover effect for tabs */
    .nav-tabs .nav-link:hover,
    .nav-tabs .nav-item .nav-link,
    .nav-tabs .nav-item .nav-link:hover {
      background: #e9ecef;
      color: #0256c4 !important;
      /* FieldKonnect Blue */
    }

    /* Active tab */
    .nav-tabs .nav-link.active {
      color: #fff !important;
      background: linear-gradient(60deg, #0256c4, #007bff) !important;
      border: none !important;
      border-radius: 5px 5px 0 0 !important;
      box-shadow: 0 -2px 10px rgba(2, 86, 196, 0.25);
    }

    /* Inactive tabs */
    .nav-tabs .nav-link:not(.active) {
      background-color: #f1f1f1;
      color: #6c757d !important;
    }

    /* Tab content box */
    .tab-content {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 0 5px 5px 5px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    /* Optional: Font Awesome icon alignment if used inside tab */
    .nav-tabs .nav-link i {
      margin-right: 6px;
      font-size: 14px;
    }

    /* Responsive tweak */
    @media (max-width: 767.98px) {
      .nav-tabs .nav-link {
        padding: 8px 15px;
        font-size: 14px;
      }
    }
  </style>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">Active Customer Process
            <span class="">
              <div class="btn-group header-frm-btn">

                <form method="GET" action="{{ URL::to('call-log-download') }}">
                  <div class="d-flex flex-wrap flex-row">
                    <div class="p-2" style="width:200px;">
                      <select class="select2" name="customer_id" id="customer_id" data-style="select-with-transition" title="Select Customer">
                        <option value="">Select Customer</option>
                        @if(@isset($customers ))
                        @foreach($customers as $customer)
                        <option value="{!! $customer['id'] !!}" {{ old( 'customer_id') == $customer->id ? 'selected' : '' }}>{!! $customer['name'] !!}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>
                    <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off" readonly></div>
                    <div class="p-2" style="width:150px;"><input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly></div>
                    {{--@if(auth()->user()->can(['call_log_download']))
                    <div class="p-2"><button class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.download') !!}  Call Logs"><i class="material-icons">cloud_download</i></button></div>
                    @endif--}}
                  </div>
                </form>

                <div class="next-btn">
                  @if(auth()->user()->can('active_process_create'))
                  <a href="{{ route('active_customer_process.create') }}" class="btn btn-just-icon btn-theme" title="Assign Process"><i class="material-icons">add_circle</i></a>
                  @endif
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
          <ul class="nav nav-tabs" id="processTabs" role="tablist">
            <li class="nav-item mr-2">
              <a class="nav-link active" id="active-tab" data-toggle="tab" href="#active" role="tab">
                <i class="fa fa-check-circle"></i> Active
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                <i class="fa fa-times-circle"></i> Closed
              </a>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="active" role="tabpanel">
              <div class="table-responsive">
                <table id="getActiveProcess" class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
                  <thead class=" text-primary">
                    <th>{!! trans('panel.global.no') !!}</th>
                    <th>{!! trans('panel.global.action') !!}</th>
                    <th>Customer Name</th>
                    <th>Customer Number</th>
                    <th>Customer Created Date</th>
                    <th>Process</th>
                    <th>Steps</th>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="tab-pane fade" id="closed" role="tabpanel">
              <div class="table-responsive">
                <table id="getClosedProcess" class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
                  <thead class=" text-primary">
                    <th>{!! trans('panel.global.no') !!}</th>
                    <th>{!! trans('panel.global.action') !!}</th>
                    <th>Customer Name</th>
                    <th>Customer Number</th>
                    <th>Customer Created Date</th>
                    <th>Process</th>
                    <th>Steps</th>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Steps Modal -->
    <div class="modal fade" id="stepsModal" tabindex="-1" role="dialog" aria-labelledby="stepsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="stepsModalLabel">Process Steps</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped mb-0">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Step Name</th>
                    <th>Status</th>
                    <th>Completed Date</th>
                    <th>Remarks</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="stepsTableBody">
                  <tr>
                    <td colspan="5" class="text-center text-muted">No steps found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary text-white" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Remark Modal -->
    <div class="modal fade" id="remarkModal" tabindex="-1" role="dialog" aria-labelledby="remarkModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form id="remarkForm">
            <div class="modal-header">
              <h5 class="modal-title" id="remarkModalLabel">Add / Update Remark</h5>
              <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <textarea class="form-control" id="remarkText" rows="4" placeholder="Enter remark" required></textarea>
            </div>
            <div class="modal-footer">
              <input type="hidden" id="remarkStepId">
              <button type="button" class="btn btn-secondary mr-2 text-white" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Save Remark</button>
            </div>
          </form>
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

        function initializeProcessTable(tableId, status) {
          return $('#' + tableId).DataTable({
            processing: true,
            serverSide: true,
            order: [
              [0, 'desc']
            ],
            ajax: {
              url: "{{ route('active_customer_process.index') }}",
              data: function(d) {
                d.customer_id = $('#customer_id').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.status = status;
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
                defaultContent: '',
                orderable: false,
                searchable: false
              },
              {
                data: 'customer.name',
                name: 'customer.name',
                defaultContent: ''
              },
              {
                data: 'customer.mobile',
                name: 'customer.mobile',
                defaultContent: ''
              },
              {
                data: 'customer.creation_date',
                name: 'customer.creation_date',
                defaultContent: '',
                searchable: false,
                orderable: false
              },
              {
                data: 'process.process_name',
                name: 'process.process_name',
                defaultContent: ''
              },
              {
                data: 'steps',
                name: 'steps',
                defaultContent: '',
                searchable: false,
                orderable: false
              }
            ]
          });
        }

        // Initialize both tables
        var activeTable = initializeProcessTable('getActiveProcess', 'active');
        var closedTable = initializeProcessTable('getClosedProcess', 'closed');

        // Common event handling for filters
        function redrawTables() {
          activeTable.draw();
          closedTable.draw();
        }

        $('#customer_id, #end_date').change(function() {
          redrawTables();
        });

        $('#start_date').change(function() {
          var selectedStartDate = $('#start_date').datepicker('getDate');
          $('#end_date').datepicker("option", "minDate", selectedStartDate);
          redrawTables();
        });

        // Common delete event for both tables
        $('body').on('click', '.delete', function() {
          var id = $(this).data("id");
          var token = $("meta[name='csrf-token']").attr("content");

          Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!"
          }).then((result) => {
            if (result.value) {
              $.ajax({
                url: "{{ url('active_customer_process') }}" + '/' + id,
                type: 'DELETE',
                data: {
                  _token: token,
                  id: id
                },
                success: function(data) {
                  if (data.status == 'success') {
                    Swal.fire("Deleted!", data.message, "success");
                  } else {
                    Swal.fire("Error!", data.message, "error");
                  }
                  redrawTables();
                },
                error: function(xhr) {
                  let message = "Something went wrong!";
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                  } else if (xhr.responseText) {
                    message = xhr.responseText;
                  }
                  Swal.fire("Error!", message, "error");
                }
              });
            }
          });
        });

        $('body').on('click', '.steps', function() {
          // return false;
          var id = $(this).data("id");
          var token = $("meta[name='csrf-token']").attr("content");
          $.ajax({
            url: "{{ url('get_active_process_steps') }}" + '/' + id,
            type: 'GET',
            data: {
              _token: token,
              id: id
            },
            success: function(data) {
              if (data.status == 'success') {
                const steps = data.steps || [];
                let html = '';

                if (steps.length > 0) {
                  steps.forEach((step, index) => {
                    const isPending = step.status === 'pending';
                    const hasRemark = step.remark && step.remark.trim() !== '';
                    html += `
                              <tr style="background-color: ${isPending ? '#f8d7da' : '#d4edda'};">
                                <td>${index + 1}</td>
                                <td>${step.step.value || ''}</td>
                                <td>
                                  <span class="badge badge-${step.status === 'completed' ? 'success' : 'danger'}">
                                    ${step.status ? step.status.charAt(0).toUpperCase() + step.status.slice(1) : ''}
                                  </span>
                                </td>
                                <td>${step.completed_at ? moment(step.completed_at).format('DD MMM YYYY') : '-'}</td>
                                <td>
                                ${hasRemark 
                                  ? `<button style="padding: 3px 7px !important;background: #0dcaf0 !important;" class="btn btn-sm view-remark" data-id="${step.id}" data-remark="${step.remark}" title="View Remark">
                                      <i class="fa fa-eye" style="font-size: 14px !important;"></i>
                                    </button>` 
                                  : `<button style="padding: 3px 9px !important;" class="btn btn-sm add-remark" data-id="${step.id}" title="Add Remark">
                                      <i class="fa fa-plus" style="font-size: 14px !important;"></i>
                                    </button>`
                                }
                              </td>
                                <td>
                                    <button style="padding: 3px 7px !important;background: #fd7e14 !important;" class="btn btn-sm complete-step" data-remark="${step.remark}" data-id="${step.id}" title="Update Step">
                                      <i class="fa fa-edit" style="font-size: 14px !important;"></i> </button>
                                </td>
                              </tr>
                            `;
                  });
                } else {
                  html = `<tr><td colspan="5" class="text-center text-muted">No steps found.</td></tr>`;
                }

                // Inject rows into modal table
                $('#stepsTableBody').html(html);

                // Show the modal
                $('#stepsModal').modal('show');
              } else {
                Swal.fire("Error!", data.message, "error");
              }
              redrawTables();
            },
            error: function(xhr) {
              let message = "Something went wrong!";
              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              } else if (xhr.responseText) {
                message = xhr.responseText;
              }
              Swal.fire("Error!", message, "error");
            }
          });
        });

        $('body').on('click', '.complete-step', function() {
          $('#stepsModal').modal('hide');

          var stepId = $(this).data('id');
          var stepRemark = $(this).data('remark');
          var token = $("meta[name='csrf-token']").attr("content");
          var $row = $(this).closest('tr');
          var currentStatus = $row.find('td:eq(2) .badge').text().trim();

          Swal.fire({
            title: "Update Step Status",
            html: `
              <div style="text-align:left">
                <label><strong>Status</strong></label>
                <select id="step-status" class="swal2-input" style="width:100%;padding: 10px !important;">
                  <option value="completed" ${currentStatus === 'Completed' ? 'selected' : ''}>Completed</option>
                  <option value="pending" ${currentStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                </select>

                <div id="date-container">
                  <label><strong>Completion Date</strong></label>
                  <input id="completion-date" type="date" class="swal2-input" value="${moment().format('YYYY-MM-DD')}" style="width:100%;">
                </div>

                <label><strong>Remark</strong></label>
                <textarea id="step-remark" class="swal2-textarea" placeholder="Enter your remark...">${stepRemark || ''}</textarea>
              </div>
            `,
            showCancelButton: true,
            confirmButtonText: "Save",
            cancelButtonText: "Cancel",
            onOpen: function() {
              // Older SweetAlert2 versions use onOpen instead of didOpen
              var $status = $('#step-status');
              var $dateContainer = $('#date-container');

              function toggleDate() {
                if ($status.val() === 'completed') {
                  $dateContainer.show();
                } else {
                  $dateContainer.hide();
                }
              }

              $status.on('change', toggleDate);
              toggleDate(); // initial check
            }
          }).then((result) => {
            if (result.value) {
              const status = $('#step-status').val();
              const remark = $('#step-remark').val().trim();
              const date = $('#completion-date').val();

              // Manual validation
              if (!remark) {
                Swal.fire("Warning!", "Remark is required!", "warning");
                return false;
              }
              if (status === 'completed' && !date) {
                Swal.fire("Warning!", "Completion date is required for completed steps!", "warning");
                return false;
              }

              // Proceed with AJAX
              $.ajax({
                url: "{{ url('complete_process_step') }}/" + stepId,
                type: "POST",
                data: {
                  _token: token,
                  remarks: remark,
                  status: status,
                  completed_at: status === 'completed' ? date : null
                },
                success: function(res) {
                  if (res.status === 'success') {
                    Swal.fire("Done!", res.message, "success");

                    if (status === 'completed') {
                      $row.find('td:eq(2) .badge')
                        .removeClass('badge-warning')
                        .addClass('badge-success')
                        .text('Completed');
                      $row.find('td:eq(3)').text(moment(date).format('DD MMM YYYY'));
                    } else {
                      $row.find('td:eq(2) .badge')
                        .removeClass('badge-success')
                        .addClass('badge-warning')
                        .text('Pending');
                      $row.find('td:eq(3)').text('-');
                    }

                    $row.find('td:eq(4)').text(remark);
                    $row.find('td:eq(5)').html('<i class="text-muted">—</i>');
                    redrawTables();
                  } else {
                    Swal.fire("Error!", res.message, "error");
                  }
                },
                error: function(xhr) {
                  let message = "Something went wrong!";
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                  }
                  Swal.fire("Error!", message, "error");
                }
              });
            }
          });
        });



      });

      // Add new remark
      $('body').on('click', '.add-remark', function() {
        $('#remarkModalLabel').text('Add Remark');
        $('#remarkStepId').val($(this).data('id'));
        $('#remarkText').val('');
        $('#remarkModal').modal('show');
      });

      // View / Update existing remark
      $('body').on('click', '.view-remark', function() {
        $('#remarkModalLabel').text('View / Update Remark');
        $('#remarkStepId').val($(this).data('id'));
        $('#remarkText').val($(this).data('remark'));
        $('#remarkModal').modal('show');
      });

      // Save or Update Remark
      $('#remarkForm').on('submit', function(e) {
        e.preventDefault();

        let stepId = $('#remarkStepId').val();
        let remark = $('#remarkText').val();

        if (remark.trim() === '') {
          Swal.fire({
            type: 'error',
            title: 'Remark is required!',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
          return;
        }

        $.ajax({
          url: `/steps/${stepId}/remark`, // 🔧 Update this route
          type: 'POST',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            remark: remark
          },
          success: function(response) {
            $('#remarkModal').modal('hide');
            Swal.fire({
              type: 'success',
              title: 'Remark saved successfully!',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true
            });
            // Reload your table data here if needed
          },
          error: function() {
            Swal.fire({
              type: 'error',
              title: 'Something went wrong!',
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true
            });
          }
        });
      });
    </script>
</x-app-layout>