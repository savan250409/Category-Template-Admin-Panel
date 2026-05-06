@extends('partials.layout')
@section('title', 'Lips Sync Management')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .item-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: .35rem; }
        .video-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: .35rem; }
        .play-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: 1px solid #0d6efd; color: #0d6efd; border-radius: 50%; background: #fff; cursor: pointer; transition: all .2s; }
        .play-btn:hover { background: #0d6efd; color: #fff; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary"><i class="bi bi-music-note-beamed me-2"></i>Lips Sync Management</h1>
                <p class="text-muted">Manage all Lips Sync items in the system</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge"><i class="bi bi-collection"></i> Total:<span class="ms-1">{{ $items->total() }}</span>Items</span>
                <a href="{{ route('lips-sync.items.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Lips Sync
                </a>
            </div>
        </div>

        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span>Show</span>
                    <select id="per_page" class="form-select form-select-sm d-inline-block mx-1" style="width: 80px;">
                        @foreach ([10, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
                <div class="d-flex gap-2">
                    <select id="category_filter" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="title_search" class="form-control form-control-sm" placeholder="Search Title..." value="{{ $search }}" style="width: 240px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Song</th>
                            <th>Video</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    @if ($item->video_thumbnail && $item->category)
                                        <img src="{{ asset('upload/lips_sync/' . $item->category->category_name . '/thumbnail/' . $item->video_thumbnail) }}" class="item-thumb" alt="">
                                    @else
                                        <div class="item-thumb bg-light d-flex align-items-center justify-content-center">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td><strong>{{ $item->title }}</strong></td>
                                <td>{{ $item->category->category_name ?? 'N/A' }}</td>
                                <td>
                                    @if ($item->song && $item->category)
                                        <button type="button" class="play-btn play-song-btn"
                                            data-src="{{ asset('upload/lips_sync/' . $item->category->category_name . '/song/' . $item->song) }}"
                                            title="Play song">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->video && $item->category)
                                        <video src="{{ asset('upload/lips_sync/' . $item->category->category_name . '/video/' . $item->video) }}" class="video-thumb" muted></video>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('lips-sync.items.edit', $item->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="action-btn delete-btn" data-id="{{ $item->id }}" data-name="{{ $item->title }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="deleteForm-{{ $item->id }}" action="{{ route('lips-sync.items.destroy', $item->id) }}" method="POST" style="display:none;">
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
                                        <i class="bi bi-music-note-list" style="font-size: 3rem; color: #b7b9cc;"></i>
                                        <h4 class="mt-2">No Lips Sync items found</h4>
                                        <p class="text-muted">Add your first item to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
                </div>
                <div>{{ $items->appends(request()->except('page'))->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Hidden audio for play -->
    <audio id="songPlayer" preload="none"></audio>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 4000, timerProgressBar: true });
            @endif
            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            // Delete confirm
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Delete item?',
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
            });

            // Per-page / filter / search
            const params = new URLSearchParams(window.location.search);
            document.getElementById('per_page').addEventListener('change', function () {
                params.set('per_page', this.value); params.delete('page');
                window.location.search = params.toString();
            });
            document.getElementById('category_filter').addEventListener('change', function () {
                if (this.value) params.set('category_id', this.value); else params.delete('category_id');
                params.delete('page');
                window.location.search = params.toString();
            });
            let searchTimer = null;
            document.getElementById('title_search').addEventListener('input', function () {
                clearTimeout(searchTimer);
                const val = this.value;
                searchTimer = setTimeout(() => {
                    if (val) params.set('search', val); else params.delete('search');
                    params.delete('page');
                    window.location.search = params.toString();
                }, 500);
            });

            // Song player
            const player = document.getElementById('songPlayer');
            let currentBtn = null;
            document.querySelectorAll('.play-song-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const src = this.getAttribute('data-src');
                    if (currentBtn === this && !player.paused) {
                        player.pause();
                        this.querySelector('i').className = 'bi bi-play-fill';
                        currentBtn = null;
                        return;
                    }
                    if (currentBtn && currentBtn !== this) {
                        currentBtn.querySelector('i').className = 'bi bi-play-fill';
                    }
                    player.src = src;
                    player.play();
                    this.querySelector('i').className = 'bi bi-pause-fill';
                    currentBtn = this;
                });
            });
            player.addEventListener('ended', function () {
                if (currentBtn) {
                    currentBtn.querySelector('i').className = 'bi bi-play-fill';
                    currentBtn = null;
                }
            });
        });
    </script>
@endsection
