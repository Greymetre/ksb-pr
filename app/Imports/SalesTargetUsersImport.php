<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductDetails;
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
use App\Models\Services;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\SalesTargetUsers;
use App\Models\User;
use Validator;

class SalesTargetUsersImport implements ToCollection, WithValidation, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;

    public function model(array $row)
    {
        return new SalesTargetUsers([
            //
        ]);
    }
    // public function collection(Collection $rows)
    // {
    //     foreach ($rows as $row) {
    //         if (!isset($row['user_id']) || empty($row['user_id'])) {
    //             continue;
    //         }
    //         $user_id = $row['user_id'];
    //         $type = $row['type'];
    //         $branchId = $row['branch_id'];
    //         foreach ($row as $key => $value) {
    //             $key = (string) $key;
    //             if (preg_match('/^(\d{2})(\d{2})$/', $key, $matches)) {
    //                 $monthNumber = $matches[1];
    //                 $year = '20' . $matches[2];
    //                 $carbonDate = Carbon::createFromFormat('Y-m-d', "$year-$monthNumber-01");
    //                 $month = $carbonDate->format('M');
    //             } elseif (is_numeric($key) && $key > 40000) {
    //                 $excelDate = $key - 25569;
    //                 $carbonDate = Carbon::createFromTimestamp($excelDate * 86400);
    //                 $month = $carbonDate->format('M');
    //                 $year = $carbonDate->format('Y');
    //             } else {
    //                 continue;
    //             }
    //             $targetValue = is_numeric($value) ? $value : 0;
    //             $salesTargetUsers = SalesTargetUsers::updateOrCreate([
    //                 'user_id' => $user_id,
    //                 'month' => $month,
    //                 'year' => $year,
    //                 'branch_id' => $branchId
    //             ], [
    //                 'type' => $type,
    //                 'target' => $targetValue
    //             ]);
    //         }
    //     }
    // }

    public function collection(Collection $rows)
    {
        // dd($rows);
        
        foreach ($rows as $row) {

        if (empty($row['user_id'])) continue;

        $user_id  = $row['user_id'];
        $branchId = $row['branch_id'];
        $type     = $row['type'];

        $data = $row->toArray();

        $keys = array_keys($data);
        $count = count($keys);

        for ($i = 0; $i < $count; $i++) {

    $key   = trim((string) $keys[$i]);
    $value = $data[$keys[$i]];

    if (in_array($key, ['user_id', 'branch_id', 'user_name', 'type'])) {
        continue;
    }

    $date = null;

    /*
    |--------------------------------------------------------------------------
    | Case 1 : 0626
    |--------------------------------------------------------------------------
    */
    if (preg_match('/^\d{4}$/', $key)) {

        $monthNo = substr($key, 0, 2);
        $yearNo  = substr($key, 2, 2);

        $date = Carbon::createFromFormat('m y', "$monthNo $yearNo")
            ->startOfMonth();

        $target   = (float) $value;
        $quantity = 0;

        if (isset($data[$key . '_qty'])) {
            $quantity = (float) $data[$key . '_qty'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Case 2 : 0626_qty
    |--------------------------------------------------------------------------
    */
    elseif (preg_match('/^(\d{4})_qty$/', $key, $matches)) {

        $monthYear = $matches[1];

        $monthNo = substr($monthYear, 0, 2);
        $yearNo  = substr($monthYear, 2, 2);

        $date = Carbon::createFromFormat('m y', "$monthNo $yearNo")
            ->startOfMonth();

        $quantity = (float) $value;

        $target = isset($data[$monthYear])
            ? (float) $data[$monthYear]
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Case 3 : Excel serial date
    |--------------------------------------------------------------------------
    */
    elseif (is_numeric($key) && (int)$key > 40000) {

        $date = Carbon::createFromTimestamp(
            ((int)$key - 25569) * 86400
        );

        $target = (float) $value;

        $quantity = isset($data[$key . '_qty'])
            ? (float) $data[$key . '_qty']
            : 0;
    }

    if (!$date) {
        continue;
    }

    $month = $date->format('M');
    $year  = $date->format('Y');

    SalesTargetUsers::updateOrCreate(
        [
            'user_id'   => $user_id,
            'branch_id' => $branchId,
            'month'     => $month,
            'year'      => $year,
        ],
        [
            'type'            => $type,
            'target'          => $target,
            'qunatity_target' => $quantity,
        ]
    );
}
    
    }
    
    }
    public function rules(): array
    {
        $rules = [
            'user_id' => 'required',
            'type' => 'required|in:primary,secondary',
        ];
        return $rules;
    }

    public function customValidationMessages()
    {
        return [
            'user_id.required' => 'The user id is required.',
            'type.required' => 'The type name field is required.',
            'type.in' => 'The type name field either have primary or secondary value.',
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
