@if(!request()->ajax())
@extends('partials.layout')
@section('title', 'Top Slider Categories')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .action-btn:hover { opacity: 0.8; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .empty-state-title { color: #5a5c69; margin-bottom: .5rem; }
        .empty-state-text { color: #858796; margin-bottom: 1.5rem; }
        .loading-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, .8); display: flex; justify-content: center; align-items: center; z-index: 10; }
        .pagination .page-link { cursor: pointer; padding: .5rem .75rem; margin: 0 .1rem; border-radius: .35rem; border: 1px solid #e3e6f0; }
        .pagination .page-item.active .page-link { background-color: #4e73df; border-color: #4e73df; color: #fff; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><i class="bi bi-images me-2"></i>Top Slider Categories</h1>
                <p class="page-subtitle">Manage all Top Slider Categories</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total: <span id="total-categories">{{ $categories->total() }}</span> Categories
                </span>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#indexingModal">
                    <i class="bi bi-arrow-up-down me-2"></i>Index
                </button>
                <a href="{{ route('top-slider.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Category
                </a>
            </div>
        </div>

        <div class="main-card position-relative">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="category_search" class="form-control border-start-0 ps-0" placeholder="Search by name..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="file_type_filter" class="form-select form-select-sm">
                        <option value="">All File Types</option>
                        <option value="image" {{ $fileType == 'image' ? 'selected' : '' }}>Image Only</option>
                        <option value="video" {{ $fileType == 'video' ? 'selected' : '' }}>Video Only</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex align-items-end">
                    <a href="#" id="dynamic_manage_link" class="btn btn-sm btn-outline-info d-none">
                        <i class="bi bi-gear-fill me-1"></i> <span>Manage Source Categories</span>
                    </a>
                </div>
            </div>

            <div id="table-content">
@endif
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>File Type</th>
                                <th>Top Slider Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr id="row-{{ $category->id }}">
                                    <td><strong>{{ $category->category_name }}</strong></td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase">{{ $category->file_type }}</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch d-flex align-items-center gap-2">
                                            <input class="form-check-input topslider-status-toggle" type="checkbox" role="switch" id="ts-status-{{ $category->id }}" data-id="{{ $category->id }}" {{ $category->top_slider_is_on ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ts-status-{{ $category->id }}">
                                                <span id="ts-badge-{{ $category->id }}" class="badge {{ $category->top_slider_is_on ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $category->top_slider_is_on ? 'ON' : 'OFF' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('top-slider.categories.edit', $category->id) }}" class="action-btn edit-btn" data-bs-toggle="tooltip" title="Edit Category">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" data-bs-toggle="tooltip" title="Delete Category">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <form id="deleteForm-{{ $category->id }}" action="{{ route('top-slider.categories.destroy', $category->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
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
                        Drag and drop categories to reorder them.
                    </div>
                    <div id="sortable-container" class="list-group" style="min-height: 200px;">
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
                    <button type="button" class="btn btn-primary" id="saveOrderBtn">
                        <i class="bi bi-save me-2"></i>Save Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<span id="categoryToDelete"></span>"?</p>
                    <p class="text-danger small">This will also delete associated files and items.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let selectedCategoryId = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            @if(session('success'))
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 5000, timerProgressBar: true });
            @endif

            @if(session('error'))
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: "{{ session('error') }}", showConfirmButton: false, timer: 5000, timerProgressBar: true });
            @endif

            // Delete
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

            // Pagination
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                const url = new URL($(this).attr('href'));
                const page = url.searchParams.get('page');
                loadTableData(page);
            });

            // Filtering
            $(document).on('input', '#category_search', function() {
                loadTableData(1);
            });

            $(document).on('change', '#file_type_filter', function() {
                updateManageLink();
                loadTableData(1);
            });

            function updateManageLink() {
                const fileType = $('#file_type_filter').val();
                const $link = $('#dynamic_manage_link');
                
                if (fileType === 'image') {
                    $link.attr('href', '{{ route("ngendev.categories.index") }}')
                         .removeClass('d-none')
                         .find('span').text('Manage AI Image Categories');
                } else if (fileType === 'video') {
                    $link.attr('href', '{{ route("ngendev-video-categories.index") }}')
                         .removeClass('d-none')
                         .find('span').text('Manage AI Video Categories');
                } else {
                    $link.addClass('d-none');
                }
            }
            
            updateManageLink(); // Run on initial load

            function loadTableData(page) {
                const search = $('#category_search').val();
                const fileType = $('#file_type_filter').val();
                
                $('#table-content').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');
                $.ajax({
                    url: '{{ route("top-slider.categories.index") }}',
                    data: { 
                        page: page,
                        search: search,
                        file_type: fileType
                    },
                    success: function (res) {
                        $('#table-content').html(res);
                        const total = $('#total-count-hidden').text();
                        if (total) $('#total-categories').text(total);
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                    },
                    error: function () {
                        $('.loading-overlay').remove();
                        Swal.fire('Error', 'Failed to load data', 'error');
                    }
                });
            }

            // Indexing
            let sortableInstance = null;
            const indexingModal = document.getElementById('indexingModal');

            indexingModal.addEventListener('show.bs.modal', function () {
                loadCategoriesForIndexing();
            });

            function loadCategoriesForIndexing() {
                const container = document.getElementById('sortable-container');
                container.innerHTML = '<div class="col-12 text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';

                $.ajax({
                    url: "{{ route('top-slider.categories.indexing') }}",
                    type: 'GET',
                    success: function (response) {
                        if (response.categories && response.categories.length > 0) {
                            container.innerHTML = '';
                            response.categories.forEach((category, index) => {
                                const html = `
                                    <div class="list-group-item d-flex align-items-center justify-content-between sortable-item" data-id="${category.id}" style="cursor: move;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-grip-vertical me-2 text-muted fs-5"></i>
                                            <span class="fw-bold me-2">${index + 1}.</span>
                                            <span>${category.category_name}</span>
                                        </div>
                                    </div>`;
                                container.innerHTML += html;
                            });
                            if (sortableInstance) sortableInstance.destroy();
                            sortableInstance = new Sortable(container, {
                                animation: 150, ghostClass: 'bg-light',
                                onEnd: function () {
                                    document.querySelectorAll('#sortable-container .list-group-item').forEach((item, index) => {
                                        item.querySelector('.fw-bold').textContent = (index + 1) + '.';
                                    });
                                }
                            });
                        } else {
                            container.innerHTML = '<div class="col-12 text-center text-muted py-5"><p>No categories found</p></div>';
                        }
                    }
                });
            }

            document.getElementById('saveOrderBtn').addEventListener('click', function () {
                const items = document.querySelectorAll('#sortable-container .list-group-item');
                const orderData = [];
                items.forEach((item, index) => { orderData.push({ id: item.getAttribute('data-id'), sort_order: index + 1 }); });

                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Saving...';

                $.ajax({
                    url: "{{ route('top-slider.categories.updateOrder') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", order: orderData },
                    success: function (response) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (response.success) {
                            bootstrap.Modal.getInstance(indexingModal).hide();
                            Swal.fire({ icon: 'success', title: 'Success', text: response.message }).then(() => { window.location.reload(); });
                        }
                    }
                });
            });

            
            // Top Slider Status Toggle
            $(document).on('change', '.topslider-status-toggle', function () {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const badge = $(`#ts-badge-${id}`);

                $.ajax({
                    url: "{{ route('top-slider.categories.updateTopSliderStatus') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", id: id, top_slider_is_on: isChecked ? 1 : 0 },
                    success: function (res) {
                        if (res.success) {
                            if (isChecked) badge.removeClass('bg-danger').addClass('bg-success').text('ON');
                            else badge.removeClass('bg-success').addClass('bg-danger').text('OFF');
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
                        } else {
                            $(`#ts-status-${id}`).prop('checked', !isChecked);
                        }
                    }
                });
            });
        });
    </script>
@endsection
@endif
