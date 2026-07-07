<x-app-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">receipt_long</i>
                    </div>
                    <h4 class="card-title">
                        New Invoice Listing
                        <span class="">
                            <div class="btn-group header-frm-btn" style="    flex-direction: row;justify-content: right;align-items: center;gap: 0px;">
                                @if(auth()->user()->can('new_invoice_export'))
                                <a href="{{ route('new-invoices.export') }}" id="exportInvoices"
                                    class="btn btn-just-icon btn-theme" title="Export Invoices">
                                    <i class="material-icons">cloud_download</i>
                                </a>
                                @endif
                                @if(auth()->user()->can('new_invoice_create'))
                                <a href="{{ route('new-invoices.create') }}" class="btn btn-just-icon btn-theme"
                                    title="Create Invoice">
                                    <i class="material-icons">add_circle</i>
                                </a>
                                @endif
                            </div>
                        </span>
                    </h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </div>
                    @endif

                    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
                    @if (session($key))
                    <div class="alert alert-{{ $class }}">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        {{ session($key) }}
                    </div>
                    @endif
                    @endforeach

                    <div class="alert invoice-message" style="display: none;">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        <span class="message"></span>
                    </div>

                    <div class="row invoice-summary mb-3">
                        <div class="col-md-3 col-lg-2 col-sm-6 mb-3">
                            <div class="invoice-stat-card">
                                <small>Total Invoices</small>
                                <h4 data-summary="total_invoices" class="text-dark">{{ $summary['total_invoices'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-2 col-sm-6 mb-3">
                            <div class="invoice-stat-card">
                                <small>Total Retailers</small>
                                <h4 data-summary="total_retailers" class="text-dark">{{ $summary['total_retailers'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-2 col-sm-6 mb-3">
                            <div class="invoice-stat-card">
                                <small>Approved By SS</small>
                                <h4 data-summary="approved_ss" class="text-dark">{{ $summary['approved_ss'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-2 col-sm-6 mb-3">
                            <div class="invoice-stat-card">
                                <small>Approved By Sales</small>
                                <h4 data-summary="approved_sales" class="text-dark">{{ $summary['approved_sales'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-2 col-sm-6 mb-3">
                            <div class="invoice-stat-card">
                                <small>Approved By HO</small>
                                <h4 data-summary="approved_ho" class="text-dark">{{ $summary['approved_ho'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-2 col-sm-6 mb-3">
                            <div class="invoice-stat-card">
                                <small>Pending / Rejected</small>
                                <h4><span data-summary="pending" class="text-dark">{{ $summary['pending'] }}</span> / <span data-summary="rejected" class="text-dark">{{ $summary['rejected'] }}</span></h4>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-sm-12 mb-3">
                            <div class="invoice-stat-card">
                                <small>Total Points / Amount</small>
                                <h4><span data-summary="total_points" class="text-dark">{{ number_format($summary['total_points'], 2) }}</span> pts</h4>
                                <small>Rs. <span data-summary="total_amount" class="text-dark">{{ number_format($summary['total_amount'], 2) }}</span></small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3 invoice-filter-card">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="material-icons align-middle">filter_list</i> Filter Invoice Transactions</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="col-form-label">Retailer Name / Mobile</label>
                                    <input type="text" id="retailer_search" class="form-control" placeholder="Name or mobile number">
                                </div>
                                <div class="col-md-2">
                                    <label class="col-form-label">Invoice No.</label>
                                    <input type="text" id="invoice_number_filter" class="form-control" placeholder="Invoice number">
                                </div>
                                <div class="col-md-2">
                                    <label class="col-form-label">Approval Status</label>
                                    <select id="approval_status_filter" class="form-control select2">
                                        <option value="">All status</option>
                                        @foreach($approvalStatuses as $status => $label)
                                        <option value="{{ $status }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="col-form-label">Branch</label>
                                    <select id="zone_filter" class="form-control select2">
                                        <option value="">All zones</option>
                                        @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="col-form-label">Branch</label>
                                    <select id="branch_filter" class="form-control select2">
                                        <option value="">All branches</option>
                                        @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mt-2">
                                    <label class="col-form-label">From Date</label>
                                    <input type="text" id="from_date" class="form-control datepicker" autocomplete="off" readonly>
                                </div>
                                <div class="col-md-2 mt-2">
                                    <label class="col-form-label">To Date</label>
                                    <input type="text" id="to_date" class="form-control datepicker" autocomplete="off" readonly>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <button type="button" id="resetFilter" class="btn btn-secondary">Reset</button>
                                    <button type="button" id="applyFilter" class="btn btn-theme">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover no-wrap" id="invoiceTable">
                            <thead class="text-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Retailer ID</th>
                                    <th>Customer Name</th>
                                    <th>Mobile Number</th>
                                    <th>City / Branch</th>
                                    <th>Invoice Date</th>
                                    <th>Invoice Number</th>
                                    <th>Pre-GST Amount</th>
                                    <th>Points</th>
                                    <th>Approval Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invoiceApprovalModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">fact_check</i>
                    </div>
                    <h4 class="card-title">
                        <span id="approvalModalTitle">Update Approval</span>
                        <span class="pull-right">
                            <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal">
                                <i class="material-icons">clear</i>
                            </a>
                        </span>
                    </h4>
                </div>
                <div class="modal-body">
                    <form id="invoiceApprovalForm" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="col-form-label">Remarks</label>
                            <textarea name="remark" id="approval_remark" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-theme" id="approvalSubmitBtn">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .invoice-stat-card {
            border: 1px solid #e3e7ef;
            border-radius: 6px;
            padding: 14px;
            min-height: 92px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .invoice-stat-card small {
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }
        .invoice-stat-card h4 {
            margin: 8px 0 2px;
            font-weight: 700;
        }
        .invoice-filter-card {
            border: 1px solid #e3e7ef;
            box-shadow: none;
        }
        #invoiceTable td {
            vertical-align: middle;
        }
    </style>

    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });

            $('.select2').select2({ width: '100%' });

            var table = $('#invoiceTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                lengthMenu: [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
                ajax: {
                    url: "{{ route('new-invoices.index') }}",
                    data: function(d) {
                        d.retailer_search = $('#retailer_search').val();
                        d.invoice_number = $('#invoice_number_filter').val();
                        d.approval_status = $('#approval_status_filter').val();
                        d.zone_id = $('#zone_filter').val();
                        d.branch_id = $('#branch_filter').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    },
                    dataSrc: function(json) {
                        updateSummary(json.summary || {});
                        return json.data;
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'retailer_id', name: 'secondary_customer_id', orderable: false, searchable: false},
                    {data: 'customer_name', name: 'customer_name', orderable: false, searchable: false},
                    {data: 'mobile_number', name: 'mobile_number', orderable: false, searchable: false},
                    {data: 'city_zone', name: 'city_zone', orderable: false, searchable: false},
                    {data: 'invoice_date', name: 'invoice_date'},
                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'amount', name: 'amount'},
                    {data: 'points', name: 'points'},
                    {data: 'approval_status', name: 'approval_status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[5, 'desc']]
            });

            $('#applyFilter').on('click', function() {
                table.draw();
            });

            $('#resetFilter').on('click', function() {
                $('#retailer_search, #invoice_number_filter, #from_date, #to_date').val('');
                $('#approval_status_filter, #zone_filter, #branch_filter').val('').trigger('change');
                table.draw();
            });

            $('#retailer_search, #invoice_number_filter').on('keypress', function(e) {
                if (e.which === 13) {
                    table.draw();
                    return false;
                }
            });

            $('#exportInvoices').on('click', function(e) {
                e.preventDefault();
                window.location.href = "{{ route('new-invoices.export') }}?" + $.param(currentFilters());
            });

            $(document).on('click', '.invoice-approval-action', function() {
                var action = $(this).data('action');
                var title = $(this).data('title');
                var requiresRemark = $(this).data('requires-remark') === 1;

                $('#invoiceApprovalForm').attr('action', action);
                $('#approvalModalTitle').text(title);
                $('#approvalSubmitBtn').text(title);
                $('#approval_remark').val('').prop('required', requiresRemark);
                $('#invoiceApprovalModal').modal('show');
            });

            $('#invoiceApprovalForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#invoiceApprovalModal').modal('hide');
                        showInvoiceMessage(response.status, response.message);
                        table.draw(false);
                    },
                    error: function(xhr) {
                        var message = 'Unable to update invoice approval.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showInvoiceMessage('error', message);
                    }
                });
            });

            function currentFilters() {
                return {
                    retailer_search: $('#retailer_search').val(),
                    invoice_number: $('#invoice_number_filter').val(),
                    approval_status: $('#approval_status_filter').val(),
                    zone_id: $('#zone_filter').val(),
                    branch_id: $('#branch_filter').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
            }

            function updateSummary(summary) {
                $.each(summary, function(key, value) {
                    var field = $('[data-summary="' + key + '"]');
                    if (field.length) {
                        if (key === 'total_points' || key === 'total_amount') {
                            value = parseFloat(value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                        field.text(value);
                    }
                });
            }

            function showInvoiceMessage(status, message) {
                var alert = $('.invoice-message');
                alert.removeClass('alert-success alert-danger').addClass(status === 'success' ? 'alert-success' : 'alert-danger');
                alert.find('.message').text(message);
                alert.show();
            }
        });
    </script>
</x-app-layout>
