@extends('partials.layout')
@section('title', isset($category) ? 'Edit Lips Sync Category' : 'Add Lips Sync Category')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .preview-img { max-width: 200px; max-height: 160px; object-fit: cover; border-radius: .35rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary">
                    <i class="bi bi-{{ isset($category) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($category) ? 'Edit Lips Sync Category' : 'Add New Lips Sync Category' }}
                </h1>
                <p class="text-muted">{{ isset($category) ? 'Update category details' : 'Create a new category' }}</p>
            </div>
            <a href="{{ route('lips-sync.categories.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Categories
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($category) ? route('lips-sync.categories.update', $category->id) : route('lips-sync.categories.store') }}"
                enctype="multipart/form-data" id="categoryForm">
                @csrf
                @if (isset($category))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="category_name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="category_name" name="category_name"
                        value="{{ old('category_name', $category->category_name ?? '') }}" placeholder="Enter category name" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Category Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept=".webp,image/webp" onchange="previewImage(this)">
                    <small class="text-warning fw-bold mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>Only .webp images are accepted
                    </small>
                    <div id="imagePreview" class="mt-2 {{ (isset($category) && $category->image) ? '' : 'd-none' }}">
                        <img id="previewImg"
                            src="{{ isset($category) && $category->image ? asset('upload/lips_sync/' . $category->category_name . '/category image/' . $category->image) : '#' }}"
                            class="preview-img" alt="Preview">
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary py-2">
                        <i class="bi bi-{{ isset($category) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($category) ? 'Update Category' : 'Add Category' }}
                    </button>
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
