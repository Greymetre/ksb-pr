<x-app-layout>
  <style>
    .objective-pill {
  background: #17a2b8;
  color: #fff;
  padding: 5px 10px;
  border-radius: 20px;
  margin: 3px;
  display: flex;
  align-items: center;
}

.objective-pill .remove-btn {
  margin-left: 8px;
  cursor: pointer;
  font-weight: bold;
}
  </style>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">Tour List
            <span class="">
              <div class="btn-group header-frm-btn">


                <form method="GET" action="{{ URL::to('tours-download') }}">
                  <div class="d-flex flex-row">

                    <div class="p-2" style="width:250px;">
                      <select class="selectpicker" multiple name="branch_id" id="branch_id"
                        data-style="select-with-transition" title="Select Branch">
                        <option value="">Select Branch</option>
                        @if(@isset($branches ))
                        @foreach($branches as $branch)
                        <option value="{!! $branch['id'] !!}">{!! $branch['name'] !!}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>

                    <div class="p-2" style="width:250px;">
                      <select class="selectpicker" name="division_id" id="division_id"
                        data-style="select-with-transition" title="Select Zone">
                        <option value="">Select Zone</option>
                        @if(@isset($divisions ))
                        @foreach($divisions as $division)
                        <option value="{!! $division['id'] !!}">{!! $division['division_name']
                          !!}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>


                    <div class="p-2" style="width: 250px;">
                      <select class="form-control select2" name="executive_id" id="executive_id"
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
                      <select class="selectpicker" multiple name="designation_id[]" id="designation_id"
                          data-style="select-with-transition" title="Select Designation">
                    
                          @foreach($designations as $designation)
                              <option value="{{ $designation->id }}"
                                  {{ in_array($designation->designation_name, ['ASR', 'DSR']) ? 'selected' : '' }}>
                                  {{ $designation->designation_name }}
                              </option>
                          @endforeach
                    
                      </select>
                    </div>



                    <div class="p-2"><input type="text" class="form-control datepicker"
                        id="start_date" name="start_date" placeholder="Start Date"
                        autocomplete="off" readonly></div>
                    <div class="p-2"><input type="text" class="form-control datepicker"
                        id="end_date" name="end_date" placeholder="End Date" autocomplete="off"
                        readonly></div>
                    <div class="p-2"><button class="btn btn-just-icon btn-theme"
                        title="{!!  trans('panel.global.download') !!} {!! trans('panel.tours.title') !!}"><i
                          class="material-icons">cloud_download</i></button></div>
                  </div>
                </form>

                <div class="next-btn">

                  <form action="{{ URL::to('tours-upload') }}" class="form-horizontal" method="post"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="input-group">
                      <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                        <span class="btn btn-just-icon btn-theme btn-file">
                          <span class="fileinput-new"><i
                              class="material-icons">attach_file</i></span>
                          <span class="fileinput-exists">Change</span>
                          <input type="hidden">
                          <input type="file" name="import_file" required
                            accept=".xls,.xlsx" />
                        </span>
                      </div>
                      <div class="input-group-append">
                        <button class="btn btn-just-icon btn-theme"
                          title="{!!  trans('panel.global.upload') !!} {!! trans('panel.tour.title') !!}">
                          <i class="material-icons">cloud_upload</i>
                          <div class="ripple-container"></div>
                        </button>
                      </div>
                    </div>
                  </form>



                  <!-- <a href="{{ URL::to('tours-download') }}" class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.download') !!} {!! trans('panel.tour.title') !!}"><i class="material-icons">cloud_download</i></a> -->
                  @if(auth()->user()->can(['tour_upload']))

                  @endif
                  @if(auth()->user()->can(['tour_download']))

                  @endif


                  @if(auth()->user()->can(['tour_template']))
                  <a href="{{ URL::to('tours-template') }}" class="btn btn-just-icon btn-theme"
                    title="{!!  trans('panel.global.template') !!} {!! trans('panel.tour.title_singular') !!}"><i
                      class="material-icons">text_snippet</i></a>
                  @endif
                  @if(auth()->user()->can(['tour_create']))
                  <a data-toggle="modal" data-target="#createCategory"
                    class="btn btn-just-icon btn-theme create"
                    title="{!!  trans('panel.global.add') !!} {!! trans('panel.tour.title_singular') !!}"><i
                      class="material-icons">add_circle</i></a>
                  @endif
                  <a href="{{ route('tours.create') }}" class="btn btn-just-icon btn-theme"><i
                      class="material-icons">add_circle</i></a>
                  <div class="d-flex align-items-center gap-2" style="gap:8px;">

                    <button class="btn btn-success btn-sm bulk-approve px-3 py-2">
                      Approve
                    </button>

                    <button class="btn btn-danger btn-sm bulk-reject px-3 py-2">
                      Reject
                    </button>

                  </div>

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
            <table id="gettour"
              class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
              <thead class=" text-primary">
                <th><input type="checkbox" class="allCustomerschecked" id="check_all" /></th>
                <!-- <th>{!! trans('panel.global.no') !!}</th> -->
                <th>{!! trans('panel.global.action') !!}</th>
                <th>Status</th>
                <th>Employee Code</th>
                <th>User Name</th>
                <th>Date</th>

                <th>Town</th>
                <th>District</th>
                <th>Objectives</th>
                <!-- <th>Type</th> -->
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal -->
  <div class="modal fade bd-example-modal-lg" id="createCategory" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title"><span class="modal-title">Edit</span> Tour
            <span class="pull-right">
              <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal"><i
                  class="material-icons">clear</i></a>
            </span>
          </h4>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('tours.toursInfoUpdate') }}" id="editTourForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="id" id="tour_id" />

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="col-form-label">Date <span class="text-danger">*</span></label>
                  <input type="text" name="date" id="date" class="form-control datepicker" required
                    autocomplete="off" />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label class="col-form-label">User <span class="text-danger">*</span></label>
                  <select class="form-control select2" name="userid" id="userid" required
                    style="width:100%;">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <!-- In the edit modal form -->
              <div class="col-md-6">
                <div class="form-group">
                  <label class="col-form-label">District</label>
                  <select class="form-control select2" name="district_name_display" id="district" style="width:100%;">
                    <option value="">Select District</option>
                  </select>
                  <!-- Hidden: actual district ID sent to backend -->
                  <input type="hidden" name="district" id="district_hidden" />
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label class="col-form-label">Town / City <span class="text-danger">*</span></label>
                  <select class="form-control select2" name="town_name_display" id="town" required style="width:100%;">
                    <option value="">Select City</option>
                  </select>
                  <!-- Hidden: actual city ID sent to backend -->
                  <input type="hidden" name="town" id="town_hidden" />
                </div>
              </div>

              <!-- <div class="col-md-12">
                <div class="form-group">
                  <label class="col-form-label">Objectives</label>
                  <textarea class="form-control" name="objectives" id="objectives"
                    rows="4"></textarea>
                </div>
              </div> -->

              <div class="col-md-12">
  <div class="form-group">
    <label class="col-form-label">Objectives</label>

    <!-- Pills container -->
    <div id="objectiveContainer" class="form-control d-flex flex-wrap" style="min-height:45px;"></div>

    <div class="d-flex flex-wrap mt-2" id="objectiveQuickOptions">
      @foreach(config('constants.tour_objectives') as $objective)
        <button type="button" class="btn btn-outline-primary btn-sm mr-1 mb-1 objective-option" data-value="{{ $objective }}">{{ $objective }}</button>
      @endforeach
    </div>

    <!-- Input + Add button -->
    <div class="d-flex mt-2">
      <input type="text" id="objectiveInput" class="form-control" placeholder="Add objective">
      <button type="button" class="btn btn-info ml-2" id="addObjective">Add</button>
    </div>

    <!-- Hidden input for backend -->
    <input type="hidden" name="objectives" id="objectives">
  </div>
</div>
            </div>

            <div class="clearfix"></div>
            <div class="pull-right mt-3">
              <button type="submit" class="btn btn-info save">Submit</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="{{ asset('public/assets/js/jquery.custom.js') }}"></script>
    <script src="{{ asset('public/assets/js/validation_products.js') }}"></script>


    <script type="text/javascript">
      $(function() {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $('body').on('click', '.tourActive', function() {
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
            url: "{{ url('tours-active') }}",
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
          $('#tour_id').val('');
          $('#createCategoryForm').trigger("reset");
          $("#tour_image").attr({"src": '{!! asset('public/assets/img/placeholder.jpg') !!}'          });
          $('.modal-title').text('{!! trans('panel.global.add ') !!}');
          objectivesArray = [];
    renderObjectives();
        });
    });
    </script>





    <script type="text/javascript">

        var table
      $(document).ready(function() {

        $('#check_all').click(function() {
          var checked = $(this).prop('checked');
          $('.checked_all').prop('checked', checked);
        });



        // ============================================================================
        // DataTable Initialization (fixed column order & removed wrong render)
        // ============================================================================
        table = $('#gettour').DataTable({
          processing: true,
          serverSide: true,
          order: [
            [5, 'desc']
          ], // sort by Date
          ajax: {
            url: "{{ route('tours.index') }}",
            data: function(d) {
              d.executive_id = $('#executive_id').val();
              d.start_date = $('#start_date').val();
              d.end_date = $('#end_date').val();
              d.division_id = $('#division_id').val();
              d.designation_id = $('#designation_id').val();
            }
          },
          columns: [{
              data: 'checkbox',
              name: 'checkbox',
              orderable: false,
              searchable: false
            },
            {
              data: 'action',
              name: 'action',
              orderable: false,
              searchable: false
            },
            {
              data: 'stauts',
              name: 'stauts'
            },
            {
              data: 'userinfo.employee_codes',
              name: 'userinfo.employee_codes',
              defaultContent: '-'
            },
            {
              data: 'userinfo.name',
              name: 'userinfo.name',
              defaultContent: '-'
            },
            {
              data: 'date',
              name: 'date'
            },
            // { data: 'town', name: 'town', defaultContent: '-' },
            {
              data: 'town',
              name: 'town',
              render: function(data, type, row) {
                return row.city ? row.city.city_name : '-';
              },
              defaultContent: '-'
            },
            {
              data: 'district',
              name: 'district',
              render: function(data, type, row) {
                return row.district_relation ? row.district_relation.district_name :
                  '-';
              },
              defaultContent: '-'
            }, {
              data: 'objectives',
              name: 'objectives',
              defaultContent: '-'
            }
          ]
        });

        // Filter changes
        $('#executive_id, #start_date, #end_date, #division_id, #designation_id').change(function() {
          table.draw();
        });


        $("#branch_id").on('change', function() {
          var search_branches = $(this).val();
          $.ajax({
            url: "{{ route('tours.index') }}",
            data: {
              "search_branches": search_branches
            },
            success: function(res) {
              if (res.status == true) {
                var select = $('#executive_id');
                select.empty();
                select.append('<option>Select User</option>');
                $.each(res.users, function(k, v) {
                  select.append('<option value="' + v.id + '" >' + v.name +
                    '</option>');
                });
                select.selectpicker('refresh');
              }
            }
          });

        })












      });
    </script>
    <script type="text/javascript">
      $(document).ready(function() {
        // Make sure Select2 is initialized on both selects
        $('#userid, #district, #town').select2({
          width: '100%',
          placeholder: function() {
            return $(this).attr('placeholder') || 'Select option';
          },
          allowClear: true
        });

        // Helper: Load districts
function loadUserDistricts(userId, preSelectedDistrictId = '') {

    return $.ajax({
        url: "{{ route('tours.ajaxUserDistricts') }}",
        type: "POST",
        data: {
            user_id: userId,
            _token: "{{ csrf_token() }}"
        }
    }).done(function(res) {

        let options = '<option value="">Select District</option>';

        (res.districts || []).forEach(d => {
            const selected = (d.id == preSelectedDistrictId) ? 'selected' : '';
            options += `<option value="${d.id}" ${selected}>${d.name}</option>`;
        });

        $('#district').html(options);

if (preSelectedDistrictId) {
    $('#district').val(preSelectedDistrictId);
    $('#district_hidden').val(preSelectedDistrictId); // 🔥 ADD THIS
}

    });
}

        // Helper: Load cities
function loadUserCities(userId, districtId, preSelectedCityId = '') {

    return $.ajax({
        url: "{{ route('tours.ajaxUserCitiesByDistrict') }}",
        type: "POST",
        data: {
            user_id: userId,
            district_id: districtId,
            _token: "{{ csrf_token() }}"
        }
    }).done(function(res) {

        let options = '<option value="">Select City</option>';

        (res.cities || []).forEach(c => {
            const selected = (c.id == preSelectedCityId) ? 'selected' : '';
            options += `<option value="${c.id}" ${selected}>${c.name}</option>`;
        });

        $('#town').html(options);

        if (preSelectedCityId) {
            $('#town').val(preSelectedCityId);
            $('#town_hidden').val(preSelectedCityId);
        }

    });
}

        // Events
        $('#userid').on('change', function() {
          loadUserDistricts($(this).val());
        });

        $('#district').on('change', function() {
          $('#district_hidden').val($(this).val());
          loadUserCities($('#userid').val(), $(this).val());
        });

        $('#town').on('change', function() {
          $('#town_hidden').val($(this).val());
        });

        // Edit button – main handler
$(document).on('click', '.edit', function () {

    const tourId = $(this).attr('id');

$.get("{{ url('tours') }}/" + tourId, function(data) {

    $('#tour_id').val(data.id);
    $('#date').val(data.date);
    $('#userid').val(data.userid).trigger('change.select2');
    // $('#objectives').val(data.objectives);
    objectivesArray = [];

if (data.objectives) {
    objectivesArray = data.objectives.split(',').map(item => item.trim());
}

renderObjectives();

    loadUserDistricts(data.userid, data.district)
        .then(function() {
            return loadUserCities(data.userid, data.district, data.town);
        });

    $('#createCategory').modal('show');
});
});
        // Delete functionality
        $('body').on('click', '.delete', function(e) {
          e.preventDefault();

          var id = $(this).attr("value") || $(this).data("id"); // flexible rakha
          console.log(id)
          if (!id) {
            alert("No ID found on delete button!");
            return;
          }

          var token = $("meta[name='csrf-token']").attr("content");

          if (!confirm("Are you sure you want to delete this tour?")) {
            return;
          }

          $.ajax({
            url: "{{ url('tours') }}/" + id,
            type: 'DELETE',
            data: {
              _token: token,
              id: id
            },
            success: function(data) {
              $('.alert').show().removeClass('alert-danger').addClass(
                'alert-success');
              $('.message').text(data.message || "Tour deleted successfully");

              table.draw(); // refresh table
            },
            error: function(xhr) {
              console.log("Delete error:", xhr.responseText);
              $('.alert').show().removeClass('alert-success').addClass(
                'alert-danger');
              $('.message').text("Delete failed: " + (xhr.responseJSON?.message ||
                "Server error"));
            }
          });
        });


        $('body').on('click', '.change_status', function() {

          var token = $("meta[name='csrf-token']").attr("content");

          // 🔥 Check if checkboxes selected
          var selectedIds = $('.checked_all:checked').map(function() {
            return this.value;
          }).get();

          var clickedId = $(this).val(); // id of clicked row
          var currentStatus = $(this).data("status");

          // ✅ If no checkbox selected → use clicked row only
          var ids =
            // selectedIds.length > 0 ? selectedIds : 
            [clickedId];

          Swal.fire({
            title: 'Change Status',
            input: 'select',
            inputOptions: {
              '1': 'Approve',
              '2': 'Reject',
              '0': 'Pending'
            },
            inputPlaceholder: 'Select status',
            inputValue: currentStatus,
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
              if (!value) {
                return 'You must select a status';
              }
            }
          }).then((result) => {

            if (!result.dismiss) {

              $.ajax({
                url: "{{ route('tours.changesttus') }}",
                method: "POST",
                data: {
                  _token: token,
                  id: ids, // array always
                  status: result.value
                },
                success: function(data) {

                  if (data.status == 'success') {
                    Swal.fire('Status changed successfully!', '',
                      'success');
                    table.draw();
                  } else {
                    Swal.fire('Error updating status', '', 'error');
                  }
                }
              });

            }
          });

        });


        $('body').on('click', '.bulk-approve', function() {

          var selectedIds = $('.checked_all:checked').map(function() {
            return this.value;
          }).get();

          if (selectedIds.length === 0) {
            Swal.fire('Please select at least one entry');
            return;
          }

          updateBulkStatus(selectedIds, 1); // 1 = Approve
        });


        $('body').on('click', '.bulk-reject', function() {

          var selectedIds = $('.checked_all:checked').map(function() {
            return this.value;
          }).get();

          if (selectedIds.length === 0) {
            Swal.fire('Please select at least one entry');
            return;
          }

          updateBulkStatus(selectedIds, 2); // 2 = Reject
        });



        function updateBulkStatus(ids, status) {

          var token = $("meta[name='csrf-token']").attr("content");

          $.ajax({
            url: "{{ route('tours.changesttus') }}",
            method: "POST",
            data: {
              _token: token,
              id: ids,
              status: status
            },
            success: function(data) {

              if (data.status == 'success') {
                Swal.fire('Status changed successfully!', '', 'success');

                table.ajax.reload(null, false); // 🔥 important

                $('.checked_all').prop('checked', false);
                $('#check_all').prop('checked', false);

              } else {
                Swal.fire('Error updating status', '', 'error');
              }
            }
          });
        }

        let objectivesArray = [];

// Render pills
function renderObjectives() {
    let container = $('#objectiveContainer');
    container.empty();

    objectivesArray.forEach((obj, index) => {
        container.append(`
            <div class="objective-pill">
                ${obj}
                <span class="remove-btn" data-index="${index}">&times;</span>
            </div>
        `);
    });

    // Update hidden input
    $('#objectives').val(objectivesArray.join(','));

    $('.objective-option').each(function() {
        const value = $(this).data('value');
        $(this).toggleClass('btn-primary active', objectivesArray.includes(value));
        $(this).toggleClass('btn-outline-primary', !objectivesArray.includes(value));
    });
}

// Add new objective
$('#addObjective').click(function() {
    let value = $('#objectiveInput').val().trim();

    if (value && !objectivesArray.includes(value)) {
        objectivesArray.push(value);
        renderObjectives();
        $('#objectiveInput').val('');
    }
});

$(document).on('click', '.objective-option', function() {
    const value = $(this).data('value');

    if (objectivesArray.includes(value)) {
        objectivesArray = objectivesArray.filter(item => item !== value);
    } else {
        objectivesArray.push(value);
    }

    renderObjectives();
});

// Enter key support
$('#objectiveInput').keypress(function(e) {
    if (e.which === 13) {
        $('#addObjective').click();
        return false;
    }
});

// Remove objective
$(document).on('click', '.remove-btn', function() {
    let index = $(this).data('index');
    objectivesArray.splice(index, 1);
    renderObjectives();
});

      })
    </script>


</x-app-layout>
