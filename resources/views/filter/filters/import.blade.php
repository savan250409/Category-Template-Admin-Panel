@extends('partials.layout')
@section('title', 'Import Filters From CSV')
@section('container')
    <style>
        .form-card { background-color: #fff; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); padding: 1.5rem; margin-bottom: 2rem; max-width: 800px; margin-left:auto; margin-right:auto; }
        .file-picker { display: flex; align-items: stretch; border: 1px solid #e3e6f0; border-radius: .35rem; overflow: hidden; }
        .file-picker .file-name { flex: 1; padding: .65rem 1rem; color: #6c757d; display: flex; align-items: center; }
        .file-picker.has-file .file-name { color: #2c3e50; }
        .upload-btn { background: linear-gradient(135deg, #c06aef 0%, #7048e8 100%); color: #fff; border: none; padding: 0 1.5rem; font-weight: 700; cursor: pointer; }
        .csv-help { font-size:.85rem; color:#6c757d; margin-top:.5rem; }
        .csv-help code { background:#f3eaff; color:#7048e8; padding:2px 6px; border-radius:4px; font-size:.8rem; }
    </style>

    <div class="container mt-4 mb-5">
        <div class="form-card">
            <h4 class="mb-1">Import Filters From CSV</h4>
            <p class="text-muted mb-4">Upload a CSV file to import filters.</p>

            <form method="POST" action="{{ route('filter.filters.importCsv') }}" enctype="multipart/form-data" id="importForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">File upload</label>
                    <div class="file-picker" id="filePicker">
                        <div class="file-name" id="fileName">Upload CSV</div>
                        <button type="button" class="upload-btn" id="chooseBtn">Upload</button>
                        <input type="file" id="csv" name="csv" accept=".csv,text/csv" hidden required>
                    </div>
                    <div class="csv-help">
                        Expected columns (any order, case-insensitive):
                        <code>category</code>, <code>filter_name</code>, <code>type</code> (pro/free),
                        <code>saturation</code>, <code>brightness</code>, <code>contrast</code>,
                        <code>red</code>, <code>green</code>, <code>blue</code>.
                        Missing categories will be auto-created.
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">
                        <i class="bi bi-cloud-upload me-2"></i>Import
                    </button>
                    <a href="{{ route('filter.filters.index') }}" class="btn btn-light py-2 px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csv = document.getElementById('csv');
            const chooseBtn = document.getElementById('chooseBtn');
            const fileName = document.getElementById('fileName');
            const filePicker = document.getElementById('filePicker');

            chooseBtn.addEventListener('click', () => csv.click());
            csv.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    filePicker.classList.add('has-file');
                }
            });

            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')) });
            @endif
        });
    </script>
@endsection
