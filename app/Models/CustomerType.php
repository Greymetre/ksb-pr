<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    use HasFactory;

    protected $table = 'customer_types';

    protected $fillable = [  'active', 'customertype_name', 'type_name','created_by', 'updated_by', 'deleted_at', 'created_at', 'updated_at'];

    public function createdbyname()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id')->select('id','name');
    }

    public function customers()
    {
        return $this->hasMany(Customers::class, 'customertype', 'id');
    }

    public function isRetailer(): bool
    {
        return strtolower(trim((string) $this->type_name)) === 'retailer';
    }

    public function isDealer(): bool
    {
        return strtolower(trim((string) $this->type_name)) === 'dealer';
    }

    public function scopeRetailer($query)
    {
        return $query->whereRaw('LOWER(type_name) = ?', ['retailer']);
    }

    public function scopeNonRetailer($query)
    {
        return $query->whereRaw('LOWER(type_name) != ?', ['retailer']);
    }
}
