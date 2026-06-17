@extends('partials.layout')
@section('title', isset($project) ? 'Edit Firebase Project' : 'Add Firebase Project')
@section('container')
    <style>
        .form-card {
            background:#fff; border-radius:.5rem;
            box-shadow:0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            padding:1.5rem; margin-bottom:2rem;
        }
        .hint { font-size:.8rem; color:#6c757d; }
        .section-title { font-weight:600; margin:1.25rem 0 .5rem; }
        .info-auto { background:#e7f5ff; border-left:4px solid #4dabf7; padding:10px 14px; border-radius:8px; font-size:.88rem; margin-top:1rem; }
        .detected-box { margin-top:1rem; padding:12px 14px; background:#f8f9fc; border:1px solid #e3e6f0; border-radius:8px; }
        .detected-box > div { display:flex; gap:.75rem; align-items:center; padding:2px 0; }
        .detected-box span { width:110px; color:#6c757d; font-size:.85rem; }
        .current-file { font-size:.83rem; color:#495057; background:#f1f3f9; border:1px solid #e3e6f0; border-radius:6px; padding:5px 10px; margin-bottom:4px; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title text-primary">
                <i class="bi bi-fire me-2"></i> {{ isset($project) ? 'Edit Firebase Project' : 'Add Firebase Project' }}
            </h1>
            <a href="{{ route('firebase-projects.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to list
            </a>
        </div>

        <p class="text-muted">Upload the JSON files and everything is filled in for you. No .env editing needed.</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="form-card">
            <form method="POST"
                  action="{{ isset($project) ? route('firebase-projects.update', $project->id) : route('firebase-projects.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @isset($project) @method('PUT') @endisset

                <div class="section-title"><i class="bi bi-filetype-json me-1 text-primary"></i> JSON Files</div>

                <div class="mb-3">
                    <label class="form-label">
                        Service Account JSON
                        @if (!isset($project)) <span class="text-danger">*</span> @endif
                    </label>
                    @isset($project)
                        @if ($project->service_account_filename)
                            <div class="current-file"><i class="bi bi-filetype-json"></i> Current file: <strong>{{ $project->service_account_filename }}</strong></div>
                        @elseif ($project->isUsable())
                            <div class="current-file"><i class="bi bi-check-circle text-success"></i> A service account is configured (stored as <code>storage/app/firebase/{{ $project->key }}.json</code>).</div>
                        @endif
                        @if ($project->clientEmail())
                            <div class="current-file"><i class="bi bi-envelope"></i> Account: <code>{{ $project->clientEmail() }}</code></div>
                        @endif
                    @endisset
                    <input type="file" name="service_account" class="form-control mt-1" accept=".json,application/json">
                    <div class="hint">Firebase Console → Project Settings → Service Accounts → Generate Private Key.
                        @isset($project)
                            <span class="text-success">Leave empty to keep the current one.</span>
                        @endisset
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">google-services.json / GoogleService-Info.plist <span class="text-muted">(Android or iOS — provides the topic / package id)</span></label>
                    @isset($project)
                        @if ($project->google_services_filename)
                            <div class="current-file"><i class="bi bi-filetype-json"></i> Current file: <strong>{{ $project->google_services_filename }}</strong></div>
                        @elseif ($project->topic)
                            <div class="current-file"><i class="bi bi-check-circle text-success"></i> Topic already set from a previous upload: <code>{{ $project->topic }}</code></div>
                        @endif
                    @endisset
                    <input type="file" name="google_services" class="form-control mt-1" accept=".json,.plist,application/json,application/x-plist,text/xml">
                    <div class="hint">
                        Android: Project Settings → Your apps → Android → <code>google-services.json</code>.
                        iOS: Project Settings → Your apps → iOS → <code>GoogleService-Info.plist</code>.
                        @isset($project) <span class="text-success">Leave empty to keep the current values.</span>@endisset
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sender ID</label>
                    <input type="text" name="sender_id" class="form-control"
                           value="{{ old('sender_id', $project->sender_id ?? '') }}" placeholder="204135163089">
                    <div class="hint">Optional — auto-filled from google-services.json if you upload it.</div>
                </div>

                <div class="info-auto">
                    <i class="bi bi-magic me-1"></i>
                    Everything else — <strong>project key</strong>, <strong>display name</strong>,
                    <strong>project ID</strong> and <strong>topic</strong> — is detected from the JSON
                    automatically and written to <code>.env</code> for you. No config to edit.
                </div>

                @isset($project)
                    <div class="detected-box">
                        <div><span>Detected key</span><code>{{ $project->key }}</code></div>
                        <div><span>Project ID</span><code>{{ $project->project_id ?: '—' }}</code></div>
                        <div><span>Topic</span><code>{{ $project->topic ?: '—' }}</code></div>
                        @if ($project->is_default)<div><span class="badge bg-success">DEFAULT</span></div>@endif
                    </div>
                @endisset

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> {{ isset($project) ? 'Update Project' : 'Add Project' }}
                    </button>
                    <a href="{{ route('firebase-projects.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
