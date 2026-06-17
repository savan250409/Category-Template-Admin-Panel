{{-- "Send push notification after save" toggle + Firebase project picker.
     Include inside a category create <form>, just before the submit button.
     Only shown when creating (not editing).

     Optional variable:
       $inlineNotification (bool, default false)
         true  → notification fields expand inline; category save + schedule in one submit
         false → after save, redirects to the stand-alone notification form
--}}
@if (!isset($category))
    @php
        $afstProjects        = \App\Models\FirebaseProject::where('is_active', 1)
                                   ->orderByDesc('is_default')->orderBy('display_name')->get();
        $inlineNotification  = $inlineNotification ?? false;
        $notifyChecked       = old('notify_after_save', 1) ? true : false;
    @endphp

    {{-- ── Toggle switch ── --}}
    <div class="mb-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="notify_after_save"
                   name="notify_after_save" value="1" {{ $notifyChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="notify_after_save">
                Send push notification after save
            </label>
        </div>
        <small class="text-muted">
            @if ($inlineNotification)
                When ON, fill in the details below — notification is scheduled on save.
            @else
                When ON, you'll be taken to the notification form after saving.
            @endif
        </small>
    </div>

    {{-- ── Collapsible wrapper (Firebase picker + optional inline fields) ── --}}
    <div id="afst-project-wrap" class="mb-3" style="{{ $notifyChecked ? '' : 'display:none;' }}">

        {{-- Firebase project multi-select --}}
        <label class="form-label fw-semibold mt-1">
            <i class="bi bi-fire text-danger me-1"></i>
            Send via Firebase Project(s)
        </label>

        @if ($afstProjects->isEmpty())
            <div class="alert alert-warning py-2 mb-2">
                No active Firebase projects found.
                <a href="{{ route('firebase-projects.index') }}">Add one</a> first.
            </div>
        @else
            <div class="project-picker border rounded" id="afst-picker">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-light rounded-top border-bottom"
                     id="afst-picker-toggle" style="cursor:pointer; user-select:none;">
                    <span>
                        <i class="bi bi-layers me-1 text-primary"></i>
                        <span id="afst-picker-label">Select projects…</span>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span class="text-muted small" id="afst-picker-count"></span>
                        <i class="bi bi-chevron-down" id="afst-picker-chevron" style="transition:transform .2s;"></i>
                    </span>
                </div>
                <div id="afst-picker-body" style="display:none; max-height:220px; overflow-y:auto;">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" id="afst-all-row">
                        <input type="checkbox" class="form-check-input" id="afst-chk-all">
                        <label for="afst-chk-all" class="mb-0 fw-semibold" style="cursor:pointer;">All projects</label>
                    </div>
                    @foreach ($afstProjects as $proj)
                        <div class="d-flex align-items-center gap-2 px-3 py-2 afst-item"
                             style="cursor:pointer;"
                             onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background=''">
                            <input type="checkbox" class="form-check-input afst-proj-chk"
                                   id="afst_proj_{{ $proj->id }}" value="{{ $proj->id }}"
                                   @if($proj->is_default) checked @endif>
                            <label for="afst_proj_{{ $proj->id }}" class="mb-0 flex-grow-1" style="cursor:pointer;">
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
            <div id="afst-hidden-inputs"></div>
            <small class="text-muted">Choose which Firebase app(s) receive this notification.</small>
        @endif

        {{-- ── Inline notification fields (AI NGD modules only) ── --}}
        @if ($inlineNotification)
            <input type="hidden" name="notify_inline" value="1">

            <div class="mt-3 pt-3 border-top">
                <h6 class="fw-semibold text-primary mb-3">
                    <i class="bi bi-bell-fill me-2"></i>Notification Details
                </h6>

                {{-- Title --}}
                <div class="mb-3">
                    <label for="notif_title" class="form-label">
                        Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control @error('notif_title') is-invalid @enderror"
                           id="notif_title" name="notif_title" maxlength="191"
                           value="{{ old('notif_title', 'New Category Available!') }}"
                           placeholder="Notification title">
                    @error('notif_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="notif_description" class="form-label">
                        Description <span class="text-muted small">(optional)</span>
                    </label>
                    <textarea class="form-control" id="notif_description" name="notif_description"
                              rows="2" maxlength="2990"
                              placeholder="Tap to explore the new category!">{{ old('notif_description') }}</textarea>
                </div>

                {{-- Image URL --}}
                <div class="mb-3">
                    <label for="notif_image_url" class="form-label">
                        Image URL <span class="text-muted small">(optional)</span>
                    </label>
                    <input type="url" class="form-control @error('notif_image_url') is-invalid @enderror"
                           id="notif_image_url" name="notif_image_url"
                           value="{{ old('notif_image_url') }}"
                           placeholder="https://example.com/banner.png">
                    @error('notif_image_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Public HTTPS URL of a notification banner image.</small>
                </div>

                {{-- Scheduled at --}}
                <div class="mb-3">
                    <label for="notif_scheduled_at" class="form-label">
                        Send at <span class="text-muted small">(optional — leave empty to send now)</span>
                    </label>
                    <div class="mb-2 d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary notif-preset-btn" data-preset="now">Now</button>
                        <button type="button" class="btn btn-sm btn-outline-primary notif-preset-btn" data-preset="5">+5 min</button>
                        <button type="button" class="btn btn-sm btn-outline-primary notif-preset-btn" data-preset="15">+15 min</button>
                        <button type="button" class="btn btn-sm btn-outline-primary notif-preset-btn" data-preset="30">+30 min</button>
                        <button type="button" class="btn btn-sm btn-outline-primary notif-preset-btn" data-preset="60">+1 hour</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary notif-preset-btn" data-preset="clear">Clear</button>
                    </div>
                    <input type="datetime-local" class="form-control" id="notif_scheduled_at" name="notif_scheduled_at"
                           value="{{ old('notif_scheduled_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                    <small class="text-muted">Leave empty to use the default delay from
                        <a href="{{ route('notifications.settings') }}" target="_blank">Settings</a>.
                    </small>
                </div>
            </div>
        @endif

    </div>{{-- end #afst-project-wrap --}}

    <script>
    (function () {
        // Toggle visibility with the notify switch
        const notifySwitch  = document.getElementById('notify_after_save');
        const projectWrap   = document.getElementById('afst-project-wrap');
        if (notifySwitch && projectWrap) {
            notifySwitch.addEventListener('change', function () {
                projectWrap.style.display = this.checked ? '' : 'none';
            });
        }

        // Picker open / close
        const pickerToggle  = document.getElementById('afst-picker-toggle');
        const pickerBody    = document.getElementById('afst-picker-body');
        const pickerChevron = document.getElementById('afst-picker-chevron');
        const pickerLabel   = document.getElementById('afst-picker-label');
        const pickerCount   = document.getElementById('afst-picker-count');
        const chkAll        = document.getElementById('afst-chk-all');
        const hiddenDiv     = document.getElementById('afst-hidden-inputs');

        if (!pickerToggle) return;

        pickerToggle.addEventListener('click', function () {
            const open = pickerBody.style.display === 'none';
            pickerBody.style.display = open ? 'block' : 'none';
            pickerChevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
        });

        document.addEventListener('click', function (e) {
            const picker = document.getElementById('afst-picker');
            if (picker && !picker.contains(e.target)) {
                pickerBody.style.display = 'none';
                pickerChevron.style.transform = 'rotate(0deg)';
            }
        });

        const chkBoxes = document.querySelectorAll('.afst-proj-chk');

        function syncState() {
            const checked = [...chkBoxes].filter(c => c.checked);
            const total   = chkBoxes.length;

            chkAll.indeterminate = checked.length > 0 && checked.length < total;
            chkAll.checked = checked.length === total;

            if (checked.length === 0) {
                pickerLabel.textContent = 'Select projects…';
                pickerCount.textContent = '';
            } else if (checked.length === total) {
                pickerLabel.textContent = 'All projects selected';
                pickerCount.textContent = '(' + total + ')';
            } else {
                pickerLabel.textContent = checked.map(c =>
                    c.closest('.afst-item').querySelector('label').childNodes[0].textContent.trim()
                ).join(', ');
                pickerCount.textContent = '(' + checked.length + '/' + total + ')';
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

        // Preset buttons for scheduled_at (inline mode only)
        document.querySelectorAll('.notif-preset-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const field = document.getElementById('notif_scheduled_at');
                if (!field) return;
                const preset = this.dataset.preset;
                if (preset === 'clear') { field.value = ''; return; }
                const now = new Date();
                if (preset !== 'now') now.setMinutes(now.getMinutes() + parseInt(preset));
                now.setSeconds(0, 0);
                const pad = n => String(n).padStart(2, '0');
                field.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            });
        });
    })();
    </script>
@endif
