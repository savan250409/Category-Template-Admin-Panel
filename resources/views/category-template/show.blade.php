@extends('partials.layout')
@section('title', $subcategory->title)
@section('container')

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-4">

                    <div
                        class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3 px-4 rounded-top">
                        <h4 class="mb-0" style="color:black">
                            <i class="bi bi-folder2-open fs-4"></i> {{ $subcategory->title }}
                        </h4>
                        <span class="badge bg-light text-dark fw-semibold">{{ $subcategory->category_name }}</span>
                    </div>

                    @if ($subcategory->category_thumbnail_image)
                        <div class="text-center mt-3">
                            <img src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/category_thumbnail/' . $subcategory->category_thumbnail_image) }}"
                                class="img-fluid rounded shadow-sm" style="max-height:200px;" alt="Category Thumbnail">
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="text-uppercase text-muted mb-1">Title</h6>
                                <p class="fw-semibold">{{ $subcategory->title }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <h6 class="text-uppercase text-muted mb-1">Category</h6>
                                <p class="fw-semibold">{{ $subcategory->category_name }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <h6 class="text-uppercase text-muted mb-1">Description</h6>
                                <p>{{ $subcategory->description ?? 'No description yet.' }}</p>
                            </div>
                        </div>

                        @if ($imageUrls && count($imageUrls))
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-2">Images</h6>
                                <div class="row">
                                    @foreach ($imageUrls as $img)
                                        <div class="col-md-4 mb-3">
                                            <div class="card shadow-sm">
                                                <img src="{{ $img['url'] }}" class="card-img-top"
                                                    style="height:200px; object-fit:cover;" alt="Image">
                                                @if (!empty($img['prompt']))
                                                    <div class="card-body">
                                                        <p class="mb-0 text-muted">{{ $img['prompt'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <a href="{{ route('subcategories.addDetailsForm', $subcategory->id) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Images & Description
                            </a>

                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editSubcategoryModal">
                                <i class="bi bi-pencil-square"></i> Edit Subcategory
                            </button>

                            <form id="deleteForm" action="{{ route('subcategories.destroy', $subcategory->id) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-secondary" onclick="confirmDelete()">
                                    <i class="bi bi-trash"></i> Delete Subcategory
                                </button>
                            </form>
                        </div>

                    </div>

                    <div class="card-footer bg-light d-flex justify-content-between align-items-center rounded-bottom p-3">
                        <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary"><i
                                class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-labelledby="editSubcategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('subcategories.save', $subcategory->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name</label>
                            <input type="text" class="form-control" value="{{ $subcategory->category_name }}" readonly>
                            <input type="hidden" name="category_name" value="{{ $subcategory->category_name }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subcategory Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ $subcategory->title }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ $subcategory->description }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thumbnail Image</label>
                            @if ($subcategory->category_thumbnail_image)
                                <div class="mb-2">
                                    <img src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/category_thumbnail/' . $subcategory->category_thumbnail_image) }}"
                                        class="img-fluid rounded mb-2" style="height:120px; object-fit:cover;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_thumbnail"
                                            id="removeThumbnail">
                                        <label class="form-check-label" for="removeThumbnail">Remove current
                                            thumbnail</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="category_thumbnail_image" accept="image/*" class="form-control">
                        </div>

                        @if ($subcategory->images)
                            @php $imagesArray = json_decode($subcategory->images, true); @endphp
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Edit Images & Prompts</label>
                                <div class="row">
                                    @foreach ($imagesArray as $index => $img)
                                        <div class="col-md-6 mb-3">
                                            <div class="card p-2">
                                                <img src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img['file']) }}"
                                                    class="img-fluid rounded mb-2"
                                                    style="height:120px; object-fit:cover;">
                                                <input type="text" name="existing_prompts[{{ $index }}]"
                                                    value="{{ $img['prompt'] }}" class="form-control mb-2"
                                                    placeholder="Edit prompt">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="remove_images[]" value="{{ $img['file'] }}"
                                                        id="removeImage{{ $index }}">
                                                    <label class="form-check-label"
                                                        for="removeImage{{ $index }}">Remove this image</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the subcategory!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('deleteForm').submit();
            });
        }
    </script>

@endsection
