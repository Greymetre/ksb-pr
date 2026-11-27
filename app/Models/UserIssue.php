<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIssue extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
    ];

    protected $table = 'user_issues';

    protected $fillable = [
        'user_id', 'issue_date', 'description', 'status', 'source', 'calling_remark_id',
        'created_by', 'solved_by', 'solve_remark', 'solved_at', 'updated_by',
    ];

    protected $casts = [
        'solved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function solvedBy()
    {
        return $this->belongsTo(User::class, 'solved_by');
    }
}
