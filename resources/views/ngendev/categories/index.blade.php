@extends('partials.layout')
@section('title', 'Ngendev Category Management')
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

        .category-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
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
                <h1 class="page-title"><i class="bi bi-tags me-2"></i>Ngendev Category Management</h1>
                <p class="page-subtitle">Manage all Ngendev categories in the system</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total: <span id="total-categories">{{ $categories->total() }}</span>
                    Categories
                </span>
                <a href="{{ route('ngendev.categories.create') }}" class="btn btn-primary"><i
                        class="bi bi-plus-lg me-2"></i>Add Category</a>
            </div>
        </div>

        @if (session('success'))
            <div id="success-alert" class="alert alert-success alert-dismissible fade show mb-4">
                <i class="bi bi-check-circle-fill me-2"></i><strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>{{ session('error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="main-card">
            <div id="table-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <label class="me-2">Show</label>
                        <select id="per-page-select" class="form-select w-auto">
                            <option value="5" {{ $categories->perPage() == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $categories->perPage() == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ $categories->perPage() == 20 ? 'selected' : '' }}>20</option>
                            <option value="30" {{ $categories->perPage() == 30 ? 'selected' : '' }}>30</option>
                        </select>
                        <label class="ms-2">entries</label>
                    </div>
                    <form id="search-form" class="d-flex align-items-center">
                        <div class="input-group">
                            <input type="text" id="search-input" name="search" value="{{ $search }}"
                                class="form-control" placeholder="Search category...">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>

                @if ($categories->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-tags"></i></div>
                        <h4 class="empty-state-title">No Categories Found</h4>
                        <p class="empty-state-text">Get started by adding your first category</p>
                        <a href="{{ route('ngendev.categories.create') }}" class="btn btn-primary"><i
                                class="bi bi-plus-lg me-2"></i>Add Category</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="data-table" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Image</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $category->category_name }}</td>
                                        <td>
                                            @if ($category->category_image)
                                                @php $images = json_decode($category->category_image, true); @endphp
                                                @foreach ((array) $images as $img)
                                                    <img src="{{ asset('upload/ngendev/images/' . $category->category_name . '/category_thumbnail_image/' . $img) }}"
                                                        class="category-image">
                                                @endforeach
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('ngendev.categories.edit', $category->id) }}"
                                                    class="action-btn edit-btn" data-bs-toggle="tooltip" title="Edit"><i
                                                        class="bi bi-pencil"></i></a>
                                                <button type="button" class="action-btn delete-btn"
                                                    data-id="{{ $category->id }}"
                                                    data-name="{{ $category->category_name }}" data-bs-toggle="tooltip"
                                                    title="Delete"><i class="bi bi-trash"></i></button>

                                                <form id="deleteForm-{{ $category->id }}"
                                                    action="{{ route('ngendev.categories.destroy', $category->id) }}"
                                                    method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing <strong>{{ $categories->firstItem() }}</strong> to
                            <strong>{{ $categories->lastItem() }}</strong> of <strong>{{ $categories->total() }}</strong>
                            entries
                        </div>
                        <ul class="pagination mb-0">
                            <li class="page-item {{ $categories->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="javascript:void(0)"
                                    data-page="{{ $categories->currentPage() - 1 }}">Previous</a>
                            </li>
                            @for ($i = 1; $i <= $categories->lastPage(); $i++)
                                <li class="page-item {{ $i == $categories->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="javascript:void(0)"
                                        data-page="{{ $i }}">{{ $i }}</a>
                                </li>
                            @endfor
                            <li
                                class="page-item {{ $categories->currentPage() == $categories->lastPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="javascript:void(0)"
                                    data-page="{{ $categories->currentPage() + 1 }}">Next</a>
                            </li>
                        </ul>
                    </div>
                @endif
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
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedCategoryId = null;
        let currentPage = 1;
        let currentPerPage = {{ $perPage }};
        let currentSearch = '{{ $search }}';

        document.addEventListener('DOMContentLoaded', function() {
            // Auto dismiss alerts after 5 sec
            ['success-alert', 'error-alert'].forEach(id => {
                const alertEl = document.getElementById(id);
                if (alertEl) {
                    setTimeout(() => {
                        new bootstrap.Alert(alertEl).close();
                    }, 5000);
                }
            });

            // Delete
            $(document).on('click', '.delete-btn', function() {
                selectedCategoryId = $(this).data('id');
                $('#categoryToDelete').text($(this).data('name'));
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });
            $('#confirmDelete').on('click', function() {
                if (selectedCategoryId) {
                    document.getElementById(`deleteForm-${selectedCategoryId}`).submit();
                }
            });

            // Search
            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                currentSearch = $('#search-input').val();
                currentPage = 1;
                loadTableData();
            });

            // Per Page
            $('#per-page-select').on('change', function() {
                currentPerPage = $(this).val();
                currentPage = 1;
                loadTableData();
            });

            // Pagination click
            $(document).on('click', '.pagination .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    currentPage = page;
                    loadTableData();
                }
            });

            function loadTableData() {
                $('#table-content').html(
                    '<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                );
                $.ajax({
                    url: '{{ route('ngendev.categories.index') }}',
                    data: {
                        page: currentPage,
                        per_page: currentPerPage,
                        search: currentSearch,
                        ajax: true
                    },
                    success: function(res) {
                        const html = $(res).find('#table-content').html();
                        $('#table-content').html(html);

                        // Update total
                        const total = $(res).find('#total-categories').text();
                        $('#total-categories').text(total);

                        // Tooltips
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                    },
                    error: function() {
                        $('#table-content').html(
                            '<div class="alert alert-danger">Error loading data. Please try again.</div>'
                        );
                    }
                });
            }
        });
    </script>
@endsection
