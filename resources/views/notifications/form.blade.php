@extends('partials.layout')
@section('title', 'Send Push Notification')
@section('container')
    @include('notifications._send_styles')

    <style>
        /* Firebase project picker (same as global.blade) */
        .project-picker { border:1px solid #dee2e6; border-radius:.375rem; background:#fff; }
        .project-picker .picker-header {
            display:flex; align-items:center; justify-content:space-between;
            padding:.5rem .75rem; border-bottom:1px solid #dee2e6;
            background:#f8f9fa; border-radius:.375rem .375rem 0 0;
            cursor:pointer; user-select:none;
        }
        .project-picker .picker-header:hover { background:#e9ecef; }
        .project-picker .picker-body { padding:.5rem 0; max-height:220px; overflow-y:auto; }
        .project-picker .project-item {
            display:flex; align-items:center; gap:.5rem;
            padding:.4rem .75rem; cursor:pointer; transition:background .12s;
        }
        .project-picker .project-item:hover { background:#f0f4ff; }
        .project-picker .project-item input[type=checkbox] { cursor:pointer; }
        .picker-selected-count { font-size:.82rem; color:#6c757d; }
        .picker-toggle-icon { transition:transform .2s; }
        .picker-toggle-icon.open { transform:rotate(180deg); }
    </style>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="page-title"><i class="bi bi-bell-fill me-2 text-primary"></i> Send Push Notification</h1>
                <p class="page-subtitle mb-0">
                    <strong>{{ $moduleName }}</strong> &mdash; category <strong>{{ $categoryName }}</strong>.
                </p>
            </div>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to list
            </a>
        </div>

        <div class="info-box info-box-blue">
            On tap the app opens this category.
            <strong>Leave the time empty to send right now.</strong> Pick a future time to schedule it.
        </div>

        <div class="form-card">
            <form method="POST" action="{{ route('notifications.store', ['module' => $module, 'id' => $category->id]) }}">
                @csrf

                {{-- ── Firebase project picker ── --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-fire text-danger me-1"></i>
                        Send via Firebase Project(s)
                        <span class="text-danger">*</span>
                    </label>

                    @if ($projects->isEmpty())
                        <div class="alert alert-warning py-2 mb-0">
                            No active Firebase projects found.
                            <a href="{{ route('firebase-projects.index') }}">Add one</a> first.
                        </div>
                    @else
                        <div class="project-picker" id="notifFormPicker">
                            <div class="picker-header" id="nfpToggle">
                                <span>
                                    <i class="bi bi-layers me-1 text-primary"></i>
                                    <span id="nfpLabel">Select projects…</span>
                                </span>
                                <span class="d-flex align-items-center gap-2">
                                    <span class="picker-selected-count" id="nfpCount"></span>
                                    <i class="bi bi-chevron-down picker-toggle-icon" id="nfpChevron"></i>
                                </span>
                            </div>
                            <div class="picker-body" id="nfpBody" style="display:none;">
                                <div class="project-item border-bottom pb-1 mb-1">
                                    <input type="checkbox" class="form-check-input" id="nfpChkAll">
                                    <label for="nfpChkAll" class="mb-0 fw-semibold" style="cursor:pointer;">All projects</label>
                                </div>
                                @foreach ($projects as $proj)
                                    @php $isPreSelected = in_array($proj->id, $preSelectedIds, true) || ($proj->is_default && empty($preSelectedIds)); @endphp
                                    <div class="project-item">
                                        <input type="checkbox" class="form-check-input nfp-chk"
                                               id="nfp_{{ $proj->id }}" value="{{ $proj->id }}"
                                               @if($isPreSelected) checked @endif>
                                        <label for="nfp_{{ $proj->id }}" class="mb-0 flex-grow-1" style="cursor:pointer;">
                                            {{ $proj->display_name }}
                                            <small class="text-muted">({{ $proj->key }})</small>
                                            @if($proj->is_default)
                                                <span class="badge bg-success ms-1" style="font-size:.68rem;">default</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div id="nfpHiddenInputs"></div>
                        <div class="hint mt-1">Choose which Firebase app(s) receive this notification.</div>
                    @endif
                </div>
                {{-- ── end picker ── --}}

                @include('notifications._send_fields', [
                    'defaultTitle' => 'New ' . $moduleName . ' Category!',
                    'defaultDesc'  => $categoryName . ' is now available — tap to explore!',
                    'submitLabel'  => 'Send Notification',
                    'defaultDelayMin' => $defaultDelayMin,
                ])
            </form>
        </div>
    </div>

    @include('notifications._send_scripts')

    <script>
    (function () {
        const toggle    = document.getElementById('nfpToggle');
        const body      = document.getElementById('nfpBody');
        const chevron   = document.getElementById('nfpChevron');
        const label     = document.getElementById('nfpLabel');
        const countEl   = document.getElementById('nfpCount');
        const chkAll    = document.getElementById('nfpChkAll');
        const hiddenDiv = document.getElementById('nfpHiddenInputs');

        if (!toggle) return;

        toggle.addEventListener('click', function () {
            const open = body.style.display === 'none';
            body.style.display = open ? 'block' : 'none';
            chevron.classList.toggle('open', open);
        });

        document.addEventListener('click', function (e) {
            if (!document.getElementById('notifFormPicker').contains(e.target)) {
                body.style.display = 'none';
                chevron.classList.remove('open');
            }
        });

        const chkBoxes = document.querySelectorAll('.nfp-chk');

        function syncState() {
            const checked = [...chkBoxes].filter(c => c.checked);
            const total   = chkBoxes.length;

            chkAll.indeterminate = checked.length > 0 && checked.length < total;
            chkAll.checked = checked.length === total;

            if (checked.length === 0) {
                label.textContent = 'Select projects…';
                countEl.textContent = '';
            } else if (checked.length === total) {
                label.textContent = 'All projects selected';
                countEl.textContent = '(' + total + ')';
            } else {
                label.textContent = checked.map(c =>
                    c.closest('.project-item').querySelector('label').childNodes[0].textContent.trim()
                ).join(', ');
                countEl.textContent = '(' + checked.length + '/' + total + ')';
            }

            hiddenDiv.innerHTML = '';
            checked.forEach(function (c) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'firebase_project_ids[]';
                inp.value = c.value;
                hiddenDiv.appendChild(inp);
            });
        }

        chkAll.addEventListener('change', function () {
            chkBoxes.forEach(c => { c.checked = chkAll.checked; });
            syncState();
        });
        chkBoxes.forEach(c => c.addEventListener('change', syncState));

        syncState();
    })();
    </script>
@endsection
