@extends('partials.layout')
@section('title', isset($item) ? 'Edit Top Slider Item' : 'Add Top Slider Item')
@section('container')
    <style>
        .form-label { font-size: 0.85rem; margin-bottom: 0.25rem; }
        .form-control-sm, .form-select-sm { font-size: 0.85rem; }
        .card-header h4 { font-size: 1.1rem; }
        .btn-sm { font-size: 0.85rem; }
        .text-danger.small { font-size: 0.75rem; }
        .alert-info { padding: 0.5rem 1rem; font-size: 0.85rem; }
    </style>
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-primary">
                                <i class="bi {{ isset($item) ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>
                                {{ isset($item) ? 'Edit Item' : 'Add New Item' }}
                            </h4>
                            <a href="{{ route('top-slider.items.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ isset($item) ? route('top-slider.items.update', $item->id) : route('top-slider.items.store') }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(isset($item))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label for="top_slider_category_id" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('top_slider_category_id') is-invalid @enderror" 
                                    id="top_slider_category_id" name="top_slider_category_id" required>
                                    <option value="" disabled {{ !isset($item) ? 'selected' : '' }}>Select a Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                            data-type="{{ $category->file_type }}"
                                            {{ (old('top_slider_category_id', $item->top_slider_category_id ?? '') == $category->id) ? 'selected' : '' }}>
                                            {{ $category->category_name }} ({{ strtoupper($category->file_type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('top_slider_category_id')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="prompt" class="form-label fw-semibold">Prompt / Title</label>
                                <textarea class="form-control form-control-sm @error('prompt') is-invalid @enderror" 
                                    id="prompt" name="prompt" rows="2" placeholder="Enter prompt...">{{ old('prompt', $item->prompt ?? '') }}</textarea>
                                @error('prompt')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <hr class="my-4">
                            
                            <div id="dynamic_upload_hints" class="alert alert-info d-none">
                                <i class="bi bi-info-circle me-2"></i> 
                                <span id="hint_text"></span>
                            </div>

                            <div id="image_upload_section" class="mb-3 upload-section d-none">
                                <label for="file_image" class="form-label fw-semibold">Image Upload <span class="text-danger small">(Only WebP allowed)</span></label>
                                <input class="form-control form-control-sm" type="file" id="file_image" name="file" accept=".webp" onchange="previewFile(this, 'item_img_preview', 'image')">
                                <div class="text-danger small mt-1">Warning: Only .webp images are supported.</div>
                                <div id="item_img_preview_container" class="mt-2 {{ isset($item) && $item->file_type == 'image' && $item->file ? '' : 'd-none' }}">
                                    <img id="item_img_preview" src="{{ isset($item) && $item->file_type == 'image' && $item->file ? asset($item->file) : '#' }}" alt="Image Preview" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <small class="text-muted">Selected category requires an image.</small>
                            </div>

                            <div id="video_upload_section" class="mb-3 upload-section d-none">
                                <div class="mb-2">
                                    <label for="file_video" class="form-label fw-semibold">Video Upload</label>
                                    <input class="form-control form-control-sm" type="file" id="file_video" name="file" accept="video/mp4,video/x-m4v,video/*" onchange="previewFile(this, 'item_video_preview', 'video')">
                                    <div id="item_video_preview_container" class="mt-2 {{ isset($item) && $item->file_type == 'video' && $item->file ? '' : 'd-none' }}">
                                        <video id="item_video_preview" src="{{ isset($item) && $item->file_type == 'video' && $item->file ? asset($item->file) : '#' }}" controls class="img-thumbnail" style="max-height: 100px;"></video>
                                    </div>
                                    <small class="text-muted">Selected category requires a video.</small>
                                </div>
                                <div class="mb-2">
                                    <label for="video_thumbnail" class="form-label fw-semibold">Video Thumbnail <span class="text-danger small">(Only WebP allowed)</span></label>
                                    <input class="form-control form-control-sm" type="file" id="video_thumbnail" name="video_thumbnail" accept=".webp" onchange="previewFile(this, 'item_vid_thumb_preview', 'image')">
                                    <div class="text-danger small mt-1">Warning: Only .webp images are supported.</div>
                                    <div id="item_vid_thumb_preview_container" class="mt-2 {{ isset($item) && $item->file_type == 'video' && $item->video_thumbnail ? '' : 'd-none' }}">
                                        <img id="item_vid_thumb_preview" src="{{ isset($item) && $item->file_type == 'video' && $item->video_thumbnail ? asset($item->video_thumbnail) : '#' }}" alt="Video Thumbnail Preview" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 d-flex align-items-center gap-4 border p-2 rounded bg-light">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                                        {{ old('status', $item->status ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 small fw-semibold" for="status">Item Active</label>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill shadow-sm">
                                    <i class="bi bi-save me-2"></i> {{ isset($item) ? 'Update Item' : 'Save Item' }}
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
            function updateUploadFields() {
                var selectedOption = $('#top_slider_category_id').find('option:selected');
                var type = selectedOption.data('type');
                
                $('#dynamic_upload_hints').removeClass('d-none');
                
                // Disable files input if sections are hidden to avoid submitting the wrong files
                if (type === 'image') {
                    $('#image_upload_section').removeClass('d-none');
                    $('#video_upload_section').addClass('d-none');
                    $('#file_video').prop('disabled', true);
                    $('#video_thumbnail').prop('disabled', true);
                    $('#file_image').prop('disabled', false);
                    $('#hint_text').text('This category expects an Image file.');
                } else if (type === 'video') {
                    $('#image_upload_section').addClass('d-none');
                    $('#video_upload_section').removeClass('d-none');
                    $('#file_image').prop('disabled', true);
                    $('#file_video').prop('disabled', false);
                    $('#video_thumbnail').prop('disabled', false);
                    $('#hint_text').text('This category expects a Video file and an optional thumbnail.');
                } else {
                    $('#image_upload_section').addClass('d-none');
                    $('#video_upload_section').addClass('d-none');
                    $('#dynamic_upload_hints').addClass('d-none');
                }
            }

            $('#top_slider_category_id').change(updateUploadFields);
            updateUploadFields(); // run on load
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
