<?php

// Test script to verify the MasterDistributorObserver works
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MasterDistributor;
use App\Models\User;

// Create a test distributor
$distributor = MasterDistributor::create([
    'legal_name' => 'Test Observer Distributor',
    'email' => 'testobserveruser@example.com',
    'mobile' => '8888999900',
    'contact_person' => 'John Doe Test'
]);

echo "Distributor created: ID {$distributor->id}\n";

// Check if user was automatically created
$user = User::where('customerid', $distributor->id)->first();

if ($user) {
    $role = $user->roles()->pluck('name')->first();
    echo "✓ User automatically created!\n";
    echo "  - Email: {$user->email}\n";
    echo "  - Mobile: {$user->mobile}\n";
    echo "  - Role: {$role}\n";
} else {
    echo "✗ User was NOT created\n";
}
