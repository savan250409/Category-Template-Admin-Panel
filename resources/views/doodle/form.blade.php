@extends('partials.layout')
@section('title', isset($doodle) ? 'Edit Doodle' : 'Add New Doodle')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .page-title { color: #7048e8; font-weight: 700; }
        .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
        .preview-img { max-width: 220px; max-height: 160px; object-fit: contain; padding: 6px; background:#f8f9fc; border:1px solid #e3e6f0; border-radius: .35rem; }

        /* Doodle type radio cards */
        .dtype-row { display: flex; gap: 1rem; flex-wrap: wrap; }
        .dtype-option {
            flex: 1; min-width: 180px; cursor: pointer; user-select: none;
            display: flex; align-items: center; gap: .55rem;
            border: 1.5px solid #e3e6f0; border-radius: .5rem;
            padding: .65rem 1rem; transition: all .15s ease;
            background: #fff;
        }
        .dtype-option:hover { border-color: #b691f5; background: #faf7ff; }
        .dtype-option input { accent-color: #7048e8; width: 1.1rem; height: 1.1rem; cursor: pointer; }
        .dtype-option.is-active { border-color: #7048e8; background: #f3eaff; box-shadow: 0 0 0 3px rgba(112, 72, 232, .12); }

        /* Choose image picker */
        .file-picker {
            display: flex; align-items: stretch; border: 1px solid #e3e6f0; border-radius: .35rem; overflow: hidden;
        }
        .file-picker .choose-btn {
            background: #fff; border: none; border-right: 1px solid #e3e6f0;
            font-weight: 700; padding: .65rem 1.1rem; color: #2c3e50;
            cursor: pointer; white-space: nowrap;
        }
        .file-picker .choose-btn:hover { background: #f8f9fc; }
        .file-picker .file-name { flex: 1; padding: .65rem 1rem; color: #6c757d; display: flex; align-items: center; }
        .file-picker.has-file .file-name { color: #2c3e50; }
        .clear-file {
            background: rgba(231, 74, 59, .95); color:#fff; border:none; padding: 0 .9rem; cursor:pointer; font-weight: 600;
        }

        .webp-warning {
            display: inline-flex; align-items: center; gap: .35rem;
            background: #fff8e1; color: #8a6d00;
            border: 1px solid #ffe28a; border-radius: .35rem;
            padding: .45rem .75rem; font-size: .85rem; font-weight: 600;
        }
        .webp-warning i { color: #c79100; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($doodle) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($doodle) ? 'Edit Doodle' : 'Add New Doodle' }}
                </h1>
                <p class="text-muted">{{ isset($doodle) ? 'Update doodle details' : 'Create a new Doodle' }}</p>
            </div>
            <a href="{{ route('doodles.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Doodles
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($doodle) ? route('doodles.update', $doodle->id) : route('doodles.store') }}"
                enctype="multipart/form-data" id="doodleForm">
                @csrf
                @if (isset($doodle))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{ old('name', $doodle->name ?? '') }}" placeholder="Doodle Name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Type</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" value="1"
                            {{ old('is_premium', isset($doodle) ? $doodle->is_premium : 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_premium">Premium (Pro)</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Doodle Type</label>
                    @php $currentType = old('doodle_type', $doodle->doodle_type ?? 'image'); @endphp
                    <div class="dtype-row">
                        <label class="dtype-option {{ $currentType === 'image' ? 'is-active' : '' }}" data-type="image">
                            <input type="radio" name="doodle_type" value="image" {{ $currentType === 'image' ? 'checked' : '' }}>
                            <span>Image</span>
                        </label>
                        <label class="dtype-option {{ $currentType === 'line' ? 'is-active' : '' }}" data-type="line">
                            <input type="radio" name="doodle_type" value="line" {{ $currentType === 'line' ? 'checked' : '' }}>
                            <span>Line</span>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Doodle Image</label>
                    <div class="file-picker {{ isset($doodle) && $doodle->image ? 'has-file' : '' }}" id="filePicker">
                        <button type="button" class="choose-btn" id="chooseImageBtn">Choose Image</button>
                        <div class="file-name" id="fileName">
                            {{ isset($doodle) && $doodle->image ? $doodle->image : 'No file chosen' }}
                        </div>
                        @if (isset($doodle) && $doodle->image)
                            <button type="button" class="clear-file" id="clearFileBtn" title="Remove"><i class="bi bi-x"></i></button>
                        @endif
                        <input type="file" id="image" name="image" accept=".webp,image/webp" hidden>
                    </div>
                    <div class="webp-warning mt-2">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Only <strong>.webp</strong> images are allowed.
                    </div>
                    @if (isset($doodle) && $doodle->image)
                        <div class="mt-2">
                            <img id="currentPreview" class="preview-img"
                                src="{{ asset('upload/doodle/' . rawurlencode($doodle->name) . '/' . rawurlencode($doodle->image)) }}" alt="Preview">
                        </div>
                    @else
                        <img id="currentPreview" class="preview-img mt-2 d-none" alt="Preview">
                    @endif
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($doodle) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($doodle) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('doodles.index') }}" data-back-to-list class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Doodle type radio styling
            const dtypeOptions = document.querySelectorAll('.dtype-option');
            dtypeOptions.forEach(opt => {
                opt.addEventListener('click', function (e) {
                    if (e.target.tagName !== 'INPUT') {
                        const input = this.querySelector('input[type=radio]');
                        if (input) input.checked = true;
                    }
                    dtypeOptions.forEach(o => o.classList.remove('is-active'));
                    this.classList.add('is-active');
                });
            });

            // File picker
            const fileInput  = document.getElementById('image');
            const chooseBtn  = document.getElementById('chooseImageBtn');
            const fileName   = document.getElementById('fileName');
            const filePicker = document.getElementById('filePicker');
            const preview    = document.getElementById('currentPreview');
            const clearBtn   = document.getElementById('clearFileBtn');

            chooseBtn.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', function () {
                if (!this.files || !this.files[0]) return;
                const file = this.files[0];

                const isWebp = (file.type === 'image/webp') || /\.webp$/i.test(file.name);
                if (!isWebp) {
                    this.value = '';
                    fileName.textContent = 'No file chosen';
                    filePicker.classList.remove('has-file');
                    preview.classList.add('d-none');
                    preview.src = '';
                    Swal.fire({ icon: 'warning', title: 'Only .webp allowed', text: 'Please select a .webp image file.' });
                    return;
                }

                fileName.textContent = file.name;
                filePicker.classList.add('has-file');
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    fileInput.value = '';
                    fileName.textContent = 'No file chosen';
                    filePicker.classList.remove('has-file');
                    preview.classList.add('d-none');
                    preview.src = '';
                });
            }

            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif
        });
    </script>
@endsection
