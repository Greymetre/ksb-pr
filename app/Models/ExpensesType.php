<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensesType extends Model
{
    use HasFactory;
    protected $table = 'expenses_types';

    protected $fillable = [ 'name', 'rate', 'is_active', 'allowance_type_id', 'created_at', 'updated_at','payroll_id'];

    public function scopeForPayroll($query, $payrollId)
    {
        if (empty($payrollId)) {
            return $query;
        }

        return $query->whereRaw('FIND_IN_SET(?, payroll_id)', [$payrollId]);
    }

    public function payrollIds(): array
    {
        return array_values(array_filter(explode(',', (string) $this->payroll_id), fn ($value) => $value !== ''));
    }
}
