<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Tour Expenses Report</title>
<style>
@page{margin:15px}body{margin:0;font-family:DejaVu Sans,sans-serif;color:#111;font-size:7px}.report{page-break-after:always}.report:last-child{page-break-after:auto}table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{border:1px solid #111;padding:4px 3px;vertical-align:middle;word-wrap:break-word}.company{position:relative;height:62px;background:#08aaa4;text-align:center}.company img{position:absolute;top:6px;left:9px;width:48px;max-height:48px}.company h1{margin:0;padding-top:8px;font-size:17px}.company p{margin:8px 0 0;font-size:8px}.title{padding:6px;text-align:center;font-size:13px;font-style:italic}.meta td{height:18px;font-size:8px}.data th{height:34px;background:#e7e5d5;text-align:center;font-size:6.5px}.data td{height:19px}.center{text-align:center}.right{text-align:right}.grand td{height:22px;background:#eee;font-weight:bold}.footer{margin-top:11px}.footer td{height:20px;font-size:8px}.footer .remarks{height:67px;vertical-align:top}.muted{color:#555;font-size:6px;text-align:right;margin-top:4px;font-style:italic}.empty{padding:20px;text-align:center}
</style></head><body>
@php $groupedReports = $expenses->groupBy('user_id'); @endphp
@forelse($groupedReports as $userId => $userExpenses)
@php
$user = $userExpenses->first()->users;
$dailyExpenses = $userExpenses->groupBy(fn($expense) => \Carbon\Carbon::parse($expense->date)->toDateString());
$typeTotals = [];
foreach ($expenseTypes as $type) $typeTotals[$type->id] = 0;
$grandTotal = 0;
@endphp
<div class="report">
<div class="company"><img src="{{ public_path('assets/img/duke_logo.png') }}" alt="Duke"><h1>Duke Pipes Pvt. Ltd.</h1><p>Survey No. 365/1, At &amp; Po - Chadotar, Gadh Road, Ta - Palanpur, Dist. B.K. - 385001</p></div>
<table><tr><th colspan="6" class="title">TOUR EXPENSES REPORT</th></tr></table>
<table class="meta">
<tr><td colspan="3"><strong>Name:</strong> {{ $user->name ?? '-' }} (Emp Code: {{ $user->employee_codes ?? '-' }})</td><td colspan="2"><strong>Designation:</strong> {{ optional($user->getdesignation)->designation_name ?? '-' }}</td><td><strong>Department:</strong> {{ optional($user->getdepartment)->name ?? '-' }}</td></tr>
<tr><td colspan="3"><strong>Team:</strong> {{ optional($user->getdivision)->division_name ?? optional($user->getbranch)->branch_name ?? '-' }}</td><td colspan="2"><strong>Submission Date:</strong> {{ $submissionDate->format('d-m-Y') }}</td><td><strong>Period:</strong> {{ $startDate->format('d-m-Y') }} to {{ $endDate->format('d-m-Y') }}</td></tr>
</table>
<table class="data"><thead><tr>
<th style="width:6%">Travel Date</th><th style="width:11%">From Place</th><th style="width:11%">To Place</th><th style="width:6%">Starting Time</th><th style="width:6%">End Time</th><th style="width:5%">Night Halt</th>
@foreach($expenseTypes as $type)<th>{{ $type->name }}</th>@endforeach
<th style="width:7%">Total</th><th style="width:7%">Status</th>
</tr></thead><tbody>
@foreach($dailyExpenses as $date => $dayExpenses)
@php
$attendance = $attendanceByUserDate->get($userId . '|' . $date);
$dayTotal = 0;
$dayStatuses = $dayExpenses->pluck('checker_status')->map(fn($status) => (string)$status)->unique();
$dayStatus = $dayStatuses->count() === 1 ? ($statusLabels[$dayStatuses->first()] ?? 'Pending') : 'Mixed';
@endphp
<tr>
<td class="center">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</td>
<td>{{ $attendance && $attendance->punchin_address ? $attendance->punchin_address : '-' }}</td><td>{{ $attendance && $attendance->punchout_address ? $attendance->punchout_address : '-' }}</td>
<td class="center">{{ $attendance && $attendance->punchin_time ? date('h:i A', strtotime($attendance->punchin_time)) : '-' }}</td><td class="center">{{ $attendance && $attendance->punchout_time ? date('h:i A', strtotime($attendance->punchout_time)) : '-' }}</td><td class="center">-</td>
@foreach($expenseTypes as $type)
@php
$amount = $dayExpenses->where('expenses_type', $type->id)->sum(fn($expense) => $expense->approve_amount !== null ? (float)$expense->approve_amount : (float)$expense->claim_amount);
$typeTotals[$type->id] += $amount; $dayTotal += $amount;
@endphp
<td class="right">{{ $amount > 0 ? number_format($amount, 2) : '-' }}</td>
@endforeach
@php $grandTotal += $dayTotal; @endphp
<td class="right"><strong>{{ number_format($dayTotal, 2) }}</strong></td><td class="center">{{ $dayStatus }}</td>
</tr>
@endforeach
<tr class="grand"><td colspan="6" class="center">GRAND TOTAL</td>@foreach($expenseTypes as $type)<td class="right">{{ $typeTotals[$type->id] > 0 ? number_format($typeTotals[$type->id], 2) : '-' }}</td>@endforeach<td class="right">{{ number_format($grandTotal, 2) }}</td><td></td></tr>
</tbody></table>
<table class="footer"><tr><td rowspan="3" colspan="3" class="remarks"><strong>Auditor Remarks:</strong><br><br>{{ $userExpenses->count() }} expense entries included in this CRM report.</td><td colspan="2"><strong>Prepared By:</strong>&nbsp;&nbsp; {{ $user->name ?? '-' }}</td><td></td></tr><tr><td colspan="2"><strong>Checked By:</strong>&nbsp;&nbsp; -</td><td><strong>On Date:</strong>&nbsp;&nbsp; -</td></tr><tr><td colspan="2"><strong>Sanctioned By:</strong>&nbsp;&nbsp; -</td><td><strong>On Date:</strong>&nbsp;&nbsp; -</td></tr></table>
<div class="muted">Generated from FieldKonnect CRM expense data</div></div>
@empty
<div class="report"><div class="company"><h1>Duke Pipes Pvt. Ltd.</h1></div><table><tr><th class="title">TOUR EXPENSES REPORT</th></tr><tr><td class="empty">No expenses found for the selected employee and date range.</td></tr></table></div>
@endforelse
</body></html>
