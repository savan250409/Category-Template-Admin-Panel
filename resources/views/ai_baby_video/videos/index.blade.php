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
        <div class="search-container">
            <div class="input-group" style="width: 350px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by prompt or category..."
                    value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        <div id="videosTableContainer">
            @if ($videos->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-camera-video"></i>
                    </div>
                    <h4 class="empty-state-title">No Videos Found</h4>
                    <p class="empty-state-text">Get started by adding your first video</p>
                </div>
            @else
            @section('table')
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Video</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>AI Prompt</th>
                            <th>Name Change</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($videos as $video)
                            <tr id="row-{{ $video->id }}">
                                <td><strong>{{ $video->category->category_name ?? 'N/A' }}</strong></td>
                                <td>
                                    @if ($video->video_path)
                                        @if(Str::startsWith($video->video_path, 'upload/'))
                                            <video src="{{ asset($video->video_path) }}" class="video-preview" controls></video>
                                        @else
                                            <video
                                                src="{{ asset('upload/AI Baby Video/' . $video->category->category_name . '/video/' . $video->video_path) }}"
                                                class="video-preview" controls></video>
                                        @endif
                                    @else
                                        <div class="text-muted">No video</div>
                                    @endif
                                </td>
                                <td>
                                    @if($video->video_thumbnail)
                                        @if(Str::startsWith($video->video_thumbnail, 'upload/'))
                                            <img src="{{ asset($video->video_thumbnail) }}" alt="Thumbnail" width="100">
                                        @else
                                            <img src="{{ asset('upload/AI Baby Video/' . $video->category->category_name . '/video thumbanail/' . $video->video_thumbnail) }}"
                                                alt="Thumbnail" width="100">
                                        @endif
                                    @else
                                        <span class="text-muted">No Thumbnail</span>
                                    @endif
                                </td>
                                <td>{{ $video->video_title }}</td>
                                <td>{{ Str::limit($video->ai_prompt, 50) }}</td>
                                <td>
                                    @if($video->name_change)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('ai-baby-video.videos.edit', $video->id) }}"
                                            class="action-btn edit-btn" data-bs-toggle="tooltip" title="Edit Video">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form id="deleteForm-{{ $video->id }}"
                                            action="{{ route('ai-baby-video.videos.destroy', $video->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="action-btn delete-btn" data-id="{{ $video->id }}"
                                                data-title="Video {{ $video->id }}" onclick="confirmDelete(this)">
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
                                    Showing {{ $videos->firstItem() }} to {{ $videos->lastItem() }} of {{ $videos->total() }}
                                    entries
                                </div>
                                <!-- Simple Pagination Links -->
                                {{ $videos->links() }}
                            </div>
                            @show
                            @endif
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                <script>
                    let searchTimeout = null;

                    document.addEventListener('DOMContentLoaded', function () {
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

                                                        const searchInput = document.getElementById('searchInput');
                        if (searchInput) {
                            searchInput.addEventListener('keyup', function () {
                                clearTimeout(searchTimeout);
                                searchTimeout = setTimeout(() => {
                                    loadVideos(1, this.value);
                                }, 500);
                            });
                        }

                        document.getElementById('clearSearch').addEventListener('click', function () {
                            searchInput.value = '';
                            loadVideos(1, '');
                        });

                        // Handle AJAX pagination
                        $(document).on('click', '.pagination a', function (e) {
                            e.preventDefault();
                            const url = new URL($(this).attr('href'));
                            const searchParam = document.getElementById('searchInput').value;
                            loadVideos(url.searchParams.get('page') || 1, searchParam);
                        });
                    });

                    function loadVideos(page, search = '') {
                        $.ajax({
                            url: "{{ route('ai-baby-video.videos.index') }}",
                            type: 'GET',
                            data: {
                                page: page,
                                search: search
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            success: function (res) {
                                $('#videosTableContainer').html(res.table + res.pagination);
                                $('#totalCount').text(res.total);
                            },
                            error: function (xhr) {
                                console.error('AJAX Error:', xhr.responseText);
                            }
                        });
                    }

                    function confirmDelete(button) {
                        const title = button.getAttribute('data-title');
                        const id = button.getAttribute('data-id');

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