<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCallingRemark extends Model
{
    protected $table = 'user_calling_remarks';

    protected $fillable = ['user_id', 'call_date', 'calling_remark', 'issue', 'created_by', 'updated_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
