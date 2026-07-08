<x-app-layout>
    <section class="fk-manual-listing">
        <div class="fk-list-page-head">
            <div class="fk-list-heading-block">
                <div class="fk-list-breadcrumb">
                    <span>Loyalty Engine</span><span>&rsaquo;</span><span class="fk-current">New Invoices</span>
                </div>
                <div class="fk-list-title-row">
                    <h1 class="fk-list-title">New Invoice Listing</h1>
                    <span class="fk-list-count is-visible" id="newInvoiceRecordCount">0 records</span>
                </div>
            </div>
            <div class="fk-list-actions">
                <button class="btn fk-filter-trigger" type="button" data-filter-target="#newInvoiceFilterDrawer">
                    <span class="material-icons">tune</span><span>Filters</span>
                </button>
                @if(auth()->user()->can('new_invoice_create'))
                <a href="{{ route('new-invoices.create') }}" class="btn fk-create-action" title="Add Invoice">
                    <span class="material-icons">add_circle</span><span>Add New Invoice</span>
                </a>
                @endif
            </div>
        </div>

        <div class="card fk-listing-card" data-fk-listing-ready="1">
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

                <div class="fk-table-meta">
                    <div class="fk-table-meta-icon"><span class="material-icons">receipt_long</span></div>
                    <div class="fk-table-meta-copy">
                        <h2>New Invoice Directory</h2>
                        <p class="fk-table-meta-subline" id="newInvoiceTableMeta">Live directory · page 1 of 1</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table fk-glass-table" id="invoiceTable">
                        <thead>
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
    </section>

    <aside class="fk-filter-drawer" id="newInvoiceFilterDrawer">
        <div class="fk-filter-drawer-head">
            <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
            <div>
                <h3>Advanced Filters</h3>
                <p>Applied live to the directory</p>
            </div>
            <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
        </div>
        <div class="fk-filter-drawer-body">
            <div class="fk-filter-field">
                <label for="retailer_search">Retailer Name / Mobile</label>
                <input type="text" id="retailer_search" class="form-control fk-filter-control" placeholder="Name or mobile number">
            </div>
            <div class="fk-filter-field">
                <label for="invoice_number_filter">Invoice No.</label>
                <input type="text" id="invoice_number_filter" class="form-control fk-filter-control" placeholder="Invoice number">
            </div>
            <div class="fk-filter-field">
                <label for="approval_status_filter">Approval Status</label>
                <select id="approval_status_filter" class="form-control select2 fk-filter-control">
                    <option value="">All status</option>
                    @foreach($approvalStatuses as $status => $label)
                    <option value="{{ $status }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fk-filter-field">
                <label for="zone_filter">Zone</label>
                <select id="zone_filter" class="form-control select2 fk-filter-control">
                    <option value="">All zones</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fk-filter-field">
                <label for="branch_filter">Branch</label>
                <select id="branch_filter" class="form-control select2 fk-filter-control">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fk-filter-field">
                <label for="from_date">From Date</label>
                <input type="text" id="from_date" class="form-control datepicker fk-filter-control" autocomplete="off" readonly>
            </div>
            <div class="fk-filter-field">
                <label for="to_date">To Date</label>
                <input type="text" id="to_date" class="form-control datepicker fk-filter-control" autocomplete="off" readonly>
            </div>
        </div>
        @if(auth()->user()->can('new_invoice_export'))
        <div class="fk-filter-drawer-tools">
            <a href="{{ route('new-invoices.export') }}" id="exportInvoices" class="btn fk-tool-export" title="Export Invoices">
                <span class="material-icons">cloud_download</span><span>Export</span>
            </a>
        </div>
        @endif
        <div class="fk-filter-drawer-foot">
            <button class="btn fk-filter-reset" id="resetFilter" type="button">Reset</button>
            <button class="btn fk-filter-apply" id="applyFilter" type="button">Apply Filters</button>
        </div>
    </aside>

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
                dom: 't<"bottom"ip>',
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
                order: [[5, 'desc']],
                drawCallback: function() {
                    var info = this.api().page.info();
                    $('#newInvoiceRecordCount').text((info.recordsDisplay || 0) + ' records');
                    $('#newInvoiceTableMeta').text('Live directory · page ' + ((info.page || 0) + 1) + ' of ' + (info.pages || 1));
                }
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
