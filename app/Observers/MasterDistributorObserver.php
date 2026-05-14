<?php

namespace App\Observers;

use App\Models\MasterDistributor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MasterDistributorObserver
{
    /**
     * Handle the MasterDistributor "created" event.
     * Creates a new user when a distributor is created.
     */
    public function created(MasterDistributor $distributor): void
    {
        // Normalize email and mobile
        $email = strtolower(trim($distributor->email ?? ''));
        $mobile = preg_replace('/\D+/', '', $distributor->mobile ?? '') ?? '';

        // Skip if email or mobile is empty
        if ($email === '' || $mobile === '') {
            return;
        }

        // Skip if mobile is too long for the column
        if (strlen($mobile) > 11) {
            return;
        }

        // Check if user already exists with this email or mobile
        $existingUser = User::where('email', $email)
            ->orWhere('mobile', $mobile)
            ->first();

        if ($existingUser) {
            return;
        }

        // Prepare user data
        $name = trim($distributor->contact_person ?: $distributor->trade_name ?: $distributor->legal_name);
        $name = $name !== '' ? $name : 'Distributor ' . $distributor->id;

        $nameParts = preg_split('/\s+/', $name, 2);

        $userData = [
            'active' => 'Y',
            'name' => $name,
            'first_name' => $nameParts[0] ?? $name,
            'last_name' => $nameParts[1] ?? '',
            'mobile' => $mobile,
            'email' => $email,
            'password' => Hash::make($mobile),
            'password_string' => $mobile,
            'customerid' => $distributor->id,
        ];

        // Create the user
        $user = User::create($userData);

        // Assign Distributor role
        $role = Role::where('name', 'Distributor')
            ->where('guard_name', 'users')
            ->first();

        if ($role) {
            $user->assignRole($role);
            
            // Assign role permissions to user
            $permissions = $role->permissions;
            if ($permissions->isNotEmpty()) {
                $user->givePermissionTo($permissions);
            }
        }
    }

    /**
     * Handle the MasterDistributor "updating" event.
     * Updates user email/mobile when distributor details are updated.
     */
    public function updating(MasterDistributor $distributor): void
    {
        // Check if email or mobile changed
        if ($distributor->isDirty('email') || $distributor->isDirty('mobile')) {
            $user = User::where('customerid', $distributor->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Distributor')
                        ->where('guard_name', 'users');
                })
                ->first();

            if ($user) {
                $email = strtolower(trim($distributor->email ?? ''));
                $mobile = preg_replace('/\D+/', '', $distributor->mobile ?? '') ?? '';

                // Only update if both email and mobile are valid
                if ($email !== '' && $mobile !== '' && strlen($mobile) <= 11) {
                    // Check if new email/mobile is already taken
                    $conflict = User::where('id', '!=', $user->id)
                        ->where(function ($query) use ($email, $mobile) {
                            $query->where('email', $email)
                                ->orWhere('mobile', $mobile);
                        })
                        ->first();

                    if (!$conflict) {
                        $user->update([
                            'email' => $email,
                            'mobile' => $mobile,
                            'password' => Hash::make($mobile),
                        ]);
                    }
                }
            }
        }
    }
}
