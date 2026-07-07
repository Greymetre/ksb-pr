<?php

namespace App\Imports;

use App\Models\ServiceChargeCategories;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Facades\DB;
use Log;

use Illuminate\Support\Facades\Auth;

class ServiceProductCategoryImport implements ToCollection, WithValidation, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable, SkipsFailures;


    public function model(array $row)
    {
        return new ServiceChargeCategories([]);
    }

    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {
            $divisionId = $row['division_id'] ?? $row['zone_id'] ?? null;

            if (isset($row['id']) && !empty($row['id'])) {
                $ServiceChargeCategories = ServiceChargeCategories::find($row['id']);
                $ServiceChargeCategories->active = 'Y';
                $ServiceChargeCategories->category_name = isset($row['category_name']) ? $row['category_name'] : '';
                $ServiceChargeCategories->division_id = $divisionId;
                $ServiceChargeCategories->created_by = Auth::user()->id;
                $ServiceChargeCategories->updated_at = getcurentDateTime();
                $ServiceChargeCategories->save();
            } else {
                ServiceChargeCategories::updateOrCreate([
                    'category_name' => isset($row['category_name']) ? $row['category_name'] : '',
                    'division_id' => $divisionId
                ], [
                    'active' => 'Y',
                    'category_name' => isset($row['category_name']) ? $row['category_name'] : '',
                    'division_id' => $divisionId,
                    'created_by' => Auth::user()->id,
                    'created_at' => getcurentDateTime(),
                    'updated_at' => getcurentDateTime()
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'category_name' => 'required',
            'division_id' => 'required_without:zone_id',
            'zone_id' => 'required_without:division_id',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function onFailure(Failure ...$failures)
    {
        Log::stack(['import-failure-logs'])->info(json_encode($failures));
    }
}
