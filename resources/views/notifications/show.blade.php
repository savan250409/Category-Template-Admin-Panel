@extends('partials.layout')
@section('title', 'Notification #' . $notification->id)
@section('container')
    <style>
        .form-card {
            background:#fff; border-radius:.5rem;
            box-shadow:0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            padding:1.5rem; margin-bottom:2rem;
        }
        .status-badge { font-size:.75rem; padding:.35em .7em; border-radius:.4rem; font-weight:600; }
        .status-sent    { background:#1cc88a; color:#fff; }
        .status-failed  { background:#e74a3b; color:#fff; }
        .status-pending { background:#f6c23e; color:#fff; }
        .kv th { width:200px; color:#6c757d; font-weight:600; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title text-primary"><i class="bi bi-bell-fill me-2"></i> Notification #{{ $notification->id }}</h1>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to list
            </a>
        </div>

        <div class="form-card">
            <table class="table kv">
                <tr><th>Title</th><td>{{ $notification->title }}</td></tr>
                <tr><th>Description</th><td>{{ $notification->description }}</td></tr>
                <tr><th>Module</th><td>{{ $notification->module }}</td></tr>
                <tr><th>Status</th><td><span class="status-badge status-{{ $notification->status }}">{{ ucfirst($notification->status) }}</span></td></tr>
                <tr><th>Scheduled for</th><td>{{ $notification->scheduled_at ? $notification->scheduled_at->format('d M Y, h:i A') : '—' }}</td></tr>
                <tr><th>Sent at</th><td>{{ $notification->sent_at ? $notification->sent_at->format('d M Y, h:i A') : '—' }}</td></tr>
                <tr><th>Delivered to apps</th><td>{{ $notification->sent_count }} ok / {{ $notification->failed_count }} failed</td></tr>
                @if ($notification->image_url)
                    <tr><th>Image</th><td><img src="{{ $notification->image_url }}" width="140" class="img-thumbnail"></td></tr>
                @endif
                @if ($notification->last_error)
                    <tr><th>Last error</th><td class="text-danger">{{ $notification->last_error }}</td></tr>
                @endif
            </table>
        </div>

        <div class="form-card">
            <h5 class="mb-3">Delivery log (per app)</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>App</th><th>Target</th><th>Result</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ optional($log->firebaseProject)->display_name ?? optional($log->firebaseProject)->key ?? '—' }}</td>
                                <td><code>{{ $log->target_type }}:{{ \Illuminate\Support\Str::limit($log->target, 30) }}</code></td>
                                <td>
                                    @if ($log->success)
                                        <span class="status-badge status-sent">OK</span>
                                    @else
                                        <span class="status-badge status-failed">Failed</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at->format('d M Y, h:i:s A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No delivery attempts logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
