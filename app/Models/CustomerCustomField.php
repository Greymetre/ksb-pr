<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'description',
        'created_by'
    ];

    public function values()
    {
        return $this->hasMany(CustomerCustomFieldValue::class, 'custom_field_id', 'id');
    }

    public function creatbyname()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->select('id', 'name');
    }
}
