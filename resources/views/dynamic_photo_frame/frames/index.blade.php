@extends('partials.layout')
@section('title', 'Dynamic Photo Frame Management')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; position: relative; }
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

        .loading-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, .7); display: flex; justify-content: center; align-items: center; z-index: 10; border-radius: .35rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary"><i class="bi bi-easel-fill me-2"></i>Dynamic Photo Frame Management</h1>
                <p class="text-muted">Manage dynamic photo frames</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge"><i class="bi bi-collection"></i> Total: <span id="totalCount" class="ms-1">{{ $frames->total() }}</span> Frames</span>
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

            <div id="ajax-container">
                @include('dynamic_photo_frame.frames.table')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 4000, timerProgressBar: true });
            @endif
            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            // Delete confirmation (delegated so it works after AJAX swaps)
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).attr('data-id');
                const name = $(this).attr('data-name');
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

            // AJAX loader
            function loadFrames(page) {
                const $card = $('.main-card');
                $card.append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');

                $.ajax({
                    url: "{{ route('dynamic-photo-frame.frames.index') }}",
                    type: 'GET',
                    data: {
                        page: page || 1,
                        per_page: $('#per_page').val(),
                        search: $('#searchInput').val(),
                        category_id: $('#category_filter').val(),
                    },
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    dataType: 'json',
                    success: function (res) {
                        if (res && typeof res.html === 'string') {
                            $('#ajax-container').html(res.html);
                            $('#totalCount').text(res.total ?? 0);
                        } else {
                            console.error('Unexpected response:', res);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected server response. See console.' });
                        }
                    },
                    error: function (xhr) {
                        console.error('AJAX error', xhr.status, xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load frames (' + xhr.status + ').' });
                    },
                    complete: function () {
                        $card.find('.loading-overlay').remove();
                    }
                });
            }

            // Filter handlers
            $('#per_page').on('change', function () { loadFrames(1); });
            $('#category_filter').on('change', function () { loadFrames(1); });

            // Search with debounce
            let searchTimer = null;
            $('#searchInput').on('keyup', function (e) {
                clearTimeout(searchTimer);
                if (e.key === 'Enter') {
                    loadFrames(1);
                    return;
                }
                searchTimer = setTimeout(() => loadFrames(1), 500);
            });
            $('#clearSearch').on('click', function () {
                $('#searchInput').val('');
                loadFrames(1);
            });

            // Pagination click (delegated)
            $(document).on('click', '#ajax-container .pagination a.page-link', function (e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadFrames(page);
            });
        });
    </script>
@endsection
