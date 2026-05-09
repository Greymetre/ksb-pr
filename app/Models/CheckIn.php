<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // if you actually use soft deletes

class CheckIn extends Model
{
    use HasFactory;
    // use SoftDeletes;   // ← uncomment only if you really use soft deletes

    protected $table = 'check_in';

    protected $fillable = [
        'active',
        'customer_id',           // ← keep for backward compatibility / old records
        'entity_type',           // NEW: 'customer', 'distributor', 'secondary_customer'
        'entity_id',             // NEW: the actual ID from the corresponding table
        'user_id',
        'checkin_date',
        'checkin_time',
        'checkin_latitude',
        'checkin_longitude',
        'checkin_address',
        'checkout_date',
        'checkout_time',
        'time_interval',
        'checkout_latitude',
        'checkout_longitude',
        'checkout_address',
        'deleted_at',
        'created_at',
        'updated_at',
        'distance',
        'beatscheduleid',
    ];

    protected $casts = [
        'entity_type' => 'string',
        'entity_id'   => 'integer',
    ];

    // ────────────────────────────────────────────────
    //  Relationships – updated / conditional
    // ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Old relationship – only for legacy customer records
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id')
            ->select('id', 'name', 'first_name', 'last_name', 'mobile', 'created_at', 'customertype');
    }

    /**
     * NEW – polymorphic-like accessor for the visited entity
     * Use $checkin->entity instead of $checkin->customer
     */
    public function getEntityAttribute()
    {
        if (!$this->entity_type || !$this->entity_id) {
            // fallback to old customer logic
            return $this->customer;
        }

        return match ($this->entity_type) {
            'distributor'        => MasterDistributor::select('id', 'legal_name', 'trade_name', 'mobile','beat_route', 'billing_city',      
    'billing_district','beat_route',
    'billing_address' )
                                        ->find($this->entity_id),
            'secondary_customer' => SecondaryCustomer::select('id', 'shop_name', 'owner_name', 'mobile_number','city_id','beat_id',
    'district_id',      
    'address_line')->with(['city', 'district', 'beat']) 
                                        ->find($this->entity_id),
            'customer'           => $this->customer,
            default              => null,
        };
    }

    /**
     * Helper: get display name no matter which entity type
     */
    public function getEntityNameAttribute()
    {
        $entity = $this->entity;

        if (!$entity) {
            return 'Unknown';
        }

        return match ($this->entity_type) {
            'distributor'        => $entity->trade_name ?? $entity->legal_name ?? 'Unnamed Distributor',
            'secondary_customer' => $entity->shop_name ?? 'Unnamed Shop',
            // 'customer'           => $entity->name ?? ($entity->first_name . ' ' . $entity->last_name) ?? 'Unknown Customer',
            default              => 'Unknown',
        };
    }

    /**
     * Helper: get entity type display name
     */
    public function getEntityTypeDisplayAttribute()
    {
        return match ($this->entity_type) {
            'distributor'        => $this->entity?->category ?? 'Distributor',
            'secondary_customer' => $this->entity?->sub_type ?? 'Secondary Customer',
            'customer'           => $this->entity?->customertypes->customertype_name ?? 'Customer',
            default              => 'Legacy Customer',
        };
    }

    public function beatschedule()
    {
        return $this->belongsTo(BeatSchedule::class, 'beatscheduleid', 'id')
            ->select('id', 'beat_id', 'beat_date');
    }

    public function orders()
    {
        // If orders are still linked only via beatscheduleid → keep as is
        // If you later want to link orders to entity_id too → add new relation
        return $this->hasMany(Order::class, 'beatscheduleid', 'beatscheduleid')
            ->select('id', 'beatscheduleid', 'total_qty', 'grand_total');
    }

    public function visitreport()
    {
        // Note: belongsTo (singular) is usually wrong for hasMany relationship
        // Most likely should be hasOne or hasMany depending on your business rule
        return $this->hasOne(VisitReport::class, 'checkin_id', 'id')
            ->select('id', 'checkin_id', 'description', 'report_title', 'visit_image', 'visit_type_id');
    }

    // If this is meant to be orders created by the user (not related to this check-in)
    public function ordersByUser()
    {
        return $this->hasMany(Order::class, 'created_by', 'user_id');
    }

    // Optional: scope for easier querying
    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('entity_type', $type)
                     ->where('entity_id', $id);
    }
    public function orders_sum()
{
    return $this->hasMany(\App\Models\Order::class, 'beatscheduleid', 'beatscheduleid');
}


}