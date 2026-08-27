<x-app-layout>
    <style>
        .datepicker{
            z-index: 100 !important;
        }
        .select2 {
            z-index: 100 !important;
        }
    </style>
    <section class="fk-manual-listing fk-export-report">

              <!-- Retailer Performance Report -->
                {{-- NEW FILTER CONTAINER with Download Button --}}
                <div id="retailerCard" class="fk-export-pane" style="display:none;">
                    <div class="fk-list-page-head">
                        <div class="fk-list-heading-block">
                            <div class="fk-list-breadcrumb">
                                <span>BEATS MANAGEMENT</span>
                                <span>›</span>
                                <span class="fk-current">ADHERENCE SUMMARY</span>
                            </div>
                            <div class="fk-list-title-row">
                                <h1 class="fk-list-title">Retailer Productivity Report</h1>
                            </div>
                        </div>
                        <div class="fk-list-actions">
                            <button type="button" id="downloadReportBtn" class="btn fk-create-action">
                                <i class="material-icons">download</i>
                                <span>Export Report</span>
                            </button>
                        </div>
                    </div>

                <div class="fk-report-card mb-4">
                    <div class="card-body">
                        <div class="row">

                            <!-- 1. Employee Name -->
                            <div class="col-md-3">
                                <label class="bmd-label-floating">Employee Name</label>
                                <select class="form-control select2" id="employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                </select>
                            </div>

                            <!-- 2. Retailer Name -->
                            <div class="col-md-3">
                                <label class="bmd-label-floating">Retailer Name</label>
                                <select class="form-control select2" id="retailer_id" name="retailer_id">
                                    <option value="">All Retailers</option>
                                </select>
                            </div>

                            <!-- 3. Distributor Name -->
                            <div class="col-md-3">
                                <label class="bmd-label-floating">Distributor Name</label>
                                <select class="form-control select2" id="distributor_id" name="distributor_id">
                                    <option value="">All Distributors</option>
                                </select>
                            </div>

                            <!-- 4. Year -->
                            <div class="col-md-3">
                                <label class="bmd-label-floating">Year</label>
                                <select class="form-control" id="year" name="year">
                                    <option value="">All Years</option>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Designation</label>
                                <select class="form-control selectpicker" 
                                    id="designation_id_retailer" 
                                    name="designation_id[]" 
                                    multiple
                                    data-style="select-with-transition"
                                    title="Select Designation"
                                    >

                                    <!-- dynamically filled -->
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                </div>
                <div id="dealerCard" class="fk-export-pane" style="display:none;">
                    <div class="fk-list-page-head">
                        <div class="fk-list-heading-block">
                            <div class="fk-list-breadcrumb">
                                <span>BEATS MANAGEMENT</span>
                                <span>›</span>
                                <span class="fk-current">ADHERENCE SUMMARY</span>
                            </div>
                            <div class="fk-list-title-row">
                                <h1 class="fk-list-title">Distributors Productivity Report</h1>
                            </div>
                        </div>
                        <div class="fk-list-actions">
                            <button type="button" id="dealerDownloadReportBtn" class="btn fk-create-action">
                                <i class="material-icons">download</i>
                                <span>Export Report</span>
                            </button>
                        </div>
                    </div>

                <div class="fk-report-card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Designation</label>
                                <select class="form-control selectpicker" 
                                    id="designation_id_dealer" 
                                    name="designation_id" 
                                    data-style="select-with-transition"
                                    title="Select Designation"
                                    >
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bmd-label-floating">Employee Name</label>
                                <select class="form-control select2" id="dealer_employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bmd-label-floating">Distributor Name</label>
                                <select class="form-control select2" id="dealer_id" name="dealer_id">
                                    <option value="">All Distributors</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bmd-label-floating">Year</label>
                                <select class="form-control" id="dealer_year" name="year">
                                    <option value="">All Years</option>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <div id="asrCard" class="fk-export-pane" style="display:none;">
                <div class="fk-list-page-head">
                    <div class="fk-list-heading-block">
                        <div class="fk-list-breadcrumb">
                            <span>BEATS MANAGEMENT</span>
                            <span>›</span>
                            <span class="fk-current">ADHERENCE SUMMARY</span>
                        </div>
                        <div class="fk-list-title-row">
                            <h1 class="fk-list-title">ASR Productivity</h1>
                        </div>
                    </div>
                </div>
                <div class="fk-report-card card-body">
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

                    <form method="GET" action="{{ URL::to('counterVisitReportDownload') }}">

                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>User</label>
                                <select class="form-control select2" id="report_user_id" name="employee_id">
                                    <option value="">All Users</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Zone</label>
                                <select class="form-control select2" id="division_id" name="division_id">
                                    <option value="">All Zones</option>
                                </select>
                            </div>

                            <!-- Branch -->
                            <div class="col-md-3">
                                <label>Branch</label>
                                <select class="form-control select2" id="branch_id" name="branch_id">
                                    <option value="">All Branches</option>
                                </select>
                            </div>

                            <!-- Designation -->
                            <div class="col-md-3">
                                <label>Designation</label>
                                <select class="form-control fk-designation-multi" id="designation_id_asr"
                                    name="designation_id[]" multiple
                                    data-placeholder="Select Designation">
                                    @foreach(($designations ?? collect()) as $designation)
                                        <option value="{{ $designation->id }}" {{ strtoupper(trim($designation->designation_name)) === 'ASR' ? 'selected' : '' }}>{{ trim($designation->designation_name) }}</option>
                                    @endforeach
                                </select>
                                <small class="fk-field-hint">Select one or more designations. Each designation is exported as its own report file.</small>
                            </div>

                        </div>    
                        <div class="row">
                            

                            <!-- Zone -->


                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                                        placeholder="Start Date" readonly required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" id="end_date" name="end_date" 
                                        placeholder="End Date" readonly required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn fk-create-action">
                                    <i class="material-icons">download</i>
                                    <span>Export Report</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
    </section>
    <script>
    $('form').on('submit', function(e) {

        let start = $('#start_date').val();
        let end   = $('#end_date').val();

        if (!start || !end) {
            e.preventDefault(); // stop form submit

            alert('⚠️ Please select Start Date and End Date before downloading report');

            return false;
        }

        let designations = $('#designation_id_asr').val() || [];

        if ($('#asrCard').is(':visible') && designations.length === 0) {
            e.preventDefault();

            alert('⚠️ Please select at least one Designation before downloading report');

            return false;
        }

    });
    </script>
    
    
    <script type="text/javascript">
        function loadReportUsers() {
    $.ajax({
        url: "{{ url('getUserList') }}",
        type: "GET",
        success: function (data) {

            let options = '<option value="">All Users</option>';

            data.forEach(function (item) {
                options += `<option value="${item.id}">${item.name}</option>`;
            });

            $('#report_user_id').html(options);
        }
    });
}
        // Zone
$.get("{{ url('getDivisions') }}")
.done(function(data) {
    console.log('Zones:', data);

    let options = '<option value="">All Zones</option>';

    if (Array.isArray(data)) {
        data.forEach(item => {
            options += `<option value="${item.id}">${item.division_name}</option>`;
        });
    } else if (data.data) {
        data.data.forEach(item => {
            options += `<option value="${item.id}">${item.division_name}</option>`;
        });
    }

    $('#division_id').html(options);
})
.fail(function(err) {
    console.error('Zone API Error:', err);
});

// Branch
$.get("{{ url('getBranches') }}", function(data) {
    let options = '<option value="">All Branches</option>';
    data.forEach(item => {
        options += `<option value="${item.id}">${item.branch_name}</option>`;
    });
    $('#branch_id').html(options);
});

/**
 * ASR Designation is a select2 multi-select. Its options are rendered server-side
 * (see the markup above) so the widget never depends on an ajax round trip - an
 * empty select is what left select2 showing "No results found".
 */
function buildAsrDesignationSelect() {
    var $el = $('#designation_id_asr');

    if (!$el.length || !$.fn.select2) {
        return;
    }

    var selected = $el.val();

    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }

    // prop() is set explicitly so the widget is always built in multi-select mode.
    $el.prop('multiple', true).select2({
        width: '100%',
        placeholder: $el.data('placeholder') || 'Select Designation',
        closeOnSelect: false,
        allowClear: false
    });

    if (selected && selected.length) {
        $el.val(selected);
    }

    $el.trigger('change.select2');
}

// Designation
$.get("{{ url('getDesignations') }}", function(data) {

    let retailerOptions = '';
    let dealerOptions = '';
    let asrDesignationId = '';

    data.forEach(item => {

        let name = $.trim(item.designation_name);
        let isAsr = name.toUpperCase() === 'ASR';
        let selected = isAsr
            ? 'selected' 
            : '';

        if (isAsr) {
            asrDesignationId = item.id;
        }

        // ✅ Retailer (MULTIPLE)
        retailerOptions += `<option value="${item.id}" ${selected}>
                                ${item.designation_name}
                            </option>`;

        // ✅ ASR (MULTIPLE bhi hai tumhara)
        dealerOptions += `<option value="${item.id}" ${selected}>
                                ${item.designation_name}
                            </option>`;

    });

    // ASR designation options are rendered server-side in the markup above.
    $('#designation_id_retailer').html(retailerOptions);
    $('#designation_id_dealer').html(dealerOptions);

    if (asrDesignationId) {
        $('#designation_id_retailer').val([asrDesignationId]);
        $('#designation_id_dealer').val(asrDesignationId);
    }

    // Refresh bootstrap-select after dynamic options are inserted.
    // Guarded: this plugin loads late, so a missing plugin must not abort the rest.
    try {
        $('#designation_id_retailer').selectpicker('refresh');
        $('#designation_id_dealer').selectpicker('refresh');
    } catch (e) {
        $(function () {
            $('#designation_id_retailer').selectpicker('refresh');
            $('#designation_id_dealer').selectpicker('refresh');
            $('#designation_id_dealer').trigger('change');
        });
        return;
    }

    setTimeout(function () {
        $('#designation_id_dealer').trigger('change');
    }, 0);
});
    // Download Report Button
$('#downloadReportBtn').on('click', function() {
     let params = {
        start_date:     $('#start_date').val(),
        end_date:       $('#end_date').val(),
        employee_id:    $('#employee_id').val(),
        retailer_id:    $('#retailer_id').val(),
        distributor_id: $('#distributor_id').val(),
        year:           $('#year').val(),
        division_id:    $('#division_id').val(),
        branch_id:      $('#branch_id').val(),
        designation_id: $('#designation_id_retailer').val()
    };

    // Remove empty parameters
    params = Object.fromEntries(
        Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );

    let queryString = $.param(params);
let downloadUrl = "{{ route('retailer.productivity.export') }}" + (queryString ? '?' + queryString : '');
    window.location.href = downloadUrl;
});

$('#dealerDownloadReportBtn').on('click', function() {
    let params = {
        start_date:     $('#start_date').val(),
        end_date:       $('#end_date').val(),
        employee_id:    $('#dealer_employee_id').val(),
        dealer_id:      $('#dealer_id').val(),
        year:           $('#dealer_year').val(),
        division_id:    $('#division_id').val(),
        branch_id:      $('#branch_id').val(),
        designation_id: $('#designation_id_dealer').val()
    };

    params = Object.fromEntries(
        Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );

    let queryString = $.param(params);
    let downloadUrl = "{{ route('dealer.productivity.export') }}" + (queryString ? '?' + queryString : '');
    window.location.href = downloadUrl;
});
$(document).ready(function () {


    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('type');
    loadReportUsers();

    if (type === 'retailer') {
        $('#retailerCard').show();
        $('#dealerCard').hide();
        $('#asrCard').hide();
    } 
    else if (type === 'dealer') {
        $('#dealerCard').show();
        $('#retailerCard').hide();
        $('#asrCard').hide();
    } 
    else if (type === 'asr') {
        $('#asrCard').show();
        $('#retailerCard').hide();
        $('#dealerCard').hide();
    } 
    else {
        // default (optional)
        $('#retailerCard').show();
        $('#dealerCard').hide();
        $('#asrCard').hide();
    }

    // Built after the card is visible so select2 sizes itself against a laid-out column.
    buildAsrDesignationSelect();

    function loadDealerEmployees() {
        $.ajax({
            url: "{{ url('getUserList') }}",
            type: "GET",
            data: {
                designation_id: $('#designation_id_dealer').val()
            },
            success: function (data) {
                let options = '<option value="">All Employees</option>';

                data.forEach(function (item) {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });

                $('#dealer_employee_id').html(options).trigger('change.select2');
            }
        });
    }

    // Employee list
    $.ajax({
        url: "{{ url('getUserList') }}",
        type: "GET",
        success: function (data) {

            let options = '<option value="">All Employees</option>';

            data.forEach(function (item) {
                options += `<option value="${item.id}">${item.name}</option>`;
            });

            $('#employee_id').html(options);
        }
    });

    loadDealerEmployees();

    $.ajax({
    url: "{{ url('getRetailerlist') }}",
    type: "GET",
    success: function (data) {

        let retailerOptions = '<option value="">All Retailers</option>';
        let distributorOptions = '<option value="">All Distributors</option>';
        let dealerOptions = '<option value="">All Distributors</option>';

        data.forEach(function (item) {

            if (item.type === 'retailer') {
                retailerOptions += `<option value="${item.id}">${item.name}</option>`;
            }

            if (item.type === 'distributor') {
                distributorOptions += `<option value="${item.id}">${item.name}</option>`;
                dealerOptions += `<option value="${item.id}">${item.name}</option>`;
            }

        });

        $('#retailer_id').html(retailerOptions);
        $('#distributor_id').html(distributorOptions);
        $('#dealer_id').html(dealerOptions);
    }
});

$('#designation_id_dealer').on('changed.bs.select change', function () {
    loadDealerEmployees();
});

});


    </script>
</x-app-layout>
