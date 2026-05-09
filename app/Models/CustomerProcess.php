<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProcess extends Model
{
    use HasFactory;

    protected $table = 'customer_processes';

    protected $fillable = [
        'process_name',
        'description',
        'created_by',
    ];

    public function steps()
    {
        return $this->hasMany(CustomerProcessStep::class)->orderBy('sort_order', 'asc');
    }


    public function creatbyname()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->select('id', 'name');
    }
}
