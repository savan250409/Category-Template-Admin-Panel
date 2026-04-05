<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Image</th>
                <th width="30%">Category Name</th>
                <th width="20%">Status</th>
                <th width="30%" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr id="row-{{ $category->id }}">
                    <td>
                        @php $images = json_decode($category->category_image, true); @endphp
                        @if (!empty($images) && isset($images[0]))
                            <img src="{{ asset('upload/filter_ai_image/images/' . rawurlencode($category->category_name) . '/category_thumbnail_image/' . rawurlencode($images[0])) }}"
                                class="category-image">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center category-image">
                                <i class="bi bi-image text-muted fs-4"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong class="text-dark">{{ $category->category_name }}</strong>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="form-check form-switch m-0 me-2">
                                <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                    id="status-{{ $category->id }}" data-id="{{ $category->id }}"
                                    {{ $category->status ? 'checked' : '' }}>
                            </div>
                            <span id="status-badge-{{ $category->id }}"
                                class="badge {{ $category->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $category->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('filter-ai-image.categories.edit', $category->id) }}" class="action-btn edit-btn"
                                data-bs-toggle="tooltip" title="Edit Category">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form id="deleteForm-{{ $category->id }}"
                                action="{{ route('filter-ai-image.categories.destroy', $category->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}"
                                    data-name="{{ $category->category_name }}" data-bs-toggle="tooltip"
                                    title="Delete Category">
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
                            <div class="empty-state-icon">
                                <i class="bi bi-tags"></i>
                            </div>
                            <h4 class="empty-state-title">No Categories Found</h4>
                            <p class="empty-state-text">Get started by creating your first Filter AI Image category</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-none" id="total-count-hidden">{{ $categories->total() }}</div>

@if ($categories->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
        <div class="text-muted small">
            Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of
            {{ $categories->total() }} categories
        </div>
        <div>
            {{ $categories->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endif
