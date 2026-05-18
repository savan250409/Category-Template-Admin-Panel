@extends('partials.layout')
@section('title', 'Filter Category Management')
@section('container')
    <style>
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; position: relative; }
        .table-responsive { margin-left: 0 !important; margin-right: 0 !important; padding-left: 0 !important; padding-right: 0 !important; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; word-wrap: break-word; }
        .data-table .col-name { width: 34%; }
        .data-table .col-image { width: 20%; }
        .data-table .col-status { width: 20%; }
        .data-table .col-action { width: 26%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; border: none; flex-shrink: 0; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .action-cell { display: flex; justify-content: flex-end; gap: .5rem; flex-wrap: nowrap; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .filter-thumb { width: 56px; height: 56px; object-fit: cover; padding: 0; border-radius: 50%; border: 1px solid #e3e6f0; background:#f8f9fc; }

        .filters-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filters-left { display: flex; align-items: center; gap: .75rem; }
        .per-page-select { border: 1px solid #d1d3e2; border-radius: .35rem; padding: .5rem .75rem; width: 88px; background-color: #fff; }
        .search-container .input-group { width: 350px; }
        .search-container .form-control { border: 1px solid #d1d3e2; border-radius: .35rem 0 0 .35rem; padding: .5rem 1rem; }

        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e3e6f0; flex-wrap: nowrap; gap: .75rem; }
        .pagination-info { color: #6e707e; font-size: .9rem; white-space: nowrap; flex-shrink: 0; }
        .pagination { display: flex; flex-wrap: nowrap; gap: 4px; padding: 0; margin: 0; list-style: none; overflow-x: auto; max-width: 100%; }
        .pagination .page-item .page-link { color: #4e73df; padding: .375rem .75rem; border: 1px solid #dddfeb; font-size: .9rem; cursor: pointer; background-color: #fff; border-radius: .25rem; text-decoration: none; display: inline-block; line-height: 1.5; min-width: 36px; text-align: center; }
        .pagination .page-item.active .page-link { background-color: #4e73df; border-color: #4e73df; color: #fff; }
        .pagination .page-item.disabled .page-link { color: #b7b9cc; pointer-events: none; background-color: #f8f9fc; }
        .loading-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, .7); display: flex; justify-content: center; align-items: center; z-index: 10; border-radius: .35rem; }

        .check-cell { display: inline-flex; align-items: center; gap: .5rem; padding: .3rem .6rem; background:#f3eaff; border-radius: .35rem; }
        .check-cell .form-check-input { width: 1.2rem; height: 1.2rem; margin: 0; cursor: pointer; border-radius: .25rem; border: 1.5px solid #7048e8; }
        .check-cell .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
        .check-cell .check-label { font-size: .85rem; font-weight: 600; color: #4a4a4a; user-select: none; }

        .page-hero {
            position: relative; overflow: hidden; border-radius: 18px;
            padding: 1.5rem 1.75rem; margin-bottom: 1.75rem; color: #fff;
            background: linear-gradient(135deg, #7048e8 0%, #c06aef 50%, #3aa9f1 100%);
            box-shadow: 0 12px 28px rgba(112, 72, 232, .25);
        }
        .page-hero::before, .page-hero::after { content: ''; position: absolute; border-radius: 50%; pointer-events: none; background: rgba(255,255,255,.10); }
        .page-hero::before { width: 220px; height: 220px; top: -70px; right: -70px; }
        .page-hero::after  { width: 140px; height: 140px; bottom: -50px; left: -50px; background: rgba(255,255,255,.07); }
        .hero-inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .hero-left { display: flex; align-items: center; gap: 1rem; }
        .hero-icon { width: 56px; height: 56px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.22); border: 1px solid rgba(255,255,255,.25); color: #fff; font-size: 1.6rem; }
        .hero-title { margin: 0; font-size: 1.55rem; font-weight: 800; color:#fff; }
        .hero-subtitle { margin: .15rem 0 0; opacity: .9; font-size: .9rem; }
        .hero-actions { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
        .hero-count { display: inline-flex; align-items: center; gap: .4rem; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.22); color: #fff; padding: .5rem .9rem; border-radius: 999px; font-weight: 700; font-size: .85rem; }
        .hero-count .badge-num { display: inline-flex; align-items: center; justify-content: center; background: #fff; color: #7048e8; padding: .05rem .55rem; border-radius: 999px; font-weight: 800; font-size: .8rem; }
        .hero-btn { display: inline-flex; align-items: center; gap: .45rem; padding: .65rem 1.15rem; border-radius: 999px; font-weight: 700; font-size: .9rem; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
        .hero-btn-add { background: #fff; color: #7048e8; box-shadow: 0 8px 18px rgba(0,0,0,.18); }
        .hero-btn-add:hover { color: #5f37d9; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-hero">
            <div class="hero-inner">
                <div class="hero-left">
                    <span class="hero-icon"><i class="bi bi-funnel"></i></span>
                    <div>
                        <h1 class="hero-title">Filter Category Management</h1>
                        <p class="hero-subtitle">Manage filter categories</p>
                    </div>
                </div>
                <div class="hero-actions">
                    <span class="hero-count"><i class="bi bi-collection"></i> Total <span class="badge-num" id="totalCount">{{ $categories->total() }}</span></span>
                    <a href="{{ route('filter.categories.create') }}" class="hero-btn hero-btn-add">
                        <i class="bi bi-plus-lg"></i> Add Category
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="filters-row">
                <div class="filters-left">
                    <span>Show</span>
                    <select id="per_page" class="per-page-select">
                        @foreach ([10, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
                <div class="search-container">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search categories..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch"><i class="bi bi-x"></i></button>
                    </div>
                </div>
            </div>

            <div id="ajax-container">
                @include('filter.categories.table')
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

            $(document).on('click', '.delete-btn', function () {
                const id = $(this).attr('data-id');
                const name = $(this).attr('data-name');
                Swal.fire({
                    title: 'Delete category?',
                    text: 'This will delete "' + name + '" and all its filters.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#bb2d3b', confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('deleteForm-' + id).submit();
                });
            });

            function load(page) {
                const $card = $('.main-card');
                $card.append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>');

                $.ajax({
                    url: "{{ route('filter.categories.index') }}",
                    type: 'GET',
                    data: { page: page || 1, per_page: $('#per_page').val(), search: $('#searchInput').val() },
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    dataType: 'json',
                    success: function (res) {
                        if (res && typeof res.html === 'string') {
                            $('#ajax-container').html(res.html);
                            $('#totalCount').text(res.total ?? 0);
                        }
                    },
                    complete: function () { $card.find('.loading-overlay').remove(); }
                });
            }

            $('#per_page').on('change', function () { load(1); });
            let searchTimer = null;
            $('#searchInput').on('keyup', function (e) {
                clearTimeout(searchTimer);
                if (e.key === 'Enter') { load(1); return; }
                searchTimer = setTimeout(() => load(1), 500);
            });
            $('#clearSearch').on('click', function () { $('#searchInput').val(''); load(1); });

            $(document).on('click', '#ajax-container .pagination a.page-link', function (e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) load(page);
            });

            $(document).on('change', '.toggle-status', function () {
                const id = $(this).data('id');
                const status = $(this).is(':checked') ? 1 : 0;
                $.ajax({
                    url: "{{ route('filter.categories.toggleStatus') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", id: id, status: status },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1500 });
                        }
                    }
                });
            });
        });
    </script>
@endsection
