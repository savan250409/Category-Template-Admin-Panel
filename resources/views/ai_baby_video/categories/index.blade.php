@extends('partials.layout')
@section('title', 'AI Baby Video Categories')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; position: relative; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-thumb { width: 12%; }
        .data-table .col-name { width: 38%; }
        .data-table .col-trending { width: 15%; }
        .data-table .col-status { width: 18%; }
        .data-table .col-action { width: 17%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .cat-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: .35rem; border: 1px solid #eee; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }

        /* Filters row (matches Dynamic Photo Frame module) */
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

        /* Pagination (matches Dynamic Photo Frame module) */
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
                <h1 class="page-title text-primary"><i class="bi bi-camera-video me-2"></i>AI Baby Video Categories</h1>
                <p class="page-subtitle text-muted">Manage all video categories in the system</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total: <span id="totalCount" class="ms-1">{{ $categories->total() }}</span> Categories
                </span>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#indexingModal">
                    <i class="bi bi-arrow-up-down me-2"></i>Index
                </button>
                <a href="{{ route('ai-baby-video.categories.create') }}" class="btn btn-primary">
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

            <div id="ajax-container">
                @include('ai_baby_video.categories.table')
            </div>
        </div>
    </div>

    <!-- Indexing Modal -->
    <div class="modal fade" id="indexingModal" tabindex="-1" aria-labelledby="indexingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="indexingModalLabel">
                        <i class="bi bi-arrow-up-down me-2"></i>Category Indexing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Drag and drop categories to reorder them. New categories (sort order 0) appear first by default.
                    </div>
                    <div id="abv-categoriesContainer" class="list-group" style="min-height: 200px;">
                        <div class="col-12 text-center text-muted py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading categories...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="abvSaveOrderBtn">
                        <i class="bi bi-save me-2"></i>Save Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
                    title: 'Delete category?',
                    text: 'This will delete "' + name + '" and all its videos and files.',
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
            function loadCategories(page) {
                const $card = $('.main-card');
                $card.append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');

                $.ajax({
                    url: "{{ route('ai-baby-video.categories.index') }}",
                    type: 'GET',
                    data: {
                        page: page || 1,
                        per_page: $('#per_page').val(),
                        search: $('#searchInput').val(),
                    },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (res) {
                        $('#ajax-container').html(res.html);
                        $('#totalCount').text(res.total);
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load categories.' });
                    },
                    complete: function () {
                        $card.find('.loading-overlay').remove();
                    }
                });
            }

            // Filter handlers
            $('#per_page').on('change', function () { loadCategories(1); });

            // Search with debounce
            let searchTimer = null;
            $('#searchInput').on('keyup', function (e) {
                clearTimeout(searchTimer);
                if (e.key === 'Enter') {
                    loadCategories(1);
                    return;
                }
                searchTimer = setTimeout(() => loadCategories(1), 500);
            });
            $('#clearSearch').on('click', function () {
                $('#searchInput').val('');
                loadCategories(1);
            });

            // Pagination click (delegated)
            $(document).on('click', '#ajax-container .pagination a.page-link', function (e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadCategories(page);
            });

            // Status Toggle (delegated)
            $(document).on('change', '.status-toggle', function () {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const status = isChecked ? 1 : 0;
                const badge = $(`#status-badge-${id}`);

                $.ajax({
                    url: "{{ route('ai-baby-video.categories.updateStatus') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", id: id, status: status },
                    success: function (res) {
                        if (res.success) {
                            if (isChecked) {
                                badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                            } else {
                                badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                            }
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
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

            // Indexing Logic
            let abvSortableInstance = null;
            const abvIndexingModal = document.getElementById('indexingModal');

            abvIndexingModal.addEventListener('show.bs.modal', function () {
                loadAbvCategoriesForIndexing();
            });

            function loadAbvCategoriesForIndexing() {
                const container = document.getElementById('abv-categoriesContainer');
                container.innerHTML = `
                    <div class="col-12 text-center text-muted py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading categories...</p>
                    </div>`;

                $.ajax({
                    url: "{{ route('ai-baby-video.categories.indexing') }}",
                    type: 'GET',
                    success: function (response) {
                        if (response.categories && response.categories.length > 0) {
                            displayAbvCategories(response.categories);
                            initAbvSortable();
                        } else {
                            container.innerHTML = `
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="bi bi-camera-video fs-1"></i>
                                    <p class="mt-2">No categories found</p>
                                </div>`;
                        }
                    },
                    error: function () {
                        container.innerHTML = `
                            <div class="col-12 text-center text-danger py-5">
                                <i class="bi bi-exclamation-triangle fs-1"></i>
                                <p class="mt-2">Error loading categories</p>
                            </div>`;
                    }
                });
            }

            function displayAbvCategories(categories) {
                const container = document.getElementById('abv-categoriesContainer');
                container.innerHTML = '';
                categories.forEach((category, index) => {
                    const html = `
                        <div class="list-group-item d-flex align-items-center justify-content-between sortable-item" data-id="${category.id}" style="cursor: move;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-grip-vertical me-2 text-muted fs-5"></i>
                                <span class="fw-bold me-2">${index + 1}.</span>
                                <span>${category.category_name}</span>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-secondary rounded-pill">ID: ${category.id}</span>
                                <span class="badge ${category.sort_order == 0 ? 'bg-success' : 'bg-light text-dark border'} rounded-pill small">
                                    Order: ${category.sort_order}
                                </span>
                            </div>
                        </div>`;
                    container.innerHTML += html;
                });
            }

            function initAbvSortable() {
                if (abvSortableInstance) {
                    abvSortableInstance.destroy();
                }
                const container = document.getElementById('abv-categoriesContainer');
                abvSortableInstance = new Sortable(container, {
                    animation: 150,
                    ghostClass: 'bg-light',
                    onEnd: function () {
                        updateAbvOrderNumbers();
                    }
                });
            }

            function updateAbvOrderNumbers() {
                const items = document.querySelectorAll('#abv-categoriesContainer .list-group-item');
                items.forEach((item, index) => {
                    const numberSpan = item.querySelector('.fw-bold');
                    if (numberSpan) {
                        numberSpan.textContent = (index + 1) + '.';
                    }
                });
            }

            document.getElementById('abvSaveOrderBtn').addEventListener('click', function () {
                const items = document.querySelectorAll('#abv-categoriesContainer .list-group-item');
                const orderData = [];
                items.forEach((item, index) => {
                    orderData.push({ id: item.getAttribute('data-id'), sort_order: index + 1 });
                });

                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';

                $.ajax({
                    url: "{{ route('ai-baby-video.categories.updateOrder') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", order: orderData },
                    success: function (response) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (response.success) {
                            const modal = bootstrap.Modal.getInstance(abvIndexingModal);
                            modal.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // refresh table via AJAX instead of full reload
                                loadCategories(1);
                            });
                        }
                    },
                    error: function (xhr) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong!' });
                    }
                });
            });
        });
    </script>
@endsection
