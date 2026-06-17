@extends('partials.layout')
@section('title', 'Test a Device')
@section('container')
    <style>
        .form-card { background:#fff; border-radius:.5rem; box-shadow:0 .15rem 1.75rem 0 rgba(58,59,69,.15); padding:1.5rem; margin-bottom:2rem; }
        .info-box { background:#e7f5ff; border-left:4px solid #4dabf7; padding:12px 16px; border-radius:8px; margin-bottom:1.5rem; font-size:.9rem; }
        .hint { font-size:.8rem; color:#6c757d; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title text-primary"><i class="bi bi-phone me-2"></i> Test a Device (by FCM token)</h1>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to list
            </a>
        </div>

        <div class="info-box">
            This sends directly to one device's <strong>FCM token</strong> — bypassing topics — so you can prove delivery.
            Get the token from the app log (it prints <code>🔥 FCM Token: ...</code> on launch), paste it below and send.
            <ul class="mb-0 mt-2">
                <li><strong>It arrives</strong> → FCM + credentials are fine; the problem is the <em>topic subscription</em> in the app.</li>
                <li><strong>"SenderId mismatch"</strong> → the app's <code>google-services.json</code> is a <em>different</em> Firebase project than the one configured here.</li>
            </ul>
        </div>

        @if (session('lookup'))
            <div class="form-card" style="background:#0b1021;color:#d1e7dd;">
                <h6 class="text-white mb-2"><i class="bi bi-search me-1"></i> Token diagnosis</h6>
                <pre style="white-space:pre-wrap;color:#cfe2ff;margin:0;font-size:.85rem;">{{ session('lookup') }}</pre>
            </div>
        @endif

        <div class="form-card">
            <form method="POST" id="tokenForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Device FCM token <span class="text-danger">*</span></label>
                    <textarea name="token" rows="3" class="form-control" required
                              placeholder="Paste the long token from the app log…">{{ old('token') }}</textarea>
                </div>

                @if ($projects->count() > 1)
                    <div class="mb-3">
                        <label class="form-label">Send via Firebase project</label>
                        <select name="firebase_project_id" class="form-select">
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}" {{ $p->is_default ? 'selected' : '' }}>
                                    {{ $p->display_name ?: $p->key }} ({{ $p->project_id }}){{ $p->is_default ? ' — default' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="hint">Pick the project whose app this device runs.</div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" maxlength="191"
                           value="{{ old('title', 'Test notification') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control" maxlength="500">{{ old('description', 'If you can see this, FCM delivery works ✅') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4"
                        formaction="{{ route('notifications.test.send') }}">
                    <i class="bi bi-send-fill me-1"></i> Send test
                </button>
                <button type="submit" class="btn btn-outline-dark px-4"
                        formaction="{{ route('notifications.test.lookup') }}">
                    <i class="bi bi-search me-1"></i> Diagnose token
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
            Swal.fire({ icon:'success', title:'Sent', text:@json(session('success')) });
            @elseif (session('error'))
            Swal.fire({ icon:'error', title:'Not delivered', text:@json(session('error')) });
            @endif
        });
    </script>
@endsection
