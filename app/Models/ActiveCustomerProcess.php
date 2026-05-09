<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveCustomerProcess extends Model
{
    use HasFactory;

    protected $table = 'active_customer_processes';

    protected $fillable = [
        'customer_id',
        'process_id',
        'assigned_by',
        'assigned_to',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function process()
    {
        return $this->belongsTo(CustomerProcess::class, 'process_id');
    }

    public function steps()
    {
        return $this->hasMany(ActiveCustomerProcessStep::class, 'active_customer_process_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
