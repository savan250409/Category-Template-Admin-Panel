@extends('partials.layout')
@section('title', 'Subcategories')
@section('container')

    <div class="container mt-4" style="padding: 0 2rem">
        <div class="pagetitle">
            <h1>Subcategories</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Subcategories</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <h5 class="card-title">Manage Subcategories</h5>
                        <a href="{{ route('subcategories.form', ['origin' => request('origin')]) }}"
                            class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Add New
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success mt-3" id="flash-message">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mt-3" id="flash-message">{{ session('error') }}</div>
                    @endif

                    <table class="table table-bordered mt-3">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">ID</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Trending</th>
                                <th width="25%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subcategories as $sub)
                                <tr>
                                    <td>{{ $sub->id }}</td>
                                    <td>{{ $sub->category_name }}</td>
                                    <td>{{ $sub->title }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-trending" type="checkbox"
                                                data-id="{{ $sub->id }}" {{ $sub->trending ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('subcategories.show', ['id' => $sub->id, 'origin' => request('origin')]) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="{{ route('subcategories.form', ['id' => $sub->id, 'origin' => request('origin')]) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form
                                            action="{{ route('subcategories.destroy', ['id' => $sub->id, 'origin' => request('origin')]) }}"
                                            method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-secondary deleteBtn">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end">
                        {{ $subcategories->appends(['origin' => request('origin')])->links('pagination::bootstrap-4') }}
                    </div>

                </div>
            </div>
        </section>
    </div>

    @section('scripts')
        <script>
            // Auto-hide flash messages
            setTimeout(() => {
                const flash = document.getElementById('flash-message');
                if (flash) flash.style.display = 'none';
            }, 5000);

            // Delete Confirmation
            document.querySelectorAll('.deleteBtn').forEach(button => {
                button.addEventListener('click', function () {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.closest('form').submit();
                        }
                    })
                });
            });

            // Trending Toggle
            document.querySelectorAll('.toggle-trending').forEach(toggle => {
                toggle.addEventListener('change', function () {
                    let id = this.dataset.id;
                    let trending = this.checked ? 1 : 0;

                    fetch("{{ route('subcategories.updateStatus') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            id: id,
                            trending: trending,
                            origin: '{{ request('origin') }}'
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // success toast?
                            } else {
                                alert('Something went wrong');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        </script>
    @endsection

@endsection