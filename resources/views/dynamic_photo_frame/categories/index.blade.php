@extends('partials.layout')
@section('title', 'Dynamic Photo Frame Category')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-name { width: 45%; }
        .data-table .col-thumb { width: 30%; }
        .data-table .col-action { width: 25%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .cat-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: .35rem; }

        /* Filters row (matches NGD module) */
        .filters-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filters-left { display: flex; align-items: center; gap: .75rem; }
        .custom-select-arrow {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%235a5c69' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right .75rem center;
            background-size: 14px 12px;
            padding-right: 2.25rem;
            cursor: pointer;
        }
        .per-page-select { border: 1px solid #d1d3e2; border-radius: .35rem; padding: .5rem .75rem; width: 88px; background-color: #fff; }
        .search-container { display: flex; justify-content: flex-end; }
        .search-container .input-group { width: 350px; }
        .search-container .form-control { border: 1px solid #d1d3e2; border-radius: .35rem 0 0 .35rem; padding: .5rem 1rem; }

        /* Pagination (matches NGD module) */
        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e3e6f0; flex-wrap: wrap; gap: .75rem; }
        .pagination-info { color: #6e707e; font-size: .9rem; }
        .pagination { display: flex; flex-wrap: wrap; gap: 4px; padding: 0; margin: 0; list-style: none; }
        .pagination .page-item { list-style: none; }
        .pagination .page-item .page-link { color: #4e73df; padding: .375rem .75rem; border: 1px solid #dddfeb; font-size: .9rem; cursor: pointer; background-color: #fff; border-radius: .25rem; text-decoration: none; display: inline-block; line-height: 1.5; min-width: 36px; text-align: center; transition: all .15s ease-in-out; }
        .pagination .page-item.active .page-link { background-color: #4e73df; border-color: #4e73df; color: #fff; }
        .pagination .page-item.disabled .page-link { color: #b7b9cc; pointer-events: none; background-color: #f8f9fc; }
        .pagination .page-item .page-link:hover { background-color: #eaecf4; border-color: #dddfeb; color: #2e59d9; }
        .pagination .page-item.active .page-link:hover { background-color: #4e73df; color: #fff; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary"><i class="bi bi-images me-2"></i>Dynamic Photo Frame Category</h1>
                <p class="page-subtitle text-muted">Manage dynamic photo frame categories</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total: <span class="ms-1">{{ $categories->total() }}</span> Categories
                </span>
                <a href="{{ route('dynamic-photo-frame.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Category
                </a>
            </div>
        </div>

        <div class="main-card">
            <div class="filters-row">
                <div class="filters-left">
                    <span>Show</span>
                    <select id="per_page" class="per-page-select custom-select-arrow">
                        @foreach ([10, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search categories..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-name">Category Name</th>
                            <th class="col-thumb">Thumbnail</th>
                            <th class="col-action text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr id="row-{{ $category->id }}">
                                <td><strong>{{ $category->category_name }}</strong></td>
                                <td>
                                    @if ($category->image)
                                        <img src="{{ asset('upload/dynamic_photo_frame/' . $category->category_name . '/category image/' . $category->image) }}" class="cat-thumb" alt="">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('dynamic-photo-frame.categories.edit', $category->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="deleteForm-{{ $category->id }}" action="{{ route('dynamic-photo-frame.categories.destroy', $category->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-folder-x"></i></div>
                                        <h4>No Categories Found</h4>
                                        <p class="text-muted">Add your first Dynamic Photo Frame category to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->total() > 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} entries
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            @if ($categories->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $categories->appends(request()->except('page'))->previousPageUrl() }}">Previous</a>
                                </li>
                            @endif

                            @php
                                $currentPage = $categories->currentPage();
                                $lastPage = $categories->lastPage();
                            @endphp

                            @if ($lastPage <= 8)
                                @foreach ($categories->getUrlRange(1, $lastPage) as $page => $url)
                                    <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url . '&' . http_build_query(request()->except('page')) }}">{{ $page }}</a>
                                    </li>
                                @endforeach
                            @else
                                @php
                                    $start = max(1, $currentPage - 3);
                                    $end = min($lastPage, $start + 7);
                                    if ($end - $start < 7) {
                                        $start = max(1, $end - 7);
                                    }
                                @endphp

                                @if ($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $categories->url(1) . '&' . http_build_query(request()->except('page')) }}">1</a>
                                    </li>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif

                                @foreach ($categories->getUrlRange($start, $end) as $page => $url)
                                    <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url . '&' . http_build_query(request()->except('page')) }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                @if ($end < $lastPage)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $categories->url($lastPage) . '&' . http_build_query(request()->except('page')) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif
                            @endif

                            @if ($categories->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $categories->appends(request()->except('page'))->nextPageUrl() }}">Next</a>
                                </li>
                            @else
                                <li class="page-item disabled"><span class="page-link">Next</span></li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 4000, timerProgressBar: true });
            @endif
            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Delete category?',
                        text: 'This will delete "' + name + '" and all its frames and files.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#bb2d3b',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('deleteForm-' + id).submit();
                        }
                    });
                });
            });

            function applyParams(updater) {
                const params = new URLSearchParams(window.location.search);
                updater(params);
                params.delete('page');
                window.location.search = params.toString();
            }

            document.getElementById('per_page').addEventListener('change', function () {
                applyParams(p => p.set('per_page', this.value));
            });

            const searchInput = document.getElementById('searchInput');
            let searchTimer = null;
            searchInput.addEventListener('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    applyParams(p => {
                        if (this.value) p.set('search', this.value);
                        else p.delete('search');
                    });
                }, 500);
            });
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimer);
                    applyParams(p => {
                        if (this.value) p.set('search', this.value);
                        else p.delete('search');
                    });
                }
            });
            document.getElementById('clearSearch').addEventListener('click', function () {
                searchInput.value = '';
                applyParams(p => p.delete('search'));
            });
        });
    </script>
@endsection
