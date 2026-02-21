@if ($videos->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-camera-video"></i>
        </div>
        <h4 class="empty-state-title">No Videos Found</h4>
        <p class="empty-state-text">No videos match your current search criteria.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>AI Prompt</th>
                    <th>Name Change</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($videos as $video)
                    <tr id="row-{{ $video->id }}">
                        <td><strong>{{ $video->category->category_name ?? 'N/A' }}</strong></td>
                        <td>
                            @if($video->video_thumbnail)
                                @if(Str::startsWith($video->video_thumbnail, 'upload/'))
                                    <img src="{{ asset($video->video_thumbnail) }}" alt="Thumbnail" width="100">
                                @else
                                    <img src="{{ asset('upload/AI Baby Video/' . ($video->category->category_name ?? 'Unknown') . '/video thumbanail/' . $video->video_thumbnail) }}"
                                        alt="Thumbnail" width="100">
                                @endif
                            @else
                                <span class="text-muted">No Thumbnail</span>
                            @endif
                        </td>
                        <td>{{ $video->video_title }}</td>
                        <td>{{ Str::limit($video->ai_prompt, 50) }}</td>
                        <td>
                            @if($video->name_change)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('ai-baby-video.videos.edit', $video->id) }}" class="action-btn edit-btn"
                                    data-bs-toggle="tooltip" title="Edit Video">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form id="deleteForm-{{ $video->id }}"
                                    action="{{ route('ai-baby-video.videos.destroy', $video->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="action-btn delete-btn" data-id="{{ $video->id }}"
                                        data-title="Video {{ $video->id }}" onclick="confirmDelete(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $videos->firstItem() }} to {{ $videos->lastItem() }} of {{ $videos->total() }}
            entries
        </div>
        {{ $videos->links() }}
    </div>
@endif