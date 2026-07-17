<x-app-layout>
    <style>
  .address-card {
    border: 1px solid #374879;
    border-radius: 8px;
    padding: 12px;
    min-height: 70px;
    background: radial-gradient(120% 90% at 18% 8%, #0d2358 0, transparent 55%), radial-gradient(110% 80% at 95% 95%, #0a1b45 0, transparent 60%), var(--fk-bg) !important;
}
form#storeOrderData18 {
    margin: 10px;
}

    .address-title {
        font-weight: 600;
        font-size: 13px;
        color: #666;
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .address-title i {
        font-size: 18px;
        margin-right: 5px;
        color: #ff9800;
    }

    #tab_logic {
        table-layout: fixed;
        width: 100%;
    }

    #tab_logic th,
    #tab_logic td {
        width: 16.6%;
    }

    #tab_logic select,
    #tab_logic input {
        width: 100% !important;
    }

    #tab_logic th:nth-child(1),
    #tab_logic td:nth-child(1) {
        width: 5%;
    }

    #tab_logic th:nth-child(7),
    #tab_logic td:nth-child(7) {
        width: 8%;
    }

    #tab_logic th:nth-child(2),
    #tab_logic td:nth-child(2),
    #tab_logic th:nth-child(3),
    #tab_logic td:nth-child(3),
    #tab_logic th:nth-child(4),
    #tab_logic td:nth-child(4),
    #tab_logic th:nth-child(5),
    #tab_logic td:nth-child(5),
    #tab_logic th:nth-child(6),
    #tab_logic td:nth-child(6) {
        width: 17%;
    }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card productlistpage">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title">
                        {{ trans('panel.global.create') }} {{ trans('panel.order.title_singular') }}
                        <span class="pull-right">
                            <div class="btn-group">
                                @if(auth()->user()->can(['order_access']))
                                <a href="{{ url('orders') }}" class="btn btn-just-icon btn-theme"
                                    title="{{ trans('panel.order.title') }} List">
                                    <i class="material-icons">next_plan</i>
                                </a>
                                @endif
                            </div>
                        </span>
                    </h4>
                </div>

                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="material-icons">close</i>
                        </button>
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {!! Form::model($orders, [
                    'route' => $orders->exists ? ['orders.update', encrypt($orders->id)] : 'orders.store',
                    'method' => $orders->exists ? 'PUT' : 'POST',
                    'id' => 'storeOrderData18',
                    'files' => true
                    ]) !!}

                    <div class="row">
                        <div class="col-md-4">
                            <label class="col-form-label">{{ trans('panel.order.order_date') }}</label>
                            <div class="form-group has-default bmd-form-group">
                                <input type="text" name="order_date" class="form-control datepicker" id="order_date"
                                    value="{{ old('order_date', $orders->order_date ?? date('Y-m-d')) }}"
                                    autocomplete="off" readonly>
                                @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">Employee</label>
                            <div class="form-group has-default bmd-form-group">
                                <select class="form-control select2" name="executive_id" required style="width:100%;">
                                    <option value="">Select Employee</option>
                                    @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('executive_id', $orders->executive_id ?? '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('executive_id')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="col-form-label">Customer Type</label>
                            <select name="customer_type_id" id="type" class="form-control select2" required>
                                <option value="">Select Customer Type</option>
                                @foreach($customerTypes as $customerType)
                                <option value="{{ $customerType->id }}"
                                    {{ (string) old('customer_type_id') === (string) $customerType->id ? 'selected' : '' }}>
                                    {{ $customerType->customertype_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">

                        <!-- Customer -->
                        <div class="col-md-6" id="de_dis">
                            <label class="col-form-label">Customer <span class="text-danger">*</span></label>

                            <select class="form-control select2 buyer" name="buyer_id" id="buyer_id" required>
                                <option value="">Select Customer</option>
                            </select>

                            <div class="address-card mt-2">
                                <div class="address-title">
                                    <i class="material-icons">location_on</i> Customer Address
                                </div>
                                @php
                                $city = null;
                                if(!empty($paln) && !empty($paln->town)){
                                $city = \App\Models\City::find($paln->town);
                                }
                                @endphp
                                <div class="buyer_address address-text">
                                    Select distributor to view address
                                </div>

                            </div>
                        </div>

                        <!-- Distributor -->
                        <div class="col-md-6" id="seller_div" style="display:none;">
                            <label class="col-form-label">Parent Customer <span
                                    class="text-danger">*</span></label>

                            <select class="form-control select2" name="seller_id" id="seller_id">
                                <option value="">Select Parent Customer</option>
                            </select>

                            <div class="address-card mt-2">
                                <div class="address-title">
                                    <i class="material-icons">account_tree</i> Parent Customer Address
                                </div>
                                <div id="customer_address_div" class="address-text">
                                    Select customer to view address
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row mt-3">
                        <!-- @if($orders->exists && $orders->orderno)
                        <div class="col-md-6">
                            <label class="col-form-label">{{ trans('panel.order.orderno') }}</label>
                            <input type="text" class="form-control" name="orderno"
                                value="{{ old('orderno', $orders->orderno) }}">
                            @error('orderno')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif -->
                        <!-- 
                        <div class="col-md-6">
                            <label class="col-form-label">Zone</label>
                            <select name="product_cat_id" id="product_cat_id" class="form-control select2" required>
                                <option value="">Select Zone</option>
                                @foreach($category ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div> -->
                    </div>

                    <!-- Items Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table kvcodes-dynamic-rows-example" id="tab_logic">
                                    <thead class="text-white">
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Sub Category</th>
                                            <th class="text-center">Products</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Unit Price</th>
                                            <th class="text-center">Amount</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($orders->exists && $orders->orderdetails)
                                        @foreach($orders->orderdetails as $key => $detail)
                                        <tr id="addr{{ $key }}" value="{{ $key + 1 }}">
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <select name="orderdetail[{{ $key }}][subcategory_id]"
                                                    class="form-control select2 subcategory-select"
                                                    data-row="{{ $key }}">
                                                    <option value="">Select Sub Category</option>
                                                    @foreach($subcategories as $sub)
                                                    <option value="{{ $sub->id }}"
                                                        {{ old("orderdetail.$key.subcategory_id", $detail->subcategory_id ?? '') == $sub->id ? 'selected' : '' }}>
                                                        {{ $sub->subcategory_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="orderdetail[{{ $key }}][product_id]"
                                                    class="form-control product rowchange select2">
                                                    <option value="">Select Product</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="orderdetail[{{ $key }}][quantity]"
                                                    class="form-control quantity rowchange" min="0"
                                                    value="{{ $detail->quantity ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" name="orderdetail[{{ $key }}][price]"
                                                    class="form-control price rowchange" min="0" step="0.01"
                                                    value="{{ $detail->price ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" name="orderdetail[{{ $key }}][line_total]"
                                                    class="form-control total" readonly
                                                    value="{{ $detail->line_total ?? '' }}">
                                            </td>
                                            <td class="text-center">
                                                <a class="remove-rows btn btn-danger btn-xs"><i
                                                        class="fa fa-minus"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <!-- New order - one empty row -->
                                        <tr id="addr0" value="1">
                                            <td>1</td>
                                            <td>
                                                <select name="orderdetail[0][subcategory_id]"
                                                    class="form-control select2 subcategory-select" data-row="0">
                                                    <option value="">Select Sub Category</option>
                                                    @foreach($subcategories as $sub)
                                                    <option value="{{ $sub->id }}">{{ $sub->subcategory_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="orderdetail[0][product_id]"
                                                    class="form-control product rowchange select2" data-row="0">
                                                    <option value="">Select Product</option>
                                                </select>
                                            </td>
                                            <td><input type="number" name="orderdetail[0][quantity]"
                                                    class="form-control quantity rowchange" min="0"></td>
                                            <td><input type="number" name="orderdetail[0][price]"
                                                    class="form-control price rowchange" min="0" step="0.01"></td>
                                            <td><input type="number" name="orderdetail[0][line_total]"
                                                    class="form-control total" readonly></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-xs remove-rows"><i
                                                        class="fa fa-minus"></i></button>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-left">
                        <button type="button" class="btn btn-success btn-sm add-rows">
                            <i class="fa fa-plus"></i> Add Row
                        </button>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">{{ trans('panel.order.grand_total') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" class="form-control" id="grandtotal" name="grand_total"
                                        value="{{ old('grand_total', $orders->grand_total ?? '') }}" readonly>
                                    @error('grand_total')
                                    <input type="hidden" name="ebd_amount" id="ebd_amount">
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-theme">Submit</button>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        window.cities = @json(\App\Models\City::pluck('city_name', 'id'));
        window.states = @json(\App\Models\State::pluck('state_name', 'id'));
        window.districts = @json(\App\Models\District::pluck('district_name', 'id'));
        window.countries = @json(\App\Models\Country::pluck('country_name', 'id'));
        window.pincodes = @json(\App\Models\Pincode::pluck('pincode', 'id'));
    </script>

    <script>
    $(document).ready(function() {
        $('.select2').select2();
        initSellerDropdown();
        initBuyerDropdown();


        $('#storeOrderData18').on('submit', function(e) {

            //  e.preventDefault();

            let payload = $(this).serializeArray();

            let obj = {};

            payload.forEach(function(item) {
                obj[item.name] = item.value;
            });

            console.log(JSON.stringify(obj, null, 2));

        });

        // Add new row
        $('.add-rows').click(function(e) {

            e.preventDefault();

            let rowCount = $('#tab_logic tbody tr').length;

            let newRow = `
    <tr>
        <td class="row-number">${rowCount + 1}</td>

        <td>
            <select name="orderdetail[${rowCount}][subcategory_id]" class="form-control select2 subcategory-select" data-row="${rowCount}">
                <option value="">Select Sub Category</option>
                ${$('#tab_logic tbody tr:first .subcategory-select').html().replace(/selected/g,'')}
            </select>
        </td>

        <td>
            <select name="orderdetail[${rowCount}][product_id]" class="form-control product rowchange select2">
                <option value="">Select Product</option>
            </select>
        </td>

        <td>
            <input type="number" name="orderdetail[${rowCount}][quantity]" class="form-control quantity rowchange" min="0">
        </td>

        <td>
            <input type="number" name="orderdetail[${rowCount}][price]" class="form-control price rowchange" min="0" step="0.01">
        </td>

        <td>
            <input type="number" name="orderdetail[${rowCount}][line_total]" class="form-control total" readonly>
        </td>

        <td class="text-center">
            <button type="button" class="btn btn-danger btn-xs remove-rows">
                <i class="fa fa-minus"></i>
            </button>
        </td>
    </tr>
    `;

            $('#tab_logic tbody').append(newRow);

            $('.select2').select2();

        });

        // Remove row
        $('#tab_logic').on('click', '.remove-rows', function() {

            if ($('#tab_logic tbody tr').length == 1) {
                alert('At least one row required');
                return;
            }

            $(this).closest('tr').remove();

            updateRowNumbers();
            calc();

        });

        function updateRowNumbers() {

            $('#tab_logic tbody tr').each(function(index) {

                $(this).find('.row-number').text(index + 1);

                $(this).find('select, input').each(function() {

                    let name = $(this).attr('name');

                    if (name) {
                        name = name.replace(/orderdetail\[\d+\]/, 'orderdetail[' + index + ']');
                        $(this).attr('name', name);
                    }

                });

            });

        }

        // Recalculate on change
        $('#tab_logic').on('change keyup', '.rowchange', calc);

        // Family → load products
        $(document).on('change', '.subcategory-select', function() {
            let row = $(this).data('row');
            let subcat_id = $(this).val();
            let $product = $(`select[name="orderdetail[${row}][product_id]"]`);

            $product.html('<option value="">Loading...</option>').trigger('change');

            if (!subcat_id) {
                $product.html('<option value="">Select Product</option>');
                return;
            }

            $.get("{{ route('getProductsBySubcategory') }}", {
                subcategory_id: subcat_id
            }, function(res) {
                let html = '<option value="">Select Product</option>';
                $.each(res.products || [], function(i, p) {
                    html += `<option value="${p.id}" data-price="${p.unit_price ?? 0}" data-hsn="${p.hsn_sac ?? ''}">
                        ${p.product_name} (${p.product_code || ''})
                    </option>`;
                });
                $product.html(html).trigger('change');
            });
        });

        // Product price is the default; the order price remains editable.
        $(document).on('change', '.product', function() {
            let $row = $(this).closest('tr');
            let $selected = $(this).find('option:selected');
            if (!$selected.val()) {
                $row.find('.price').val('');
                calc();
                return;
            }
            if (typeof $selected.attr('data-price') === 'undefined') return;
            let price = parseFloat($selected.attr('data-price')) || 0;
            $row.find('.price').val(price.toFixed(2));
            calc();
        });

        // Customer Type logic
        $('#type').on('change', function() {

            let type = $(this).val();

            const isRetailer = String(type) === String(@json($retailerCustomerTypeId));

            $('#de_dis').toggle(Boolean(type));
            $('#seller_div').toggle(isRetailer);
            $('#buyer_id').prop('required', Boolean(type));
            $('#seller_id').prop('required', isRetailer);

            $('#seller_id').val(null).trigger('change');
            $('#buyer_id').val(null).trigger('change');

            // Reinitialize both dropdowns
            initBuyerDropdown();

        }).trigger('change');

        function initSellerDropdown() {

            if ($('#seller_id').hasClass('select2-hidden-accessible')) {
                $('#seller_id').select2('destroy');
            }
            $('#seller_id').off('.orderParent');

            $('#seller_id').select2({

                    placeholder: 'Select parent customer...',
                    allowClear: true,

                    ajax: {
                        url: "{{ route('getCustomerDataSelect') }}",
                        dataType: 'json',
                        delay: 250,

                        data: params => ({
                            term: params.term || '',
                            parent_of: $('#buyer_id').val(),
                            page: params.page || 1
                        }),

                        processResults: data => ({

                            results: data.results.map(item => {

                                let addr = item.customeraddress || {};

                                let cityname = window.cities[addr.city_id] || '';
                                let districtname = window.districts[addr.district_id] || '';
                                let statename = window.states[addr.state_id] || '';
                                let countryname = window.countries[addr.country_id] || '';
                                let pincodename = window.pincodes[addr.pincode_id] || '';

                                let fullAddress = [
                                    addr.address1,
                                    cityname,
                                    districtname,
                                    statename,
                                    countryname,
                                    pincodename
                                ].filter(Boolean).join(', ');

                                return {
                                    id: item.id,
                                    text: item.text,

                                    address1: addr.address1,
                                    cityname: cityname,
                                    districtname: districtname,
                                    statename: statename,
                                    countryname: countryname,
                                    pincodename: pincodename,

                                    full_address: fullAddress
                                };

                            }),

                            pagination: {
                                more: data.pagination.more
                            }

                        }),

                        cache: true
                    }

                })

                .on('select2:select.orderParent', function(e) {

                    let data = e.params.data;

                    let address = [
                        data.address1,
                        data.cityname,
                        data.districtname,
                        data.statename,
                        data.countryname,
                        data.pincodename
                    ].filter(Boolean).join(', ');

                    $('#customer_address_div').text(data.full_address || address);

                })

                .on('select2:unselect.orderParent', function() {

                    $('#customer_address_div').text('');

                });

        }

        function autoSelectAssignedParent(customerId) {
            if (!customerId) return;

            $.get("{{ route('getCustomerDataSelect') }}", {
                parent_of: customerId,
                page: 1
            }, function(response) {
                const assignedParent = (response.results || []).find(item => item.is_assigned_parent);
                if (!assignedParent) return;

                const option = new Option(assignedParent.text, assignedParent.id, true, true);
                $('#seller_id').append(option).trigger('change');
                $('#customer_address_div').text(assignedParent.full_address || '');
            });
        }

        function initBuyerDropdown() {
            $('#buyer_id').select2({
                placeholder: 'Select Customer...',
                allowClear: true,
                ajax: {
                    url: "{{ route('getCustomerDataSelect') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        customer_type_id: $('#type').val(),
                        page: params.page || 1
                    }),
                    processResults: data => ({
                        results: data.results.map(item => ({
                            id: item.id,
                            text: item.text,
                            full_address: item.full_address
                        })),
                        pagination: {
                            more: data.pagination.more
                        }
                    }),
                    cache: true
                }
            }).on('select2:select', e => {
                $('.buyer_address').text(e.params.data.full_address || '');
                if (String($('#type').val()) === String(@json($retailerCustomerTypeId))) {
                    $('#seller_id').val(null).trigger('change');
                    initSellerDropdown();
                    autoSelectAssignedParent(e.params.data.id);
                }
            }).on('select2:unselect', () => {
                $('.buyer_address').text('');
                $('#seller_id').val(null).trigger('change');
            });
        }

        function calc() {
            let grand = 0;
            $('#tab_logic tbody tr').each(function() {
                let qty = parseFloat($(this).find('.quantity').val()) || 0;
                let price = parseFloat($(this).find('.price').val()) || 0;
                let total = qty * price;
                $(this).find('.total').val(total.toFixed(2));
                grand += total;
            });
            $('#grandtotal').val(grand.toFixed(2));
            $('#ebd_amount').val(grand.toFixed(2));
        }

        // Trigger initial calculation (edit mode)
        $('.subcategory-select').trigger('change');
        calc();
    });
    </script>
</x-app-layout>
