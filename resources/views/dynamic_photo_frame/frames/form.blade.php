@extends('partials.layout')
@section('title', isset($frame) ? 'Edit Dynamic Photo Frame' : 'Add New Dynamic Photo Frame')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .preview-img { max-width: 200px; max-height: 160px; object-fit: cover; border-radius: .35rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary">
                    <i class="bi bi-{{ isset($frame) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($frame) ? 'Edit Dynamic Photo Frame' : 'Add New Dynamic Photo Frame' }}
                </h1>
                <p class="text-muted">{{ isset($frame) ? 'Update dynamic photo frame' : 'Create a new dynamic photo frame' }}</p>
            </div>
            <a href="{{ route('dynamic-photo-frame.frames.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Frames
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($frame) ? route('dynamic-photo-frame.frames.update', $frame->id) : route('dynamic-photo-frame.frames.store') }}"
                enctype="multipart/form-data" id="frameForm">
                @csrf
                @if (isset($frame))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="dynamic_photo_frame_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="dynamic_photo_frame_category_id" name="dynamic_photo_frame_category_id" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('dynamic_photo_frame_category_id', $frame->dynamic_photo_frame_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="zip_file" class="form-label">Zip File{{ isset($frame) ? '' : ' *' }}</label>
                    <input type="file" class="form-control" id="zip_file" name="zip_file" accept=".zip,application/zip,application/x-zip-compressed" {{ isset($frame) ? '' : 'required' }}>
                    <small class="text-warning fw-bold mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>Upload Zip File (.zip only)
                    </small>
                    @if (isset($frame) && $frame->zip_file && $frame->category)
                        <div class="mt-2">
                            <a href="{{ asset('upload/dynamic_photo_frame/' . $frame->category->category_name . '/zip/' . $frame->zip_file) }}" target="_blank" class="text-primary">
                                <i class="bi bi-file-earmark-zip me-1"></i>{{ $frame->zip_file }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="input_count" class="form-label">No. of Input Count <span class="text-danger">*</span></label>
                    <input type="number" min="1" class="form-control" id="input_count" name="input_count"
                        value="{{ old('input_count', $frame->input_count ?? 1) }}" required>
                </div>

                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Thumbnail{{ isset($frame) ? '' : ' *' }}</label>
                    <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept=".webp,image/webp" onchange="previewImage(this)" {{ isset($frame) ? '' : 'required' }}>
                    <small class="text-warning fw-bold mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>Upload Thumbnail (.webp only)
                    </small>
                    <div id="imagePreview" class="mt-2 {{ (isset($frame) && $frame->thumbnail) ? '' : 'd-none' }}">
                        <img id="previewImg"
                            src="{{ isset($frame) && $frame->thumbnail && $frame->category ? asset('upload/dynamic_photo_frame/' . $frame->category->category_name . '/thumbnail/' . $frame->thumbnail) : '#' }}"
                            class="preview-img" alt="Preview">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4" id="submitBtn">
                        <i class="bi bi-{{ isset($frame) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($frame) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('dynamic-photo-frame.frames.index') }}" class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.name.toLowerCase().endsWith('.webp')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid File Format',
                        text: 'Only .webp images are accepted. Please choose a .webp file.'
                    });
                    input.value = '';
                    document.getElementById('imagePreview').classList.add('d-none');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            const zipInput = document.getElementById('zip_file');
            zipInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;
                if (!file.name.toLowerCase().endsWith('.zip')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid File Format',
                        text: 'Only .zip files are accepted. Please choose a .zip file.'
                    });
                    this.value = '';
                }
            });
        });
    </script>
@endsection
