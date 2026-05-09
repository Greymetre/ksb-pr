<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProcessStep extends Model
{
    use HasFactory;

    protected $table = 'customer_process_steps';

    protected $fillable = [
        'customer_process_id',
        'value',
        'sort_order',
    ];

    public function process()
    {
        return $this->belongsTo(CustomerProcess::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('sortOrder', function ($query) {
            $query->orderBy('sort_order', 'asc');
        });
    }
}
