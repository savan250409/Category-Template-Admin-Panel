@extends('partials.layout')
@section('title', 'Add Details to ' . $subcategory->title)
@section('container')

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-gradient-primary text-white">
                    Add Images & Description for {{ $subcategory->title }}
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('subcategories.saveDetails', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="4" class="form-control">{{ old('description', $subcategory->description) }}</textarea>
                        </div>

                        {{-- Images with prompts --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Add Images & Prompts</label>
                            <div id="newImagesWrapper"></div>
                            <button type="button" id="addImageBtn" class="btn btn-outline-primary btn-sm mt-2">+ Add Image</button>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-success px-4">Save</button>
                            <a href="{{ route('subcategories.show', $subcategory->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('newImagesWrapper');
    const addBtn = document.getElementById('addImageBtn');

    addBtn.addEventListener('click', function() {
        const div = document.createElement('div');
        div.classList.add('d-flex', 'gap-2', 'align-items-start', 'mb-2');
        div.innerHTML = `
            <input type="file" name="images[]" accept="image/*" class="form-control w-50" required>
            <input type="text" name="prompts[]" placeholder="Enter prompt" class="form-control w-50">
            <button type="button" class="btn btn-danger btn-sm remove-new">X</button>
        `;
        wrapper.appendChild(div);
    });

    wrapper.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-new')) {
            e.target.closest('div').remove();
        }
    });
});
</script>

@endsection
