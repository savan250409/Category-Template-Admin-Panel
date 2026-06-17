{{-- Shared send-form fields. Expects: $defaultTitle, $defaultDesc, $submitLabel, $defaultDelayMin --}}
<div class="mb-3">
    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="title" name="title"
           value="{{ old('title', $defaultTitle) }}" maxlength="191" required
           placeholder="Enter notification title">
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" id="description" name="description" rows="3" maxlength="2990"
              placeholder="Enter notification body / description">{{ old('description', $defaultDesc) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Notification image <span class="text-muted">(optional)</span></label>
    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-primary" id="btn-paste-url">
            <i class="bi bi-link-45deg"></i> Paste URL
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-upload-device">
            <i class="bi bi-upload"></i> Upload from device
        </button>
    </div>

    <input type="url" class="form-control" id="notification_image_url" name="notification_image_url"
           value="{{ old('notification_image_url') }}" placeholder="https://example.com/banner.png"
           onchange="notifPreview(this.value)">

    <input type="file" class="d-none" id="notification_image_file" accept="image/*">

    <div class="hint mt-1">Public HTTPS URL of an image hosted anywhere (CDN, S3, your own server).</div>
    <div class="hint">If you upload a file, it is hosted on this server and its URL fills the field above.</div>

    <div id="notif-upload-status" class="hint text-primary mt-1" style="display:none;">
        <span class="spinner-border spinner-border-sm"></span> Uploading…
    </div>
    <div class="mt-2" id="notif-preview-wrap" style="display:none;">
        <img id="notif-preview" src="" alt="preview" width="120" class="img-thumbnail">
    </div>
</div>

<div class="mb-3">
    <label for="scheduled_at" class="form-label">Send at <span class="text-muted">(optional)</span></label>
    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="now">Now</button>
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="5">+5 min</button>
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="15">+15 min</button>
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="30">+30 min</button>
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="60">+1 hour</button>
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="180">+3 hours</button>
        <button type="button" class="btn btn-sm btn-outline-primary preset-btn" data-preset="1440">+1 day</button>
        <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-preset="clear">Clear</button>
    </div>
    <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
           value="{{ old('scheduled_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
    <div class="hint">
        Uses your device's local time. Leave empty to use the default delay from
        <a href="{{ route('notifications.settings') }}">Settings</a>
        (currently: <strong>{{ $defaultDelayMin }} minutes after</strong>).
    </div>
</div>

@php
    $exampleVals = ['category_id' => '17', 'type' => 'img / vid', 'image_url' => 'www.image.png'];
    $oldKeys = old('extra_keys');
    $oldVals = old('extra_vals');
    if (is_array($oldKeys) && count($oldKeys)) {
        $extraRows = [];
        foreach ($oldKeys as $i => $k) {
            $extraRows[] = ['key' => $k, 'val' => $oldVals[$i] ?? ''];
        }
    } else {
        $extraRows = [
            ['key' => 'category_id', 'val' => ''],
            ['key' => 'type',        'val' => ''],
            ['key' => 'image_url',   'val' => ''],
        ];
    }
@endphp
<div class="mb-3">
    <label class="form-label">Additional data fields <span class="text-muted">(optional)</span></label>
    <div class="hint mb-2">
        Custom <code>key : value</code> pairs sent inside the push's data payload — the app reads these on tap.
        Rows with an empty key <em>or</em> value are ignored.
    </div>
    <div id="extra-fields">
        @foreach ($extraRows as $row)
            <div class="row g-2 mb-2 extra-row">
                <div class="col-5">
                    <input type="text" name="extra_keys[]" class="form-control" placeholder="key"
                           value="{{ $row['key'] }}">
                </div>
                <div class="col-6">
                    <input type="text" name="extra_vals[]" class="form-control"
                           placeholder="{{ $exampleVals[$row['key']] ?? 'value' }}" value="{{ $row['val'] }}">
                </div>
                <div class="col-1 d-grid">
                    <button type="button" class="btn btn-outline-danger remove-extra" title="Remove">&times;</button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-extra">
        <i class="bi bi-plus-lg"></i> Add field
    </button>
</div>

<div class="d-grid d-md-block">
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-send-fill me-1"></i> {{ $submitLabel }}
    </button>
</div>
