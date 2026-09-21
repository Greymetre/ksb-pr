<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
| Creates the CRM login for the Odoo developer with only the "odoo" role
| (Odoo Sync page). See odoo-integration-docs/README.md
|
|   php artisan odoo:crm-user "Odoo Developer" odoo.dev@example.com 9876543210
*/
class OdooCrmUser extends Command
{
    protected $signature = 'odoo:crm-user {name} {email} {mobile}';

    protected $description = 'Create a CRM user with the odoo role (Odoo Sync page only)';

    public function handle()
    {
        $email = $this->argument('email');
        $mobile = $this->argument('mobile');

        if (User::where('email', $email)->orWhere('mobile', $mobile)->exists()) {
            $this->error('A user with this email or mobile already exists. Assign the "odoo" role to it from CRM > Users instead.');
            return self::FAILURE;
        }

        $password = Str::random(14);

        try {
            $user = User::create([
                'name' => $this->argument('name'),
                'first_name' => $this->argument('name'),
                'email' => $email,
                'mobile' => $mobile,
                'password' => Hash::make($password),
                'active' => 'Y',
            ]);
            $user->assignRole('odoo');
        } catch (\Throwable $e) {
            $this->error('Could not create the user: ' . $e->getMessage());
            $this->line('Run the migrations first, or create the user from CRM > Users and give it only the "odoo" role.');
            return self::FAILURE;
        }

        $this->info("User #{$user->id} created with the odoo role.");
        $this->line("Login: {$email} (or {$mobile})");
        $this->line("Password (shown only once): {$password}");

        return self::SUCCESS;
    }
}
