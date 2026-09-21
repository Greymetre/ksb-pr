<?php

namespace App\Http\Controllers;

use App\Services\Odoo\PartyPriceSync;
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

        $counts = [
            'test' => DB::table(PartyPriceSync::tableFor('test'))->count(),
            'live' => DB::table(PartyPriceSync::tableFor('live'))->count(),
            'test_unlinked' => DB::table(PartyPriceSync::tableFor('test'))->where(fn ($q) => $q->whereNull('party_id')->orWhereNull('product_id'))->count(),
            'last_request' => DB::table('odoo_sync_logs')->max('created_at'),
        ];

        return view('odoo_sync.index', compact('clients', 'counts'));
    }

    public function logs()
    {
        abort_if(Gate::denies('odoo_sync_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = DB::table('odoo_sync_logs as l')
            ->leftJoin('odoo_api_clients as k', 'k.id', '=', 'l.client_id')
            ->select('l.*', 'k.name as client_name');

        return datatables()->query($query)
            ->editColumn('created_at', fn ($row) => $row->created_at ? showdatetimeformat($row->created_at) : '')
            ->editColumn('mode', fn ($row) => $this->modeBadge($row->mode))
            ->editColumn('status_code', function ($row) {
                $class = $row->status_code < 300 ? 'badge-success' : 'badge-danger';
                return '<span class="badge ' . $class . '">' . (int) $row->status_code . '</span>';
            })
            ->editColumn('errors', function ($row) {
                if (!$row->errors) {
                    return '';
                }
                $pretty = json_encode(json_decode($row->errors), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                return '<pre style="max-height:160px;max-width:420px;overflow:auto;white-space:pre-wrap;margin:0">' . e($pretty) . '</pre>';
            })
            ->rawColumns(['mode', 'status_code', 'errors'])
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
                'pp.id', 'pp.external_id', 'pp.price_list_code', 'pp.party_code', 'pp.party_type', 'pp.party_id',
                'pp.product_code', 'pp.product_id', 'pp.currency_code', 'pp.base_price', 'pp.party_price',
                'pp.discount_percent', 'pp.tax_inclusive', 'pp.minimum_quantity', 'pp.maximum_quantity', 'pp.uom_code',
                'pp.valid_from', 'pp.valid_to', 'pp.priority', 'pp.active', 'pp.is_deleted', 'pp.odoo_updated_at', 'pp.updated_at',
                DB::raw('COALESCE(c.name, md.legal_name) as party_name'),
                'pr.product_name'
            );

        return datatables()->query($query)
            ->editColumn('party_name', function ($row) {
                if (!$row->party_id) {
                    return '<span class="badge badge-warning">Not linked</span>';
                }
                $type = $row->party_type === 'master_distributor' ? 'Distributor' : 'Customer';
                return e($row->party_name) . '<br><small class="text-muted">' . $type . ' #' . (int) $row->party_id . '</small>';
            })
            ->editColumn('product_name', function ($row) {
                if (!$row->product_id) {
                    return '<span class="badge badge-warning">Not linked</span>';
                }
                return e($row->product_name) . '<br><small class="text-muted">#' . (int) $row->product_id . '</small>';
            })
            ->addColumn('quantity_slab', fn ($row) => (float) $row->minimum_quantity . ' - ' . ($row->maximum_quantity === null ? 'No max' : (float) $row->maximum_quantity))
            ->addColumn('validity', fn ($row) => e($row->valid_from) . '<br>to ' . ($row->valid_to ? e($row->valid_to) : 'Open-ended'))
            ->editColumn('tax_inclusive', fn ($row) => $row->tax_inclusive ? 'Yes' : 'No')
            ->editColumn('active', function ($row) {
                if ($row->is_deleted) {
                    return '<span class="badge badge-danger">Deleted</span>';
                }
                return $row->active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->editColumn('updated_at', fn ($row) => $row->updated_at ? showdatetimeformat($row->updated_at) : '')
            ->rawColumns(['party_name', 'product_name', 'validity', 'active'])
            ->make(true);
    }

    private function modeBadge(string $mode): string
    {
        return $mode === 'live' ? '<span class="badge badge-danger">LIVE</span>' : '<span class="badge badge-info">TEST</span>';
    }
}
