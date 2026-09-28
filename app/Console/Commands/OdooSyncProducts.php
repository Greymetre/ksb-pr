<?php

namespace App\Console\Commands;

use App\Services\Odoo\ProductSync;
use Illuminate\Console\Command;

/*
| Pull products from Odoo into odoo_products.
| Scheduled twice a day in app/Console/Kernel.php. See odoo-integration-docs/README.md
|
|   php artisan odoo:sync-products
*/
class OdooSyncProducts extends Command
{
    protected $signature = 'odoo:sync-products';

    protected $description = 'Pull products from Odoo (Product Master Odoo)';

    public function handle(ProductSync $sync)
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
