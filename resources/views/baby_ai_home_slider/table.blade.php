<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-type">Source Type</th>
                <th class="col-source">Source Category</th>
                <th class="col-title">Title</th>
                <th class="col-preview">Preview</th>
                <th class="col-status">Status</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sliders as $slider)
                <tr id="row-{{ $slider->id }}">
                    <td>
                        <span class="type-badge {{ $slider->source_type }}">
                            @if ($slider->source_type === 'image') Image
                            @elseif ($slider->source_type === 'video') Video
                            @else Dynamic Frame
                            @endif
                        </span>
                    </td>
                    <td><strong>{{ $slider->source_name }}</strong></td>
                    <td>{{ $slider->title ?: '—' }}</td>
                    <td>
                        @php $base = 'upload/baby_ai_home_slider/' . $slider->source_type . '/'; @endphp
                        @if ($slider->source_type === 'video')
                            @if ($slider->video_thumbnail)
                                <img loading="lazy" decoding="async" src="{{ asset($base . $slider->video_thumbnail) }}" class="preview-thumb" alt="">
                            @elseif ($slider->video)
                                <video src="{{ asset($base . $slider->video) }}" class="preview-thumb" muted></video>
                            @else
                                <div class="preview-thumb bg-light d-flex align-items-center justify-content-center"><i class="bi bi-camera-video text-muted"></i></div>
                            @endif
                        @else
                            @if ($slider->image)
                                <img loading="lazy" decoding="async" src="{{ asset($base . $slider->image) }}" class="preview-thumb" alt="">
                            @else
                                <div class="preview-thumb bg-light d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input slider-status-toggle" type="checkbox" role="switch" id="status-{{ $slider->id }}" data-id="{{ $slider->id }}" {{ $slider->is_on ? 'checked' : '' }}>
                            <label class="form-check-label" for="status-{{ $slider->id }}">
                                <span id="badge-{{ $slider->id }}" class="badge {{ $slider->is_on ? 'bg-success' : 'bg-danger' }}">{{ $slider->is_on ? 'ON' : 'OFF' }}</span>
                            </label>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('baby-ai-home-slider.edit', $slider->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $slider->id }}" data-name="{{ $slider->source_name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $slider->id }}" action="{{ route('baby-ai-home-slider.destroy', $slider->id) }}" method="POST" style="display:none;">
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
                            <div class="empty-state-icon"><i class="bi bi-house-heart"></i></div>
                            <h4>No Sliders Found</h4>
                            <p class="text-muted">Add up to 3 home screen sliders — one for each source type.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($sliders->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $sliders->firstItem() }} to {{ $sliders->lastItem() }} of {{ $sliders->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($sliders->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $sliders->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $sliders->currentPage();
                    $lastPage = $sliders->lastPage();
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

                @if ($sliders->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $sliders->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
