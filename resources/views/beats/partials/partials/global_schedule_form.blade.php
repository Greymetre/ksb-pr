<form id="globalScheduleForm">
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
                    <th>Recurrence</th> <!-- renamed -->
                    <th>Start Date</th>
                    <th>End Date</th> <!-- always visible -->
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="text-right mt-3">
        <button type="submit" class="btn btn-primary px-5">
            Save
        </button>
    </div>
</form>

</form>


<script>
function generateRow(index){

    return `
    <tr>
        <td class="row-index">${index}</td>

        <td>
            <select name="users[]" class="form-control" required>
                <option value="">Select User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </td>

        <td>
            <select name="beats[]" class="form-control" required>
                <option value="">Select Beat</option>
                @foreach($beats as $beat)
                    <option value="{{ $beat->id }}">{{ $beat->beat_name }}</option>
                @endforeach
            </select>
        </td>

        <td>
            <select name="recurrence[]" class="form-control" required>
                <option value="">Select Recurrence</option>
                <option value="single">Single Date</option>
                <option value="multiple">Multiple Date</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
        </td>

        <td>
            <input type="date" name="start_date[]" class="form-control" required>
        </td>

        <td>
            <input type="date" name="end_date[]" class="form-control" required>
        </td>

        <td>
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

    $('#scheduleTable tbody').append(generateRow(rowCount));
});

// Remove Row
$(document).on('click', '.removeRow', function () {

    $(this).closest('tr').remove();

    $('#scheduleTable tbody tr').each(function(index) {
        $(this).find('.row-index').text(index + 1);
    });
});



// Show/Hide End Date based on schedule type
$(document).on('change', '.scheduleType', function(){

    let value = $(this).val();
    let row = $(this).closest('tr');

    if(value === 'weekly' || value === 'monthly'){

        row.find('.end-date').show();
        $('.end-date-header').show();
        row.find('input[name="end_date[]"]').attr('required', true);

    } else {

        row.find('.end-date').hide();
        row.find('input[name="end_date[]"]').removeAttr('required');
    }
});


// Default first row
$(document).ready(function(){
    $('#scheduleTable tbody').append(generateRow(1));
});
</script>