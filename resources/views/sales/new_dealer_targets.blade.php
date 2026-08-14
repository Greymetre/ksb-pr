<x-app-layout>
    @php
        $totalPlan = $dealerTargets->sum('target');
        $totalAchievement = $dealerTargets->sum('achievement');
        $achievementPercentage = $totalPlan > 0 ? round(($totalAchievement / $totalPlan) * 100, 1) : 0;
        $selectedZone = $zones->firstWhere('id', (int) request('zone_id'));
        $selectedFilterMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) request('month')) ? request('month') : null;
    @endphp

    <style>
        .dealer-target-page { color:#eef4ff; padding:20px 24px 42px; font-family:inherit; }
        .dealer-target-header { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; margin-bottom:24px; }
        .dealer-target-header > div:first-child { flex:1 1 auto; min-width:0; }
        .dealer-target-breadcrumb { display:flex; align-items:center; gap:11px; margin:0 0 11px; color:#7187bd; font-size:11px !important; font-weight:700; letter-spacing:.24em; text-transform:uppercase; }
        .dealer-target-breadcrumb .current { color:#438cf4; }
        .dealer-target-title-row { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
        .dealer-target-header h1 { color:#f4f7ff; font-size:26px !important; font-weight:700; line-height:1.25; margin:0 0 7px; }
        .dealer-target-title-row h1 { margin-bottom:0; }
        .dealer-target-record-count { min-height:36px; padding:0 18px; display:inline-flex; align-items:center; justify-content:center; color:#22d2ea; border:1px solid #1681a6; border-radius:999px; background:rgba(21,126,169,.18); font-size:14px !important; font-weight:700; white-space:nowrap; }
        .dealer-target-header p { color:#91a6d5; font-size:14px !important; line-height:1.5; margin:0; }
        .dealer-target-title-row + p { margin-top:8px; }
        .dealer-target-actions { flex:0 0 auto; display:flex; gap:10px; flex-wrap:nowrap; justify-content:flex-end; padding-top:3px; }
        .dealer-target-btn { min-height:42px; padding:0 18px; border:1px solid #294677; border-radius:12px; background:#0a1838; color:#eef4ff; font-size:14px !important; font-weight:600; display:inline-flex; align-items:center; justify-content:center; gap:7px; cursor:pointer; }
        .dealer-target-btn .material-icons { font-size:19px; }
        .dealer-target-btn.primary { border:0; color:#06152e; background:linear-gradient(100deg,#26cce0,#438cf4); }
        .dealer-target-stats { display:grid; grid-template-columns:repeat(3,minmax(180px,1fr)); gap:9px; max-width:690px; margin-bottom:20px; }
        .dealer-target-stat { position:relative; min-height:68px; padding:10px 12px; display:flex; align-items:center; overflow:hidden; border:1px solid rgba(34,211,238,.25); border-radius:14px; background:linear-gradient(135deg,rgba(34,211,238,.1),rgba(8,20,50,.72)); box-shadow:inset 0 1px 0 rgba(255,255,255,.025); transition:transform .2s ease,border-color .2s ease; }
        .dealer-target-stat:hover { transform:translateY(-1px); border-color:rgba(34,211,238,.58); }
        .dealer-target-stat-icon { width:36px; height:36px; margin-right:10px; display:flex; align-items:center; justify-content:center; flex:0 0 36px; color:#24cde8; border:1px solid rgba(34,211,238,.3); border-radius:11px; background:rgba(34,211,238,.1); }
        .dealer-target-stat-icon .material-icons { color:inherit; font-size:18px !important; }
        .dealer-target-stat-content { min-width:0; display:flex; flex-direction:column; }
        .dealer-target-stat-label { color:#94a9d8; font-size:9px !important; font-weight:600; letter-spacing:.055em; line-height:1.2; text-transform:uppercase; white-space:nowrap; }
        .dealer-target-stat-value { margin-top:5px; color:#f3f7ff; font-family:'Sora','Inter',sans-serif; font-size:20px !important; line-height:1; font-weight:700; }
        .dealer-target-delta { color:#ff5477; font-size:8px !important; font-weight:600; margin-top:5px; line-height:1; }
        .dealer-target-filters { display:grid; grid-template-columns:minmax(260px,1.45fr) repeat(2,minmax(190px,1fr)); gap:12px; align-items:start; max-width:1120px; margin-bottom:24px; }
        .dealer-target-filter-wrap { position:relative; }
        .dealer-target-search-wrap { position:relative; }
        .dealer-target-search-wrap > .material-icons { position:absolute; z-index:1; top:50%; left:15px; transform:translateY(-50%); color:#8fa7d6; font-size:20px; pointer-events:none; }
        .dealer-target-search-wrap .dealer-target-control { padding-left:46px; }
        .dealer-target-filter-btn { min-width:112px; height:46px; }
        .dealer-target-control { height:46px; padding:0 16px; border:1px solid #294878; border-radius:12px; background:#091936; color:#cbd8f4; font-size:14px !important; width:100%; outline:none; transition:border-color .2s ease,box-shadow .2s ease; }
        .dealer-target-control:focus { border-color:#26cce0; box-shadow:0 0 0 2px rgba(38,204,224,.1); }
        .dealer-target-filter-label { display:flex; align-items:center; gap:10px; }
        .dealer-target-filter-label .material-icons { color:#8fa7d6; font-size:19px; }
        .dealer-target-table-card { overflow:hidden; border:1px solid #284775; border-top:3px solid #23cee8; border-radius:19px; background:#0b2046; }
        .dealer-target-table-title { display:flex; align-items:center; gap:14px; padding:19px 24px; border-bottom:1px solid #274371; }
        .dealer-target-table-title .icon-box { width:48px; height:48px; border:1px solid #23cee8; border-radius:13px; display:flex; align-items:center; justify-content:center; color:#23cee8; }
        .dealer-target-table-title h2 { color:#eef4ff; font-size:19px !important; margin:0; }
        .dealer-target-table-title p { color:#8fa4d3; font-size:13px !important; margin:3px 0 0; }
        .dealer-target-table-wrap { overflow-x:auto; }
        .dealer-target-table { width:100%; min-width:1050px; border-collapse:collapse; }
        .dealer-target-table th { padding:15px 18px; text-align:left; color:#91a6d5; font-size:11px !important; font-weight:700; letter-spacing:.075em; text-transform:uppercase; border-bottom:1px solid #294675; white-space:nowrap; }
        .dealer-target-table td { padding:15px 18px; color:#b7c7e8; font-size:13px !important; border-bottom:1px solid #223e6b; }
        .dealer-target-row-actions { display:flex; align-items:center; gap:7px; white-space:nowrap; }
        .dealer-target-action-btn { width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; color:#b9caee; border:1px solid #315187; border-radius:9px; background:#091936; cursor:pointer; }
        .dealer-target-action-btn:hover { color:#fff; border-color:#29cbe5; background:#123d60; }
        .dealer-target-action-btn.delete:hover { color:#ff6682; border-color:#a43b5d; background:rgba(255,67,105,.1); }
        .dealer-target-action-btn .material-icons { font-size:18px; }
        .dealer-target-delete-form { margin:0; }
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
        .dealer-target-custom-select { position:relative; }
        .dealer-target-select-trigger { display:flex; align-items:center; justify-content:space-between; text-align:left; cursor:pointer; }
        .dealer-target-select-trigger .material-icons { color:#8fa7d6; font-size:21px; }
        .dealer-target-select-panel { display:none; position:absolute; top:calc(100% + 7px); left:0; right:0; z-index:100003; padding:10px; overflow:hidden; border:1px solid #315187; border-radius:11px; background:#071733; box-shadow:0 16px 35px rgba(0,0,0,.42); }
        .dealer-target-select-panel.show { display:block; }
        .dealer-target-user-search { height:40px; margin-bottom:8px; }
        .dealer-target-user-options { max-height:230px; overflow-y:auto; }
        .dealer-target-user-option { width:100%; padding:10px 12px; color:#b9c8e8; text-align:left; border:0; border-radius:8px; background:transparent; cursor:pointer; font-size:14px; }
        .dealer-target-user-option:hover, .dealer-target-user-option.active { color:#fff; background:#123d60; }
        .dealer-target-user-empty { display:none; padding:18px 12px; color:#8fa4d3; text-align:center; font-size:13px; }
        .dealer-target-month-wrap { position:relative; }
        .dealer-target-month-wrap .dealer-target-input { padding-right:48px; cursor:pointer; }
        .dealer-target-month-icon { position:absolute; top:50%; right:15px; transform:translateY(-50%); color:#8fa7d6; pointer-events:none; font-size:21px; }
        #newDealerTargetModal .select2-container { width:100% !important; }
        #newDealerTargetModal .select2-container .select2-selection--single { height:48px !important; border:1px solid #315187 !important; border-radius:11px !important; background:#091936 !important; }
        #newDealerTargetModal .select2-container .select2-selection--single .select2-selection__rendered { height:46px; line-height:46px !important; padding:0 43px 0 15px !important; color:#eef4ff !important; font-size:14px !important; }
        #newDealerTargetModal .select2-container .select2-selection--single .select2-selection__placeholder { color:#8296c3 !important; }
        #newDealerTargetModal .select2-container .select2-selection--single .select2-selection__arrow { height:46px !important; right:12px !important; }
        #newDealerTargetModal .select2-container--open .select2-selection--single { border-color:#26cce0 !important; box-shadow:0 0 0 2px rgba(38,204,224,.12); }
        .new-dealer-target-dropdown.select2-dropdown { z-index:100001; overflow:hidden; color:#eaf1ff; border:1px solid #315187 !important; border-radius:11px !important; background:#071733 !important; box-shadow:0 16px 35px rgba(0,0,0,.4); }
        .new-dealer-target-dropdown .select2-search--dropdown { padding:10px; }
        .new-dealer-target-dropdown .select2-search__field { height:40px; padding:0 12px; color:#eef4ff; border:1px solid #315187 !important; border-radius:8px; background:#0a1d40; outline:none; }
        .new-dealer-target-dropdown .select2-results__options { max-height:245px !important; }
        .new-dealer-target-dropdown .select2-results__option { padding:10px 14px; color:#b9c8e8; font-size:14px; }
        .new-dealer-target-dropdown .select2-results__option--highlighted[aria-selected] { color:#fff !important; background:#123d60 !important; }
        .new-dealer-target-dropdown .select2-results__option[aria-selected=true] { color:#27d2e9; background:#0d294e; }
        #ui-datepicker-div.new-dealer-month-picker { z-index:100002 !important; width:300px; padding:12px; color:#eef4ff; border:1px solid #315187; border-radius:12px; background:#071733; box-shadow:0 18px 40px rgba(0,0,0,.45); }
        .new-dealer-month-picker .ui-datepicker-header { padding:8px; border:0; border-radius:8px; background:#0d294e; }
        .new-dealer-month-picker .ui-datepicker-title { display:flex; gap:8px; justify-content:center; }
        .new-dealer-month-picker select.ui-datepicker-month, .new-dealer-month-picker select.ui-datepicker-year { height:35px; padding:0 7px; color:#eef4ff; border:1px solid #315187; border-radius:7px; background:#091936; }
        .new-dealer-month-picker .ui-datepicker-calendar { display:none !important; }
        .new-dealer-month-picker .ui-datepicker-buttonpane { display:flex; justify-content:flex-end; gap:8px; padding-top:10px; border:0; background:transparent; }
        .new-dealer-month-picker .ui-datepicker-buttonpane button { padding:7px 12px; color:#071733; border:0; border-radius:7px; background:#2fc5ec; font-weight:700; opacity:1; }
        .dealer-target-month-panel { padding:14px; }
        .dealer-target-month-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; color:#eef4ff; font-weight:700; }
        .dealer-target-month-nav { width:34px; height:34px; display:flex; align-items:center; justify-content:center; color:#a8b9dd; border:1px solid #315187; border-radius:8px; background:#0a1d40; cursor:pointer; }
        .dealer-target-month-nav .material-icons { font-size:19px; }
        .dealer-target-month-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
        .dealer-target-month-option { padding:9px 5px; color:#b9c8e8; border:1px solid transparent; border-radius:8px; background:transparent; cursor:pointer; font-size:13px; }
        .dealer-target-month-option:hover { color:#fff; background:#123d60; }
        .dealer-target-month-option.active { color:#06152e; border-color:#2fc5ec; background:#2fc5ec; font-weight:700; }
        .dealer-target-modal-footer { display:flex; align-items:center; justify-content:flex-end; gap:12px; padding:17px 24px 22px; }
        .dealer-target-modal-footer .dealer-target-btn { min-width:110px; }
        .dealer-target-file-input { position:absolute; width:1px; height:1px; opacity:0; overflow:hidden; }
        .dealer-target-file-box { min-height:145px; padding:24px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:9px; text-align:center; color:#a8b9dc; border:1px dashed #3b6097; border-radius:12px; background:#091936; cursor:pointer; transition:.2s ease; }
        .dealer-target-file-box:hover { color:#eef4ff; border-color:#26cce0; background:#0b2448; }
        .dealer-target-file-box .material-icons { color:#29cbe5; font-size:34px; }
        .dealer-target-file-box strong { color:#eef4ff; font-size:15px; }
        .dealer-target-file-box small { color:#8296c3; font-size:12px; }
        .dealer-target-import-help { margin:15px 0 0; color:#91a6d5; font-size:12px; line-height:1.6; }
        .dealer-target-table tr:last-child td { border-bottom:0; }
        .dealer-target-table .number { color:#f1f5ff; font-weight:700; }
        .achievement-pill { display:inline-flex; min-width:76px; justify-content:center; padding:7px 12px; border-radius:999px; font-weight:700; }
        .achievement-pill.good { color:#16e4a3; border:1px solid #158f78; background:rgba(17,190,142,.12); }
        .achievement-pill.warning { color:#ffbb3e; border:1px solid #9f7230; background:rgba(255,173,32,.11); }
        .achievement-pill.low { color:#ff5978; border:1px solid #a43b5d; background:rgba(255,67,105,.12); }
        @media (max-width: 991px) {
            .dealer-target-header { flex-direction:column; }
            .dealer-target-actions { justify-content:flex-start; flex-wrap:wrap; }
            .dealer-target-stats { grid-template-columns:1fr; max-width:none; }
            .dealer-target-filters { grid-template-columns:1fr; max-width:none; }
        }
    </style>

    <div class="dealer-target-page">
        @if(session('success'))
            <div class="dealer-target-alert success">{{ session('success') }}</div>
        @endif
        @if(session('import_error'))
            <div class="dealer-target-alert error">{{ session('import_error') }}</div>
        @endif
        @if(!empty($setupRequired) || session('setup_error'))
            <div class="dealer-target-alert error">{{ session('setup_error', 'Dealer targets database setup is pending. Please run the database migration.') }}</div>
        @endif
        @if($errors->getBag('default')->any())
            <div class="dealer-target-alert error">{{ $errors->first() }}</div>
        @endif
        <div class="dealer-target-header">
            <div>
                <div class="dealer-target-breadcrumb"><span>Sales Management</span><span>›</span><span class="current">New Dealer Targets</span></div>
                <div class="dealer-target-title-row">
                    <h1>New Dealer Appointment: Target vs Achievement</h1>
                    <span class="dealer-target-record-count">{{ $dealerTargets->count() }} records</span>
                </div>
                <p>Track monthly new dealer appointment targets against achievement, by employee</p>
            </div>
            <div class="dealer-target-actions">
                <button type="button" class="dealer-target-btn" id="openDealerTargetImport"><i class="material-icons">upload</i> Import</button>
                <a class="dealer-target-btn" href="{{ route('new-dealer-targets.export', request()->only(['search', 'zone_id', 'month'])) }}"><i class="material-icons">download</i> Export</a>
                <button type="button" class="dealer-target-btn primary" id="openNewDealerTarget"><i class="material-icons">add</i> New Target</button>
            </div>
        </div>

        <div class="dealer-target-stats">
            <div class="dealer-target-stat">
                <div class="dealer-target-stat-icon"><i class="material-icons">outlined_flag</i></div>
                <div class="dealer-target-stat-content"><div class="dealer-target-stat-label">Total Plan (New Dealers)</div><div class="dealer-target-stat-value">{{ $totalPlan }}</div></div>
            </div>
            <div class="dealer-target-stat">
                <div class="dealer-target-stat-icon"><i class="material-icons">add_business</i></div>
                <div class="dealer-target-stat-content"><div class="dealer-target-stat-label">Total Achievement</div><div class="dealer-target-stat-value">{{ $totalAchievement }}</div></div>
            </div>
            <div class="dealer-target-stat">
                <div class="dealer-target-stat-icon"><i class="material-icons">bar_chart</i></div>
                <div class="dealer-target-stat-content">
                    <div class="dealer-target-stat-label">Overall Achievement %</div>
                    <div class="dealer-target-stat-value">{{ $achievementPercentage }}%</div>
                    @if($totalPlan > 0 && $achievementPercentage < 100)
                        <div class="dealer-target-delta">{{ round(100 - $achievementPercentage, 1) }}% below target</div>
                    @endif
                </div>
            </div>
        </div>

        <form class="dealer-target-filters" method="GET" action="{{ route('new-dealer-targets') }}" id="dealerTargetFilterForm">
            <div class="dealer-target-search-wrap">
                <i class="material-icons">search</i>
                <input class="dealer-target-control" id="dealerTargetSearchFilter" name="search" type="search" value="{{ request('search') }}" placeholder="Search by user name or code" autocomplete="off">
            </div>
            <div class="dealer-target-custom-select dealer-target-filter-wrap" id="dealerTargetZoneFilter">
                <button type="button" class="dealer-target-control dealer-target-select-trigger" id="dealerTargetZoneTrigger">
                    <span class="dealer-target-filter-label"><i class="material-icons">public</i><span id="dealerTargetZoneText">{{ $selectedZone->division_name ?? 'All Zones' }}</span></span><i class="material-icons">expand_more</i>
                </button>
                <div class="dealer-target-select-panel" id="dealerTargetZonePanel">
                    <div class="dealer-target-user-options">
                        <button type="button" class="dealer-target-user-option {{ request('zone_id') ? '' : 'active' }}" data-zone-id="" data-zone-label="All Zones">All Zones</button>
                        @foreach($zones as $zone)
                            <button type="button" class="dealer-target-user-option {{ (string) request('zone_id') === (string) $zone->id ? 'active' : '' }}" data-zone-id="{{ $zone->id }}" data-zone-label="{{ $zone->division_name }}">{{ $zone->division_name }}</button>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" name="zone_id" id="dealerTargetZoneValue" value="{{ request('zone_id') }}">
            </div>
            <div class="dealer-target-month-wrap dealer-target-filter-wrap">
                <button type="button" class="dealer-target-control dealer-target-select-trigger" id="dealerTargetFilterMonthTrigger">
                    <span class="dealer-target-filter-label"><i class="material-icons">calendar_month</i><span id="dealerTargetFilterMonthText">{{ $selectedFilterMonth ? \Carbon\Carbon::createFromFormat('Y-m', $selectedFilterMonth)->format('F Y') : 'All Months' }}</span></span>
                    <i class="material-icons">expand_more</i>
                </button>
                <div class="dealer-target-select-panel dealer-target-month-panel" id="dealerTargetFilterMonthPanel">
                    <div class="dealer-target-month-head">
                        <button type="button" class="dealer-target-month-nav" id="dealerTargetFilterPreviousYear"><i class="material-icons">chevron_left</i></button>
                        <span id="dealerTargetFilterCalendarYear"></span>
                        <button type="button" class="dealer-target-month-nav" id="dealerTargetFilterNextYear"><i class="material-icons">chevron_right</i></button>
                    </div>
                    <div class="dealer-target-month-grid" id="dealerTargetFilterMonthGrid"></div>
                    <button type="button" class="dealer-target-user-option" id="dealerTargetAllMonths">All Months</button>
                </div>
                <input type="hidden" name="month" id="dealerTargetFilterMonthValue" value="{{ $selectedFilterMonth }}">
            </div>
        </form>

        <div class="dealer-target-table-card">
            <div class="dealer-target-table-title">
                <div class="icon-box"><i class="material-icons">storefront</i></div>
                <div><h2>Appointment Records</h2><p>{{ $dealerTargets->count() }} records</p></div>
            </div>
            <div class="dealer-target-table-wrap">
                <table class="dealer-target-table">
                    <thead><tr><th>No</th>@if(auth()->user()->can('new_dealer_target_edit') || auth()->user()->can('new_dealer_target_delete'))<th>Action</th>@endif<th>Emp Code</th><th>Emp Name</th><th>Zone</th><th>Month</th><th>Plan Nos</th><th>Achievement Nos</th><th>Achievement %</th><th>Note</th></tr></thead>
                    <tbody>
                        @forelse($dealerTargets as $target)
                            @php $percentage = $target->target > 0 ? round(($target->achievement / $target->target) * 100, 1) : 0; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                @if(auth()->user()->can('new_dealer_target_edit') || auth()->user()->can('new_dealer_target_delete'))
                                <td>
                                    <div class="dealer-target-row-actions">
                                        @can('new_dealer_target_edit')
                                        <button type="button" class="dealer-target-action-btn editDealerTarget" title="Edit" data-id="{{ $target->id }}" data-user-id="{{ $target->user_id }}" data-month="{{ $target->target_month->format('Y-m') }}" data-target="{{ $target->target }}" data-achievement="{{ $target->achievement }}" data-note="{{ $target->note }}"><i class="material-icons">edit</i></button>
                                        @endcan
                                        @can('new_dealer_target_delete')
                                        <form class="dealer-target-delete-form" method="POST" action="{{ route('new-dealer-targets.destroy', $target) }}" onsubmit="return confirm('Are you sure you want to delete this dealer target?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dealer-target-action-btn delete" title="Delete"><i class="material-icons">clear</i></button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                                @endif
                                <td>{{ $target->user->employee_codes ?? '—' }}</td>
                                <td>{{ $target->user->name ?: trim(($target->user->first_name ?? '').' '.($target->user->last_name ?? '')) }}</td>
                                <td>{{ optional($target->user->getdivision)->division_name ?? '—' }}</td>
                                <td>{{ $target->target_month->format('M Y') }}</td>
                                <td class="number">{{ $target->target }}</td><td class="number">{{ $target->achievement }}</td>
                                <td><span class="achievement-pill {{ $percentage >= 100 ? 'good' : ($percentage >= 70 ? 'warning' : 'low') }}">{{ $percentage }}%</span></td>
                                <td>{{ $target->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ (auth()->user()->can('new_dealer_target_edit') || auth()->user()->can('new_dealer_target_delete')) ? 10 : 9 }}" class="dealer-target-empty">No dealer target records available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dealer-target-modal {{ $errors->getBag('default')->any() ? 'show' : '' }}" id="newDealerTargetModal" aria-hidden="{{ $errors->getBag('default')->any() ? 'false' : 'true' }}">
        <div class="dealer-target-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="newDealerTargetTitle">
            <form method="POST" action="{{ route('new-dealer-targets.store') }}" id="dealerTargetForm">
                @csrf
                <input type="hidden" name="target_id" id="dealerTargetId" value="{{ old('target_id') }}">
                <div class="dealer-target-modal-head">
                    <h2 id="newDealerTargetTitle">{{ old('target_id') ? 'Edit Dealer Appointment Target' : 'New Dealer Appointment Target' }}</h2>
                    <button type="button" class="dealer-target-modal-close closeNewDealerTarget" aria-label="Close"><i class="material-icons">close</i></button>
                </div>
                <div class="dealer-target-modal-body">
                    <div class="dealer-target-field">
                        <label for="dealerTargetUserTrigger">User</label>
                        @php $oldUser = $users->firstWhere('id', (int) old('user_id')); @endphp
                        <div class="dealer-target-custom-select" id="dealerTargetUserSelect">
                            <button type="button" class="dealer-target-input dealer-target-select-trigger" id="dealerTargetUserTrigger">
                                <span id="dealerTargetUserText">{{ $oldUser ? (($oldUser->employee_codes ? $oldUser->employee_codes.' - ' : '').($oldUser->name ?: trim($oldUser->first_name.' '.$oldUser->last_name))) : 'Select user...' }}</span>
                                <i class="material-icons">expand_more</i>
                            </button>
                            <div class="dealer-target-select-panel" id="dealerTargetUserPanel">
                                <input type="search" class="dealer-target-input dealer-target-user-search" id="dealerTargetUserSearch" placeholder="Search user name or code" autocomplete="off">
                                <div class="dealer-target-user-options">
                                    @foreach($users as $user)
                                        @php $userLabel = ($user->employee_codes ? $user->employee_codes.' - ' : '').($user->name ?: trim($user->first_name.' '.$user->last_name)); @endphp
                                        <button type="button" class="dealer-target-user-option {{ (string) old('user_id') === (string) $user->id ? 'active' : '' }}" data-id="{{ $user->id }}" data-label="{{ $userLabel }}">{{ $userLabel }}</button>
                                    @endforeach
                                    <div class="dealer-target-user-empty" id="dealerTargetUserEmpty">No users found</div>
                                </div>
                            </div>
                        </div>
                        <input id="dealerTargetUser" name="user_id" type="hidden" value="{{ old('user_id') }}" required>
                    </div>
                    <div class="dealer-target-field">
                        <label for="dealerTargetMonth">Month</label>
                        <div class="dealer-target-month-wrap">
                            <button type="button" class="dealer-target-input dealer-target-select-trigger" id="dealerTargetMonthDisplay">
                                <span id="dealerTargetMonthText">{{ \Carbon\Carbon::createFromFormat('Y-m', old('target_month', now()->format('Y-m')))->format('F Y') }}</span>
                            </button>
                            <i class="material-icons dealer-target-month-icon">calendar_month</i>
                            <div class="dealer-target-select-panel dealer-target-month-panel" id="dealerTargetMonthPanel">
                                <div class="dealer-target-month-head">
                                    <button type="button" class="dealer-target-month-nav" id="dealerTargetPreviousYear"><i class="material-icons">chevron_left</i></button>
                                    <span id="dealerTargetCalendarYear"></span>
                                    <button type="button" class="dealer-target-month-nav" id="dealerTargetNextYear"><i class="material-icons">chevron_right</i></button>
                                </div>
                                <div class="dealer-target-month-grid" id="dealerTargetMonthGrid"></div>
                            </div>
                        </div>
                        <input id="dealerTargetMonth" name="target_month" type="hidden" value="{{ old('target_month', now()->format('Y-m')) }}">
                    </div>
                    <div class="dealer-target-field">
                        <label for="dealerTargetNumber">New Dealer Appointment Target (Nos.)</label>
                        <input class="dealer-target-input" id="dealerTargetNumber" name="target" type="number" min="1" step="1" value="{{ old('target') }}" placeholder="e.g. 12" required>
                    </div>
                    <div class="dealer-target-field" id="dealerTargetAchievementField" style="{{ old('target_id') ? '' : 'display:none;' }}">
                        <label for="dealerTargetAchievement">Achievement (Nos.)</label>
                        <input class="dealer-target-input" id="dealerTargetAchievement" name="achievement" type="number" min="0" step="1" value="{{ old('achievement') }}" placeholder="Calculated automatically when left blank">
                    </div>
                    <div class="dealer-target-field">
                        <label for="dealerTargetNote">Note</label>
                        <textarea class="dealer-target-input" id="dealerTargetNote" name="note" maxlength="500" placeholder="Optional note">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="dealer-target-modal-footer">
                    <button type="button" class="dealer-target-btn closeNewDealerTarget">Cancel</button>
                    <button type="submit" class="dealer-target-btn primary"><i class="material-icons">save</i> <span id="dealerTargetSaveText">{{ old('target_id') ? 'Update' : 'Save' }}</span></button>
                </div>
            </form>
        </div>
    </div>

    <div class="dealer-target-modal {{ $errors->import->any() ? 'show' : '' }}" id="dealerTargetImportModal" aria-hidden="{{ $errors->import->any() ? 'false' : 'true' }}">
        <div class="dealer-target-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="dealerTargetImportTitle">
            <form method="POST" action="{{ route('new-dealer-targets.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="dealer-target-modal-head">
                    <h2 id="dealerTargetImportTitle">Import New Dealer Targets</h2>
                    <button type="button" class="dealer-target-modal-close closeDealerTargetImport" aria-label="Close"><i class="material-icons">close</i></button>
                </div>
                <div class="dealer-target-modal-body">
                    @if($errors->import->any())
                        <div class="dealer-target-alert error">{{ $errors->import->first() }}</div>
                    @endif
                    <input class="dealer-target-file-input" id="dealerTargetImportFile" type="file" name="import_file" accept=".xlsx,.xls,.csv" required>
                    <label class="dealer-target-file-box" for="dealerTargetImportFile">
                        <i class="material-icons">upload_file</i>
                        <strong id="dealerTargetImportFileName">Choose Excel or CSV file</strong>
                        <small>XLSX, XLS or CSV · Maximum 10 MB</small>
                    </label>
                    <p class="dealer-target-import-help">
                        Required columns: <strong>Emp Code</strong>, <strong>New Dealer Plan Nos</strong>, and <strong>Month</strong>. <strong>Achievement Nos</strong> and Note are optional. Imported achievements update the listing, totals, percentages, and future exports. Identical records are skipped; changed records are updated; new user/month records are added.
                    </p>
                </div>
                <div class="dealer-target-modal-footer">
                    <button type="button" class="dealer-target-btn closeDealerTargetImport">Cancel</button>
                    <button type="submit" class="dealer-target-btn primary"><i class="material-icons">upload</i> Import</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('newDealerTargetModal');
            const openButton = document.getElementById('openNewDealerTarget');
            const closeButtons = document.querySelectorAll('.closeNewDealerTarget');
            const importModal = document.getElementById('dealerTargetImportModal');
            const importOpenButton = document.getElementById('openDealerTargetImport');
            const importCloseButtons = document.querySelectorAll('.closeDealerTargetImport');
            const importFile = document.getElementById('dealerTargetImportFile');

            function setModal(open) {
                modal.classList.toggle('show', open);
                modal.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.style.overflow = open ? 'hidden' : '';
            }

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () { setModal(false); });
            });
            modal.addEventListener('click', function (event) {
                if (event.target === modal) setModal(false);
            });
            function setImportModal(open) {
                importModal.classList.toggle('show', open);
                importModal.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.style.overflow = open ? 'hidden' : '';
            }
            importOpenButton.addEventListener('click', function () { setImportModal(true); });
            importCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () { setImportModal(false); });
            });
            importModal.addEventListener('click', function (event) {
                if (event.target === importModal) setImportModal(false);
            });
            importFile.addEventListener('change', function () {
                document.getElementById('dealerTargetImportFileName').textContent = this.files.length ? this.files[0].name : 'Choose Excel or CSV file';
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setModal(false);
                    setImportModal(false);
                }
            });

            const userTrigger = document.getElementById('dealerTargetUserTrigger');
            const userPanel = document.getElementById('dealerTargetUserPanel');
            const userSearch = document.getElementById('dealerTargetUserSearch');
            const userInput = document.getElementById('dealerTargetUser');
            const userText = document.getElementById('dealerTargetUserText');
            const userOptions = Array.from(document.querySelectorAll('.dealer-target-user-option'));
            const userEmpty = document.getElementById('dealerTargetUserEmpty');

            userTrigger.addEventListener('click', function () {
                userPanel.classList.toggle('show');
                monthPanel.classList.remove('show');
                if (userPanel.classList.contains('show')) setTimeout(function () { userSearch.focus(); }, 0);
            });
            userSearch.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let visible = 0;
                userOptions.forEach(function (option) {
                    const matches = option.dataset.label.toLowerCase().includes(query);
                    option.style.display = matches ? 'block' : 'none';
                    if (matches) visible++;
                });
                userEmpty.style.display = visible ? 'none' : 'block';
            });
            userOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    userInput.value = this.dataset.id;
                    userText.textContent = this.dataset.label;
                    userOptions.forEach(function (item) { item.classList.remove('active'); });
                    this.classList.add('active');
                    userPanel.classList.remove('show');
                });
            });

            const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            const monthTrigger = document.getElementById('dealerTargetMonthDisplay');
            const monthPanel = document.getElementById('dealerTargetMonthPanel');
            const monthGrid = document.getElementById('dealerTargetMonthGrid');
            const monthInput = document.getElementById('dealerTargetMonth');
            const monthText = document.getElementById('dealerTargetMonthText');
            const calendarYear = document.getElementById('dealerTargetCalendarYear');
            let calendarYearValue = Number((monthInput.value || new Date().toISOString().slice(0, 7)).slice(0, 4));

            function renderMonths() {
                calendarYear.textContent = calendarYearValue;
                monthGrid.innerHTML = '';
                monthNames.forEach(function (name, index) {
                    const value = calendarYearValue + '-' + String(index + 1).padStart(2, '0');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'dealer-target-month-option' + (monthInput.value === value ? ' active' : '');
                    button.textContent = name.slice(0, 3);
                    button.addEventListener('click', function () {
                        monthInput.value = value;
                        monthText.textContent = name + ' ' + calendarYearValue;
                        monthPanel.classList.remove('show');
                    });
                    monthGrid.appendChild(button);
                });
            }
            monthTrigger.addEventListener('click', function () {
                monthPanel.classList.toggle('show');
                userPanel.classList.remove('show');
                renderMonths();
            });
            document.getElementById('dealerTargetPreviousYear').addEventListener('click', function () { calendarYearValue--; renderMonths(); });
            document.getElementById('dealerTargetNextYear').addEventListener('click', function () { calendarYearValue++; renderMonths(); });
            renderMonths();

            const targetIdInput = document.getElementById('dealerTargetId');
            const targetNumberInput = document.getElementById('dealerTargetNumber');
            const targetAchievementInput = document.getElementById('dealerTargetAchievement');
            const targetAchievementField = document.getElementById('dealerTargetAchievementField');
            const targetNoteInput = document.getElementById('dealerTargetNote');
            const targetModalTitle = document.getElementById('newDealerTargetTitle');
            const targetSaveText = document.getElementById('dealerTargetSaveText');

            function setTargetFormMode(target) {
                const editing = Boolean(target);
                targetIdInput.value = editing ? target.dataset.id : '';
                targetModalTitle.textContent = editing ? 'Edit Dealer Appointment Target' : 'New Dealer Appointment Target';
                targetSaveText.textContent = editing ? 'Update' : 'Save';
                targetNumberInput.value = editing ? target.dataset.target : '';
                targetAchievementInput.value = editing ? target.dataset.achievement : '';
                targetAchievementField.style.display = editing ? 'block' : 'none';
                targetNoteInput.value = editing ? target.dataset.note : '';

                const selectedUserId = editing ? target.dataset.userId : '';
                userInput.value = selectedUserId;
                userOptions.forEach(function (option) {
                    const active = option.dataset.id === selectedUserId;
                    option.classList.toggle('active', active);
                    if (active) userText.textContent = option.dataset.label;
                });
                if (!editing) userText.textContent = 'Select user...';

                const selectedMonth = editing ? target.dataset.month : new Date().toISOString().slice(0, 7);
                monthInput.value = selectedMonth;
                calendarYearValue = Number(selectedMonth.slice(0, 4));
                monthText.textContent = monthNames[Number(selectedMonth.slice(5, 7)) - 1] + ' ' + calendarYearValue;
                userSearch.value = '';
                userOptions.forEach(function (option) { option.style.display = 'block'; });
                userEmpty.style.display = 'none';
                renderMonths();
            }

            openButton.addEventListener('click', function () {
                setTargetFormMode(null);
                setModal(true);
            });
            document.querySelectorAll('.editDealerTarget').forEach(function (button) {
                button.addEventListener('click', function () {
                    setTargetFormMode(this);
                    setModal(true);
                });
            });

            const zoneFilter = document.getElementById('dealerTargetZoneFilter');
            const zoneTrigger = document.getElementById('dealerTargetZoneTrigger');
            const zonePanel = document.getElementById('dealerTargetZonePanel');
            const zoneValue = document.getElementById('dealerTargetZoneValue');
            const zoneText = document.getElementById('dealerTargetZoneText');
            const zoneOptions = Array.from(zonePanel.querySelectorAll('[data-zone-id]'));
            const filterForm = document.getElementById('dealerTargetFilterForm');

            zoneTrigger.addEventListener('click', function () {
                zonePanel.classList.toggle('show');
                filterMonthPanel.classList.remove('show');
            });
            zoneOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    zoneValue.value = this.dataset.zoneId;
                    zoneText.textContent = this.dataset.zoneLabel;
                    zoneOptions.forEach(function (item) { item.classList.remove('active'); });
                    this.classList.add('active');
                    zonePanel.classList.remove('show');
                    filterForm.submit();
                });
            });

            const filterMonthTrigger = document.getElementById('dealerTargetFilterMonthTrigger');
            const filterMonthPanel = document.getElementById('dealerTargetFilterMonthPanel');
            const filterMonthGrid = document.getElementById('dealerTargetFilterMonthGrid');
            const filterMonthValue = document.getElementById('dealerTargetFilterMonthValue');
            const filterMonthText = document.getElementById('dealerTargetFilterMonthText');
            const filterCalendarYear = document.getElementById('dealerTargetFilterCalendarYear');
            let filterYearValue = Number((filterMonthValue.value || new Date().toISOString().slice(0, 7)).slice(0, 4));

            function renderFilterMonths() {
                filterCalendarYear.textContent = filterYearValue;
                filterMonthGrid.innerHTML = '';
                monthNames.forEach(function (name, index) {
                    const value = filterYearValue + '-' + String(index + 1).padStart(2, '0');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'dealer-target-month-option' + (filterMonthValue.value === value ? ' active' : '');
                    button.textContent = name.slice(0, 3);
                    button.addEventListener('click', function () {
                        filterMonthValue.value = value;
                        filterMonthText.textContent = name + ' ' + filterYearValue;
                        filterMonthPanel.classList.remove('show');
                        filterForm.submit();
                    });
                    filterMonthGrid.appendChild(button);
                });
            }
            filterMonthTrigger.addEventListener('click', function () {
                filterMonthPanel.classList.toggle('show');
                zonePanel.classList.remove('show');
                renderFilterMonths();
            });
            document.getElementById('dealerTargetFilterPreviousYear').addEventListener('click', function () { filterYearValue--; renderFilterMonths(); });
            document.getElementById('dealerTargetFilterNextYear').addEventListener('click', function () { filterYearValue++; renderFilterMonths(); });
            document.getElementById('dealerTargetAllMonths').addEventListener('click', function () {
                filterMonthValue.value = '';
                filterMonthText.textContent = 'All Months';
                filterMonthPanel.classList.remove('show');
                filterForm.submit();
            });
            renderFilterMonths();

            const searchFilter = document.getElementById('dealerTargetSearchFilter');
            let searchTimer;
            searchFilter.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { filterForm.submit(); }, 500);
            });

            document.addEventListener('click', function (event) {
                if (!document.getElementById('dealerTargetUserSelect').contains(event.target)) userPanel.classList.remove('show');
                if (!event.target.closest('.dealer-target-month-wrap')) monthPanel.classList.remove('show');
                if (!zoneFilter.contains(event.target)) zonePanel.classList.remove('show');
                if (!document.getElementById('dealerTargetFilterMonthTrigger').parentElement.contains(event.target)) filterMonthPanel.classList.remove('show');
            });
        });
    </script>
</x-app-layout>
