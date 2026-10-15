<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Services\Odoo\CustomerSync;
use App\Services\Odoo\OdooPullSync;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/*
| CRM "Odoo Sync" page: lets the Odoo developer and superadmin see what Odoo
| pushed (request logs, test prices, live prices). Read only.
| See odoo-integration-docs/README.md
*/
class OdooSyncController extends Controller
{
    // odoo_sync_logs.entity => label. Add a line per new Odoo module.
    private const MODULES = [
        'party_prices' => 'Party Wise Pricing',
        'categories' => 'Category Master Odoo',
        'subcategories' => 'Sub Category Master Odoo',
        'products' => 'Product Master Odoo',
        'customers' => 'Primary Customer Odoo',
    ];

    /**
     * Sync Overview: API keys and request logs of every Odoo module.
     */
    public function index()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $clients = DB::table('odoo_api_clients')
            ->select('id', 'name', 'mode', 'active', 'last_used_at', 'created_at')
            ->orderBy('id')
            ->get();

        $lastRequest = DB::table('odoo_sync_logs')->orderByDesc('id')->first(['created_at', 'status_code', 'failed_count']);

        $counts = [
            'logs' => DB::table('odoo_sync_logs')->count(),
            'requests_today' => DB::table('odoo_sync_logs')->where('created_at', '>=', Carbon::today())->count(),
            'failed_today' => DB::table('odoo_sync_logs')->where('created_at', '>=', Carbon::today())->sum('failed_count'),
        ];

        return view('odoo_sync.index', compact('clients', 'counts', 'lastRequest'));
    }

    /**
     * Primary Customer Odoo: primary customers / dealers pulled from Odoo (cron twice a day, or Sync now).
     */
    public function customers()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lastRun = DB::table('odoo_sync_logs')->where('entity', CustomerSync::ENTITY)->orderByDesc('id')
            ->first(['created_at', 'status_code', 'received_count', 'failed_count']);

        $counts = [
            'total' => DB::table('odoo_customers')->count(),
            'active' => DB::table('odoo_customers')->where('active', true)->where('is_deleted', false)->count(),
            'unlinked' => DB::table('odoo_customers')->whereNull('customer_id')->count(),
        ];

        return view('odoo_sync.customers', compact('counts', 'lastRun'));
    }

    public function customersData(Request $request)
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table('odoo_customers as ocu')
            ->leftJoin('customers as c', 'c.id', '=', 'ocu.customer_id')
            ->when($request->input('link') === 'linked', fn ($q) => $q->whereNotNull('ocu.customer_id'))
            ->when($request->input('link') === 'unlinked', fn ($q) => $q->whereNull('ocu.customer_id'))
            ->select('ocu.id', 'ocu.customer_odoo_id', 'ocu.firm_name', 'ocu.contact_person_name', 'ocu.mobile', 'ocu.email',
                'ocu.city', 'ocu.district', 'ocu.state', 'ocu.pin_code', 'ocu.gstin', 'ocu.customer_type', 'ocu.salesperson',
                'ocu.active', 'ocu.is_deleted', 'ocu.customer_id', 'ocu.odoo_updated_at', 'ocu.updated_at', 'c.name as fk_customer_name');

        return datatables()->query($query)
            ->editColumn('customer_odoo_id', fn ($row) => '<span class="os-mono os-copy os-strong" title="Click to copy" data-copy="' . e($row->customer_odoo_id) . '">' . e($row->customer_odoo_id) . '</span>')
            ->editColumn('firm_name', fn ($row) => '<div class="os-strong">' . e(\Illuminate\Support\Str::limit($row->firm_name, 45)) . '</div>'
                . ($row->contact_person_name ? '<div class="os-sub">' . e($row->contact_person_name) . '</div>' : ''))
            ->addColumn('contact', fn ($row) => '<div class="os-strong">' . e($row->mobile ?: '—') . '</div>'
                . ($row->email ? '<div class="os-sub">' . e($row->email) . '</div>' : ''))
            ->addColumn('location', fn ($row) => '<div class="os-strong">' . e($row->state ?: '—') . '</div>'
                . '<div class="os-sub">' . e(implode(', ', array_filter([$row->city, $row->district, $row->pin_code])) ?: '—') . '</div>')
            ->editColumn('gstin', fn ($row) => '<span class="os-mono">' . e($row->gstin ?: '—') . '</span>')
            ->editColumn('customer_type', fn ($row) => '<div class="os-strong">' . e($row->customer_type ?: '—') . '</div>'
                . ($row->salesperson ? '<div class="os-sub">' . e($row->salesperson) . '</div>' : ''))
            ->addColumn('linked', fn ($row) => $row->customer_id
                ? '<div class="os-sub">' . e(\Illuminate\Support\Str::limit($row->fk_customer_name, 40)) . ' <span class="os-tag">#' . (int) $row->customer_id . '</span></div>'
                : '<span class="os-pill os-pill-warning os-pill-sm">Not linked</span>')
            ->addColumn('status', function ($row) {
                if ($row->is_deleted) {
                    return '<span class="os-pill os-pill-danger">Deleted</span>';
                }
                return $row->active ? '<span class="os-pill os-pill-success">Active</span>' : '<span class="os-pill os-pill-neutral">Inactive</span>';
            })
            ->editColumn('odoo_updated_at', fn ($row) => $this->dateTimeCell($row->odoo_updated_at))
            ->editColumn('updated_at', fn ($row) => $this->dateTimeCell($row->updated_at))
            ->rawColumns(['customer_odoo_id', 'firm_name', 'contact', 'location', 'gstin', 'customer_type', 'linked', 'status', 'odoo_updated_at', 'updated_at'])
            ->make(true);
    }

    public function syncCustomers(CustomerSync $sync)
    {
        return $this->runPullSync($sync);
    }

    /**
     * Excel of Primary Customer Odoo rows; ?link=linked|unlinked limits it like the page filter.
     */
    public function customersExport(Request $request)
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $link = $request->input('link');
        $rows = DB::table('odoo_customers as ocu')
            ->leftJoin('customers as c', 'c.id', '=', 'ocu.customer_id')
            ->when($link === 'linked', fn ($q) => $q->whereNotNull('ocu.customer_id'))
            ->when($link === 'unlinked', fn ($q) => $q->whereNull('ocu.customer_id'))
            ->orderBy('ocu.firm_name')
            ->get(['ocu.*', 'c.name as fk_customer_name'])
            ->map(fn ($row) => [
                $row->customer_odoo_id,
                $row->firm_name,
                $row->contact_person_name,
                $row->mobile,
                $row->phone,
                $row->email,
                $row->street,
                $row->street2,
                $row->city,
                $row->district,
                $row->state,
                $row->pin_code,
                $row->country,
                $row->gstin,
                $row->customer_type,
                $row->salesperson,
                $row->customer_id,
                $row->fk_customer_name ?? 'Not linked',
                $this->statusText($row),
                $this->exportDate($row->odoo_updated_at),
                $this->exportDate($row->updated_at),
            ])->all();

        return Excel::download(new ExcelExport([
            'Odoo Customer ID', 'Firm Name', 'Contact Person', 'Mobile', 'Phone', 'Email', 'Street', 'Street 2', 'City', 'District',
            'State', 'PIN Code', 'Country', 'GSTIN', 'Customer Type', 'Salesperson', 'FieldKonnect Customer ID', 'FieldKonnect Customer',
            'Status', 'Odoo Updated', 'Synced',
        ], $rows), 'odoo_customers_' . (in_array($link, ['linked', 'unlinked'], true) ? $link . '_' : '') . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * "Sync now" buttons: the same pull the cron runs.
     */
    private function runPullSync(OdooPullSync $sync)
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Products are a few thousand rows; don't let PHP's web time limit cut the sync
        set_time_limit(300);

        $result = $sync->run();
        unset($result['errors']);

        return response()->json($result, $result['status_code'] === 502 ? 502 : 200);
    }

    public function logs()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table('odoo_sync_logs as l')
            ->leftJoin('odoo_api_clients as k', 'k.id', '=', 'l.client_id')
            ->select('l.*', 'k.name as client_name');

        return datatables()->query($query)
            ->editColumn('created_at', fn ($row) => $this->dateTimeCell($row->created_at))
            ->addColumn('request', function ($row) {
                return '<span class="os-method os-method-' . strtolower(e($row->method)) . '">' . e($row->method) . '</span>'
                    . '<span class="os-mono os-copy" title="Click to copy" data-copy="' . e($row->correlation_id) . '">' . e($row->correlation_id) . '</span>';
            })
            ->addColumn('module', fn ($row) => '<span class="os-strong">' . e(self::MODULES[$row->entity] ?? $row->entity) . '</span>')
            ->addColumn('client', function ($row) {
                // PULL rows are FieldKonnect calling Odoo (cron / Sync now), not an API key
                $name = $row->method === 'PULL' ? 'FieldKonnect pull' : ($row->client_name ?? 'Deleted key');
                return '<div class="os-strong">' . e($name) . '</div>' . $this->modePill($row->mode);
            })
            ->addColumn('result', function ($row) {
                $chip = fn ($label, $value, $tone) => '<span class="os-count' . ($value > 0 ? ' os-count-' . $tone : '') . '"><b>' . (int) $value . '</b> ' . $label . '</span>';
                return '<div class="os-counts">'
                    . $chip('received', $row->received_count, 'neutral')
                    . $chip('created', $row->created_count, 'success')
                    . $chip('updated', $row->updated_count, 'info')
                    . $chip('skipped', $row->skipped_count, 'warning')
                    . $chip('failed', $row->failed_count, 'danger')
                    . '</div>';
            })
            ->editColumn('status_code', function ($row) {
                $tone = $row->status_code < 300 ? 'success' : ($row->status_code < 500 ? 'warning' : 'danger');
                return '<span class="os-pill os-pill-' . $tone . '">' . (int) $row->status_code . '</span>';
            })
            ->editColumn('errors', function ($row) {
                $errors = $row->errors ? json_decode($row->errors, true) : null;
                if (!$errors) {
                    return '<span class="os-muted">—</span>';
                }
                $pretty = json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                return '<button type="button" class="os-link-btn os-view-errors" data-correlation="' . e($row->correlation_id) . '" data-errors="' . e($pretty) . '">'
                    . '<span class="material-icons">error_outline</span>View ' . count($errors) . '</button>';
            })
            ->rawColumns(['created_at', 'request', 'module', 'client', 'result', 'status_code', 'errors'])
            ->make(true);
    }

    private function modePill(?string $mode): string
    {
        return $mode === 'live'
            ? '<span class="os-pill os-pill-live os-pill-sm">Live</span>'
            : '<span class="os-pill os-pill-test os-pill-sm">Test</span>';
    }

    private function statusText(object $row): string
    {
        if ($row->is_deleted) {
            return 'Deleted';
        }
        return $row->active ? 'Active' : 'Inactive';
    }

    private function exportDate($value): string
    {
        return $value ? Carbon::parse($value)->format('d M Y, h:i A') : '';
    }

    private function dateTimeCell($value): string
    {
        if (!$value) {
            return '';
        }
        $date = Carbon::parse($value);
        return '<div class="os-strong">' . $date->format('d M Y') . '</div><div class="os-sub">' . $date->format('h:i:s A') . '</div>';
    }
}
