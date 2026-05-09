<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterDistributor;
use App\Models\SecondaryCustomer;
class BeatCustomer extends Model
{
    use HasFactory;

    protected $table = 'beat_customers';

    protected $fillable = [ 'active', 'beat_id','distributor_id', 'customer_id','customer_type','created_at', 'updated_at'];

    public function beats()
    {
        return $this->belongsTo('App\Models\Beat', 'beat_id', 'id')->select('id', 'active','beat_name','created_by');
    }



    public function beatschedules()
    {
        return $this->belongsTo('App\Models\BeatSchedule', 'beat_id', 'beat_id')->select('id','beat_id','beat_date','user_id');
    }

public function retailer()
{
    return $this->belongsTo(\App\Models\SecondaryCustomer::class,'distributor_id','id')
        ->select('id','shop_name as name','mobile_number as mobile');
}

public function distributor()
{
    return $this->belongsTo(\App\Models\MasterDistributor::class,'distributor_id','id')
        ->select('id','trade_name as name','mobile');
}


public function retailerFull()
{
    return $this->belongsTo(\App\Models\SecondaryCustomer::class,'distributor_id','id');
}

public function distributorFull()
{
    return $this->belongsTo(\App\Models\MasterDistributor::class,'distributor_id','id');
}


public function customerName()
{
    if ($this->customer_type == 'secondary') {
        return optional($this->retailer)->shop_name;
    }

    if ($this->customer_type == 'master') {
        return optional($this->distributor)->trade_name;
    }

    return null;
}

public function getCustomerAttribute()
{
    if ($this->customer_type === 'master') {
        return $this->distributor; // already loaded relation
    }

    if ($this->customer_type === 'secondary') {
        return $this->retailer; // already loaded relation
    }

    return null;
}

public function getCustomerFullAttribute()
{
    if ($this->customer_type === 'master') {
        return $this->distributorFull;
    }

    if ($this->customer_type === 'secondary') {
        return $this->retailerFull;
    }

    return null;
}
}
