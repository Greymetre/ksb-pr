<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourLog extends Model
{
    use HasFactory;

    protected $table = 'tour_logs';

    protected $fillable = [
        'tour_programme_id',
        'action',
        'status',
        'performed_by',
        'remark'
    ];

    public function tourProgramme()
    {
        return $this->belongsTo(TourProgramme::class, 'tour_programme_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}