<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-name">Name</th>
                <th class="col-image">Doodle Image</th>
                <th class="col-dtype">Doodle Type</th>
                <th class="col-type">Type</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($doodles as $doodle)
                <tr id="row-{{ $doodle->id }}">
                    <td><strong>{{ $doodle->name }}</strong></td>
                    <td>
                        @if ($doodle->image)
                            <img src="{{ asset('upload/doodle/' . rawurlencode($doodle->name) . '/' . rawurlencode($doodle->image)) }}" class="doodle-thumb" alt="">
                        @else
                            <div class="doodle-thumb d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="type-pill {{ $doodle->doodle_type }}">{{ ucfirst($doodle->doodle_type) }}</span>
                    </td>
                    <td>
                        <label class="check-cell" for="premium-{{ $doodle->id }}">
                            <input class="form-check-input toggle-premium" type="checkbox" id="premium-{{ $doodle->id }}" data-id="{{ $doodle->id }}" {{ $doodle->is_premium ? 'checked' : '' }}>
                            <span class="check-label">Pro</span>
                        </label>
                    </td>
                    <td class="text-end">
                        <div class="action-cell">
                            <a href="{{ route('doodles.edit', $doodle->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $doodle->id }}" data-name="{{ $doodle->name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $doodle->id }}" action="{{ route('doodles.destroy', $doodle->id) }}" method="POST" style="display:none;">
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
                            <div class="empty-state-icon"><i class="bi bi-stars"></i></div>
                            <h4>No Doodles Found</h4>
                            <p class="text-muted">Add your first doodle to get started.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($doodles->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $doodles->firstItem() }} to {{ $doodles->lastItem() }} of {{ $doodles->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($doodles->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $doodles->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $doodles->currentPage();
                    $lastPage = $doodles->lastPage();
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

                @if ($doodles->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $doodles->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
