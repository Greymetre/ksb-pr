<x-app-layout>
    <style>
        .customer-filter-data {
            cursor: pointer;
            border: 1px solid #ccc;
            padding: 5px;
            margin: 5px;
            border-radius: 5px;
            background: #ccc;
            text-align: center;
            align-content: center;
            text-shadow: 0px 2px 1px #fff;
            font-weight: 500;
        }

        .filter-data {
            height: 300px;
            overflow-y: auto;
        }

        #search-btn {
            color: #000;
            font-size: 18px;
            position: absolute;
            right: 6px;
            cursor: pointer;
            z-index: 999999 !important;
            height: 35px;
            border-left: 1px solid lightgrey;
            padding: 9px;
        }

        ul#suggestion-list {
            background: #7673737d;
            width: 500px !important;
            font-weight: 500;
            line-height: 15px;
        }

        li.list-group-item.suggestion-item {
            border: 1px dashed;
            color: #FFF;
            cursor: pointer;
            text-shadow: 0px 1px 2px #000;
        }

        li.list-group-item.suggestion-item:hover {
            background: #767373;
        }

        .gm-ui-hover-effect {
            width: 35px !important;
            /* Set custom width */
            height: 30px !important;
        }

        /* Optionally, make the X icon itself bigger */
        .gm-ui-hover-effect span {
            width: 15px !important;
            height: 15px !important;
            margin: 8px 10px !important;
        }

        table.geodatatable tbody tr td,
        table.geodatatable tbody tr th {
            font-size: 12px !important;
            padding: 4px;
        }

        table.geodatatable tbody tr th {
            font-weight: 700 !important;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title ">Complete Map </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="search-container">
                                <div class="input-group">
                                    <input type="search" id="search" class="form-control" placeholder="Search">
                                    <div class="input-group-append" id="search-btn">
                                        <i class="fa fa-search"></i>
                                    </div>
                                </div>
                                <ul id="suggestion-list" class="list-group position-absolute w-100" style="z-index:9999999;"></ul>
                            </div>
                            <div id="map"></div>
                        </div>
                        <div class="col-md-4">
                            <div id="sidebar">
                                <h2 class="text-dark">Geo Locator</h2>
                                <div class="d-flex" style="align-items: baseline;">
                                    <input type="radio" name="type" value="1" id="type" class="mr-2" checked>
                                    <p class="text-dark mr-2">Customer</p>
                                    <input type="radio" class="mr-2" name="type" value="2" id="type">
                                    <p class="text-dark mr-2">Lead</p>
                                </div>
                                <div class="filter_div">
                                    @php
                                    $customerFilters = $setting->customer_filter ?? [];
                                    $leadFilters = $setting->lead_filter ?? [];
                                    @endphp
                                    <select name="customer_filter" id="customer_filter" class="form-control">
                                        <option value="">Customer Filter</option>
                                        @if($customerFilters && count($customerFilters) > 0)
                                        @foreach($customerFilters as $filter)
                                        <option value="{{ $filter }}" data-custom_field="false">{{ $filter }}</option>
                                        @endforeach
                                        @endif
                                        @if($cutom_fields && count($cutom_fields) > 0)
                                        @foreach($cutom_fields as $field)
                                        <option value="{{ $field->field_name }}" data-custom_field="true">{{ $field->field_name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <select name="lead_filter" id="lead_filter" class="form-control">
                                        <option value="">Lead Filter</option>
                                        @if($leadFilters && count($leadFilters) > 0)
                                        @foreach($leadFilters as $filter)
                                        <option value="{{ $filter }}" data-custom_field="false">{{ $filter }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="filter-data row mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVSDwHbKULnZa93kYpYINTqX4eaWy9q18"></script>

    <script>
        function centerMapByState(stateName, zoomLevel = 7) {
            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({
                address: stateName + ", India"
            }, (results, status) => {
                if (status === "OK" && results[0]) {
                    const location = results[0].geometry.location;
                    map.setCenter(location);
                    map.setZoom(zoomLevel); // adjust zoom for state-level view
                } else {
                    console.error("Geocode failed: " + status);
                }
            });
        }
        let map;

        function initMap(filter = '', filter_by = '', custom_field = false) {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: 20.5937,
                    lng: 78.9629
                }, // center of India or wherever
                zoom: 5
            });
            let type = $('input[name="type"]:checked').val();
            infoWindow = new google.maps.InfoWindow();
            // fetch customers
            fetch("{{ route('geolocator.data') }}?type=" + type + "&filter=" + filter + "&filter_by=" + filter_by + "&custom_field=" + custom_field)
                .then(response => response.json())
                .then(customers => {
                    if (!customers.length) return;
                    if (customers.length === 1 && customers[0].latitude && customers[0].longitude) {
                        map.setCenter({
                            lat: parseFloat(customers[0].latitude),
                            lng: parseFloat(customers[0].longitude)
                        });
                        map.setZoom(10); // zoom in closer
                    } else if (filter_by == 'State' || filter_by == 'City' || filter_by == 'District' || filter_by == 'Pincode') {
                        map.setCenter({
                            lat: parseFloat(customers[0].latitude),
                            lng: parseFloat(customers[0].longitude)
                        });
                        if (filter_by == 'City' || filter_by == 'District' || filter_by == 'Pincode') {
                            map.setZoom(8);
                        } else {
                            map.setZoom(6); // zoom in closer
                        }
                    }
                    customers.forEach(cust => {
                        if (!cust.latitude || !cust.longitude) return;

                        let iconUrl;
                        switch (cust.customertype) {
                            case 1:
                                iconUrl = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
                                break;
                            case 2:
                                iconUrl = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
                                break;
                            case 3:
                                iconUrl = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
                                break;
                            default:
                                iconUrl = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
                        }
                        const marker = new google.maps.Marker({
                            position: {
                                lat: parseFloat(cust.latitude),
                                lng: parseFloat(cust.longitude)
                            },
                            map: map,
                            title: cust.name,
                            icon: iconUrl
                        });
                        marker.addListener('click', () => {
                            var contentString;
                            if (type == '1') {
                                contentString = `
                                    <div style="min-width:200px">
                                        <h5 class='text-dark'>Details</h5>
                                        <table class="table table-sm table-borderless m-0 geodatatable">
                                            <tr>
                                                <th>Firm Name: </th>
                                                <td><h4 class='text-dark mb-0'>${cust.name}</h4></td>
                                            </tr>
                                            <tr>
                                                <th>Contact Person: </th>
                                                <td class='text-dark mb-0'>${cust.first_name} ${cust.last_name}</td>
                                            </tr>
                                            <tr>
                                                <th>Customer Type: </th>
                                                <td class='text-dark mb-0'>${cust.customertypes?.customertype_name || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Mobile Number: </th>
                                                <td class='text-dark mb-0'>${cust.mobile || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Pin Code: </th>
                                                <td class='text-dark mb-0'>
                                                    ${cust.customeraddress?.pincodename?.pincode || ''}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>City: </th>
                                                <td class='text-dark mb-0'>
                                                    ${cust.customeraddress?.cityname?.city_name || ''} 
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>District: </th>
                                                <td class='text-dark mb-0'>
                                                    ${cust.customeraddress?.districtname?.district_name || ''}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>State: </th>
                                                <td class='text-dark mb-0'>${cust.customeraddress?.statename?.state_name || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Grade: </th>
                                                <td class='text-dark mb-0'>${cust.customerdetails?.grade || ''}</td>
                                            </tr>
                                        </table>
                                    </div>
                                `;

                            } else {
                                contentString = `
                                <div style="min-width:200px">
                                        <h5 class='text-dark'>Details</h5>
                                        <table class="table table-sm table-borderless m-0 geodatatable">
                                            <tr>
                                                <th>Firm Name: </th>
                                                <td><h4 class='text-dark mb-0'>${cust.company_name}</h4></td>
                                            </tr>
                                            <tr>
                                                <th>Customer Name: </th>
                                                <td class='text-dark mb-0'>${cust.contacts[0]?.name || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Lead Status: </th>
                                                <td class='text-dark mb-0'>${cust.status_is?.status_name || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Lead Source: </th>
                                                <td class='text-dark mb-0'>${cust.lead_source}</td>
                                            </tr>
                                            <tr>
                                                <th>Mobile Number: </th>
                                                <td class='text-dark mb-0'>${cust.contacts[0]?.phone_number || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Pin Code: </th>
                                                <td class='text-dark mb-0'>
                                                    ${cust.address?.pincodename?.pincode || ''}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>City: </th>
                                                <td class='text-dark mb-0'>
                                                    ${cust.address?.cityname?.city_name || ''} 
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>District: </th>
                                                <td class='text-dark mb-0'>
                                                    ${cust.address?.districtname?.district_name || ''}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>State: </th>
                                                <td class='text-dark mb-0'>${cust.address?.statename?.state_name || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Assigned To: </th>
                                                <td class='text-dark mb-0'>${cust.assign_user?.name || ''}</td>
                                            </tr>
                                            <tr>
                                                <th>Opportunity: </th>
                                                <td class='text-dark mb-0'>
                                                    ${
                                                        cust.opportunities && cust.opportunities.length > 0
                                                            ? `${cust.opportunities[0]?.status_is?.status_name || ''} - ₹${cust.opportunities[0]?.amount || ''}`
                                                            : 'Opportunity not available'
                                                    }
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Address: </th>
                                                <td class='text-dark mb-0'>${cust.address?.full_address || ''}</td>
                                            </tr>
                                        </table>
                                    </div>
                                `;
                            }
                            infoWindow.setContent(contentString);
                            infoWindow.open(map, marker);
                        });
                    });
                })
                .catch(err => console.error(err));
        }

        window.onload = initMap;

        $('input[name="type"]').on('change', function() {
            var type = $('input[name="type"]:checked').val();
            if (type == '1') {
                $('#customer_filter').show();
                $('#lead_filter').hide();
            } else if (type == '2') {
                $('#customer_filter').hide();
                $('#lead_filter').show();
            }
            $('.filter-data').html('');
            initMap();
        }).trigger('change');

        $("#customer_filter, #lead_filter").on("change", function() {
            initMap();
            var type = $('input[name="type"]:checked').val();
            if (type == '1') {
                var filter = $('#customer_filter').val();
                var custom_field = $('#customer_filter option:selected').data('custom_field');
            } else {
                var filter = $('#lead_filter').val();
                var custom_field = $('#lead_filter option:selected').data('custom_field');
            }
            fetch("{{ route('geolocator.filter.data') }}?type=" + type + "&filter=" + filter + "&custom_field=" + custom_field)
                .then(response => response.json())
                .then(response => {
                    $('.filter-data').html('');

                    for (let city in response) {
                        $('.filter-data').append(
                            '<p class="col-5 text-dark customer-filter-data" data-id="' + city + '">' + city + ' (' + response[city] + ')</p>'
                        );
                    }
                })
                .catch(err => console.error(err));
        });

        $(document).on('click', '.customer-filter-data', function() {
            var filter = $(this).data('id');
            var type = $('input[name="type"]:checked').val();
            if (type == '1') {
                var filter_by = $('#customer_filter').val();
                var custom_field = $('#customer_filter option:selected').data('custom_field');
            } else {
                var filter_by = $('#lead_filter').val();
                var custom_field = $('#lead_filter option:selected').data('custom_field');
            }
            initMap(filter, filter_by, custom_field);
        });

        $("#search-btn").on("click", function() {
            var search = $("#search").val();
            initMap(search, 'search');
        });

        $("#search").on("keypress", function(e) {
            if (e.which === 13) { // 13 is Enter key
                var search = $(this).val();
                initMap(search, 'search');
            }
        });


        $(document).ready(function() {
            $('#search').on('keyup', function() {
                let query = $(this).val();
                var type = $('input[name="type"]:checked').val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('customer.suggestions') }}",
                        type: "GET",
                        data: {
                            search: query,
                            type: type
                        },
                        success: function(data) {
                            $('#suggestion-list').empty();
                            if (data.length > 0) {
                                data.forEach(function(item) {
                                    $('#suggestion-list').append(
                                        '<li class="list-group-item suggestion-item">' + item.name + '</li>'
                                    );
                                });
                            } else {
                                $('#suggestion-list').append('<li class="list-group-item">No results</li>');
                            }
                            $('#suggestion-list').show();
                        }
                    });
                } else {
                    $('#suggestion-list').empty();
                    $('#suggestion-list').hide();
                }
            });

            // Click suggestion
            $(document).on('click', '.suggestion-item', function() {
                $('#search').val($(this).text());
                $('#suggestion-list').empty().hide();
            });

            // Close suggestion list when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('#search, #suggestion-list').length) {
                    $('#suggestion-list').empty().hide();
                }
            });
        });
    </script>
</x-app-layout>