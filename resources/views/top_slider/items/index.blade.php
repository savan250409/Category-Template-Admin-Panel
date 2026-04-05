@if(!request()->ajax())
@extends('partials.layout')
@section('title', 'Top Slider Items')
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
                <h1 class="page-title"><i class="bi bi-collection-play me-2"></i>Top Slider Items</h1>
                <p class="page-subtitle">Manage all Top Slider Items</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total: <span id="total-items">{{ $items->total() }}</span> Items
                </span>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#indexingModal">
                    <i class="bi bi-arrow-up-down me-2"></i>Index
                </button>
                <a href="{{ route('top-slider.items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Item
                </a>
            </div>
        </div>

        <div class="main-card position-relative">
            <div class="row mb-3">
                <div class="col-md-4">
                    <form action="{{ route('top-slider.items.index') }}" method="GET" id="filterForm">
                        <select name="category_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->category_name }} ({{ strtoupper($cat->file_type) }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            
            <div id="table-content">
@endif
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Prompt</th>
                                <th>File Type</th>
                                <th>Media preview</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr id="row-{{ $item->id }}">
                                    <td>
                                        <span class="badge bg-secondary">{{ $item->category->category_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ Str::limit($item->prompt, 30) }}</td>
                                    <td>
                                        <span class="badge bg-info text-uppercase">{{ $item->file_type }}</span>
                                    </td>
                                    <td>
                                        @if ($item->file_type == 'image' && $item->file)
                                            <img src="{{ asset($item->file) }}" alt="Image" style="height: 50px; width: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;">
                                        @elseif ($item->file_type == 'video' && $item->video_thumbnail)
                                            <img src="{{ asset($item->video_thumbnail) }}" alt="Video Thumbnail" style="height: 50px; width: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px; border-radius: 5px; border: 1px solid #eee;">
                                                <i class="bi bi-collection-play text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch d-flex align-items-center gap-2">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch" id="status-{{ $item->id }}" data-id="{{ $item->id }}" {{ $item->status ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status-{{ $item->id }}">
                                                <span id="status-badge-{{ $item->id }}" class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $item->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('top-slider.items.edit', $item->id) }}" class="action-btn edit-btn" data-bs-toggle="tooltip" title="Edit Item">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="action-btn delete-btn" data-id="{{ $item->id }}" data-bs-toggle="tooltip" title="Delete Item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <form id="deleteForm-{{ $item->id }}" action="{{ route('top-slider.items.destroy', $item->id) }}" method="POST" style="display: none;">
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
                                            <h4 class="empty-state-title">No Items Found</h4>
                                            <p class="empty-state-text">Try adjusting your search or add a new item.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
                    </div>
                    <div>
                        {{ $items->appends(request()->except('page'))->links() }}
                    </div>
                </div>
                <div id="total-count-hidden" style="display: none;">{{ $items->total() }}</div>

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
                        <i class="bi bi-arrow-up-down me-2"></i>Item Indexing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Select a category to sort its items.
                    </div>
                    <select id="indexingCategorySelect" class="form-select mb-3">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                    
                    <div id="sortable-container" class="list-group" style="min-height: 200px;">
                        <div class="col-12 text-center text-muted py-5">
                            <p class="mt-2">Select a category to load items</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveOrderBtn" style="display: none;">
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
                    <p>Are you sure you want to delete this item?</p>
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
        let selectedItemId = null;

        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            @if(session('success'))
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 5000, timerProgressBar: true });
            @endif

            $(document).on('click', '.delete-btn', function () {
                selectedItemId = $(this).data('id');
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });

            $('#confirmDelete').on('click', function () {
                if (selectedItemId) {
                    document.getElementById(`deleteForm-${selectedItemId}`).submit();
                }
            });

            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                const url = new URL($(this).attr('href'));
                const page = url.searchParams.get('page');
                const categoryId = '{{ request("category_id") }}';
                loadTableData(page, categoryId);
            });

            function loadTableData(page, categoryId) {
                $('#table-content').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');
                $.ajax({
                    url: '{{ route("top-slider.items.index") }}',
                    data: { page: page, category_id: categoryId },
                    success: function (res) {
                        $('#table-content').html(res);
                        const total = $('#total-count-hidden').text();
                        if (total) $('#total-items').text(total);
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
                const catId = document.getElementById('indexingCategorySelect').value;
                if(catId) {
                    loadItemsForIndexing(catId);
                } else {
                    document.getElementById('saveOrderBtn').style.display = 'none';
                }
            });
            
            document.getElementById('indexingCategorySelect').addEventListener('change', function() {
                if(this.value) {
                    loadItemsForIndexing(this.value);
                } else {
                    document.getElementById('sortable-container').innerHTML = '<div class="col-12 text-center text-muted py-5"><p class="mt-2">Select a category to load items</p></div>';
                    document.getElementById('saveOrderBtn').style.display = 'none';
                }
            });

            function loadItemsForIndexing(categoryId) {
                const container = document.getElementById('sortable-container');
                container.innerHTML = '<div class="col-12 text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';

                $.ajax({
                    url: "{{ route('top-slider.items.indexing') }}",
                    type: 'GET',
                    data: { category_id: categoryId },
                    success: function (response) {
                        if (response.items && response.items.length > 0) {
                            document.getElementById('saveOrderBtn').style.display = 'inline-block';
                            container.innerHTML = '';
                            response.items.forEach((item, index) => {
                                const html = `
                                    <div class="list-group-item d-flex align-items-center justify-content-between sortable-item" data-id="${item.id}" style="cursor: move;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-grip-vertical me-2 text-muted fs-5"></i>
                                            <span class="fw-bold me-2">${index + 1}.</span>
                                            <span>${item.prompt ? item.prompt.substring(0, 30) : 'Item #' + item.id}</span>
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
                            document.getElementById('saveOrderBtn').style.display = 'none';
                            container.innerHTML = '<div class="col-12 text-center text-muted py-5"><p>No items found in this category.</p></div>';
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
                    url: "{{ route('top-slider.items.updateOrder') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", order: orderData },
                    success: function (response) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (response.success) {
                            const params = new URLSearchParams(window.location.search);
                            // Refresh the page retaining category id
                            window.location.reload();
                        }
                    }
                });
            });

            // Status Toggle
            $(document).on('change', '.status-toggle', function () {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const badge = $(`#status-badge-${id}`);

                $.ajax({
                    url: "{{ route('top-slider.items.updateStatus') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", id: id, status: isChecked ? 1 : 0 },
                    success: function (res) {
                        if (res.success) {
                            if (isChecked) badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                            else badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
                        } else {
                            $(`#status-${id}`).prop('checked', !isChecked);
                        }
                    }
                });
            });
        });
    </script>
@endsection
@endif
