<x-app-layout>
@php
    $sparkPoints = function ($values, $width = 150, $height = 34) {
        $values = collect($values)->map(fn ($value) => (float) $value)->values();
        if ($values->isEmpty()) {
            return '';
        }

        $min = $values->min();
        $max = $values->max();
        $range = max($max - $min, 1);
        $last = max($values->count() - 1, 1);

        return $values->map(function ($value, $index) use ($width, $height, $min, $range, $last) {
            $x = round(($index / $last) * $width, 2);
            $y = round($height - ((($value - $min) / $range) * ($height - 6)) - 3, 2);
            return $x . ',' . $y;
        })->implode(' ');
    };

    $linePoints = function ($values, $width = 560, $height = 210) {
        $values = collect($values)->map(fn ($value) => (float) $value)->values();
        if ($values->isEmpty()) {
            return '';
        }

        $max = max($values->max(), 1);
        $last = max($values->count() - 1, 1);

        return $values->map(function ($value, $index) use ($width, $height, $max, $last) {
            $x = round(($index / $last) * $width, 2);
            $y = round($height - (($value / $max) * ($height - 18)) - 9, 2);
            return $x . ',' . $y;
        })->implode(' ');
    };

    $maxProduct = max(collect($dashboard['charts']['products'])->max('value') ?: 1, 1);
    $maxEmployee = max(collect($dashboard['topEmployees'])->max('value') ?: 1, 1);
    $attendanceKpi = collect($dashboard['kpis'])->firstWhere('label', 'Today Attendance');
    $present = collect($attendanceKpi['subs'] ?? [])->firstWhere('label', 'Present')['value'] ?? '0';
@endphp

<style>
    .fk-dashboard {
        --dash-bg: #050e24;
        --dash-panel: rgba(9, 20, 48, .52);
        --dash-panel-strong: rgba(12, 26, 60, .68);
        --dash-input: rgba(8, 20, 50, .66);
        --dash-border: rgba(90, 130, 220, .18);
        --dash-border-strong: rgba(120, 160, 255, .25);
        --dash-cyan: #22d3ee;
        --dash-sky: #7cc8ff;
        --dash-blue: #3b82f6;
        --dash-green: #12d18e;
        --dash-yellow: #ffb547;
        --dash-red: #ff5d7a;
        --dash-text: #e8f0ff;
        --dash-muted: #7d8fbf;
        position: relative;
        min-height: calc(100vh - 72px);
        padding: 22px 24px 44px;
        color: var(--dash-text);
        font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
        overflow: hidden;
    }

    .fk-dashboard::before {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(120% 90% at 18% 8%, #0d2358 0, transparent 55%),
            radial-gradient(110% 80% at 95% 95%, #0a1b45 0, transparent 60%);
        z-index: -1;
    }

    .fk-dashboard * { box-sizing: border-box; }
    .fk-dash-shell { max-width: 1560px; margin: 0 auto; animation: fkDashRise .5s cubic-bezier(.2,.8,.2,1) both; }
    .fk-dash-breadcrumb { display: flex; gap: 9px; align-items: center; margin-bottom: 10px; font-family: Sora, Inter, sans-serif; font-size: 9.5px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--dash-muted); }
    .fk-dash-breadcrumb b { color: #4ea1ff; }
    .fk-dash-titlebar { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
    .fk-dash-titlebar h1 { margin: 0; color: #fff; font-family: Sora, Inter, sans-serif; font-size: clamp(24px, 2.4vw, 34px); line-height: 1.1; font-weight: 800; letter-spacing: -.4px; }
    .fk-dash-subtitle { margin-top: 5px; font-size: 12.5px; color: var(--dash-muted); }
    .fk-dash-live, .fk-dash-pill { display: inline-flex; align-items: center; height: 26px; padding: 0 11px; border-radius: 999px; font-family: Sora, Inter, sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 1.6px; }
    .fk-dash-live { gap: 7px; border: 1px solid rgba(34,211,238,.35); background: rgba(34,211,238,.08); color: var(--dash-cyan); }
    .fk-dash-live i { width: 6px; height: 6px; border-radius: 50%; background: var(--dash-cyan); box-shadow: 0 0 8px var(--dash-cyan); animation: fkDashPulse 1.4s ease-in-out infinite; }
    .fk-dash-pill { border: 1px solid var(--dash-border); background: var(--dash-input); color: var(--dash-muted); }
    .fk-dash-actions { margin-left: auto; display: flex; gap: 8px; }
    .fk-dash-icon-btn { width: 40px; height: 40px; border-radius: 11px; border: 1px solid var(--dash-border-strong); background: var(--dash-input); color: #a9bce6; display: grid; place-items: center; transition: .2s ease; }
    .fk-dash-icon-btn:hover { border-color: rgba(34,211,238,.45); color: var(--dash-cyan); box-shadow: 0 0 16px rgba(34,211,238,.25); }

    .fk-dash-scope, .fk-dash-card, .fk-dash-chart, .fk-dash-banner {
        border: 1px solid var(--dash-border-strong);
        border-radius: 18px;
        background: var(--dash-panel);
        backdrop-filter: blur(16px);
        box-shadow: 0 30px 80px -40px rgba(0, 0, 0, .85);
    }

    .fk-dash-banner { position: relative; height: 188px; overflow: hidden; margin-bottom: 14px; }
    .fk-dash-slide { position: absolute; inset: 0; display: grid; grid-template-columns: 310px 1fr; opacity: 0; transform: translateX(22px); pointer-events: none; transition: opacity .55s ease, transform .55s ease; }
    .fk-dash-slide.is-active { opacity: 1; transform: translateX(0); pointer-events: auto; }
    .fk-dash-banner-media { position: relative; min-height: 100%; display: grid; place-items: center; color: rgba(125,143,191,.55); border-right: 1px solid rgba(90,130,220,.11); background: linear-gradient(90deg, rgba(5,14,36,.72), rgba(12,35,80,.32), transparent); }
    .fk-dash-banner-media::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(34,211,238,.08), transparent); animation: fkDashSweep 6s linear infinite; pointer-events: none; }
    .fk-dash-banner-media-inner { position: relative; display: grid; gap: 8px; place-items: center; z-index: 1; }
    .fk-dash-banner-media .material-icons { font-size: 31px; opacity: .5; }
    .fk-dash-banner-body { padding: 38px 72px 34px 36px; display: flex; flex-direction: column; justify-content: center; gap: 8px; min-width: 0; }
    .fk-dash-kicker { display: inline-flex; align-items: center; gap: 8px; font-family: Sora, Inter, sans-serif; font-size: 10px; font-weight: 800; letter-spacing: 2.2px; text-transform: uppercase; color: var(--dash-cyan); }
    .fk-dash-slide.fk-slide-success .fk-dash-kicker { color: var(--dash-green); }
    .fk-dash-slide.fk-slide-warning .fk-dash-kicker { color: var(--dash-yellow); }
    .fk-dash-slide.fk-slide-sky .fk-dash-kicker { color: var(--dash-sky); }
    .fk-dash-banner b { color: #fff; font-family: Sora, Inter, sans-serif; font-size: clamp(22px, 2.2vw, 30px); line-height: 1.18; font-weight: 800; letter-spacing: -.45px; }
    .fk-dash-banner span:last-child { color: var(--dash-muted); font-size: 14px; max-width: 680px; }
    .fk-dash-slider-dots { position: absolute; right: 24px; bottom: 17px; display: inline-flex; align-items: center; gap: 8px; z-index: 3; }
    .fk-dash-slider-dot { width: 8px; height: 8px; border: 0; padding: 0; border-radius: 999px; background: rgba(125,143,191,.38); cursor: pointer; transition: .22s ease; }
    .fk-dash-slider-dot.is-active { width: 24px; background: var(--dash-cyan); box-shadow: 0 0 12px rgba(34,211,238,.75); }

    .fk-dash-scope { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; padding: 9px 12px; margin-bottom: 16px; }
    .fk-dash-chip { height: 32px; display: inline-flex; align-items: center; padding: 0 14px; border-radius: 999px; border: 1px solid var(--dash-border); color: #a9bce6; font-size: 12px; font-weight: 700; background: var(--dash-input); cursor: pointer; }
    .fk-dash-chip.active { color: #061125; border: none; background: linear-gradient(135deg, var(--dash-cyan), var(--dash-blue)); }
    .fk-dash-select { height: 42px; min-width: 150px; border-radius: 12px; border: 1px solid var(--dash-border-strong); background: var(--dash-input); color: #cbd9ff; padding: 0 13px; font-size: 12.5px; outline: none; }
    .fk-dash-date { height: 42px; width: 138px; border-radius: 12px; border: 1px solid var(--dash-border-strong); background: var(--dash-input); color: #cbd9ff; padding: 0 12px; font-size: 12.5px; outline: none; color-scheme: dark; }
    .fk-dash-custom-fields { display: none; gap: 8px; align-items: center; }
    .fk-dash-custom-fields.is-visible { display: inline-flex; }
    .fk-dash-grid { display: grid; gap: 14px; }
    .fk-dash-kpis { grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); }
    .fk-dash-types { grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); }
    .fk-dash-charts { grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); }
    .fk-dash-small { grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); }
    .fk-dash-bottom { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
    .fk-dash-card { padding: 14px 15px 13px; display: flex; flex-direction: column; gap: 9px; overflow: hidden; transition: .22s ease; }
    .fk-dash-card:hover, .fk-dash-chart:hover { transform: translateY(-2px); border-color: rgba(34,211,238,.45); box-shadow: 0 30px 80px -40px rgba(0,0,0,.85), 0 0 16px rgba(34,211,238,.22); }
    .fk-dash-card-head { display: flex; align-items: center; gap: 9px; }
    .fk-dash-icon { width: 32px; height: 32px; border-radius: 11px; display: grid; place-items: center; border: 1px solid rgba(34,211,238,.35); background: rgba(34,211,238,.08); color: var(--dash-cyan); flex: none; }
    .fk-tone-success .fk-dash-icon { border-color: rgba(18,209,142,.38); background: rgba(18,209,142,.1); color: var(--dash-green); }
    .fk-tone-warning .fk-dash-icon { border-color: rgba(255,181,71,.38); background: rgba(255,181,71,.1); color: var(--dash-yellow); }
    .fk-tone-danger .fk-dash-icon { border-color: rgba(255,93,122,.38); background: rgba(255,93,122,.1); color: var(--dash-red); }
    .fk-tone-sky .fk-dash-icon { border-color: rgba(124,200,255,.3); background: rgba(124,200,255,.1); color: var(--dash-sky); }
    .fk-dash-label { font-family: Sora, Inter, sans-serif; font-size: 9.5px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--dash-muted); }
    .fk-dash-delta { margin-left: auto; font-size: 10.5px; font-weight: 700; color: var(--dash-green); white-space: nowrap; }
    .fk-dash-value { font-family: Sora, Inter, sans-serif; font-size: 27px; font-weight: 800; line-height: 1.1; letter-spacing: -.4px; color: #fff; }
    .fk-dash-meta { font-size: 11px; color: var(--dash-muted); }
    .fk-dash-substats { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .fk-dash-substat { display: inline-flex; align-items: center; gap: 5px; font-size: 10.5px; color: var(--dash-muted); }
    .fk-dash-substat i { width: 6px; height: 6px; border-radius: 50%; background: var(--dash-cyan); }
    .fk-dash-substat b { color: #fff; font-size: 12px; }
    .fk-dot-success { background: var(--dash-green) !important; box-shadow: 0 0 8px rgba(18,209,142,.9); }
    .fk-dot-warning { background: var(--dash-yellow) !important; }
    .fk-dot-danger { background: var(--dash-red) !important; box-shadow: 0 0 8px rgba(255,93,122,.9); }
    .fk-section-title { display: flex; align-items: center; gap: 10px; margin: 22px 0 10px; font-family: Sora, Inter, sans-serif; font-size: 9.5px; font-weight: 800; letter-spacing: 2.2px; text-transform: uppercase; color: var(--dash-muted); }
    .fk-section-title::after { content: ""; flex: 1; height: 1px; background: linear-gradient(90deg, var(--dash-border-strong), transparent); }
    .fk-progress { height: 9px; border-radius: 999px; border: 1px solid rgba(90,130,220,.12); background: rgba(120,160,255,.1); overflow: hidden; }
    .fk-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--dash-cyan), var(--dash-sky)); box-shadow: 0 0 8px rgba(34,211,238,.35); }
    .fk-dash-chart { overflow: hidden; transition: .22s ease; }
    .fk-dash-chart-head { display: flex; align-items: center; gap: 11px; padding: 12px 16px; border-bottom: 1px solid rgba(90,130,220,.2); }
    .fk-dash-chart-title b { display: block; font-family: Sora, Inter, sans-serif; font-size: 15px; color: #fff; }
    .fk-dash-chart-title span { display: block; margin-top: 1px; font-size: 10.5px; color: var(--dash-muted); }
    .fk-dash-chart-badge { margin-left: auto; height: 22px; display: inline-flex; align-items: center; padding: 0 9px; border-radius: 999px; border: 1px solid var(--dash-border); background: var(--dash-input); color: #cbd9ff; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11px; }
    .fk-chart-pad { padding: 12px 14px 10px; }
    .fk-line-chart { width: 100%; height: 220px; overflow: visible; }
    .fk-line-grid { stroke: rgba(90,130,220,.14); stroke-width: 1; }
    .fk-line-path { fill: none; stroke: var(--dash-cyan); stroke-width: 4; stroke-linecap: round; stroke-linejoin: round; filter: drop-shadow(0 0 8px rgba(34,211,238,.45)); }
    .fk-line-path.secondary { stroke: var(--dash-sky); opacity: .75; }
    .fk-bars { height: 220px; display: flex; align-items: end; gap: 10px; padding: 12px 8px 0; }
    .fk-bar { flex: 1; min-width: 30px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .fk-bar-stack { width: 100%; height: 165px; display: flex; align-items: end; gap: 5px; }
    .fk-bar-fill { flex: 1; border-radius: 9px 9px 3px 3px; background: linear-gradient(180deg, var(--dash-cyan), rgba(34,211,238,.24)); box-shadow: 0 0 12px rgba(34,211,238,.3); }
    .fk-bar-fill.secondary { background: linear-gradient(180deg, var(--dash-sky), rgba(124,200,255,.22)); }
    .fk-bar label { font-size: 10.5px; color: var(--dash-muted); }
    .fk-hbar { display: grid; gap: 12px; padding: 6px 2px; }
    .fk-hbar-row { display: grid; grid-template-columns: minmax(120px, 1fr) 2fr 58px; align-items: center; gap: 10px; color: #cbd9ff; font-size: 12.5px; }
    .fk-hbar-track { height: 8px; border-radius: 999px; background: rgba(120,160,255,.1); overflow: hidden; }
    .fk-hbar-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--dash-cyan), var(--dash-sky)); }
    .fk-donut { width: 160px; height: 160px; border-radius: 50%; margin: 18px auto; background: conic-gradient(var(--dash-green) 0 65%, var(--dash-red) 65% 83%, var(--dash-yellow) 83% 100%); display: grid; place-items: center; box-shadow: 0 0 24px rgba(18,209,142,.14); }
    .fk-donut::after { content: attr(data-label); width: 96px; height: 96px; border-radius: 50%; background: #071634; border: 1px solid var(--dash-border); display: grid; place-items: center; color: #fff; font-family: Sora, Inter, sans-serif; font-weight: 800; }
    .fk-list { padding: 13px 16px; display: grid; gap: 12px; }
    .fk-list-row { display: flex; align-items: center; gap: 10px; padding: 5px 2px; border-radius: 11px; }
    .fk-list-row:hover { background: rgba(34,211,238,.08); }
    .fk-rank { width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; font-family: Sora, Inter, sans-serif; font-size: 10.5px; font-weight: 800; border: 1px solid rgba(255,181,71,.38); background: rgba(255,181,71,.1); color: var(--dash-yellow); }
    .fk-avatar-mini { width: 28px; height: 28px; border-radius: 10px; display: grid; place-items: center; color: #061125; font-family: Sora, Inter, sans-serif; font-size: 11px; font-weight: 800; background: linear-gradient(135deg, var(--dash-cyan), var(--dash-blue)); }
    .fk-map { height: 86px; border-radius: 12px; margin-bottom: 12px; overflow: hidden; background: radial-gradient(circle at 30% 30%, rgba(34,211,238,.14), transparent 60%), repeating-linear-gradient(0deg, rgba(120,160,255,.07) 0 1px, transparent 1px 18px), repeating-linear-gradient(90deg, rgba(120,160,255,.07) 0 1px, transparent 1px 18px), #071634; position: relative; }
    .fk-map i { position: absolute; width: 7px; height: 7px; border-radius: 50%; background: var(--dash-cyan); animation: fkMapPulse 2.6s ease-out infinite; }
    @keyframes fkDashPulse { 50% { opacity: .25; } }
    @keyframes fkDashRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    @keyframes fkMapPulse { 0% { box-shadow: 0 0 0 0 rgba(34,211,238,.5); } 70% { box-shadow: 0 0 0 10px rgba(34,211,238,0); } 100% { box-shadow: 0 0 0 0 rgba(34,211,238,0); } }
    @keyframes fkDashSweep { from { transform: translateX(-70%); } to { transform: translateX(110%); } }
    @media (max-width: 767px) {
        .fk-dashboard { padding: 16px 12px 32px; }
        .fk-dash-titlebar { display: block; }
        .fk-dash-actions { margin: 12px 0 0; }
        .fk-dash-banner { height: auto; }
        .fk-dash-slide { position: relative; display: none; grid-template-columns: 1fr; min-height: 260px; }
        .fk-dash-slide.is-active { display: grid; }
        .fk-dash-banner-media { min-height: 120px; border-right: 0; border-bottom: 1px solid rgba(90,130,220,.14); }
        .fk-dash-banner-body { padding: 22px 18px 54px; }
        .fk-dash-charts { grid-template-columns: 1fr; }
    }
</style>

<div class="fk-dashboard">
    <div class="fk-dash-shell">
        <div class="fk-dash-breadcrumb">
            <span>Dashboard</span><span>›</span><b>Sales Summary</b>
        </div>

        <div class="fk-dash-titlebar">
            <div>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                    <h1>Sales Summary</h1>
                    <span class="fk-dash-live"><i></i>LIVE</span>
                    <span class="fk-dash-pill">{{ $dashboard['today'] }}</span>
                </div>
                <div class="fk-dash-subtitle">Every number that runs your field business — one view.</div>
            </div>
            <div class="fk-dash-actions">
                <a class="fk-dash-icon-btn" href="{{ url('dashboard') }}" title="Refresh data"><span class="material-icons">refresh</span></a>
                <a class="fk-dash-icon-btn" href="{{ url('sales_summary_dashboard') }}" title="Open sales summary"><span class="material-icons">cloud_download</span></a>
            </div>
        </div>

        <section class="fk-dash-banner" id="fkDashboardHeroSlider">
            @foreach($dashboard['slides'] as $index => $slide)
                <article class="fk-dash-slide fk-slide-{{ $slide['tone'] }} {{ $index === 0 ? 'is-active' : '' }}" data-dashboard-slide="{{ $index }}">
                    <div class="fk-dash-banner-media">
                        <span class="fk-dash-banner-media-inner">
                            <span class="material-icons">image</span>
                            <span>{{ $slide['media'] }}</span>
                        </span>
                    </div>
                    <div class="fk-dash-banner-body">
                        <span class="fk-dash-kicker"><span class="material-icons" style="font-size:15px">{{ $slide['icon'] }}</span>{{ $slide['kicker'] }}</span>
                        <b>{{ $slide['title'] }}</b>
                        <span>{{ $slide['text'] }}</span>
                    </div>
                </article>
            @endforeach
            <div class="fk-dash-slider-dots">
                @foreach($dashboard['slides'] as $index => $slide)
                    <button type="button" class="fk-dash-slider-dot {{ $index === 0 ? 'is-active' : '' }}" data-dashboard-slide-dot="{{ $index }}" aria-label="Open dashboard slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        </section>

        <form method="GET" action="{{ url('dashboard') }}" class="fk-dash-scope" id="fkDashboardFilters">
            <span class="material-icons" style="font-size:17px;color:var(--dash-muted)">calendar_month</span>
            <input type="hidden" name="period" id="fkDashboardPeriod" value="{{ $dashboard['filters']['period'] }}">
            <button type="button" class="fk-dash-chip {{ $dashboard['filters']['period'] === 'mtd' ? 'active' : '' }}" data-dashboard-period="mtd">MTD</button>
            <button type="button" class="fk-dash-chip {{ $dashboard['filters']['period'] === 'ytd' ? 'active' : '' }}" data-dashboard-period="ytd">YTD</button>
            <button type="button" class="fk-dash-chip {{ $dashboard['filters']['period'] === 'custom' ? 'active' : '' }}" data-dashboard-period="custom">Custom Range</button>
            <span class="fk-dash-custom-fields {{ $dashboard['filters']['period'] === 'custom' ? 'is-visible' : '' }}" id="fkDashboardCustomDates">
                <input class="fk-dash-date" type="date" name="from_date" value="{{ $dashboard['filters']['from_date'] }}">
                <input class="fk-dash-date" type="date" name="to_date" value="{{ $dashboard['filters']['to_date'] }}">
                <button type="submit" class="fk-dash-chip active">Apply</button>
            </span>
            <span style="width:1px;align-self:stretch;background:var(--dash-border)"></span>
            <select class="fk-dash-select" name="zone_id" onchange="this.form.submit()">
                <option value="">All Zones</option>
                @foreach($dashboard['filters']['zones'] as $zone)
                    <option value="{{ $zone->id }}" {{ (string) $dashboard['filters']['zone_id'] === (string) $zone->id ? 'selected' : '' }}>{{ $zone->division_name }}</option>
                @endforeach
            </select>
            <select class="fk-dash-select" name="state_id" onchange="this.form.submit()">
                <option value="">All States</option>
                @foreach($dashboard['filters']['states'] as $state)
                    <option value="{{ $state->id }}" {{ (string) $dashboard['filters']['state_id'] === (string) $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>
                @endforeach
            </select>
            <select class="fk-dash-select" name="user_id" onchange="this.form.submit()">
                <option value="">All Users</option>
                @foreach($dashboard['filters']['users'] as $user)
                    <option value="{{ $user->id }}" {{ (string) $dashboard['filters']['user_id'] === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <span style="flex:1"></span>
            <span style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11px;letter-spacing:1px;color:var(--dash-muted);text-transform:uppercase">{{ $dashboard['periodLabel'] }}</span>
        </form>

        <div class="fk-dash-grid fk-dash-kpis">
            @foreach($dashboard['kpis'] as $kpi)
                <a href="{{ $kpi['url'] }}" class="fk-dash-card fk-tone-{{ $kpi['tone'] }}">
                    <span class="fk-dash-card-head">
                        <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">{{ $kpi['icon'] }}</span></span>
                        <span class="fk-dash-label">{{ $kpi['label'] }}</span>
                        <span class="fk-dash-delta">{{ $kpi['delta'] }}</span>
                    </span>
                    <span class="fk-dash-value">{{ $kpi['value'] }}</span>
                    @if(!empty($kpi['subs']))
                        <span class="fk-dash-substats">
                            @foreach($kpi['subs'] as $sub)
                                <span class="fk-dash-substat"><i class="fk-dot-{{ $sub['tone'] }}"></i><b>{{ $sub['value'] }}</b>{{ $sub['label'] }}</span>
                            @endforeach
                        </span>
                    @endif
                    <span class="fk-dash-meta">{{ $kpi['meta'] }}</span>
                    @if(isset($kpi['progress']))
                        <span class="fk-progress"><span style="width:{{ $kpi['progress'] }}%"></span></span>
                    @else
                        <svg viewBox="0 0 150 34" width="100%" height="34" preserveAspectRatio="none">
                            <polyline points="{{ $sparkPoints($kpi['spark'] ?? []) }}" fill="none" stroke="var(--dash-cyan)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="fk-section-title">Customer Type Breakdown</div>
        <div class="fk-dash-grid fk-dash-types">
            @foreach($dashboard['types'] as $type)
                <div class="fk-dash-card fk-tone-{{ $type['tone'] }}">
                    <span class="fk-dash-card-head">
                        <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">{{ $type['icon'] }}</span></span>
                        <span>
                            <b style="display:block;color:#fff;font-family:Sora,Inter,sans-serif;font-size:15px">{{ $type['label'] }}</b>
                            <span style="font-size:10.5px;color:var(--dash-muted)">of all customers · {{ $type['share'] }}% share</span>
                        </span>
                        <span class="fk-dash-value" style="margin-left:auto;font-size:24px">{{ $type['total'] }}</span>
                    </span>
                    <span class="fk-progress"><span style="width:{{ $type['pct'] }}%"></span></span>
                    <span class="fk-dash-substats">
                        <span class="fk-dash-substat"><i class="fk-dot-success"></i><b>{{ $type['active'] }}</b>Active</span>
                        <span class="fk-dash-substat"><i></i><b>{{ $type['inactive'] }}</b>Inactive</span>
                        <span style="margin-left:auto" class="fk-dash-chip">{{ $type['pct'] }}% active</span>
                    </span>
                </div>
            @endforeach
        </div>

        <div class="fk-section-title">Performance Analytics</div>
        <div class="fk-dash-grid fk-dash-charts">
            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">trending_up</span></span>
                    <span class="fk-dash-chart-title"><b>Primary Sales Trend</b><span>₹ Lakh · last 6 months</span></span>
                    <span class="fk-dash-chart-badge">{{ end($dashboard['charts']['primary']) ?: 0 }} L</span>
                </div>
                <div class="fk-chart-pad">
                    <svg class="fk-line-chart" viewBox="0 0 560 220" preserveAspectRatio="none">
                        @foreach([40, 85, 130, 175] as $y)
                            <line class="fk-line-grid" x1="0" y1="{{ $y }}" x2="560" y2="{{ $y }}"></line>
                        @endforeach
                        <polyline class="fk-line-path" points="{{ $linePoints($dashboard['charts']['primary']) }}"></polyline>
                    </svg>
                </div>
            </section>

            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">storefront</span></span>
                    <span class="fk-dash-chart-title"><b>Secondary Sales vs Primary</b><span>₹ Lakh · comparative movement</span></span>
                    <span class="fk-dash-chart-badge">6 months</span>
                </div>
                <div class="fk-bars">
                    @php $maxBar = max(max($dashboard['charts']['primary'] ?: [1]), max($dashboard['charts']['secondary'] ?: [1]), 1); @endphp
                    @foreach($dashboard['charts']['labels'] as $index => $label)
                        <div class="fk-bar">
                            <div class="fk-bar-stack">
                                <span class="fk-bar-fill" style="height:{{ (($dashboard['charts']['primary'][$index] ?? 0) / $maxBar) * 100 }}%"></span>
                                <span class="fk-bar-fill secondary" style="height:{{ (($dashboard['charts']['secondary'][$index] ?? 0) / $maxBar) * 100 }}%"></span>
                            </div>
                            <label>{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">badge</span></span>
                    <span class="fk-dash-chart-title"><b>Attendance Overview</b><span>Today · all field teams</span></span>
                    <span class="fk-dash-chart-badge">{{ $present }} present</span>
                </div>
                <div class="fk-chart-pad">
                    <div class="fk-donut" data-label="On Roll"></div>
                    <div class="fk-dash-substats" style="justify-content:center">
                        <span class="fk-dash-substat"><i class="fk-dot-success"></i>Present</span>
                        <span class="fk-dash-substat"><i class="fk-dot-danger"></i>Absent</span>
                        <span class="fk-dash-substat"><i class="fk-dot-warning"></i>Mis Punch</span>
                    </div>
                </div>
            </section>

            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">inventory_2</span></span>
                    <span class="fk-dash-chart-title"><b>Product Performance</b><span>Top SKUs by primary sales</span></span>
                    <span class="fk-dash-chart-badge">₹ Lakh</span>
                </div>
                <div class="fk-chart-pad">
                    <div class="fk-hbar">
                        @forelse($dashboard['charts']['products'] as $product)
                            <div class="fk-hbar-row">
                                <span>{{ $product['label'] }}</span>
                                <span class="fk-hbar-track"><span style="width:{{ (($product['value'] ?? 0) / $maxProduct) * 100 }}%"></span></span>
                                <b>{{ $product['value'] }} L</b>
                            </div>
                        @empty
                            <div class="fk-dash-meta">No product sales available.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <div class="fk-section-title">Business Snapshot</div>
        <div class="fk-dash-grid fk-dash-small">
            @foreach($dashboard['mini'] as $mini)
                <div class="fk-dash-card">
                    <span class="fk-dash-label">{{ $mini['label'] }}</span>
                    <span class="fk-dash-value" style="font-size:23px">{{ $mini['value'] }}</span>
                    <span class="fk-dash-meta">{{ $mini['meta'] }}</span>
                    <svg viewBox="0 0 150 34" width="100%" height="34" preserveAspectRatio="none">
                        <polyline points="{{ $sparkPoints($mini['spark'] ?? []) }}" fill="none" stroke="var(--dash-cyan)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </div>
            @endforeach
        </div>

        <div class="fk-dash-grid fk-dash-bottom">
            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">military_tech</span></span>
                    <span class="fk-dash-chart-title"><b>Top Performing Employees</b><span>{{ $dashboard['periodLabel'] }} · by primary sales</span></span>
                </div>
                <div class="fk-list">
                    @forelse($dashboard['topEmployees'] as $index => $employee)
                        <div class="fk-list-row">
                            <span class="fk-rank">{{ $index + 1 }}</span>
                            <span class="fk-avatar-mini">{{ collect(explode(' ', $employee['name']))->map(fn($part) => substr($part, 0, 1))->take(2)->implode('') }}</span>
                            <span style="flex:1;min-width:0">
                                <span style="display:block;font-size:12.5px;font-weight:700;color:#e8f0ff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $employee['name'] }}</span>
                                <span class="fk-progress" style="display:block;margin-top:4px;height:4px"><span style="width:{{ (($employee['value'] ?? 0) / $maxEmployee) * 100 }}%"></span></span>
                            </span>
                            <b style="font-family:Sora,Inter,sans-serif;font-size:12.5px;color:#fff">{{ $employee['value'] }} L</b>
                        </div>
                    @empty
                        <div class="fk-dash-meta">No employee sales available.</div>
                    @endforelse
                </div>
            </section>

            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">notifications</span></span>
                    <span class="fk-dash-chart-title"><b>Active Alerts</b><span>Needs attention today</span></span>
                    <span class="fk-dash-chart-badge">{{ count($dashboard['alerts']) }}</span>
                </div>
                <div class="fk-list">
                    @foreach($dashboard['alerts'] as $alert)
                        <div class="fk-list-row">
                            <span class="fk-dash-substat"><i class="fk-dot-{{ $alert['tone'] }}"></i></span>
                            <span class="material-icons" style="font-size:17px;color:var(--dash-{{ $alert['tone'] === 'danger' ? 'red' : ($alert['tone'] === 'warning' ? 'yellow' : 'sky') }})">{{ $alert['icon'] }}</span>
                            <span style="flex:1;font-size:12.5px;color:#e8f0ff">{{ $alert['text'] }}</span>
                            <span class="material-icons" style="font-size:15px;color:var(--dash-muted)">chevron_right</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="fk-dash-chart">
                <div class="fk-dash-chart-head">
                    <span class="fk-dash-icon"><span class="material-icons" style="font-size:18px">person_pin_circle</span></span>
                    <span class="fk-dash-chart-title"><b>Customer Spread</b><span>By state · all customer types</span></span>
                </div>
                <div class="fk-chart-pad">
                    <div class="fk-map">
                        <i style="left:30%;top:34%"></i><i style="left:55%;top:22%;animation-delay:.4s"></i><i style="left:42%;top:58%;animation-delay:.8s"></i><i style="left:68%;top:62%;animation-delay:1.2s"></i><i style="left:20%;top:66%;animation-delay:1.6s"></i>
                    </div>
                    <div class="fk-hbar">
                        @forelse($dashboard['regions'] as $region)
                            <div class="fk-hbar-row">
                                <span>{{ $region['name'] }}</span>
                                <span class="fk-hbar-track"><span style="width:{{ $region['pct'] }}%"></span></span>
                                <b>{{ $region['value'] }}</b>
                            </div>
                        @empty
                            <div class="fk-dash-meta">No customer state mapping available.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('fkDashboardFilters');
        const periodInput = document.getElementById('fkDashboardPeriod');
        const customDates = document.getElementById('fkDashboardCustomDates');

        if (!form || !periodInput || !customDates) {
            return;
        }

        document.querySelectorAll('[data-dashboard-period]').forEach(function (button) {
            button.addEventListener('click', function () {
                const period = button.getAttribute('data-dashboard-period');
                periodInput.value = period;

                if (period === 'custom') {
                    customDates.classList.add('is-visible');
                    return;
                }

                form.submit();
            });
        });

        const slides = Array.from(document.querySelectorAll('[data-dashboard-slide]'));
        const dots = Array.from(document.querySelectorAll('[data-dashboard-slide-dot]'));
        let activeSlide = 0;
        let sliderTimer = null;

        function showDashboardSlide(index) {
            if (!slides.length) {
                return;
            }

            activeSlide = (index + slides.length) % slides.length;
            slides.forEach(function (slide, slideIndex) {
                slide.classList.toggle('is-active', slideIndex === activeSlide);
            });
            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === activeSlide);
            });
        }

        function startDashboardSlider() {
            if (sliderTimer || slides.length < 2) {
                return;
            }

            sliderTimer = window.setInterval(function () {
                showDashboardSlide(activeSlide + 1);
            }, 5200);
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                window.clearInterval(sliderTimer);
                sliderTimer = null;
                showDashboardSlide(parseInt(dot.getAttribute('data-dashboard-slide-dot'), 10) || 0);
                startDashboardSlider();
            });
        });

        startDashboardSlider();
    });
</script>
</x-app-layout>
