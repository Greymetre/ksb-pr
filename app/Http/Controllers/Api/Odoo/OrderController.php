<?php

namespace App\Http\Controllers\Api\Odoo;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
| FieldKonnect -> Odoo orders (read only, live data), one record per order with its
| product lines nested. Incremental sync with updated_since. API spec: odoo-integration-docs/order-api.md
|
| Ship / balance quantity is not sent on purpose: dispatch happens in Odoo.
*/
class OrderController extends Controller
{
    private const MAX_PAGE_SIZE = 200;

    // An order counts as changed when the order row or any of its lines changed
    private const LAST_MODIFIED = 'GREATEST(COALESCE(o.updated_at, o.created_at), COALESCE(ld.lines_updated_at, o.updated_at, o.created_at))';

    public function index(Request $request)
    {
        $client = $request->attributes->get('odoo_client');
        $correlationId = $request->attributes->get('correlation_id');

        $since = null;
        if ($request->filled('updated_since')) {
            try {
                $since = Carbon::parse((string) $request->query('updated_since'))->setTimezone(config('app.timezone'));
            } catch (\Throwable $e) {
                return $this->error($request, $client, 'updated_since must be a date-time, e.g. 2026-10-14 09:30:00', 'updated_since');
            }
        }
        foreach (['from_date', 'to_date'] as $field) {
            if ($request->filled($field) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query($field))) {
                return $this->error($request, $client, "$field must be YYYY-MM-DD", $field);
            }
        }

        $pageSize = min(max((int) $request->query('page_size', 50), 1), self::MAX_PAGE_SIZE);
        $page = max((int) $request->query('page', 1), 1);

        $lines = DB::table('order_details')->selectRaw('order_id, MAX(updated_at) as lines_updated_at')->groupBy('order_id');
        $query = DB::table('orders as o')
            ->leftJoinSub($lines, 'ld', 'ld.order_id', '=', 'o.id')
            ->whereNull('o.deleted_at');

        if ($since) {
            $query->whereRaw(self::LAST_MODIFIED . ' >= ?', [$since->format('Y-m-d H:i:s')]);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('o.order_date', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('o.order_date', '<=', $request->query('to_date'));
        }
        if ($request->filled('order_no')) {
            $query->where('o.orderno', $request->query('order_no'));
        }
        if ($request->filled('distributor_code')) {
            $query->whereIn('o.seller_id', DB::table('customers')->where('customer_code', $request->query('distributor_code'))->select('id'));
        }

        $total = (clone $query)->count();
        $orders = $query->selectRaw('o.*, ' . self::LAST_MODIFIED . ' as last_modified')
            ->orderBy('last_modified')->orderBy('o.id')
            ->forPage($page, $pageSize)
            ->get();

        $data = $this->build($orders);
        $totalPages = (int) ceil($total / $pageSize);

        $this->writeLog($request, $client, $data->count(), 200, []);

        return response()->json([
            'success' => true,
            'message' => 'Records fetched successfully',
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_records' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
            ],
            'correlation_id' => $correlationId,
        ]);
    }

    private function build($orders)
    {
        if ($orders->isEmpty()) {
            return collect();
        }

        $lines = DB::table('order_details as d')
            ->leftJoin('products as p', 'p.id', '=', 'd.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('subcategories as s', 's.id', '=', 'p.subcategory_id')
            ->whereIn('d.order_id', $orders->pluck('id'))
            ->orderBy('d.id')
            ->get([
                'd.id', 'd.order_id', 'd.quantity', 'd.price', 'd.discount_amount', 'd.gst', 'd.gst_amount', 'd.line_total',
                'p.product_code', 'p.product_name', 'p.odoo_product_id', 'c.category_name', 's.subcategory_name',
            ])
            ->groupBy('order_id');

        $users = DB::table('users as u')
            ->leftJoin('designations as dg', 'dg.id', '=', 'u.designation_id')
            ->leftJoin('divisions as dv', 'dv.id', '=', 'u.division_id')
            ->whereIn('u.id', $orders->pluck('created_by')->filter()->unique())
            ->get(['u.id', 'u.name', 'u.employee_codes', 'u.reportingid', 'dg.designation_name', 'dv.division_name'])
            ->keyBy('id');

        $managerIds = $users->pluck('reportingid')->filter()
            ->flatMap(fn ($ids) => explode(',', (string) $ids))
            ->map(fn ($id) => (int) trim($id))->filter()->unique();
        $managers = DB::table('users')->whereIn('id', $managerIds)->pluck('name', 'id');

        $customers = DB::table('customers as cu')
            ->leftJoin('customer_types as ct', 'ct.id', '=', 'cu.customertype')
            ->whereIn('cu.id', $orders->pluck('buyer_id')->merge($orders->pluck('seller_id'))->filter()->unique())
            ->get(['cu.id', 'cu.name', 'cu.customer_code', 'cu.sap_code', 'ct.customertype_name'])
            ->keyBy('id');

        $statuses = DB::table('statuses')->whereIn('id', $orders->pluck('status_id')->filter()->unique())
            ->get(['id', 'status_name', 'display_name'])->keyBy('id');

        return $orders->map(function ($o) use ($lines, $users, $managers, $customers, $statuses) {
            $user = $users->get($o->created_by);
            $status = $statuses->get($o->status_id);

            return [
                'order_id' => $o->id,
                'order_no' => $o->orderno,
                'order_date' => $o->order_date ? substr($o->order_date, 0, 10) : null,
                'status' => $status ? ($status->display_name ?: $status->status_name) : 'Pending',
                'order_remark' => $o->order_remark !== '' ? $o->order_remark : null,
                'employee' => [
                    'code' => $user && $user->employee_codes !== null ? trim($user->employee_codes) : null,
                    'name' => $user->name ?? null,
                    'designation' => $user->designation_name ?? null,
                    'reporting_manager' => $user && $user->reportingid
                        ? collect(explode(',', (string) $user->reportingid))->map(fn ($id) => $managers->get((int) trim($id)))->filter()->implode(', ') ?: null
                        : null,
                    'zone' => $user->division_name ?? null,
                ],
                'customer' => $this->party($customers->get($o->buyer_id)),
                'distributor' => $this->party($customers->get($o->seller_id)),
                'total_qty' => (float) $o->total_qty,
                'sub_total' => (float) $o->sub_total,
                'gst_amount' => (float) $o->total_gst,
                'grand_total' => (float) $o->grand_total,
                'lines' => ($lines->get($o->id) ?? collect())->map(fn ($l) => [
                    'line_id' => $l->id,
                    'product_code' => $l->product_code,
                    'product_name' => $l->product_name,
                    'odoo_product_id' => $l->odoo_product_id !== null ? (int) $l->odoo_product_id : null,
                    'category' => $l->category_name,
                    'subcategory' => $l->subcategory_name,
                    'quantity' => (float) $l->quantity,
                    'rate' => (float) $l->price,
                    'discount_amount' => (float) $l->discount_amount,
                    'gst_percent' => (float) $l->gst,
                    'gst_amount' => (float) $l->gst_amount,
                    'line_total' => (float) $l->line_total,
                ])->values(),
                'created_at' => $o->created_at,
                'updated_at' => $o->last_modified,
                'time_zone' => config('app.timezone'),
            ];
        })->values();
    }

    private function party($customer): ?array
    {
        if (!$customer) {
            return null;
        }

        return [
            'name' => $customer->name,
            'code' => $customer->customer_code !== '' ? $customer->customer_code : null,
            'type' => $customer->customertype_name,
            // "Odoo Code" on the FieldKonnect customer form = Odoo partner ID (odoo_parent_id)
            'odoo_party_id' => $customer->sap_code !== null && $customer->sap_code !== '' ? $customer->sap_code : null,
        ];
    }

    private function error(Request $request, $client, string $message, string $field)
    {
        $this->writeLog($request, $client, 0, 400, [['field' => $field, 'message' => $message]]);

        return response()->json([
            'success' => false,
            'error' => ['code' => 'VALIDATION_ERROR', 'message' => $message, 'details' => [['field' => $field]]],
            'correlation_id' => $request->attributes->get('correlation_id'),
        ], 400);
    }

    private function writeLog(Request $request, $client, int $count, int $statusCode, array $errors): void
    {
        try {
            DB::table('odoo_sync_logs')->insert([
                'correlation_id' => $request->attributes->get('correlation_id'),
                'client_id' => $client->id,
                'mode' => $client->mode,
                'entity' => 'orders',
                'method' => $request->method(),
                'ip' => $request->ip(),
                'received_count' => $count,
                'status_code' => $statusCode,
                'errors' => $errors ? json_encode($errors) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Could not write odoo_sync_logs row', ['error' => $e->getMessage()]);
        }
    }
}
