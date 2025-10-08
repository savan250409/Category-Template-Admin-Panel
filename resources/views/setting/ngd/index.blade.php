@extends('partials.layout')
@section('title', 'AI Image NGD Settings')
@section('container')

    <div class="container mt-4" style="padding: 0 2rem">
        <div class="pagetitle">
            <h1>AI Image NGD Settings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">NGD AI Settings</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <h5 class="card-title">Manage AI Image NGD Settings</h5>

                        @if ($settings->count() == 0)
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                                <i class="bi bi-plus-circle"></i> Add New
                            </button>
                        @endif
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
                                <th width="10%">ID</th>
                                <th>Model Name</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                                <tr>
                                    <td>{{ $setting->id }}</td>
                                    <td>{{ $setting->model }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning editBtn" data-id="{{ $setting->id }}"
                                            data-model="{{ $setting->model }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>

                                        <button class="btn btn-sm btn-outline-secondary deleteBtn" data-id="{{ $setting->id }}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </section>

        <!-- Create Modal -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('ai-image-ngd-setting.store') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New AI Setting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="model" class="form-label">Model Name</label>
                            <input type="text" name="model" id="model" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Setting</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form id="editForm" method="POST" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit AI Setting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editModel" class="form-label">Model Name</label>
                            <input type="text" name="model" id="editModel" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Setting</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @section('scripts')
        <script>
            // Auto-hide flash messages
            setTimeout(() => {
                const flash = document.getElementById('flash-message');
                if (flash) flash.style.display = 'none';
            }, 5000);

            // Open edit modal
            document.querySelectorAll('.editBtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const model = this.dataset.model;
                    const form = document.getElementById('editForm');

                    form.action = `/ai-image-ngd-setting/${id}`;
                    document.getElementById('editModel').value = model;

                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
            });

            // SweetAlert delete confirmation
            document.querySelectorAll('.deleteBtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
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
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/ai-image-ngd-setting/${id}`;
                            form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    })
                });
            });
        </script>
    @endsection

@endsection
