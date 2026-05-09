<x-app-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">build_circle</i>
                    </div>
                    <h4 class="card-title">
                        View Mechanic Details
                        <span class="pull-right">
                            <a href="{{ route('mechanics.index') }}" class="btn btn-theme">
                                <i class="material-icons">arrow_back</i> Back to List
                            </a>
                        </span>
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Photos Section (Dono images upar cards mein) -->
                    <h5 class="mt-3 mb-4 text-theme font-weight-bold">Mechanic Photos</h5>
                    <div class="row mb-5">
                        <div class="col-md-6 text-center">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">ID / Owner Photo</h6>
                                </div>
                                <div class="card-body p-3">
                                    @if($customer->owner_photo)
                                        <img src="{{ asset('storage/'.$customer->owner_photo) }}"
                                             class="img-fluid rounded shadow-sm mechanic-image-popup cursor-pointer"
                                             style="max-height: 250px; object-fit: cover;"
                                             alt="Owner Photo">
                                    @else
                                        <img src="{{ asset('assets/img/placeholder.jpg') }}"
                                             class="img-fluid rounded shadow-sm"
                                             style="max-height: 250px; object-fit: cover;"
                                             alt="No Owner Photo">
                                        <p class="text-muted mt-3 mb-0"><em>No photo uploaded</em></p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 text-center">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">Shop Photo</h6>
                                </div>
                                <div class="card-body p-3">
                                    @if($customer->shop_photo)
                                        <img src="{{ asset('storage/'.$customer->shop_photo) }}"
                                             class="img-fluid rounded shadow-sm mechanic-image-popup cursor-pointer"
                                             style="max-height: 250px; object-fit: cover;"
                                             alt="Shop Photo">
                                    @else
                                        <img src="{{ asset('assets/img/placeholder.jpg') }}"
                                             class="img-fluid rounded shadow-sm"
                                             style="max-height: 250px; object-fit: cover;"
                                             alt="No Shop Photo">
                                        <p class="text-muted mt-3 mb-0"><em>No photo uploaded</em></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details in Two Cards -->
                    <div class="row mt-3">
                        <!-- Basic Information Card -->
                        <div class="col-md-6">
                            <div class="card card-plain h-100">
                                <div class="card-body p-3">
                                    <div class="ctmr-box">
                                        <h6 class="">Basic Information</h6>
                                        <ul class="list-group">
                                            <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                <strong class="text-dark">Type:</strong> &nbsp; MECHANIC
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Sub Type:</strong> &nbsp; {{ $customer->sub_type ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Owner Name:</strong> &nbsp; {{ $customer->owner_name }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Shop Name:</strong> &nbsp; {{ $customer->shop_name }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Mobile Number:</strong> &nbsp; {{ $customer->mobile_number }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">WhatsApp / Alternate:</strong> &nbsp; {{ $customer->whatsapp_number ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Vehicle Segment:</strong> &nbsp; {{ $customer->vehicle_segment ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 pt-3 text-sm">
                                                <strong class="text-dark">
                                                    <i class="material-icons" style="font-size:18px;vertical-align:middle;">local_fire_department</i>
                                                    Opportunity Status:
                                                </strong>
                                                &nbsp;
                                                <span class="badge badge-pill {{
                                                    $customer->opportunity_status == 'HOT' ? 'badge-danger' :
                                                    ($customer->opportunity_status == 'WARM' ? 'badge-warning' :
                                                    ($customer->opportunity_status == 'COLD' ? 'badge-info' : 'badge-secondary'))
                                                }}">
                                                    {{ $customer->opportunity_status ?? 'N/A' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                <strong class="text-dark">
                                                    <i class="material-icons" style="font-size:18px;vertical-align:middle;">school</i>
                                                    Saathi Awareness Status:
                                                </strong>
                                                &nbsp;
                                                <span class="badge badge-pill {{ $customer->saathi_awareness_status == 'Done' ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $customer->saathi_awareness_status == 'Done' ? 'DONE' : 'NOT DONE' }}
                                                </span>
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Created At:</strong> &nbsp; {{ showdatetimeformat($customer->created_at) }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Card -->
                        <div class="col-md-6">
                            <div class="card card-plain h-100">
                                <div class="card-body p-3">
                                    <div class="ctmr-box">
                                        <h6 class="">Address Information</h6>
                                        @php
                                            $state = \App\Models\State::find($customer->state_id);
                                            $city = \App\Models\City::find($customer->city_id);
                                        @endphp
                                        <ul class="list-group">
                                            <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                <strong class="text-dark">Address Line:</strong> &nbsp; {{ $customer->address_line ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">State:</strong> &nbsp; {{ $state?->state_name ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">District:</strong> &nbsp; {{ $city?->city_name ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">Belt / Area / Market:</strong> &nbsp; {{ $customer->belt_area_market_name ?? '-' }}
                                            </li>
                                            <li class="list-group-item border-0 ps-0 text-sm">
                                                <strong class="text-dark">GPS Coordinates:</strong> &nbsp; 
                                                @if($customer->gps_location)
                                                    <code class="text-success">{{ $customer->gps_location }}</code><br>
                                                    <small>
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $customer->gps_location }}"
                                                           target="_blank"
                                                           class="text-decoration-none text-muted">
                                                            <i class="material-icons" style="font-size:14px;vertical-align:middle;">open_in_new</i> Open in Google Maps
                                                        </a>
                                                    </small>
                                                @else
                                                    -
                                                @endif
                                            </li>
                                        </ul>

                                        @if($customer->gps_location)
                                            <div class="mt-4 text-center">
                                                <button type="button"
                                                        class="btn btn-theme btn-round shadow-sm"
                                                        onclick="showMechanicOnMap()">
                                                    <i class="material-icons">location_on</i>
                                                    View Location on Map
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map Section (Hidden by default) -->
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <div class="showCustomerLocationonMaps" style="display:none;">
                                <div id="mechanicMap" style="height: 600px; width: 100%; border-radius: 0.5rem;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-right mt-5">
                        <a href="{{ route('mechanics.edit', encrypt($customer->id)) }}"
                           class="btn btn-warning btn-lg mr-3 shadow-sm">
                            <i class="material-icons">edit</i> Edit Mechanic
                        </a>
                        <a href="{{ route('mechanics.index') }}"
                           class="btn btn-secondary btn-lg shadow-sm">
                            <i class="material-icons">list</i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVSDwHbKULnZa93kYpYINTqX4eaWy9q18"></script>

    <!-- Image Popup + Map Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Unique class for Mechanic images
            document.querySelectorAll('.mechanic-image-popup').forEach(img => {
                img.style.cursor = 'zoom-in';

                img.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const src = this.getAttribute('src');

                    if (document.getElementById('imagePopupOverlay')) {
                        document.getElementById('imagePopupOverlay').remove();
                    }

                    const overlay = document.createElement('div');
                    overlay.id = 'imagePopupOverlay';
                    overlay.style.position = 'fixed';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100vw';
                    overlay.style.height = '100vh';
                    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                    overlay.style.display = 'flex';
                    overlay.style.alignItems = 'center';
                    overlay.style.justifyContent = 'center';
                    overlay.style.zIndex = '9999';
                    overlay.style.padding = '40px';
                    overlay.style.boxSizing = 'border-box';

                    const imgContainer = document.createElement('div');
                    imgContainer.style.position = 'relative';
                    imgContainer.style.maxWidth = '95%';
                    imgContainer.style.maxHeight = '95%';
                    imgContainer.style.overflow = 'hidden';

                    const largeImg = document.createElement('img');
                    largeImg.src = src;
                    largeImg.style.maxWidth = '100%';
                    largeImg.style.maxHeight = '95vh';
                    largeImg.style.objectFit = 'contain';
                    largeImg.style.borderRadius = '12px';
                    largeImg.style.boxShadow = '0 10px 40px rgba(0, 0, 0, 0.6)';
                    largeImg.style.transition = 'transform 0.3s ease';
                    largeImg.style.transform = 'scale(1) translate(0px, 0px)';
                    largeImg.style.cursor = 'grab';
                    largeImg.style.userSelect = 'none';

                    // Drag & Zoom variables
                    let isDragging = false;
                    let startX, startY, translateX = 0, translateY = 0;
                    let currentScale = 1;

                    const updateTransform = () => {
                        largeImg.style.transform = `scale(${currentScale}) translate(${translateX}px, ${translateY}px)`;
                    };

                    // Zoom buttons
                    const zoomIn = document.createElement('div');
                    zoomIn.innerHTML = '+';
                    zoomIn.style.position = 'absolute';
                    zoomIn.style.bottom = '20px';
                    zoomIn.style.right = '70px';
                    zoomIn.style.fontSize = '30px';
                    zoomIn.style.color = 'white';
                    zoomIn.style.background = 'rgba(0,0,0,0.7)';
                    zoomIn.style.width = '50px';
                    zoomIn.style.height = '50px';
                    zoomIn.style.borderRadius = '50%';
                    zoomIn.style.display = 'flex';
                    zoomIn.style.alignItems = 'center';
                    zoomIn.style.justifyContent = 'center';
                    zoomIn.style.cursor = 'pointer';

                    const zoomOut = document.createElement('div');
                    zoomOut.innerHTML = '−';
                    zoomOut.style.position = 'absolute';
                    zoomOut.style.bottom = '20px';
                    zoomOut.style.right = '10px';
                    zoomOut.style.fontSize = '40px';
                    zoomOut.style.color = 'white';
                    zoomOut.style.background = 'rgba(0,0,0,0.7)';
                    zoomOut.style.width = '50px';
                    zoomOut.style.height = '50px';
                    zoomOut.style.borderRadius = '50%';
                    zoomOut.style.display = 'flex';
                    zoomOut.style.alignItems = 'center';
                    zoomOut.style.justifyContent = 'center';
                    zoomOut.style.cursor = 'pointer';

                    const closeBtn = document.createElement('div');
                    closeBtn.innerHTML = '×';
                    closeBtn.style.position = 'absolute';
                    closeBtn.style.top = '20px';
                    closeBtn.style.right = '20px';
                    closeBtn.style.fontSize = '40px';
                    closeBtn.style.color = 'white';
                    closeBtn.style.background = 'rgba(0,0,0,0.7)';
                    closeBtn.style.width = '50px';
                    closeBtn.style.height = '50px';
                    closeBtn.style.borderRadius = '50%';
                    closeBtn.style.display = 'flex';
                    closeBtn.style.alignItems = 'center';
                    closeBtn.style.justifyContent = 'center';
                    closeBtn.style.cursor = 'pointer';

                    // Zoom functionality
                    zoomIn.onclick = (e) => {
                        e.stopPropagation();
                        if (currentScale < 5) {
                            currentScale += 0.25;
                            updateTransform();
                        }
                    };

                    zoomOut.onclick = (e) => {
                        e.stopPropagation();
                        if (currentScale > 0.5) {
                            currentScale -= 0.25;
                            if (currentScale <= 1) { translateX = 0; translateY = 0; }
                            updateTransform();
                        }
                    };

                    largeImg.ondblclick = (e) => {
                        e.stopPropagation();
                        currentScale = 1; translateX = 0; translateY = 0;
                        updateTransform();
                    };

                    // Drag to pan
                    largeImg.onmousedown = (e) => {
                        if (currentScale > 1) {
                            isDragging = true;
                            startX = e.clientX - translateX;
                            startY = e.clientY - translateY;
                            largeImg.style.cursor = 'grabbing';
                            e.preventDefault();
                        }
                    };

                    document.onmousemove = (e) => {
                        if (!isDragging) return;
                        translateX = e.clientX - startX;
                        translateY = e.clientY - startY;
                        updateTransform();
                    };

                    document.onmouseup = () => {
                        isDragging = false;
                        largeImg.style.cursor = currentScale > 1 ? 'grab' : 'default';
                    };

                    // Append
                    imgContainer.appendChild(largeImg);
                    imgContainer.appendChild(closeBtn);
                    imgContainer.appendChild(zoomIn);
                    imgContainer.appendChild(zoomOut);
                    overlay.appendChild(imgContainer);
                    document.body.appendChild(overlay);

                    // Close
                    const closePopup = () => {
                        overlay.style.opacity = '0';
                        setTimeout(() => overlay.remove(), 300);
                    };

                    overlay.onclick = (e) => {
                        if (e.target === overlay || e.target === closeBtn) closePopup();
                    };

                    document.addEventListener('keydown', function esc(e) {
                        if (e.key === 'Escape') { closePopup(); document.removeEventListener('keydown', esc); }
                    });
                });
            });
        });

        // Map Function for Mechanic
        function showMechanicOnMap() {
            const gps = "{{ $customer->gps_location }}";
            if (!gps) return;

            const [lat, lng] = gps.split(',').map(coord => parseFloat(coord.trim()));
            if (isNaN(lat) || isNaN(lng)) {
                alert("Invalid GPS coordinates.");
                return;
            }

            const map = new google.maps.Map(document.getElementById('mechanicMap'), {
                zoom: 16,
                center: { lat, lng },
                mapTypeId: 'hybrid',
                streetViewControl: true,
                fullscreenControl: true
            });

            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
                animation: google.maps.Animation.DROP
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="min-width:220px; padding: 8px;">
                        <h5 class="mb-2"><strong>{{ addslashes($customer->shop_name) }}</strong></h5>
                        <p class="mb-1"><strong>Owner:</strong> {{ $customer->owner_name }}</p>
                        <p class="mb-1"><strong>Mobile:</strong> {{ $customer->mobile_number }}</p>
                        <p class="mb-0 text-muted">
                            <small>{{ $customer->address_line ?? '' }}<br>
                            {{ $city?->city_name ?? '' }}, {{ $state?->state_name ?? '' }}</small>
                        </p>
                    </div>
                `
            });

            infoWindow.open(map, marker);
            marker.addListener('click', () => infoWindow.open(map, marker));

            $('.showCustomerLocationonMaps').show();
            document.querySelector('.showCustomerLocationonMaps').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</x-app-layout>