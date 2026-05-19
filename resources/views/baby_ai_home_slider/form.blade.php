@extends('partials.layout')
@section('title', isset($slider) ? 'Edit Home Screen Slider' : 'Add Home Screen Slider')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .preview-img { max-width: 220px; max-height: 160px; object-fit: cover; border-radius: .35rem; }
        .preview-video { max-width: 260px; max-height: 160px; border-radius: .35rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary">
                    <i class="bi bi-{{ isset($slider) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($slider) ? 'Edit Home Screen Slider' : 'Add Home Screen Slider' }}
                </h1>
                <p class="text-muted">{{ isset($slider) ? 'Update slider details' : 'Create a new home screen slider entry' }}</p>
            </div>
            <a href="{{ route('baby-ai-home-slider.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Sliders
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($slider) ? route('baby-ai-home-slider.update', $slider->id) : route('baby-ai-home-slider.store') }}"
                enctype="multipart/form-data" id="sliderForm">
                @csrf
                @if (isset($slider))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="source_type" class="form-label fw-semibold">Source Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="source_type" name="source_type" required {{ isset($slider) ? 'disabled' : '' }}>
                        @php $current = old('source_type', $slider->source_type ?? ''); @endphp
                        @if (!isset($slider))
                            <option value="">Select Source Type</option>
                        @endif
                        <option value="image" {{ $current === 'image' ? 'selected' : '' }} {{ !isset($slider) && in_array('image', $used ?? []) ? 'disabled' : '' }}>
                            Image (Sub Category){{ !isset($slider) && in_array('image', $used ?? []) ? ' — already added' : '' }}
                        </option>
                        <option value="video" {{ $current === 'video' ? 'selected' : '' }} {{ !isset($slider) && in_array('video', $used ?? []) ? 'disabled' : '' }}>
                            Video Category{{ !isset($slider) && in_array('video', $used ?? []) ? ' — already added' : '' }}
                        </option>
                        <option value="dynamic_frame" {{ $current === 'dynamic_frame' ? 'selected' : '' }} {{ !isset($slider) && in_array('dynamic_frame', $used ?? []) ? 'disabled' : '' }}>
                            Dynamic Frame Category{{ !isset($slider) && in_array('dynamic_frame', $used ?? []) ? ' — already added' : '' }}
                        </option>
                    </select>
                    @if (isset($slider))
                        <input type="hidden" name="source_type" value="{{ $slider->source_type }}">
                        <small class="text-muted">Source type cannot be changed after creation.</small>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="source_id" class="form-label fw-semibold" id="source_id_label">Source Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="source_id" name="source_id" required>
                        <option value="">Select Category</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $slider->title ?? '') }}" placeholder="Enter title (optional)">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="Enter description (optional)">{{ old('description', $slider->description ?? '') }}</textarea>
                </div>

                <hr class="my-4">

                <div id="image_upload_section" class="mb-3 upload-section d-none">
                    <label for="image" class="form-label fw-semibold">Slider Image <span class="text-danger small">(Only .webp allowed)</span></label>
                    <input class="form-control" type="file" id="image" name="image" accept=".webp" onchange="previewImage(this)">
                    <div id="imagePreviewWrapper" class="mt-2 {{ isset($slider) && $slider->source_type !== 'video' && $slider->image ? '' : 'd-none' }}">
                        <img id="imagePreview" src="{{ isset($slider) && $slider->source_type !== 'video' && $slider->image ? asset('upload/baby_ai_home_slider/' . $slider->source_type . '/' . $slider->image) : '#' }}" class="preview-img" alt="Preview">
                    </div>
                </div>

                <div id="video_upload_section" class="mb-3 upload-section d-none">
                    <div class="mb-3">
                        <label for="video" class="form-label fw-semibold">Slider Video</label>
                        <input class="form-control" type="file" id="video" name="video" accept="video/mp4,video/quicktime" onchange="previewVideo(this)">
                        <div id="videoPreviewWrapper" class="mt-2 {{ isset($slider) && $slider->source_type === 'video' && $slider->video ? '' : 'd-none' }}">
                            <video id="videoPreview" src="{{ isset($slider) && $slider->source_type === 'video' && $slider->video ? asset('upload/baby_ai_home_slider/video/' . $slider->video) : '#' }}" controls class="preview-video"></video>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="video_thumbnail" class="form-label fw-semibold">Slider Video Thumbnail <span class="text-danger small">(Only .webp allowed)</span></label>
                        <input class="form-control" type="file" id="video_thumbnail" name="video_thumbnail" accept=".webp" onchange="previewVideoThumb(this)">
                        <div id="videoThumbPreviewWrapper" class="mt-2 {{ isset($slider) && $slider->source_type === 'video' && $slider->video_thumbnail ? '' : 'd-none' }}">
                            <img id="videoThumbPreview" src="{{ isset($slider) && $slider->source_type === 'video' && $slider->video_thumbnail ? asset('upload/baby_ai_home_slider/video/' . $slider->video_thumbnail) : '#' }}" class="preview-img" alt="Preview">
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-flex align-items-center gap-3 border p-2 rounded bg-light">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_on" name="is_on" value="1" {{ old('is_on', $slider->is_on ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label ms-2 small fw-semibold" for="is_on">Slider is ON</label>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($slider) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($slider) ? 'Update Slider' : 'Save Slider' }}
                    </button>
                    <a href="{{ route('baby-ai-home-slider.index') }}" data-back-to-list class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const imageSubcategories     = @json($imageSubcategories ?? []);
        const videoCategories        = @json($videoCategories ?? []);
        const dynamicFrameCategories = @json($dynamicFrameCategories ?? []);
        const currentSourceId        = "{{ old('source_id', $slider->source_id ?? '') }}";

        function refreshSourceOptions() {
            const type   = document.getElementById('source_type').value;
            const $sel   = $('#source_id');
            const $label = $('#source_id_label');

            if (type === 'image') {
                $label.html('Sub Category <span class="text-danger">*</span>');
                $sel.empty().append('<option value="">Select Sub Category</option>');
                imageSubcategories.forEach(s => {
                    const sel = (s.id == currentSourceId) ? 'selected' : '';
                    $sel.append(`<option value="${s.id}" ${sel}>${s.title}</option>`);
                });
                return;
            }

            $label.html('Source Category <span class="text-danger">*</span>');
            $sel.empty().append('<option value="">Select Category</option>');

            let list = [];
            if (type === 'video') list = videoCategories.map(s => ({ id: s.id, label: s.category_name }));
            else if (type === 'dynamic_frame') list = dynamicFrameCategories.map(s => ({ id: s.id, label: s.category_name }));

            list.forEach(item => {
                const sel = (item.id == currentSourceId) ? 'selected' : '';
                $sel.append(`<option value="${item.id}" ${sel}>${item.label}</option>`);
            });
        }

        function toggleUploadSections() {
            const type = document.getElementById('source_type').value;
            $('#image_upload_section').addClass('d-none');
            $('#video_upload_section').addClass('d-none');
            if (type === 'image' || type === 'dynamic_frame') {
                $('#image_upload_section').removeClass('d-none');
            } else if (type === 'video') {
                $('#video_upload_section').removeClass('d-none');
            }
            refreshSourceOptions();
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            $('#source_type').on('change', toggleUploadSections);
            toggleUploadSections();
        });

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.name.toLowerCase().endsWith('.webp')) {
                    Swal.fire({ icon: 'warning', title: 'Invalid File Format', text: 'Only .webp images are accepted.' });
                    input.value = '';
                    document.getElementById('imagePreviewWrapper').classList.add('d-none');
                    return;
                }
                const r = new FileReader();
                r.onload = e => { document.getElementById('imagePreview').src = e.target.result; document.getElementById('imagePreviewWrapper').classList.remove('d-none'); };
                r.readAsDataURL(file);
            }
        }

        function previewVideo(input) {
            if (input.files && input.files[0]) {
                document.getElementById('videoPreview').src = URL.createObjectURL(input.files[0]);
                document.getElementById('videoPreviewWrapper').classList.remove('d-none');
            }
        }

        function previewVideoThumb(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.name.toLowerCase().endsWith('.webp')) {
                    Swal.fire({ icon: 'warning', title: 'Invalid File Format', text: 'Only .webp images are accepted.' });
                    input.value = '';
                    document.getElementById('videoThumbPreviewWrapper').classList.add('d-none');
                    return;
                }
                const r = new FileReader();
                r.onload = e => { document.getElementById('videoThumbPreview').src = e.target.result; document.getElementById('videoThumbPreviewWrapper').classList.remove('d-none'); };
                r.readAsDataURL(file);
            }
        }
    </script>
@endsection
