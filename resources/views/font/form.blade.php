@extends('partials.layout')
@section('title', isset($font) ? 'Edit Font' : 'Add New Font')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; }
        .page-title { color: #7048e8; font-weight: 700; }
        .form-check-input:checked { background-color: #7048e8; border-color: #7048e8; }
        .preview-img { max-width: 220px; max-height: 160px; object-fit: contain; padding: 6px; background:#f8f9fc; border:1px solid #e3e6f0; border-radius: .35rem; }

        .dropzone-area {
            border: 2px dashed #b691f5;
            border-radius: .5rem;
            background: #faf7ff;
            min-height: 130px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center;
            cursor: pointer;
            padding: 1.25rem;
            transition: all .15s ease;
        }
        .dropzone-area.dragover { background: #f0e7ff; border-color: #7048e8; }
        .dropzone-icon { width: 44px; height: 44px; border-radius: 50%; background: #ede4ff; color: #7048e8; display:flex; align-items:center; justify-content:center; font-size: 1.2rem; margin-bottom: .65rem; }
        .dz-filename { font-weight: 600; color: #2c3e50; word-break: break-all; }
        .dz-hint { font-size: .8rem; color: #6c757d; }
        .clear-file {
            background: rgba(231, 74, 59, .95); color:#fff; border:none; height: 28px; padding: 0 .65rem; border-radius: .25rem; font-size: .8rem; font-weight: 600;
        }
    </style>

    <div class="container mt-4 mb-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-{{ isset($font) ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                    {{ isset($font) ? 'Edit Font' : 'Add New Font' }}
                </h1>
                <p class="text-muted">{{ isset($font) ? 'Update font details' : 'Create a new font' }}</p>
            </div>
            <a href="{{ route('fonts.index') }}" data-back-to-list class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Fonts
            </a>
        </div>

        <div class="form-card">
            <form method="POST"
                action="{{ isset($font) ? route('fonts.update', $font->id) : route('fonts.store') }}"
                enctype="multipart/form-data" id="fontForm">
                @csrf
                @if (isset($font))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="font_name" class="form-label fw-semibold">Font Name</label>
                    <input type="text" class="form-control" id="font_name" name="font_name"
                        value="{{ old('font_name', $font->font_name ?? '') }}" placeholder="Enter Font Name" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" value="1"
                        {{ old('is_premium', isset($font) ? $font->is_premium : 1) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_premium">Premium (Pro)</label>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Font Preview Image</label>
                    <div id="previewDropzone" class="dropzone-area">
                        <div id="previewEmpty">
                            <div class="dropzone-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                            <div class="dz-filename mb-1">Drag &amp; drop a file here or click</div>
                            <div class="dz-hint">.webp only</div>
                        </div>
                        <div id="previewSelected" class="d-none w-100 d-flex flex-column align-items-center gap-2">
                            <img id="previewImg" class="preview-img" alt="">
                            <div class="dz-filename" id="previewFilename"></div>
                            <button type="button" class="clear-file" id="clearPreviewBtn"><i class="bi bi-x"></i> Remove</button>
                        </div>
                        <input type="file" id="preview_image" name="preview_image" accept=".webp,image/webp" hidden>
                    </div>
                    <small class="text-warning fw-bold mt-1 d-block">
                        <i class="bi bi-exclamation-triangle me-1"></i>Upload Image (.webp only)
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Font File <span class="text-muted fw-normal">(.ttf, .otf)</span></label>
                    <div id="fontDropzone" class="dropzone-area">
                        <div id="fontEmpty">
                            <div class="dropzone-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                            <div class="dz-filename mb-1">Drag &amp; drop a file here or click</div>
                            <div class="dz-hint">.ttf or .otf only</div>
                        </div>
                        <div id="fontSelected" class="d-none w-100 d-flex flex-column align-items-center gap-2">
                            <i class="bi bi-file-earmark-font" style="font-size:2rem;color:#7048e8;"></i>
                            <div class="dz-filename" id="fontFilename"></div>
                            <button type="button" class="clear-file" id="clearFontBtn"><i class="bi bi-x"></i> Remove</button>
                        </div>
                        <input type="file" id="font_file" name="font_file" accept=".ttf,.otf" hidden>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-{{ isset($font) ? 'check-circle-fill' : 'plus-lg' }} me-2"></i>
                        {{ isset($font) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route('fonts.index') }}" data-back-to-list class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Preview image dropzone ---
            const previewDz       = document.getElementById('previewDropzone');
            const previewInput    = document.getElementById('preview_image');
            const previewEmptyEl  = document.getElementById('previewEmpty');
            const previewSelEl    = document.getElementById('previewSelected');
            const previewImgEl    = document.getElementById('previewImg');
            const previewNameEl   = document.getElementById('previewFilename');
            const clearPreviewBtn = document.getElementById('clearPreviewBtn');

            @if (isset($font) && $font->preview_image)
                previewImgEl.src = "{{ asset('upload/font/' . rawurlencode($font->font_name) . '/' . rawurlencode($font->preview_image)) }}";
                previewNameEl.textContent = @json($font->preview_image);
                previewEmptyEl.classList.add('d-none');
                previewSelEl.classList.remove('d-none');
            @endif

            function isWebp(file) {
                const nameOk = file.name && file.name.toLowerCase().endsWith('.webp');
                const typeOk = file.type === 'image/webp';
                return nameOk || typeOk;
            }

            function setPreviewFile(file) {
                if (!file) return;
                if (!isWebp(file)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid File Format',
                        text: 'Only .webp images are accepted. Please choose a .webp file.'
                    });
                    previewInput.value = '';
                    return;
                }
                const dt = new DataTransfer();
                dt.items.add(file);
                previewInput.files = dt.files;
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImgEl.src = e.target.result;
                    previewNameEl.textContent = file.name;
                    previewEmptyEl.classList.add('d-none');
                    previewSelEl.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }

            previewDz.addEventListener('click', (e) => {
                if (e.target.closest('.clear-file')) return;
                previewInput.click();
            });
            previewInput.addEventListener('change', () => { if (previewInput.files[0]) setPreviewFile(previewInput.files[0]); });

            ['dragenter','dragover'].forEach(ev => previewDz.addEventListener(ev, e => { e.preventDefault(); previewDz.classList.add('dragover'); }));
            ['dragleave','drop'].forEach(ev => previewDz.addEventListener(ev, e => { e.preventDefault(); previewDz.classList.remove('dragover'); }));
            previewDz.addEventListener('drop', e => { if (e.dataTransfer.files[0]) setPreviewFile(e.dataTransfer.files[0]); });

            clearPreviewBtn.addEventListener('click', () => {
                previewInput.value = '';
                previewImgEl.src = '';
                previewNameEl.textContent = '';
                previewSelEl.classList.add('d-none');
                previewEmptyEl.classList.remove('d-none');
            });

            // --- Font file dropzone ---
            const fontDz       = document.getElementById('fontDropzone');
            const fontInput    = document.getElementById('font_file');
            const fontEmptyEl  = document.getElementById('fontEmpty');
            const fontSelEl    = document.getElementById('fontSelected');
            const fontNameEl   = document.getElementById('fontFilename');
            const clearFontBtn = document.getElementById('clearFontBtn');

            @if (isset($font) && $font->font_file)
                fontNameEl.textContent = @json($font->font_file);
                fontEmptyEl.classList.add('d-none');
                fontSelEl.classList.remove('d-none');
            @endif

            function isFontFile(file) {
                const name = (file.name || '').toLowerCase();
                return name.endsWith('.ttf') || name.endsWith('.otf');
            }

            function setFontFile(file) {
                if (!file) return;
                if (!isFontFile(file)) {
                    Swal.fire({ icon: 'warning', title: 'Invalid file', text: 'Only .ttf or .otf files are allowed.' });
                    return;
                }
                const dt = new DataTransfer();
                dt.items.add(file);
                fontInput.files = dt.files;
                fontNameEl.textContent = file.name;
                fontEmptyEl.classList.add('d-none');
                fontSelEl.classList.remove('d-none');
            }

            fontDz.addEventListener('click', (e) => {
                if (e.target.closest('.clear-file')) return;
                fontInput.click();
            });
            fontInput.addEventListener('change', () => { if (fontInput.files[0]) setFontFile(fontInput.files[0]); });

            ['dragenter','dragover'].forEach(ev => fontDz.addEventListener(ev, e => { e.preventDefault(); fontDz.classList.add('dragover'); }));
            ['dragleave','drop'].forEach(ev => fontDz.addEventListener(ev, e => { e.preventDefault(); fontDz.classList.remove('dragover'); }));
            fontDz.addEventListener('drop', e => { if (e.dataTransfer.files[0]) setFontFile(e.dataTransfer.files[0]); });

            clearFontBtn.addEventListener('click', () => {
                fontInput.value = '';
                fontNameEl.textContent = '';
                fontSelEl.classList.add('d-none');
                fontEmptyEl.classList.remove('d-none');
            });

            @if($errors->any())
                Swal.fire({ icon: 'error', title: 'Validation Error', text: @json($errors->all()).join(' | ') });
            @elseif(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif
        });
    </script>
@endsection
