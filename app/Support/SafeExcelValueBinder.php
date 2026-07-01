<?php

namespace App\Support;

use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class SafeExcelValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $value = sanitizeForExcel($value);
        }

        return parent::bindValue($cell, $value);
    }
}
