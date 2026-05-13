@extends('partials.layout')
@section('title', 'Sticker Management')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; position: relative; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-name { width: 30%; }
        .data-table .col-images { width: 55%; }
        .data-table .col-action { width: 15%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; flex-shrink: 0; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .action-cell { display: flex; justify-content: flex-end; gap: .5rem; flex-wrap: nowrap; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }

        .sticker-thumb { width: 90px; height: 90px; object-fit: contain; padding: 6px; border-radius: .5rem; border: 1px solid #e3e6f0; background: #f8f9fc; }
        .sticker-images-cell { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        .more-badge {
            display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            height: 34px; padding: 0 .9rem; border: none; border-radius: 999px; cursor: pointer;
            color: #fff; font-weight: 600; font-size: .82rem; letter-spacing: .2px; white-space: nowrap;
            background: linear-gradient(135deg, #7048e8 0%, #3aa9f1 100%);
            box-shadow: 0 4px 10px rgba(112, 72, 232, .25);
            transition: transform .18s ease, box-shadow .25s ease, filter .25s ease;
            position: relative; overflow: hidden;
        }
        .more-badge::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.22) 50%, transparent 70%);
            transform: translateX(-110%); transition: transform .7s ease;
            pointer-events: none;
        }
        .more-badge:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(112, 72, 232, .32); filter: brightness(1.06); color:#fff; }
        .more-badge:hover::before { transform: translateX(110%); }
        .more-badge:active { transform: translateY(0); }
        .more-badge .count-pill {
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.25); color: #fff;
            padding: .05rem .45rem; border-radius: 999px;
            font-size: .75rem; font-weight: 800;
            border: 1px solid rgba(255,255,255,.3);
        }
        .more-badge i { transition: transform .25s ease; font-size: .85rem; }
        .more-badge:hover i { transform: translateX(3px); }

        /* Gallery modal */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
        .gallery-grid img { width: 100%; height: 180px; object-fit: contain; background:#f8f9fc; border:1px solid #e3e6f0; border-radius: .5rem; padding: 8px; transition: transform .2s ease, box-shadow .2s ease; }
        .gallery-grid img:hover { transform: scale(1.03); box-shadow: 0 4px 16px rgba(0,0,0,.12); }

        /* Filters row */
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

        /* Pagination */
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

        .page-title { color: #7048e8; font-weight: 700; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><i class="bi bi-stickies-fill me-2"></i>Sticker Management</h1>
                <p class="text-muted">Manage stickers</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge"><i class="bi bi-collection"></i> Total: <span id="totalCount" class="ms-1">{{ $totalStickers }}</span> Stickers</span>
                <a href="{{ route('sticker.stickers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Sticker
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Search categories..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="ajax-container">
                @include('sticker.stickers.table')
            </div>
        </div>
    </div>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">
                        <i class="bi bi-emoji-smile-fill me-2"></i>Stickers — <span id="galleryCategoryName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="galleryGrid" class="gallery-grid"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
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

            // Delete confirmation (whole category of stickers)
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).attr('data-id');
                const name = $(this).attr('data-name');
                Swal.fire({
                    title: 'Delete all stickers?',
                    text: 'This will delete all stickers under "' + name + '".',
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

            function loadStickers(page) {
                const $card = $('.main-card');
                $card.append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');

                $.ajax({
                    url: "{{ route('sticker.stickers.index') }}",
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
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected server response.' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load stickers (' + xhr.status + ').' });
                    },
                    complete: function () {
                        $card.find('.loading-overlay').remove();
                    }
                });
            }

            $('#per_page').on('change', function () { loadStickers(1); });
            $('#category_filter').on('change', function () { loadStickers(1); });

            let searchTimer = null;
            $('#searchInput').on('keyup', function (e) {
                clearTimeout(searchTimer);
                if (e.key === 'Enter') { loadStickers(1); return; }
                searchTimer = setTimeout(() => loadStickers(1), 500);
            });
            $('#clearSearch').on('click', function () {
                $('#searchInput').val('');
                loadStickers(1);
            });

            $(document).on('click', '#ajax-container .pagination a.page-link', function (e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadStickers(page);
            });

            // Open gallery modal with all stickers
            const galleryModalEl = document.getElementById('galleryModal');
            const galleryModal = galleryModalEl ? new bootstrap.Modal(galleryModalEl) : null;

            $(document).on('click', '#ajax-container .open-gallery-btn', function () {
                const name = $(this).attr('data-name') || '';
                let urls = [];
                try {
                    urls = JSON.parse($(this).attr('data-stickers') || '[]');
                } catch (e) {
                    urls = [];
                }

                $('#galleryCategoryName').text(name);
                const $grid = $('#galleryGrid').empty();
                if (urls.length === 0) {
                    $grid.html('<div class="text-center text-muted py-4">No stickers found.</div>');
                } else {
                    urls.forEach(u => {
                        const safeUrl = $('<div>').text(u).html();
                        $grid.append('<img src="' + safeUrl + '" alt="">');
                    });
                }
                if (galleryModal) galleryModal.show();
            });
        });
    </script>
@endsection
