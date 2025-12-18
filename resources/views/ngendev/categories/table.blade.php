<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center">
        <label class="me-2">Show</label>
        <select id="per-page-select" class="form-select w-auto">
            <option value="5" {{ $categories->perPage() == 5 ? 'selected' : '' }}>5</option>
            <option value="10" {{ $categories->perPage() == 10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ $categories->perPage() == 20 ? 'selected' : '' }}>20</option>
            <option value="30" {{ $categories->perPage() == 30 ? 'selected' : '' }}>30</option>
        </select>
        <label class="ms-2">entries</label>
    </div>
    <form id="search-form" class="d-flex align-items-center">
        <div class="input-group">
            <input type="text" id="search-input" name="search" value="{{ $search }}" class="form-control"
                placeholder="Search category...">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

@if ($categories->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-tags"></i></div>
        <h4 class="empty-state-title">No Categories Found</h4>
        <p class="empty-state-text">Get started by adding your first category</p>
        <a href="{{ route('ngendev.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add
            Category</a>
    </div>
@else
    <div class="table-responsive">
        <table class="data-table" id="categoriesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Image</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->category_name }}</td>
                        <td>
                            @if ($category->category_image)
                                @php $images = json_decode($category->category_image, true); @endphp
                                @foreach ((array) $images as $img)
                                    <img src="{{ asset('upload/ngendev/images/' . $category->category_name . '/category_thumbnail_image/' . $img) }}"
                                        class="category-image">
                                @endforeach
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('ngendev.categories.edit', $category->id) }}" class="action-btn edit-btn"
                                    data-bs-toggle="tooltip" title="Edit"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}"
                                    data-name="{{ $category->category_name }}" data-bs-toggle="tooltip" title="Delete"><i
                                        class="bi bi-trash"></i></button>

                                <form id="deleteForm-{{ $category->id }}"
                                    action="{{ route('ngendev.categories.destroy', $category->id) }}" method="POST"
                                    class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <span id="total-count-hidden" class="d-none">{{ $categories->total() }}</span>
        <div>
            Showing <strong>{{ $categories->firstItem() }}</strong> to
            <strong>{{ $categories->lastItem() }}</strong> of <strong>{{ $categories->total() }}</strong>
            entries
        </div>
        <ul class="pagination mb-0">
            <li class="page-item {{ $categories->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="javascript:void(0)" data-page="{{ $categories->currentPage() - 1 }}">Previous</a>
            </li>
            @for ($i = 1; $i <= $categories->lastPage(); $i++)
                <li class="page-item {{ $i == $categories->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="javascript:void(0)" data-page="{{ $i }}">{{ $i }}</a>
                </li>
            @endfor
            <li class="page-item {{ $categories->currentPage() == $categories->lastPage() ? 'disabled' : '' }}">
                <a class="page-link" href="javascript:void(0)" data-page="{{ $categories->currentPage() + 1 }}">Next</a>
            </li>
        </ul>
    </div>
@endif