<div class="btn-group btn-group-sm" role="group">
    @if(auth()->user()->can('new_invoice_access'))
    <a href="{{ route('new-invoices.show', $invoice->id) }}"
        class="btn btn-just-icon btn-info btn-sm" title="View">
        <i class="material-icons">visibility</i>
    </a>
    @endif

    @if(auth()->user()->can('new_invoice_edit') && $invoice->approval_status == \App\Models\NewInvoice::STATUS_PENDING)
    <a href="{{ route('new-invoices.edit', $invoice->id) }}"
        class="btn btn-just-icon btn-warning btn-sm" title="Edit">
        <i class="material-icons">edit</i>
    </a>
    @endif

    @if(auth()->user()->can('new_invoice_approve_ss') && $invoice->canMoveToStatus(\App\Models\NewInvoice::STATUS_APPROVED_SS))
    <button type="button" class="btn btn-just-icon btn-dark btn-sm invoice-approval-action"
        data-action="{{ route('new-invoices.approve', [$invoice->id, 'ss']) }}"
        data-title="Approve By SS" data-requires-remark="0" title="Approve By SS">
        <i class="material-icons">done</i>
    </button>
    @endif

    @if(auth()->user()->can('new_invoice_approve_sales') && $invoice->canMoveToStatus(\App\Models\NewInvoice::STATUS_APPROVED_SALES))
    <button type="button" class="btn btn-just-icon btn-primary btn-sm invoice-approval-action"
        data-action="{{ route('new-invoices.approve', [$invoice->id, 'sales']) }}"
        data-title="Approve By Sales" data-requires-remark="0" title="Approve By Sales">
        <i class="material-icons">verified</i>
    </button>
    @endif

    @if(auth()->user()->can('new_invoice_approve_ho') && $invoice->canMoveToStatus(\App\Models\NewInvoice::STATUS_APPROVED_HO))
    <button type="button" class="btn btn-just-icon btn-success btn-sm invoice-approval-action"
        data-action="{{ route('new-invoices.approve', [$invoice->id, 'ho']) }}"
        data-title="Approve By HO" data-requires-remark="0" title="Approve By HO">
        <i class="material-icons">task_alt</i>
    </button>
    @endif

    @if(auth()->user()->can('new_invoice_reject') && $invoice->canMoveToStatus(\App\Models\NewInvoice::STATUS_REJECTED))
    <button type="button" class="btn btn-just-icon btn-danger btn-sm invoice-approval-action"
        data-action="{{ route('new-invoices.reject', $invoice->id) }}"
        data-title="Reject Invoice" data-requires-remark="1" title="Reject">
        <i class="material-icons">close</i>
    </button>
    @endif

    @if(auth()->user()->can('new_invoice_delete') && $invoice->approval_status == \App\Models\NewInvoice::STATUS_PENDING)
    <form action="{{ route('new-invoices.destroy', $invoice->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-just-icon btn-danger btn-sm" title="Delete"
            onclick="return confirm('Are you sure you want to delete this invoice?');">
            <i class="material-icons">delete</i>
        </button>
    </form>
    @endif
</div>
