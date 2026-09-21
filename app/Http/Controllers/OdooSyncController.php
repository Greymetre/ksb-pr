<?php

namespace App\Http\Controllers;

use App\Services\Odoo\PartyPriceSync;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/*
| CRM "Odoo Sync" page: lets the Odoo developer and superadmin see what Odoo
| pushed (request logs, test prices, live prices). Read only.
| See odoo-integration-docs/README.md
*/
class OdooSyncController extends Controller
{
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
            'test' => DB::table(PartyPriceSync::tableFor('test'))->count(),
            'live' => DB::table(PartyPriceSync::tableFor('live'))->count(),
            'test_unlinked' => DB::table(PartyPriceSync::tableFor('test'))->where(fn ($q) => $q->whereNull('party_id')->orWhereNull('product_id'))->count(),
            'failed_today' => DB::table('odoo_sync_logs')->where('created_at', '>=', Carbon::today())->sum('failed_count'),
        ];

        return view('odoo_sync.index', compact('clients', 'counts', 'lastRequest'));
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
            ->addColumn('client', fn ($row) => '<div class="os-strong">' . e($row->client_name ?? 'Deleted key') . '</div>' . $this->modePill($row->mode))
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
            ->rawColumns(['created_at', 'request', 'client', 'result', 'status_code', 'errors'])
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
