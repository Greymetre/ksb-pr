<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class NewInvoice extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED_SS = 1;
    public const STATUS_APPROVED_SALES = 2;
    public const STATUS_APPROVED_HO = 3;
    public const STATUS_REJECTED = 4;

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED_SS => 'Approved By SS',
        self::STATUS_APPROVED_SALES => 'Approved By Sales',
        self::STATUS_APPROVED_HO => 'Approved By HO',
        self::STATUS_REJECTED => 'Rejected',
    ];

    public const STATUS_CLASSES = [
        self::STATUS_PENDING => 'badge-warning',
        self::STATUS_APPROVED_SS => 'badge-info',
        self::STATUS_APPROVED_SALES => 'badge-primary',
        self::STATUS_APPROVED_HO => 'badge-success',
        self::STATUS_REJECTED => 'badge-danger',
    ];

    protected $table = 'new_invoices';

    protected $fillable = [
        'secondary_customer_id',
        'invoice_number',
        'invoice_date',
        'amount',
        'points',
        'approval_status',
        'approval_remark',
        'approved_ss_by',
        'approved_ss_at',
        'approved_sales_by',
        'approved_sales_at',
        'approved_ho_by',
        'approved_ho_at',
        'rejected_by',
        'rejected_at',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'amount' => 'decimal:2',
        'points' => 'decimal:2',
        'approved_ss_at' => 'datetime',
        'approved_sales_at' => 'datetime',
        'approved_ho_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'application/pdf']);
    }

    /**
     * Get the secondary customer that owns this invoice
     */
    public function customer()
    {
        return $this->belongsTo(SecondaryCustomer::class, 'secondary_customer_id', 'id');
    }

    /**
     * Get the user who created this invoice
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function approvedBySs()
    {
        return $this->belongsTo(User::class, 'approved_ss_by', 'id');
    }

    public function approvedBySales()
    {
        return $this->belongsTo(User::class, 'approved_sales_by', 'id');
    }

    public function approvedByHo()
    {
        return $this->belongsTo(User::class, 'approved_ho_by', 'id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by', 'id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(NewInvoiceApprovalLog::class, 'new_invoice_id', 'id');
    }

    public function getApprovalStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->approval_status] ?? self::STATUS_LABELS[self::STATUS_PENDING];
    }

    public function getApprovalStatusClassAttribute()
    {
        return self::STATUS_CLASSES[$this->approval_status] ?? self::STATUS_CLASSES[self::STATUS_PENDING];
    }

    public function canMoveToStatus(int $status): bool
    {
        if ($this->approval_status == self::STATUS_REJECTED || $this->approval_status == self::STATUS_APPROVED_HO) {
            return false;
        }

        return match ($status) {
            self::STATUS_APPROVED_SS => (int) $this->approval_status === self::STATUS_PENDING,
            self::STATUS_APPROVED_SALES => (int) $this->approval_status === self::STATUS_APPROVED_SS,
            self::STATUS_APPROVED_HO => (int) $this->approval_status === self::STATUS_APPROVED_SALES,
            self::STATUS_REJECTED => true,
            default => false,
        };
    }

    /**
     * Get invoices by a specific customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('secondary_customer_id', $customerId);
    }

    /**
     * Get invoices within a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }
}
