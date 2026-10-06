<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionalGiftStockMovement extends Model
{
    public const TYPES = [
        'opening' => 'Opening Stock',
        'add' => 'Stock Added',
        'activity' => 'Activity Completed',
    ];

    protected $fillable = [
        'promotional_gift_id',
        'type',
        'quantity',
        'balance_after',
        'promotional_activity_id',
        'remark',
        'created_by',
    ];

    public function gift()
    {
        return $this->belongsTo(PromotionalGift::class, 'promotional_gift_id');
    }

    public function activity()
    {
        return $this->belongsTo(PromotionalActivity::class, 'promotional_activity_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name');
    }
}
