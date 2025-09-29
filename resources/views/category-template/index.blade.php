@extends('partials.layout')
@section('title', 'Subcategories')
@section('container')

    <div class="container mt-5">
        {{-- Card --}}
        <div class="card shadow-lg border-0 rounded-4">

            {{-- Card Header --}}
            <div
                class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center p-3 rounded-top">
                <h4 class="mb-0 d-flex align-items-center gap-2" style="color: black">
                    <i class="bi bi-folder2-open fs-4"></i> Subcategories
                </h4>
                <a href="{{ route('subcategories.form') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Add Subcategory
                </a>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Card Body --}}
            <div class="card-body p-0">
                @if ($subcategories->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-hash"></i> ID</th>
                                    <th><i class="bi bi-folder-fill"></i> Category</th>
                                    <th><i class="bi bi-image"></i> Thumbnail</th>
                                    <th><i class="bi bi-card-text"></i> Title</th>
                                    <th><i class="bi bi-file-text"></i> Description</th>
                                    <th class="text-center"><i class="bi bi-gear"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subcategories as $sub)
                                    <tr>
                                        <td class="fw-semibold">{{ $sub->id }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $sub->category_name }}</span></td>

                                        {{-- Category Thumbnail --}}
                                        <td>
                                            @if ($sub->category_thumbnail_image)
                                                <img src="{{ asset('upload/' . $sub->category_name . '/' . $sub->title . '/category_thumbnail/' . $sub->category_thumbnail_image) }}"
                                                    alt="Thumbnail" class="img-fluid rounded"
                                                    style="height:50px; object-fit:cover;">
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>

                                        <td>{{ $sub->title }}</td>
                                        <td>{{ Str::limit($sub->description, 50) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('subcategories.form', $sub->id) }}"
                                                class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="tooltip"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('subcategories.destroy', $sub->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-bs-toggle="tooltip" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center p-3">
                        {{ $subcategories->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x fs-1 text-muted"></i>
                        <p class="mt-2 text-muted">No subcategories found. Start by adding a new one.</p>
                        <a href="{{ route('subcategories.form') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Subcategory
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- SweetAlert Delete Confirmation --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let form = this.closest('form');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This subcategory will be deleted permanently.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Enable Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endsection
