@extends('partials.layout')
@section('title', $subcategory->id ? 'Edit Subcategory' : 'Add Subcategory')
@section('container')

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div
                        class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0" style="color: black">
                            <i class="bi {{ $subcategory->id ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                            {{ $subcategory->id ? 'Edit Subcategory' : 'Add Subcategory' }}
                        </h4>
                        <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <form
                            action="{{ $subcategory->id ? route('subcategories.save', $subcategory->id) : route('subcategories.save') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name <span
                                        class="text-danger">*</span></label>
                                <select name="category_name"
                                    class="form-control @error('category_name') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}"
                                            {{ old('category_name', $subcategory->category_name) == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subcategory Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    placeholder="Enter subcategory title" value="{{ old('title', $subcategory->title) }}"
                                    required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subcategory Thumbnail Image
                                    {{ $subcategory->id ? '' : '*' }}</label>
                                @if ($subcategory->id && $subcategory->category_thumbnail_image)
                                    <div class="mb-2">
                                        <img id="thumbnailPreview"
                                            src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/category_thumbnail/' . $subcategory->category_thumbnail_image) }}"
                                            class="img-fluid rounded" style="height:120px; object-fit:cover;">
                                    </div>
                                @else
                                    <div class="mb-2">
                                        <img id="thumbnailPreview" src="#" class="img-fluid rounded"
                                            style="height:120px; object-fit:cover; display:none;">
                                    </div>
                                @endif
                                <input type="file" name="category_thumbnail_image" accept=".webp" class="form-control"
                                    {{ $subcategory->id ? '' : 'required' }} onchange="validateThumbnail(this)">
                                @error('category_thumbnail_image')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-check-circle"></i> {{ $subcategory->id ? 'Update' : 'Save' }}
                                </button>
                                <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function validateThumbnail(input) {
            const file = input.files[0];
            if (file) {
                const allowedTypes = ['image/webp'];
                const maxSize = 1 * 1024 * 1024;
                if (!allowedTypes.includes(file.type)) {
                    alert('Only .webp files are allowed.');
                    input.value = '';
                    return false;
                }
                if (file.size > maxSize) {
                    alert('File size must not exceed 1 MB.');
                    input.value = '';
                    return false;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnailPreview').src = e.target.result;
                    document.getElementById('thumbnailPreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

@endsection
