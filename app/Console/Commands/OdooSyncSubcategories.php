<?php

namespace App\Console\Commands;

use App\Services\Odoo\SubcategorySync;
use Illuminate\Console\Command;

/*
| Pull product sub-categories from Odoo into odoo_subcategories.
| Scheduled twice a day in app/Console/Kernel.php. See odoo-integration-docs/README.md
|
|   php artisan odoo:sync-subcategories
*/
class OdooSyncSubcategories extends Command
{
    protected $signature = 'odoo:sync-subcategories';

    protected $description = 'Pull product sub-categories from Odoo (Sub Category Master Odoo)';

    public function handle(SubcategorySync $sync)
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
