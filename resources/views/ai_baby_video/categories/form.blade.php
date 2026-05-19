@extends('partials.layout')
@section('title', isset($category) ? 'Edit Category' : 'Add Category')
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
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($category) ? 'pencil-square' : 'plus-circle' }} me-2 text-primary"></i>
                    {{ isset($category) ? 'Edit Category' : 'Add New Category' }}
                </h1>
                <p class="page-subtitle">{{ isset($category) ? 'Update category details' : 'Create a new AI Baby Video category' }}
                </p>
            </div>
            <div>
                <a href="{{ route('ai-baby-video.categories.index') }}" data-back-to-list class="btn-custom-back">
                    <i class="bi bi-arrow-left me-2"></i>Back to Categories
                </a>
            </div>
        </div>



        <div class="form-card">
            <form method="POST"
                action="{{ isset($category) ? route('ai-baby-video.categories.update', $category->id) : route('ai-baby-video.categories.store') }}"
                id="categoryForm" enctype="multipart/form-data">
                @csrf
                @if (isset($category))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="category_name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="category_name" name="category_name"
                        value="{{ old('category_name', $category->category_name ?? '') }}" required
                        placeholder="Enter category name">
                </div>

                <div class="mb-3">
                    <label for="category_image" class="form-label">Category Image</label>
                    <input type="file" class="form-control" id="category_image" name="category_image" accept="image/*" onchange="previewImage(event)">
                    
                    <div class="mt-2" id="image_preview_container" style="{{ (isset($category) && $category->category_image) ? '' : 'display: none;' }}">
                        @if (isset($category) && $category->category_image)
                             @if(Str::startsWith($category->category_image, 'upload/'))
                                <img id="image_preview" src="{{ asset($category->category_image) }}" alt="Category Image" width="100" class="img-thumbnail">
                            @elseif(file_exists(public_path('upload/AI Baby Video/' . $category->category_name . '/category thumbanail/' . $category->category_image)))
                                <img id="image_preview" src="{{ asset('upload/AI Baby Video/' . $category->category_name . '/category thumbanail/' . $category->category_image) }}" alt="Category Image" width="100" class="img-thumbnail">
                            @else
                                <img id="image_preview" src="{{ asset('upload/AI Baby Video/Category/' . $category->category_image) }}" alt="Category Image" width="100" class="img-thumbnail">
                            @endif
                        @else
                            <img id="image_preview" src="" alt="Category Image" width="100" class="img-thumbnail">
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                        placeholder="Enter category description">{{ old('description', $category->description ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="trending" name="trending" value="1"
                            {{ old('trending', $category->trending ?? 0) ? 'checked' : '' }}>
                        <label class="form-check-label" for="trending">Trending</label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1"
                            {{ old('status', $category->status ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Active Status</label>
                    </div>
                </div>

                <div class="d-grid">
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
        function previewImage(event) {
            const input = event.target;
            const container = document.getElementById('image_preview_container');
            const preview = document.getElementById('image_preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
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

            @if ($errors->any())
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
    </script>
@endsection
