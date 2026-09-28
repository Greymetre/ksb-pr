<?php

namespace App\Services\Odoo;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/*
|--------------------------------------------------------------------------
| Pulls product categories from Odoo into odoo_categories.
|--------------------------------------------------------------------------
| Odoo: product.category / get_fieldkonnect_categories (paginated).
| Run by `php artisan odoo:sync-categories` (cron, twice a day) and by the
| "Sync now" button on Odoo Sync -> Category Master Odoo.
|
| - Upsert key is external_id; re-syncing never duplicates.
| - A record whose updated_at is not newer than the stored one is skipped.
| - One bad record never blocks the others.
| - One odoo_sync_logs row per run (entity = categories, method = PULL).
*/
class CategorySync
{
    public const ENTITY = 'categories';
    private const PAGE_SIZE = 100;
    // Stops a broken has_next from looping forever
    private const MAX_PAGES = 500;

    private array $categoryCache = [];

    public function __construct(private OdooRpcClient $odoo)
    {
    }

    /**
     * @return array{received: int, created: int, updated: int, skipped: int, failed: int, status_code: int, errors: array, correlation_id: string}
     */
    public function run(): array
    {
        $correlationId = (string) Str::uuid();
        $summary = ['received' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $errors = [];
        $statusCode = 200;

        try {
            $page = 1;
            do {
                $result = $this->odoo->call('product.category', 'get_fieldkonnect_categories', [
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
            // Odoo unreachable / auth failed: keep what was already saved, log the error
            $statusCode = 502;
            $errors[] = ['index' => null, 'external_id' => null, 'status' => 'failed', 'errors' => ['odoo' => [$e->getMessage()]]];
            Log::error('Odoo category sync failed', ['correlation_id' => $correlationId, 'error' => $e->getMessage()]);
        }

        if ($statusCode === 200 && $summary['failed'] > 0) {
            $statusCode = 207;
        }

        $this->writeLog($correlationId, $summary, $statusCode, $errors);

        return $summary + ['status_code' => $statusCode, 'errors' => $errors, 'correlation_id' => $correlationId];
    }

    /**
     * @return array{status: string, errors?: array}
     */
    private function upsert(array $record, string $correlationId): array
    {
        if (empty($record['external_id']) || empty($record['category_name'])) {
            return ['status' => 'failed', 'errors' => ['record' => ['external_id and category_name are required.']]];
        }

        $odooUpdatedAt = $this->toLocal($record['updated_at'] ?? null);

        $existing = DB::table('odoo_categories')
            ->where('external_id', $record['external_id'])
            ->first(['id', 'odoo_updated_at', 'category_id']);

        // Unchanged in Odoo: nothing to do, unless the FieldKonnect link is still missing
        if ($existing && $odooUpdatedAt && $existing->odoo_updated_at && !Carbon::parse($existing->odoo_updated_at)->lt($odooUpdatedAt)
            && ($existing->category_id || !$this->resolveCategoryId($record['category_name']))) {
            return ['status' => 'skipped'];
        }

        $row = [
            'category_code' => (string) ($record['category_code'] ?? ''),
            'category_name' => $record['category_name'],
            'description' => $record['description'] ?? null,
            'ranking' => (int) ($record['ranking'] ?? 1),
            'image_url' => $record['image_url'] ?? null,
            'active' => filter_var($record['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_deleted' => filter_var($record['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'category_id' => $this->resolveCategoryId($record['category_name']),
            'odoo_created_at' => $this->toLocal($record['created_at'] ?? null),
            'odoo_updated_at' => $odooUpdatedAt,
            'last_correlation_id' => $correlationId,
            'raw_payload' => json_encode($record),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('odoo_categories')->where('id', $existing->id)->update($row);
            return ['status' => 'updated'];
        }

        DB::table('odoo_categories')->insert($row + [
            'external_id' => $record['external_id'],
            'created_at' => now(),
        ]);

        return ['status' => 'created'];
    }

    /**
     * category_name is matched against FieldKonnect categories.category_name (not deleted).
     */
    private function resolveCategoryId(string $name): ?int
    {
        $key = mb_strtolower(trim($name));

        if (!array_key_exists($key, $this->categoryCache)) {
            $this->categoryCache[$key] = DB::table('categories')
                ->whereNull('deleted_at')
                ->where('category_name', trim($name))
                ->value('id');
        }

        return $this->categoryCache[$key];
    }

    private function writeLog(string $correlationId, array $summary, int $statusCode, array $errors): void
    {
        try {
            DB::table('odoo_sync_logs')->insert([
                'correlation_id' => $correlationId,
                'client_id' => null,
                'mode' => config('services.odoo.mode') === 'live' ? 'live' : 'test',
                'entity' => self::ENTITY,
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

    private function toLocal(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value)->setTimezone(config('app.timezone')) : null;
    }
}
