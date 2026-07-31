<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\File;

class UserSessionInvalidator
{
    /**
     * Revoke every API token and file-backed web session belonging to the user.
     */
    public static function invalidate(User $user): void
    {
        $user->tokens()->delete();

        $sessionPath = storage_path('framework/sessions');

        if (! File::isDirectory($sessionPath)) {
            return;
        }

        foreach (File::files($sessionPath) as $file) {
            $sessionContent = File::get($file);

            if (str_contains($sessionContent, 'user_idsss";i:' . $user->id . ';')) {
                File::delete($file);
            }
        }
    }
}
