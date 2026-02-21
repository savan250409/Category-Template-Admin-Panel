@extends('partials.layout')
@section('title', 'AI Baby Video Management')
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

        .video-preview {
            max-width: 150px;
            max-height: 100px;
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"><i class="bi bi-camera-video me-2"></i>AI Baby Video Management</h1>
                <p class="page-subtitle">Manage all videos in the system</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge"><i class="bi bi-collection"></i> Total: <span
                        id="totalCount">{{ $videos->total() }}</span> Videos</span>
                <a href="{{ route('ai-baby-video.videos.create') }}" class="btn btn-primary"><i
                        class="bi bi-plus-lg me-2"></i>Add Video</a>
            </div>
        </div>




        <div class="main-card">
            <div class="search-container gap-2">
                <select id="categoryFilter" class="form-select" style="width: 200px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group" style="width: 350px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by title or prompt..."
                        value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <div id="videosTableContainer">
                @include('ai_baby_video.videos.table')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let searchTimeout = null;

        $(document).ready(function () {
            @if(session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "Validation Error",
                    text: "{{ $errors->first() }}",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                });
            @endif

            // Search input handler
            $(document).on('keyup', '#searchInput', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadVideos(1);
                }, 500);
            });

            // Category filter handler
            $(document).on('change', '#categoryFilter', function () {
                loadVideos(1);
            });

            // Clear search handler
            $(document).on('click', '#clearSearch', function () {
                $('#searchInput').val('');
                $('#categoryFilter').val('');
                loadVideos(1);
            });

            // Handle AJAX pagination
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                loadVideos(page);
            });
        });

        function loadVideos(page) {
            const search = $('#searchInput').val();
            const category_id = $('#categoryFilter').val();

            $.ajax({
                url: "{{ route('ai-baby-video.videos.index') }}",
                type: 'GET',
                data: {
                    page: page,
                    search: search,
                    category_id: category_id
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (res) {
                    $('#videosTableContainer').html(res.html);
                    $('#totalCount').text(res.total);

                    // Re-initialize tooltips
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
                },
                error: function (xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
        }

        function confirmDelete(button) {
            const id = $(button).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: `Are you sure you want to delete this video?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm-' + id).submit();
                }
            });
        }
    </script>
@endsection