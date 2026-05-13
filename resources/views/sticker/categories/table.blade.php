<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-name">Category Name</th>
                <th class="col-thumb">Thumbnail</th>
                <th class="col-premium">Premium</th>
                <th class="col-status">Status</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr id="row-{{ $category->id }}">
                    <td><strong>{{ $category->category_name }}</strong></td>
                    <td>
                        @if ($category->image)
                            <img src="{{ asset('upload/sticker/' . rawurlencode($category->category_name) . '/category image/' . rawurlencode($category->image)) }}" class="cat-thumb" alt="">
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </td>
                    <td>
                        <label class="check-cell" for="premium-{{ $category->id }}">
                            <input class="form-check-input toggle-premium" type="checkbox" id="premium-{{ $category->id }}" data-id="{{ $category->id }}" {{ $category->is_premium ? 'checked' : '' }}>
                            <span class="check-label">Pro</span>
                        </label>
                    </td>
                    <td>
                        <label class="check-cell" for="status-{{ $category->id }}">
                            <input class="form-check-input toggle-status" type="checkbox" id="status-{{ $category->id }}" data-id="{{ $category->id }}" {{ $category->status ? 'checked' : '' }}>
                            <span class="check-label">Active</span>
                        </label>
                    </td>
                    <td class="text-end">
                        <div class="action-cell">
                            <a href="{{ route('sticker.categories.edit', $category->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $category->id }}" action="{{ route('sticker.categories.destroy', $category->id) }}" method="POST" style="display:none;">
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
                            <p class="text-muted">Add your first sticker category to get started.</p>
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
