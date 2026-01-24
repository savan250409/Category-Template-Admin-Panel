@extends('partials.layout')
@section('title', 'Add Details to ' . $subcategory->title)
@section('container')

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-gradient-primary text-black">
                        Add {{ request('origin') === 'video' ? 'Videos' : 'Images' }} & Description for {{ $subcategory->title }}
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('subcategories.saveDetails', $subcategory->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="origin" value="{{ request('origin') }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" rows="4" class="form-control">{{ old('description', $subcategory->description) }}</textarea>
                            </div>

                            @if ((request('origin') === 'video' && $subcategory->videos) || (request('origin') !== 'video' && $subcategory->images))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Existing {{ request('origin') === 'video' ? 'Videos' : 'Images' }}</label>
                                    <div class="row g-3" id="existingImagesWrapper">
                                        @php
                                            $existingData = request('origin') === 'video' ? $subcategory->videos : $subcategory->images;
                                            $existingItems = json_decode($existingData, true) ?? [];
                                        @endphp
                                        @foreach ($existingItems as $img)
                                            <div class="col-md-4 image-block">
                                                <div class="card">
                                                    @if(request('origin') === 'video')
                                                        <video class="card-img-top" style="height:200px; object-fit:cover;" controls>
                                                            <source src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img['file']) }}" type="video/{{ strtolower(pathinfo($img['file'], PATHINFO_EXTENSION)) }}">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @else
                                                        <img src="{{ asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img['file']) }}"
                                                            class="card-img-top" alt="Image">
                                                    @endif
                                                    <div class="card-body p-2">
                                                        <div class="mb-1">
                                                            <label class="small text-muted">Prompt</label>
                                                            <input type="text"
                                                                name="existing_prompts[{{ $img['file'] }}]"
                                                                value="{{ $img['prompt'] ?? '' }}"
                                                                class="form-control form-control-sm">
                                                        </div>
                                                        <div class="mb-1">
                                                            <label class="small text-muted">{{ request('origin') === 'video' ? 'Video' : 'Image' }} Title</label>
                                                            <input type="text"
                                                                name="{{ request('origin') === 'video' ? 'existing_video_title' : 'existing_image_title' }}[{{ $img['file'] }}]"
                                                                value="{{ $img['video_title'] ?? ($img['image_title'] ?? '') }}"
                                                                class="form-control form-control-sm">
                                                        </div>
                                                        <div class="form-check form-switch mb-1">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="existing_name_change[{{ $img['file'] }}]"
                                                                value="1"
                                                                {{ !empty($img['name_change']) ? 'checked' : '' }}>
                                                            <label class="form-check-label">Name Change</label>
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" name="remove_images[]"
                                                                value="{{ $img['file'] }}">
                                                            <label class="form-check-label">Remove</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Add New {{ request('origin') === 'video' ? 'Videos' : 'Images' }}</label>
                                <div id="newImagesWrapper"></div>
                                <button type="button" id="addImageBtn" class="btn btn-outline-primary btn-sm mt-2">+ Add
                                    {{ request('origin') === 'video' ? 'Video' : 'Image' }}</button>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success px-4">Save</button>
                                <a href="{{ route('subcategories.show', $subcategory->id) }}"
                                    class="btn btn-outline-secondary px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const origin = '{{ request('origin') }}';
            const wrapper = document.getElementById('newImagesWrapper');
            const addBtn = document.getElementById('addImageBtn');

            addBtn.addEventListener('click', function() {
                const div = document.createElement('div');
                div.classList.add('d-flex', 'gap-2', 'align-items-start', 'mb-2');

                 div.innerHTML = `
            <div class="flex-grow-1">
                <label class="small text-muted">${origin === 'video' ? 'Video' : 'Image'}</label>
                <input type="file" name="images[]" accept="${origin === 'video' ? 'video/*' : 'image/*'}" class="form-control" required>
            </div>
            <div class="flex-grow-1">
                <label class="small text-muted">Prompt</label>
                <input type="text" name="prompts[]" placeholder="Enter prompt" class="form-control">
            </div>
            <div class="flex-grow-1">
                <label class="small text-muted">${origin === 'video' ? 'Video' : 'Image'} Title</label>
                <input type="text" name="${origin === 'video' ? 'video_title' : 'image_title'}[]" placeholder="Enter ${origin === 'video' ? 'video' : 'image'} title" class="form-control">
            </div>
            <div class="flex-grow-1">
                <label class="small text-muted">Name Change</label>
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="name_change[]" value="1">
                    <label class="form-check-label">Enable</label>
                </div>
            </div>
            <div class="d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-new">X</button>
            </div>
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
