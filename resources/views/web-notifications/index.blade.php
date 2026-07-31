<x-app-layout>
    <style>
        .web-notification-page {
            max-width: 1050px;
            margin: 0 auto;
        }
        .web-notification-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .web-notification-heading h3 {
            margin: 0;
            color: #fff;
            font-family: 'Sora', 'Inter', sans-serif;
        }
        .web-notification-card {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            gap: 20px;
            margin-bottom: 16px;
            padding: 18px;
            border: 1px solid rgba(90, 130, 220, .2);
            border-radius: 16px;
            background: rgba(8, 20, 50, .72);
        }
        .web-notification-card.unread {
            border-color: rgba(34, 211, 238, .52);
            box-shadow: inset 3px 0 0 #22d3ee;
        }
        .web-notification-media {
            width: 180px;
            height: 125px;
            overflow: hidden;
            border-radius: 12px;
            background: rgba(90, 130, 220, .14);
        }
        .web-notification-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .web-notification-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #22d3ee;
        }
        .web-notification-placeholder .material-symbols-outlined {
            font-size: 42px;
        }
        .web-notification-body {
            min-width: 0;
        }
        .web-notification-title {
            margin: 0 0 8px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .web-notification-message {
            margin: 0;
            color: #a9bce6;
            font-size: 14px;
            line-height: 1.65;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
        .web-notification-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            color: #7185b3;
            font-size: 11px;
        }
        .web-notification-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }
        .web-notification-actions .btn {
            margin: 0;
        }
        .web-notification-empty-page {
            padding: 70px 20px;
            border: 1px solid rgba(90, 130, 220, .2);
            border-radius: 16px;
            color: #8798bf;
            background: rgba(8, 20, 50, .72);
            text-align: center;
        }
        @media (max-width: 700px) {
            .web-notification-card {
                grid-template-columns: 1fr;
            }
            .web-notification-media {
                width: 100%;
                height: 210px;
            }
            .web-notification-actions {
                width: 100%;
                margin: 8px 0 0;
            }
        }
    </style>

    <div class="web-notification-page">
        <div class="web-notification-heading">
            <h3>Notifications</h3>
            <a href="{{ url('dashboard') }}" class="btn btn-outline-info btn-sm">
                <i class="material-icons">arrow_back</i> Dashboard
            </a>
        </div>

        @forelse($notifications as $notification)
            <article class="web-notification-card {{ $notification->read ? '' : 'unread' }}">
                @if($notification->image)
                    <a class="web-notification-media" href="{{ $notification->image }}" target="_blank" rel="noopener">
                        <img src="{{ $notification->image }}" alt="{{ $notification->type }}">
                    </a>
                @else
                    <div class="web-notification-media web-notification-placeholder">
                        <span class="material-symbols-outlined">notifications</span>
                    </div>
                @endif

                <div class="web-notification-body">
                    <h4 class="web-notification-title">{{ $notification->type ?: 'FieldKonnect' }}</h4>
                    <p class="web-notification-message">{{ $notification->data }}</p>
                    <div class="web-notification-meta">
                        <span>{{ $notification->created_at?->format('d M Y, h:i A') }}</span>
                        <span>{{ $notification->read ? 'Read' : 'Unread' }}</span>
                        <div class="web-notification-actions">
                            @if($notification->image)
                                <a class="btn btn-outline-info btn-sm"
                                    href="{{ route('web-notifications.download', $notification) }}">
                                    <i class="material-icons">download</i> Download media
                                </a>
                            @endif
                            <a class="btn btn-info btn-sm"
                                href="{{ route('web-notifications.open', $notification) }}">
                                Open
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="web-notification-empty-page">No notifications yet.</div>
        @endforelse

        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
