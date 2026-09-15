<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintOfficeAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id', 'material_provided', 'quantity_provided', 'service_engineer_provided', 'visit_report_path',
        'replacement', 'replacement_quantity', 'corrective_action', 'preventive_action', 'points_discussed',
        'customer_care_name', 'department_head_name', 'manager_name', 'final_decision', 'updated_by',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }
}
