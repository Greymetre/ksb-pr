<?php

namespace App\Http\Controllers\Api\Odoo;

use App\Http\Controllers\Controller;
use App\Services\Odoo\PartyPriceSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
| Odoo -> FieldKonnect party-wise pricing. API spec: odoo-integration-docs/party-prices-api.md
*/
class PartyPriceController extends Controller
{
    private const MAX_RECORDS = 500;

    public function store(Request $request, PartyPriceSync $sync)
    {
        $client = $request->attributes->get('odoo_client');
        $correlationId = $request->attributes->get('correlation_id');
        $records = $request->input('records');

        if (!is_array($records) || !array_is_list($records) || count($records) === 0 || count($records) > self::MAX_RECORDS) {
            $error = ['field' => 'records', 'message' => 'records must be a non-empty array of at most ' . self::MAX_RECORDS . ' items'];
            $this->writeLog($request, $client, 0, [], 400, [$error]);

            return response()->json([
                'success' => false,
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Invalid request body', 'details' => [$error]],
                'correlation_id' => $correlationId,
            ], 400);
        }

        $summary = ['received' => count($records), 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $results = [];

        foreach ($records as $index => $record) {
            $externalId = is_array($record) ? ($record['external_id'] ?? null) : null;

            try {
                $result = is_array($record)
                    ? $sync->upsert($record, $client->mode, $correlationId)
                    : ['status' => 'failed', 'errors' => ['record' => ['Each record must be a JSON object.']]];
            } catch (\Throwable $e) {
                Log::error('Odoo party price upsert failed', ['correlation_id' => $correlationId, 'external_id' => $externalId, 'error' => $e->getMessage()]);
                $result = ['status' => 'failed', 'errors' => ['record' => ['Internal error while saving this record.']]];
            }

            $summary[$result['status']]++;
            $results[] = [
                'index' => $index,
                'external_id' => $externalId,
                'status' => $result['status'],
                'errors' => $result['errors'] ?? [],
                'warnings' => $result['warnings'] ?? [],
            ];
        }

        $allFailed = $summary['failed'] === $summary['received'];
        $statusCode = $allFailed ? 422 : 200;

        $this->writeLog($request, $client, $summary['received'], $summary, $statusCode,
            array_values(array_filter($results, fn ($r) => $r['status'] !== 'created' && $r['status'] !== 'updated')));

        return response()->json([
            'success' => !$allFailed,
            'message' => "Processed {$summary['received']} records: {$summary['created']} created, {$summary['updated']} updated, {$summary['skipped']} skipped, {$summary['failed']} failed",
            'mode' => $client->mode,
            'summary' => $summary,
            'results' => $results,
            'correlation_id' => $correlationId,
        ], $statusCode);
    }

    /**
     * Lets Odoo read back what FieldKonnect has stored for its key's mode.
     */
    public function index(Request $request)
    {
        $client = $request->attributes->get('odoo_client');
        $pageSize = min(max((int) $request->query('page_size', 100), 1), 500);
        $page = max((int) $request->query('page', 1), 1);

        $query = DB::table(PartyPriceSync::tableFor($client->mode));
        foreach (['external_id', 'party_code', 'product_code', 'price_list_code'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy('id')
            ->forPage($page, $pageSize)
            ->get()
            ->map(function ($row) {
                unset($row->raw_payload);
                return $row;
            });
        $totalPages = (int) ceil($total / $pageSize);

        return response()->json([
            'success' => true,
            'message' => 'Records fetched successfully',
            'mode' => $client->mode,
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total_records' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
            ],
            'correlation_id' => $request->attributes->get('correlation_id'),
        ]);
    }

    private function writeLog(Request $request, $client, int $received, array $summary, int $statusCode, array $errors): void
    {
        try {
            DB::table('odoo_sync_logs')->insert([
                'correlation_id' => $request->attributes->get('correlation_id'),
                'client_id' => $client->id,
                'mode' => $client->mode,
                'entity' => 'party_prices',
                'method' => $request->method(),
                'ip' => $request->ip(),
                'received_count' => $received,
                'created_count' => $summary['created'] ?? 0,
                'updated_count' => $summary['updated'] ?? 0,
                'skipped_count' => $summary['skipped'] ?? 0,
                'failed_count' => $summary['failed'] ?? 0,
                'status_code' => $statusCode,
                'errors' => $errors ? json_encode(array_slice($errors, 0, 100)) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Could not write odoo_sync_logs row', ['error' => $e->getMessage()]);
        }
    }
}
