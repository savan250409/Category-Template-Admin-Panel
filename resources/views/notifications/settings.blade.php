@extends('partials.layout')
@section('title', 'Notification Settings')
@section('container')
    <style>
        .form-card {
            background:#fff; border-radius:.5rem;
            box-shadow:0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            padding:1.5rem; margin-bottom:2rem; max-width:560px;
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title text-primary"><i class="bi bi-gear-fill me-2"></i> Notification Settings</h1>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to list
            </a>
        </div>

        <div class="form-card">
            <h5><i class="bi bi-clock me-1 text-primary"></i> Default delay</h5>
            <p class="text-muted">
                When you send a notification <strong>without picking a date/time</strong>, the system waits
                this long before firing it. Useful as a small buffer to catch typos or last-minute changes.
            </p>

            <form method="POST" action="{{ route('notifications.settings.save') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Delay (minutes)</label>
                    <select name="default_delay_minutes" class="form-select">
                        @foreach ([0, 1, 2, 3, 5, 10, 15, 30, 60] as $m)
                            <option value="{{ $m }}" {{ (int) $setting->default_delay_minutes === $m ? 'selected' : '' }}>
                                {{ $m === 0 ? 'Send immediately' : $m . ' minute' . ($m === 1 ? '' : 's') . ' after' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Settings</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
            Swal.fire({ toast:true, position:'top-end', icon:'success',
                title:@json(session('success')), showConfirmButton:false, timer:4000, timerProgressBar:true });
            @endif
        });
    </script>
@endsection
