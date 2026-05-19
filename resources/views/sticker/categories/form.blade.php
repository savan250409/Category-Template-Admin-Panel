@extends('partials.layout')
@section('title', isset($category) ? 'Edit Sticker Category' : 'Add Sticker Category')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .preview-img { max-width: 200px; max-height: 160px; object-fit: cover; border-radius: .35rem; }
        .page-title { color: #7048e8; font-weight: 700; }
        .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($category) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($category) ? 'Edit Sticker Category' : 'Add New Sticker Category' }}
                </h1>
                <p class="text-muted">{{ isset($category) ? 'Update Sticker Category' : 'Create a new Sticker Category' }}</p>
            </div>
            <a href="{{ route('sticker.categories.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Categories
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($category) ? route('sticker.categories.update', $category->id) : route('sticker.categories.store') }}"
                enctype="multipart/form-data" id="categoryForm">
                @csrf
                @if (isset($category))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="category_name" class="form-label fw-semibold">Category Name</label>
                    <input type="text" class="form-control" id="category_name" name="category_name"
                        value="{{ old('category_name', $category->category_name ?? '') }}" placeholder="Category Name" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Thumbnail Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept=".webp,image/webp" onchange="previewImage(this)">
                    <small class="text-warning fw-bold mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>Upload Image (.webp only)
                    </small>
                    <div id="imagePreview" class="mt-2 {{ (isset($category) && $category->image) ? '' : 'd-none' }}">
                        <img id="previewImg"
                            src="{{ isset($category) && $category->image ? asset('upload/sticker/' . rawurlencode($category->category_name) . '/category image/' . rawurlencode($category->image)) : '#' }}"
                            class="preview-img" alt="Preview">
                    </div>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" value="1"
                        {{ old('is_premium', isset($category) ? $category->is_premium : 1) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_premium">Premium (Pro)</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                        {{ old('status', isset($category) ? $category->status : 1) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="status">Active</label>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($category) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($category) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('sticker.categories.index') }}" data-back-to-list class="btn btn-light py-2 px-4">Cancel</a>
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
        });
    </script>
@endsection
