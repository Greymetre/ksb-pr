<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewDealerTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_month',
        'target',
        'achievement',
        'note',
        'created_by',
    ];

    protected $casts = [
        'target_month' => 'date',
        'target' => 'integer',
        'achievement' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
