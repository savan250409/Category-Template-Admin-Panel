<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-category">Category</th>
                <th class="col-thumb">Thumbnail</th>
                <th class="col-title">Title</th>
                <th class="col-prompt">AI Prompt</th>
                <th class="col-name-change">Name Change</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($videos as $video)
                <tr id="row-{{ $video->id }}">
                    <td><strong>{{ $video->category?->category_name ?? 'N/A' }}</strong></td>
                    <td>
                        @if ($video->video_thumbnail)
                            @if (\Illuminate\Support\Str::startsWith($video->video_thumbnail, 'upload/'))
                                <img loading="lazy" decoding="async" src="{{ asset($video->video_thumbnail) }}" class="video-thumb" alt="">
                            @else
                                <img loading="lazy" decoding="async" src="{{ asset('upload/AI Baby Video/' . ($video->category?->category_name ?? 'Unknown') . '/video thumbanail/' . $video->video_thumbnail) }}" class="video-thumb" alt="">
                            @endif
                        @else
                            <div class="video-thumb bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>{{ $video->video_title }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($video->ai_prompt, 50) }}</td>
                    <td>
                        @if ($video->name_change)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('ai-baby-video.videos.edit', $video->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $video->id }}" data-title="{{ $video->video_title }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $video->id }}" action="{{ route('ai-baby-video.videos.destroy', $video->id) }}" method="POST" style="display:none;">
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
                            <div class="empty-state-icon"><i class="bi bi-camera-video"></i></div>
                            <h4>No Videos Found</h4>
                            <p class="text-muted">No videos match your current search criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($videos->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $videos->firstItem() }} to {{ $videos->lastItem() }} of {{ $videos->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($videos->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $videos->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $videos->currentPage();
                    $lastPage = $videos->lastPage();
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

                @if ($videos->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $videos->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
