@extends('partials.layout')
@section('title', isset($filter) ? 'Edit Filter' : 'Add New Filter')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .page-title { color: #7048e8; font-weight: 700; }
        .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
        .col-pair { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .col-triple { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        @media (max-width: 768px) { .col-pair, .col-triple { grid-template-columns: 1fr; } }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($filter) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($filter) ? 'Edit Filter' : 'Add New Filter' }}
                </h1>
                <p class="text-muted">{{ isset($filter) ? 'Update filter values' : 'Create a new Filter' }}</p>
            </div>
            <a href="{{ route('filter.filters.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Filters
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                  action="{{ isset($filter) ? route('filter.filters.update', $filter->id) : route('filter.filters.store') }}"
                  id="filterForm">
                @csrf
                @if (isset($filter)) @method('PUT') @endif

                <div class="mb-3">
                    <label for="filter_category_id" class="form-label fw-semibold">Category</label>
                    <select class="form-control" id="filter_category_id" name="filter_category_id" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('filter_category_id', $filter->filter_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Filter Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="{{ old('name', $filter->name ?? '') }}" placeholder="Filter Name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Type</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" value="1"
                               {{ old('is_premium', isset($filter) ? $filter->is_premium : 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_premium">Premium (Pro)</label>
                    </div>
                </div>

                <div class="mb-3 col-pair">
                    <div>
                        <label for="saturation" class="form-label fw-semibold">Saturation</label>
                        <input type="number" step="any" class="form-control" id="saturation" name="saturation"
                               value="{{ old('saturation', $filter->saturation ?? 1) }}" required>
                    </div>
                    <div>
                        <label for="brightness" class="form-label fw-semibold">Brightness</label>
                        <input type="number" step="any" class="form-control" id="brightness" name="brightness"
                               value="{{ old('brightness', $filter->brightness ?? 0) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="contrast" class="form-label fw-semibold">Contrast</label>
                    <input type="number" step="any" class="form-control" id="contrast" name="contrast"
                           value="{{ old('contrast', $filter->contrast ?? 1) }}" required>
                </div>

                <div class="mb-3 col-triple">
                    <div>
                        <label for="red" class="form-label fw-semibold">Red</label>
                        <input type="number" step="any" class="form-control" id="red" name="red"
                               value="{{ old('red', $filter->red ?? 1) }}" required>
                    </div>
                    <div>
                        <label for="green" class="form-label fw-semibold">Green</label>
                        <input type="number" step="any" class="form-control" id="green" name="green"
                               value="{{ old('green', $filter->green ?? 1) }}" required>
                    </div>
                    <div>
                        <label for="blue" class="form-label fw-semibold">Blue</label>
                        <input type="number" step="any" class="form-control" id="blue" name="blue"
                               value="{{ old('blue', $filter->blue ?? 1) }}" required>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($filter) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($filter) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('filter.filters.index') }}" class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif
        });
    </script>
@endsection
