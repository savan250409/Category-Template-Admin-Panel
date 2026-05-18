<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-thumb">Image</th>
                <th class="col-name">Category Name</th>
                <th class="col-trending">Trending</th>
                <th class="col-status">Status</th>
                <th class="col-action text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr id="row-{{ $category->id }}">
                    <td>
                        @if ($category->category_image)
                            @if (\Illuminate\Support\Str::startsWith($category->category_image, 'upload/'))
                                <img loading="lazy" decoding="async" src="{{ asset($category->category_image) }}" class="cat-thumb" alt="{{ $category->category_name }}">
                            @elseif (file_exists(public_path('upload/AI Baby Video/' . $category->category_name . '/category thumbanail/' . $category->category_image)))
                                <img loading="lazy" decoding="async" src="{{ asset('upload/AI Baby Video/' . $category->category_name . '/category thumbanail/' . $category->category_image) }}" class="cat-thumb" alt="{{ $category->category_name }}">
                            @else
                                <img loading="lazy" decoding="async" src="{{ asset('upload/AI Baby Video/Category/' . $category->category_image) }}" class="cat-thumb" alt="{{ $category->category_name }}">
                            @endif
                        @else
                            <div class="cat-thumb bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td><strong>{{ $category->category_name }}</strong></td>
                    <td>
                        @if ($category->trending)
                            <span class="badge bg-success"><i class="bi bi-graph-up me-1"></i>Trending</span>
                        @else
                            <span class="badge bg-secondary">Normal</span>
                        @endif
                    </td>
                    <td>
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input status-toggle" type="checkbox" role="switch" id="status-{{ $category->id }}" data-id="{{ $category->id }}" {{ $category->status ? 'checked' : '' }}>
                            <label class="form-check-label" for="status-{{ $category->id }}">
                                <span id="status-badge-{{ $category->id }}" class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $category->status ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('ai-baby-video.categories.edit', $category->id) }}" class="action-btn edit-btn" title="Edit Category">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" title="Delete Category">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $category->id }}" action="{{ route('ai-baby-video.categories.destroy', $category->id) }}" method="POST" style="display: none;">
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
                            <div class="empty-state-icon"><i class="bi bi-folder-x"></i></div>
                            <h4>No Categories Found</h4>
                            <p class="text-muted">Try adjusting your search or add a new category.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($categories->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($categories->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $categories->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $categories->currentPage();
                    $lastPage = $categories->lastPage();
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

                @if ($categories->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $categories->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
