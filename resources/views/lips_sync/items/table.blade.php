<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-thumb">Thumbnail</th>
                <th class="col-title">Title</th>
                <th class="col-category">Category</th>
                <th class="col-song">Song</th>
                <th class="col-video">Video</th>
                <th class="col-action text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        @if ($item->video_thumbnail && $item->category)
                            <img loading="lazy" decoding="async" src="{{ asset('upload/lips_sync/' . $item->category?->category_name . '/thumbnail/' . $item->video_thumbnail) }}" class="item-thumb" alt="">
                        @else
                            <div class="item-thumb bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td><strong>{{ $item->title }}</strong></td>
                    <td>{{ $item->category?->category_name ?? 'N/A' }}</td>
                    <td>
                        @if ($item->song && $item->category)
                            <button type="button" class="play-btn play-song-btn"
                                data-src="{{ asset('upload/lips_sync/' . $item->category?->category_name . '/song/' . $item->song) }}"
                                title="Play song">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($item->video && $item->category)
                            <video src="{{ asset('upload/lips_sync/' . $item->category?->category_name . '/video/' . $item->video) }}" class="video-thumb" muted></video>
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
                            <p class="text-muted">No items match your current search criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($items->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($items->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $items->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $items->currentPage();
                    $lastPage = $items->lastPage();
                @endphp

                @if ($lastPage <= 8)
                    @for ($p = 1; $p <= $lastPage; $p++)
                        <li class="page-item {{ $p == $currentPage ? 'active' : '' }}">
                            <a class="page-link" href="#" data-page="{{ $p }}">{{ $p }}</a>
                        </li>
                    @endfor
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
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif

                    @for ($p = $start; $p <= $end; $p++)
                        <li class="page-item {{ $p == $currentPage ? 'active' : '' }}">
                            <a class="page-link" href="#" data-page="{{ $p }}">{{ $p }}</a>
                        </li>
                    @endfor

                    @if ($end < $lastPage)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="{{ $lastPage }}">{{ $lastPage }}</a>
                        </li>
                    @endif
                @endif

                @if ($items->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $items->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
