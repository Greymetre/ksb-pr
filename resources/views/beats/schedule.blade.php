<x-app-layout>

<style>

/* ===== TABLE LAYOUT FIX ===== */
#tab_beat_schedule {
    table-layout: fixed;
    width: 100%;
}

#tab_beat_schedule th,
#tab_beat_schedule td {
    vertical-align: middle;
    padding: 8px;
}

/* ===== FIX COLUMN WIDTHS ===== */
#tab_beat_schedule th:nth-child(1),
#tab_beat_schedule td:nth-child(1) {
    width: 5%;
}

#tab_beat_schedule th:nth-child(2),
#tab_beat_schedule td:nth-child(2) {
    width: 25%;
}

#tab_beat_schedule th:nth-child(3),
#tab_beat_schedule td:nth-child(3) {
    width: 25%;
}

#tab_beat_schedule th:nth-child(4),
#tab_beat_schedule td:nth-child(4) {
    width: 20%;
}

#tab_beat_schedule th:nth-child(5),
#tab_beat_schedule td:nth-child(5) {
    width: 15%;
}

#tab_beat_schedule th:nth-child(6),
#tab_beat_schedule td:nth-child(6) {
    width: 10%;
}

/* ===== FORCE FULL WIDTH INPUTS ===== */
#tab_beat_schedule .form-control {
    width: 100% !important;
}

/* ===== SELECT2 WIDTH FIX ===== */
#tab_beat_schedule .select2-container {
    width: 100% !important;
}

/* ===== FLATPICKR WIDTH FIX ===== */
.flatpickr-wrapper {
    width: 100% !important;
}

.flatpickr-input[readonly] {
    width: 100% !important;
}

/* ===== REMOVE HORIZONTAL SCROLL BREAK ===== */
.table-responsive {
    overflow-x: auto;
}

/* ===== BUTTON ALIGNMENT ===== */
#tab_beat_schedule .btn {
    padding: 6px 10px;
}

</style>

<div class="row mt-4">
      <div class="col-lg-12">
		<div class="card mt-4" data-animation="true">
			<div class="card-body">
         @if(session()->has('message_success'))
         <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="material-icons">close</i>
          </button>
          <span>
          {{ session()->get('message_success') }}
          </span>
        </div>
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
				<h5 class="font-weight-normal mt-4"> Schedule Beat</h5>
            <div class="row p-3">
            <div class="table-responsive">
            <a href="javascript:;" title="Add a row" class="btn btn-just-icon btn-success pull-right add-schedule-rows" onclick="getScheduleUserlist()">+</a>
                  <form action="{{ route('beats.saveIndividualSchedule')  }}" class="form-horizontal" method="post">
                     {{ csrf_field() }}
                     <input type="hidden" name="beat_id" id="beat_id" value="{!! old( 'beat_id', $beats->id) !!}">
                     <table class="table beat-schedule-rows" id="tab_beat_schedule">
<thead>
<tr>
    <th>#</th>
    <th>User Name</th>
    <th>Start Date</th>
    <th>Schedule Type</th>
    <th style="display:none;">End Date</th>
    <th></th>
</tr>
</thead>
<tbody></tbody>
                     </table>
                     <button class="btn btn-theme pull-right"> Add</button>
                     </form>
                  </div>
               </div>
			</div>
		</div>
	</div>
</div>
<script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
<!-- <script src="{{ asset('assets/js/jquery.beat.js') }}"></script> -->
<!-- <script src="{{ url('/').'/'.asset('assets/js/jquery.beat.js') }}"></script> -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
   $(document).ready(function(){

    let counter = 0;

$('a.add-schedule-rows').click(function(e){

    e.preventDefault();
    counter++;

    var newRow = `
    <tr>
        <td>${counter}</td>

        <td>
            <select name="beatdetail[${counter}][user_id]" 
                    class="form-control user select2"></select>
        </td>

<td>
    <input type="text"
           name="beatdetail[${counter}][start_date]"
           class="form-control startPicker">

    <input type="hidden"
           name="beatdetail[${counter}][multiple_dates]"
           class="multipleDates">
</td>

        <td>
            <select name="beatdetail[${counter}][schedule_type]"
                    class="form-control scheduleType">
                <option value="">Select Type</option>
                <option value="single">Does Not Repeat</option>
            </select>
        </td>

        <td style="display:none;">
            <input type="text"
                   name="beatdetail[${counter}][end_date]"
                   class="form-control endPicker">
        </td>

        <td>
            <a class="remove-rows btn btn-danger">
                <i class="material-icons">close</i>
            </a>
        </td>
    </tr>`;

    $('#tab_beat_schedule tbody').append(newRow);

    let lastRow = $('#tab_beat_schedule tbody tr:last');

    initFlatpickr(lastRow.find('.startPicker')[0]);
    initFlatpickr(lastRow.find('.endPicker')[0]);

    getScheduleUserlist();
});

    $('#tab_beat_schedule').on('click','.remove-rows',function(){
        $(this).closest('tr').remove();
    });

});
function initFlatpickr(element) {

    if (!element) return;

    if (element._flatpickr) {
        element._flatpickr.destroy();
    }

    flatpickr(element, {
        mode: "multiple",
        dateFormat: "Y-m-d",

        altInput: true,
        altFormat: "Y-m-d",
        altInputClass: "form-control",

        allowInput: false,

        onReady: function(selectedDates, dateStr, instance) {
            instance.altInput.placeholder = "Select Date";
        },

        onChange: function(selectedDates, dateStr, instance) {

            if (!$(element).hasClass('startPicker')) return;

            let row = $(instance.element).closest('tr');
            let recurrenceDropdown = row.find('.scheduleType');
            let endTd = row.find('.endPicker').closest('td');
            let hiddenInput = row.find('.multipleDates');

            // ===== WHEN NOTHING SELECTED =====
            if (selectedDates.length === 0) {
                instance.altInput.value = "";
                instance.altInput.placeholder = "Select Date";
                hiddenInput.val("");
                recurrenceDropdown.html(`<option value="">Select Type</option>`);
                return;
            }

            // ===== SINGLE DATE =====
            if (selectedDates.length === 1) {

                let selectedDate = selectedDates[0];
                let dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' });
                let weekNumber = Math.ceil(selectedDate.getDate() / 7);

                function getOrdinal(n) {
                    if (n === 1) return "st";
                    if (n === 2) return "nd";
                    if (n === 3) return "rd";
                    return "th";
                }

                recurrenceDropdown.html(`
                    <option value="">Select Type</option>
                    <option value="single">Does Not Repeat</option>
                    <option value="weekly">Weekly On ${dayName}</option>
                    <option value="monthly">Monthly ${weekNumber}${getOrdinal(weekNumber)} ${dayName}</option>
                `);

                recurrenceDropdown.val('single').trigger('change');
                endTd.hide();

                hiddenInput.val(dateStr); // store single date also
            }

            // ===== MULTIPLE DATES =====
            else if (selectedDates.length > 1) {

                recurrenceDropdown.html(`
                    <option value="multiple">Does Not Repeat</option>
                `).val('multiple').trigger('change');

                hiddenInput.val(dateStr); // store comma separated dates

                // Show only "Multiple" in visible field
                instance.altInput.value = "Multiple";

                // Keep actual values internally
                instance.element.value = dateStr;

                endTd.hide();
            }
        }
    });
}
$(document).on('change', '.scheduleType', function(){

    let type = $(this).val();
    let row = $(this).closest('tr');
    let endTd = row.find('.endPicker').closest('td');

    if(type === 'weekly' || type === 'monthly'){
        endTd.show();
    } else {
        endTd.hide();
    }

});

function getScheduleUserlist()
{
    var token = $("meta[name='csrf-token']").attr("content");
    var base_url = $('.baseurl').data('baseurl'); 
    var beat_id = $('#beat_id').val();

    $.ajax({
        url: base_url + '/getUserList',
        dataType: "json",
        type: "POST",
        data:{ "_token": token, beat_id : beat_id},
        success: function(res){

            if(res){

                var $select = $('#tab_beat_schedule tbody tr:last').find(".user");

                $select.empty();
                $select.append('<option value="">Select User</option>');
                
                $.each(res,function(key,value){ 
                    $select.append(
                      '<option value="'+value.id+'">'+value.name+' '+value.mobile+'</option>'
                    );
                });

                $select.select2({
                    placeholder: "Select User",
                    width: '100%'
                });
            }
        }
    });
}

$('form').on('submit', function(e){

    e.preventDefault();

    var form = $(this);

    $.ajax({
        url: form.attr('action'),
        type: "POST",
        data: form.serialize(),
        success: function(response){

            if(response.status){

                alert(response.message);

                location.reload();
            }
        }
    });

});




</script>

</x-app-layout>