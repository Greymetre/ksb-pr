<?php

namespace App\Console\Commands;

use App\Services\Odoo\CategorySync;
use Illuminate\Console\Command;

/*
| Pull product categories from Odoo into odoo_categories.
| Scheduled twice a day in app/Console/Kernel.php. See odoo-integration-docs/README.md
|
|   php artisan odoo:sync-categories
*/
class OdooSyncCategories extends Command
{
    protected $signature = 'odoo:sync-categories';

    protected $description = 'Pull product categories from Odoo (Category Master Odoo)';

    public function handle(CategorySync $sync)
    {
        $result = $sync->run();

        $this->table(['Received', 'Created', 'Updated', 'Skipped', 'Failed', 'HTTP'], [[
            $result['received'], $result['created'], $result['updated'], $result['skipped'], $result['failed'], $result['status_code'],
        ]]);
        $this->line('Correlation ID: ' . $result['correlation_id']);

        foreach (array_slice($result['errors'], 0, 10) as $error) {
            $this->warn(($error['external_id'] ?? 'request') . ': ' . json_encode($error['errors']));
        }

        return $result['status_code'] === 502 ? self::FAILURE : self::SUCCESS;
    }
}
