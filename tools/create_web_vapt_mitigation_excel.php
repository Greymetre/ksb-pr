<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$source = '/Users/apple/Downloads/FieldConnect VAPT Activity Final Sheet 1.xlsx';
$outputDir = __DIR__ . '/../public/downloads';
$output = $outputDir . '/FieldConnect_Web_Application_VAPT_Mitigation.xlsx';

if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$spreadsheet = IOFactory::load($source);

for ($index = $spreadsheet->getSheetCount() - 1; $index >= 0; $index--) {
    if ($spreadsheet->getSheet($index)->getTitle() !== 'Web Application') {
        $spreadsheet->removeSheetByIndex($index);
    }
}

$sheet = $spreadsheet->getSheetByName('Web Application');
$spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($sheet));

$mitigationRow = 9;
$sheet->setCellValue("A{$mitigationRow}", 'Mitigation Steps');

$mitigations = [
    'B' => "Implemented a global SecurityHeaders middleware to add Strict-Transport-Security, X-Frame-Options, Content-Security-Policy, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, and legacy X-XSS-Protection headers. Remove server technology disclosure headers and verify using securityheaders.com after deployment.",
    'C' => "Upgrade vulnerable frontend libraries to supported versions, including jQuery, jQuery UI, DataTables, bootstrap-select, jquery-validation, and moment.js. Remove unused local vulnerable copies where possible, pin dependency versions, and periodically run dependency/CVE checks before release.",
    'D' => "Centralize exception handling so production users receive a generic error message while detailed stack traces stay only in server logs. Keep APP_DEBUG=false in production and validate request parameters before controller/database execution.",
    'E' => "Restrict export endpoints with backend authorization checks and ownership/role scoping. Export only the records and columns the logged-in user is permitted to view, and avoid exposing hidden/internal fields in generated files.",
    'F' => "Remove password/password_string and other secret fields from API responses using model hidden attributes and response sanitization middleware. Store only hashed passwords and never return credential values in JSON or export responses.",
    'G' => "Sanitize request input and encode output in Blade views. Use Laravel escaping by default, avoid raw HTML rendering for user-controlled values, validate input fields, and enforce CSP to reduce script execution impact.",
    'H' => "Sanitize all Excel/CSV export cell values before writing files. Prefix dangerous formula-leading characters (=, +, -, @, and double quote after leading whitespace) with an apostrophe and force exported values to string where needed.",
    'I' => "Enforce Add New Lead permissions on the backend using route/controller authorization checks. Do not trust hidden form fields or user_id values from the client; derive permitted user scope from the authenticated session.",
    'J' => "Validate lead ownership and role permissions before conversion. The backend must confirm the authenticated user is authorized to convert the specific lead, regardless of intercepted or modified request parameters.",
    'K' => "Validate delete permissions and lead ownership on the server before deletion. Block low-privilege users from deleting higher-privilege or unrelated users' leads and log denied attempts for audit review.",
    'L' => "Protect customer Active/Inactive changes with backend role and ownership checks. Ignore tampered customer/user identifiers from the request unless the authenticated role is explicitly allowed to manage that customer.",
];

foreach ($mitigations as $column => $text) {
    $sheet->setCellValue("{$column}{$mitigationRow}", $text);
}

$lastColumn = 'L';
$sheet->getStyle("A{$mitigationRow}:{$lastColumn}{$mitigationRow}")->applyFromArray([
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9EAF7'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '808080'],
        ],
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true,
    ],
]);

$sheet->getRowDimension($mitigationRow)->setRowHeight(150);

for ($col = 'A'; $col <= $lastColumn; $col++) {
    $sheet->getStyle("{$col}1:{$col}{$mitigationRow}")
        ->getAlignment()
        ->setWrapText(true)
        ->setVertical(Alignment::VERTICAL_TOP);
}

$writer = new Xlsx($spreadsheet);
$writer->save($output);

echo $output . PHP_EOL;
