      <x-app-layout>

  <style>

  /* Prevent text wrapping */
  #scheduleTable td,
  #scheduleTable th {
      white-space: nowrap;
  }

  /* Fix table layout so columns expand properly */
  #scheduleTable {
      table-layout: auto !important;
      width: 100%;
  }

  /* User & Beat select width */
  .scheduleSelect {
      min-width: 220px;
      width: 100%;
  }

  /* Date column width control */
  #scheduleTable td.date-column {
      min-width: 260px !important;
  }

  /* Start & End date inputs */
  .startPicker,
  .endPicker {
      min-width: 260px !important;
      width: 100% !important;
  }

  /* Flatpickr visible input (altInput) */
  .flatpickr-alt-input {
      min-width: 260px !important;
      width: 100% !important;
  }

  /* Hidden multiple date storage */
  .multiPicker {
      min-width: 260px;
      width: 100%;
  }

  /* End date column */
  #scheduleTable td.end-date {
      min-width: 260px;
  }

  /* Make remove button column compact */
  #scheduleTable td:last-child {
      width: 70px;
  }

  /* Optional: make modal more spacious */
  .modal-lg {
      max-width: 95% !important;
  }

  </style>
          <div class="row">
              <div class="col-md-12">
                  <div class="card">
                      <div class="card-header card-header-icon card-header-theme">
                          <div class="card-icon">
                              <i class="material-icons">perm_identity</i>
                          </div>

                          <h4 class="card-title ">{!! trans('panel.beat.title_singular') !!}{!! trans('panel.global.list') !!}
                          </h4>
                          <div class="card p-3 mb-3">
                              <div class="row">

                                  <div class="col-md-3">
                                      <!-- <input type="text" id="filter_beat_name" class="form-control" placeholder="Beat Name"> -->
                                      <select id="filter_beat_name" class="form-control">
                                          <option value="">Select Beat</option>
                                          @foreach($beatsList as $beat)
                                          <option value="{{ $beat->id }}">{{ $beat->beat_name }}</option>
                                          @endforeach
                                      </select>
                                  </div>

                                  <div class="col-md-3">
                                      <select id="filter_state" class="form-control">
                                          <option value="">Select State</option>
                                          @foreach($states as $state)
                                          <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                                          @endforeach
                                      </select>
                                  </div>

                                  <div class="col-md-3">
                                      <select id="filter_district" class="form-control">
                                          <option value="">Select District</option>
                                      </select>
                                  </div>

                                  <div class="col-md-3">
                                      <select id="filter_city" class="form-control">
                                          <option value="">Select City</option>
                                      </select>
                                  </div>

                              </div>
                          </div>
                          <span class="">
                              <div class="btn-group header-frm-btn">
                                  <div class="next-btn">
      @if(true)
      <a href="javascript:void(0);" 
        id="globalScheduleBtn" 
        class="btn btn-just-icon btn-theme"
        title="Global Schedule">
          <i class="material-icons">calendar_today</i>
      </a>
      @endif


                                      @if(auth()->user()->can(['beat_upload']))
                                      <form action="{{ URL::to('beats-upload') }}" class="form-horizontal" method="post"
                                          enctype="multipart/form-data">
                                          {{ csrf_field() }}
                                          <div class="input-group">
                                              <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                                  <span class="btn btn-just-icon btn-theme btn-file">
                                                      <span class="fileinput-new"><i
                                                              class="material-icons">attach_file</i></span>
                                                      <span class="fileinput-exists">Change</span>
                                                      <input type="hidden">
                                                      <input type="file" name="import_file" required accept=".xls,.xlsx" />
                                                  </span>
                                              </div>
                                              <div class="input-group-append">
                                                  <button class="btn btn-just-icon btn-theme"
                                                      title="{!!  trans('panel.global.upload') !!} {!! trans('panel.beat.title') !!}">
                                                      <i class="material-icons">cloud_upload</i>
                                                      <div class="ripple-container"></div>
                                                  </button>
                                              </div>
                                          </div>
                                      </form>
                                      @endif

                                      @if(auth()->user()->can(['beat_download']))
                                      <a href="{{ URL::to('beats-download') }}" class="btn btn-just-icon btn-theme"
                                          title="{!!  trans('panel.global.download') !!} {!! trans('panel.beat.title') !!}"><i
                                              class="material-icons">cloud_download</i></a>
                                      @endif
                                      @if(auth()->user()->can(['beat_template']))
                                      <a href="{{ URL::to('beats-template') }}" class="btn btn-just-icon btn-theme"
                                          title="{!!  trans('panel.global.template') !!} {!! trans('panel.beat.title_singular') !!}"><i
                                              class="material-icons">text_snippet</i></a>
                                      @endif
                                      @if(auth()->user()->can(['beat_create']))
                                      <a href="{{ route('beats.create') }}" class="btn btn-just-icon btn-theme"
                                          title="{!!  trans('panel.global.add') !!} {!! trans('panel.beat.title_singular') !!}"><i
                                              class="material-icons">add_circle</i></a>
                                      @endif
                                  </div>
                              </div>
                          </span>

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
                              <table id="getbeat" class="table">
                                  <thead class=" text-primary">
                                      <th>{!! trans('panel.global.no') !!}</th>
                                      <th>{!! trans('panel.global.action') !!}</th>
                                      <th>{!! trans('panel.beat.beat_name') !!}</th>
                                      <th>{!! trans('panel.beat.description') !!}</th>
                                      <th>{!! trans('panel.global.city') !!}</th>
                                      <th>{!! trans('panel.global.district') !!}</th>
                                      <th>{!! trans('panel.global.state') !!}</th>
                                      <th>{!! trans('panel.global.created_by') !!}</th>
                                      <th>{!! trans('panel.global.created_at') !!}</th>
                                  </thead>
                                  <tbody>
                                  </tbody>
                              </table>
                          </div>
                      </div>
                      <div class="modal fade" id="globalScheduleModal" tabindex="-1">
                          <div class="modal-dialog modal-lg">
                              <div class="modal-content">

                                  <div class="modal-header">
                                      <h5 class="modal-title">Global Beat Schedule</h5>
                                      <button type="button" class="close" data-dismiss="modal">
                                          <span>&times;</span>
                                      </button>
                                  </div>

                                  <div class="modal-body" id="scheduleFormContainer">
        <form id="globalScheduleForm">
          @csrf

          <div class="d-flex justify-content-between align-items-center mb-4">
              <h5>Global Beat Schedule</h5>

              <button type="button" id="addRowBtn"
                  class="btn btn-success btn-just-icon"
                  style="width:45px;height:45px;border-radius:8px;">
                  <i class="material-icons">add</i>
              </button>
          </div>

          <div class="table-responsive">
              <table class="table" id="scheduleTable">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>User</th>
                          <th>Beat</th>
                          
                          <th>Date</th>
                          <th>Recurrence</th>
                          <th>End Date</th>
                          <th class="end-date-header" style="display:none;">End Date</th>
                          <th></th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>

          <div class="text-right mt-3">
              <button type="submit" class="btn btn-primary px-5">
                  Save
              </button>
          </div>
      </form>
                                  </div>

                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>


          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


          <script>

                    function initSelect2(row){

        row.find('.scheduleSelect').select2({
            width: '100%',
            dropdownParent: $('#globalScheduleModal')
        });

    }
          $(document).ready(function() {

  


              // ✅ DataTable Init
              var table = $('#getbeat').DataTable({
                  processing: true,
                  serverSide: true,
                  order: [
                      [0, 'desc']
                  ],
                  ajax: {
                      url: "{{ route('beats.index') }}",
                      data: function(d) {
                          d.beat_id = $('#filter_beat_name').val();
                          d.state_id = $('#filter_state').val();
                          d.district_id = $('#filter_district').val();
                          d.city_id = $('#filter_city').val();
                      }
                  },
                  columns: [{
                          data: 'DT_RowIndex',
                          orderable: false,
                          searchable: false
                      },
                      {
                          data: 'action',
                          orderable: false,
                          searchable: false
                      },
                      {
                          data: 'beat_name'
                      },
                      {
                          data: 'description'
                      },
                      {
                          data: 'city_name',
                          orderable: false,
                          searchable: false
                      },
                      {
                          data: 'district_name',
                          orderable: false,
                          searchable: false
                      },
                      {
                          data: 'statename.state_name',
                          orderable: false,
                          searchable: false
                      },
                      {
                          data: 'createdbyname.name',
                          orderable: false,
                          searchable: false
                      },
                      {
                          data: 'created_at',
                          orderable: false,
                          searchable: false
                      },
                  ]
              });

              // ✅ Filter redraw
              $('#filter_beat_name, #filter_state, #filter_district, #filter_city')
                  .on('keyup change', function() {
                      table.draw();
                  });

              // 🔹 State Change → Load District
              $('#filter_state').on('change', function() {

                  let state_id = $(this).val();

                  $('#filter_district').html('<option value="">Loading...</option>');
                  $('#filter_city').html('<option value="">Select City</option>');

                  if (state_id) {
                      $.ajax({
                          url: "{{ url('get-districts') }}/" + state_id,
                          type: 'GET',
                          success: function(data) {

                              let options = '<option value="">Select District</option>';

                              $.each(data, function(key, district) {
                                  options += '<option value="' + district.id + '">' +
                                      district.district_name + '</option>';
                              });

                              $('#filter_district').html(options);
                          }
                      });
                  } else {
                      $('#filter_district').html('<option value="">Select District</option>');
                  }
              });

              // 🔹 District Change → Load City
              $('#filter_district').on('change', function() {

                  let district_id = $(this).val();

                  $('#filter_city').html('<option value="">Loading...</option>');

                  if (district_id) {
                      $.ajax({
                          url: "{{ url('get-cities') }}/" + district_id,
                          type: 'GET',
                          success: function(data) {

                              let options = '<option value="">Select City</option>';

                              $.each(data, function(key, city) {
                                  options += '<option value="' + city.id + '">' + city
                                      .city_name + '</option>';
                              });

                              $('#filter_city').html(options);
                          }
                      });
                  } else {
                      $('#filter_city').html('<option value="">Select City</option>');
                  }
              });

              $(document).on('click', '.deleteBeat', function(e) {
                  e.preventDefault();

                  var id = $(this).data('id');

                  if (confirm('Are you sure you want to delete this beat?')) {

                      $.ajax({
                          url: '/beats/' + id,
                          type: 'DELETE',
                          data: {
                              _token: '{{ csrf_token() }}'
                          },
                          success: function(response) {

                              if (response.status) {

                                  alert('Deleted Successfully');


                                  table.ajax.reload(null, false);
                                  // null = keep paging
                                  // false = don't reset pagination

                              } else {
                                  alert('Something went wrong');
                              }
                          }
                      });
                  }
              });

              // $('#globalScheduleBtn').on('click', function() {

              //     $('#scheduleFormContainer').html('<div class="text-center p-3">Loading...</div>');

              //     $('#globalScheduleModal').modal('show');

              //     $.ajax({
              //         url: "{{ route('beats.beatdetail') }}", // ya jo route tum form ke liye use kar rahe ho
              //         type: "GET",
              //         success: function(response) {

              //             $('#scheduleFormContainer').html(response);

              //         },
              //         error: function() {
              //             $('#scheduleFormContainer').html(
              //                 '<div class="text-danger text-center">Failed to load form</div>'
              //                 );
              //         }
              //     });

              // });

              

              $(document).ready(function() {
                  $('#filter_beat_name, #filter_state, #filter_district, #filter_city').select2({
                      width: '100%',
                      dropdownParent: $('body')
                  });

              });
          });
          </script>
      <script>
  $(document).on('click', '#globalScheduleBtn', function () {

      let tbody = $('#scheduleTable tbody');

      // clear old rows
      tbody.html('');

      // add fresh row
      let firstRow = $(generateRow(1));
      tbody.append(firstRow);

      initFlatpickr(firstRow.find('.startPicker')[0]);
      initFlatpickr(firstRow.find('.endPicker')[0]);
      initSelect2(firstRow);

      $('#globalScheduleModal').modal('show');
  });

      function generateRow(index){

        return `
        <tr>
        <td class="row-index" style="width:50px;">${index}</td>

        <td class="date-column">
            <select name="users[]" class="form-control scheduleSelect " required>
                <option value="">Select User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </td>

        <td class="date-column"">
            <select name="beats[]" class="form-control scheduleSelect" required>
                <option value="">Select Beat</option>
                @foreach($beatsList as $beat)
                    <option value="{{ $beat->id }}">{{ $beat->beat_name }}</option>
                @endforeach
            </select>
        </td>

        <td class="date-column"">
            <input type="text" name="start_date[]" 
                class="form-control startPicker" 
                required autocomplete="off"
                placeholder="Select Date">
        </td>

        <td class="multi-date date-column" style=" display:none;">
            <input type="text" name="multiple_dates[]" 
                class="form-control multiPicker" 
                autocomplete="off">
        </td>

        <td class="date-column"">
            <select name="schedule_type[]" class="form-control scheduleType scheduleSelect" required>
                <option value="">Select Type</option>
                <option value="single">Does Not Repeat</option>
            </select>
        </td>

<td class="end-date" style="width:160px;">
    <input type="text" name="end_date[]" 
        class="form-control endPicker" 
        autocomplete="off" disabled
        placeholder="Select Date">
</td>

        <td style="width:60px;">
            <button type="button" class="btn btn-danger btn-sm removeRow">
                <i class="material-icons">close</i>
            </button>
        </td>
    </tr>
        `;
    }


      // Add Row
    $(document).on('click', '#addRowBtn', function () {

        let rowCount = $('#scheduleTable tbody tr').length + 1;
        let row = $(generateRow(rowCount));

        $('#scheduleTable tbody').append(row);

  initFlatpickr(row.find('.startPicker')[0]);
  initFlatpickr(row.find('.endPicker')[0]);
  // initFlatpickr(row.find('.multiPicker')[0], 'multiple');

        initSelect2(row); // 👈 ADD THIS
    });


      // Remove Row
      $(document).on('click', '.removeRow', function () {
          $(this).closest('tr').remove();
          $('#scheduleTable tbody tr').each(function(index) {
              $(this).find('.row-index').text(index + 1);
          });
      });

      function getWeekOfMonth(date){
        return Math.ceil(date.getDate() / 7);
    }

      // Show/Hide End Date
    // $(document).on('change', '.scheduleType', function(){

    //     let value = $(this).val();
    //     let row = $(this).closest('tr');

    //     let startInput = row.find('.startPicker');
    //     let multiInput = row.find('.multiPicker');
    //     let endInput = row.find('.endPicker');

    //     if(value === 'multiple'){

    //         row.find('.multi-date').show();
    //         row.find('.startPicker').closest('td').hide();
    //         row.find('.end-date').hide();

    //         startInput.prop('required', false);
    //         endInput.prop('required', false);
    //         multiInput.prop('required', true);

    //     }
    //     else if(value === 'weekly' || value === 'monthly'){

    //         row.find('.startPicker').closest('td').show();
    //         row.find('.end-date').show();
    //         row.find('.multi-date').hide();

    //         startInput.prop('required', true);
    //         endInput.prop('required', true);
    //         multiInput.prop('required', false);

    //     }
    //     else { // single

    //         row.find('.startPicker').closest('td').show();
    //         row.find('.multi-date').hide();
    //         row.find('.end-date').hide();

    //         startInput.prop('required', true);
    //         endInput.prop('required', false);
    //         multiInput.prop('required', false);
    //     }

    // });

    $(document).on('change', '.scheduleType', function(){

    let value = $(this).val();
    let row = $(this).closest('tr');

    let startInput = row.find('.startPicker');
    let multiInput = row.find('.multiPicker');
    let endInput = row.find('.endPicker');

    // End date column always visible
    row.find('.end-date').show();

    if(value === 'single'){ // Does Not Repeat

        endInput.prop('disabled', true);
        endInput.val('');

        startInput.prop('required', true);
        endInput.prop('required', false);
        multiInput.prop('required', false);
    }
else if(value === 'weekly' || value === 'monthly'){

    endInput.prop('disabled', false);

    // 🔥 Reinitialize flatpickr after enabling
    if(endInput[0]._flatpickr){
        endInput[0]._flatpickr.destroy();
    }

    initFlatpickr(endInput[0]);

    startInput.prop('required', true);
    endInput.prop('required', true);
    multiInput.prop('required', false);
}
    else if(value === 'multiple'){

        endInput.prop('disabled', true);
        endInput.val('');

        startInput.prop('required', false);
        endInput.prop('required', false);
        multiInput.prop('required', false);
    }

});

      $('#globalScheduleForm').on('submit', function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('updateschedule') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response){
                alert('Schedule Saved Successfully');
                document.activeElement.blur();
                $('#globalScheduleModal').modal('hide');
            },
            error: function(xhr){
                alert('Something went wrong');
            }
        });
    });

  $('#globalScheduleModal').on('hidden.bs.modal', function () {

      let form = $('#globalScheduleForm');

      form[0].reset();

      form.find('.select2').val(null).trigger('change');

      form.find('.startPicker, .endPicker').each(function(){
          if(this._flatpickr){
              this._flatpickr.clear();
          }
      });

      $('#scheduleTable tbody').html('');
  });

    // function initFlatpickr(element, type = 'single') {

    //     if (element._flatpickr) {
    //         element._flatpickr.destroy();
    //     }

    //     if(type === 'multiple'){
    //         flatpickr(element, {
    //             mode: "multiple",
    //             dateFormat: "Y-m-d"
    //         });
    //     } else {
    //         flatpickr(element, {
    //             dateFormat: "Y-m-d"
    //         });
    //     }
    // }

    // function initFlatpickr(element, type = 'single') {

    //     if (element._flatpickr) {
    //         element._flatpickr.destroy();
    //     }

    //     if(type === 'multiple'){
    //         flatpickr(element, {
    //             mode: "multiple",
    //             dateFormat: "Y-m-d",
    //             onChange: function(selectedDates, dateStr, instance) {
    //                 console.log("Multiple Dates Selected:", dateStr);
    //                 console.log("Selected Dates Array:", selectedDates);
    //             }
    //         });
    //     } 
    //     else {
    //         flatpickr(element, {
    //             dateFormat: "Y-m-d",
    //             onChange: function(selectedDates, dateStr, instance) {

    //                 if($(element).hasClass('startPicker')){
    //                     console.log("Start Date Selected:", dateStr);
    //                 }

    //                 if($(element).hasClass('endPicker')){
    //                     console.log("End Date Selected:", dateStr);
    //                 }

    //                 console.log("Selected Date Object:", selectedDates);
    //             }
    //         });
    //     }
    // }

  //   function initFlatpickr(element, type = 'single') {

  //       if (element._flatpickr) {
  //           element._flatpickr.destroy();
  //       }

  //       if(type === 'multiple'){
  //           flatpickr(element, {
  //               mode: "multiple",
  //               dateFormat: "Y-m-d"
  //           });
  //       } 
  //   else {
  //     flatpickr(element, {
  //         mode: "multiple",
  //         dateFormat: "Y-m-d",

  //         onChange: function(selectedDates, dateStr, instance) {

  //             let row = $(element).closest('tr');
  //             let recurrenceDropdown = row.find('.scheduleType');

  //             if(selectedDates.length === 1){

  //                 let selectedDate = selectedDates[0];
  //                 let dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'short' });
  //                 let weekNumber = getWeekOfMonth(selectedDate);

  //                 instance.input.value = instance.formatDate(selectedDate, "Y-m-d");

  //                 recurrenceDropdown.html(`
  //                     <option value="">Select Type</option>
  //                     <option value="single">Does Not Repeat</option>
  //                     <option value="weekly">Weekly On ${dayName}</option>
  //                     <option value="monthly">Monthly ${weekNumber}${getOrdinal(weekNumber)} ${dayName}</option>
  //                 `);
  //             }

  //             else if(selectedDates.length > 1){

  //                 // 👇 Input me sirf "Multiple" show hoga
  //                 instance.input.value = "Multiple";

  //                 recurrenceDropdown.html(`
  //                     <option value="">Select Type</option>
  //                     <option value="single">Does Not Repeat</option>
  //                 `);

  //                 recurrenceDropdown.val('single').trigger('change');
  //             }

  //         }
  //     });
  // }
  //   }

function initFlatpickr(element) {

    if (element._flatpickr) {
        element._flatpickr.destroy();
    }

    // ✅ END DATE PICKER
    if ($(element).hasClass('endPicker')) {

        flatpickr(element, {
            mode: "single",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "Y-m-d",
            altInputClass: "form-control"
        });

        return;
    }

    // ✅ START DATE PICKER
    flatpickr(element, {

        mode: "multiple",
        dateFormat: "Y-m-d",

        altInput: true,
        altFormat: "Y-m-d",
        altInputClass: "form-control",

        onChange: function(selectedDates, dateStr, instance) {

            let row = $(instance.element).closest('tr');
            let recurrenceDropdown = row.find('.scheduleType');
            let multiInput = row.find('.multiPicker');
            let endInput = row.find('.endPicker');

            if (selectedDates.length === 1) {

                let selectedDate = selectedDates[0];

                let dayFull = selectedDate.toLocaleDateString('en-US',{weekday:'long'});
                let dayShort = selectedDate.toLocaleDateString('en-US',{weekday:'short'});

                let weekNumber = Math.ceil(selectedDate.getDate()/7);

                function getOrdinal(n){
                    if(n==1) return "st";
                    if(n==2) return "nd";
                    if(n==3) return "rd";
                    return "th";
                }

                let optionsHtml = `
                    <option value="">Select Type</option>
                    <option value="single">Does Not Repeat</option>
                    <option value="weekly">Weekly on ${dayFull}</option>
                `;

                // only 5th weekday = last
                if(weekNumber === 5){

                    optionsHtml += `
                        <option value="monthly">
                            Last ${dayShort} of the month
                        </option>
                    `;

                } else {

                    optionsHtml += `
                        <option value="monthly">
                            ${weekNumber}${getOrdinal(weekNumber)} ${dayShort} of the month
                        </option>
                    `;
                }

                recurrenceDropdown.html(optionsHtml);
                recurrenceDropdown.val('single').trigger('change');

                multiInput.val('');
                endInput.val('');

                instance.altInput.value = instance.formatDate(selectedDate,"Y-m-d");
            }

            else if(selectedDates.length > 1){

                recurrenceDropdown.html(`
                    <option value="multiple">Does Not Repeat</option>
                `).val('multiple').trigger('change');

                multiInput.val(dateStr);

                instance.element.value = '';

                endInput.val('');

                instance.altInput.value = "Multiple";
            }
        }
    });
}

    // function getOrdinal(n) {
    //     if (n == 1) return "st";
    //     if (n == 2) return "nd";
    //     if (n == 3) return "rd";
    //     return "th";
    // }

    // Start Date Picker
    // $(document).on('focus', '.startPicker', function(){

    //     if (!this._flatpickr) {
    //         flatpickr(this, {
    //             dateFormat: "Y-m-d"
    //         });
    //     }

    // });

    // $(document).on('focus', '.endPicker', function(){

    //     if (!this._flatpickr) {
    //         flatpickr(this, {
    //             dateFormat: "Y-m-d"
    //         });
    //     }

    // });

    // Multiple Date Picker
    // $(document).on('focus', '.multiPicker', function(){

    //     if (!this._flatpickr) {
    //         flatpickr(this, {
    //             mode: "multiple",
    //             dateFormat: "Y-m-d",
    //             onChange: function(selectedDates, dateStr, instance) {

    //                 console.log("Selected Multiple Dates:", dateStr);

    //             }
    //         });
    //     }

    // });


      // Default first row
    $(document).ready(function(){

        let firstRow = $(generateRow(1));
        $('#scheduleTable tbody').append(firstRow);

        initFlatpickr(firstRow.find('.startPicker')[0]);
        initFlatpickr(firstRow.find('.endPicker')[0]);
  // initFlatpickr(firstRow.find('.multiPicker')[0], 'multiple');

        initSelect2(firstRow); // 👈 ADD THIS
    });
      </script>



      </x-app-layout>