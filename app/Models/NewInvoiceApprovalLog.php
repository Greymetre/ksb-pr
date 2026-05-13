<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewInvoiceApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_date',
        'new_invoice_id',
        'created_by',
        'status_type',
        'from_status',
        'to_status',
        'remark',
    ];

    public function invoice()
    {
        return $this->belongsTo(NewInvoice::class, 'new_invoice_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
