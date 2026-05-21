<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Http\Requests\StoreNewInvoiceRequest;
use App\Models\Branch;
use App\Models\NewInvoice;
use App\Models\NewInvoiceApprovalLog;
use App\Models\SecondaryCustomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class NewInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('new_invoice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            return DataTables::of($this->invoiceQuery($request))
                ->addIndexColumn()
                ->addColumn('retailer_id', function ($invoice) {
                    return '<span class="badge badge-light text-dark">RET-' . str_pad($invoice->secondary_customer_id, 4, '0', STR_PAD_LEFT) . '</span>';
                })
                ->addColumn('customer_name', function ($invoice) {
                    return e($invoice->customer->owner_name ?? '-');
                })
                ->addColumn('mobile_number', function ($invoice) {
                    return e($invoice->customer->mobile_number ?? '-');
                })
                ->addColumn('city_zone', function ($invoice) {
                    $city = $invoice->customer->city->city_name ?? '-';
                    $zone = $invoice->creator->getbranch->branch_name ?? '-';

                    return e($city) . '<br><small class="text-muted">' . e($zone) . '</small>';
                })
                ->editColumn('invoice_date', function ($invoice) {
                    return $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '-';
                })
                ->editColumn('invoice_number', function ($invoice) {
                    return '<strong>' . e($invoice->invoice_number) . '</strong>';
                })
                ->editColumn('amount', function ($invoice) {
                    return 'Rs. ' . number_format((float) $invoice->amount, 2);
                })
                ->editColumn('points', function ($invoice) {
                    return number_format((float) $invoice->points, 2);
                })
                ->addColumn('approval_status', function ($invoice) {
                    return $this->statusBadge($invoice);
                })
                ->addColumn('action', function ($invoice) {
                    return view('new-invoices.actions', ['invoice' => $invoice])->render();
                })
                ->rawColumns(['retailer_id', 'city_zone', 'invoice_number', 'approval_status', 'action'])
                ->with(['summary' => $this->summaryCards($request)])
                ->make(true);
        }

        $customers = $this->activeCustomers();
        $branches = Branch::where('active', 'Y')->orderBy('branch_name')->get(['id', 'branch_name']);
        $summary = $this->summaryCards($request);
        $approvalStatuses = NewInvoice::STATUS_LABELS;

        return view('new-invoices.index', compact('customers', 'branches', 'summary', 'approvalStatuses'));
    }

    public function create()
    {
        abort_if(Gate::denies('new_invoice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $customers = $this->activeCustomers();

        return view('new-invoices.create', compact('customers'));
    }

    public function store(StoreNewInvoiceRequest $request)
    {
        abort_if(Gate::denies('new_invoice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $validated['points'] = $validated['points'] ?? 0;
            $validated['created_by'] = Auth::id();
            $validated['approval_status'] = NewInvoice::STATUS_PENDING;

            $invoice = NewInvoice::create($validated);
            $this->logApproval($invoice, 'generated', null, NewInvoice::STATUS_PENDING);
            $this->storeAttachments($request, $invoice);

            DB::commit();

            return redirect()->route('new-invoices.index')->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating invoice: ' . $e->getMessage());
        }
    }

    public function show(NewInvoice $newInvoice)
    {
        abort_if(Gate::denies('new_invoice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $invoice = $newInvoice->load([
            'customer.city',
            'creator.getbranch',
            'approvedBySs',
            'approvedBySales',
            'approvedByHo',
            'rejectedBy',
            'approvalLogs.user',
        ]);

        return view('new-invoices.show', compact('invoice'));
    }

    public function edit(NewInvoice $newInvoice)
    {
        abort_if(Gate::denies('new_invoice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if($newInvoice->approval_status != NewInvoice::STATUS_PENDING, Response::HTTP_FORBIDDEN, 'Approved or rejected invoices cannot be edited.');

        $customers = $this->activeCustomers();

        return view('new-invoices.edit', compact('newInvoice', 'customers'));
    }

    public function update(Request $request, NewInvoice $newInvoice)
    {
        abort_if(Gate::denies('new_invoice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if($newInvoice->approval_status != NewInvoice::STATUS_PENDING, Response::HTTP_FORBIDDEN, 'Approved or rejected invoices cannot be edited.');

        $validated = $request->validate([
            'secondary_customer_id' => 'required|integer|exists:secondary_customers,id',
            'invoice_number' => 'required|string|min:1|max:100|unique:new_invoices,invoice_number,' . $newInvoice->id,
            'invoice_date' => 'required|date_format:Y-m-d',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'points' => 'nullable|numeric|min:0|max:999999999.99',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $validated['points'] = $validated['points'] ?? 0;
            $newInvoice->update($validated);
            $this->storeAttachments($request, $newInvoice);
            $this->logApproval($newInvoice, 'updated', $newInvoice->approval_status, $newInvoice->approval_status);

            DB::commit();

            return redirect()->route('new-invoices.index')->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }

    public function destroy(NewInvoice $newInvoice)
    {
        abort_if(Gate::denies('new_invoice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DB::beginTransaction();

        try {
            $newInvoice->approvalLogs()->delete();
            $newInvoice->delete();

            DB::commit();

            return redirect()->route('new-invoices.index')->with('success', 'Invoice deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error deleting invoice: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, NewInvoice $newInvoice, string $level)
    {
        $map = [
            'ss' => ['permission' => 'new_invoice_approve_ss', 'status' => NewInvoice::STATUS_APPROVED_SS, 'column' => 'approved_ss'],
            'sales' => ['permission' => 'new_invoice_approve_sales', 'status' => NewInvoice::STATUS_APPROVED_SALES, 'column' => 'approved_sales'],
            'ho' => ['permission' => 'new_invoice_approve_ho', 'status' => NewInvoice::STATUS_APPROVED_HO, 'column' => 'approved_ho'],
        ];

        abort_if(!isset($map[$level]), Response::HTTP_NOT_FOUND, 'Approval level not found');
        abort_if(Gate::denies($map[$level]['permission']), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'remark' => 'nullable|string|max:1000',
        ]);

        if (!$newInvoice->canMoveToStatus($map[$level]['status'])) {
            return $this->approvalResponse($request, 'error', 'Invoice cannot move to the selected approval status.');
        }

        DB::beginTransaction();

        try {
            $fromStatus = (int) $newInvoice->approval_status;
            $column = $map[$level]['column'];

            $newInvoice->update([
                'approval_status' => $map[$level]['status'],
                'approval_remark' => $request->remark,
                $column . '_by' => Auth::id(),
                $column . '_at' => now(),
            ]);

            $this->logApproval($newInvoice, strtolower(NewInvoice::STATUS_LABELS[$map[$level]['status']]), $fromStatus, $map[$level]['status'], $request->remark);

            DB::commit();

            return $this->approvalResponse($request, 'success', 'Invoice approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->approvalResponse($request, 'error', 'Approval failed: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, NewInvoice $newInvoice)
    {
        abort_if(Gate::denies('new_invoice_reject'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'remark' => 'required|string|max:1000',
        ]);

        if (!$newInvoice->canMoveToStatus(NewInvoice::STATUS_REJECTED)) {
            return $this->approvalResponse($request, 'error', 'Invoice cannot be rejected from the current status.');
        }

        DB::beginTransaction();

        try {
            $fromStatus = (int) $newInvoice->approval_status;

            $newInvoice->update([
                'approval_status' => NewInvoice::STATUS_REJECTED,
                'approval_remark' => $request->remark,
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
            ]);

            $this->logApproval($newInvoice, 'rejected', $fromStatus, NewInvoice::STATUS_REJECTED, $request->remark);

            DB::commit();

            return $this->approvalResponse($request, 'success', 'Invoice rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->approvalResponse($request, 'error', 'Reject failed: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        abort_if(Gate::denies('new_invoice_export'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->invoiceQuery($request)->get()->map(function ($invoice) {
            return [
                'RET-' . str_pad($invoice->secondary_customer_id, 4, '0', STR_PAD_LEFT),
                $invoice->customer->owner_name ?? '-',
                $invoice->customer->mobile_number ?? '-',
                $invoice->customer->city->city_name ?? '-',
                $invoice->creator->getbranch->branch_name ?? '-',
                $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '-',
                $invoice->invoice_number,
                (float) $invoice->amount,
                (float) $invoice->points,
                $invoice->getMedia('attachments')->isNotEmpty() ? 'Yes' : 'No',
                $invoice->approval_status_label,
                $invoice->approval_remark,
            ];
        })->toArray();

        return Excel::download(new ExcelExport([
            'Retailer ID',
            'Customer Name',
            'Mobile Number',
            'City',
            'Zone',
            'Invoice Date',
            'Invoice Number',
            'Pre-GST Amount',
            'Points',
            'Has Attachment',
            'Approval Status',
            'Remark',
        ], $data), 'new-invoices-report.xlsx');
    }

    public function getCustomerDetails(Request $request)
    {
        abort_if(
            Gate::denies('new_invoice_access') && Gate::denies('new_invoice_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        $customer = SecondaryCustomer::with('city')->find($request->input('customer_id'));

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json([
            'owner_name' => $customer->owner_name,
            'mobile_number' => $customer->mobile_number,
            'shop_name' => $customer->shop_name,
            'city' => $customer->city->city_name ?? '',
        ]);
    }

    private function invoiceQuery(Request $request)
    {
        $query = NewInvoice::query()
            ->with(['customer.city', 'creator.getbranch', 'media'])
            ->latest('new_invoices.created_at');

        if ($request->filled('retailer_search')) {
            $search = $request->retailer_search;
            $query->whereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery->where('owner_name', 'like', '%' . $search . '%')
                    ->orWhere('shop_name', 'like', '%' . $search . '%')
                    ->orWhere('mobile_number', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('zone_id') || $request->filled('branch_id')) {
            $branchIds = collect([$request->zone_id, $request->branch_id])->filter()->unique()->values()->all();
            $query->whereHas('creator', function ($creatorQuery) use ($branchIds) {
                $creatorQuery->whereIn('branch_id', $branchIds);
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', Carbon::parse($request->from_date)->format('Y-m-d'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', Carbon::parse($request->to_date)->format('Y-m-d'));
        }

        if ($request->filled('searchInput')) {
            $search = $request->searchInput;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('points', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('owner_name', 'like', '%' . $search . '%')
                            ->orWhere('shop_name', 'like', '%' . $search . '%')
                            ->orWhere('mobile_number', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    private function summaryCards(Request $request): array
    {
        $query = $this->invoiceQuery($request);

        return [
            'total_invoices' => (clone $query)->count(),
            'total_retailers' => (clone $query)->distinct('secondary_customer_id')->count('secondary_customer_id'),
            'approved_ss' => (clone $query)->where('approval_status', NewInvoice::STATUS_APPROVED_SS)->count(),
            'approved_sales' => (clone $query)->where('approval_status', NewInvoice::STATUS_APPROVED_SALES)->count(),
            'approved_ho' => (clone $query)->where('approval_status', NewInvoice::STATUS_APPROVED_HO)->count(),
            'pending' => (clone $query)->where('approval_status', NewInvoice::STATUS_PENDING)->count(),
            'rejected' => (clone $query)->where('approval_status', NewInvoice::STATUS_REJECTED)->count(),
            'total_points' => (clone $query)->sum('points'),
            'total_amount' => (clone $query)->sum('amount'),
        ];
    }

    private function activeCustomers()
    {
        $query = SecondaryCustomer::query()->with('city');

        if (auth()->user()->hasRole('Distributor')) {
            $query->where('distributor_name', auth()->user()->customerid);
        }
        if (Schema::hasColumn('secondary_customers', 'active')) {
            $query->where('active', 'Y');
        }

        return $query
            ->select('id', 'owner_name', 'shop_name', 'mobile_number', 'city_id')
            ->orderBy('owner_name', 'asc')
            ->get();
    }

    private function statusBadge(NewInvoice $invoice): string
    {
        return '<span class="badge ' . $invoice->approval_status_class . '">' . e($invoice->approval_status_label) . '</span>';
    }

    private function logApproval(NewInvoice $invoice, string $statusType, ?int $fromStatus, int $toStatus, ?string $remark = null): void
    {
        NewInvoiceApprovalLog::create([
            'log_date' => now()->format('Y-m-d'),
            'new_invoice_id' => $invoice->id,
            'created_by' => Auth::id(),
            'status_type' => $statusType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'remark' => $remark,
        ]);
    }

    private function storeAttachments(Request $request, NewInvoice $invoice): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $invoice->addMedia($file)
                ->usingName($file->getClientOriginalName())
                ->usingFileName(time() . '_' . $file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }
    }

    private function approvalResponse(Request $request, string $status, string $message)
    {
        if ($request->ajax()) {
            return response()->json(['status' => $status, 'message' => $message]);
        }

        $flashKey = $status === 'success' ? 'success' : 'error';

        return redirect()->back()->with($flashKey, $message);
    }
}
