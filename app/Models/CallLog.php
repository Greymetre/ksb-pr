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
