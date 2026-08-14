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
    </style>

    <script>
        const locations = @json($coordinates);

        function initMap() {
            if (!locations.length) {
                document.getElementById("map").innerHTML = '<div class="no-location-data">No location activity found for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}.</div>';
                return;
            }

            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: {
                    lat: parseFloat(locations[0].latitude),
                    lng: parseFloat(locations[0].longitude)
                }
            });

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
            tooltip.style.position = "absolute";
            tooltip.style.background = "white";
            tooltip.style.padding = "2px 6px";
            tooltip.style.borderRadius = "4px";
            tooltip.style.fontSize = "12px";
            tooltip.style.boxShadow = "0 1px 3px rgba(0,0,0,0.3)";
            tooltip.style.display = "none";
            tooltip.style.pointerEvents = "none";
            tooltip.style.zIndex = "1000";
            document.body.appendChild(tooltip);

            const markers = locations.map((loc, index) => {
                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(loc.latitude),
                        lng: parseFloat(loc.longitude)
                    },
                    label: `${index + 1}`,
                    title: loc.name,
                    map: map
                });

                marker.addListener("mouseover", (e) => {
                    tooltip.innerText = loc.time;
                    tooltip.style.left = e.domEvent.pageX + 10 + "px";
                    tooltip.style.top = e.domEvent.pageY + 10 + "px";
                    tooltip.style.display = "block";
                });

                marker.addListener("mousemove", (e) => {
                    tooltip.style.left = e.domEvent.pageX + 10 + "px";
                    tooltip.style.top = e.domEvent.pageY + 10 + "px";
                });

                marker.addListener("mouseout", () => {
                    tooltip.style.display = "none";
                });

                return marker;
            });

        }
    </script>

    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap">
    </script>
</head>

<body>
    <div id="map"></div>
</body>

</html>
