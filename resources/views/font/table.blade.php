<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-name">Font Name</th>
                <th class="col-file">Font File</th>
                <th class="col-preview">Preview</th>
                <th class="col-premium">Type</th>
                <th class="col-action text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($fonts as $font)
                <tr id="row-{{ $font->id }}">
                    <td><strong>{{ $font->font_name }}</strong></td>
                    <td>
                        @if ($font->font_file)
                            <a class="file-link" href="{{ asset('upload/font/' . rawurlencode($font->font_name) . '/' . rawurlencode($font->font_file)) }}" target="_blank" title="{{ $font->font_file }}">
                                <i class="bi bi-file-earmark-font me-1"></i>{{ \Illuminate\Support\Str::limit($font->font_file, 40) }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($font->preview_image)
                            <img src="{{ asset('upload/font/' . rawurlencode($font->font_name) . '/' . rawurlencode($font->preview_image)) }}" class="font-preview-thumb" alt="">
                        @else
                            <div class="font-preview-thumb d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <label class="check-cell" for="premium-{{ $font->id }}">
                            <input class="form-check-input toggle-premium" type="checkbox" id="premium-{{ $font->id }}" data-id="{{ $font->id }}" {{ $font->is_premium ? 'checked' : '' }}>
                            <span class="check-label">Pro</span>
                        </label>
                    </td>
                    <td class="text-end">
                        <div class="action-cell">
                            <a href="{{ route('fonts.edit', $font->id) }}" class="action-btn edit-btn" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="action-btn delete-btn" data-id="{{ $font->id }}" data-name="{{ $font->font_name }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="deleteForm-{{ $font->id }}" action="{{ route('fonts.destroy', $font->id) }}" method="POST" style="display:none;">
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
                            <div class="empty-state-icon"><i class="bi bi-fonts"></i></div>
                            <h4>No Fonts Found</h4>
                            <p class="text-muted">Add your first font to get started.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($fonts->total() > 0)
    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $fonts->firstItem() }} to {{ $fonts->lastItem() }} of {{ $fonts->total() }} entries
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if ($fonts->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $fonts->currentPage() - 1 }}">Previous</a>
                    </li>
                @endif

                @php
                    $currentPage = $fonts->currentPage();
                    $lastPage = $fonts->lastPage();
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

                @if ($fonts->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{{ $fonts->currentPage() + 1 }}">Next</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
