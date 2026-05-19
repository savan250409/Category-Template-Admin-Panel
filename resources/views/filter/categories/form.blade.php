@extends('partials.layout')
@section('title', isset($category) ? 'Edit Filter Category' : 'Add New Filter Category')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .page-title { color: #7048e8; font-weight: 700; }
        .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
        .preview-img { max-width: 180px; max-height: 140px; object-fit: contain; padding: 6px; background:#f8f9fc; border:1px solid #e3e6f0; border-radius: .35rem; }

        .file-picker { display: flex; align-items: stretch; border: 1px solid #e3e6f0; border-radius: .35rem; overflow: hidden; }
        .file-picker .choose-btn { background: #fff; border: none; border-right: 1px solid #e3e6f0; font-weight: 700; padding: .65rem 1.1rem; color: #2c3e50; cursor: pointer; }
        .file-picker .choose-btn:hover { background: #f8f9fc; }
        .file-picker .file-name { flex: 1; padding: .65rem 1rem; color: #6c757d; display: flex; align-items: center; }
        .file-picker.has-file .file-name { color: #2c3e50; }
        .upload-btn { background: linear-gradient(135deg, #c06aef 0%, #7048e8 100%); color: #fff; border: none; padding: 0 1.5rem; font-weight: 700; cursor: pointer; }
        .webp-warning { display:inline-flex; align-items:center; gap:.35rem; background:#fff8e1; color:#8a6d00; border:1px solid #ffe082; padding:.4rem .75rem; border-radius:.35rem; font-size:.85rem; font-weight:600; }
        .webp-warning i { color:#f0a500; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($category) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($category) ? 'Edit Filter Category' : 'Add New Filter Category' }}
                </h1>
                <p class="text-muted">{{ isset($category) ? 'Update category details' : 'Create a new Filter Category' }}</p>
            </div>
            <a href="{{ route('filter.categories.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Categories
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                  action="{{ isset($category) ? route('filter.categories.update', $category->id) : route('filter.categories.store') }}"
                  enctype="multipart/form-data" id="categoryForm">
                @csrf
                @if (isset($category)) @method('PUT') @endif

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="{{ old('name', $category->name ?? '') }}" placeholder="Category Name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Thumbnail Image</label>
                    <div class="file-picker {{ isset($category) && $category->image ? 'has-file' : '' }}" id="filePicker">
                        <button type="button" class="choose-btn" id="chooseImageBtn">Upload Image</button>
                        <div class="file-name" id="fileName">
                            {{ isset($category) && $category->image ? $category->image : 'Upload Image' }}
                        </div>
                        <button type="button" class="upload-btn" id="chooseImageBtnAlt">Upload</button>
                        <input type="file" id="image" name="image" accept=".webp,image/webp" hidden>
                    </div>
                    <div class="webp-warning mt-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Only <strong>.webp</strong> images are allowed.
                    </div>
                    @if (isset($category) && $category->image)
                        <div class="mt-2">
                            <img id="currentPreview" class="preview-img"
                                 src="{{ asset('upload/filter/' . rawurlencode($category->name) . '/category image/' . rawurlencode($category->image)) }}" alt="">
                        </div>
                    @else
                        <img id="currentPreview" class="preview-img mt-2 d-none" alt="">
                    @endif
                </div>

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                           {{ old('status', isset($category) ? $category->status : 1) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="status">Active</label>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($category) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($category) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('filter.categories.index') }}" data-back-to-list class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput  = document.getElementById('image');
            const chooseBtn  = document.getElementById('chooseImageBtn');
            const chooseBtn2 = document.getElementById('chooseImageBtnAlt');
            const fileName   = document.getElementById('fileName');
            const filePicker = document.getElementById('filePicker');
            const preview    = document.getElementById('currentPreview');

            [chooseBtn, chooseBtn2].forEach(b => b && b.addEventListener('click', () => fileInput.click()));

            fileInput.addEventListener('change', function () {
                if (!this.files || !this.files[0]) return;
                const file = this.files[0];
                fileName.textContent = file.name;
                filePicker.classList.add('has-file');
                const reader = new FileReader();
                reader.onload = e => { preview.src = e.target.result; preview.classList.remove('d-none'); };
                reader.readAsDataURL(file);
            });

            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif
        });
    </script>
@endsection
