<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Services\Odoo\CategorySync;
use App\Services\Odoo\OdooPullSync;
use App\Services\Odoo\PartyPriceSync;
use App\Services\Odoo\ProductSync;
use App\Services\Odoo\SubcategorySync;
use Carbon\Carbon;
use Gate;
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
     * Party Wise Pricing: prices stored with the test key and with the live key.
     */
    public function partyPrices()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $unlinked = fn ($mode) => DB::table(PartyPriceSync::tableFor($mode))->where(fn ($q) => $q->whereNull('party_id')->orWhereNull('product_id'))->count();

        $counts = [
            'test' => DB::table(PartyPriceSync::tableFor('test'))->count(),
            'test_unlinked' => $unlinked('test'),
            'live' => DB::table(PartyPriceSync::tableFor('live'))->count(),
            'live_unlinked' => $unlinked('live'),
            'last_received' => DB::table('odoo_sync_logs')->where('entity', 'party_prices')->max('created_at'),
        ];

        return view('odoo_sync.party_prices', compact('counts'));
    }

    /**
     * Category Master Odoo: categories pulled from Odoo (cron twice a day, or Sync now).
     */
    public function categories()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lastRun = DB::table('odoo_sync_logs')->where('entity', CategorySync::ENTITY)->orderByDesc('id')
            ->first(['created_at', 'status_code', 'received_count', 'failed_count']);

        // Only Odoo categories linked to an existing FieldKonnect category are listed
        $linked = fn () => DB::table('odoo_categories as oc')
            ->join('categories as c', 'c.id', '=', 'oc.category_id')
            ->whereNull('c.deleted_at');

        $counts = [
            'total' => $linked()->count(),
            'active' => $linked()->where('oc.active', true)->where('oc.is_deleted', false)->count(),
            'unlinked' => DB::table('odoo_categories')->whereNull('category_id')->count(),
        ];

        return view('odoo_sync.categories', compact('counts', 'lastRun'));
    }

    public function categoriesData()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table('odoo_categories as oc')
            ->join('categories as c', 'c.id', '=', 'oc.category_id')
            ->whereNull('c.deleted_at')
            ->select('oc.id', 'oc.external_id', 'oc.category_code', 'oc.category_name', 'oc.description', 'oc.ranking',
                'oc.active', 'oc.is_deleted', 'oc.category_id', 'oc.odoo_updated_at', 'oc.updated_at', 'c.category_name as fk_category_name');

        return datatables()->query($query)
            ->editColumn('external_id', fn ($row) => '<span class="os-mono os-copy" title="Click to copy" data-copy="' . e($row->external_id) . '">' . e($row->external_id) . '</span>')
            ->editColumn('category_code', fn ($row) => '<span class="os-mono os-strong">' . e($row->category_code) . '</span>')
            ->editColumn('category_name', fn ($row) => '<div class="os-strong">' . e($row->category_name) . '</div>'
                . ($row->description ? '<div class="os-sub">' . e(\Illuminate\Support\Str::limit($row->description, 60)) . '</div>' : ''))
            ->addColumn('linked', fn ($row) => $row->category_id
                ? '<div class="os-sub">' . e($row->fk_category_name) . ' <span class="os-tag">#' . (int) $row->category_id . '</span></div>'
                : '<span class="os-pill os-pill-warning os-pill-sm">Not linked</span>')
            ->addColumn('status', function ($row) {
                if ($row->is_deleted) {
                    return '<span class="os-pill os-pill-danger">Deleted</span>';
                }
                return $row->active ? '<span class="os-pill os-pill-success">Active</span>' : '<span class="os-pill os-pill-neutral">Inactive</span>';
            })
            ->editColumn('odoo_updated_at', fn ($row) => $this->dateTimeCell($row->odoo_updated_at))
            ->editColumn('updated_at', fn ($row) => $this->dateTimeCell($row->updated_at))
            ->rawColumns(['external_id', 'category_code', 'category_name', 'linked', 'status', 'odoo_updated_at', 'updated_at'])
            ->make(true);
    }

    public function syncCategories(CategorySync $sync)
    {
        return $this->runPullSync($sync);
    }

    /**
     * Sub Category Master Odoo: sub-categories pulled from Odoo (cron twice a day, or Sync now).
     */
    public function subcategories()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lastRun = DB::table('odoo_sync_logs')->where('entity', SubcategorySync::ENTITY)->orderByDesc('id')
            ->first(['created_at', 'status_code', 'received_count', 'failed_count']);

        $counts = [
            'total' => DB::table('odoo_subcategories')->count(),
            'active' => DB::table('odoo_subcategories')->where('active', true)->where('is_deleted', false)->count(),
            'unlinked' => DB::table('odoo_subcategories')->whereNull('subcategory_id')->count(),
        ];

        return view('odoo_sync.subcategories', compact('counts', 'lastRun'));
    }

    public function subcategoriesData()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table('odoo_subcategories as os')
            ->leftJoin('odoo_categories as oc', 'oc.external_id', '=', 'os.category_external_id')
            // Nested sub-category (Column Pipe > Heavy Pipe): parent is another sub-category
            ->leftJoin('odoo_subcategories as ps', 'ps.external_id', '=', 'os.category_external_id')
            ->leftJoin('subcategories as s', 's.id', '=', 'os.subcategory_id')
            ->select('os.id', 'os.external_id', 'os.subcategory_code', 'os.subcategory_name', 'os.description', 'os.category_code', 'os.ranking',
                'os.active', 'os.is_deleted', 'os.subcategory_id', 'os.odoo_updated_at', 'os.updated_at',
                DB::raw('COALESCE(oc.category_name, ps.subcategory_name) as parent_name'), 's.subcategory_name as fk_subcategory_name');

        return datatables()->query($query)
            ->editColumn('external_id', fn ($row) => '<span class="os-mono os-copy" title="Click to copy" data-copy="' . e($row->external_id) . '">' . e($row->external_id) . '</span>')
            ->editColumn('subcategory_code', fn ($row) => '<span class="os-mono os-strong">' . e($row->subcategory_code) . '</span>')
            ->editColumn('subcategory_name', fn ($row) => '<div class="os-strong">' . e($row->subcategory_name) . '</div>'
                . ($row->description ? '<div class="os-sub">' . e(\Illuminate\Support\Str::limit($row->description, 60)) . '</div>' : ''))
            ->addColumn('parent', fn ($row) => '<div class="os-mono os-strong">' . e($row->category_code) . '</div>'
                . '<div class="os-sub">' . ($row->parent_name ? e($row->parent_name) : 'Category not synced yet') . '</div>')
            ->addColumn('linked', fn ($row) => $row->subcategory_id
                ? '<div class="os-sub">' . e($row->fk_subcategory_name) . ' <span class="os-tag">#' . (int) $row->subcategory_id . '</span></div>'
                : '<span class="os-pill os-pill-warning os-pill-sm">Not linked</span>')
            ->addColumn('status', function ($row) {
                if ($row->is_deleted) {
                    return '<span class="os-pill os-pill-danger">Deleted</span>';
                }
                return $row->active ? '<span class="os-pill os-pill-success">Active</span>' : '<span class="os-pill os-pill-neutral">Inactive</span>';
            })
            ->editColumn('odoo_updated_at', fn ($row) => $this->dateTimeCell($row->odoo_updated_at))
            ->editColumn('updated_at', fn ($row) => $this->dateTimeCell($row->updated_at))
            ->rawColumns(['external_id', 'subcategory_code', 'subcategory_name', 'parent', 'linked', 'status', 'odoo_updated_at', 'updated_at'])
            ->make(true);
    }

    public function syncSubcategories(SubcategorySync $sync)
    {
        return $this->runPullSync($sync);
    }

    /**
     * Excel of every row on Sub Category Master Odoo.
     */
    public function subcategoriesExport()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $rows = DB::table('odoo_subcategories as os')
            ->leftJoin('odoo_categories as oc', 'oc.external_id', '=', 'os.category_external_id')
            ->leftJoin('odoo_subcategories as ps', 'ps.external_id', '=', 'os.category_external_id')
            ->leftJoin('subcategories as s', 's.id', '=', 'os.subcategory_id')
            ->orderBy('os.subcategory_name')
            ->get(['os.*', DB::raw('COALESCE(oc.category_name, ps.subcategory_name) as parent_name'), 's.subcategory_name as fk_subcategory_name'])
            ->map(fn ($row) => [
                $row->external_id,
                $row->subcategory_code,
                $row->subcategory_name,
                $row->category_external_id,
                $row->category_code,
                $row->parent_name,
                $row->subcategory_id,
                $row->fk_subcategory_name ?? 'Not linked',
                $row->description,
                $row->ranking,
                $this->statusText($row),
                $this->exportDate($row->odoo_updated_at),
                $this->exportDate($row->updated_at),
            ])->all();

        return Excel::download(new ExcelExport([
            'External ID', 'Sub-category Code', 'Sub-category', 'Parent External ID', 'Parent Code', 'Parent Name',
            'FieldKonnect Sub-category ID', 'FieldKonnect Sub-category', 'Description', 'Ranking', 'Status', 'Odoo Updated', 'Synced',
        ], $rows), 'odoo_subcategories_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Product Master Odoo: products pulled from Odoo (cron twice a day, or Sync now).
     */
    public function products()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $lastRun = DB::table('odoo_sync_logs')->where('entity', ProductSync::ENTITY)->orderByDesc('id')
            ->first(['created_at', 'status_code', 'received_count', 'failed_count']);

        $counts = [
            'total' => DB::table('odoo_products')->count(),
            'active' => DB::table('odoo_products')->where('active', true)->where('is_deleted', false)->count(),
            'unlinked' => DB::table('odoo_products')->whereNull('product_id')->count(),
        ];

        return view('odoo_sync.products', compact('counts', 'lastRun'));
    }

    public function productsData()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table('odoo_products as op')
            ->leftJoin('odoo_categories as oc', 'oc.external_id', '=', 'op.category_external_id')
            ->leftJoin('odoo_subcategories as os', 'os.external_id', '=', 'op.subcategory_external_id')
            ->leftJoin('products as p', 'p.id', '=', 'op.product_id')
            ->select('op.id', 'op.external_id', 'op.product_code', 'op.product_name', 'op.display_name', 'op.category_code', 'op.subcategory_code',
                'op.brand_name', 'op.uom_code', 'op.hsn_code', 'op.mrp', 'op.standard_price', 'op.gst_percent', 'op.currency_code',
                'op.orderable', 'op.active', 'op.is_deleted', 'op.product_id', 'op.odoo_updated_at', 'op.updated_at',
                'oc.category_name', 'os.subcategory_name', 'p.product_name as fk_product_name');

        return datatables()->query($query)
            ->editColumn('external_id', fn ($row) => '<span class="os-mono os-copy" title="Click to copy" data-copy="' . e($row->external_id) . '">' . e($row->external_id) . '</span>')
            ->editColumn('product_code', fn ($row) => '<span class="os-mono os-strong">' . e($row->product_code) . '</span>')
            ->editColumn('product_name', fn ($row) => '<div class="os-strong">' . e(\Illuminate\Support\Str::limit($row->product_name, 50)) . '</div>'
                . ($row->brand_name ? '<div class="os-sub">' . e($row->brand_name) . '</div>' : ''))
            ->addColumn('category', fn ($row) => '<div class="os-strong">' . e($row->category_name ?? $row->category_code) . '</div>'
                . '<div class="os-sub">' . e($row->subcategory_name ?? $row->subcategory_code) . '</div>')
            ->addColumn('price', function ($row) {
                $gst = $row->gst_percent !== null ? rtrim(rtrim(number_format($row->gst_percent, 2), '0'), '.') . '% GST' : 'GST —';
                return '<div class="os-price">MRP ' . $this->money($row->mrp, $row->currency_code) . '</div>'
                    . '<div class="os-sub">Std ' . $this->money($row->standard_price, $row->currency_code) . ' · ' . $gst . '</div>';
            })
            ->addColumn('uom', fn ($row) => '<div class="os-strong">' . e($row->uom_code) . '</div>'
                . '<div class="os-sub">HSN ' . e($row->hsn_code ?: '—') . '</div>')
            ->addColumn('linked', fn ($row) => $row->product_id
                ? '<div class="os-sub">' . e(\Illuminate\Support\Str::limit($row->fk_product_name, 40)) . ' <span class="os-tag">#' . (int) $row->product_id . '</span></div>'
                : '<span class="os-pill os-pill-warning os-pill-sm">Not linked</span>')
            ->addColumn('status', function ($row) {
                if ($row->is_deleted) {
                    return '<span class="os-pill os-pill-danger">Deleted</span>';
                }
                $status = $row->active ? '<span class="os-pill os-pill-success">Active</span>' : '<span class="os-pill os-pill-neutral">Inactive</span>';
                return $status . ($row->orderable ? '' : '<div class="os-sub">Not orderable</div>');
            })
            ->editColumn('odoo_updated_at', fn ($row) => $this->dateTimeCell($row->odoo_updated_at))
            ->editColumn('updated_at', fn ($row) => $this->dateTimeCell($row->updated_at))
            ->rawColumns(['external_id', 'product_code', 'product_name', 'category', 'price', 'uom', 'linked', 'status', 'odoo_updated_at', 'updated_at'])
            ->make(true);
    }

    public function syncProducts(ProductSync $sync)
    {
        return $this->runPullSync($sync);
    }

    /**
     * Excel of every row on Product Master Odoo.
     */
    public function productsExport()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $rows = DB::table('odoo_products as op')
            ->leftJoin('odoo_categories as oc', 'oc.external_id', '=', 'op.category_external_id')
            ->leftJoin('odoo_subcategories as os', 'os.external_id', '=', 'op.subcategory_external_id')
            ->leftJoin('products as p', 'p.id', '=', 'op.product_id')
            ->orderBy('op.product_name')
            ->get(['op.*', 'oc.category_name', 'os.subcategory_name', 'p.product_name as fk_product_name'])
            ->map(fn ($row) => [
                $row->external_id,
                $row->product_code,
                $row->product_name,
                $row->display_name,
                $row->category_external_id,
                $row->category_code,
                $row->category_name ?? $row->category_code,
                $row->subcategory_external_id,
                $row->subcategory_code,
                $row->subcategory_name ?? $row->subcategory_code,
                $row->brand_name,
                $row->uom_code,
                $row->hsn_code,
                $row->mrp,
                $row->standard_price,
                $row->gst_percent,
                $row->currency_code,
                $row->orderable ? 'Yes' : 'No',
                $row->product_id,
                $row->fk_product_name ?? 'Not linked',
                $this->statusText($row),
                $this->exportDate($row->odoo_updated_at),
                $this->exportDate($row->updated_at),
            ])->all();

        return Excel::download(new ExcelExport([
            'External ID', 'Product Code', 'Product Name', 'Display Name', 'Category External ID', 'Category Code', 'Category',
            'Sub-category External ID', 'Sub-category Code', 'Sub-category', 'Brand', 'UOM', 'HSN', 'MRP', 'Standard Price', 'GST %',
            'Currency', 'Orderable', 'FieldKonnect Product ID', 'FieldKonnect Product', 'Status', 'Odoo Updated', 'Synced',
        ], $rows), 'odoo_products_' . now()->format('Y-m-d_His') . '.xlsx');
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

    public function testPrices()
    {
        return $this->pricesTable('test');
    }

    public function livePrices()
    {
        return $this->pricesTable('live');
    }

    private function pricesTable(string $mode)
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table(PartyPriceSync::tableFor($mode) . ' as pp')
            ->leftJoin('customers as c', function ($join) {
                $join->on('c.id', '=', 'pp.party_id')->where('pp.party_type', '=', 'customer');
            })
            ->leftJoin('master_distributors as md', function ($join) {
                $join->on('md.id', '=', 'pp.party_id')->where('pp.party_type', '=', 'master_distributor');
            })
            ->leftJoin('products as pr', 'pr.id', '=', 'pp.product_id')
            ->select(
                'pp.id', 'pp.external_id', 'pp.price_list_code', 'pp.price_list_name', 'pp.party_code', 'pp.party_type', 'pp.party_id',
                'pp.product_code', 'pp.product_id', 'pp.currency_code', 'pp.base_price', 'pp.party_price',
                'pp.discount_percent', 'pp.tax_inclusive', 'pp.minimum_quantity', 'pp.maximum_quantity', 'pp.uom_code',
                'pp.valid_from', 'pp.valid_to', 'pp.priority', 'pp.active', 'pp.is_deleted', 'pp.odoo_updated_at', 'pp.updated_at',
                DB::raw('COALESCE(c.name, md.legal_name) as party_name'),
                'pr.product_name'
            );

        return datatables()->query($query)
            ->editColumn('external_id', fn ($row) => '<span class="os-mono os-copy" title="Click to copy" data-copy="' . e($row->external_id) . '">' . e($row->external_id) . '</span>')
            ->addColumn('party', function ($row) {
                $type = $row->party_type === 'master_distributor' ? 'Distributor' : 'Customer';
                $linked = $row->party_id
                    ? '<div class="os-sub">' . e($row->party_name) . ' <span class="os-tag">' . $type . '</span></div>'
                    : '<div class="os-sub"><span class="os-pill os-pill-warning os-pill-sm">Not linked</span></div>';
                return '<div class="os-mono os-strong">' . e($row->party_code) . '</div>' . $linked;
            })
            ->addColumn('product', function ($row) {
                $linked = $row->product_id
                    ? '<div class="os-sub">' . e($row->product_name) . '</div>'
                    : '<div class="os-sub"><span class="os-pill os-pill-warning os-pill-sm">Not linked</span></div>';
                return '<div class="os-mono os-strong">' . e($row->product_code) . '</div>' . $linked;
            })
            ->editColumn('price_list_code', fn ($row) => '<div class="os-strong">' . e($row->price_list_code) . '</div>'
                . ($row->price_list_name ? '<div class="os-sub">' . e($row->price_list_name) . '</div>' : ''))
            ->addColumn('price', function ($row) {
                $discount = $row->discount_percent !== null ? ' · ' . rtrim(rtrim(number_format($row->discount_percent, 2), '0'), '.') . '% off' : '';
                return '<div class="os-price">' . $this->money($row->party_price, $row->currency_code) . '</div>'
                    . '<div class="os-sub">Base ' . $this->money($row->base_price, $row->currency_code) . $discount . '</div>'
                    . '<div class="os-sub">' . ($row->tax_inclusive ? 'Incl. tax' : 'Excl. tax') . '</div>';
            })
            ->addColumn('slab', function ($row) {
                $min = $this->qty($row->minimum_quantity);
                $range = $row->maximum_quantity === null ? $min . '+' : $min . ' – ' . $this->qty($row->maximum_quantity);
                return '<div class="os-strong">' . $range . '</div><div class="os-sub">' . e($row->uom_code) . '</div>';
            })
            ->addColumn('validity', function ($row) {
                $to = $row->valid_to ? Carbon::parse($row->valid_to)->format('d M Y') : 'Open-ended';
                return '<div class="os-strong">' . Carbon::parse($row->valid_from)->format('d M Y') . '</div><div class="os-sub">to ' . $to . '</div>';
            })
            ->addColumn('status', function ($row) {
                if ($row->is_deleted) {
                    return '<span class="os-pill os-pill-danger">Deleted</span>';
                }
                return $row->active ? '<span class="os-pill os-pill-success">Active</span>' : '<span class="os-pill os-pill-neutral">Inactive</span>';
            })
            ->editColumn('updated_at', fn ($row) => $this->dateTimeCell($row->updated_at))
            ->rawColumns(['external_id', 'party', 'product', 'price_list_code', 'price', 'slab', 'validity', 'status', 'updated_at'])
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

    private function money($amount, ?string $currency): string
    {
        $symbol = strtoupper((string) $currency) === 'INR' ? '₹' : e($currency) . ' ';
        return $symbol . number_format((float) $amount, 2);
    }

    private function qty($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    }
}
