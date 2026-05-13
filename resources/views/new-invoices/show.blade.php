<x-app-layout>
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">receipt_long</i>
                    </div>
                    <h4 class="card-title">
                        Invoice Details
                        <span class="pull-right">
                            <div class="btn-group header-frm-btn">
                                @if(auth()->user()->can('new_invoice_edit') && $invoice->approval_status == \App\Models\NewInvoice::STATUS_PENDING)
                                <a href="{{ route('new-invoices.edit', $invoice->id) }}"
                                    class="btn btn-just-icon btn-warning" title="Edit">
                                    <i class="material-icons">edit</i>
                                </a>
                                @endif
                                <a href="{{ route('new-invoices.index') }}"
                                    class="btn btn-just-icon btn-secondary" title="Back">
                                    <i class="material-icons">arrow_back</i>
                                </a>
                            </div>
                        </span>
                    </h4>
                </div>

                <div class="card-body">
                    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $class)
                    @if (session($key))
                    <div class="alert alert-{{ $class }}">{{ session($key) }}</div>
                    @endif
                    @endforeach

                    <div class="mb-3">
                        <span class="badge {{ $invoice->approval_status_class }}">{{ $invoice->approval_status_label }}</span>
                        @if($invoice->approval_remark)
                        <small class="text-muted ml-2">{{ $invoice->approval_remark }}</small>
                        @endif
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="card-subtitle mb-3"><strong>Invoice Information</strong></h5>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Invoice Number</strong></label>
                                <input type="text" class="form-control" value="{{ $invoice->invoice_number }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Invoice Date</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '-' }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Pre-GST Amount</strong></label>
                                <input type="text" class="form-control"
                                    value="Rs. {{ number_format((float) $invoice->amount, 2) }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Points</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ number_format((float) $invoice->points, 2) }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="card-subtitle mb-3"><strong>Retailer Information</strong></h5>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Retailer ID</strong></label>
                                <input type="text" class="form-control"
                                    value="RET-{{ str_pad($invoice->secondary_customer_id, 4, '0', STR_PAD_LEFT) }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Customer Name</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->customer->owner_name ?? '-' }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label"><strong>Mobile Number</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->customer->mobile_number ?? '-' }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label"><strong>City / Zone</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->customer->city->city_name ?? '-' }} / {{ $invoice->creator->getbranch->branch_name ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label"><strong>Created By</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->creator->name ?? '-' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-form-label"><strong>Created At</strong></label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->created_at ? $invoice->created_at->format('d-m-Y H:i:s') : '-' }}" readonly>
                            </div>
                        </div>
                    </div>

                    @if($invoice->getMedia('attachments')->isNotEmpty())
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="card-subtitle mb-3"><strong>Attachments</strong></h5>
                            <div class="row">
                                @foreach($invoice->getMedia('attachments') as $media)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            @if(str_contains($media->mime_type, 'image/'))
                                            <img src="{{ $media->getUrl() }}"
                                                alt="{{ $media->name }}" class="img-fluid mb-2" style="max-height: 100px;">
                                            @else
                                            <i class="material-icons" style="font-size: 48px; color: #666;">description</i>
                                            @endif
                                            <p class="mb-1"><small>{{ $media->name }}</small></p>
                                            <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="material-icons">visibility</i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('new-invoices.index') }}" class="btn btn-secondary">
                            <i class="material-icons">arrow_back</i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Approval Actions</h5>
                    @include('new-invoices.actions', ['invoice' => $invoice])
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Approval History</h5>
                    <div class="approval-timeline">
                        @forelse($invoice->approvalLogs->sortByDesc('created_at') as $log)
                        <div class="approval-item">
                            <strong>{{ ucwords($log->status_type) }}</strong>
                            <div>{{ $log->user->employee_codes ?? '' }} {{ $log->user->name ?? '-' }}</div>
                            <small>{{ $log->created_at ? $log->created_at->format('d M Y - g:i A') : '-' }}</small>
                            @if($log->remark)
                            <p class="mb-0 text-muted">{{ $log->remark }}</p>
                            @endif
                        </div>
                        @empty
                        <p class="text-muted mb-0">No approval history found.</p>
                        @endforelse
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
        .approval-item {
            border-left: 3px solid #00aadb;
            padding: 0 0 14px 12px;
            margin-bottom: 14px;
        }
        .approval-item small {
            color: #777;
        }
    </style>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.invoice-approval-action', function() {
                $('#invoiceApprovalForm').attr('action', $(this).data('action'));
                $('#approvalModalTitle').text($(this).data('title'));
                $('#approvalSubmitBtn').text($(this).data('title'));
                $('#approval_remark').val('').prop('required', $(this).data('requires-remark') === 1);
                $('#invoiceApprovalModal').modal('show');
            });
        });
    </script>
</x-app-layout>
