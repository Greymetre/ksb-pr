<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',
        'opening_stock',
        'active',
        'created_by',
        'updated_by',
    ];

    public function stockMovements()
    {
        return $this->hasMany(PromotionalGiftStockMovement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name');
    }
}
