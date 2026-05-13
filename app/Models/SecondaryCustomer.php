<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryCustomer extends Model
{
    use HasFactory;

    protected $table = 'secondary_customers';

    protected $fillable = [
    'type',
    'sub_type',
    'owner_name',
    'shop_name',
    'mobile_number',
    'whatsapp_number',
    'owner_photo',
    'shop_photo',
    'vehicle_segment',
    'address_line',
    'belt_area_market_name',
    'saathi_awareness_status',
    'nistha_awareness_status',
    'opportunity_status',
    'gps_location',
    'country_id',
    'state_id',
    'district_id',
    'city_id',
    'pincode_id',
    'beat_id',
    'distributor_name',

    'gst_number',
    'pan_number',
    'gst_attachment',
    'pan_attachment',
    'bank_proof',
    'bank_account_type',
    'bank_account_number',
    'bank_name',
    'ifsc_code',
    'account_holder_name',

    'status',
    'active',
    'employee_id',
    'created_by',
    'agri_distributor',
    'remark',
    'approve_reject_by',
    'gmap',
    'status_updated_at'
    
];

public function state()
{
    return $this->belongsTo(\App\Models\State::class, 'state_id','id');
}

public function city()
{
    return $this->belongsTo(\App\Models\City::class, 'city_id','id');
}

public function beat()
{
    return $this->belongsTo(\App\Models\Beat::class);
}
public function district()
{
    return $this->belongsTo(\App\Models\District::class, 'district_id','id');
}

public function pincode()
{
    return $this->belongsTo(\App\Models\Pincode::class, 'pincode_id');
}

public function country()
{
    return $this->belongsTo(\App\Models\Country::class, 'country_id');
}
public function distributor()
{
    return $this->belongsTo(MasterDistributor::class, 'distributor_name');
}

public function getMobileNumbersAttribute()
{
    return explode(',', $this->mobile_number);
}
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function employee()
{
    return $this->belongsTo(User::class, 'employee_id');
}
public function agriDistributor()
{
    return $this->belongsTo(MasterDistributor::class, 'agri_distributor');
}
public function approvedBy()
{
    return $this->belongsTo(User::class, 'approve_reject_by');
}
public function getEmployeeNamesAttribute()
{
    if (!$this->employee_id) return '-';

    $ids = explode(',', $this->employee_id);

    return \App\Models\User::whereIn('id', $ids)
        ->pluck('name')
        ->map(fn($name) => \Illuminate\Support\Str::title($name))
        ->implode(', ');
}
public function orders()
{
    return $this->hasMany(Order::class, 'buyer_id'); 
    // ensure buyer_id = secondary_customer_id
}
}