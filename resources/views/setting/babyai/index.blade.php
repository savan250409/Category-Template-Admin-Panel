@extends('partials.layout')
@section('title', 'AI Image Baby Photo Settings')
@section('container')

    <div class="container-fluid py-4" style="padding: 0 2rem">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">AI Image Baby Photo Settings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Baby AI Settings</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                @if ($settings->count() == 0)
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-lg me-1"></i> Add New Setting
                    </button>
                @endif
            </div>
        </div>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-black"><i class="bi bi-gear-fill me-2"></i>Manage Configuration</h5>
            </div>

            <div class="card-body p-4">
                <!-- Flash messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert"
                        id="flash-message">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert"
                        id="flash-message">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4"
                                    width="10%">ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Model
                                    Name</th>
                                <th class="text-secondary opacity-7 text-center" width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">#{{ $setting->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $setting->model }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-action btn-outline-primary btn-sm mx-1 rounded-2 editBtn"
                                                data-id="{{ $setting->id }}" data-model="{{ $setting->model }}"
                                                data-bs-toggle="tooltip" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button class="btn btn-action btn-outline-danger btn-sm mx-1 rounded-2 deleteBtn"
                                                data-id="{{ $setting->id }}" data-bs-toggle="tooltip" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                            <h6 class="text-muted">No settings configuration found.</h6>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('ai-image-baby-photo-setting.store') }}" method="POST"
                class="modal-content rounded-4 border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add New AI Setting</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="model" class="form-label fw-bold text-secondary">Model Name</label>
                        <input type="text" name="model" id="model" class="form-control form-control-lg rounded-3"
                            placeholder="Enter model name..." required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Add Setting</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit AI Setting</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="editModel" class="form-label fw-bold text-secondary">Model Name</label>
                        <input type="text" name="model" id="editModel" class="form-control form-control-lg rounded-3"
                            required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Update Setting</button>
                </div>
            </form>
        </div>
    </div>

    @section('scripts')
        <script>
            // Auto-hide flash messages after 5 sec
            setTimeout(() => {
                const flash = document.getElementById('flash-message');
                if (flash) flash.style.display = 'none';
            }, 5000);

            // Open edit modal and populate data
            document.querySelectorAll('.editBtn').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const model = this.dataset.model;
                    const form = document.getElementById('editForm');

                    form.action = `/ai-image-baby-photo-setting/${id}`;
                    document.getElementById('editModel').value = model;

                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
            });

            // SweetAlert delete confirmation
            document.querySelectorAll('.deleteBtn').forEach(button => {
                button.addEventListener('click', function () {
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
                            form.action = `/ai-image-baby-photo-setting/${id}`;
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