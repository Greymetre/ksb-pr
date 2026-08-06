<x-app-layout>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}" type="text/javascript"></script>
    <style>
        .live-location-page { margin-top: 0 !important; }
        .live-location-page .live-location-card {
            margin-top: 0 !important;
            border: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)) !important;
            border-radius: 14px !important;
            background: var(--fk-list-panel, #0b1730) !important;
            box-shadow: none !important;
        }
        .live-location-page .live-location-card > .card-body { padding: 20px !important; }
        .live-location-page .location-filter-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            align-items: end;
            gap: 14px;
            margin: 0;
        }
        .live-location-page .location-filter-field,
        .live-location-page .location-date-field,
        .live-location-page .location-action-field { width: auto; max-width: none; padding: 0; }
        .live-location-page .location-filter-field { grid-column: span 3; }
        .live-location-page .location-date-field { grid-column: span 2; align-self: center; }
        .live-location-page .location-action-field { grid-column: span 8; align-self: center; }
        .live-location-page .location-filter-form .form-group,
        .live-location-page .location-filter-form .bmd-form-group { margin: 0 !important; padding: 0 !important; }
        .live-location-page .location-filter-form .bootstrap-select,
        .live-location-page .location-filter-form .select2-container { width: 100% !important; }
        body.fk-shell .live-location-page .location-filter-form .form-control,
        body.fk-shell .live-location-page .location-filter-form .bootstrap-select > .dropdown-toggle,
        body.fk-shell .live-location-page .location-filter-form .bootstrap-select > .location-select-control,
        body.fk-shell .live-location-page .location-filter-form .bootstrap-select.show > .dropdown-toggle,
        body.fk-shell .live-location-page .location-filter-form .bootstrap-select > .dropdown-toggle:focus,
        body.fk-shell .live-location-page .location-filter-form .bootstrap-select > .dropdown-toggle:active,
        body.fk-shell .live-location-page .location-filter-form .select2-selection--single {
            height: 44px !important;
            min-height: 44px !important;
            border: 1px solid var(--fk-list-border-strong, rgba(90, 130, 220, .34)) !important;
            border-radius: 10px !important;
            background: rgba(5, 14, 36, .72) !important;
            color: var(--fk-list-soft, #c8d5ea) !important;
            box-shadow: none !important;
            background-image: none !important;
        }
        body.fk-shell .live-location-page .location-filter-form .bootstrap-select .filter-option-inner-inner,
        body.fk-shell .live-location-page .location-filter-form .select2-selection__rendered {
            color: var(--fk-list-soft, #c8d5ea) !important;
            line-height: 42px !important;
        }
        body.fk-shell .live-location-page .location-filter-form .btn {
            height: 44px !important;
            min-height: 44px !important;
            margin: 0 !important;
            border-radius: 10px !important;
            text-transform: none !important;
        }
        .live-location-page .location-actions {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 0;
        }
        .live-location-page .location-filter-grid.is-live-only {
            grid-template-columns: 1fr;
        }
        .live-location-page .location-filter-grid.is-live-only .location-action-field {
            grid-column: 1 / -1;
        }
        .live-location-page .location-filter-grid.is-live-only .location-actions {
            display: flex;
            justify-content: flex-start;
        }
        body.fk-shell .live-location-page .location-filter-grid.is-live-only .location-actions .btn {
            width: auto;
            min-width: 190px;
        }
        .live-location-page .location-actions.geolocator-actions {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        body.fk-shell .live-location-page .location-actions .btn {
            width: 100%;
            padding: 9px 10px !important;
            border: 1px solid rgba(90, 130, 220, .25) !important;
            background: rgba(5, 14, 36, .76) !important;
            color: var(--fk-list-heading, #f1f5ff) !important;
            box-shadow: 0 7px 16px rgba(0, 0, 0, .16) !important;
            font-size: 10px !important;
            font-weight: 600 !important;
            white-space: nowrap;
        }
        body.fk-shell .live-location-page .location-actions .btn:hover,
        body.fk-shell .live-location-page .location-actions .btn:focus,
        body.fk-shell .live-location-page .location-actions .btn:active,
        body.fk-shell .live-location-page .location-actions .btn.active {
            border-color: rgba(34, 211, 238, .46) !important;
            background: rgba(13, 35, 67, .94) !important;
            color: #fff !important;
            box-shadow: 0 7px 18px rgba(0, 0, 0, .2) !important;
        }
        body.fk-shell .live-location-page .location-actions .all-users-location-btn {
            border: 1px solid rgba(34, 211, 238, .38) !important;
            background: rgba(34, 211, 238, .08) !important;
            color: var(--fk-list-accent, #22d3ee) !important;
            font-size: 9px !important;
            letter-spacing: .1px;
        }
        .live-location-page .location-actions .all-users-location-btn .material-icons { font-size: 14px !important; }
        .live-location-page .location-workspace {
            display: none;
            margin: 20px 0 0 !important;
            padding: 0 !important;
            overflow: hidden;
            border: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22));
            border-radius: 14px;
            background: rgba(5, 14, 36, .62);
        }
        .live-location-page .location-workspace.is-visible { display: flex; }
        .live-location-page .live-users-panel {
            height: 520px;
            padding: 0 !important;
            overflow: hidden;
            border-right: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22));
            background: rgba(6, 17, 39, .94);
        }
        .live-location-page .live-users-head { padding: 15px; border-bottom: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)); }
        .live-location-page .live-users-title-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .live-location-page .live-users-title { margin: 0; color: var(--fk-list-heading, #f1f5ff); font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .7px; }
        .live-location-page .live-users-count { padding: 5px 8px; border: 1px solid rgba(34, 211, 238, .28); border-radius: 7px; background: rgba(34, 211, 238, .08); color: #67e8f9; font-size: 9px; font-weight: 800; }
        .live-location-page .live-users-search-wrap { position: relative; margin-top: 12px; }
        .live-location-page .live-users-search-wrap .material-icons { position: absolute; top: 50%; left: 11px; transform: translateY(-50%); color: var(--fk-list-dim, #8291ad); font-size: 16px; }
        body.fk-shell .live-location-page .live-users-search {
            width: 100%; height: 38px !important; min-height: 38px !important; padding: 0 12px 0 34px !important;
            border: 1px solid rgba(90, 130, 220, .26) !important; border-radius: 9px !important;
            background: rgba(5, 14, 36, .72) !important; color: var(--fk-list-heading, #f1f5ff) !important; box-shadow: none !important;
        }
        .live-location-page .live-users-filters { display: flex; gap: 6px; margin-top: 10px; }
        body.fk-shell .live-location-page .live-user-filter {
            min-height: 28px !important; height: 28px !important; padding: 0 9px !important;
            border: 1px solid rgba(90, 130, 220, .24) !important; border-radius: 999px !important;
            background: transparent !important; color: var(--fk-list-dim, #8291ad) !important; box-shadow: none !important; font-size: 9px !important;
        }
        body.fk-shell .live-location-page .live-user-filter.active { border-color: rgba(34, 211, 238, .45) !important; background: rgba(34, 211, 238, .1) !important; color: #67e8f9 !important; }
        .live-location-page .live-users-list { height: calc(100% - 145px); padding: 8px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(34, 211, 238, .35) transparent; }
        .live-location-page .live-user-row { display: flex; align-items: center; gap: 10px; width: 100%; margin-bottom: 7px; padding: 10px; border: 1px solid transparent; border-radius: 10px; background: transparent; cursor: pointer; transition: .18s ease; }
        .live-location-page .live-user-row:hover, .live-location-page .live-user-row.active { border-color: rgba(34, 211, 238, .25); background: rgba(34, 211, 238, .07); }
        .live-location-page .live-user-avatar { display: grid; place-items: center; flex: 0 0 34px; width: 34px; height: 34px; border: 1px solid rgba(34, 211, 238, .28); border-radius: 10px; background: rgba(34, 211, 238, .1); color: #67e8f9; font-size: 10px; font-weight: 800; }
        .live-location-page .live-user-copy { min-width: 0; flex: 1; }
        .live-location-page .live-user-name { overflow: hidden; color: var(--fk-list-heading, #f1f5ff); font-size: 12px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .live-location-page .live-user-role, .live-location-page .live-user-address { overflow: hidden; margin-top: 3px; color: var(--fk-list-dim, #8291ad); font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
        .live-location-page .live-user-status { display: inline-flex; align-items: center; gap: 4px; margin-top: 5px; color: var(--fk-list-dim, #8291ad); font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .live-location-page .live-user-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #f59e0b; }
        .live-location-page .live-user-status.is-online { color: #6ee7b7; }
        .live-location-page .live-user-status.is-online::before { background: #34d399; box-shadow: 0 0 8px rgba(52, 211, 153, .65); }
        .live-location-page .live-user-status.is-offline { color: #fda4af; }
        .live-location-page .live-user-status.is-offline::before { background: #fb7185; }
        .live-location-page .live-user-distance { margin-top: 4px; color: #22d3ee; font-size: 10px; font-weight: 700; }
        .live-location-page .live-users-empty { padding: 36px 10px; color: var(--fk-list-dim, #8291ad); font-size: 11px; text-align: center; }
        .live-location-page .gm-style .gm-style-iw-c { max-width: 390px !important; padding: 0 !important; border: 1px solid rgba(90, 130, 220, .38); border-radius: 14px; background: #0b1c3c !important; box-shadow: 0 18px 45px rgba(0, 0, 0, .36); }
        .live-location-page .gm-style .gm-style-iw-d { overflow: hidden !important; }
        .live-location-page .gm-style .gm-style-iw-tc::after { background: #0b1c3c !important; }
        .live-location-page .gm-style .gm-ui-hover-effect { filter: invert(1); opacity: .7; }
        .live-location-page .live-user-popup { width: 360px; padding: 20px; color: var(--fk-list-soft, #c8d5ea); font-family: 'Inter', sans-serif; }
        .live-location-page .live-user-popup-head { display: flex; align-items: center; gap: 12px; padding-right: 20px; }
        .live-location-page .live-user-popup-avatar { display: grid; place-items: center; flex: 0 0 48px; width: 48px; height: 48px; border: 1px dashed rgba(130, 145, 180, .55); border-radius: 50%; color: #94a3c7; font-size: 11px; }
        .live-location-page .live-user-popup-name { color: var(--fk-list-heading, #f1f5ff); font-size: 14px; font-weight: 800; }
        .live-location-page .live-user-popup-role { margin-top: 3px; color: #8ea2ce; font-size: 11px; }
        .live-location-page .live-user-popup-status { display: inline-flex; align-items: center; gap: 5px; margin-top: 7px; color: #6ee7b7; font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .live-location-page .live-user-popup-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .live-location-page .live-user-popup-status.is-offline { color: #fda4af; }
        .live-location-page .live-user-popup-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; margin-top: 16px; }
        .live-location-page .live-user-popup-metric { min-height: 72px; padding: 11px; border: 1px solid rgba(90, 130, 220, .26); border-radius: 10px; background: rgba(5, 14, 36, .28); }
        .live-location-page .live-user-popup-label { color: #7184ae; font-size: 9px; letter-spacing: .8px; text-transform: uppercase; }
        .live-location-page .live-user-popup-value { margin-top: 6px; color: var(--fk-list-heading, #f1f5ff); font-size: 13px; font-weight: 800; line-height: 1.25; }
        .live-location-page .live-user-popup-value.is-cyan { color: #22d3ee; }
        .live-location-page .live-user-popup-value.is-green { color: #34d399; }
        .live-location-page .live-user-popup-foot { margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(90, 130, 220, .25); color: #8194bf; font-size: 10px; line-height: 1.65; }
        .live-location-page .map-column { padding: 0 !important; border-right: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)); }
        .live-location-page #map { width: 100% !important; height: 520px !important; background: #071126; }
        .live-location-page .activity-column { height: 520px; padding: 0 !important; overflow: hidden; }
        .live-location-page .activity-panel-head {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: 16px 18px;
            border-bottom: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22));
            background: rgba(9, 22, 47, .97);
        }
        .live-location-page .activity-panel-head h3 { margin: 0; color: var(--fk-list-heading, #f1f5ff); font-size: 15px; font-weight: 800; }
        .live-location-page .activity-panel-head p { margin: 4px 0 0; color: var(--fk-list-dim, #8291ad); font-size: 11px; }
        .live-location-page .activity-scroll { height: calc(100% - 67px); padding: 16px 16px 20px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(34, 211, 238, .38) transparent; }
        .live-location-page #todayActivity { position: relative; margin: 0; padding: 0 0 0 42px; list-style: none; }
        .live-location-page #todayActivity::before { content: ''; position: absolute; top: 16px; bottom: 16px; left: 15px; width: 1px; background: linear-gradient(#22d3ee, rgba(90, 130, 220, .18)); }
        .live-location-page .activity-item { position: relative; margin: 0 0 14px; }
        .live-location-page .activity-marker {
            position: absolute; top: 16px; left: -42px; display: grid; place-items: center;
            width: 31px; height: 31px; border: 1px solid rgba(34, 211, 238, .36); border-radius: 9px;
            background: #0d2345; color: #22d3ee; box-shadow: 0 0 0 5px #071329;
        }
        .live-location-page .activity-marker .material-icons { font-size: 17px; }
        .live-location-page .activity-card {
            padding: 14px; border: 1px solid rgba(90, 130, 220, .2); border-radius: 11px;
            background: rgba(12, 28, 57, .9); box-shadow: 0 8px 20px rgba(0, 0, 0, .12);
        }
        .live-location-page .activity-time { display: inline-flex; padding: 5px 9px; border-radius: 7px; background: rgba(34, 211, 238, .11); color: #67e8f9; font-size: 10px; font-weight: 800; letter-spacing: .5px; }
        .live-location-page .activity-title { margin: 11px 0 5px; color: var(--fk-list-heading, #f1f5ff); font-size: 14px; font-weight: 800; }
        .live-location-page .activity-customer { margin: 0; color: var(--fk-list-soft, #c8d5ea); font-size: 12px; line-height: 1.45; }
        .live-location-page .activity-meta { margin: 6px 0 0; color: var(--fk-list-dim, #8291ad); font-size: 10px; line-height: 1.45; }
        .live-location-page .activity-location-btn { display: inline-flex; align-items: center; gap: 5px; min-height: 30px; margin: 12px 0 0 !important; padding: 6px 9px !important; border: 1px solid rgba(34, 211, 238, .35) !important; border-radius: 7px !important; background: rgba(34, 211, 238, .08) !important; color: #67e8f9 !important; box-shadow: none !important; font-size: 10px !important; text-transform: none !important; }
        .live-location-page .activity-location-btn .material-icons { font-size: 14px; }
        .live-location-page .activity-state { padding: 36px 12px; color: var(--fk-list-dim, #8291ad); text-align: center; font-size: 12px; }
        @media (max-width: 991px) {
            .live-location-page .location-filter-field,
            .live-location-page .location-date-field { grid-column: span 6; }
            .live-location-page .location-action-field { grid-column: span 12; }
            .live-location-page .map-column { border-right: 0; border-bottom: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)); }
            .live-location-page .live-users-panel { height: 360px; border-right: 0; border-bottom: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)); }
            .live-location-page #map, .live-location-page .activity-column { height: 430px !important; }
            .live-location-page .location-actions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575px) {
            .live-location-page .location-filter-field,
            .live-location-page .location-date-field { grid-column: span 12; }
            .live-location-page .location-actions { grid-template-columns: 1fr; }
        }
    </style>
    <div class="row mt-4 live-location-page">
        <div class="col-lg-12">
            <div class="fk-list-page-head">
                <div class="fk-list-heading-block">
                    <div class="fk-list-breadcrumb"><span>CRM</span><span>&rsaquo;</span><span class="fk-current">{{ $locationMode === 'live' ? 'USER LIVE LOCATION' : 'GEOLOCATOR' }}</span></div>
                    <div class="fk-list-title-row"><h1 class="fk-list-title">{{ $locationMode === 'live' ? 'User Live Location' : 'Geolocator' }}</h1></div>
                </div>
            </div>
            <div class="card mt-4 live-location-card" data-animation="true">
                <div class="card-body">
                    @if(session()->has('message_success'))
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        <span>
                            {{ session()->get('message_success') }}
                        </span>
                    </div>
                    @endif
                    @if(count($errors) > 0)
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        <span>
                            @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </span>
                    </div>
                    @endif
                    <form target="_blank" method="post" action="{{url('map-all')}}" class="location-filter-form" novalidate onsubmit="return validateLocationSubmit(event)">
                        @csrf
                        <div class="location-filter-grid {{ $locationMode === 'live' ? 'is-live-only' : '' }}">
                            @if($locationMode === 'geolocator')
                            <div class="location-filter-field">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" multiple id="branch_id" name="branch_id" data-style="location-select-control" title="Choose Branch" data-size="10" tabindex="-98">
                                        <option disabled=""> Select Branch</option>
                                        @if(@isset($branches ))
                                        @foreach($branches as $branch)
                                        <option value="{!! $branch['id'] !!}">{!! $branch['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="location-filter-field">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" multiple id="division_id" name="division_id" data-style="location-select-control" title="Choose Zone" data-size="10" tabindex="-98">
                                        <option disabled=""> Select Zone</option>
                                        @if(@isset($divisions ))
                                        @foreach($divisions as $division)
                                        <option value="{!! $division['id'] !!}">{!! $division['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="location-filter-field">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" multiple id="department_id" name="department_id" data-style="location-select-control" title="Choose Department" data-size="10" tabindex="-98">
                                        <option disabled=""> Select Department</option>
                                        @if(@isset($departments ))
                                        @foreach($departments as $department)
                                        <option value="{!! $department['id'] !!}">{!! $department['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="location-filter-field">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="select2" id="user_id" name="user_id" title="Choose User" data-size="10" tabindex="-98" required>
                                        <option disabled="" selected> Select Users</option>
                                        @if(@isset($users ))
                                        @foreach($users as $user)
                                        <option {{(!empty($user_id) && $user_id == $user['id'])?'selected':''}} value="{!! $user['id'] !!}">{!! $user['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="location-date-field">
                                <div class="form-group has-default bmd-form-group">
                                    <input type="text" class="form-control datepicker" id="date" required name="date" value="{{ old('date', !empty($date) ? $date : \Carbon\Carbon::today()->format('Y-m-d')) }}" placeholder="Date From" autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="location-date-field">
                                <div class="form-group has-default bmd-form-group">
                                    <input type="text" class="form-control datepicker" id="to_date" required name="to_date"
                                        value="{{ old('to_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" placeholder="Date To"
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            @endif
                            <div class="location-action-field">
                              <div class="location-actions {{ $locationMode === 'geolocator' ? 'geolocator-actions' : '' }}">
                                @if($locationMode === 'live')
                                <button type="button" class="btn btn-sm location-action-btn all-users-location-btn" onclick="getAllUsersLiveLocations()">
                                    <i class="material-icons mr-1">groups</i> User Live Location
                                </button>
                                @else
                                <button type="button" class="btn btn-sm location-action-btn" onclick="getActivityData()">Activity Detailed</button>
                                <input type="submit" name="submit" class="btn btn-sm location-action-btn" value="Complete Map Activity">
                                <input type="submit" name="submit" class="btn btn-sm location-action-btn" value="Track Activity">
                                @endif
                              </div>
                            </div>
                        </div>
                    </form>
                    <div class="row location-workspace" id="locationWorkspace">
                        <aside class="col-lg-4 live-users-panel d-none" id="liveUsersPanel">
                            <div class="live-users-head">
                                <div class="live-users-title-row">
                                    <h3 class="live-users-title">Field team</h3>
                                    <span class="live-users-count" id="liveUsersCount">0 records</span>
                                </div>
                                <div class="live-users-search-wrap">
                                    <i class="material-icons">search</i>
                                    <input type="search" class="form-control live-users-search" id="liveUsersSearch" placeholder="Search employee…" autocomplete="off">
                                </div>
                                <div class="live-users-filters">
                                    <button type="button" class="btn live-user-filter active" data-status="all">All</button>
                                    <button type="button" class="btn live-user-filter" data-status="Online">Online</button>
                                    <button type="button" class="btn live-user-filter" data-status="Offline">Offline</button>
                                    <button type="button" class="btn live-user-filter" data-status="GPS Off">GPS Off</button>
                                </div>
                            </div>
                            <div class="live-users-list" id="liveUsersList"></div>
                        </aside>
                        <div class="col-lg-7 map-column" id="mapColumn">
                            <div id="map"></div>
                        </div>
                        <div class="col-lg-5 activity-column" id="activityColumn">
                            <div class="activity-panel-head">
                                <h3>Activity details</h3>
                                <p>Click a location to focus it on the map.</p>
                            </div>
                            <div class="activity-scroll"><ul id="todayActivity"></ul></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var liveUsersMap = null;
        var liveUsersInfoWindow = null;
        var liveUserLocations = [];
        var liveUserMarkers = [];
        var liveUserStatusFilter = 'all';

        $(document).ready(function() {
            $('#loader').hide();
            $('#liveUsersSearch').on('input', applyLiveUserFilters);
            $('.live-user-filter').on('click', function() {
                liveUserStatusFilter = $(this).data('status');
                $('.live-user-filter').removeClass('active');
                $(this).addClass('active');
                applyLiveUserFilters();
            });
        })

        function showLocationWorkspace(showActivityDetails) {
            $('#locationWorkspace').addClass('is-visible');
            $('#activityColumn').toggleClass('d-none', !showActivityDetails);
            $('#liveUsersPanel').toggleClass('d-none', showActivityDetails);
            $('#mapColumn')
                .toggleClass('col-lg-7', showActivityDetails)
                .toggleClass('col-lg-8', !showActivityDetails)
                .removeClass('col-lg-12');
        }

        function showLocationAlert(message) {
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Selection required',
                    text: message,
                    confirmButtonText: 'OK'
                });
            } else {
                window.alert(message);
            }
        }

        function validateLocationFilters(requireDates) {
            var userId = $('#user_id').val();
            var fromDate = $('#date').val();
            var toDate = $('#to_date').val();

            if (!userId) {
                showLocationAlert('Please select a user before continuing.');
                return false;
            }
            if (requireDates && (!fromDate || !toDate)) {
                showLocationAlert('Please select both From Date and To Date.');
                return false;
            }
            if (requireDates && new Date(fromDate) > new Date(toDate)) {
                showLocationAlert('From Date cannot be later than To Date.');
                return false;
            }
            return true;
        }

        function validateLocationSubmit(event) {
            var submitter = event.submitter || document.activeElement;
            var action = submitter ? submitter.value : '';

            if (action === 'Track Activity') {
                return validateLocationFilters(false);
            }
            if (action === 'Complete Map Activity') {
                return validateLocationFilters(true);
            }

            showLocationAlert('Please choose a location action.');
            return false;
        }

        function getAllUsersLiveLocations() {
            showLocationWorkspace(false);
            var $button = $('.all-users-location-btn');
            var originalContent = $button.html();
            $button.prop('disabled', true).html('<i class="material-icons mr-1" style="font-size:16px">sync</i> Loading…');

            $.ajax({
                url: "{{ route('livelocation.all-users') }}",
                dataType: 'json',
                type: 'GET',
                success: function(response) {
                    renderAllUsersMap(response.locations || []);
                },
                error: function() {
                    $('#map').html('<div class="activity-state">Unable to load live locations. Please try again.</div>');
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalContent);
                }
            });
        }

        function renderAllUsersMap(locations) {
            liveUserLocations = locations;
            var mappedLocations = locations.filter(function(location) {
                var lat = parseFloat(location.latitude);
                var lng = parseFloat(location.longitude);
                return Number.isFinite(lat) && Number.isFinite(lng);
            });

            if (!locations.length) {
                $('#map').html('<div class="activity-state">No user locations have been reported today.</div>');
                $('#liveUsersList').html('<div class="live-users-empty">No employees have reported a location today.</div>');
                $('#liveUsersCount').text('0 records');
                return;
            }

            liveUsersMap = new google.maps.Map(document.getElementById('map'), {
                zoom: 6,
                center: { lat: 20.5937, lng: 78.9629 },
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapTypeControl: false,
                streetViewControl: false
            });
            var bounds = new google.maps.LatLngBounds();
            liveUsersInfoWindow = new google.maps.InfoWindow();
            liveUserMarkers = [];

            mappedLocations.forEach(function(location) {
                var position = { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) };
                bounds.extend(position);
                var marker = new google.maps.Marker({
                    map: liveUsersMap,
                    position: position,
                    title: location.name || 'User',
                    icon: makeLiveUserMarkerIcon(location.status)
                });
                location.marker = marker;
                marker.addListener('click', function() {
                    focusLiveUser(location.user_id, false);
                });
                liveUserMarkers.push(marker);
            });

            renderLiveUsersList(liveUserLocations);
            if (!mappedLocations.length) {
                $('#map').html('<div class="activity-state">No valid user locations are available.</div>');
            } else if (mappedLocations.length === 1) {
                liveUsersMap.setCenter(bounds.getCenter());
                liveUsersMap.setZoom(14);
            } else {
                liveUsersMap.fitBounds(bounds, 50);
            }
        }

        function makeLiveUserMarkerIcon(status) {
            var color = status === 'Online' ? '#2fbd3b' : '#fb7185';
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="38" height="50" viewBox="0 0 38 50">' +
                '<defs><filter id="s" x="-60%" y="-30%" width="220%" height="190%"><feDropShadow dx="3" dy="5" stdDeviation="3" flood-color="#000" flood-opacity=".45"/></filter></defs>' +
                '<path filter="url(#s)" d="M19 1C9.1 1 1 9.1 1 19c0 13.3 18 29 18 29s18-15.7 18-29C37 9.1 28.9 1 19 1z" fill="' + color + '" stroke="#168a24" stroke-width="2"/>' +
                '<circle cx="19" cy="18" r="6.5" fill="#fff" stroke="rgba(0,0,0,.18)" stroke-width="2"/></svg>';
            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(30, 40),
                anchor: new google.maps.Point(15, 38)
            };
        }

        function getLiveUserInitials(name) {
            return (name || 'User').split(/\s+/).slice(0, 2).map(function(part) {
                return part.charAt(0).toUpperCase();
            }).join('');
        }

        function renderLiveUsersList(locations) {
            var $list = $('#liveUsersList').empty();
            $('#liveUsersCount').text(locations.length + (locations.length === 1 ? ' record' : ' records'));
            if (!locations.length) {
                $list.append('<div class="live-users-empty">No employees match the current filter.</div>');
                return;
            }

            locations.forEach(function(location) {
                var $row = $('<div>', { class: 'live-user-row', 'data-user-id': location.user_id });
                var $avatar = $('<div>', { class: 'live-user-avatar', text: getLiveUserInitials(location.name) });
                var $copy = $('<div>', { class: 'live-user-copy' });
                $copy.append($('<div>', { class: 'live-user-name', text: location.name || 'Unknown user' }));
                $copy.append($('<div>', { class: 'live-user-role', text: location.designation || 'Field employee' }));
                $copy.append($('<div>', { class: 'live-user-distance', text: Number(location.distance_km || 0).toFixed(1) + ' km travelled today' }));
                $copy.append($('<div>', {
                    class: 'live-user-status is-' + String(location.status || 'gps-off').toLowerCase().replace(/\s+/g, '-'),
                    text: location.status || 'GPS Off'
                }));
                $row.append($avatar, $copy).on('click', function() {
                    focusLiveUser(location.user_id, true);
                });
                $list.append($row);
            });
        }

        function focusLiveUser(userId, moveMap) {
            var location = liveUserLocations.find(function(item) {
                return String(item.user_id) === String(userId);
            });
            if (!location) return;

            $('.live-user-row').removeClass('active');
            $('.live-user-row[data-user-id="' + userId + '"]').addClass('active');
            if (!location.marker || !liveUsersMap) {
                showLocationAlert('GPS location is not available for this user.');
                return;
            }
            if (moveMap) {
                liveUsersMap.panTo(location.marker.getPosition());
                liveUsersMap.setZoom(14);
            }

            var content = buildLiveUserPopup(location);
            liveUsersInfoWindow.setContent(content);
            liveUsersInfoWindow.open(liveUsersMap, location.marker);
        }

        function buildLiveUserPopup(location) {
            var popup = $('<div>', { class: 'live-user-popup' });
            var head = $('<div>', { class: 'live-user-popup-head' });
            head.append($('<div>', { class: 'live-user-popup-avatar', text: getLiveUserInitials(location.name) }));
            var identity = $('<div>');
            identity.append($('<div>', {
                class: 'live-user-popup-name',
                text: (location.name || 'User') + (location.employee_code ? ' (' + location.employee_code + ')' : '')
            }));
            identity.append($('<div>', { class: 'live-user-popup-role', text: location.designation || 'Field employee' }));
            identity.append($('<div>', {
                class: 'live-user-popup-status' + (location.status === 'Offline' ? ' is-offline' : ''),
                text: location.status || 'GPS Off'
            }));
            head.append(identity);
            popup.append(head);

            var grid = $('<div>', { class: 'live-user-popup-grid' });
            appendPopupMetric(grid, "Today's Plan", location.today_plan || 'No plan assigned', '');
            appendPopupMetric(grid, 'Total KM Run', Number(location.distance_km || 0).toFixed(1) + ' km', 'is-cyan');
            appendPopupMetric(grid, 'Customer Visits', String(location.visits_today || 0), '');
            appendPopupMetric(grid, "Today's Order Value", '₹' + Number(location.order_value || 0).toLocaleString('en-IN'), 'is-green');
            popup.append(grid);

            var foot = $('<div>', { class: 'live-user-popup-foot' });
            foot.append($('<div>', { text: 'Last update: ' + (location.time || 'Unknown') }));
            if (location.mobile) foot.append($('<div>', { text: location.mobile }));
            foot.append($('<div>', { text: location.address || 'Address unavailable' }));
            popup.append(foot);
            return popup.get(0);
        }

        function appendPopupMetric(container, label, value, valueClass) {
            var metric = $('<div>', { class: 'live-user-popup-metric' });
            metric.append($('<div>', { class: 'live-user-popup-label', text: label }));
            metric.append($('<div>', { class: 'live-user-popup-value ' + valueClass, text: value }));
            container.append(metric);
        }

        function applyLiveUserFilters() {
            var query = ($('#liveUsersSearch').val() || '').toLowerCase().trim();
            var visible = liveUserLocations.filter(function(location) {
                var matchesStatus = liveUserStatusFilter === 'all' || location.status === liveUserStatusFilter;
                var haystack = [location.name, location.employee_code, location.designation, location.branch, location.division, location.address]
                    .filter(Boolean).join(' ').toLowerCase();
                return matchesStatus && (!query || haystack.indexOf(query) !== -1);
            });
            var visibleIds = new Set(visible.map(function(location) { return String(location.user_id); }));
            liveUserLocations.forEach(function(location) {
                if (location.marker) location.marker.setMap(visibleIds.has(String(location.user_id)) ? liveUsersMap : null);
            });
            renderLiveUsersList(visible);
        }

        function getLocationData(lat, lang) {
            if (lat !== '' && lang !== '' && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lang))) {
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 12,
                    center: new google.maps.LatLng(lat, lang),
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
                var infowindow = new google.maps.InfoWindow();
                var marker;
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(lat, lang),
                    map: map
                });
                google.maps.event.addListener(marker, 'click', (function(marker) {
                    return function() {
                        infowindow.setContent(lat);
                        infowindow.open(map, marker);
                    }
                })(marker));
            } else {
                $("#map").html('<div class="activity-state">No location found for this activity.</div>');
            }


        }

        function renderActivityMap(activities) {
            var validActivities = activities.filter(function(item) {
                return item.latitude !== '' && item.longitude !== '' &&
                    !isNaN(parseFloat(item.latitude)) && !isNaN(parseFloat(item.longitude));
            });
            if (!validActivities.length) {
                $("#map").html('<div class="activity-state">No mapped locations found for these activities.</div>');
                return;
            }

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {
                    lat: parseFloat(validActivities[0].latitude),
                    lng: parseFloat(validActivities[0].longitude)
                },
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            var bounds = new google.maps.LatLngBounds();
            var infoWindow = new google.maps.InfoWindow();

            validActivities.forEach(function(item) {
                var position = { lat: parseFloat(item.latitude), lng: parseFloat(item.longitude) };
                var marker = new google.maps.Marker({ position: position, map: map, title: item.title || 'Activity' });
                bounds.extend(position);
                marker.addListener('click', function() {
                    var content = document.createElement('div');
                    var title = document.createElement('strong');
                    title.textContent = item.title || 'Activity';
                    var detail = document.createElement('div');
                    detail.textContent = (item.time_display || item.time || '') + (item.customer ? ' · ' + item.customer : '');
                    content.append(title, detail);
                    infoWindow.setContent(content);
                    infoWindow.open(map, marker);
                });
            });
            if (validActivities.length > 1) map.fitBounds(bounds, 48);
        }

        function getActivityData() {
            if (!validateLocationFilters(true)) return;
            showLocationWorkspace(true);
            $("#todayActivity").empty();
            $("#todayActivity").append('<li class="activity-state">Loading activity…</li>');
            var date = $("input[name=date]").val();
            var user_id = $("select[name=user_id]").val();
            $.ajax({
                url: "{{ url('getUserActivityData') }}",
                dataType: "json",
                type: "POST",
                data: {
                    _token: "{{csrf_token()}}",
                    date: date,
                    user_id: user_id
                },
                success: function(res) {
                    $("#todayActivity").empty();
                    if (res.length > 0) {
                        renderActivityMap(res);
                        $.each(res, function(index, item) {
                            var icon = 'timeline';
                            switch (item.title) {
                                case 'Punchin':
                                    icon = 'login';
                                    break;
                                case 'Punchout':
                                    icon = 'logout';
                                    break;
                                case 'Checkin':
                                    icon = 'location_on';
                                    break;
                                case 'Checkout':
                                    icon = 'location_off';
                                    break;
                                case 'Order':
                                    icon = 'shopping_bag';
                                    break;
                            }

                            var $item = $('<li>', { class: 'activity-item' });
                            var $marker = $('<div>', { class: 'activity-marker' })
                                .append($('<i>', { class: 'material-icons', text: icon }));
                            var $card = $('<div>', { class: 'activity-card' });
                            $card.append($('<span>', { class: 'activity-time', text: item.time_display || item.time || '--' }));
                            $card.append($('<h4>', { class: 'activity-title', text: item.title || 'Activity' }));

                            var detail = item.customer || item.location || item.city || 'No additional details';
                            $card.append($('<p>', { class: 'activity-customer', text: detail }));
                            var metadata = [item.customer_type, item.city, item.location, item.remark]
                                .filter(function(value, position, values) {
                                    return value && value !== detail && values.indexOf(value) === position;
                                })
                                .join(' · ');
                            if (metadata) $card.append($('<p>', { class: 'activity-meta', text: metadata }));

                            var hasLocation = item.latitude !== '' && item.longitude !== '' &&
                                !isNaN(parseFloat(item.latitude)) && !isNaN(parseFloat(item.longitude));
                            if (hasLocation) {
                                var $locationButton = $('<button>', {
                                    type: 'button',
                                    class: 'btn btn-sm activity-location-btn'
                                }).append($('<i>', { class: 'material-icons', text: 'near_me' }))
                                  .append(document.createTextNode(' View on map'));
                                $locationButton.on('click', function() {
                                    getLocationData(item.latitude, item.longitude);
                                });
                                $card.append($locationButton);
                            }

                            $item.append($marker, $card);
                            $("#todayActivity").append($item);
                        });
                    } else {
                        $("#todayActivity").append('<li class="activity-state">No activity found for the selected user and date.</li>');
                    }

                }
            });
        }

        $("#branch_id").on('change', function() {
            var search_branches = $(this).val();
            $.ajax({
                url: "{{ url('livelocation') }}",
                data: {
                    "search_branches": search_branches
                },
                success: function(res) {
                    if (res.status == true) {
                        var select = $('#user_id');
                        select.empty();
                        select.append('<option>Select User</option>');
                        $.each(res.users, function(k, v) {
                            select.append('<option value="' + v.id + '" >' + v.name + '</option>');
                        });
                        select.selectpicker('refresh');
                    }
                }
            });

        })

        $("#division_id").on('change', function() {
            var search_divisions = $(this).val();
            $.ajax({
                url: "{{ url('livelocation') }}",
                data: {
                    "search_divisions": search_divisions
                },
                success: function(res) {
                    if (res.status == true) {
                        var select = $('#user_id');
                        select.empty();
                        select.append('<option>Select User</option>');
                        $.each(res.users, function(k, v) {
                            select.append('<option value="' + v.id + '" >' + v.name + '</option>');
                        });
                        select.selectpicker('refresh');
                    }
                }
            });

        })

        $("#department_id").on('change', function() {
            var search_departments = $(this).val();
            $.ajax({
                url: "{{ url('livelocation') }}",
                data: {
                    "search_departments": search_departments
                },
                success: function(res) {
                    if (res.status == true) {
                        var select = $('#user_id');
                        select.empty();
                        select.append('<option>Select User</option>');
                        $.each(res.users, function(k, v) {
                            select.append('<option value="' + v.id + '" >' + v.name + '</option>');
                        });
                        select.selectpicker('refresh');
                    }
                }
            });
        })
    </script>
</x-app-layout>
