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

class SalesTargetUsersImport implements ToCollection
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
        $rows = $rows->map(function ($row) {
            return array_values($row instanceof Collection ? $row->toArray() : (array) $row);
        })->values();

        if ($rows->isEmpty()) {
            return;
        }

        $firstHeading = strtolower(trim((string) ($rows[0][0] ?? '')));

        if (in_array($firstHeading, ['emp code', 'employee code'], true)) {
            $this->importExportedReport($rows);
            return;
        }

        $this->importFlatTemplate($rows);
    }

    private function importExportedReport(Collection $rows): void
    {
        $headings = $rows[0];
        $monthColumns = [];

        for ($column = 8; $column < count($headings); $column += 3) {
            $heading = trim((string) ($headings[$column] ?? ''));
            if (strtolower($heading) === 'total' || !preg_match('/^([A-Za-z]+)\/(\d{4})$/', $heading, $matches)) {
                break;
            }

            $monthColumns[$column] = [
                'month' => Carbon::parse('1 ' . $matches[1])->format('M'),
                'year' => $matches[2],
            ];
        }

        foreach ($rows->slice(2) as $row) {
            $employeeCode = trim((string) ($row[0] ?? ''));
            if ($employeeCode === '') {
                continue;
            }

            $userId = User::where('employee_codes', $employeeCode)->value('id');
            if (!$userId) {
                continue;
            }

            $branchId = $row[4] ?? null;
            $type = strtolower(trim((string) ($row[7] ?? '')));
            if (!$branchId || !in_array($type, ['primary', 'secondary'], true)) {
                continue;
            }

            foreach ($monthColumns as $column => $period) {
                $target = $row[$column] ?? null;
                if ($target === null || $target === '' || !is_numeric($target)) {
                    continue;
                }

                $this->saveTarget($userId, $branchId, $type, $period['month'], $period['year'], $target);
            }
        }
    }

    private function importFlatTemplate(Collection $rows): void
    {
        $headings = array_map(function ($heading) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', (string) $heading), '_'));
        }, $rows[0]);

        $userColumn = array_search('user_id', $headings, true);
        $branchColumn = array_search('branch_id', $headings, true);
        $typeColumn = array_search('type', $headings, true);

        if ($userColumn === false || $branchColumn === false || $typeColumn === false) {
            return;
        }

        foreach ($rows->slice(1) as $row) {
            $userId = $row[$userColumn] ?? null;
            $branchId = $row[$branchColumn] ?? null;
            $type = strtolower(trim((string) ($row[$typeColumn] ?? '')));

            if (!$userId || !$branchId || !in_array($type, ['primary', 'secondary'], true)) {
                continue;
            }

            foreach ($headings as $column => $heading) {
                if (!preg_match('/^(\d{2})_(\d{2}|\d{4})$/', $heading, $matches)) {
                    continue;
                }

                $target = $row[$column] ?? null;
                if ($target === null || $target === '' || !is_numeric($target)) {
                    continue;
                }

                $year = strlen($matches[2]) === 2 ? '20' . $matches[2] : $matches[2];
                $date = Carbon::createFromDate($year, (int) $matches[1], 1);
                $this->saveTarget($userId, $branchId, $type, $date->format('M'), $date->format('Y'), $target);
            }
        }
    }

    private function saveTarget($userId, $branchId, string $type, string $month, string $year, $target): void
    {
        SalesTargetUsers::updateOrCreate(
            [
                'user_id' => $userId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            [
                'type' => $type,
                'target' => (float) $target,
            ]
        );
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
