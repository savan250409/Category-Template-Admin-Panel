@extends('partials.layout')
@section('title', isset($category) ? 'Edit Top Slider Category' : 'Add Top Slider Category')
@section('container')
    <style>
        .form-label { font-size: 0.85rem; margin-bottom: 0.25rem; }
        .form-control-sm, .form-select-sm { font-size: 0.85rem; }
        .card-header h4 { font-size: 1.1rem; }
        .btn-sm { font-size: 0.85rem; }
        .text-danger.small { font-size: 0.75rem; }
    </style>
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-primary">
                                <i class="bi {{ isset($category) ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>
                                {{ isset($category) ? 'Edit Category' : 'Add New Category' }}
                            </h4>
                            <a href="{{ route('top-slider.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ isset($category) ? route('top-slider.categories.update', $category->id) : route('top-slider.categories.store') }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(isset($category))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label for="file_type" class="form-label fw-semibold">Top Slider Category Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('file_type') is-invalid @enderror" id="file_type" name="file_type" required>
                                    <option value="image" {{ (old('file_type', $category->file_type ?? '') == 'image') ? 'selected' : '' }}>Image Module</option>
                                    <option value="video" {{ (old('file_type', $category->file_type ?? '') == 'video') ? 'selected' : '' }}>Video Module</option>
                                </select>
                                @error('file_type')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="title" class="form-label fw-semibold">Title</label>
                                <input type="text" class="form-control form-control-sm @error('title') is-invalid @enderror" 
                                    id="title" name="title" value="{{ old('title', $category->title ?? '') }}" placeholder="Enter title...">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-semibold">Description</label>
                                <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="2" placeholder="Enter description...">{{ old('description', $category->description ?? '') }}</textarea>
                            </div>
                            
                            <hr class="my-4">


                            <div id="image_upload_section" class="mb-3 upload-section">
                                <label for="image" class="form-label fw-semibold">Top Slider Image <span class="text-danger small">(Only WebP allowed)</span></label>
                                <input class="form-control form-control-sm" type="file" id="image" name="image" accept=".webp" onchange="previewFile(this, 'slider_img_preview', 'image')">
                                <div class="text-danger small mt-1">Warning: Only .webp images are supported.</div>
                                <div id="slider_img_preview_container" class="mt-2 {{ isset($category) && $category->file_type == 'image' && $category->image ? '' : 'd-none' }}">
                                    <img id="slider_img_preview" src="{{ isset($category) && $category->file_type == 'image' && $category->image ? asset('upload/top_slider/categories/' . $category->category_name . '/' . $category->image) : '#' }}" alt="Slider Image Preview" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <small class="text-muted">Image will be shown if Top Slider is ON.</small>
                            </div>

                            <div id="video_upload_section" class="mb-3 upload-section d-none">
                                <div class="mb-2">
                                    <label for="video" class="form-label fw-semibold">Top Slider Video <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-sm" type="file" id="video" name="video" accept="video/mp4,video/x-m4v,video/*" onchange="previewFile(this, 'video_preview', 'video')">
                                    <div id="video_preview_container" class="mt-2 {{ isset($category) && $category->file_type == 'video' && $category->video ? '' : 'd-none' }}">
                                        <video id="video_preview" src="{{ isset($category) && $category->file_type == 'video' && $category->video ? asset('upload/top_slider/categories/' . $category->category_name . '/' . $category->video) : '#' }}" controls class="img-thumbnail" style="max-height: 100px;"></video>
                                    </div>
                                    <small class="text-muted">Video will be shown if Top Slider is ON.</small>
                                </div>
                                <div class="mb-2">
                                    <label for="video_thumbnail" class="form-label fw-semibold">Top Slider Video Thumbnail <span class="text-danger small">(Only WebP allowed)</span></label>
                                    <input class="form-control form-control-sm" type="file" id="video_thumbnail" name="video_thumbnail" accept=".webp" onchange="previewFile(this, 'vid_thumb_preview', 'image')">
                                    <div class="text-danger small mt-1">Warning: Only .webp images are supported.</div>
                                    <div id="vid_thumb_preview_container" class="mt-2 {{ isset($category) && $category->file_type == 'video' && $category->video_thumbnail ? '' : 'd-none' }}">
                                        <img id="vid_thumb_preview" src="{{ isset($category) && $category->file_type == 'video' && $category->video_thumbnail ? asset('upload/top_slider/categories/' . $category->category_name . '/' . $category->video_thumbnail) : '#' }}" alt="Video Thumbnail Preview" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 d-flex align-items-center gap-4 border p-2 rounded bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="top_slider_is_on" name="top_slider_is_on" value="1"
                                        {{ old('top_slider_is_on', $category->top_slider_is_on ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 small fw-semibold" for="top_slider_is_on">Top Slider is ON</label>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill shadow-sm">
                                    <i class="bi bi-save me-2"></i> {{ isset($category) ? 'Update Category' : 'Save Category' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const imageCategories = @json($imageCategories ?? []);
            const videoCategories = @json($videoCategories ?? []);
            const currentCategory = "{{ old('category_id', $category->category_id ?? '') }}";

            function updateCategoryOptions() {
                const fileType = $('#file_type').val();
                const $categorySelect = $('#category_id');
                $categorySelect.empty().append('<option value="">Select Category</option>');

                const categories = fileType === 'image' ? imageCategories : videoCategories;

                categories.forEach(cat => {
                    const id = cat.id;
                    const name = cat.category_name;
                    const selected = id == currentCategory ? 'selected' : '';
                    $categorySelect.append(`<option value="${id}" ${selected}>${name}</option>`);
                });
            }

            function toggleUploadSections() {
                var fileType = $('#file_type').val();
                if (fileType === 'image') {
                    $('#image_upload_section').removeClass('d-none');
                    $('#video_upload_section').addClass('d-none');
                } else if (fileType === 'video') {
                    $('#image_upload_section').addClass('d-none');
                    $('#video_upload_section').removeClass('d-none');
                }
                updateCategoryOptions();
            }

            $('#file_type').on('change', toggleUploadSections);
            toggleUploadSections(); // run on load
        });

        function previewFile(input, previewId, type) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            const container = document.getElementById(previewId + '_container');
            
            if (file) {
                const url = URL.createObjectURL(file);
                preview.src = url;
                container.classList.remove('d-none');
            }
        }
    </script>
@endsection
