@extends('partials.layout')
@section('title', isset($video) ? 'Edit Video' : 'Add Video')
@section('container')
    <style>
        .btn-custom-back {
            color: #4e73df;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        .btn-custom-back:hover {
            color: #2e59d9;
        }

        .form-card {
            background-color: #fff;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .video-preview {
            max-width: 150px;
            max-height: 100px;
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($video) ? 'pencil-square' : 'plus-circle' }} me-2 text-primary"></i>
                    {{ isset($video) ? 'Edit Video' : 'Add New Video' }}
                </h1>
                <p class="page-subtitle">{{ isset($video) ? 'Update video details' : 'Create a new AI Baby Video' }}
                </p>
            </div>
            <div>
                <a href="{{ route('ai-baby-video.videos.index') }}" class="btn-custom-back">
                    <i class="bi bi-arrow-left me-2"></i>Back to Videos
                </a>
            </div>
        </div>



        <div class="form-card">
            <form method="POST"
                action="{{ isset($video) ? route('ai-baby-video.videos.update', $video->id) : route('ai-baby-video.videos.store') }}"
                id="videoForm" enctype="multipart/form-data">
                @csrf
                @if (isset($video))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $video->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="video_path" class="form-label">Video File</label>
                        <input type="file" class="form-control" id="video_path" name="video_path" accept="video/*"
                            onchange="generateThumbnail(this)">
                        <small class="text-muted">Upload a video to automatically generate a thumbnail.</small>
                        <small id="thumbnailStatus" class="text-info d-none"><i class="bi bi-hourglass-split"></i>
                            Generating thumbnail...</small>
                        <input type="hidden" name="generated_thumbnail" id="generated_thumbnail">
                        <canvas id="thumbnailCanvas" style="display: none;"></canvas>

                        <div id="videoPreviewContainer"
                            class="mt-3 {{ isset($video) && $video->video_path ? '' : 'd-none' }}">
                            <div class="d-flex align-items-start gap-4 p-3 bg-light rounded border">
                                <div>
                                    <span class="d-block text-muted small mb-2 fw-bold">Video Preview</span>
                                    @php
                                        $videoAsset = '';
                                        if (isset($video) && $video->video_path) {
                                            if (Str::startsWith($video->video_path, 'upload/')) {
                                                $videoAsset = asset($video->video_path);
                                            } else {
                                                $videoAsset = asset('upload/AI Baby Video/' . $video->category->category_name . '/video/' . $video->video_path);
                                            }
                                        }
                                    @endphp
                                    <video id="previewVideo" src="{{ $videoAsset }}" controls
                                        class="video-preview rounded shadow-sm"></video>
                                </div>
                                <div>
                                    <span class="d-block text-muted small mb-2 fw-bold">Thumbnail Preview</span>
                                    @php
                                        $thumbAsset = '';
                                        if (isset($video) && $video->video_thumbnail) {
                                            if (Str::startsWith($video->video_thumbnail, 'upload/')) {
                                                $thumbAsset = asset($video->video_thumbnail);
                                            } else {
                                                $thumbAsset = asset('upload/AI Baby Video/' . $video->category->category_name . '/video thumbanail/' . $video->video_thumbnail);
                                            }
                                        }
                                    @endphp
                                    <img id="thumbPreview" src="{{ $thumbAsset }}" class="rounded shadow-sm"
                                        style="max-width: 150px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="ai_prompt" class="form-label">AI Prompt</label>
                    <textarea class="form-control" id="ai_prompt" name="ai_prompt" rows="3"
                        placeholder="Enter AI Prompt">{{ old('ai_prompt', $video->ai_prompt ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="video_title" class="form-label">Video Title</label>
                    <input type="text" class="form-control" id="video_title" name="video_title"
                        value="{{ old('video_title', $video->video_title ?? '') }}" placeholder="Enter Video Title"
                        required>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="name_change" name="name_change"
                            value="1" {{ old('name_change', $video->name_change ?? 0) ? 'checked' : '' }}>
                        <label class="form-check-label" for="name_change">Name Change</label>
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary py-2" id="submitBtn">
                        <i class="bi bi-{{ isset($video) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($video) ? 'Update Video' : 'Add Video' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 and Thumbnail Gen Logic -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                });
            @endif
            @if($errors->any())
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "Validation Error",
                    text: "{{ $errors->first() }}",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                });
            @endif
            });

        function generateThumbnail(input) {
            const submitBtn = document.getElementById('submitBtn');
            const statusText = document.getElementById('thumbnailStatus');

            if (input.files && input.files[0]) {
                // Disable submit button and show status
                submitBtn.disabled = true;
                if (statusText) statusText.classList.remove('d-none');

                var file = input.files[0];
                var fileURL = URL.createObjectURL(file);
                var video = document.createElement('video');
                var canvas = document.getElementById('thumbnailCanvas');
                var context = canvas.getContext('2d');
                var thumbPreview = document.getElementById('thumbPreview');

                video.src = fileURL;
                video.muted = true;
                video.preload = 'metadata';

                // Fallback timeout in case video fails to load
                const timeout = setTimeout(() => {
                    submitBtn.disabled = false;
                    if (statusText) statusText.classList.add('d-none');
                    console.error("Thumbnail generation timed out");
                }, 5000);

                video.onloadedmetadata = function () {
                    video.currentTime = 1;
                };

                video.onseeked = function () {
                    clearTimeout(timeout);
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    var dataURL = canvas.toDataURL('image/jpeg', 0.7);
                    document.getElementById('generated_thumbnail').value = dataURL;
                    thumbPreview.src = dataURL;

                    URL.revokeObjectURL(fileURL);

                    submitBtn.disabled = false;
                    if (statusText) statusText.classList.add('d-none');
                };

                video.onerror = function () {
                    clearTimeout(timeout);
                    submitBtn.disabled = false;
                    if (statusText) statusText.classList.add('d-none');
                    console.error("Error loading video for thumbnail");
                };

                // Show preview
                document.getElementById('previewVideo').src = fileURL;
                document.getElementById('videoPreviewContainer').classList.remove('d-none');
            }
        }
    </script>
@endsection