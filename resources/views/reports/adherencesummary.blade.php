<x-app-layout>
    <style>
        .datepicker{
            z-index: 100 !important;
        }
        .select2 {
            z-index: 100 !important;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
              {{-- NEW FILTER CONTAINER --}}

              <!-- Retailer Performance Report -->
                {{-- NEW FILTER CONTAINER with Download Button --}}
                <div id="retailerCard" style="display:none;">
                    <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title ">Retailer Productivity Report
                        <span class="pull-right">
                            <div class="btn-group">

                            </div>
                        </span>
                    </h4>
                </div>
                <div class=" mb-4">
                    <div class="card-header ">
                        
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-0">Filters</h5>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" id="downloadReportBtn" class="btn btn-success">
                                    <i class="material-icons">get_app</i> Download Excel Report
                                </button>
                            </div>
                        </div>
                    </div>

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
                <div id="dealerCard" style="display:none;">
                    <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title ">Distributors Productivity Report
                        <span class="pull-right">
                            <div class="btn-group">

                            </div>
                        </span>
                    </h4>
                </div>
                <div class=" mb-4">
                    <div class="card-header ">
                        
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-0">Filters</h5>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" id="dealerDownloadReportBtn" class="btn btn-success">
                                    <i class="material-icons">get_app</i> Download Excel Report
                                </button>
                            </div>
                        </div>
                    </div>

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
                <div id="asrCard" style="display:none;">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title ">ASR Productivity
                        <span class="pull-right">
                            <div class="btn-group">

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

                    

                    <!-- <form method="GET" action="{{ URL::to('counterVisitReportDownload') }}">
            <div class="row">
              <div class="col-md-4">
              </div>
              <div class="col-md-4">
                <div class="form-group has-default bmd-form-group">
                <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off" readonly>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group has-default bmd-form-group">
                <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly>
                </div>
              </div>
            </div>
          <form> -->

                        <!-- ASR Productivity Report -->
                    <form method="GET" action="{{ URL::to('counterVisitReportDownload') }}">

                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>User</label>
                                <select class="form-control select2" id="report_user_id" name="employee_id">>
                                    <option value="">All Users</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Division</label>
                                <select class="form-control select2" id="division_id" name="division_id">
                                    <option value="">All Divisions</option>
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
                                <select class="form-control selectpicker" id="designation_id_asr" name="designation_id"
                                    data-style="select-with-transition"
                                    title="Select Designation"
                                    >
                                </select>
                            </div>
                            
                        </div>    
                        <div class="row">
                            

                            <!-- Division -->


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
                                <button type="submit" class="btn btn-success">
                                    Download Report
                                </button>
                            </div>
                        </div>
                    </form>
                    <!-- <div class="table-responsive">
                        <table id="getattendance"
                            class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
                            <thead class=" text-primary">
                                <th>No</th>
                                <th>User ID</th>
                                <th>User Name</th>
                                <th>Total Counter Beat</th>
                                <th>Total Visited Counter </th>
                                <th>Beat Adherance %</th>
                                <th>Total Order Counter</th>
                                <th>Beat Productivity %</th>
                                <th>New Counter Add</th>
                                <th>Total Qty</th>
                                <th>Order Value</th>
                                <th>Per Day Sale</th>
                                <th>Total Cumulative Counter</th>
                                <th>TLSD</th>
                                <th>Unique SKU Count</th>
                                <th>Active Counter</th>
                                <th>Inactive Counter</th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div> -->
                </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $('form').on('submit', function(e) {

        let start = $('#start_date').val();
        let end   = $('#end_date').val();

        if (!start || !end) {
            e.preventDefault(); // stop form submit

            alert('⚠️ Please select Start Date and End Date before downloading report');

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
        // Division
$.get("{{ url('getDivisions') }}")
.done(function(data) {
    console.log('Divisions:', data);

    let options = '<option value="">All Divisions</option>';

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
    console.error('Division API Error:', err);
});

// Branch
$.get("{{ url('getBranches') }}", function(data) {
    let options = '<option value="">All Branches</option>';
    data.forEach(item => {
        options += `<option value="${item.id}">${item.branch_name}</option>`;
    });
    $('#branch_id').html(options);
});

// Designation
$.get("{{ url('getDesignations') }}", function(data) {

    let retailerOptions = '';
    let dealerOptions = '';
    let asrOptions = '';

    data.forEach(item => {

        let selected = (item.designation_name === 'ASR') 
            ? 'selected' 
            : '';

        // ✅ Retailer (MULTIPLE)
        retailerOptions += `<option value="${item.id}" ${selected}>
                                ${item.designation_name}
                            </option>`;

        // ✅ ASR (MULTIPLE bhi hai tumhara)
        dealerOptions += `<option value="${item.id}" ${selected}>
                                ${item.designation_name}
                            </option>`;

        asrOptions += `<option value="${item.id}" ${selected}>
                            ${item.designation_name}
                       </option>`;
    });

    $('#designation_id_retailer').html(retailerOptions);
    $('#designation_id_dealer').html(dealerOptions);
    $('#designation_id_asr').html(asrOptions);

    // 🔥 VERY IMPORTANT for selectpicker
    $('#designation_id_retailer').selectpicker('refresh');
    $('#designation_id_dealer').selectpicker('refresh');
    $('#designation_id_asr').selectpicker('refresh');

    setTimeout(function () {
        $('#designation_id_dealer').trigger('change');
    }, 0);
});
    var table = $('#getattendance').DataTable({
        'destroy': true,
        processing: true,
        serverSide: true,
        lengthChange: true,
        responsive: true,
        "pageLength": 100,
        lengthMenu: [
            [100, 200, 500, 1000],
            [100, 200, 500, 1000]
        ],
        dom: 'Bfrtip',
        buttons: [
        'pageLength',
        {
            text: 'Download Retailer Productivity',
            className: 'btn btn-primary',
            action: function () {

                let params = {
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    employee_id: $('#employee_id').val(),
                    retailer_id: $('#retailer_id').val(),
                    distributor_id: $('#distributor_id').val(),
                    year: $('#year').val(),
                
                };

                params = Object.fromEntries(
                    Object.entries(params).filter(([_, v]) => v != null && v !== '')
                );

                let query = $.param(params);

                let url = "{{ route('retailer.productivity.export') }}" + (query ? '?' + query : '');

                window.location.href = url;
            }
        }
    ],
        "retrieve": true,
        ajax: {
            url: "{{ url('reports/adherencesummary') }}",
            data: function(d) {
                d.employee_id    = $('#employee_id').val();
                d.retailer_id    = $('#retailer_id').val();
                d.distributor_id = $('#distributor_id').val();
                d.year           = $('#year').val();
                d.division_id    = $('#division_id').val();
                d.branch_id      = $('#branch_id').val();
                d.designation_id = $('#designation_id_asr').val();                                            
            }
        },

        
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'id',
                name: 'id',
                "defaultContent": ''
            },
            {
                data: 'name',
                name: 'name',
                "defaultContent": ''
            },
            {
                data: 'total_beat_counter',
                name: 'total_beat_counter',
                "defaultContent": ''
            },
            {
                data: 'total_visited_counter',
                name: 'total_visited_counter',
                "defaultContent": ''
            },
            {
                data: 'beat_adherence',
                name: 'beat_adherence',
                "defaultContent": ''
            },
            {
                data: 'total_order_counter',
                name: 'total_order_counter',
                "defaultContent": ''
            },
            {
                data: 'beat_productivity',
                name: 'beat_productivity',
                "defaultContent": ''
            },
            {
                data: 'new_counter_added',
                name: 'new_counter_added',
                "defaultContent": ''
            },
            {
                data: 'total_order_qty',
                name: 'total_order_qty',
                "defaultContent": ''
            },
            {
                data: 'total_order_value',
                name: 'total_order_value',
                "defaultContent": ''
            },
            // {data: 'pincode', name: 'pincode',"defaultContent": ''},
            {
                data: 'total_assign_counter',
                name: 'total_assign_counter',
                "defaultContent": ''
            },
            {
                data: 'unique_sku_count',
                name: 'unique_sku_count',
                "defaultContent": ''
            },
            {
                data: 'active_counter',
                name: 'active_counter',
                "defaultContent": ''
            },
            {
                data: 'inactive_counter',
                name: 'inactive_counter',
                "defaultContent": ''
            },
        ]
    });
    $(document).ready(function() {
        table.draw();
        loadReportUsers();
    });
    $('#start_date').change(function() {
        table.draw();
    });
    $('#end_date').change(function() {
        table.draw();
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

$('#employee_id, #retailer_id, #distributor_id, #year, #dealer_employee_id, #dealer_id, #dealer_year').change(function () {
    table.draw();
});

});


    </script>
</x-app-layout>
