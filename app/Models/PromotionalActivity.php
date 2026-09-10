<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_status_id', 'activity_date', 'location_name', 'company_share',
        'distributor_share', 'remark', 'approval_status', 'created_by',
        'reporting_manager_id', 'approved_rejected_by', 'approved_rejected_at',
        'approval_remark',
        'distributor_id', 'activity_photos', 'participants', 'execution_remark', 'completed_at',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'company_share' => 'decimal:2',
        'distributor_share' => 'decimal:2',
        'approved_rejected_at' => 'datetime',
        'activity_photos' => 'array',
        'participants' => 'array',
        'completed_at' => 'datetime',
    ];

    public function activityType()
    {
        return $this->belongsTo(Status::class, 'activity_status_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name', 'reportingid');
    }

    public function reportingManager()
    {
        return $this->belongsTo(User::class, 'reporting_manager_id')->select('id', 'name', 'designation_id');
    }

    public function gifts()
    {
        return $this->belongsToMany(PromotionalGift::class, 'promotional_activity_gifts')
            ->withPivot('quantity')->withTimestamps();
    }

    public function distributor()
    {
        return $this->belongsTo(Customers::class, 'distributor_id');
    }
}
