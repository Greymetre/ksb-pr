<?php

namespace App\Console\Commands;

use App\Models\OdooApiClient;
use Illuminate\Console\Command;

/*
| Manage Odoo API keys. See odoo-integration-docs/README.md
|
|   php artisan odoo:client create "Odoo UAT" --mode=test
|   php artisan odoo:client list
|   php artisan odoo:client revoke 3
*/
class OdooClient extends Command
{
    protected $signature = 'odoo:client {action : create | list | revoke} {value? : client name (create) or id (revoke)} {--mode=test : test or live}';

    protected $description = 'Create, list or revoke Odoo integration API keys';

    public function handle()
    {
        switch ($this->argument('action')) {
            case 'create':
                return $this->create();
            case 'list':
                return $this->listClients();
            case 'revoke':
                return $this->revoke();
            default:
                $this->error('Action must be create, list or revoke.');
                return self::FAILURE;
        }
    }

    private function create()
    {
        $name = $this->argument('value');
        $mode = $this->option('mode');

        if (!$name) {
            $this->error('Give a name, e.g. php artisan odoo:client create "Odoo UAT" --mode=test');
            return self::FAILURE;
        }
        if (!in_array($mode, ['test', 'live'], true)) {
            $this->error('--mode must be test or live.');
            return self::FAILURE;
        }

        $prefix = "odk_{$mode}_";
        $plainKey = $prefix . bin2hex(random_bytes(24));

        $client = OdooApiClient::create([
            'name' => $name,
            'key_prefix' => $prefix,
            'api_key_hash' => OdooApiClient::hashKey($plainKey),
            'mode' => $mode,
            'active' => true,
        ]);

        $this->info("Client #{$client->id} \"{$name}\" created in {$mode} mode.");
        $this->line('API key (shown only once, store it safely):');
        $this->line($plainKey);

        return self::SUCCESS;
    }

    private function listClients()
    {
        $this->table(
            ['ID', 'Name', 'Mode', 'Active', 'Key prefix', 'Last used', 'Created'],
            OdooApiClient::orderBy('id')->get()->map(fn ($c) => [
                $c->id, $c->name, $c->mode, $c->active ? 'yes' : 'no', $c->key_prefix, $c->last_used_at, $c->created_at,
            ])
        );

        return self::SUCCESS;
    }

    private function revoke()
    {
        $client = OdooApiClient::find($this->argument('value'));

        if (!$client) {
            $this->error('Client not found. Use: php artisan odoo:client list');
            return self::FAILURE;
        }

        $client->update(['active' => false]);
        $this->info("Client #{$client->id} \"{$client->name}\" revoked.");

        return self::SUCCESS;
    }
}
