@extends('partials.layout')
@section('title', 'Baby AI Home Screen Slider')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-type { width: 18%; }
        .data-table .col-source { width: 22%; }
        .data-table .col-title { width: 22%; }
        .data-table .col-preview { width: 13%; }
        .data-table .col-status { width: 12%; }
        .data-table .col-action { width: 13%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .preview-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: .35rem; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .type-badge { display: inline-block; padding: .35rem .65rem; border-radius: .35rem; font-size: .8rem; font-weight: 600; text-transform: uppercase; white-space: nowrap; }
        .type-badge.image { background: #e7f5ff; color: #1c7ed6; }
        .type-badge.video { background: #fff4e6; color: #e8590c; }
        .type-badge.dynamic_frame { background: #f3eaff; color: #7048e8; }

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
        .source-filter { border: 1px solid #d1d3e2; border-radius: .35rem; padding: .5rem 1rem; min-width: 220px; background-color: #fff; }
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
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary"><i class="bi bi-house-heart-fill me-2"></i>Baby AI Home Screen Slider</h1>
                <p class="text-muted">Manage Baby AI home screen slider (max 1 entry per source type)</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge"><i class="bi bi-collection"></i> Total: <span class="ms-1">{{ $sliders->total() }}</span> / 3 Sliders</span>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#indexingModal" @if($sliders->total() === 0) disabled title="No sliders to reorder" @endif>
                    <i class="bi bi-arrow-up-down me-2"></i>Index
                </button>
                @if ($canAddMore)
                    <a href="{{ route('baby-ai-home-slider.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add Slider
                    </a>
                @else
                    <button type="button" class="btn btn-primary" disabled title="All 3 source types already added">
                        <i class="bi bi-plus-lg me-2"></i>Add Slider
                    </button>
                @endif
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
                    <select id="source_filter" class="source-filter custom-select-arrow">
                        <option value="">All Source Types</option>
                        <option value="image" {{ $sourceType === 'image' ? 'selected' : '' }}>Image (Sub Category)</option>
                        <option value="video" {{ $sourceType === 'video' ? 'selected' : '' }}>Video Category</option>
                        <option value="dynamic_frame" {{ $sourceType === 'dynamic_frame' ? 'selected' : '' }}>Dynamic Frame Category</option>
                    </select>
                </div>
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by title or description..." value="{{ $search }}">
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
                            <th class="col-type">Source Type</th>
                            <th class="col-source">Source Category</th>
                            <th class="col-title">Title</th>
                            <th class="col-preview">Preview</th>
                            <th class="col-status">Status</th>
                            <th class="col-action text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sliders as $slider)
                            <tr id="row-{{ $slider->id }}">
                                <td>
                                    <span class="type-badge {{ $slider->source_type }}">
                                        @if ($slider->source_type === 'image') Image
                                        @elseif ($slider->source_type === 'video') Video
                                        @else Dynamic Frame
                                        @endif
                                    </span>
                                </td>
                                <td><strong>{{ $slider->source_name }}</strong></td>
                                <td>{{ $slider->title ?: '—' }}</td>
                                <td>
                                    @php $base = 'upload/baby_ai_home_slider/' . $slider->source_type . '/'; @endphp
                                    @if ($slider->source_type === 'video')
                                        @if ($slider->video_thumbnail)
                                            <img src="{{ asset($base . $slider->video_thumbnail) }}" class="preview-thumb" alt="">
                                        @elseif ($slider->video)
                                            <video src="{{ asset($base . $slider->video) }}" class="preview-thumb" muted></video>
                                        @else
                                            <div class="preview-thumb bg-light d-flex align-items-center justify-content-center"><i class="bi bi-camera-video text-muted"></i></div>
                                        @endif
                                    @else
                                        @if ($slider->image)
                                            <img src="{{ asset($base . $slider->image) }}" class="preview-thumb" alt="">
                                        @else
                                            <div class="preview-thumb bg-light d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch d-flex align-items-center gap-2">
                                        <input class="form-check-input slider-status-toggle" type="checkbox" role="switch" id="status-{{ $slider->id }}" data-id="{{ $slider->id }}" {{ $slider->is_on ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status-{{ $slider->id }}">
                                            <span id="badge-{{ $slider->id }}" class="badge {{ $slider->is_on ? 'bg-success' : 'bg-danger' }}">{{ $slider->is_on ? 'ON' : 'OFF' }}</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('baby-ai-home-slider.edit', $slider->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="action-btn delete-btn" data-id="{{ $slider->id }}" data-name="{{ $slider->source_name }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="deleteForm-{{ $slider->id }}" action="{{ route('baby-ai-home-slider.destroy', $slider->id) }}" method="POST" style="display:none;">
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
                                        <div class="empty-state-icon"><i class="bi bi-house-heart"></i></div>
                                        <h4>No Sliders Found</h4>
                                        <p class="text-muted">Add up to 3 home screen sliders — one for each source type.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sliders->total() > 0)
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing {{ $sliders->firstItem() }} to {{ $sliders->lastItem() }} of {{ $sliders->total() }} entries
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            @if ($sliders->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $sliders->appends(request()->except('page'))->previousPageUrl() }}">Previous</a>
                                </li>
                            @endif

                            @php
                                $currentPage = $sliders->currentPage();
                                $lastPage = $sliders->lastPage();
                            @endphp

                            @if ($lastPage <= 8)
                                @foreach ($sliders->getUrlRange(1, $lastPage) as $page => $url)
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
                                        <a class="page-link" href="{{ $sliders->url(1) . '&' . http_build_query(request()->except('page')) }}">1</a>
                                    </li>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif

                                @foreach ($sliders->getUrlRange($start, $end) as $page => $url)
                                    <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url . '&' . http_build_query(request()->except('page')) }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                @if ($end < $lastPage)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $sliders->url($lastPage) . '&' . http_build_query(request()->except('page')) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif
                            @endif

                            @if ($sliders->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $sliders->appends(request()->except('page'))->nextPageUrl() }}">Next</a>
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

    <!-- Indexing Modal -->
    <div class="modal fade" id="indexingModal" tabindex="-1" aria-labelledby="indexingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="indexingModalLabel">
                        <i class="bi bi-arrow-up-down me-2"></i>Slider Indexing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Drag and drop to reorder the sliders. The sequence here controls the API response order.
                    </div>
                    <div id="sortable-container" class="list-group" style="min-height: 200px;">
                        <div class="col-12 text-center text-muted py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading sliders...</p>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
                        title: 'Delete slider?',
                        text: 'This will delete the slider for "' + name + '" and its files.',
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

            document.getElementById('source_filter').addEventListener('change', function () {
                applyParams(p => {
                    if (this.value) p.set('source_type', this.value);
                    else p.delete('source_type');
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

            // Indexing modal
            let sortableInstance = null;
            const indexingModalEl = document.getElementById('indexingModal');

            if (indexingModalEl) {
                indexingModalEl.addEventListener('show.bs.modal', loadSlidersForIndexing);
            }

            function typeBadgeClass(t) {
                if (t === 'image') return 'type-badge image';
                if (t === 'video') return 'type-badge video';
                return 'type-badge dynamic_frame';
            }

            function loadSlidersForIndexing() {
                const container = document.getElementById('sortable-container');
                container.innerHTML = '<div class="col-12 text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';

                $.ajax({
                    url: "{{ route('baby-ai-home-slider.indexing') }}",
                    type: 'GET',
                    success: function (response) {
                        if (response.sliders && response.sliders.length > 0) {
                            container.innerHTML = '';
                            response.sliders.forEach((s, index) => {
                                const titleHtml = s.title ? ' &mdash; <span class="text-muted">' + $('<div>').text(s.title).html() + '</span>' : '';
                                const html = `
                                    <div class="list-group-item d-flex align-items-center justify-content-between sortable-item" data-id="${s.id}" style="cursor: move;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-grip-vertical me-2 text-muted fs-5"></i>
                                            <span class="fw-bold me-2">${index + 1}.</span>
                                            <span class="${typeBadgeClass(s.source_type)} me-2">${s.type_label}</span>
                                            <span><strong>${$('<div>').text(s.source_name || '').html()}</strong>${titleHtml}</span>
                                        </div>
                                    </div>`;
                                container.insertAdjacentHTML('beforeend', html);
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
                            container.innerHTML = '<div class="col-12 text-center text-muted py-5"><p>No sliders found</p></div>';
                        }
                    },
                    error: function () {
                        container.innerHTML = '<div class="col-12 text-center text-danger py-5"><p>Failed to load sliders.</p></div>';
                    }
                });
            }

            document.getElementById('saveOrderBtn').addEventListener('click', function () {
                const items = document.querySelectorAll('#sortable-container .list-group-item.sortable-item');
                if (!items.length) return;

                const orderData = [];
                items.forEach((item, index) => {
                    orderData.push({ id: item.getAttribute('data-id'), sort_order: index + 1 });
                });

                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Saving...';

                $.ajax({
                    url: "{{ route('baby-ai-home-slider.updateOrder') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", order: orderData },
                    success: function (response) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (response.success) {
                            bootstrap.Modal.getInstance(indexingModalEl).hide();
                            Swal.fire({ icon: 'success', title: 'Success', text: response.message }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function () {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save order.' });
                    }
                });
            });

            $(document).on('change', '.slider-status-toggle', function () {
                const id = $(this).data('id');
                const isOn = $(this).is(':checked');
                const badge = $('#badge-' + id);
                $.ajax({
                    url: "{{ route('baby-ai-home-slider.toggleStatus') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", id: id, is_on: isOn ? 1 : 0 },
                    success: function (res) {
                        if (res.success) {
                            if (isOn) badge.removeClass('bg-danger').addClass('bg-success').text('ON');
                            else badge.removeClass('bg-success').addClass('bg-danger').text('OFF');
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 2000 });
                        } else {
                            $('#status-' + id).prop('checked', !isOn);
                        }
                    },
                    error: function () {
                        $('#status-' + id).prop('checked', !isOn);
                    }
                });
            });
        });
    </script>
@endsection
