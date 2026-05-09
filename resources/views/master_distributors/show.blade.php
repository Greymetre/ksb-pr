<x-app-layout>

    <style>
        .card.text-center .card-body.p-3 {
            font-size: 16px;
            font-weight: 900;
            line-height: 40px;
            text-shadow: 2px 2px 10px gray;
        }
    </style>

    <!-- Points Summary Cards (Agar Master Distributor ke liye points system hai to rakho, warna remove kar dena) -->
    <div class="row all-points mb-4">
        <div class="col-md-2">
            <div class="card card-primary text-center card-body p-3">
                Total Point Earn <br> <span>{{ $total_points ?? 0 }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-primary text-center card-body p-3">
                Total Active Point <br> <span>{{ $active_points ?? 0 }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-primary text-center card-body p-3">
                Total Provision Point <br> <span>{{ $provision_points ?? 0 }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-success text-center card-body p-3">
                Total Redeem Point <br> <span>{{ $total_redemption ?? 0 }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-danger text-center card-body p-3">
                Total Rejected Point <br> <span>{{ $total_rejected ?? 0 }}</span>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-info text-center card-body p-3">
                Total Balance Point <br> <span>{{ $total_balance ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card card-body">
                <!-- Edit & Download Buttons -->
                <div class="row">
                    <div class="col-md-12">
                        <span class="float-right">
                            <div class="btn-group">
                                @if(auth()->user()->can(['master-distributor_edit']))
                                <a href="{{ route('master-distributors.edit', encrypt($distributor->id)) }}" class="btn btn-just-icon btn-theme">
                                    <i class="material-icons">edit</i>
                                </a>
                                @endif

                                <!-- Agar transaction history download chahiye to add kar sakte ho -->
                            </div>
                        </span>
                    </div>
                </div>

                

                <!-- Profile Header with Image and Name -->
                <div class="row gx-4 mb-2 view-tab align-items-center">
                    <div class="col-auto">
                        <div class="avatar avatar-xl position-relative">
                            <img style="border-radius: 10%;" 
                                 src="{{ !empty($distributor->profile_image) ? asset('storage/' . $distributor->profile_image) : asset('/assets/img/placeholder.jpg') }}" 
                                 alt="Profile Image" 
                                 class="w-100 border-radius-lg shadow-sm imageDisplayModel">
                        </div>
                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">{{ $distributor->legal_name ?? '' }}</h5>
                            <p class="font-weight-normal text-sm">
                                {{ $distributor->trade_name ?? '' }}
                            </p>
                        </div>
                    </div>

                    <!-- Tabs (Agar Master Distributor ke liye tabs chahiye to add kar sakte ho, abhi simple rakha hai) -->
                    <!-- Agar future mein Orders, Sales etc. tabs chahiye to bata dena -->
                </div>

                <div class="">
                    <div class="tab-content tab-subcategories">
                        <div class="tab-pane active show" id="profile-tabs-detail">
                            <div class="row mt-3">

                                <!-- Personal & Basic Info -->
                                <div class="col-md-4 col-xl-4 mt-md-0 mt-4 position-relative">
                                    <div class="card card-plain h-100">
                                        <div class="card-body p-3">
                                            <div class="ctmr-box">
                                                <h6>Basic Information</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                        <strong class="text-dark">Distributor Code:</strong> &nbsp; {{ $distributor->distributor_code ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Contact Person:</strong> &nbsp; {{ $distributor->contact_person ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Mobile:</strong> &nbsp; 
                                                        @if($distributor->mobile)
                                                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $distributor->mobile) }}" target="_blank" class="text-success">
                                                                <i class="fab fa-whatsapp"></i> {{ $distributor->mobile }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Email:</strong> &nbsp; {{ $distributor->email ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Business Status:</strong> &nbsp;
                                                        <span class="badge badge-{{ $distributor->business_status === 'Active' ? 'success' : 'danger' }}">
                                                            {{ strtoupper($distributor->business_status ?? 'N/A') }}
                                                        </span>
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Start Date:</strong> &nbsp; 
                                                        {{ $distributor->business_start_date ? \Carbon\Carbon::parse($distributor->business_start_date)->format('d M Y') : '-' }}
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="ctmr-box">
                                                <h6>Billing Address</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                        <strong class="text-dark">Address:</strong> &nbsp; {{ $distributor->billing_address ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">City:</strong> &nbsp; {{ $distributor->billing_city ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">District:</strong> &nbsp; {{ $distributor->billing_district ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">State:</strong> &nbsp; {{ $distributor->billing_state ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Pincode:</strong> &nbsp; {{ $distributor->billing_pincode ?? '-' }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- KYC & Banking + Business Capacity -->
                                <div class="col-md-4 col-xl-4 mt-md-0 mt-4 position-relative">
                                    <div class="card card-plain h-100">
                                        <div class="card-body p-3">
                                            <div class="ctmr-box">
                                                <h6>KYC & Banking Details</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                        <strong class="text-dark">GST Number:</strong> &nbsp; {{ $distributor->gst_number ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">PAN Number:</strong> &nbsp; {{ $distributor->pan_number ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Bank Name:</strong> &nbsp; {{ $distributor->bank_name ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Account Holder:</strong> &nbsp; {{ $distributor->account_holder ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Account Number:</strong> &nbsp; {{ $distributor->account_number ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">IFSC Code:</strong> &nbsp; {{ $distributor->ifsc ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Cancelled Cheque:</strong> &nbsp;
                                                        @if($distributor->cancelled_cheque)
                                                            <a href="{{ asset('storage/' . $distributor->cancelled_cheque) }}" target="_blank" class="btn btn-sm btn-theme">
                                                                <i class="material-icons">visibility</i> View
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="ctmr-box">
                                                <h6>Business Capacity</h6>
                                                <ul class="list-group">
                                                    <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                                        <strong class="text-dark">Credit Limit:</strong> &nbsp; ₹{{ number_format($distributor->credit_limit ?? 0) }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Credit Days:</strong> &nbsp; {{ $distributor->credit_days ?? '-' }} days
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Monthly Sales:</strong> &nbsp; ₹{{ number_format($distributor->monthly_sales ?? 0) }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Turnover:</strong> &nbsp; ₹{{ number_format($distributor->turnover ?? 0) }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Staff Strength:</strong> &nbsp; {{ $distributor->staff_strength ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Vehicles:</strong> &nbsp; {{ $distributor->vehicles_capacity ?? '-' }}
                                                    </li>
                                                    <li class="list-group-item border-0 ps-0 text-sm">
                                                        <strong class="text-dark">Warehouse Size:</strong> &nbsp; {{ $distributor->warehouse_size ?? '-' }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activities Timeline (Right Side) -->
                                <div class="col-md-4 mt-md-0 mt-4">
                                    <div class="card card-plain h-100 activity-conv">
                                        <div class="card-header pb-0 p-3">
                                            <h6>Recent Activities</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <ul class="timeline timeline-simple">
                                                <!-- Agar activities hai to loop mein daal dena, abhi placeholder -->
                                                <li class="timeline-inverted">
                                                    <div class="timeline-badge info">
                                                        <i class="material-icons">add_task</i>
                                                    </div>
                                                    <div class="timeline-panel">
                                                        <div class="timeline-body">
                                                            <p>Distributor created</p>
                                                        </div>
                                                        <h6><i class="ti-time"></i> {{ $distributor->created_at->format('d M Y') }}</h6>
                                                    </div>
                                                </li>
                                                <li class="timeline-inverted">
                                                    <h6 class="text-muted">No other activities yet</h6>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Additional Documents -->
                            @if($distributor->documents && ($docs = json_decode($distributor->documents, true)) && count($docs) > 0)
                            <div class="row mt-5">
                                <div class="col-12">
                                    <h5 class="text-theme mb-4"><strong>Additional Documents ({{ count($docs) }})</strong></h5>
                                    <div class="row">
                                        @foreach($docs as $index => $doc)
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 text-center">
                                            <a href="{{ asset('storage/' . $doc) }}" target="_blank">
                                                <img src="{{ asset('assets/img/pdf-icon.png') }}" alt="Document" style="width: 80px;" class="mb-2">
                                                <br>
                                                <small class="text-primary">View Document {{ $index + 1 }}</small>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Timestamp Footer -->
                            <div class="text-center mt-5 text-muted border-top pt-4">
                                <small>
                                    Created: {{ showdatetimeformat($distributor->created_at) }} 
                                    | Last Updated: {{ showdatetimeformat($distributor->updated_at) }}
                                </small>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>