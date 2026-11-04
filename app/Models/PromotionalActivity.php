<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no', 'activity_status_id', 'activity_date', 'location_name', 'purpose',
        'estimated_budget', 'company_share', 'distributor_share', 'remark', 'attachments',
        'approval_status', 'created_by',
        'reporting_manager_id', 'approved_rejected_by', 'approved_rejected_at',
        'approval_remark',
        'distributor_id', 'activity_photos', 'participants', 'execution_remark', 'completed_at',
        'actual_expense', 'bills', 'execution_submitted_at', 'reviewed_by', 'reviewed_at', 'review_remark',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'estimated_budget' => 'decimal:2',
        'company_share' => 'decimal:2',
        'distributor_share' => 'decimal:2',
        'attachments' => 'array',
        'approved_rejected_at' => 'datetime',
        'activity_photos' => 'array',
        'participants' => 'array',
        'completed_at' => 'datetime',
        'actual_expense' => 'decimal:2',
        'bills' => 'array',
        'execution_submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public const MAX_ATTACHMENTS = 5;

    public const DECISIONS = ['approved', 'rejected', 'revision_required'];

    // Activity Request ID, e.g. PA-00012, given once the row has its id.
    protected static function booted()
    {
        static::created(function (PromotionalActivity $activity) {
            if (!$activity->request_no) {
                $activity->forceFill(['request_no' => 'PA-' . str_pad($activity->id, 5, '0', STR_PAD_LEFT)])->saveQuietly();
            }
        });
    }

    public static function decisionMessage(string $status): string
    {
        return $status === 'revision_required' ? 'Activity sent back for correction.' : 'Activity '.$status.' successfully.';
    }

    // Actual expense above the estimated budget is flagged to the reviewing manager.
    public function isOverBudget(): bool
    {
        return $this->actual_expense !== null && $this->estimated_budget !== null
            && (float) $this->actual_expense > (float) $this->estimated_budget;
    }

    // Push + in-app notification to everyone in the creator's reporting line (they can all approve).
    public function notifyManagers(string $message, string $title): void
    {
        $creator = User::select('id', 'reportingid')->find($this->created_by);
        $managerIds = collect(explode(',', (string) optional($creator)->reportingid))
            ->map(fn ($id) => trim($id))->filter(fn ($id) => ctype_digit($id))
            ->map(fn ($id) => (int) $id)->reject(fn ($id) => $id === (int) $this->created_by)->unique();

        foreach ($managerIds as $managerId) {
            SendPushNotification($managerId, $message, 'promotional_activity', $this->id, $title);
        }
    }

    // Tells the creator what the manager decided, with the manager's remark.
    public function notifyCreatorOfDecision(): void
    {
        [$title, $message] = match ($this->approval_status) {
            'approved' => ['Promotional activity approved', 'Your promotional activity '.$this->request_no.' has been approved.'],
            'rejected' => ['Promotional activity rejected', 'Your promotional activity '.$this->request_no.' has been rejected.'],
            'revision_required' => ['Promotional activity needs correction', 'Your promotional activity '.$this->request_no.' has been sent back for correction.'],
        };
        if ($this->approval_remark) $message .= ' Remark: '.$this->approval_remark;

        SendPushNotification($this->created_by, $message, 'promotional_activity', $this->id, $title);
    }

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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_rejected_by')->select('id', 'name');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->select('id', 'name');
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
