<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-name">Category Name</th>
                <th class="col-images">Sticker Images</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stickerCategories as $category)
                @php
                    $stickers    = is_array($category->stickers) ? $category->stickers : [];
                    $totalCount  = count($stickers);
                    $preview     = array_slice($stickers, 0, 3);
                    $extraCount  = max(0, $totalCount - count($preview));
                    $stickerUrls = array_map(function ($filename) use ($category) {
                        return asset('upload/sticker/' . rawurlencode($category->category_name) . '/stickers/' . rawurlencode($filename));
                    }, $stickers);
                @endphp
                <tr>
                    <td><strong>{{ $category->category_name }}</strong></td>
                    <td>
                        <div class="sticker-images-cell">
                            @if ($totalCount > 0)
                                @foreach ($preview as $filename)
                                    <img loading="lazy" decoding="async" src="{{ asset('upload/sticker/' . rawurlencode($category->category_name) . '/stickers/' . rawurlencode($filename)) }}" class="sticker-thumb" alt="">
                                @endforeach
                                @if ($extraCount > 0)
                                    <button type="button" class="more-badge open-gallery-btn"
                                        data-name="{{ $category->category_name }}"
                                        data-stickers='@json(array_values($stickerUrls))'
                                        title="View all stickers">
                                        <span class="count-pill">+{{ $extraCount }}</span>
                                        <span>View More</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                @endif
                            @else
                                <span class="text-muted">No stickers yet</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="action-cell">
                            <a href="{{ route('sticker.stickers.edit', $category->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $category->id }}" action="{{ route('sticker.stickers.destroy', $category->id) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-emoji-frown"></i></div>
                            <h4>No stickers found</h4>
                            <p class="text-muted">Add your first sticker to get started.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($stickerCategories->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $stickerCategories->firstItem() }} to {{ $stickerCategories->lastItem() }} of {{ $stickerCategories->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($stickerCategories->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $stickerCategories->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $stickerCategories->currentPage();
                    $lastPage = $stickerCategories->lastPage();
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

                @if ($stickerCategories->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $stickerCategories->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
