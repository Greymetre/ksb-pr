<x-app-layout>

    <style>

        .timeline {
            position: relative;
            margin: 30px 0;
            padding-left: 40px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 18px;
            width: 4px;
            height: 100%;
            background: #e0e0e0;
            border-radius: 10px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-icon {
            position: absolute;
            left: -2px;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .timeline-content {
            margin-left: 60px;
            border-radius: 12px;
        }

        .timeline-content .card-body {
            padding: 20px;
        }

    </style>

    <div class="container-fluid">

        <div class="row mb-4">
            <div class="col-md-12">

                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h3 class="mb-1">
                            Tour Activity Timeline
                        </h3>

                        <p class="text-muted mb-0">
                            Tour Date:
                            {{ \Carbon\Carbon::parse($tour->date)->format('d M Y') }}
                        </p>

                    </div>
                </div>

            </div>
        </div>

        <div class="timeline">

            @forelse($tour->logs as $log)

                @php

                    $badgeClass = match($log->action) {
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'pending' => 'bg-warning',
                        'updated' => 'bg-info',
                        'created' => 'bg-primary',
                        default => 'bg-secondary'
                    };

                    $icon = match($log->action) {
                        'approved' => 'check_circle',
                        'rejected' => 'cancel',
                        'pending' => 'pending',
                        'updated' => 'edit',
                        'created' => 'add_circle',
                        default => 'history'
                    };

                @endphp

                <div class="timeline-item">

                    <div class="timeline-icon {{ $badgeClass }}">
                        <i class="material-icons">{{ $icon }}</i>
                    </div>

                    <div class="timeline-content card shadow-sm border-0">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <h5 class="mb-1 text-capitalize">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </h5>

                                <!-- <span class="badge badge-secondary">
                                    {{ $log->status }}
                                </span> -->

                            </div>

                            <p class="mb-2 text-muted">
                                {{ $log->remark }}
                            </p>

                            <div class="small text-secondary">

                                <strong>
                                    {{ $log->user->name ?? 'N/A' }}
                                </strong>

                                •

                                {{ $log->created_at->format('d M Y h:i A') }}

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="alert alert-info">
                    No activity logs found.
                </div>

            @endforelse

        </div>

    </div>

</x-app-layout>