<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'active', 'type', 'data', 'image', 'read', 'model', 'model_id',
        'delivery_status', 'sent_at', 'failure_reason', 'customer_id', 'user_id',
    ];

    protected $casts = [
        'read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select('id','name');
    }
}
