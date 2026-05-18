<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-category">Category</th>
                <th class="col-name">Name</th>
                <th class="col-thumb">Image</th>
                <th class="col-prompt">Prompt</th>
                <th class="col-action text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($images as $img)
                <tr id="row-{{ $img->id }}">
                    <td><span class="category-badge">{{ $img->category?->category_name ?? 'N/A' }}</span></td>
                    <td><span class="fw-bold text-dark">{{ $img->name ?? 'N/A' }}</span></td>
                    <td>
                        @if ($img->image_path && $img->category)
                            <div class="img-container">
                                <img loading="lazy" decoding="async" src="{{ asset('upload/filter_ai_image/images/' . $img->category?->category_name . '/category_image/' . $img->image_path) }}" alt="{{ $img->name }}">
                            </div>
                        @else
                            <div class="img-container d-flex align-items-center justify-content-center bg-light">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="prompt-text" title="{{ $img->ai_prompt }}">
                            {{ $img->ai_prompt }}
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="action-btn edit-btn"
                                data-id="{{ $img->id }}"
                                data-category="{{ $img->category_id }}"
                                data-name="{{ $img->name }}"
                                data-prompt="{{ $img->ai_prompt }}"
                                data-image="{{ $img->image_path }}" onclick="editImage(this)" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form id="deleteForm-{{ $img->id }}" action="{{ route('filter-ai-image.images.destroy', $img->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="action-btn delete-btn" data-id="{{ $img->id }}" data-category="{{ $img->category?->category_name ?? 'N/A' }}" onclick="confirmDelete(this)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-robot"></i></div>
                            <h4 class="empty-state-title">No Filter AI Images Found</h4>
                            <p class="empty-state-text">No images match your current search criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($images->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $images->firstItem() }} to {{ $images->lastItem() }} of {{ $images->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($images->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $images->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $images->currentPage();
                    $lastPage = $images->lastPage();
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

                @if ($images->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $images->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
