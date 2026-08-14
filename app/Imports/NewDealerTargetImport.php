<?php

namespace App\Imports;

use App\Models\NewDealerTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class NewDealerTargetImport implements ToCollection, WithHeadingRow
{
    public int $added = 0;
    public int $updated = 0;
    public int $unchanged = 0;
    public int $skipped = 0;

    private ?int $createdBy;

    public function __construct(?int $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function collection(Collection $rows)
    {
        $usersByCode = User::query()
            ->whereNotNull('employee_codes')
            ->get(['id', 'employee_codes'])
            ->keyBy(function (User $user) {
                return strtolower(trim((string) $user->employee_codes));
            });

        foreach ($rows as $row) {
            $employeeCode = trim((string) ($row['emp_code'] ?? ''));
            $targetValue = $row['new_dealer_plan_nos'] ?? null;
            $achievementValue = $row['achievement_nos'] ?? null;
            $monthValue = $row['month'] ?? null;

            if ($employeeCode === '' || ! is_numeric($targetValue) || (int) $targetValue < 1 || empty($monthValue)) {
                $this->skipped++;
                continue;
            }

            $hasAchievement = $achievementValue !== null && trim((string) $achievementValue) !== '';
            if ($hasAchievement && (! is_numeric($achievementValue) || (int) $achievementValue < 0)) {
                $this->skipped++;
                continue;
            }

            $user = $usersByCode->get(strtolower($employeeCode));
            $month = $this->parseMonth($monthValue);

            if (! $user || ! $month) {
                $this->skipped++;
                continue;
            }

            $note = isset($row['note']) ? trim((string) $row['note']) : null;
            $note = $note === '' ? null : $note;
            $targetNumber = (int) $targetValue;

            $target = NewDealerTarget::query()->firstOrNew([
                'user_id' => $user->id,
                'target_month' => $month->toDateString(),
            ]);

            if (! $target->exists) {
                $target->fill([
                    'target' => $targetNumber,
                    'achievement' => $hasAchievement ? (int) $achievementValue : null,
                    'note' => $note,
                    'created_by' => $this->createdBy,
                ])->save();
                $this->added++;
                continue;
            }

            $achievementChanged = $hasAchievement
                && ($target->achievement === null || (int) $target->achievement !== (int) $achievementValue);
            if ((int) $target->target === $targetNumber
                && ! $achievementChanged
                && $this->normaliseNote($target->note) === $this->normaliseNote($note)) {
                $this->unchanged++;
                continue;
            }

            $updates = [
                'target' => $targetNumber,
                'note' => $note,
                'created_by' => $this->createdBy,
            ];
            if ($hasAchievement) {
                $updates['achievement'] = (int) $achievementValue;
            }

            $target->update($updates);
            $this->updated++;
        }
    }

    private function parseMonth($value): ?Carbon
    {
        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->startOfMonth();
            }

            $value = trim((string) $value);
            foreach (['M Y', 'F Y', 'Y-m', 'm/Y', 'm-Y'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->startOfMonth();
                } catch (\Throwable $exception) {
                    // Try the next supported month format.
                }
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return null;
    }

    private function normaliseNote($note): string
    {
        return trim((string) $note);
    }
}
