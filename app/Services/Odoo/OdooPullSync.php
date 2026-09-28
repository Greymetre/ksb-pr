<?php

namespace App\Services\Odoo;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/*
|--------------------------------------------------------------------------
| Base for modules PULLED from Odoo (categories, sub-categories...).
|--------------------------------------------------------------------------
| run() pages through an Odoo method, hands each record to upsert(), and
| writes one odoo_sync_logs row per run (method = PULL).
|
| - One bad record never blocks the others.
| - Odoo unreachable / auth failed: rows already saved are kept, run logged as HTTP 502.
*/
abstract class OdooPullSync
{
    private const PAGE_SIZE = 100;
    // Stops a broken has_next from looping forever
    private const MAX_PAGES = 500;

    public function __construct(protected OdooRpcClient $odoo)
    {
    }

    /** odoo_sync_logs.entity */
    abstract public function entity(): string;

    /** [Odoo model, Odoo method] */
    abstract protected function source(): array;

    /**
     * @return array{status: string, errors?: array}  status: created | updated | skipped | failed
     */
    abstract protected function upsert(array $record, string $correlationId): array;

    /**
     * @return array{received: int, created: int, updated: int, skipped: int, failed: int, status_code: int, errors: array, correlation_id: string}
     */
    public function run(): array
    {
        [$model, $method] = $this->source();
        $correlationId = (string) Str::uuid();
        $summary = ['received' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $errors = [];
        $statusCode = 200;

        try {
            $page = 1;
            do {
                $result = $this->odoo->call($model, $method, [
                    ['page' => $page, 'page_size' => self::PAGE_SIZE],
                ]);

                foreach ($result['data'] ?? [] as $record) {
                    $index = $summary['received']++;
                    try {
                        $outcome = $this->upsert($record, $correlationId);
                        $summary[$outcome['status']]++;
                        if (!empty($outcome['errors'])) {
                            $errors[] = ['index' => $index, 'external_id' => $record['external_id'] ?? null, 'status' => $outcome['status'], 'errors' => $outcome['errors']];
                        }
                    } catch (Throwable $e) {
                        $summary['failed']++;
                        $errors[] = ['index' => $index, 'external_id' => $record['external_id'] ?? null, 'status' => 'failed', 'errors' => ['record' => [$e->getMessage()]]];
                    }
                }

                $hasNext = !empty($result['pagination']['has_next']);
                $page++;
            } while ($hasNext && $page <= self::MAX_PAGES);
        } catch (Throwable $e) {
            $statusCode = 502;
            $errors[] = ['index' => null, 'external_id' => null, 'status' => 'failed', 'errors' => ['odoo' => [$e->getMessage()]]];
            Log::error("Odoo {$this->entity()} sync failed", ['correlation_id' => $correlationId, 'error' => $e->getMessage()]);
        }

        if ($statusCode === 200 && $summary['failed'] > 0) {
            $statusCode = 207;
        }

        $this->writeLog($correlationId, $summary, $statusCode, $errors);

        return $summary + ['status_code' => $statusCode, 'errors' => $errors, 'correlation_id' => $correlationId];
    }

    /**
     * True when the stored row is at least as new as Odoo's updated_at.
     */
    protected function isUnchanged(?object $existing, ?Carbon $odooUpdatedAt): bool
    {
        return $existing && $odooUpdatedAt && $existing->odoo_updated_at
            && !Carbon::parse($existing->odoo_updated_at)->lt($odooUpdatedAt);
    }

    protected function toLocal(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value)->setTimezone(config('app.timezone')) : null;
    }

    private function writeLog(string $correlationId, array $summary, int $statusCode, array $errors): void
    {
        try {
            DB::table('odoo_sync_logs')->insert([
                'correlation_id' => $correlationId,
                'client_id' => null,
                'mode' => config('services.odoo.mode') === 'live' ? 'live' : 'test',
                'entity' => $this->entity(),
                'method' => 'PULL',
                'ip' => null,
                'received_count' => $summary['received'],
                'created_count' => $summary['created'],
                'updated_count' => $summary['updated'],
                'skipped_count' => $summary['skipped'],
                'failed_count' => $summary['failed'],
                'status_code' => $statusCode,
                'errors' => $errors ? json_encode(array_slice($errors, 0, 100)) : null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Could not write odoo_sync_logs row', ['error' => $e->getMessage()]);
        }
    }
}
