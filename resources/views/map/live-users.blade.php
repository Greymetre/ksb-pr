<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Users Live Location</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Icons&display=swap">
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; height: 100%; margin: 0; overflow: hidden; background: #061127; color: #eaf1ff; font-family: Inter, sans-serif; }
        .live-map-shell { display: grid; grid-template-rows: auto 1fr; width: 100%; height: 100%; }
        .live-map-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 15px 20px; border-bottom: 1px solid rgba(90, 130, 220, .24); background: #091833; }
        .live-map-title { display: flex; align-items: center; gap: 11px; min-width: 0; }
        .live-map-icon { display: grid; place-items: center; width: 38px; height: 38px; border: 1px solid rgba(34, 211, 238, .32); border-radius: 10px; background: rgba(34, 211, 238, .1); color: #22d3ee; }
        h1 { margin: 0; font-size: 18px; font-weight: 800; }
        .subtitle { margin: 4px 0 0; color: #8291ad; font-size: 11px; }
        .live-count { padding: 8px 12px; border: 1px solid rgba(52, 211, 153, .26); border-radius: 9px; background: rgba(52, 211, 153, .08); color: #6ee7b7; font-size: 11px; font-weight: 700; white-space: nowrap; }
        #map { width: 100%; height: 100%; background: #071329; }
        .empty-state { display: grid; place-items: center; height: 100%; color: #8291ad; font-size: 13px; text-align: center; }
        .map-info { min-width: 210px; max-width: 280px; color: #172033; font-family: Inter, sans-serif; }
        .map-info strong { display: block; margin-bottom: 6px; font-size: 14px; }
        .map-info span { display: block; margin-top: 4px; color: #526079; font-size: 11px; line-height: 1.4; }
        @media (max-width: 600px) { .live-map-head { padding: 12px; } h1 { font-size: 15px; } .subtitle { display: none; } }
    </style>
</head>
<body>
<main class="live-map-shell">
    <header class="live-map-head">
        <div class="live-map-title">
            <div class="live-map-icon"><span class="material-icons">groups</span></div>
            <div><h1>All Users Live Location</h1><p class="subtitle">Latest reported location for today</p></div>
        </div>
        <div class="live-count">{{ $locations->count() }} users online</div>
    </header>
    <div id="map"><div class="empty-state">Loading live locations…</div></div>
</main>

<script>
    const locations = @json($locations);

    function initMap() {
        if (!locations.length) {
            document.getElementById('map').innerHTML = '<div class="empty-state">No user locations have been reported today.</div>';
            return;
        }

        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 6,
            center: { lat: 20.5937, lng: 78.9629 },
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });
        const bounds = new google.maps.LatLngBounds();
        const infoWindow = new google.maps.InfoWindow();

        locations.forEach(function(location) {
            const position = { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) };
            if (!Number.isFinite(position.lat) || !Number.isFinite(position.lng)) return;

            const marker = new google.maps.Marker({ map: map, position: position, title: location.name });
            bounds.extend(position);
            marker.addListener('click', function() {
                const wrapper = document.createElement('div');
                wrapper.className = 'map-info';
                const name = document.createElement('strong');
                name.textContent = location.name;
                const time = document.createElement('span');
                time.textContent = 'Last update: ' + (location.time || 'Unknown');
                const address = document.createElement('span');
                address.textContent = location.address || 'Address unavailable';
                wrapper.append(name, time, address);
                infoWindow.setContent(wrapper);
                infoWindow.open(map, marker);
            });
        });

        if (!bounds.isEmpty()) map.fitBounds(bounds, 60);
    }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap"></script>
</body>
</html>
