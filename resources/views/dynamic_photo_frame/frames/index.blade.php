@extends('partials.layout')
@section('title', 'Dynamic Photo Frame Management')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-name { width: 22%; }
        .data-table .col-zip { width: 30%; }
        .data-table .col-input { width: 13%; }
        .data-table .col-thumb { width: 15%; }
        .data-table .col-action { width: 20%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .frame-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: .35rem; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .zip-link { color: #0d6efd; text-decoration: none; word-break: break-all; }
        .zip-link:hover { text-decoration: underline; }

        /* Filters row (matches NGD module layout) */
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
        .category-filter { border: 1px solid #d1d3e2; border-radius: .35rem; padding: .5rem 1rem; min-width: 220px; background-color: #fff; }
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
                <h1 class="page-title text-primary"><i class="bi bi-easel-fill me-2"></i>Dynamic Photo Frame Management</h1>
                <p class="text-muted">Manage dynamic photo frames</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge"><i class="bi bi-collection"></i> Total: <span class="ms-1">{{ $frames->total() }}</span> Frames</span>
                <a href="{{ route('dynamic-photo-frame.frames.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Frame
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
                    <select id="category_filter" class="category-filter custom-select-arrow">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by category..." value="{{ $search }}">
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
                            <th class="col-zip">Zip File</th>
                            <th class="col-input">Input Count</th>
                            <th class="col-thumb">Thumbnail</th>
                            <th class="col-action text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($frames as $frame)
                            <tr>
                                <td><strong>{{ $frame->category->category_name ?? 'N/A' }}</strong></td>
                                <td>
                                    @if ($frame->zip_file && $frame->category)
                                        <a class="zip-link" href="{{ asset('upload/dynamic_photo_frame/' . $frame->category->category_name . '/zip/' . $frame->zip_file) }}" target="_blank" title="{{ $frame->zip_file }}">
                                            <i class="bi bi-file-earmark-zip me-1"></i>{{ \Illuminate\Support\Str::limit($frame->zip_file, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $frame->input_count }}</td>
                                <td>
                                    @if ($frame->thumbnail && $frame->category)
                                        <img src="{{ asset('upload/dynamic_photo_frame/' . $frame->category->category_name . '/thumbnail/' . $frame->thumbnail) }}" class="frame-thumb" alt="">
                                    @else
                                        <div class="frame-thumb bg-light d-flex align-items-center justify-content-center">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('dynamic-photo-frame.frames.edit', $frame->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="action-btn delete-btn" data-id="{{ $frame->id }}" data-name="{{ $frame->category->category_name ?? 'this frame' }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="deleteForm-{{ $frame->id }}" action="{{ route('dynamic-photo-frame.frames.destroy', $frame->id) }}" method="POST" style="display:none;">
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
                                        <div class="empty-state-icon"><i class="bi bi-images"></i></div>
                                        <h4>No frames found</h4>
                                        <p class="text-muted">Add your first dynamic photo frame to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($frames->total() > 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing {{ $frames->firstItem() }} to {{ $frames->lastItem() }} of {{ $frames->total() }} entries
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            @if ($frames->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $frames->appends(request()->except('page'))->previousPageUrl() }}">Previous</a>
                                </li>
                            @endif

                            @php
                                $currentPage = $frames->currentPage();
                                $lastPage = $frames->lastPage();
                            @endphp

                            @if ($lastPage <= 8)
                                @foreach ($frames->getUrlRange(1, $lastPage) as $page => $url)
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
                                        <a class="page-link" href="{{ $frames->url(1) . '&' . http_build_query(request()->except('page')) }}">1</a>
                                    </li>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif

                                @foreach ($frames->getUrlRange($start, $end) as $page => $url)
                                    <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url . '&' . http_build_query(request()->except('page')) }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                @if ($end < $lastPage)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $frames->url($lastPage) . '&' . http_build_query(request()->except('page')) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif
                            @endif

                            @if ($frames->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $frames->appends(request()->except('page'))->nextPageUrl() }}">Next</a>
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
                        title: 'Delete frame?',
                        text: 'This will delete the frame from "' + name + '" and its files.',
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

            document.getElementById('category_filter').addEventListener('change', function () {
                applyParams(p => {
                    if (this.value) p.set('category_id', this.value);
                    else p.delete('category_id');
                });
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
