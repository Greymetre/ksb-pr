<x-app-layout>
    <style>
.select2-container {
    z-index: 9999 !important; /* not too high */
}

.modal {
    z-index: 10500 !important;
}

.modal-backdrop {
    z-index: 10400 !important;
}

.modal-dialog {
    z-index: 10600 !important;
}

.ui-datepicker {
    z-index: 10700 !important;
}

    </style>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- Main content -->
    <section class="content new_item">
        <div class="container-fluid">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header card-header-icon card-header-theme">
                            <h3 class="card-title">Tour Create</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        @if($errors->any())
                        <div>
                            <ul class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="card-body ">
                            <div class="tab-content tab-space">
                                {!! Form::model($tours,[
                                'route' => $tours->exists ? ['tours.update', $tours->id] : 'tours.store',
                                'method' => $tours->exists ? 'PUT' : 'POST',
                                'id' => 'createCompany',
                                'files'=>true
                                ]) !!}
                                <div class="row">
                                    <input type="hidden" name="id" id="id">
                                    <div class="table-responsive w-100">
                                        <table class="table kvcodes-dynamic-rows-example" id="tab_logic">
                                            <thead>
                                                <tr class="text-white">
                                                    <th class="text-center">Date</th>
                                                    <th>User</th>
                                                    <th class="text-center">District</th> <!-- new -->
                                                    <th class="text-center">City</th>
                                                    <th class="text-center">Objectives</th>
                                                    <th class="text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr id='addr0' value="1">
                                                    <td class='col-3'>
                                                        <input type="text" name="detail[1][date]"
                                                            class="form-control datepicker" autocomplete="off" style="z-index: 1000000"/>
                                                    </td>
                                                    <td class='col-3'>
                                                        <select class="form-control select2 user-select"
                                                            name="detail[1][userid]" data-row="1">
                                                            <option value="">Select User</option>
                                                            @foreach($users as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class='col-3'>
                                                        <select class="form-control select2 district-select"
                                                            name="detail[1][district]" data-row="1">
                                                            <option value="">Select District</option>
                                                        </select>
                                                    </td>
                                                    <td class='col-3'>
                                                        <select class="form-control select2 city-select"
                                                            name="detail[1][city]" data-row="1">
                                                            <option value="">Select District First</option>
                                                        </select>
                                                    </td>
                                                    <td class='col-3'>
                                              <input type="text" name="detail[1][objectives]" 
       class="form-control objective-input" 
       readonly 
       placeholder="Select Objectives" />
                                                    </td>
                                                    <td class="td-actions text-center">
                                                        <a class="remove btn btn-danger btn-xs remove-rows"><i
                                                                class="fa fa-minus"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="td-actions text-left">
                                                    <a href="javascript:void(0)" class="btn btn-xs add-rows">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- <table class="table">
                                        <tbody>
                                            <tr>
                                                <td class="td-actions text-left">
                                                    <a href="javascript:void(0)" class="btn  btn-xs add-rows"
                                                        onclick="getUserlist()"> <i class="fa fa-plus"></i> </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table> -->
                                </div>
                                {{ Form::submit('Submit', array('class' => 'btn btn-theme pull-right')) }}
                                {{ Form::close() }}

<!-- Objective Modal -->
<div class="modal fade" id="objectiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title">Select Objectives</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <!-- Buttons -->
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary obj-btn" data-value="Retailer Visit">Retailer Visit</button>
                    <button type="button" class="btn btn-outline-primary obj-btn" data-value="Retailer Meet">Retailer Meet</button>
                    <button type="button" class="btn btn-outline-primary obj-btn" data-value="Nukkad Meet">Nukkad Meet</button>
                    <button type="button" class="btn btn-outline-primary obj-btn" data-value="Field Demo">Field Demo</button>
                </div>

                <!-- Manual Input -->
                <div class="form-group">
                    <label>Add Manual Objective</label>
                    <input type="text" id="manualObjective" class="form-control" placeholder="Enter custom objective">
                    <button type="button" class="btn btn-sm btn-success mt-2" id="addManual">Add</button>
                </div>

                <!-- Selected List -->
                <div>
                    <strong>Selected:</strong>
                    <div id="selectedObjectives"></div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveObjectives">Done</button>
            </div>

        </div>
    </div>
</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </section>
    <script type="text/javascript">
    $(document).ready(function() {

        var $table = $('#tab_logic');
        var counter = $table.find('tbody tr').length;

        // Initialize plugins
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('.select2').select2({
            width: '100%'
        });

        // ──────────────── When USER changes ────────────────
        $table.on('change', '.user-select', function() {

            const $row = $(this).closest('tr');
            const userId = $(this).val();

            const $districtSelect = $row.find('.district-select');
            const $citySelect = $row.find('.city-select');

            $districtSelect.html('<option value="">Select District</option>').val('');
            $citySelect.html('<option value="">Select District First</option>').val('');

            if (!userId) return;

            $.ajax({
                url: "{{ route('tours.ajaxUserDistricts') }}",
                type: "POST",
                data: {
                    user_id: userId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {

                    let options = '<option value="">Select District</option>';

                    if (res.districts) {
                        res.districts.forEach(d => {
                            options += `<option value="${d.id}">${d.name}</option>`;
                        });
                    }

                    $districtSelect.html(options);
                }
            });
        });


        // ──────────────── When DISTRICT changes ────────────────
        $table.on('change', '.district-select', function() {

            const $row = $(this).closest('tr');
            const userId = $row.find('.user-select').val();
            const districtId = $(this).val();

            const $citySelect = $row.find('.city-select');

            $citySelect.html('<option value="">Loading...</option>');

            if (!userId || !districtId) {
                $citySelect.html('<option value="">Select District First</option>');
                return;
            }

            $.ajax({
                url: "{{ route('tours.ajaxUserCitiesByDistrict') }}",
                type: "POST",
                data: {
                    user_id: userId,
                    district_id: districtId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {

                    let options = '<option value="">Select City</option>';

                    if (res.cities) {
                        res.cities.forEach(c => {
                            options += `<option value="${c.id}">${c.name}</option>`;
                        });
                    }

                    $citySelect.html(options);
                }
            });
        });


        // ──────────────── Add new row ────────────────
//         $(document).on('click', '.add-rows', function(e) {
//             e.preventDefault();

//             counter++;

//             let firstUserSelectHtml = $table.find('tbody tr:first .user-select').html();

//             let newRow = `
//         <tr value="${counter}">
//             <td>
//                 <input type="text" name="detail[${counter}][date]" 
//                        class="form-control datepicker" autocomplete="off"/>
//             </td>
//             <td>
//                 <select class="form-control select2 user-select" 
//                         name="detail[${counter}][userid]" 
//                         data-row="${counter}">
//                     <option value="">Select User</option>
//                     ${firstUserSelectHtml}
//                 </select>
//             </td>
//             <td>
//                 <select class="form-control select2 district-select" 
//                         name="detail[${counter}][district]" 
//                         data-row="${counter}">
//                     <option value="">Select User First</option>
//                 </select>
//             </td>
//             <td>
//                 <select class="form-control select2 city-select" 
//                         name="detail[${counter}][city]" 
//                         data-row="${counter}">
//                     <option value="">Select District First</option>
//                 </select>
//             </td>
//             <td>
//                 <input type="text" name="detail[${counter}][objectives]" 
//                        class="form-control"/>
//             </td>
//             <td class="td-actions text-center">
//                 <a href="javascript:void(0)" 
//                    class="remove btn btn-danger btn-xs remove-rows">
//                    <i class="fa fa-minus"></i>
//                 </a>
//             </td>
//         </tr>
//     `;

//             $table.find('tbody').append(newRow);

// // After appending new row
// let $newRow = $table.find('tbody tr:last');

// // Initialize datepicker & select2
// $newRow.find('.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });

// $newRow.find('.select2').each(function() {
//     $(this).select2({
//         width: '100%',
//         dropdownParent: $('#tab_logic').closest('.card')
//     });
// });

// // 🔹 Trigger district & city population for default user if any
// const userId = $newRow.find('.user-select').val();
// if(userId){
//     $newRow.find('.user-select').trigger('change'); // will populate district automatically
// }

//         });


//         // ──────────────── Remove row ────────────────
//         $table.on('click', '.remove-rows', function() {
//             if ($table.find('tbody tr').length > 1) {
//                 $(this).closest('tr').remove();
//             }
//         });

$(document).on('click', '.add-rows', function(e) {
    e.preventDefault();
    counter++;

    // Get first user select HTML (options only)
    let firstUserSelectHtml = $table.find('tbody tr:first .user-select option').map(function() {
        return `<option value="${$(this).val()}">${$(this).text()}</option>`;
    }).get().join('');

    // Create new row HTML
    let newRow = `
        <tr value="${counter}">
            <td>
                <input type="text" name="detail[${counter}][date]" 
                       class="form-control datepicker" autocomplete="off"/>
            </td>
            <td>
                <select class="form-control user-select" 
                        name="detail[${counter}][userid]" 
                        data-row="${counter}">
                    <option value="">Select User</option>
                    ${firstUserSelectHtml}
                </select>
            </td>
            <td>
                <select class="form-control district-select" 
                        name="detail[${counter}][district]" 
                        data-row="${counter}">
                    <option value="">Select District</option>
                </select>
            </td>
            <td>
                <select class="form-control city-select" 
                        name="detail[${counter}][city]" 
                        data-row="${counter}">
                    <option value="">Select District First</option>
                </select>
            </td>
            <td>
                <input type="text" name="detail[${counter}][objectives]" 
                       class="form-control objective-input" readonly "/>
            </td>
            <td class="td-actions text-center">
                <a href="javascript:void(0)" 
                   class="remove btn btn-danger btn-xs remove-rows">
                   <i class="fa fa-minus"></i>
                </a>
            </td>
        </tr>
    `;

    $table.find('tbody').append(newRow);

    let $newRow = $table.find('tbody tr:last');

    // Initialize datepicker
    $newRow.find('.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });

    // Initialize select2
    $newRow.find('.user-select, .district-select, .city-select').select2({
        width: '100%',
        dropdownParent: $newRow.closest('table').parent()
    });

    // Ensure all selects are empty
    $newRow.find('.user-select, .district-select, .city-select').val(null).trigger('change');
});

let currentInput = null;
let selectedObjectives = [];

// Open modal
$(document).on('click', '.objective-input', function () {
    currentInput = $(this);

    // STEP 1: reset
    selectedObjectives = [];
    $('#selectedObjectives').html('');
    $('#manualObjective').val('');
    $('.obj-btn').removeClass('active btn-primary')
                 .addClass('btn-outline-primary');

    // STEP 2: GET EXISTING VALUE
    let existing = currentInput.val();

    if (existing) {
        selectedObjectives = existing.split(',').map(v => v.trim());
    }

    // STEP 3: Highlight buttons
    $('.obj-btn').each(function () {
        let val = $(this).data('value');

        if (selectedObjectives.includes(val)) {
            $(this).removeClass('btn-outline-primary')
                   .addClass('btn-primary active');
        }
    });

    // STEP 4: Render selected list (including manual values)
    renderSelected();

    // STEP 5: open modal
    $('#objectiveModal').modal('show');
});
// Toggle button select
$(document).on('click', '.obj-btn', function () {
    let value = $(this).data('value');

    if (selectedObjectives.includes(value)) {
        selectedObjectives = selectedObjectives.filter(v => v !== value);
        $(this).removeClass('btn-primary active').addClass('btn-outline-primary');
    } else {
        selectedObjectives.push(value);
        $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
    }

    renderSelected();
});

// Add manual input
$('#addManual').on('click', function () {
    let val = $('#manualObjective').val().trim();

    if (val && !selectedObjectives.includes(val)) {
        selectedObjectives.push(val);
        $('#manualObjective').val('');
        renderSelected();
    }
});

// Render selected items
function renderSelected() {
    let html = '';

    selectedObjectives.forEach((item, index) => {
        html += `
            <span class="badge badge-info mr-1">
                ${item} 
                <i class="fa fa-times remove-obj" data-index="${index}" style="cursor:pointer;"></i>
            </span>
        `;
    });

    $('#selectedObjectives').html(html);
}

// Remove selected item
$(document).on('click', '.remove-obj', function () {
    let index = $(this).data('index');
    selectedObjectives.splice(index, 1);
    renderSelected();
});

// Save to input
$('#saveObjectives').on('click', function () {
    if (currentInput) {
        currentInput.val(selectedObjectives.join(', '));
    }
    $('#objectiveModal').modal('hide');
});


    });
    </script>
</x-app-layout>