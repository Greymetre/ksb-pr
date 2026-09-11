<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        @page { margin: 18px; }
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 9px; }
        h2 { margin: 0 0 5px; text-align: center; font-size: 17px; }
        .period { margin-bottom: 12px; text-align: center; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px 4px; border: 1px solid #bbb; vertical-align: top; }
        th { background: #e9eef8; font-weight: bold; }
        td.number { text-align: right; }
        .empty { padding: 22px; text-align: center; }
        .total { font-weight: bold; background: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Expense Report</h2>
    <div class="period">
        {{ $selectedUser ? $selectedUser->name . ' | ' : 'All Employees | ' }}
        {{ $startDate->format('d-m-Y') }} to {{ $endDate->format('d-m-Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Date</th><th>Emp. Code</th><th>Employee</th><th>Designation</th>
                <th>Branch</th><th>Expense Type</th><th>Rate</th><th>Claim</th><th>Approved</th><th>Status</th><th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td>{{ optional($expense->date ? \Carbon\Carbon::parse($expense->date) : null)->format('d-m-Y') }}</td>
                <td>{{ $expense->users->employee_codes ?? '' }}</td>
                <td>{{ $expense->users->name ?? '' }}</td>
                <td>{{ optional(optional($expense->users)->getdesignation)->designation_name ?? '' }}</td>
                <td>{{ optional(optional($expense->users)->getbranch)->branch_name ?? '' }}</td>
                <td>{{ $expense->expense_type->name ?? '' }}</td>
                <td class="number">{{ number_format((float) ($expense->rate ?? optional($expense->expense_type)->rate ?? 0), 2) }}</td>
                <td class="number">{{ number_format((float) $expense->claim_amount, 2) }}</td>
                <td class="number">{{ number_format((float) $expense->approve_amount, 2) }}</td>
                <td>{{ $statusLabels[(string) $expense->checker_status] ?? 'Pending' }}</td>
                <td>{{ $expense->note }}</td>
            </tr>
            @empty
            <tr><td colspan="12" class="empty">No expenses found for the selected filters.</td></tr>
            @endforelse
            @if($expenses->isNotEmpty())
            <tr class="total">
                <td colspan="8" class="number">Total</td>
                <td class="number">{{ number_format((float) $expenses->sum('claim_amount'), 2) }}</td>
                <td class="number">{{ number_format((float) $expenses->sum('approve_amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
