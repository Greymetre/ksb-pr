<x-app-layout>
    @php
        $totalPlan = $dealerTargets->sum('target');
        $totalAchievement = $dealerTargets->sum('achievement');
        $achievementPercentage = $totalPlan > 0 ? round(($totalAchievement / $totalPlan) * 100, 1) : 0;
    @endphp

    <style>
        .dealer-target-page { color: #eef4ff; padding: 20px 24px 42px; }
        .dealer-target-header { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; margin-bottom:28px; }
        .dealer-target-header h1 { color:#f4f7ff; font-size:28px !important; font-weight:700; line-height:1.25; margin:0 0 7px; }
        .dealer-target-header p { color:#91a6d5; font-size:14px !important; line-height:1.5; margin:0; }
        .dealer-target-actions { display:flex; gap:12px; flex-wrap:wrap; justify-content:flex-end; }
        .dealer-target-btn { min-height:42px; padding:0 18px; border:1px solid #294677; border-radius:12px; background:#0a1838; color:#eef4ff; font-size:14px !important; font-weight:600; display:inline-flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; }
        .dealer-target-btn .material-icons { font-size:19px; }
        .dealer-target-btn.primary { border:0; color:#06152e; background:linear-gradient(100deg,#26cce0,#438cf4); }
        .dealer-target-stats { display:grid; grid-template-columns:repeat(3,minmax(220px,1fr)); gap:18px; max-width:920px; margin-bottom:26px; }
        .dealer-target-stat { min-height:125px; padding:22px; border:1px solid #284775; border-radius:16px; background:#0c2148; }
        .dealer-target-stat-label { color:#94a9d8; font-size:13px !important; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
        .dealer-target-stat-value { color:#f3f7ff; font-size:34px !important; line-height:1; font-weight:700; margin-top:18px; }
        .dealer-target-delta { color:#ff5477; font-size:13px !important; font-weight:600; margin-top:14px; }
        .dealer-target-filters { display:grid; grid-template-columns:minmax(280px,1.5fr) repeat(2,minmax(190px,1fr)); gap:14px; max-width:1000px; margin-bottom:24px; }
        .dealer-target-control { height:46px; padding:0 16px; border:1px solid #294878; border-radius:12px; background:#091936; color:#cbd8f4; font-size:14px !important; width:100%; outline:none; }
        .dealer-target-table-card { overflow:hidden; border:1px solid #284775; border-top:3px solid #23cee8; border-radius:19px; background:#0b2046; }
        .dealer-target-table-title { display:flex; align-items:center; gap:15px; padding:22px 28px; border-bottom:1px solid #274371; }
        .dealer-target-table-title .icon-box { width:48px; height:48px; border:1px solid #23cee8; border-radius:13px; display:flex; align-items:center; justify-content:center; color:#23cee8; }
        .dealer-target-table-title h2 { color:#eef4ff; font-size:19px !important; margin:0; }
        .dealer-target-table-title p { color:#8fa4d3; font-size:13px !important; margin:3px 0 0; }
        .dealer-target-table-wrap { overflow-x:auto; }
        .dealer-target-table { width:100%; min-width:1050px; border-collapse:collapse; }
        .dealer-target-table th { padding:16px 20px; text-align:left; color:#91a6d5; font-size:12px !important; letter-spacing:.06em; text-transform:uppercase; border-bottom:1px solid #294675; white-space:nowrap; }
        .dealer-target-table td { padding:17px 20px; color:#b7c7e8; font-size:14px !important; border-bottom:1px solid #223e6b; }
        .dealer-target-empty { padding:42px 20px !important; text-align:center; color:#91a6d5 !important; }
        .dealer-target-alert { padding:13px 17px; margin-bottom:20px; border-radius:10px; font-size:14px; }
        .dealer-target-alert.success { color:#24dfa4; border:1px solid #167d69; background:rgba(20,174,132,.12); }
        .dealer-target-alert.error { color:#ff7690; border:1px solid #933852; background:rgba(218,54,88,.12); }
        .dealer-target-modal { display:none; position:fixed; inset:0; z-index:99999; padding:24px; background:rgba(2,9,25,.78); align-items:center; justify-content:center; }
        .dealer-target-modal.show { display:flex; }
        .dealer-target-modal-dialog { width:100%; max-width:620px; border:1px solid #294a7d; border-radius:18px; background:#0c2148; box-shadow:0 24px 70px rgba(0,0,0,.5); overflow:hidden; }
        .dealer-target-modal-head { display:flex; align-items:center; justify-content:space-between; gap:15px; padding:21px 24px; border-bottom:1px solid #29446f; }
        .dealer-target-modal-head h2 { color:#f4f7ff; font-size:21px !important; margin:0; }
        .dealer-target-modal-close { border:0; background:transparent; color:#91a6d5; padding:2px; cursor:pointer; line-height:1; }
        .dealer-target-modal-close .material-icons { font-size:25px; }
        .dealer-target-modal-body { padding:22px 24px 8px; }
        .dealer-target-field { margin-bottom:18px; }
        .dealer-target-field label { display:block; color:#9fb2dc; font-size:12px !important; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px; }
        .dealer-target-input { width:100%; height:48px; padding:0 15px; color:#eef4ff; font-size:14px !important; border:1px solid #315187; border-radius:11px; background:#091936; outline:none; }
        textarea.dealer-target-input { height:90px; padding-top:13px; resize:vertical; }
        .dealer-target-input:focus { border-color:#26cce0; box-shadow:0 0 0 2px rgba(38,204,224,.12); }
        .dealer-target-modal-footer { display:flex; align-items:center; justify-content:flex-end; gap:12px; padding:17px 24px 22px; }
        .dealer-target-modal-footer .dealer-target-btn { min-width:110px; }
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
        @if(session('success'))
            <div class="dealer-target-alert success">{{ session('success') }}</div>
        @endif
        @if(!empty($setupRequired) || session('setup_error'))
            <div class="dealer-target-alert error">{{ session('setup_error', 'Dealer targets database setup is pending. Please run the database migration.') }}</div>
        @endif
        @if($errors->any())
            <div class="dealer-target-alert error">{{ $errors->first() }}</div>
        @endif
        <div class="dealer-target-header">
            <div>
                <h1>New Dealer Appointment: Target vs Achievement</h1>
                <p>Track monthly new dealer appointment targets against achievement, by employee</p>
            </div>
            <div class="dealer-target-actions">
                <button type="button" class="dealer-target-btn"><i class="material-icons">upload</i> Import</button>
                <button type="button" class="dealer-target-btn"><i class="material-icons">download</i> Export</button>
                <button type="button" class="dealer-target-btn primary" id="openNewDealerTarget"><i class="material-icons">add</i> New Target</button>
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
                @if($totalPlan > 0 && $achievementPercentage < 100)
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
                <div><h2>Appointment Records</h2><p>{{ $dealerTargets->count() }} records</p></div>
            </div>
            <div class="dealer-target-table-wrap">
                <table class="dealer-target-table">
                    <thead><tr><th>Emp Code</th><th>Emp Name</th><th>Zone</th><th>Month</th><th>Plan Nos</th><th>Achievement Nos</th><th>Achievement %</th><th>Note</th></tr></thead>
                    <tbody>
                        @forelse($dealerTargets as $target)
                            @php $percentage = $target->target > 0 ? round(($target->achievement / $target->target) * 100, 1) : 0; @endphp
                            <tr>
                                <td>{{ $target->user->employee_codes ?? '—' }}</td>
                                <td>{{ $target->user->name ?: trim(($target->user->first_name ?? '').' '.($target->user->last_name ?? '')) }}</td>
                                <td>{{ optional($target->user->getdivision)->division_name ?? '—' }}</td>
                                <td>{{ $target->target_month->format('M Y') }}</td>
                                <td class="number">{{ $target->target }}</td><td class="number">{{ $target->achievement }}</td>
                                <td><span class="achievement-pill {{ $percentage >= 100 ? 'good' : ($percentage >= 70 ? 'warning' : 'low') }}">{{ $percentage }}%</span></td>
                                <td>{{ $target->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="dealer-target-empty">No dealer target records available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dealer-target-modal {{ $errors->any() ? 'show' : '' }}" id="newDealerTargetModal" aria-hidden="{{ $errors->any() ? 'false' : 'true' }}">
        <div class="dealer-target-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="newDealerTargetTitle">
            <form method="POST" action="{{ route('new-dealer-targets.store') }}">
                @csrf
                <div class="dealer-target-modal-head">
                    <h2 id="newDealerTargetTitle">New Dealer Appointment Target</h2>
                    <button type="button" class="dealer-target-modal-close closeNewDealerTarget" aria-label="Close"><i class="material-icons">close</i></button>
                </div>
                <div class="dealer-target-modal-body">
                    <div class="dealer-target-field">
                        <label for="dealerTargetUser">Employee</label>
                        <select class="dealer-target-input" id="dealerTargetUser" name="user_id" required>
                            <option value="">Select employee...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string) old('user_id') === (string) $user->id ? 'selected' : '' }}>
                                    {{ $user->employee_codes ? $user->employee_codes.' - ' : '' }}{{ $user->name ?: trim($user->first_name.' '.$user->last_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dealer-target-field">
                        <label for="dealerTargetMonth">Month</label>
                        <input class="dealer-target-input" id="dealerTargetMonth" name="target_month" type="month" value="{{ old('target_month', now()->format('Y-m')) }}" required>
                    </div>
                    <div class="dealer-target-field">
                        <label for="dealerTargetNumber">New Dealer Appointment Target (Nos.)</label>
                        <input class="dealer-target-input" id="dealerTargetNumber" name="target" type="number" min="1" step="1" value="{{ old('target') }}" placeholder="e.g. 12" required>
                    </div>
                    <div class="dealer-target-field">
                        <label for="dealerTargetNote">Note</label>
                        <textarea class="dealer-target-input" id="dealerTargetNote" name="note" maxlength="500" placeholder="Optional note">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="dealer-target-modal-footer">
                    <button type="button" class="dealer-target-btn closeNewDealerTarget">Cancel</button>
                    <button type="submit" class="dealer-target-btn primary"><i class="material-icons">save</i> Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('newDealerTargetModal');
            const openButton = document.getElementById('openNewDealerTarget');
            const closeButtons = document.querySelectorAll('.closeNewDealerTarget');

            function setModal(open) {
                modal.classList.toggle('show', open);
                modal.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.style.overflow = open ? 'hidden' : '';
            }

            openButton.addEventListener('click', function () { setModal(true); });
            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () { setModal(false); });
            });
            modal.addEventListener('click', function (event) {
                if (event.target === modal) setModal(false);
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') setModal(false);
            });
        });
    </script>
</x-app-layout>
