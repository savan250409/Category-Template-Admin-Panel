@extends('partials.layout')
@section('title', isset($item) ? 'Edit Lips Sync Item' : 'Add New Lips Sync Item')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .preview-img { max-width: 200px; max-height: 160px; object-fit: cover; border-radius: .35rem; }
        .preview-video { max-width: 240px; max-height: 160px; border-radius: .35rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary">
                    <i class="bi bi-{{ isset($item) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($item) ? 'Edit Lips Sync Item' : 'Add New Lips Sync Item' }}
                </h1>
                <p class="text-muted">{{ isset($item) ? 'Update Lips Sync item' : 'Create a new Lips Sync item' }}</p>
            </div>
            <a href="{{ route('lips-sync.items.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($item) ? route('lips-sync.items.update', $item->id) : route('lips-sync.items.store') }}"
                enctype="multipart/form-data" id="lipsSyncForm">
                @csrf
                @if (isset($item))
                    @method('PUT')
                @endif

                <input type="hidden" name="video_thumbnail" id="video_thumbnail">

                <div class="mb-3">
                    <label for="lips_sync_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="lips_sync_category_id" name="lips_sync_category_id" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('lips_sync_category_id', $item->lips_sync_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title"
                        value="{{ old('title', $item->title ?? '') }}" placeholder="Enter title" required>
                </div>

                <div class="mb-3">
                    <label for="song" class="form-label">Song (MP3/WAV){{ isset($item) ? '' : ' *' }}</label>
                    <input type="file" class="form-control" id="song" name="song" accept=".mp3,.wav,audio/mpeg,audio/wav" {{ isset($item) ? '' : 'required' }}>
                    <small class="text-muted">Max 10 seconds duration.</small>
                    @if (isset($item) && $item->song && $item->category)
                        <div class="mt-2">
                            <audio src="{{ asset('upload/lips_sync/' . $item->category->category_name . '/song/' . $item->song) }}" controls preload="none" style="width: 320px;"></audio>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="video" class="form-label">Video (MP4/MOV){{ isset($item) ? '' : ' *' }}</label>
                    <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/quicktime" {{ isset($item) ? '' : 'required' }}>
                    @if (isset($item) && $item->video && $item->category)
                        <div class="mt-2">
                            <video id="existingVideo" src="{{ asset('upload/lips_sync/' . $item->category->category_name . '/video/' . $item->video) }}" controls preload="metadata" class="preview-video"></video>
                        </div>
                    @endif
                    <div id="newVideoPreviewWrapper" class="mt-2 d-none">
                        <video id="newVideoPreview" controls preload="metadata" class="preview-video"></video>
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary py-2" id="submitBtn">
                        <i class="bi bi-{{ isset($item) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($item) ? 'Update Item' : 'Add Item' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            const SONG_MAX_SECONDS = 10;
            const songInput = document.getElementById('song');
            const videoInput = document.getElementById('video');
            const thumbField = document.getElementById('video_thumbnail');
            const newVideoPreview = document.getElementById('newVideoPreview');
            const newVideoPreviewWrapper = document.getElementById('newVideoPreviewWrapper');
            const form = document.getElementById('lipsSyncForm');

            let songDuration = null;
            let songValid = true;

            songInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) { songDuration = null; songValid = true; return; }
                const url = URL.createObjectURL(file);
                const audio = new Audio();
                audio.preload = 'metadata';
                audio.src = url;
                audio.onloadedmetadata = function () {
                    songDuration = audio.duration;
                    URL.revokeObjectURL(url);
                    if (songDuration > SONG_MAX_SECONDS + 0.05) {
                        songValid = false;
                        songInput.value = '';
                        Swal.fire({
                            icon: 'error',
                            title: 'Song too long',
                            text: 'Song duration must be 10 seconds or less. Selected: ' + songDuration.toFixed(2) + 's.'
                        });
                    } else {
                        songValid = true;
                    }
                };
            });

            videoInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) {
                    newVideoPreviewWrapper.classList.add('d-none');
                    thumbField.value = '';
                    return;
                }
                const url = URL.createObjectURL(file);
                newVideoPreview.src = url;
                newVideoPreviewWrapper.classList.remove('d-none');

                // Generate thumbnail from first frame
                const tmp = document.createElement('video');
                tmp.preload = 'metadata';
                tmp.src = url;
                tmp.muted = true;
                tmp.playsInline = true;
                tmp.onloadedmetadata = function () {
                    tmp.currentTime = Math.min(0.5, (tmp.duration || 1) / 4);
                };
                tmp.onseeked = function () {
                    const canvas = document.createElement('canvas');
                    canvas.width = tmp.videoWidth || 320;
                    canvas.height = tmp.videoHeight || 240;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(tmp, 0, 0, canvas.width, canvas.height);
                    thumbField.value = canvas.toDataURL('image/jpeg', 0.7);
                };
            });

            form.addEventListener('submit', function (e) {
                if (songInput.files && songInput.files[0] && !songValid) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Song too long', text: 'Song duration must be 10 seconds or less.' });
                    return;
                }
                if (songInput.files && songInput.files[0] && songDuration === null) {
                    // metadata not loaded yet — block briefly
                    e.preventDefault();
                    Swal.fire({ icon: 'info', title: 'Please wait', text: 'Validating song duration… click Add again in a second.' });
                }
            });
        });
    </script>
@endsection
