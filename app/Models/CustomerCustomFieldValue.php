<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_field_id',
        'value',
        'sort_order'
    ];

    public function field()
    {
        return $this->belongsTo(CustomerCustomField::class, 'custom_field_id');
    }
}
