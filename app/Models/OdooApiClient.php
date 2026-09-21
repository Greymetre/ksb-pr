<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdooApiClient extends Model
{
    protected $table = 'odoo_api_clients';

    protected $fillable = ['name', 'key_prefix', 'api_key_hash', 'mode', 'active', 'last_used_at'];

    protected $hidden = ['api_key_hash'];

    protected $casts = [
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public static function hashKey(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }
}
