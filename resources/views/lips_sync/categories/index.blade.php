@extends('partials.layout')
@section('title', 'Lips Sync Categories')
@section('container')
    <style>
        .stats-badge { background-color: #eaecf4; color: #5a5c69; padding: .5rem 1rem; border-radius: .35rem; font-size: .85rem; font-weight: 700; }
        .main-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15); padding: 1.5rem; margin-bottom: 2rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background-color: #f8f9fc; color: #5a5c69; font-weight: 700; padding: .75rem; border-bottom: 1px solid #e3e6f0; }
        .data-table td { padding: .75rem; vertical-align: middle; border-bottom: 1px solid #e3e6f0; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: .35rem; color: #fff !important; text-decoration: none; transition: all 0.2s; border: none; }
        .action-btn i { font-size: 0.9rem; color: #fff !important; }
        .edit-btn { background-color: #0dcaf0; }
        .delete-btn { background-color: #bb2d3b; }
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-state-icon { font-size: 3.5rem; color: #b7b9cc; margin-bottom: 1rem; }
        .cat-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: .35rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title text-primary"><i class="bi bi-grid-3x3-gap me-2"></i>Lips Sync Categories</h1>
                <p class="page-subtitle text-muted">Manage categories for Lips Sync items</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="stats-badge">
                    <i class="bi bi-collection"></i> Total:<span class="ms-1">{{ $categories->total() }}</span>Categories
                </span>
                <a href="{{ route('lips-sync.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Category
                </a>
            </div>
        </div>

        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span>Show</span>
                    <select id="per_page" class="form-select form-select-sm d-inline-block mx-1" style="width: 80px;">
                        @foreach ([10, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
                <div>
                    <input type="text" id="category_search" class="form-control form-control-sm" placeholder="Search Category Name..." value="{{ $search }}" style="width: 280px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Image</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr id="row-{{ $category->id }}">
                                <td><strong>{{ $category->category_name }}</strong></td>
                                <td>
                                    @if ($category->image)
                                        <img src="{{ asset('upload/lips_sync/' . $category->category_name . '/category image/' . $category->image) }}" class="cat-thumb" alt="">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('lips-sync.categories.edit', $category->id) }}" class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="action-btn delete-btn" data-id="{{ $category->id }}" data-name="{{ $category->category_name }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="deleteForm-{{ $category->id }}" action="{{ route('lips-sync.categories.destroy', $category->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-folder-x"></i></div>
                                        <h4>No Categories Found</h4>
                                        <p class="text-muted">Add your first Lips Sync category to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} entries
                </div>
                <div>{{ $categories->appends(request()->except('page'))->links() }}</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 4000, timerProgressBar: true });
            @endif
            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Delete category?',
                        text: 'This will delete "' + name + '" and all its items and files.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#bb2d3b',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('deleteForm-' + id).submit();
                        }
                    });
                });
            });

            const params = new URLSearchParams(window.location.search);
            document.getElementById('per_page').addEventListener('change', function () {
                params.set('per_page', this.value);
                params.delete('page');
                window.location.search = params.toString();
            });

            let searchTimer = null;
            document.getElementById('category_search').addEventListener('input', function () {
                clearTimeout(searchTimer);
                const val = this.value;
                searchTimer = setTimeout(() => {
                    if (val) params.set('search', val); else params.delete('search');
                    params.delete('page');
                    window.location.search = params.toString();
                }, 500);
            });
        });
    </script>
@endsection
