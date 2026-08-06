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
        .live-location-page .location-filter-form > .row { align-items: flex-end; gap: 12px 0; }
        .live-location-page .location-filter-form .form-control,
        .live-location-page .location-filter-form .bootstrap-select > .dropdown-toggle,
        .live-location-page .location-filter-form .select2-selection--single {
            min-height: 42px;
            border: 1px solid var(--fk-list-border-strong, rgba(90, 130, 220, .34)) !important;
            border-radius: 10px !important;
            background: rgba(5, 14, 36, .72) !important;
            color: var(--fk-list-soft, #c8d5ea) !important;
            box-shadow: none !important;
        }
        .live-location-page .location-filter-form .btn {
            min-height: 42px;
            margin: 0 !important;
            border-radius: 10px !important;
            text-transform: none !important;
        }
        .live-location-page .all-users-location-btn {
            border: 1px solid rgba(34, 211, 238, .38) !important;
            background: rgba(34, 211, 238, .12) !important;
            color: var(--fk-list-accent, #22d3ee) !important;
        }
        .live-location-page .location-workspace {
            margin: 20px 0 0 !important;
            padding: 0 !important;
            overflow: hidden;
            border: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22));
            border-radius: 14px;
            background: rgba(5, 14, 36, .62);
        }
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
        .live-location-page .activity-location-btn { display: inline-flex; align-items: center; gap: 5px; min-height: 30px; margin: 12px 0 0 !important; padding: 6px 9px !important; border-radius: 7px !important; font-size: 10px !important; text-transform: none !important; }
        .live-location-page .activity-location-btn .material-icons { font-size: 14px; }
        .live-location-page .activity-state { padding: 36px 12px; color: var(--fk-list-dim, #8291ad); text-align: center; font-size: 12px; }
        @media (max-width: 991px) {
            .live-location-page .map-column { border-right: 0; border-bottom: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)); }
            .live-location-page #map, .live-location-page .activity-column { height: 430px !important; }
        }
    </style>
    <div class="row mt-4 live-location-page">
        <div class="col-lg-12">
            <div class="fk-list-page-head">
                <div class="fk-list-heading-block">
                    <div class="fk-list-breadcrumb"><span>CRM</span><span>&rsaquo;</span><span class="fk-current">LIVE LOCATION</span></div>
                    <div class="fk-list-title-row"><h1 class="fk-list-title">User Live Location</h1></div>
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
                    <form target="_blank" method="post" action="{{url('map-all')}}" class="location-filter-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" multiple id="branch_id" name="branch_id" data-style="select-with-transition" title="Choose Branch" data-size="10" tabindex="-98">
                                        <option disabled=""> Select Branch</option>
                                        @if(@isset($branches ))
                                        @foreach($branches as $branch)
                                        <option value="{!! $branch['id'] !!}">{!! $branch['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" multiple id="division_id" name="division_id" data-style="select-with-transition" title="Choose Zone" data-size="10" tabindex="-98">
                                        <option disabled=""> Select Zone</option>
                                        @if(@isset($divisions ))
                                        @foreach($divisions as $division)
                                        <option value="{!! $division['id'] !!}">{!! $division['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" multiple id="department_id" name="department_id" data-style="select-with-transition" title="Choose Department" data-size="10" tabindex="-98">
                                        <option disabled=""> Select Department</option>
                                        @if(@isset($departments ))
                                        @foreach($departments as $department)
                                        <option value="{!! $department['id'] !!}">{!! $department['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="select2" id="user_id" name="user_id" data-style="select-with-transition" title="Choose User" data-size="10" tabindex="-98" required>
                                        <option disabled="" selected> Select Users</option>
                                        @if(@isset($users ))
                                        @foreach($users as $user)
                                        <option {{(!empty($user_id) && $user_id == $user['id'])?'selected':''}} value="{!! $user['id'] !!}">{!! $user['name'] !!}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group has-default bmd-form-group">
                                    <input type="text" class="form-control datepicker" id="date" required name="date" value="{{$date??''}}" placeholder="Date From" autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group has-default bmd-form-group">
                                    <input type="text" class="form-control datepicker" id="to_date" required name="to_date"
                                        value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" placeholder="Select Date"
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-md-2 p-0 text-center">
                                <button type="button" class="btn btn-info btn-sm" onclick="getActivityData()">Activity Detailed</button>
                            </div>
                            <div class="col-md-2 p-0 text-center">
                                <!-- <button type="button" class="btn btn-info btn-sm" onclick="getLocationData()">Location</button> -->
                                <input type="submit" name="submit" class="btn btn-primary btn-sm" value="Complete Map Activity">
                            </div>
                            <div class="col-md-2 p-0 text-center">
                                <input type="submit" name="submit" class="btn btn-primary btn-sm" value="Track Activity">
                            </div>
                            <div class="col-md-3 p-0 text-center">
                                <button type="submit" name="submit" value="All Users Live Location" formnovalidate class="btn btn-sm all-users-location-btn">
                                    <i class="material-icons mr-1" style="font-size:16px">groups</i> All Users Live Location
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="row location-workspace">
                        <div class="col-lg-7 map-column">
                            <div id="map"></div>
                        </div>
                        <div class="col-lg-5 activity-column" id="custom-scroll">
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
        $(document).ready(function() {
            // getActivityData();
            $('#loader').hide();
            getActivityData();
        })

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
                                    class: 'btn btn-info btn-sm activity-location-btn'
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
