<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-name">Category Name</th>
                <th class="col-zip">Zip File</th>
                <th class="col-input">Input Count</th>
                <th class="col-thumb">Thumbnail</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($frames as $frame)
                <tr>
                    <td><strong>{{ $frame->category?->category_name ?? 'N/A' }}</strong></td>
                    <td>
                        @if ($frame->zip_file && $frame->category)
                            <a class="zip-link" href="{{ asset('upload/dynamic_photo_frame/' . $frame->category?->category_name . '/zip/' . $frame->zip_file) }}" target="_blank" title="{{ $frame->zip_file }}">
                                <i class="bi bi-file-earmark-zip me-1"></i>{{ \Illuminate\Support\Str::limit($frame->zip_file, 30) }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $frame->input_count }}</td>
                    <td>
                        @if ($frame->thumbnail && $frame->category)
                            <img loading="lazy" decoding="async" src="{{ asset('upload/dynamic_photo_frame/' . $frame->category?->category_name . '/thumbnail/' . $frame->thumbnail) }}" class="frame-thumb" alt="">
                        @else
                            <div class="frame-thumb bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dynamic-photo-frame.frames.edit', $frame->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $frame->id }}" data-name="{{ $frame->category?->category_name ?? 'this frame' }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $frame->id }}" action="{{ route('dynamic-photo-frame.frames.destroy', $frame->id) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-images"></i></div>
                            <h4>No frames found</h4>
                            <p class="text-muted">Add your first dynamic photo frame to get started.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($frames->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $frames->firstItem() }} to {{ $frames->lastItem() }} of {{ $frames->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($frames->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $frames->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $frames->currentPage();
                    $lastPage = $frames->lastPage();
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

                @if ($frames->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $frames->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
