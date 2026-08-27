<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'number',
        'started_at',
        'duration',
        'user_id',
        'status',
        'plivo_status',
        'plivo_call_uuid',
        'plivo_b_leg_uuid',
        'recording_url',
        'recording_id',
        'cost',
        'answered_at',
        'completed_at',
        'webhook_token',
    ];

    protected $hidden = ['webhook_token'];

    protected $casts = [
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * A call log belongs to a lead.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
