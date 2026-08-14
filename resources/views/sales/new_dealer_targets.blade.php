<x-app-layout>
    @php
        $totalPlan = $dealerTargets->sum('plan');
        $totalAchievement = $dealerTargets->sum('achievement');
        $achievementPercentage = $totalPlan > 0 ? round(($totalAchievement / $totalPlan) * 100, 1) : 0;
    @endphp

    <style>
        .dealer-target-page { color: #eef4ff; padding: 24px 28px 50px; }
        .dealer-target-header { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; margin-bottom:28px; }
        .dealer-target-header h1 { color:#f4f7ff; font-size:36px; font-weight:700; line-height:1.2; margin:0 0 8px; }
        .dealer-target-header p { color:#91a6d5; font-size:17px; margin:0; }
        .dealer-target-actions { display:flex; gap:12px; flex-wrap:wrap; justify-content:flex-end; }
        .dealer-target-btn { min-height:48px; padding:0 24px; border:1px solid #294677; border-radius:14px; background:#0a1838; color:#eef4ff; font-size:16px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; gap:9px; cursor:pointer; }
        .dealer-target-btn.primary { border:0; color:#06152e; background:linear-gradient(100deg,#26cce0,#438cf4); }
        .dealer-target-stats { display:grid; grid-template-columns:repeat(3,minmax(220px,1fr)); gap:18px; max-width:920px; margin-bottom:26px; }
        .dealer-target-stat { min-height:155px; padding:27px; border:1px solid #284775; border-radius:18px; background:#0c2148; }
        .dealer-target-stat-label { color:#94a9d8; font-size:15px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
        .dealer-target-stat-value { color:#f3f7ff; font-size:42px; line-height:1; font-weight:700; margin-top:23px; }
        .dealer-target-delta { color:#ff5477; font-size:15px; font-weight:600; margin-top:18px; }
        .dealer-target-filters { display:grid; grid-template-columns:minmax(280px,1.5fr) repeat(2,minmax(190px,1fr)); gap:14px; max-width:1000px; margin-bottom:24px; }
        .dealer-target-control { height:52px; padding:0 18px; border:1px solid #294878; border-radius:13px; background:#091936; color:#cbd8f4; font-size:16px; width:100%; outline:none; }
        .dealer-target-table-card { overflow:hidden; border:1px solid #284775; border-top:3px solid #23cee8; border-radius:19px; background:#0b2046; }
        .dealer-target-table-title { display:flex; align-items:center; gap:15px; padding:22px 28px; border-bottom:1px solid #274371; }
        .dealer-target-table-title .icon-box { width:48px; height:48px; border:1px solid #23cee8; border-radius:13px; display:flex; align-items:center; justify-content:center; color:#23cee8; }
        .dealer-target-table-title h2 { color:#eef4ff; font-size:22px; margin:0; }
        .dealer-target-table-title p { color:#8fa4d3; margin:3px 0 0; }
        .dealer-target-table-wrap { overflow-x:auto; }
        .dealer-target-table { width:100%; min-width:1050px; border-collapse:collapse; }
        .dealer-target-table th { padding:19px 22px; text-align:left; color:#91a6d5; font-size:14px; letter-spacing:.06em; text-transform:uppercase; border-bottom:1px solid #294675; white-space:nowrap; }
        .dealer-target-table td { padding:20px 22px; color:#b7c7e8; font-size:16px; border-bottom:1px solid #223e6b; }
        .dealer-target-table tr:last-child td { border-bottom:0; }
        .dealer-target-table .number { color:#f1f5ff; font-weight:700; }
        .achievement-pill { display:inline-flex; min-width:76px; justify-content:center; padding:7px 12px; border-radius:999px; font-weight:700; }
        .achievement-pill.good { color:#16e4a3; border:1px solid #158f78; background:rgba(17,190,142,.12); }
        .achievement-pill.warning { color:#ffbb3e; border:1px solid #9f7230; background:rgba(255,173,32,.11); }
        .achievement-pill.low { color:#ff5978; border:1px solid #a43b5d; background:rgba(255,67,105,.12); }
        @media (max-width: 991px) {
            .dealer-target-header { flex-direction:column; }
            .dealer-target-actions { justify-content:flex-start; }
            .dealer-target-stats { grid-template-columns:1fr; max-width:none; }
            .dealer-target-filters { grid-template-columns:1fr; max-width:none; }
        }
    </style>

    <div class="dealer-target-page">
        <div class="dealer-target-header">
            <div>
                <h1>New Dealer Appointment: Target vs Achievement</h1>
                <p>Track monthly new dealer appointment targets against achievement, by employee</p>
            </div>
            <div class="dealer-target-actions">
                <button type="button" class="dealer-target-btn"><i class="material-icons">upload</i> Import</button>
                <button type="button" class="dealer-target-btn"><i class="material-icons">download</i> Export</button>
                <button type="button" class="dealer-target-btn primary"><i class="material-icons">add</i> New Target</button>
            </div>
        </div>

        <div class="dealer-target-stats">
            <div class="dealer-target-stat">
                <div class="dealer-target-stat-label">Total Plan (New Dealers)</div>
                <div class="dealer-target-stat-value">{{ $totalPlan }}</div>
            </div>
            <div class="dealer-target-stat">
                <div class="dealer-target-stat-label">Total Achievement</div>
                <div class="dealer-target-stat-value">{{ $totalAchievement }}</div>
            </div>
            <div class="dealer-target-stat">
                <div class="dealer-target-stat-label">Overall Achievement %</div>
                <div class="dealer-target-stat-value">{{ $achievementPercentage }}%</div>
                @if($achievementPercentage < 100)
                    <div class="dealer-target-delta">{{ round(100 - $achievementPercentage, 1) }}% below target</div>
                @endif
            </div>
        </div>

        <div class="dealer-target-filters">
            <input class="dealer-target-control" type="search" placeholder="Search by employee name or code">
            <select class="dealer-target-control"><option>All Zones</option><option>North</option><option>South</option><option>East</option><option>West</option><option>Central</option></select>
            <select class="dealer-target-control"><option>All Months</option><option>Jun 2026</option><option>Jul 2026</option><option>Aug 2026</option></select>
        </div>

        <div class="dealer-target-table-card">
            <div class="dealer-target-table-title">
                <div class="icon-box"><i class="material-icons">storefront</i></div>
                <div><h2>Appointment Records</h2><p>{{ $dealerTargets->count() }} dummy records</p></div>
            </div>
            <div class="dealer-target-table-wrap">
                <table class="dealer-target-table">
                    <thead><tr><th>Emp Code</th><th>Emp Name</th><th>Zone</th><th>Month</th><th>Plan Nos</th><th>Achievement Nos</th><th>Achievement %</th><th>Note</th></tr></thead>
                    <tbody>
                        @foreach($dealerTargets as $target)
                            @php $percentage = $target['plan'] > 0 ? round(($target['achievement'] / $target['plan']) * 100, 1) : 0; @endphp
                            <tr>
                                <td>{{ $target['employee_code'] }}</td><td>{{ $target['employee_name'] }}</td><td>{{ $target['zone'] }}</td><td>{{ $target['month'] }}</td>
                                <td class="number">{{ $target['plan'] }}</td><td class="number">{{ $target['achievement'] }}</td>
                                <td><span class="achievement-pill {{ $percentage >= 100 ? 'good' : ($percentage >= 70 ? 'warning' : 'low') }}">{{ $percentage }}%</span></td>
                                <td>{{ $target['note'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
