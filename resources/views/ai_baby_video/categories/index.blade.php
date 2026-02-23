@if(!request()->ajax())
@extends('partials.layout')
@section('title', 'AI Baby Video Categories')
@section('container')
    <style>
        .stats-badge {
            background-color: #eaecf4;
            color: #5a5c69;
            padding: .5rem 1rem;
            border-radius: .35rem;
            font-size: .85rem;
            font-weight: 700;
        }

        .main-card {
            background-color: #fff;
            border-radius: .35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: #f8f9fc;
            color: #5a5c69;
            font-weight: 700;
            padding: .75rem;
            border-bottom: 1px solid #e3e6f0;
        }

        .data-table td {
            padding: .75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e3e6f0;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: .35rem;
            color: #fff;
            text-decoration: none;
        }

        .edit-btn {
            background-color: var(--bs-info);
        }

        .delete-btn {
            background-color: var(--bs-danger);
            border: none;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state-icon {
            font-size: 3.5rem;
            color: #b7b9cc;
            margin-bottom: 1rem;
        }

        .empty-state-title {
            color: #5a5c69;
            margin-bottom: .5rem;
        }

        .empty-state-text {
            color: #858796;
            margin-bottom: 1.5rem;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, .8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .pagination .page-link {
            cursor: pointer;
            padding: .5rem .75rem;
            margin: 0 .1rem;
            border-radius: .35rem;
            border: 1px solid #e3e6f0;
        }

        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><i class="bi bi-camera-video me-2"></i>AI Baby Video Categories</h1>
                <p class="page-subtitle">Manage all video categories in the system</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total: <span id="total-categories">{{ $categories->total() }}</span>
                    Categories
                </span>
                <a href="{{ route('ai-baby-video.categories.create') }}" class="btn btn-primary"><i
                        class="bi bi-plus-lg me-2"></i>Add Category</a>
            </div>
        </div>



        <div class="main-card position-relative">
            <div id="table-content">
@endif
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Category Name</th>
                                <th>Trending</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr id="row-{{ $category->id }}">
                                    <td>
                                        @if ($category->category_image)
                                            @if(\Illuminate\Support\Str::startsWith($category->category_image, 'upload/'))
                                                <img src="{{ asset($category->category_image) }}"
                                                    alt="{{ $category->category_name }}" style="height: 50px; width: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;">
                                            @elseif(file_exists(public_path('upload/AI Baby Video/' . $category->category_name . '/category thumbanail/' . $category->category_image)))
                                                <img src="{{ asset('upload/AI Baby Video/' . $category->category_name . '/category thumbanail/' . $category->category_image) }}"
                                                    alt="{{ $category->category_name }}" style="height: 50px; width: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;">
                                            @else
                                                <img src="{{ asset('upload/AI Baby Video/Category/' . $category->category_image) }}"
                                                    alt="{{ $category->category_name }}" style="height: 50px; width: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;">
                                            @endif
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px; border-radius: 5px; border: 1px solid #eee;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td><strong>{{ $category->category_name }}</strong></td>
                                    <td>
                                        @if($category->trending)
                                            <span class="badge bg-success"><i class="bi bi-graph-up me-1"></i>Trending</span>
                                        @else
                                            <span class="badge bg-secondary">Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch d-flex align-items-center gap-2">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch" id="status-{{ $category->id }}"
                                                data-id="{{ $category->id }}" {{ $category->status ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status-{{ $category->id }}">
                                                <span id="status-badge-{{ $category->id }}" class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $category->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('ai-baby-video.categories.edit', $category->id) }}" class="action-btn edit-btn" data-bs-toggle="tooltip" title="Edit Category">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" data-bs-toggle="tooltip" title="Delete Category">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <form id="deleteForm-{{ $category->id }}" action="{{ route('ai-baby-video.categories.destroy', $category->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="bi bi-folder-x"></i></div>
                                            <h4 class="empty-state-title">No Categories Found</h4>
                                            <p class="empty-state-text">Try adjusting your search or add a new category.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} entries
                    </div>
                    <div>
                        {{ $categories->appends(request()->except('page'))->links() }}
                    </div>
                </div>
                <div id="total-count-hidden" style="display: none;">{{ $categories->total() }}</div>

@if(!request()->ajax())
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm
                        Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<span id="categoryToDelete"></span>"?</p>
                    <p class="text-danger small">This will also delete all videos within this category!</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let selectedCategoryId = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            // Auto dismiss alerts using SweetAlert toasts
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

            @if(session('error'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
            });
            @endif

            // Delete Handling
            $(document).on('click', '.delete-btn', function () {
                selectedCategoryId = $(this).data('id');
                $('#categoryToDelete').text($(this).data('name'));
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });

            $('#confirmDelete').on('click', function () {
                if (selectedCategoryId) {
                    document.getElementById(`deleteForm-${selectedCategoryId}`).submit();
                }
            });

            // AJAX Pagination
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                const url = new URL($(this).attr('href'));
                const page = url.searchParams.get('page');
                loadTableData(page);
            });

            function loadTableData(page) {
                $('#table-content').append(
                    '<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                );

                $.ajax({
                    url: '{{ route("ai-baby-video.categories.index") }}',
                    data: { page: page },
                    success: function (res) {
                        $('#table-content').html(res);
                        const total = $('#total-count-hidden').text();
                        if (total) $('#total-categories').text(total);
                        
                        // Re-initialize tooltips
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                    },
                    error: function () {
                        $('.loading-overlay').remove();
                        Swal.fire('Error', 'Failed to load data', 'error');
                    }
                });
            }

            // Status Toggle
            $(document).on('change', '.status-toggle', function () {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const status = isChecked ? 1 : 0;
                const badge = $(`#status-badge-${id}`);

                $.ajax({
                    url: "{{ route('ai-baby-video.categories.updateStatus') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function (res) {
                        if (res.success) {
                            if (isChecked) {
                                badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                            } else {
                                badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: res.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            $(`#status-${id}`).prop('checked', !isChecked);
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function () {
                        $(`#status-${id}`).prop('checked', !isChecked);
                        Swal.fire('Error', 'Failed to update status', 'error');
                    }
                });
            });
        });
    </script>
@endsection
@endif