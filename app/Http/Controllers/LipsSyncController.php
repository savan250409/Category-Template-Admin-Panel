<?php

namespace App\Http\Controllers;

use App\Models\LipsSyncCategory;
use App\Models\LipsSyncItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LipsSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->input('per_page', 10);
        $search     = $request->input('search', '');
        $categoryId = $request->input('category_id', '');

        $categories = LipsSyncCategory::orderBy('category_name')->get();

        $query = LipsSyncItem::with('category');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        if ($categoryId) {
            $query->where('lips_sync_category_id', $categoryId);
        }

        $items = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('lips_sync.items.table', compact('items'))->render(),
                'total' => $items->total(),
            ]);
        }

        return view('lips_sync.items.index', compact('items', 'categories', 'perPage', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = LipsSyncCategory::where('status', 1)->orderBy('category_name')->get();
        return view('lips_sync.items.form', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'lips_sync_category_id' => 'required|exists:lips_sync_categories,id',
                'title'                 => 'required|string|max:255',
                'song'                  => 'required|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/wave,audio/x-wav,audio/vnd.wave|max:20480',
                'video'                 => 'required|file|mimetypes:video/mp4,video/quicktime|max:51200',
                'video_thumbnail'       => 'nullable|string',
            ], [
                'song.mimetypes'  => 'Song must be an MP3 or WAV file.',
                'video.mimetypes' => 'Video must be an MP4 or MOV file.',
            ]);

            $category = LipsSyncCategory::findOrFail($request->lips_sync_category_id);
            $base     = 'upload/lips_sync/' . $category->category_name;

            $songFilename  = null;
            $videoFilename = null;
            $thumbFilename = null;

            // Song
            if ($request->hasFile('song')) {
                $file = $request->file('song');
                $path = public_path($base . '/song');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $songFilename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $songFilename);
            }

            // Video
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $path = public_path($base . '/video');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $videoFilename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $videoFilename);
            }

            // Thumbnail (base64 from client video frame extraction)
            if ($request->filled('video_thumbnail')) {
                $thumbFilename = $this->saveThumbnailFromBase64($request->video_thumbnail, $base);
            }

            LipsSyncItem::create([
                'lips_sync_category_id' => $category->id,
                'title'                 => $request->title,
                'song'                  => $songFilename,
                'video'                 => $videoFilename,
                'video_thumbnail'       => $thumbFilename,
            ]);

            return view('partials.history_redirect', ['fallback' => route('lips-sync.items.index'), 'message' => 'Lips Sync item added successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add item: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item       = LipsSyncItem::findOrFail($id);
        $categories = LipsSyncCategory::where('status', 1)->orderBy('category_name')->get();
        return view('lips_sync.items.form', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $item = LipsSyncItem::findOrFail($id);

            $request->validate([
                'lips_sync_category_id' => 'required|exists:lips_sync_categories,id',
                'title'                 => 'required|string|max:255',
                'song'                  => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/wave,audio/x-wav,audio/vnd.wave|max:20480',
                'video'                 => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:51200',
                'video_thumbnail'       => 'nullable|string',
            ], [
                'song.mimetypes'  => 'Song must be an MP3 or WAV file.',
                'video.mimetypes' => 'Video must be an MP4 or MOV file.',
            ]);

            $category = LipsSyncCategory::findOrFail($request->lips_sync_category_id);
            $oldCategoryName = $item->category ? $item->category->category_name : null;

            $item->lips_sync_category_id = $category->id;
            $item->title                 = $request->title;

            $base = 'upload/lips_sync/' . $category->category_name;
            $oldBase = $oldCategoryName ? 'upload/lips_sync/' . $oldCategoryName : $base;

            // Song
            if ($request->hasFile('song')) {
                if ($item->song && file_exists(public_path($oldBase . '/song/' . $item->song))) {
                    @unlink(public_path($oldBase . '/song/' . $item->song));
                }
                $file = $request->file('song');
                $path = public_path($base . '/song');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $item->song = $filename;
            }

            // Video
            if ($request->hasFile('video')) {
                if ($item->video && file_exists(public_path($oldBase . '/video/' . $item->video))) {
                    @unlink(public_path($oldBase . '/video/' . $item->video));
                }
                $file = $request->file('video');
                $path = public_path($base . '/video');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $item->video = $filename;
            }

            // Thumbnail
            if ($request->filled('video_thumbnail')) {
                if ($item->video_thumbnail && file_exists(public_path($oldBase . '/thumbnail/' . $item->video_thumbnail))) {
                    @unlink(public_path($oldBase . '/thumbnail/' . $item->video_thumbnail));
                }
                $thumbName = $this->saveThumbnailFromBase64($request->video_thumbnail, $base);
                if ($thumbName) {
                    $item->video_thumbnail = $thumbName;
                }
            }

            // Cleanup empty subfolders in the OLD category folder (relevant when category was changed)
            if ($oldBase !== $base) {
                $this->rmIfEmpty(public_path($oldBase . '/song'));
                $this->rmIfEmpty(public_path($oldBase . '/video'));
                $this->rmIfEmpty(public_path($oldBase . '/thumbnail'));
                $this->rmIfEmpty(public_path($oldBase));
            }

            $item->save();

            return view('partials.history_redirect', ['fallback' => route('lips-sync.items.index'), 'message' => 'Lips Sync item updated successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $item = LipsSyncItem::findOrFail($id);
        $category = $item->category;

        if ($category) {
            $base = 'upload/lips_sync/' . $category->category_name;
            if ($item->song && file_exists(public_path($base . '/song/' . $item->song))) {
                @unlink(public_path($base . '/song/' . $item->song));
            }
            if ($item->video && file_exists(public_path($base . '/video/' . $item->video))) {
                @unlink(public_path($base . '/video/' . $item->video));
            }
            if ($item->video_thumbnail && file_exists(public_path($base . '/thumbnail/' . $item->video_thumbnail))) {
                @unlink(public_path($base . '/thumbnail/' . $item->video_thumbnail));
            }

            // Clean up empty subfolders
            $this->rmIfEmpty(public_path($base . '/song'));
            $this->rmIfEmpty(public_path($base . '/video'));
            $this->rmIfEmpty(public_path($base . '/thumbnail'));
            $this->rmIfEmpty(public_path($base));
        }

        $item->delete();

        return redirect()->route('lips-sync.items.index')->with('success', 'Lips Sync item deleted successfully!');
    }

    private function rmIfEmpty(string $folder): void
    {
        if (!is_dir($folder)) return;
        $items = @scandir($folder);
        $items = array_diff($items ?: [], ['.', '..']);
        if (empty($items)) {
            @rmdir($folder);
        }
    }

    private function uniqueFilename(string $folder, string $originalName): string
    {
        if (!file_exists($folder . DIRECTORY_SEPARATOR . $originalName)) {
            return $originalName;
        }
        $info = pathinfo($originalName);
        $base = $info['filename'] ?? $originalName;
        $ext  = isset($info['extension']) ? '.' . $info['extension'] : '';
        return $base . '_' . time() . $ext;
    }

    private function saveThumbnailFromBase64($dataUrl, $base)
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $m)) {
            return null;
        }
        $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $payload = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary  = base64_decode($payload);
        if ($binary === false) return null;

        $path = public_path($base . '/thumbnail');
        if (!File::exists($path)) File::makeDirectory($path, 0777, true);

        $filename = time() . '_thumb.' . $ext;
        file_put_contents($path . '/' . $filename, $binary);
        return $filename;
    }
}
