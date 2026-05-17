@extends('partials.layout')
@section('title', 'Doodle Management')
@section('container')
    <style>
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; position: relative; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-name { width: 32%; }
        .data-table .col-image { width: 22%; }
        .data-table .col-type { width: 14%; }
        .data-table .col-dtype { width: 14%; }
        .data-table .col-action { width: 18%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; flex-shrink: 0; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .action-cell { display: flex; justify-content: flex-end; gap: .5rem; flex-wrap: nowrap; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .doodle-thumb { width: 56px; height: 56px; object-fit: contain; padding: 6px; border-radius: .5rem; border: 1px solid #e3e6f0; background:#f8f9fc; }

        .type-pill {
            display: inline-block; padding: .25rem .65rem; border-radius: 999px;
            font-size: .75rem; font-weight: 700; letter-spacing: .2px; text-transform: uppercase;
        }
        .type-pill.image { background: #e7f5ff; color: #1c7ed6; }
        .type-pill.line  { background: #fff4e6; color: #e8590c; }

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

        .check-cell { display: inline-flex; align-items: center; gap: .5rem; padding: .3rem .6rem; background:#f3eaff; border-radius: .35rem; }
        .check-cell .form-check-input { width: 1.2rem; height: 1.2rem; margin: 0; cursor: pointer; border-radius: .25rem; border: 1.5px solid #7048e8; }
        .check-cell .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
        .check-cell .form-check-input:focus { box-shadow: 0 0 0 .2rem rgba(112, 72, 232, .2); }
        .check-cell .check-label { font-size: .85rem; font-weight: 600; color: #4a4a4a; user-select: none; }

        /* Hero header */
        .page-hero {
            position: relative; overflow: hidden;
            border-radius: 18px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.75rem;
            color: #fff;
            background: linear-gradient(135deg, #7048e8 0%, #c06aef 50%, #3aa9f1 100%);
            box-shadow: 0 12px 28px rgba(112, 72, 232, .25);
        }
        .page-hero::before, .page-hero::after {
            content: ''; position: absolute; border-radius: 50%; pointer-events: none;
            background: rgba(255,255,255,.10);
        }
        .page-hero::before { width: 220px; height: 220px; top: -70px; right: -70px; }
        .page-hero::after  { width: 140px; height: 140px; bottom: -50px; left: -50px; background: rgba(255,255,255,.07); }

        .hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .hero-left  { display: flex; align-items: center; gap: 1rem; }
        .hero-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.22);
            border: 1px solid rgba(255,255,255,.25);
            color: #fff; font-size: 1.6rem;
            box-shadow: 0 6px 14px rgba(0,0,0,.12);
            backdrop-filter: blur(4px);
        }
        .hero-title  { margin: 0; font-size: 1.55rem; font-weight: 800; letter-spacing: -.01em; text-shadow: 0 2px 6px rgba(0,0,0,.12); }
        .hero-subtitle { margin: .15rem 0 0; opacity: .9; font-size: .9rem; }

        .hero-actions { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
        .hero-count {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.22);
            color: #fff; padding: .5rem .9rem; border-radius: 999px;
            font-weight: 700; font-size: .85rem;
            backdrop-filter: blur(4px);
        }
        .hero-count .badge-num {
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff; color: #7048e8; padding: .05rem .55rem; border-radius: 999px;
            font-weight: 800; font-size: .8rem;
        }
        .hero-btn {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .65rem 1.15rem; border-radius: 999px;
            font-weight: 700; font-size: .9rem; line-height: 1;
            border: 1px solid transparent;
            transition: transform .18s ease, box-shadow .25s ease, background .2s ease, color .2s ease, filter .2s ease;
            cursor: pointer; white-space: nowrap; text-decoration: none;
            position: relative; overflow: hidden;
        }
        .hero-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%);
            transform: translateX(-110%); transition: transform .7s ease;
            pointer-events: none;
        }
        .hero-btn:hover:not(:disabled)::before { transform: translateX(110%); }
        .hero-btn:disabled { opacity: .55; cursor: not-allowed; }
        .hero-btn-reorder {
            background: linear-gradient(135deg, #ffb84a 0%, #ff7a59 100%);
            color: #fff;
            box-shadow: 0 8px 18px rgba(255, 122, 89, .35), inset 0 0 0 1px rgba(255,255,255,.18);
        }
        .hero-btn-reorder:hover:not(:disabled) { color: #fff; transform: translateY(-2px); filter: brightness(1.05); box-shadow: 0 12px 24px rgba(255, 122, 89, .45), inset 0 0 0 1px rgba(255,255,255,.22); }
        .hero-btn-add {
            background: #fff; color: #7048e8;
            box-shadow: 0 8px 18px rgba(0,0,0,.18), 0 0 0 0 rgba(255,255,255,.55);
            animation: hero-pulse 2.2s ease-in-out infinite;
        }
        .hero-btn-add:hover { color: #5f37d9; transform: translateY(-2px); box-shadow: 0 14px 26px rgba(0,0,0,.22), 0 0 0 6px rgba(255,255,255,.18); animation: none; }
        @keyframes hero-pulse {
            0%   { box-shadow: 0 8px 18px rgba(0,0,0,.18), 0 0 0 0   rgba(255,255,255,.55); }
            70%  { box-shadow: 0 8px 18px rgba(0,0,0,.18), 0 0 0 12px rgba(255,255,255,0);   }
            100% { box-shadow: 0 8px 18px rgba(0,0,0,.18), 0 0 0 0   rgba(255,255,255,0);    }
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-hero">
            <div class="hero-inner">
                <div class="hero-left">
                    <span class="hero-icon"><i class="bi bi-stars"></i></span>
                    <div>
                        <h1 class="hero-title">Doodle Management</h1>
                        <p class="hero-subtitle">Manage doodles available in your app</p>
                    </div>
                </div>
                <div class="hero-actions">
                    <span class="hero-count"><i class="bi bi-collection"></i> Total <span class="badge-num" id="totalCount">{{ $doodles->total() }}</span></span>
                    <button type="button" class="hero-btn hero-btn-reorder" data-bs-toggle="modal" data-bs-target="#indexingModal" @if($doodles->total() === 0) disabled title="No doodles to reorder" @endif>
                        <i class="bi bi-arrow-down-up"></i> Reorder
                    </button>
                    <a href="{{ route('doodles.create') }}" class="hero-btn hero-btn-add">
                        <i class="bi bi-plus-lg"></i> Add Doodle
                    </a>
                </div>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Search doodles..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="ajax-container">
                @include('doodle.table')
            </div>
        </div>
    </div>

    <!-- Reorder Modal -->
    <div class="modal fade" id="indexingModal" tabindex="-1" aria-labelledby="indexingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="indexingModalLabel">
                        <i class="bi bi-arrow-up-down me-2"></i>Reorder Doodles
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Drag and drop to reorder doodles. The sequence here controls the API response order.
                    </div>
                    <div id="sortable-container" class="list-group" style="min-height: 200px;">
                        <div class="col-12 text-center text-muted py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading doodles...</p>
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

            $(document).on('click', '.delete-btn', function () {
                const id = $(this).attr('data-id');
                const name = $(this).attr('data-name');
                Swal.fire({
                    title: 'Delete doodle?',
                    text: 'This will delete "' + name + '" and its files.',
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

            function loadDoodles(page) {
                const $card = $('.main-card');
                $card.append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');

                $.ajax({
                    url: "{{ route('doodles.index') }}",
                    type: 'GET',
                    data: {
                        page: page || 1,
                        per_page: $('#per_page').val(),
                        search: $('#searchInput').val(),
                    },
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    dataType: 'json',
                    success: function (res) {
                        if (res && typeof res.html === 'string') {
                            $('#ajax-container').html(res.html);
                            $('#totalCount').text(res.total ?? 0);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected server response.' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load doodles (' + xhr.status + ').' });
                    },
                    complete: function () {
                        $card.find('.loading-overlay').remove();
                    }
                });
            }

            $('#per_page').on('change', function () { loadDoodles(1); });

            let searchTimer = null;
            $('#searchInput').on('keyup', function (e) {
                clearTimeout(searchTimer);
                if (e.key === 'Enter') { loadDoodles(1); return; }
                searchTimer = setTimeout(() => loadDoodles(1), 500);
            });
            $('#clearSearch').on('click', function () {
                $('#searchInput').val('');
                loadDoodles(1);
            });

            $(document).on('click', '#ajax-container .pagination a.page-link', function (e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadDoodles(page);
            });

            $(document).on('change', '.toggle-premium', function () {
                const id = $(this).data('id');
                const isPremium = $(this).is(':checked') ? 1 : 0;
                $.ajax({
                    url: "{{ route('doodles.togglePremium') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", id: id, is_premium: isPremium },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1500 });
                        }
                    }
                });
            });

            // Reorder modal
            let sortableInstance = null;
            const indexingModalEl = document.getElementById('indexingModal');

            if (indexingModalEl) {
                indexingModalEl.addEventListener('show.bs.modal', loadDoodlesForIndexing);
            }

            function loadDoodlesForIndexing() {
                const container = document.getElementById('sortable-container');
                container.innerHTML = '<div class="col-12 text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';

                $.ajax({
                    url: "{{ route('doodles.indexing') }}",
                    type: 'GET',
                    success: function (response) {
                        if (response.doodles && response.doodles.length > 0) {
                            container.innerHTML = '';
                            response.doodles.forEach((d, index) => {
                                const thumb = d.image_url
                                    ? '<img src="' + d.image_url + '" style="width:40px;height:40px;object-fit:contain;padding:4px;border:1px solid #e3e6f0;border-radius:.25rem;background:#f8f9fc;" class="me-2" alt="">'
                                    : '<span class="me-2 text-muted"><i class="bi bi-image"></i></span>';
                                const html = `
                                    <div class="list-group-item d-flex align-items-center justify-content-between sortable-item" data-id="${d.id}" style="cursor: move;">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-grip-vertical me-2 text-muted fs-5"></i>
                                            <span class="fw-bold me-2">${index + 1}.</span>
                                            ${thumb}
                                            <span><strong>${$('<div>').text(d.name || '').html()}</strong></span>
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
                            container.innerHTML = '<div class="col-12 text-center text-muted py-5"><p>No doodles found</p></div>';
                        }
                    },
                    error: function () {
                        container.innerHTML = '<div class="col-12 text-center text-danger py-5"><p>Failed to load doodles.</p></div>';
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
                    url: "{{ route('doodles.updateOrder') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", order: orderData },
                    success: function (response) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (response.success) {
                            bootstrap.Modal.getInstance(indexingModalEl).hide();
                            Swal.fire({ icon: 'success', title: 'Success', text: response.message }).then(() => {
                                loadDoodles(1);
                            });
                        }
                    },
                    error: function () {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save order.' });
                    }
                });
            });
        });
    </script>
@endsection
