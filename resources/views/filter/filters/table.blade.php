<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-category">Category</th>
                <th class="col-name">Filter Name</th>
                <th class="col-values">Values</th>
                <th class="col-type">Type</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filters as $filter)
                <tr id="row-{{ $filter->id }}">
                    <td><strong>{{ optional($filter->category)->name ?? '—' }}</strong></td>
                    <td>{{ $filter->name }}</td>
                    <td>
                        <div class="values-cell">
                            <span>S: {{ rtrim(rtrim(number_format($filter->saturation, 2, '.', ''), '0'), '.') }}, B: {{ rtrim(rtrim(number_format($filter->brightness, 2, '.', ''), '0'), '.') }}, C: {{ rtrim(rtrim(number_format($filter->contrast, 2, '.', ''), '0'), '.') }}</span>
                            <br>
                            <span>R: {{ rtrim(rtrim(number_format($filter->red, 2, '.', ''), '0'), '.') }}, G: {{ rtrim(rtrim(number_format($filter->green, 2, '.', ''), '0'), '.') }}, B: {{ rtrim(rtrim(number_format($filter->blue, 2, '.', ''), '0'), '.') }}</span>
                        </div>
                    </td>
                    <td>
                        <label class="check-cell" for="premium-{{ $filter->id }}">
                            <input class="form-check-input toggle-premium" type="checkbox"
                                   id="premium-{{ $filter->id }}" data-id="{{ $filter->id }}"
                                   {{ $filter->is_premium ? 'checked' : '' }}>
                            <span class="check-label">{{ $filter->is_premium ? 'Pro' : 'Free' }}</span>
                        </label>
                    </td>
                    <td class="text-end">
                        <div class="action-cell">
                            <a href="{{ route('filter.filters.edit', $filter->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $filter->id }}" data-name="{{ $filter->name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $filter->id }}" action="{{ route('filter.filters.destroy', $filter->id) }}" method="POST" style="display:none;">
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
                            <div class="empty-state-icon"><i class="bi bi-sliders"></i></div>
                            <h4>No Filters Found</h4>
                            <p class="text-muted">Add your first filter or import from CSV.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($filters->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $filters->firstItem() }} to {{ $filters->lastItem() }} of {{ $filters->total() }} entries
        </div>
        <nav>
            <ul class="pagination">
                @if ($filters->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $filters->currentPage() - 1 }}">Previous</a></li>
                @endif

                @php $currentPage = $filters->currentPage(); $lastPage = $filters->lastPage(); @endphp

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

                @if ($filters->hasMorePages())
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $filters->currentPage() + 1 }}">Next</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
