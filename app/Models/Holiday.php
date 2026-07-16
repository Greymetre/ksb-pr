<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $fillable = [ 'active', 'name','branch','holiday_date','created_by', 'updated_by', 'deleted_at', 'created_at', 'updated_at'];

    public function createdbyname()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id')->select('id','name');
    }

    public function getbranch()
    {
        return $this->belongsTo('App\Models\Branch', 'branch', 'id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_holiday')->withTimestamps();
    }

    public function scopeForBranches($query, $branchIds)
    {
        $branchIds = collect(is_array($branchIds) ? $branchIds : explode(',', (string) $branchIds))
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '' && ctype_digit($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($branchIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($query) use ($branchIds) {
            $query->whereHas('branches', function ($query) use ($branchIds) {
                $query->whereIn('branches.id', $branchIds);
            })->orWhereIn('branch', $branchIds);
        });
    }

    public static function datesForBranches($branchIds): array
    {
        return static::query()
            ->where('active', 'Y')
            ->forBranches($branchIds)
            ->pluck('holiday_date')
            ->flatMap(fn ($dates) => explode(',', (string) $dates))
            ->map(fn ($date) => trim($date))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

}
