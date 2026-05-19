@extends('partials.layout')
@section('title', isset($category) ? 'Edit Stickers' : 'Add New Sticker')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .page-title { color: #7048e8; font-weight: 700; }
        .dropzone-area {
            border: 2px dashed #b691f5;
            border-radius: .5rem;
            background: #faf7ff;
            min-height: 220px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center;
            cursor: pointer;
            padding: 1.5rem;
            transition: all .15s ease;
        }
        .dropzone-area.dragover { background: #f0e7ff; border-color: #7048e8; }
        .dropzone-icon { width: 56px; height: 56px; border-radius: 50%; background: #ede4ff; color: #7048e8; display:flex; align-items:center; justify-content:center; font-size: 1.4rem; margin-bottom: 1rem; }
        .preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-top: 1rem; }
        .preview-item { position: relative; border-radius: .35rem; overflow: hidden; border: 1px solid #e3e6f0; background:#fff; cursor: move; }
        .preview-item img { width: 100%; height: 120px; object-fit: cover; display: block; }
        .preview-item .remove-btn {
            position: absolute; top: 4px; right: 4px;
            background: rgba(231, 74, 59, .95); color:#fff; border:none; width:24px; height:24px; border-radius:50%;
            display:flex; align-items:center; justify-content:center; cursor:pointer; font-size: .8rem;
        }
        .existing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; }
        .existing-item { position: relative; border-radius: .35rem; overflow: hidden; border: 1px solid #e3e6f0; background:#fff; cursor: move; }
        .existing-item img { width: 100%; height: 120px; object-fit: cover; display: block; }
        .existing-item .delete-existing {
            position: absolute; top: 4px; right: 4px;
            background: rgba(231, 74, 59, .95); color:#fff; border:none; width:24px; height:24px; border-radius:50%;
            display:flex; align-items:center; justify-content:center; cursor:pointer; font-size: .8rem; z-index: 3;
        }
        .drag-handle-hint { font-size: .8rem; color: #6c757d; }
        .sortable-ghost { opacity: .4; }
        .sortable-drag  { transform: rotate(2deg); }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($category) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($category) ? 'Edit Stickers' : 'Add New Sticker' }}
                </h1>
                <p class="text-muted">{{ isset($category) ? 'Update stickers for this category' : 'Create a new Sticker' }}</p>
            </div>
            <a href="{{ route('sticker.stickers.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Stickers
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($category) ? route('sticker.stickers.update', $category->id) : route('sticker.stickers.store') }}"
                enctype="multipart/form-data" id="stickerForm">
                @csrf
                @if (isset($category))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="sticker_category_id" class="form-label fw-semibold">Category</label>
                    <select class="form-select" id="sticker_category_id" name="sticker_category_id" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ (isset($category) && $category->id == $cat->id) || old('sticker_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php
                    $existingStickers = isset($category) && is_array($category->stickers) ? $category->stickers : [];
                @endphp
                @if (isset($category) && count($existingStickers) > 0)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Existing Stickers <span class="drag-handle-hint fw-normal">(Drag to reorder)</span></label>
                        <div class="existing-grid" id="existingGrid" data-category-id="{{ $category->id }}">
                            @foreach ($existingStickers as $filename)
                                <div class="existing-item" data-image="{{ $filename }}">
                                    <img src="{{ asset('upload/sticker/' . rawurlencode($category->category_name) . '/stickers/' . rawurlencode($filename)) }}" alt="">
                                    <button type="button" class="delete-existing" data-image="{{ $filename }}" title="Delete">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Sticker Images <span class="text-muted fw-normal">(Drag &amp; drop to upload — drag handle to reorder)</span></label>
                    <div id="dropzone" class="dropzone-area">
                        <div class="dropzone-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                        <div class="fw-semibold mb-1">Drag &amp; drop images here</div>
                        <div class="text-muted small">or click anywhere in this area to browse files</div>
                        <input type="file" id="images" name="images[]" accept=".webp,image/webp" multiple hidden>
                    </div>
                    <small class="text-warning fw-bold mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>Upload Images (.webp only)
                    </small>
                    <div id="previewGrid" class="preview-grid"></div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($category) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($category) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('sticker.stickers.index') }}" data-back-to-list class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('images');
            const previewGrid = document.getElementById('previewGrid');
            let filesArr = [];
            let previewSortable = null;

            function refreshPreview() {
                previewGrid.innerHTML = '';
                filesArr.forEach((file, idx) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const item = document.createElement('div');
                        item.className = 'preview-item';
                        item.setAttribute('data-idx', idx);
                        item.innerHTML = `
                            <img src="${e.target.result}" alt="">
                            <button type="button" class="remove-btn"><i class="bi bi-x"></i></button>
                        `;
                        previewGrid.appendChild(item);
                    };
                    reader.readAsDataURL(file);
                });

                if (previewSortable) previewSortable.destroy();
                previewSortable = Sortable.create(previewGrid, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    filter: '.remove-btn',
                    onEnd: function () {
                        const newOrder = Array.from(previewGrid.querySelectorAll('.preview-item'))
                            .map(el => parseInt(el.getAttribute('data-idx'), 10));
                        const reordered = newOrder.map(i => filesArr[i]);
                        filesArr = reordered;
                        syncInput();
                        Array.from(previewGrid.querySelectorAll('.preview-item')).forEach((el, i) => {
                            el.setAttribute('data-idx', i);
                        });
                    }
                });
            }

            function syncInput() {
                const dt = new DataTransfer();
                filesArr.forEach(f => dt.items.add(f));
                fileInput.files = dt.files;
            }

            function isWebp(file) {
                const nameOk = file.name && file.name.toLowerCase().endsWith('.webp');
                const typeOk = file.type === 'image/webp';
                return nameOk || typeOk;
            }

            function addFiles(files) {
                const rejected = [];
                Array.from(files).forEach(f => {
                    if (isWebp(f)) {
                        filesArr.push(f);
                    } else {
                        rejected.push(f.name);
                    }
                });
                if (rejected.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid File Format',
                        html: 'Only .webp images are accepted. The following files were skipped:<br><br><code>' + rejected.map(n => $('<div>').text(n).html()).join('<br>') + '</code>'
                    });
                }
                syncInput();
                refreshPreview();
            }

            dropzone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => addFiles(fileInput.files));

            ['dragenter', 'dragover'].forEach(ev => {
                dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('dragover'); });
            });
            ['dragleave', 'drop'].forEach(ev => {
                dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('dragover'); });
            });
            dropzone.addEventListener('drop', e => {
                if (e.dataTransfer && e.dataTransfer.files) addFiles(e.dataTransfer.files);
            });

            previewGrid.addEventListener('click', e => {
                const btn = e.target.closest('.remove-btn');
                if (!btn) return;
                const item = btn.closest('.preview-item');
                if (!item) return;
                const idx = parseInt(item.getAttribute('data-idx'), 10);
                filesArr.splice(idx, 1);
                syncInput();
                refreshPreview();
            });

            // Existing stickers — drag to reorder, save to DB on drop
            const existingGrid = document.getElementById('existingGrid');
            if (existingGrid) {
                const existingCatId = existingGrid.getAttribute('data-category-id');

                Sortable.create(existingGrid, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    filter: '.delete-existing',
                    onEnd: function () {
                        const items = existingGrid.querySelectorAll('.existing-item');
                        const order = Array.from(items).map(el => el.getAttribute('data-image'));
                        fetch("{{ route('sticker.stickers.updateOrder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ category_id: existingCatId, order: order })
                        }).then(r => r.json()).then(res => {
                            if (res.success) {
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1500 });
                            }
                        }).catch(() => {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save order.' });
                        });
                    }
                });
            }

            // Delete existing sticker (AJAX)
            document.querySelectorAll('.delete-existing').forEach(btn => {
                btn.addEventListener('click', function () {
                    const image = this.getAttribute('data-image');
                    const itemEl = this.closest('.existing-item');
                    const catId = existingGrid ? existingGrid.getAttribute('data-category-id') : null;
                    Swal.fire({
                        title: 'Delete sticker?',
                        text: 'This will permanently remove this sticker image.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#bb2d3b',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        fetch("{{ route('sticker.stickers.destroyOne') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ category_id: catId, image: image })
                        }).then(r => r.json()).then(res => {
                            if (res.success) {
                                itemEl.remove();
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, showConfirmButton: false, timer: 1500 });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to delete.' });
                            }
                        }).catch(() => {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete.' });
                        });
                    });
                });
            });

            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif
        });
    </script>
@endsection
