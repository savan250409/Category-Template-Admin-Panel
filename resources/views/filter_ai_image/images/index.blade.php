@extends('partials.layout')
@section('title', 'Filter AI Images Management')
@section('container')
    <style>
        .stats-badge {
            background: #eaecf4;
            color: #5a5c69;
            padding: .5rem 1rem;
            border-radius: .35rem;
            font-size: .85rem;
            font-weight: 700;
        }

        .main-card {
            background: #fff;
            border-radius: .35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background: #f8f9fc;
            color: #4e73df;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem .75rem;
            border-bottom: 2px solid #e3e6f0;
        }

        .data-table td {
            padding: 1.25rem .75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e3e6f0;
            color: #5a5c69;
            font-size: 0.9rem;
        }

        .data-table tr:hover {
            background-color: #f8f9fc;
            transition: background-color 0.2s ease;
        }

        .category-badge {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .img-container {
            width: 100px;
            height: 60px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 2px solid #fff;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .prompt-text {
            color: #858796;
            font-size: 0.8rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
            max-width: 300px;
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

        .form-card {
            background: #fff;
            border-radius: .35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .img-thumbnail {
            max-height: 100px;
            object-fit: contain;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e3e6f0;
        }

        .pagination-info {
            color: #6e707e;
            font-size: .9rem;
        }

        .pagination {
            margin: 0;
        }

        .page-item .page-link {
            color: #4e73df;
            padding: .375rem .75rem;
            border: 1px solid #dddfeb;
            font-size: .9rem;
        }

        .page-item.active .page-link {
            background: #4e73df;
            border-color: #4e73df;
            color: white;
        }

        .page-item.disabled .page-link {
            color: #b7b9cc;
        }

        .page-link:hover {
            background: #eaecf4;
            border-color: #dddfeb;
        }

        .search-container {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }

        .search-input {
            border: 1px solid #d1d3e2;
            border-radius: .35rem;
            padding: .5rem 1rem;
            width: 300px;
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
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: #858796;
            margin-bottom: 1.5rem;
        }

        .sortable-ghost {
            opacity: 0.4;
        }

        .sortable-chosen {
            transform: scale(1.05);
        }

        .sortable-drag {
            transform: rotate(5deg);
        }

        .sortable-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><i class="bi bi-robot me-2"></i>Filter AI Images Management</h1>
                <p class="page-subtitle">Manage all Filter AI images in the system</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#indexingModal">
                    <i class="bi bi-arrow-up-down me-2"></i>Indexing
                </button>
                <span class="stats-badge"><i class="bi bi-collection"></i> Total: <span
                        id="totalCount">{{ $images->total() }}</span> Images</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="form-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 id="formTitle"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Filter AI Image</h4>
                <button type="button" id="cancelEdit" class="btn btn-outline-secondary d-none"><i
                        class="bi bi-x-lg me-1"></i>Cancel Edit</button>
            </div>

            <form id="filterAiImageForm" method="POST" enctype="multipart/form-data"
                action="{{ route('filter-ai-image.images.store') }}">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">
                <input type="hidden" id="editId" name="id" value="">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ai_prompt" class="form-label">Prompt</label>
                        <textarea class="form-control" id="ai_prompt" name="ai_prompt" rows="3" placeholder="Enter prompt" required></textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept=".webp"
                            onchange="previewImage(this)">
                        <small class="text-warning fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Only .webp images are allowed</small>
                        <div class="form-text">Upload image (max 4 MB)</div>
                        <div id="imagePreview" class="mt-2 d-none">
                            <img id="previewImg" src="#" alt="Preview" class="img-thumbnail">
                        </div>
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary py-2" id="submitBtn"><i class="bi bi-plus-lg me-2"></i>Add
                        Image</button>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="search-container">
                <div class="input-group" style="width: 350px;">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="Search by name, prompt, model, or category..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <div id="imagesTableContainer">
                @if ($images->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h4 class="empty-state-title">No Filter AI Images Found</h4>
                        <p class="empty-state-text">Get started by adding your first Filter AI image</p>
                    </div>
                @else
                    @section('table')
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="15%">Category</th>
                                        <th width="15%">Name</th>
                                        <th width="15%">Image</th>
                                        <th width="40%">Prompt</th>
                                        <th width="15%" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($images as $img)
                                        <tr id="row-{{ $img->id }}">
                                            <td><span class="category-badge">{{ $img->category->category_name ?? 'N/A' }}</span></td>
                                            <td><span class="fw-bold text-dark">{{ $img->name ?? 'N/A' }}</span></td>
                                            <td>
                                                @if ($img->image_path)
                                                    <div class="img-container">
                                                        <img src="{{ asset('upload/filter_ai_image/images/' . $img->category->category_name . '/category_image/' . $img->image_path) }}"
                                                            alt="{{ $img->name }}">
                                                    </div>
                                                @else
                                                    <div class="img-container d-flex align-items-center justify-content-center bg-light">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="prompt-text" title="{{ $img->ai_prompt }}">
                                                    {{ $img->ai_prompt }}
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="action-btn edit-btn"
                                                        data-id="{{ $img->id }}"
                                                        data-category="{{ $img->category_id }}"
                                                        data-name="{{ $img->name }}"
                                                        data-prompt="{{ $img->ai_prompt }}"
                                                        data-image="{{ $img->image_path }}" onclick="editImage(this)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form id="deleteForm-{{ $img->id }}"
                                                        action="{{ route('filter-ai-image.images.destroy', $img->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button" class="action-btn delete-btn"
                                                            data-id="{{ $img->id }}"
                                                            data-category="{{ $img->category->category_name ?? 'N/A' }}"
                                                            onclick="confirmDelete(this)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @show

                    @section('pagination')
                        <div class="pagination-container">
                            <div class="pagination-info">
                                Showing {{ $images->firstItem() }} to {{ $images->lastItem() }} of {{ $images->total() }}
                                entries
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    @if ($images->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link ajax-pagination"
                                                href="{{ $images->previousPageUrl() }}&search={{ request('search') }}">Previous</a>
                                        </li>
                                    @endif

                                    @php
                                        $currentPage = $images->currentPage();
                                        $lastPage = $images->lastPage();
                                    @endphp

                                    @if ($lastPage <= 8)
                                        @foreach ($images->getUrlRange(1, $lastPage) as $page => $url)
                                            <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                                <a class="page-link ajax-pagination"
                                                    href="{{ $url }}&search={{ request('search') }}">{{ $page }}</a>
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
                                                <a class="page-link ajax-pagination"
                                                    href="{{ $images->url(1) }}&search={{ request('search') }}">1</a>
                                            </li>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif

                                        @foreach ($images->getUrlRange($start, $end) as $page => $url)
                                            <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                                <a class="page-link ajax-pagination"
                                                    href="{{ $url }}&search={{ request('search') }}">{{ $page }}</a>
                                            </li>
                                        @endforeach

                                        @if ($end < $lastPage)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <li class="page-item">
                                                <a class="page-link ajax-pagination"
                                                    href="{{ $images->url($lastPage) }}&search={{ request('search') }}">{{ $lastPage }}</a>
                                            </li>
                                        @endif
                                    @endif

                                    @if ($images->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link ajax-pagination"
                                                href="{{ $images->nextPageUrl() }}&search={{ request('search') }}">Next</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @show
                @endif
            </div>
        </div>

    </div>

    <div class="modal fade" id="indexingModal" tabindex="-1" aria-labelledby="indexingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="indexingModalLabel">
                        <i class="bi bi-arrow-up-down me-2"></i>Image Indexing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="categorySelect" class="form-label">Select Category</label>
                                <select class="form-select" id="categorySelect">
                                    <option value="">Choose a category...</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Drag and drop images to reorder them. The new order will be saved automatically.
                            </div>
                        </div>
                    </div>

                    <div id="imagesContainer" class="row" style="min-height: 200px;">
                        <div class="col-12 text-center text-muted py-5">
                            <i class="bi bi-image fs-1"></i>
                            <p class="mt-2">Select a category to view and reorder images</p>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        let searchTimeout = null;

        document.addEventListener('DOMContentLoaded', function() {
            window.previewImage = function(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];

                    if (!file.name.toLowerCase().endsWith('.webp')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid File Format',
                            text: 'Only .webp images are allowed! Please select a .webp file.'
                        });
                        input.value = '';
                        document.getElementById('imagePreview').classList.add('d-none');
                        return;
                    }

                    const maxSize = 4 * 1024 * 1024;
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: 'The selected image exceeds 4 MB. Please choose a smaller file.'
                        });
                        input.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            };

            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadImages(1, this.value);
                    }, 500);
                });
            }

            document.getElementById('clearSearch').addEventListener('click', function() {
                searchInput.value = '';
                loadImages(1, '');
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimeout);
                    loadImages(1, this.value);
                }
            });
        });

        function loadImages(page, search = '') {
            $.ajax({
                url: "{{ route('filter-ai-image.images.index') }}",
                type: 'GET',
                data: {
                    page: page,
                    search: search
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    $('#imagesTableContainer').html(res.table + res.pagination);
                    $('#totalCount').text(res.total);
                    $('.ajax-pagination').off('click').on('click', function(e) {
                        e.preventDefault();
                        const url = new URL($(this).attr('href'));
                        const searchParam = url.searchParams.get('search') || '';
                        loadImages(url.searchParams.get('page') || 1, searchParam);
                    });
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        }

        function editImage(button) {
            const id = button.getAttribute('data-id');
            const category = button.getAttribute('data-category');
            const name = button.getAttribute('data-name');
            const prompt = button.getAttribute('data-prompt');
            const imagePath = button.getAttribute('data-image');

            document.getElementById('formTitle').innerHTML =
                '<i class="bi bi-pencil-square me-2 text-info"></i>Edit Filter AI Image';
            document.getElementById('submitBtn').innerHTML =
                '<i class="bi bi-save me-2"></i>Update Image';
            document.getElementById('cancelEdit').classList.remove('d-none');

            document.getElementById('filterAiImageForm').action = "{{ url('filter-ai-image/images') }}/" + id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('editId').value = id;
            document.getElementById('category_id').value = category;
            document.getElementById('name').value = name;
            document.getElementById('ai_prompt').value = prompt;

            if (imagePath) {
                const categoryName = button.closest('tr').querySelector('td:first-child strong').textContent.trim();
                const imgUrl = "{{ asset('upload/filter_ai_image/images') }}/" + categoryName + '/category_image/' + imagePath;
                document.getElementById('previewImg').src = imgUrl;
                document.getElementById('imagePreview').classList.remove('d-none');
            } else {
                document.getElementById('imagePreview').classList.add('d-none');
            }
        }

        function confirmDelete(button) {
            const id = button.getAttribute('data-id');
            const categoryName = button.getAttribute('data-category');

            Swal.fire({
                title: 'Are you sure?',
                text: `Are you sure you want to delete this Filter AI image?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm-' + id).submit();
                }
            });
        }

        document.getElementById('cancelEdit').addEventListener('click', function() {
            resetForm();
        });

        function resetForm() {
            document.getElementById('formTitle').innerHTML =
                '<i class="bi bi-plus-circle me-2 text-primary"></i>Add New Filter AI Image';
            document.getElementById('submitBtn').innerHTML =
                '<i class="bi bi-plus-lg me-2"></i>Add Image';
            document.getElementById('cancelEdit').classList.add('d-none');
            document.getElementById('filterAiImageForm').action = "{{ route('filter-ai-image.images.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('editId').value = '';
            document.getElementById('filterAiImageForm').reset();
            document.getElementById('imagePreview').classList.add('d-none');
        }

        document.getElementById('filterAiImageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const url = this.action;
            const method = document.getElementById('formMethod').value;
            const searchTerm = document.getElementById('searchInput').value;

            Swal.fire({
                title: method === 'POST' ? 'Adding Image...' : 'Updating Image...',
                text: 'Please wait while we process your request',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    loadImages(1, searchTerm);
                    resetForm();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: method === 'POST' ? 'Image added successfully!' :
                            'Image updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    let errMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errMsg
                    });
                }
            });
        });

        let sortableInstance = null;
        let currentCategoryId = null;

        document.getElementById('categorySelect').addEventListener('change', function() {
            const categoryId = this.value;
            if (categoryId) {
                loadCategoryImages(categoryId);
                currentCategoryId = categoryId;
            } else {
                document.getElementById('imagesContainer').innerHTML = `
                    <div class="col-12 text-center text-muted py-5">
                        <i class="bi bi-image fs-1"></i>
                        <p class="mt-2">Select a category to view and reorder images</p>
                    </div>
                `;
                document.getElementById('saveOrderBtn').style.display = 'none';
                if (sortableInstance) {
                    sortableInstance.destroy();
                    sortableInstance = null;
                }
            }
        });

        function loadCategoryImages(categoryId) {
            document.getElementById('imagesContainer').innerHTML = `
                <div class="col-12 text-center text-muted py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading images...</p>
                </div>
            `;

            $.ajax({
                url: "{{ route('filter-ai-image.images.indexing') }}",
                type: 'GET',
                data: {
                    category_id: categoryId
                },
                success: function(response) {
                    if (response.images && response.images.length > 0) {
                        displayImages(response.images);
                        initializeSortable();
                        document.getElementById('saveOrderBtn').style.display = 'inline-block';
                    } else {
                        document.getElementById('imagesContainer').innerHTML = `
                            <div class="col-12 text-center text-muted py-5">
                                <i class="bi bi-image fs-1"></i>
                                <p class="mt-2">No images found for this category</p>
                            </div>
                        `;
                        document.getElementById('saveOrderBtn').style.display = 'none';
                    }
                },
                error: function(xhr) {
                    console.error('Error loading images:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load images for this category.'
                    });
                }
            });
        }

        function displayImages(images) {
            const container = document.getElementById('imagesContainer');
            container.innerHTML = '';

            images.forEach((image, index) => {
                const imageHtml = `
                    <div class="col-md-3 mb-3" data-image-id="${image.id}">
                        <div class="card sortable-item" style="cursor: move;">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-primary me-2">${index + 1}</span>
                                    <small class="text-muted">ID: ${image.id}</small>
                                </div>
                                ${image.image_url ?
                                    `<img src="${image.image_url}"
                                                 class="img-fluid rounded" style="height: 120px; object-fit: cover; width: 100%;"
                                                 alt="Image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="height: 120px; display: none;">
                                                <i class="bi bi-image text-muted fs-4"></i>
                                            </div>` :
                                    `<div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="height: 120px;">
                                                <i class="bi bi-image text-muted fs-4"></i>
                                             </div>`
                                }
                                <div class="mt-2">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                        <strong>${image.name || 'No Name'}</strong><br>
                                        ${image.ai_prompt ? image.ai_prompt.substring(0, 40) + '...' : 'No prompt'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += imageHtml;
            });
        }

        function initializeSortable() {
            if (sortableInstance) {
                sortableInstance.destroy();
            }

            const container = document.getElementById('imagesContainer');
            sortableInstance = new Sortable(container, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag'
            });
        }

        document.getElementById('saveOrderBtn').addEventListener('click', function() {
            const items = document.querySelectorAll('#imagesContainer [data-image-id]');
            const orderData = [];

            items.forEach((item, index) => {
                orderData.push({
                    id: item.getAttribute('data-image-id'),
                    sort_order: index + 1
                });
            });

            Swal.fire({
                title: 'Saving Order...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('filter-ai-image.images.updateOrder') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    category_id: currentCategoryId,
                    order: orderData
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Order updated successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadImages(1, document.getElementById('searchInput').value);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update order.'
                    });
                }
            });
        });
    </script>
@endsection
