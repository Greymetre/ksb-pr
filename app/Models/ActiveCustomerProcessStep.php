<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveCustomerProcessStep extends Model
{
    use HasFactory;

    protected $table = 'active_customer_process_steps';

    protected $fillable = [
        'active_customer_process_id',
        'customer_process_step_id',
        'status',
        'completed_by',
        'completed_at',
        'remark',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function activeProcess()
    {
        return $this->belongsTo(ActiveCustomerProcess::class, 'active_customer_process_id');
    }

    public function step()
    {
        return $this->belongsTo(CustomerProcessStep::class, 'customer_process_step_id');
    }

    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
