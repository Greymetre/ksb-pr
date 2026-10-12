<?php

namespace App\Console\Commands;

use App\Services\Odoo\CustomerSync;
use Illuminate\Console\Command;

/*
| Pull primary customers from Odoo into odoo_customers.
| Scheduled twice a day in app/Console/Kernel.php. See odoo-integration-docs/README.md
|
|   php artisan odoo:sync-customers
*/
class OdooSyncCustomers extends Command
{
    protected $signature = 'odoo:sync-customers';

    protected $description = 'Pull primary customers from Odoo (Primary Customer Odoo)';

    public function handle(CustomerSync $sync)
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
