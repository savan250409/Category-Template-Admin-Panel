@extends('partials.layout')
@section('title', $subcategory->id ? 'Edit Subcategory' : 'Add Subcategory')
@section('container')

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-3">
                    <div
                        class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0" style="color: black">
                            <i class="bi {{ $subcategory->id ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                            {{ $subcategory->id ? 'Edit Subcategory' : 'Add Subcategory' }}
                        </h4>
                        <a href="{{ route('subcategories.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <form
                            action="{{ $subcategory->id ? route('subcategories.save', $subcategory->id) : route('subcategories.save') }}"
                            method="POST" enctype="multipart/form-data" id="subcategoryForm">
                            @csrf
                            <input type="hidden" name="category_name"
                                value="{{ old('category_name', $subcategory->category_name) }}">

                            {{-- Title --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    placeholder="Enter subcategory title" value="{{ old('title', $subcategory->title) }}"
                                    required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Images --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Images {{ $subcategory->id ? '' : '*' }}</label>
                                <input type="file" id="imagesInput" name="images[]" multiple accept="image/*"
                                    class="form-control mb-2">

                                <div class="d-flex flex-wrap gap-2" id="imagesPreview">
                                    {{-- Existing images --}}
                                    @if ($subcategory->images)
                                        @foreach (json_decode($subcategory->images, true) as $img)
                                            <div class="position-relative border rounded image-preview-item"
                                                style="width:120px; height:120px;"
                                                data-full="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img) }}">
                                                <img src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img) }}"
                                                    class="img-fluid h-100 w-100 object-fit-cover preview-clickable">
                                                <button type="button"
                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image"
                                                    data-existing="1" data-name="{{ $img }}"
                                                    style="font-size:14px;padding:2px 6px;">&times;</button>
                                                <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Enter a short description...">{{ old('description', $subcategory->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Buttons --}}
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle"></i>
                                    {{ $subcategory->id ? 'Update' : 'Save' }}</button>
                                <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary px-4"><i
                                        class="bi bi-x-circle"></i> Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imagesInput = document.getElementById('imagesInput');
            const imagesPreview = document.getElementById('imagesPreview');

            let newFiles = [];

            // Preview new images
            imagesInput.addEventListener('change', function() {
                Array.from(this.files).forEach(file => newFiles.push(file));
                renderPreview();
            });

            function renderPreview() {
                imagesPreview.innerHTML = '';

                // Existing images
                document.querySelectorAll('input[name="existing_images[]"]').forEach(input => {
                    const name = input.value;
                    const div = document.createElement('div');
                    div.classList.add('position-relative', 'border', 'rounded', 'image-preview-item');
                    div.style.width = '120px';
                    div.style.height = '120px';
                    div.dataset.full =
                        "{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/') }}" +
                        name;
                    div.innerHTML = `
                <img src="${div.dataset.full}" class="img-fluid h-100 w-100 object-fit-cover preview-clickable">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image" data-existing="1" data-name="${name}" style="font-size:14px;padding:2px 6px;">&times;</button>
                <input type="hidden" name="existing_images[]" value="${name}">
            `;
                    imagesPreview.appendChild(div);
                });

                // New files
                newFiles.forEach((file, index) => {
                    const div = document.createElement('div');
                    div.classList.add('position-relative', 'border', 'rounded', 'image-preview-item');
                    div.style.width = '120px';
                    div.style.height = '120px';
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        div.dataset.full = e.target.result;
                        div.innerHTML = `
                    <img src="${e.target.result}" class="img-fluid h-100 w-100 object-fit-cover preview-clickable">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image" data-existing="0" data-index="${index}" style="font-size:14px;padding:2px 6px;">&times;</button>
                `;
                    }
                    reader.readAsDataURL(file);
                    imagesPreview.appendChild(div);
                });
            }

            // Remove image
            imagesPreview.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-image')) {
                    const isExisting = e.target.dataset.existing === '1';
                    if (isExisting) {
                        const name = e.target.dataset.name;
                        document.querySelectorAll('input[name="existing_images[]"]').forEach(input => {
                            if (input.value === name) input.remove();
                        });
                    } else {
                        const index = parseInt(e.target.dataset.index);
                        newFiles.splice(index, 1);
                        const dt = new DataTransfer();
                        newFiles.forEach(f => dt.items.add(f));
                        imagesInput.files = dt.files;
                    }
                    e.target.parentElement.remove();
                }
            });
        });
    </script>
@endsection
