<x-app-layout>
    @php($isCustomerLocator = ($locatorMode ?? 'punch') === 'customer')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}" type="text/javascript"></script>
    <style>
        .punch-locator-page { margin-top: 0 !important; }
        .punch-locator-shell { display: flex; height: calc(100dvh - 170px); min-height: 520px; overflow: hidden; border: 1px solid rgba(90,130,220,.16); border-radius: 12px; background: rgba(5,14,36,.38); }
        .punch-locator-sidebar { flex: 0 0 340px; width: 340px; overflow: hidden; border-right: 1px solid rgba(90,130,220,.18); background: rgba(6,17,39,.96); }
        .punch-locator-head { padding: 16px; border-bottom: 1px solid rgba(90,130,220,.18); }
        .punch-locator-title-row { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .punch-locator-title { margin:0; color:var(--fk-list-heading,#f1f5ff); font-size:12px; font-weight:800; letter-spacing:.8px; text-transform:uppercase; }
        .punch-locator-count { padding:5px 8px; border:1px solid rgba(56,189,248,.26); border-radius:7px; background:rgba(56,189,248,.08); color:#7dd3fc; font-size:9px; font-weight:800; }
        .punch-filter-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:13px; }
        .punch-search-wrap { position:relative; }
        .punch-search-wrap .material-icons { position:absolute; z-index:2; top:50%; left:12px; width:16px; transform:translateY(-50%); color:#7184ae; font-size:16px; line-height:1; pointer-events:none; }
        body.fk-shell .punch-locator-page .punch-search, body.fk-shell .punch-locator-page .punch-zone {
            width:100%; height:38px !important; min-height:38px !important; margin:0 !important; border:1px solid rgba(90,130,220,.25) !important; border-radius:9px !important;
            background:rgba(5,14,36,.72) !important; color:var(--fk-list-soft,#c8d5ea) !important; box-shadow:none !important; font-size:10px !important;
        }
        body.fk-shell .content .punch-locator-page input.form-control.punch-search { padding:0 12px 0 38px !important; line-height:38px !important; }
        body.fk-shell .content .punch-locator-page input.form-control.punch-search::placeholder { color:var(--fk-list-soft,#c8d5ea) !important; opacity:.72 !important; }
        body.fk-shell .punch-locator-page .punch-zone { padding:0 30px 0 10px !important; }
        .punch-list { height:calc(100% - 104px); padding:8px; overflow-y:auto; scrollbar-width:thin; scrollbar-color:rgba(56,189,248,.3) transparent; }
        .punch-user { display:flex; align-items:flex-start; gap:10px; width:100%; margin-bottom:6px; padding:10px; border:1px solid transparent; border-radius:10px; cursor:pointer; transition:.18s ease; }
        .punch-user:hover,.punch-user.active { border-color:rgba(56,189,248,.22); background:rgba(56,189,248,.07); }
        .punch-avatar { display:grid; place-items:center; flex:0 0 36px; width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#3b82f6,#22d3ee); color:#fff; font-size:10px; font-weight:800; }
        .punch-copy { min-width:0; flex:1; }
        .punch-name { overflow:hidden; color:#eef4ff; font-size:11px; font-weight:750; line-height:1.3; text-overflow:ellipsis; white-space:nowrap; }
        .punch-role,.punch-address { overflow:hidden; margin-top:3px; color:#8294bd; font-size:8px; line-height:1.3; text-overflow:ellipsis; white-space:nowrap; }
        .punch-time { margin-top:4px; color:#22d3ee; font-size:9px; font-weight:800; line-height:1.3; }
        .punch-empty { padding:40px 12px; color:#8291ad; font-size:11px; text-align:center; }
        .punch-map-wrap { flex:1 1 auto; min-width:0; }
        #punchInMap { width:100%; height:100%; background:#071126; }
        .punch-locator-page .gm-style .gm-style-iw-c { max-width:290px !important; padding:0 !important; border:1px solid rgba(90,130,220,.26); border-radius:11px; background:#0b1c3c !important; box-shadow:0 14px 32px rgba(0,0,0,.32); }
        .punch-locator-page .gm-style .gm-style-iw-d { overflow:hidden !important; }
        .punch-locator-page .gm-style .gm-style-iw-tc::after { background:#0b1c3c !important; }
        .punch-locator-page .gm-style .gm-style-iw-chr { position:absolute !important; z-index:3; top:0; right:0; width:34px; height:34px !important; min-height:0 !important; }
        body.fk-shell .punch-locator-page .gm-style button.gm-ui-hover-effect {
            top:5px !important; right:5px !important; width:26px !important; height:26px !important; min-height:26px !important;
            margin:0 !important; padding:0 !important; border:0 !important; border-radius:7px !important;
            outline:0 !important; background:rgba(255,255,255,.05) !important; box-shadow:none !important; opacity:.72;
        }
        .punch-locator-page .gm-style button.gm-ui-hover-effect:hover { background:rgba(255,255,255,.1) !important; opacity:1; }
        .punch-locator-page .gm-style button.gm-ui-hover-effect > span { width:15px !important; height:15px !important; margin:5px !important; filter:invert(1); }
        .punch-locator-page .gm-style button.gm-ui-hover-effect > img { width:15px !important; height:15px !important; margin:5px !important; filter:invert(1); }
        .punch-popup { box-sizing:border-box; width:260px; padding:15px 38px 15px 15px; font-family:'Inter',sans-serif; text-align:left; }
        .punch-popup-name { color:#f1f5ff; font-size:13px; font-weight:800; line-height:1.3; }
        .punch-popup-role { margin-top:3px; color:#8ea2ce; font-size:10px; }
        .punch-popup-address { margin-top:11px; color:#b8c6e4; font-size:10px; line-height:1.45; overflow-wrap:anywhere; unicode-bidi:plaintext; }
        .punch-popup-time { margin-top:8px; color:#22d3ee; font-size:10px; font-weight:800; line-height:1.35; }
        @media(max-width:991px){.punch-locator-shell{height:auto;min-height:0;flex-wrap:wrap}.punch-locator-sidebar{flex:0 0 100%;width:100%;height:360px;border-right:0;border-bottom:1px solid rgba(90,130,220,.18)}.punch-map-wrap{flex:0 0 100%;height:430px}.punch-filter-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:575px){.punch-filter-grid{grid-template-columns:1fr}.punch-list{height:calc(100% - 148px)}}
    </style>

    <div class="row mt-4 punch-locator-page">
        <div class="col-lg-12">
            <div class="fk-list-page-head">
                <div class="fk-list-heading-block">
                    <div class="fk-list-breadcrumb"><span>CRM</span><span>&rsaquo;</span><span class="fk-current">{{ $isCustomerLocator ? 'CUSTOMER LOCATOR' : 'USER PUNCH-IN LOCATOR' }}</span></div>
                    <div class="fk-list-title-row"><h1 class="fk-list-title">{{ $isCustomerLocator ? 'Customer Check-In Map' : 'User Punch-In Locator' }}</h1></div>
                </div>
            </div>
            <section class="punch-locator-shell">
                <aside class="punch-locator-sidebar">
                    <div class="punch-locator-head">
                        <div class="punch-locator-title-row">
                            <h2 class="punch-locator-title">{{ $isCustomerLocator ? 'Customer check-ins today' : 'Punch-ins today' }}</h2>
                            <span class="punch-locator-count" id="punchCount">0 records</span>
                        </div>
                        <div class="punch-filter-grid">
                            <div class="punch-search-wrap"><i class="material-icons">search</i><input type="search" class="form-control punch-search" id="punchSearch" placeholder="{{ $isCustomerLocator ? 'Search customer…' : 'Search user…' }}" autocomplete="off"></div>
                            <select class="form-control punch-zone" id="punchZone" aria-label="Filter punch-ins by zone"><option value="all">All zones</option></select>
                        </div>
                    </div>
                    <div class="punch-list" id="punchList"></div>
                </aside>
                <div class="punch-map-wrap"><div id="punchInMap"></div></div>
            </section>
        </div>
    </div>

    <script>
        var punchIns = @json($punchIns);
        var isCustomerLocator = @json($isCustomerLocator);
        var punchMap;
        var punchInfoWindow;

        $(document).ready(function () {
            populatePunchZones();
            $('#punchSearch').on('input', applyPunchFilters);
            $('#punchZone').on('change', applyPunchFilters);
            initialisePunchMap();
        });

        function initials(name) { return (name || 'User').split(/\s+/).slice(0,2).map(function(part){ return part.charAt(0).toUpperCase(); }).join(''); }

        function populatePunchZones() {
            var zones = punchIns.map(function(item){ return (item.zone || '').trim(); }).filter(Boolean).filter(function(zone,index,all){ return all.indexOf(zone) === index; }).sort();
            var $zone = $('#punchZone');
            zones.forEach(function(zone){ $zone.append($('<option>',{value:zone,text:zone})); });
        }

        function punchMarkerIcon() {
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="40" viewBox="0 0 30 40"><defs><filter id="s" x="-35%" y="-20%" width="170%" height="165%"><feDropShadow dx="1" dy="2" stdDeviation="1.2" flood-color="#020617" flood-opacity=".4"/></filter></defs><path filter="url(#s)" d="M15 1.25A13.25 13.25 0 0 0 1.75 14.5C1.75 24.1 15 38.5 15 38.5S28.25 24.1 28.25 14.5A13.25 13.25 0 0 0 15 1.25Z" fill="#3182c4" stroke="#1d4f91" stroke-width="1.5"/><circle cx="15" cy="14.3" r="4.6" fill="#fff" stroke="#1d4f91" stroke-width="1.2"/></svg>';
            return {url:'data:image/svg+xml;charset=UTF-8,'+encodeURIComponent(svg),scaledSize:new google.maps.Size(20,27),anchor:new google.maps.Point(10,26)};
        }

        function initialisePunchMap() {
            punchMap = new google.maps.Map(document.getElementById('punchInMap'), {
                zoom:6,
                center:{lat:20.5937,lng:78.9629},
                mapTypeId:google.maps.MapTypeId.HYBRID,
                mapTypeControl:false,
                streetViewControl:false,
                fullscreenControl:true,
                backgroundColor:'#071329'
            });
            punchInfoWindow = new google.maps.InfoWindow();
            var bounds = new google.maps.LatLngBounds();
            punchIns.forEach(function(item){
                var position={lat:Number(item.latitude),lng:Number(item.longitude)};
                item.marker=new google.maps.Marker({map:punchMap,position:position,title:item.name,icon:punchMarkerIcon()});
                bounds.extend(position);
                item.marker.addListener('click',function(){ focusPunch(item.attendance_id,false); });
            });
            renderPunchList(punchIns);
            fitPunchBounds(punchIns,bounds);
        }

        function fitPunchBounds(items, suppliedBounds) {
            if (!punchMap || !items.length) return;
            if (items.length === 1) { punchMap.setCenter(items[0].marker.getPosition()); punchMap.setZoom(14); return; }
            var bounds=suppliedBounds || new google.maps.LatLngBounds();
            if (!suppliedBounds) items.forEach(function(item){ if(item.marker) bounds.extend(item.marker.getPosition()); });
            punchMap.fitBounds(bounds,45);
        }

        function renderPunchList(items) {
            var $list=$('#punchList').empty();
            $('#punchCount').text(items.length+(items.length===1?' record':' records'));
            if(!items.length){$list.append($('<div>',{class:'punch-empty',text:isCustomerLocator?'No customer check-ins match the current filters.':'No punch-ins match the current filters.'}));return;}
            items.forEach(function(item){
                var $row=$('<div>',{class:'punch-user','data-id':item.attendance_id});
                var $copy=$('<div>',{class:'punch-copy'}).append($('<div>',{class:'punch-name',text:item.name}),$('<div>',{class:'punch-role',text:item.designation}),$('<div>',{class:'punch-address',text:item.address}),$('<div>',{class:'punch-time',text:(isCustomerLocator?'Checked in ':'Punched in ')+item.time}));
                if(isCustomerLocator&&item.representative){$copy.append($('<div>',{class:'punch-role',text:item.representative+' · '+item.representative_role}));}
                $row.append($('<div>',{class:'punch-avatar',text:initials(item.name)}),$copy).on('click',function(){focusPunch(item.attendance_id,true);});
                $list.append($row);
            });
        }

        function focusPunch(id,moveMap) {
            var item=punchIns.find(function(record){return String(record.attendance_id)===String(id);});
            if(!item||!item.marker)return;
            $('.punch-user').removeClass('active'); $('.punch-user[data-id="'+id+'"]').addClass('active');
            if(moveMap){punchMap.panTo(item.marker.getPosition());punchMap.setZoom(14);}
            var popup=$('<div>',{class:'punch-popup'}).append($('<div>',{class:'punch-popup-name',text:item.name}),$('<div>',{class:'punch-popup-role',text:item.designation}),$('<div>',{class:'punch-popup-address',text:item.address}),$('<div>',{class:'punch-popup-time',text:(isCustomerLocator?'Checked in at ':'Punched in at ')+item.time}));
            if(isCustomerLocator&&item.representative){popup.append($('<div>',{class:'punch-popup-role',text:item.representative+' · '+item.representative_role}));}
            punchInfoWindow.setContent(popup.get(0)); punchInfoWindow.open(punchMap,item.marker);
        }

        function applyPunchFilters() {
            var query=($('#punchSearch').val()||'').toLowerCase().trim(); var zone=$('#punchZone').val()||'all';
            var visible=punchIns.filter(function(item){var text=[item.name,item.employee_code,item.designation,item.address,item.zone].filter(Boolean).join(' ').toLowerCase();return(zone==='all'||item.zone===zone)&&(!query||text.indexOf(query)!==-1);});
            var ids=new Set(visible.map(function(item){return String(item.attendance_id);})); punchIns.forEach(function(item){item.marker.setMap(ids.has(String(item.attendance_id))?punchMap:null);});
            punchInfoWindow.close(); renderPunchList(visible); fitPunchBounds(visible);
        }
    </script>
</x-app-layout>
