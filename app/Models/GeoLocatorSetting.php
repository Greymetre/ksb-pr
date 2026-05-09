<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoLocatorSetting extends Model
{
    protected $fillable = [
        'customer_filter',
        'lead_filter',
    ];

    protected $casts = [
        'customer_filter' => 'array',
        'lead_filter' => 'array',
    ];
}
