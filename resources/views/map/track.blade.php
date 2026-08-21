<!DOCTYPE html>
<html>

<head>
    <title>Track Activity - {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</title>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }

        .no-location-data {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #071634;
            color: #eaf1ff;
            font: 600 18px Arial, sans-serif;
        }

        .map-legend {
            position: absolute;
            left: 12px;
            bottom: 26px;
            z-index: 5;
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 1px 5px rgba(0, 0, 0, .3);
            font: 12px Arial, sans-serif;
            color: #1f2937;
            pointer-events: none;
        }

        .map-legend strong {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .map-legend span {
            display: flex;
            align-items: center;
            margin-top: 4px;
        }

        .map-legend i {
            width: 12px;
            height: 12px;
            margin-right: 7px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, .25);
        }

        .map-tooltip {
            position: absolute;
            display: none;
            z-index: 1000;
            max-width: 260px;
            padding: 6px 9px;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .3);
            font: 12px Arial, sans-serif;
            color: #1f2937;
            pointer-events: none;
        }

        .map-tooltip span {
            display: block;
        }

        .map-tooltip .tooltip-title {
            font-weight: 700;
        }

        .info-window {
            max-width: 260px;
            font: 12px Arial, sans-serif;
            color: #1f2937;
            line-height: 1.5;
        }

        .info-window .info-title {
            display: block;
            margin-bottom: 2px;
            font-size: 13px;
            font-weight: 700;
        }

        .info-window .info-type {
            display: block;
            margin-bottom: 6px;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .info-window .info-row {
            display: block;
        }

        .info-window .info-row b {
            font-weight: 700;
        }
    </style>

    <script>
        const locations = @json($coordinates);
        const visits = @json($visits ?? []);

        function isValidPoint(lat, lng) {
            return lat !== null && lat !== '' && lng !== null && lng !== '' &&
                !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng));
        }

        function toLatLngLiteral(lat, lng) {
            return { lat: parseFloat(lat), lng: parseFloat(lng) };
        }

        function initMap() {
            const validVisits = visits.filter(v => isValidPoint(v.latitude, v.longitude));

            if (!locations.length && !validVisits.length) {
                document.getElementById("map").innerHTML = '<div class="no-location-data">No location activity found for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}.</div>';
                return;
            }

            const firstPoint = locations.length
                ? toLatLngLiteral(locations[0].latitude, locations[0].longitude)
                : toLatLngLiteral(validVisits[0].latitude, validVisits[0].longitude);

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: firstPoint
            });

            const bounds = new google.maps.LatLngBounds();
            const directionsService = new google.maps.DirectionsService();

            const chunkSize = 25; // max waypoints per request
            let routeChunks = [];

            for (let i = 0; i < locations.length - 1; i += chunkSize) {
                const chunk = locations.slice(i, i + chunkSize + 1);
                if (chunk.length >= 2) {
                    routeChunks.push(chunk);
                }
            }

            routeChunks.forEach((chunk, i) => {
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: true,
                    preserveViewport: true
                });

                const origin = {
                    lat: parseFloat(chunk[0].latitude),
                    lng: parseFloat(chunk[0].longitude)
                };
                const destination = {
                    lat: parseFloat(chunk[chunk.length - 1].latitude),
                    lng: parseFloat(chunk[chunk.length - 1].longitude)
                };
                const waypoints = chunk.slice(1, -1).map(loc => ({
                    location: {
                        lat: parseFloat(loc.latitude),
                        lng: parseFloat(loc.longitude)
                    },
                    stopover: true
                }));

                directionsService.route({
                    origin: origin,
                    destination: destination,
                    waypoints: waypoints,
                    travelMode: google.maps.TravelMode.DRIVING
                }, (response, status) => {
                    if (status === 'OK') {
                        directionsRenderer.setDirections(response);
                    } else {
                        console.error('Directions request failed: ' + status);
                    }
                });
            });

            // Tooltip logic
            const tooltip = document.createElement("div");
            tooltip.className = "map-tooltip";
            document.body.appendChild(tooltip);

            function moveTooltip(e) {
                tooltip.style.left = e.domEvent.pageX + 10 + "px";
                tooltip.style.top = e.domEvent.pageY + 10 + "px";
            }

            function attachTooltip(marker, buildContent) {
                marker.addListener("mouseover", (e) => {
                    tooltip.innerHTML = '';
                    tooltip.appendChild(buildContent());
                    moveTooltip(e);
                    tooltip.style.display = "block";
                });
                marker.addListener("mousemove", moveTooltip);
                marker.addListener("mouseout", () => {
                    tooltip.style.display = "none";
                });
            }

            function textLine(text, className) {
                const line = document.createElement("span");
                if (className) line.className = className;
                line.textContent = text;
                return line;
            }

            const infoWindow = new google.maps.InfoWindow();

            const mapPin = (fillColor, scale) => ({
                path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z",
                fillColor: fillColor,
                fillOpacity: 1,
                strokeColor: "#ffffff",
                strokeWeight: 2,
                scale: scale || 1.9,
                anchor: new google.maps.Point(12, 22),
                labelOrigin: new google.maps.Point(12, 9)
            });

            const visitPin = (fillColor) => mapPin(fillColor);

            function infoContent(title, subtitle, rows) {
                const wrap = document.createElement("div");
                wrap.className = "info-window";
                wrap.appendChild(textLine(title, "info-title"));
                if (subtitle) wrap.appendChild(textLine(subtitle, "info-type"));

                rows.forEach(([label, value]) => {
                    const row = document.createElement("span");
                    row.className = "info-row";
                    const strong = document.createElement("b");
                    strong.textContent = label + ': ';
                    row.appendChild(strong);
                    row.appendChild(document.createTextNode(value));
                    wrap.appendChild(row);
                });

                return wrap;
            }

            // ---------- movement trail: numbered markers, last one highlighted ----------
            const lastMovementIndex = (() => {
                for (let i = locations.length - 1; i >= 0; i--) {
                    if (isValidPoint(locations[i].latitude, locations[i].longitude)) return i;
                }
                return -1;
            })();

            locations.forEach((loc, index) => {
                if (!isValidPoint(loc.latitude, loc.longitude)) return;

                const position = toLatLngLiteral(loc.latitude, loc.longitude);
                bounds.extend(position);
                const isLast = index === lastMovementIndex;
                const heading = isLast
                    ? `Last known location (point ${index + 1})`
                    : `Movement point ${index + 1}`;

                const marker = new google.maps.Marker({
                    position: position,
                    label: isLast
                        ? { text: `${index + 1}`, color: "#ffffff", fontSize: "11px", fontWeight: "700" }
                        : `${index + 1}`,
                    title: loc.name || heading,
                    icon: isLast ? mapPin("#7c3aed", 2.2) : undefined,
                    zIndex: isLast ? 300 : 10,
                    map: map
                });

                attachTooltip(marker, () => {
                    const wrap = document.createDocumentFragment();
                    wrap.appendChild(textLine(heading, "tooltip-title"));
                    wrap.appendChild(textLine(loc.time || '-'));
                    if (loc.address) wrap.appendChild(textLine(loc.address));
                    return wrap;
                });

                marker.addListener("click", () => {
                    tooltip.style.display = "none";
                    const rows = [['Time', loc.time || '-']];
                    if (loc.address) rows.push(['Address', loc.address]);
                    infoWindow.setContent(infoContent(
                        heading,
                        isLast ? 'Last known location' : 'Movement',
                        rows
                    ));
                    infoWindow.open(map, marker);
                });
            });

            // ---------- customer visits: highlighted pins ----------
            function visitTooltip(visit, stage) {
                const wrap = document.createDocumentFragment();
                wrap.appendChild(textLine(visit.customer, "tooltip-title"));
                const timing = stage === 'checkout'
                    ? `Check Out · ${visit.checkout_time || '-'}`
                    : `Check In · ${visit.checkin_time || '-'}`;
                wrap.appendChild(textLine(timing));
                return wrap;
            }

            function visitInfoContent(visit, stage) {
                const rows = [];
                rows.push(['Check In', visit.checkin_time || '-']);
                if (visit.checkout_time) rows.push(['Check Out', visit.checkout_time]);
                if (visit.duration) rows.push(['Duration', visit.duration]);

                const address = stage === 'checkout'
                    ? (visit.checkout_address || visit.checkin_address)
                    : visit.checkin_address;
                if (address) rows.push(['Address', address]);
                if (visit.remark) rows.push(['Remark', visit.remark]);

                return infoContent(
                    `${visit.sequence}. ${visit.customer}`,
                    visit.customer_type || 'Customer',
                    rows
                );
            }

            function addVisitMarker(visit, stage, lat, lng) {
                const position = toLatLngLiteral(lat, lng);
                bounds.extend(position);

                const marker = new google.maps.Marker({
                    position: position,
                    label: {
                        text: `${visit.sequence}`,
                        color: "#ffffff",
                        fontSize: "11px",
                        fontWeight: "700"
                    },
                    title: `${visit.customer} · ${stage === 'checkout' ? 'Check Out' : 'Check In'}`,
                    icon: visitPin(stage === 'checkout' ? "#f59e0b" : "#16a34a"),
                    zIndex: stage === 'checkout' ? 100 : 200,
                    map: map
                });

                attachTooltip(marker, () => visitTooltip(visit, stage));
                marker.addListener("click", () => {
                    tooltip.style.display = "none";
                    infoWindow.setContent(visitInfoContent(visit, stage));
                    infoWindow.open(map, marker);
                });
            }

            validVisits.forEach((visit) => {
                addVisitMarker(visit, 'checkin', visit.latitude, visit.longitude);

                // only plot the check-out pin when it happened somewhere else
                const hasCheckout = isValidPoint(visit.checkout_latitude, visit.checkout_longitude);
                const movedAway = hasCheckout &&
                    (parseFloat(visit.checkout_latitude).toFixed(5) !== parseFloat(visit.latitude).toFixed(5) ||
                        parseFloat(visit.checkout_longitude).toFixed(5) !== parseFloat(visit.longitude).toFixed(5));
                if (movedAway) {
                    addVisitMarker(visit, 'checkout', visit.checkout_latitude, visit.checkout_longitude);
                }
            });

            if (!bounds.isEmpty()) {
                map.fitBounds(bounds, 48);
            }
        }
    </script>

    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap">
    </script>
</head>

<body>
    <div id="map"></div>
    <div class="map-legend">
        <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong>
        <span><i style="background:#ea4335"></i> Movement point</span>
        <span><i style="background:#7c3aed"></i> Last known location</span>
        <span><i style="background:#16a34a"></i> Customer visit (check in)</span>
        <span><i style="background:#f59e0b"></i> Customer visit (check out)</span>
    </div>
</body>

</html>
