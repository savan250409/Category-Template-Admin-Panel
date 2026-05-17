<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-name">Name</th>
                <th class="col-image">Thumbnail</th>
                <th class="col-status">Status</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr id="row-{{ $category->id }}">
                    <td><strong>{{ $category->name }}</strong></td>
                    <td>
                        @if ($category->image)
                            <img src="{{ asset('upload/filter/' . rawurlencode($category->name) . '/category image/' . rawurlencode($category->image)) }}"
                                 class="filter-thumb" alt="">
                        @else
                            <div class="filter-thumb d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <label class="check-cell" for="status-{{ $category->id }}">
                            <input class="form-check-input toggle-status" type="checkbox"
                                   id="status-{{ $category->id }}" data-id="{{ $category->id }}"
                                   {{ $category->status ? 'checked' : '' }}>
                            <span class="check-label">{{ $category->status ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </td>
                    <td class="text-end">
                        <div class="action-cell">
                            <a href="{{ route('filter.categories.edit', $category->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $category->id }}" action="{{ route('filter.categories.destroy', $category->id) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-funnel"></i></div>
                            <h4>No Filter Categories Found</h4>
                            <p class="text-muted">Add your first category to get started.</p>
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
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $categories->currentPage() - 1 }}">Previous</a></li>
                @endif

                @php $currentPage = $categories->currentPage(); $lastPage = $categories->lastPage(); @endphp

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
                        if ($end - $start < 7) { $start = max(1, $end - 7); }
                    @endphp

                    @if ($start > 1)
                        <li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif

                    @for ($p = $start; $p <= $end; $p++)
                        <li class="page-item {{ $p == $currentPage ? 'active' : '' }}">
                            <a class="page-link" href="#" data-page="{{ $p }}">{{ $p }}</a>
                        </li>
                    @endfor

                    @if ($end < $lastPage)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <li class="page-item"><a class="page-link" href="#" data-page="{{ $lastPage }}">{{ $lastPage }}</a></li>
                    @endif
                @endif

                @if ($categories->hasMorePages())
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $categories->currentPage() + 1 }}">Next</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
