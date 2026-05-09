<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourProgramme extends Model
{
    use HasFactory;

    protected $table = 'tour_programmes';

    protected $fillable = [ 'date', 'userid', 'town','district', 'objectives', 'type', 'status', 'deleted_at', 'created_at', 'updated_at', 'remark'];

    public function userinfo()
    {
        // return $this->belongsTo('App\Models\User', 'userid', 'id')->select('id','name');
        return $this->belongsTo('App\Models\User', 'userid', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'id');
        //                     ^ related model
        //                           ^ foreign key on tour_programmes table
        //                                 ^ primary key on users table
    }

    public function tourdetails()
    {
        return $this->hasMany('App\Models\TourDetail', 'tourid', 'id');
    }

    public function city()
{
    return $this->belongsTo(City::class, 'town', 'id');
    // or if you renamed column to city_id:
    // return $this->belongsTo(City::class, 'city_id', 'id');
}

public function cityRelation() {
    return $this->belongsTo(City::class, 'town', 'id');
}

public function districtRelation() {
    return $this->belongsTo(District::class, 'district', 'id');
}


// public function districtRelation()
// {
//     return $this->belongsTo(District::class, 'district', 'id');
//     // or if renamed to district_id:
//     // return $this->belongsTo(District::class, 'district_id', 'id');
// }
}
