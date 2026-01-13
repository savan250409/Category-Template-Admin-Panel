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
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#indexingModal">
                    <i class="bi bi-arrow-up-down me-2"></i>Indexing
                </button>
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

        <div class="main-card position-relative">
            <div id="table-content">
                @include('ngendev.categories.table')
            </div>
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
                        Drag and drop categories to reorder them. The new order will be saved automatically.
                    </div>

                    <div id="categoriesContainer" class="list-group" style="min-height: 200px;">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let selectedCategoryId = null;
        let currentPage = 1;
        let currentPerPage = {{ $perPage }};
        let currentSearch = '{{ $search }}';

        document.addEventListener('DOMContentLoaded', function () {
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
            $(document).on('click', '.delete-btn', function () {
                selectedCategoryId = $(this).data('id');
                $('#categoryToDelete').text($(this).data('name'));
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });
            $('#confirmDelete').on('click', function () {
                if (selectedCategoryId) {
                    document.getElementById(`deleteForm-${selectedCategoryId}`).submit();
                }
            });

            // Search
            $('#search-form').on('submit', function (e) {
                e.preventDefault();
                currentSearch = $('#search-input').val();
                currentPage = 1;
                loadTableData();
            });

            // Per Page
            $('#per-page-select').on('change', function () {
                currentPerPage = $(this).val();
                currentPage = 1;
                loadTableData();
            });

            // Pagination click
            $(document).on('click', '.pagination .page-link', function (e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    currentPage = page;
                    loadTableData();
                }
            });

            function loadTableData() {
                // Remove existing overlay if any
                $('.loading-overlay').remove();

                // Append overlay
                $('#table-content').append(
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
                    success: function (res) {
                        $('#table-content').html(res);

                        // Update total
                        const total = $('#table-content').find('#total-count-hidden').text();
                        if (total) {
                            $('#total-categories').text(total);
                        }

                        // Tooltips
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                    },
                    error: function () {
                        $('.loading-overlay').remove(); // Remove overlay on error
                        $('#table-content').prepend(
                            '<div class="alert alert-danger alert-dismissible fade show">Error loading data. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'
                        );
                    }
                });
            }

            // Indexing Logic
            let sortableInstance = null;
            const indexingModal = document.getElementById('indexingModal');

            indexingModal.addEventListener('show.bs.modal', function () {
                loadCategoriesForIndexing();
            });

            function loadCategoriesForIndexing() {
                const container = document.getElementById('categoriesContainer');
                container.innerHTML = `
                                    <div class="col-12 text-center text-muted py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading categories...</p>
                                    </div>
                                `;

                $.ajax({
                    url: "{{ route('ngendev.categories.indexing') }}",
                    type: 'GET',
                    success: function (response) {
                        if (response.categories && response.categories.length > 0) {
                            displayCategories(response.categories);
                            initializeSortable();
                        } else {
                            container.innerHTML = `
                                                <div class="col-12 text-center text-muted py-5">
                                                    <i class="bi bi-tags fs-1"></i>
                                                    <p class="mt-2">No categories found</p>
                                                </div>
                                            `;
                        }
                    },
                    error: function (xhr) {
                        console.error('Error loading categories:', xhr.responseText);
                        container.innerHTML = `
                                             <div class="col-12 text-center text-danger py-5">
                                                <i class="bi bi-exclamation-triangle fs-1"></i>
                                                <p class="mt-2">Error loading categories</p>
                                            </div>
                                        `;
                    }
                });
            }

            function displayCategories(categories) {
                const container = document.getElementById('categoriesContainer');
                container.innerHTML = '';

                categories.forEach((category, index) => {
                    // console.log(category);
                    const categoryHtml = `
                                        <div class="list-group-item d-flex align-items-center justify-content-between sortable-item" data-id="${category.id}" style="cursor: move;">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-grip-vertical me-2 text-muted"></i>
                                                <span class="fw-bold me-2">${index + 1}.</span>
                                                <span>${category.category_name}</span>
                                            </div>
                                            <span class="badge bg-secondary rounded-pill">ID: ${category.id}</span>
                                        </div>
                                    `;
                    container.innerHTML += categoryHtml;
                });
            }

            function initializeSortable() {
                if (sortableInstance) {
                    sortableInstance.destroy();
                }

                const container = document.getElementById('categoriesContainer');
                sortableInstance = new Sortable(container, {
                    animation: 150,
                    ghostClass: 'bg-light',
                    onEnd: function (evt) {
                        updateOrderNumbers();
                    }
                });
            }

            function updateOrderNumbers() {
                const items = document.querySelectorAll('#categoriesContainer .list-group-item');
                items.forEach((item, index) => {
                    const numberSpan = item.querySelector('.fw-bold');
                    if (numberSpan) {
                        numberSpan.textContent = (index + 1) + '.';
                    }
                });
            }

            document.getElementById('saveOrderBtn').addEventListener('click', function () {
                const items = document.querySelectorAll('#categoriesContainer .list-group-item');
                const orderData = [];

                items.forEach((item, index) => {
                    const id = item.getAttribute('data-id');
                    orderData.push({
                        id: id,
                        sort_order: index + 1
                    });
                });

                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';

                $.ajax({
                    url: "{{ route('ngendev.categories.updateOrder') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        order: orderData
                    },
                    success: function (response) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;

                        if (response.success) {
                            // Close modal
                            const modal = bootstrap.Modal.getInstance(indexingModal);
                            modal.hide();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = response.redirect_url;
                            });
                        }
                    },
                    error: function (xhr) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        console.error('Error saving order:', xhr.responseText);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Something went wrong!'
                        });
                    }
                });
            });

            // Status Toggle
            $(document).on('change', '.status-toggle', function () {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const status = isChecked ? 1 : 0;
                const badge = $(`#status-badge-${id}`);

                $.ajax({
                    url: "{{ route('ngendev.categories.updateStatus') }}",
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

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });

                            Toast.fire({
                                icon: 'success',
                                title: res.message
                            });
                        }
                    },
                    error: function (xhr) {
                        // Revert on error
                        $(`#status-${id}`).prop('checked', !isChecked);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update status'
                        });
                    }
                });
            });

        });
    </script>
@endsection