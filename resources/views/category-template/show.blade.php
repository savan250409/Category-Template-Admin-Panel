@extends('partials.layout')
@section('title', $subcategory->title)
@section('container')

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-4">

                    {{-- Header --}}
                    <div
                        class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3 px-4 rounded-top">
                        <h4 class="mb-0 d-flex align-items-center gap-2" style="color:black">
                            <i class="bi bi-folder2-open fs-4"></i>
                            <span>{{ $subcategory->title }}</span>
                        </h4>
                        <span class="badge bg-light text-dark fw-semibold">{{ $subcategory->category_name }}</span>
                    </div>

                    {{-- Header Image --}}
                    @if ($headerImage)
                        <div class="text-center mt-3">
                            <img src="{{ $headerImage }}" class="img-fluid rounded shadow-sm" style="max-height:250px;"
                                alt="Header Image">
                        </div>
                    @endif

                    {{-- Body --}}
                    <div class="card-body p-4">

                        {{-- Gallery --}}
                        @if (count($imageUrls))
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-2">Images</h6>
                                <div class="row g-2" id="image-preview">
                                    @foreach ($imageUrls as $index => $url)
                                        @if ($index < 3)
                                            <div class="col-4 col-md-2 position-relative">
                                                <img src="{{ $url }}"
                                                    class="img-fluid rounded shadow-sm border img-thumbnail gallery-img"
                                                    style="cursor:pointer;" data-bs-toggle="modal"
                                                    data-bs-target="#imageModal" data-img="{{ $url }}">
                                                @if ($index === 2 && count($imageUrls) > 3)
                                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                                                        style="background: rgba(0,0,0,0.5); color:#fff; font-weight:bold; font-size:1rem; cursor:pointer;"
                                                        id="overlay-more">
                                                        +{{ count($imageUrls) - 3 }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Details --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-uppercase text-muted mb-1">Title</h6>
                                <p class="fw-semibold">{{ $subcategory->title }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-uppercase text-muted mb-1">Category</h6>
                                <p class="fw-semibold">{{ $subcategory->category_name }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <h6 class="text-uppercase text-muted mb-1">Description</h6>
                                <p class="text-dark">{{ $subcategory->description ?? 'No description available.' }}</p>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="card-footer bg-light d-flex justify-content-between align-items-center rounded-bottom p-3">
                        <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <div class="d-flex gap-2">
                            <a href="{{ route('subcategories.form', $subcategory->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <form id="delete-form-{{ $subcategory->id }}"
                                action="{{ route('subcategories.destroy', $subcategory->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-secondary"
                                    style="background-color:red;color:white"
                                    onclick="confirmDelete({{ $subcategory->id }})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                    <img src="" id="modalImage" class="img-fluid rounded" alt="Full Image">
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageUrls = @json($imageUrls ?? []);

            // Delete subcategory with SweetAlert
            window.confirmDelete = function(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }

            // Modal image click
            function bindModalClick() {
                document.querySelectorAll('.gallery-img').forEach(img => {
                    img.addEventListener('click', function() {
                        document.getElementById('modalImage').src = this.dataset.img;
                    });
                });
            }
            bindModalClick();

            // "+X more" overlay click
            const overlayMore = document.getElementById('overlay-more');
            if (overlayMore) {
                overlayMore.addEventListener('click', function() {
                    const container = document.getElementById('image-preview');
                    container.innerHTML = '';
                    imageUrls.forEach(url => {
                        const div = document.createElement('div');
                        div.classList.add('col-4', 'col-md-2');
                        div.innerHTML =
                            `<img src="${url}" class="img-fluid rounded shadow-sm border img-thumbnail gallery-img" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#imageModal" data-img="${url}">`;
                        container.appendChild(div);
                    });
                    bindModalClick();
                });
            }
        });
    </script>

@endsection
