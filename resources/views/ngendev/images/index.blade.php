@extends('partials.layout')
@section('title', 'Ngendev Images Management')
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
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8f9fc;
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
                <h1 class="page-title"><i class="bi bi-robot me-2"></i>Ngendev Images Management</h1>
                <p class="page-subtitle">Manage all Ngendev images in the system</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#bulkNameChangeModal">
                    <i class="bi bi-toggles me-2"></i>Name Change
                </button>
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
                <h4 id="formTitle"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Ngendev Image</h4>
                <button type="button" id="cancelEdit" class="btn btn-outline-secondary d-none"><i
                        class="bi bi-x-lg me-1"></i>Cancel Edit</button>
            </div>

            <form id="ngendevImageForm" method="POST" enctype="multipart/form-data"
                action="{{ route('ngendev.images.store') }}">
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
                        <label for="ai_model" class="form-label">Model</label>
                        <select class="form-select" id="ai_model" name="ai_model" required>
                            <option value="Ngendev Image">Ngendev Image</option>
                            <option value="Ngendev Figure">Ngendev Figure</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ai_prompt" class="form-label d-flex justify-content-between align-items-center">
                            <span>Prompt</span>
                            <small class="text-muted"><span id="aiPromptCounter">0</span>/2990</small>
                        </label>
                        <textarea class="form-control" id="ai_prompt" name="ai_prompt" rows="6" placeholder="Enter prompt" required></textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="mb-3">
                            <label for="no_of_image" class="form-label">No of Image</label>
                            <input type="number" class="form-control" id="no_of_image" name="no_of_image" value="1" min="1" required>
                        </div>
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="name_change" name="name_change" value="1">
                            <label class="form-check-label" for="name_change">Name Change</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 d-none" id="imageHintWrapper">
                        <label for="image_hint" class="form-label">Image Hint</label>
                        <input type="text" class="form-control" id="image_hint" name="image_hint" maxlength="255" placeholder="Enter image hint">
                        <small class="form-text text-muted">Shown only when Name Change is enabled.</small>
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
                        placeholder="Search by prompt, model, or category..." value="{{ request('search') }}">
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
                        <h4 class="empty-state-title">No Ngendev Images Found</h4>
                        <p class="empty-state-text">Get started by adding your first Ngendev image</p>
                    </div>
                @else
                    @section('table')
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Model</th>
                                        <th>Image</th>
                                        <th>Prompt</th>
                                        <th>No Of Image</th>
                                        <th>Name Change</th>
                                        <th>Image Hint</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($images as $img)
                                        <tr id="row-{{ $img->id }}">
                                            <td><strong>{{ $img->category->category_name ?? 'N/A' }}</strong></td>
                                            <td>{{ $img->ai_model ?? 'Ngendev Image' }}</td>
                                            <td>
                                                @if ($img->image_path)
                                                    <img src="{{ asset('upload/ngendev/images/' . $img->category->category_name . '/category_image/' . $img->image_path) }}"
                                                        width="80">
                                                @else
                                                    <div class="text-muted">No image</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width:300px;"
                                                    title="{{ $img->ai_prompt }}">
                                                    {{ $img->ai_prompt }}
                                                </div>
                                            </td>
                                            <td>{{ $img->no_of_image }}</td>
                                            <td>
                                                @if($img->name_change)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($img->name_change && $img->image_hint)
                                                    <div class="text-truncate" style="max-width:200px;" title="{{ $img->image_hint }}">{{ $img->image_hint }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="action-btn edit-btn"
                                                        data-id="{{ $img->id }}"
                                                        data-category="{{ $img->category_id }}"
                                                        data-model="{{ $img->ai_model }}"
                                                        data-prompt="{{ $img->ai_prompt }}"
                                                        data-noofimage="{{ $img->no_of_image }}"
                                                        data-namechange="{{ $img->name_change }}"
                                                        data-imagehint="{{ $img->image_hint }}"
                                                        data-image="{{ $img->image_path }}" onclick="editImage(this)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form id="deleteForm-{{ $img->id }}"
                                                        action="{{ route('ngendev.images.destroy', $img->id) }}"
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

        function toggleImageHint() {
            const checked = document.getElementById('name_change').checked;
            const wrapper = document.getElementById('imageHintWrapper');
            if (checked) {
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
                document.getElementById('image_hint').value = '';
            }
        }

        const PROMPT_LIMIT = 2990;

        function updatePromptCounter() {
            const ta = document.getElementById('ai_prompt');
            const counter = document.getElementById('aiPromptCounter');
            if (!ta || !counter) return;
            const len = ta.value.length;
            counter.textContent = len;
            const over = len > PROMPT_LIMIT;
            ta.classList.toggle('is-invalid', over);
            ta.style.borderColor = over ? '#dc3545' : '';
            counter.style.color = over ? '#dc3545' : '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name_change').addEventListener('change', toggleImageHint);
            toggleImageHint();

            const promptEl = document.getElementById('ai_prompt');
            if (promptEl) {
                promptEl.addEventListener('input', updatePromptCounter);
                updatePromptCounter();
            }

            const hintEl = document.getElementById('image_hint');
            if (hintEl) {
                hintEl.addEventListener('input', function () {
                    if (hintEl.value.trim() !== '') {
                        hintEl.classList.remove('is-invalid');
                        hintEl.style.borderColor = '';
                    }
                });
            }

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
                url: "{{ route('ngendev.images.index') }}",
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
            const model = button.getAttribute('data-model') || 'Ngendev Image';
            const prompt = button.getAttribute('data-prompt');
            const noOfImage = button.getAttribute('data-noofimage');
            const nameChange = button.getAttribute('data-namechange');
            const imageHint = button.getAttribute('data-imagehint') || '';
            const imagePath = button.getAttribute('data-image');

            document.getElementById('formTitle').innerHTML =
                '<i class="bi bi-pencil-square me-2 text-info"></i>Edit Ngendev Image';
            document.getElementById('submitBtn').innerHTML =
                '<i class="bi bi-save me-2"></i>Update Image';
            document.getElementById('cancelEdit').classList.remove('d-none');

            document.getElementById('ngendevImageForm').action = "{{ url('ngendev/images') }}/" + id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('editId').value = id;
            document.getElementById('category_id').value = category;
            document.getElementById('ai_model').value = model;
            document.getElementById('ai_prompt').value = prompt;
            document.getElementById('no_of_image').value = noOfImage;
            document.getElementById('name_change').checked = (nameChange == 1);
            document.getElementById('image_hint').value = imageHint;
            toggleImageHint();
            updatePromptCounter();

            if (imagePath) {
                const categoryName = button.closest('tr').querySelector('td:first-child strong').textContent.trim();
                const imgUrl = "{{ asset('upload/ngendev/images') }}/" + categoryName + '/category_image/' + imagePath;
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
                text: `Are you sure you want to delete the Ngendev image "${categoryName}"?`,
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
                '<i class="bi bi-plus-circle me-2 text-primary"></i>Add New Ngendev Image';
            document.getElementById('submitBtn').innerHTML =
                '<i class="bi bi-plus-lg me-2"></i>Add Image';
            document.getElementById('cancelEdit').classList.add('d-none');
            document.getElementById('ngendevImageForm').action = "{{ route('ngendev.images.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('editId').value = '';
            document.getElementById('ngendevImageForm').reset();
            document.getElementById('no_of_image').value = 1;
            document.getElementById('name_change').checked = false;
            document.getElementById('image_hint').value = '';
            toggleImageHint();
            updatePromptCounter();
            document.getElementById('imagePreview').classList.add('d-none');
        }

        document.getElementById('ngendevImageForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const promptVal = document.getElementById('ai_prompt').value;
            if (promptVal.length > PROMPT_LIMIT) {
                Swal.fire({
                    icon: 'error',
                    title: 'Prompt too long',
                    text: 'Prompt must not exceed ' + PROMPT_LIMIT + ' characters. Current: ' + promptVal.length + '.'
                });
                return;
            }

            const nameChangeChecked = document.getElementById('name_change').checked;
            const hintEl = document.getElementById('image_hint');
            const hintVal = hintEl.value.trim();
            if (nameChangeChecked && hintVal === '') {
                hintEl.classList.add('is-invalid');
                hintEl.style.borderColor = '#dc3545';
                hintEl.focus();
                Swal.fire({
                    icon: 'error',
                    title: 'Image Hint required',
                    text: 'Image Hint is required when Name Change is enabled.'
                });
                return;
            }
            hintEl.classList.remove('is-invalid');
            hintEl.style.borderColor = '';

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
                url: "{{ route('ngendev.images.indexing') }}",
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
                                        ${image.ai_prompt ? image.ai_prompt.substring(0, 50) + '...' : 'No prompt'}
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
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    updateOrderNumbers();
                }
            });
        }

        function updateOrderNumbers() {
            const items = document.querySelectorAll('#imagesContainer .col-md-3');
            items.forEach((item, index) => {
                const badge = item.querySelector('.badge');
                if (badge) {
                    badge.textContent = index + 1;
                }
            });
        }

        document.getElementById('saveOrderBtn').addEventListener('click', function() {
            if (!currentCategoryId) return;

            const items = document.querySelectorAll('#imagesContainer .col-md-3');
            const orderData = [];

            items.forEach((item, index) => {
                const imageId = item.getAttribute('data-image-id');
                orderData.push({
                    id: imageId,
                    sort_order: index + 1
                });
            });

            Swal.fire({
                title: 'Saving Order...',
                text: 'Please wait while we update the image order',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('ngendev.images.updateOrder') }}",
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
                        text: 'Image order updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadImages(1, document.getElementById('searchInput').value);
                },
                error: function(xhr) {
                    console.error('Error saving order:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to save image order.'
                    });
                }
            });
        });
    </script>

    <!-- Bulk Name Change Modal -->
    <div class="modal fade" id="bulkNameChangeModal" tabindex="-1" aria-labelledby="bulkNameChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkNameChangeModalLabel">
                        <i class="bi bi-toggles me-2"></i>Bulk Name Change by Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Click <strong>Set True</strong> or <strong>Set False</strong> on a category to update the
                        <code>name_change</code> field for <strong>all images</strong> in that category at once.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Category</th>
                                    <th class="text-center" style="width:170px;">Current State</th>
                                    <th class="text-center" style="width:170px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="bulkNameChangeTbody">
                                @foreach ($categories as $i => $category)
                                    <tr data-category-id="{{ $category->id }}">
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $category->category_name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary nc-state-badge">Loading...</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-grid gap-1">
                                                <button type="button" class="btn btn-sm btn-set-true text-nowrap"
                                                    style="background-color:#198754;border:1px solid #198754;color:#fff;border-radius:.25rem;"
                                                    data-category-id="{{ $category->id }}">
                                                    <i class="bi bi-check-lg"></i> Set True
                                                </button>
                                                <button type="button" class="btn btn-sm btn-set-false text-nowrap"
                                                    style="background-color:#dc3545;border:1px solid #dc3545;color:#fff;border-radius:.25rem;width:auto;height:auto;padding:.25rem .5rem;"
                                                    data-category-id="{{ $category->id }}">
                                                    <i class="bi bi-x-lg"></i> Set False
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const statsUrl = "{{ route('ngendev.images.nameChangeStats') }}";
            const bulkUrl = "{{ route('ngendev.images.bulkNameChange') }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";
            const modalEl = document.getElementById('bulkNameChangeModal');

            function renderBadge($cell, total, trueCount) {
                let label, cls;
                if (total === 0) {
                    label = 'No images';
                    cls = 'bg-secondary';
                } else if (trueCount === total) {
                    label = 'All True (' + total + ')';
                    cls = 'bg-success';
                } else if (trueCount === 0) {
                    label = 'All False (' + total + ')';
                    cls = 'bg-danger';
                } else {
                    label = 'Mixed (' + trueCount + '/' + total + ' true)';
                    cls = 'bg-warning text-dark';
                }
                $cell.find('.nc-state-badge').removeClass().addClass('badge nc-state-badge ' + cls).text(label);
            }

            function loadStats() {
                $.get(statsUrl, function (resp) {
                    const stats = resp.stats || {};
                    $('#bulkNameChangeTbody tr').each(function () {
                        const $row = $(this);
                        const catId = $row.data('category-id');
                        const stat = stats[catId];
                        const total = stat ? parseInt(stat.total) : 0;
                        const trueCount = stat ? parseInt(stat.true_count) : 0;
                        renderBadge($row, total, trueCount);
                    });
                });
            }

            modalEl.addEventListener('show.bs.modal', loadStats);

            $('#bulkNameChangeTbody').on('click', '.btn-set-true, .btn-set-false', function () {
                const $btn = $(this);
                const categoryId = $btn.data('category-id');
                const value = $btn.hasClass('btn-set-true') ? 1 : 0;
                const label = value ? 'true' : 'false';

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Set name_change to ' + label + ' for all images in this category?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, set ' + label,
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: value ? '#198754' : '#dc3545',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $btn.prop('disabled', true);
                    $.ajax({
                        url: bulkUrl,
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            category_id: categoryId,
                            name_change: value,
                        },
                        success: function (resp) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: resp.message || 'Updated successfully!',
                                showConfirmButton: false,
                                timer: 3500,
                                timerProgressBar: true,
                            });
                            loadStats();
                            if (typeof loadImages === 'function') {
                                const searchEl = document.getElementById('searchInput');
                                loadImages(1, searchEl ? searchEl.value : '');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to update.',
                            });
                        },
                        complete: function () {
                            $btn.prop('disabled', false);
                        },
                    });
                });
            });
        })();
    </script>
@endsection
