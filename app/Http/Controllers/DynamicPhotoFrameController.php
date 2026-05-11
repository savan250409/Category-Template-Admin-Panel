<?php

namespace App\Http\Controllers;

use App\Models\DynamicPhotoFrameCategory;
use App\Models\DynamicPhotoFrame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DynamicPhotoFrameController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->input('per_page', 10);
        $search     = $request->input('search', '');
        $categoryId = $request->input('category_id', '');

        $categories = DynamicPhotoFrameCategory::orderBy('category_name')->get();

        $query = DynamicPhotoFrame::with('category');

        if ($search) {
            $query->whereHas('category', function ($q) use ($search) {
                $q->where('category_name', 'like', '%' . $search . '%');
            });
        }
        if ($categoryId) {
            $query->where('dynamic_photo_frame_category_id', $categoryId);
        }

        $frames = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('dynamic_photo_frame.frames.table', compact('frames'))->render(),
                'total' => $frames->total(),
            ]);
        }

        return view('dynamic_photo_frame.frames.index', compact('frames', 'categories', 'perPage', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = DynamicPhotoFrameCategory::where('status', 1)->orderBy('category_name')->get();
        return view('dynamic_photo_frame.frames.form', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'dynamic_photo_frame_category_id' => 'required|exists:dynamic_photo_frame_categories,id',
                'zip_file'                        => 'required|file|mimes:zip|max:102400',
                'input_count'                     => 'required|integer|min:1',
                'thumbnail'                       => 'required|image|mimes:webp|max:10000',
            ], [
                'zip_file.mimes'  => 'File must be a .zip file.',
                'thumbnail.mimes' => 'Thumbnail must be a .webp image.',
            ]);

            $category = DynamicPhotoFrameCategory::findOrFail($request->dynamic_photo_frame_category_id);
            $base     = 'upload/dynamic_photo_frame/' . $category->category_name;

            $zipFilename   = null;
            $thumbFilename = null;

            if ($request->hasFile('zip_file')) {
                $file = $request->file('zip_file');
                $path = public_path($base . '/zip');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $zipFilename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $zipFilename);
            }

            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $path = public_path($base . '/thumbnail');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $thumbFilename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $thumbFilename);
            }

            DynamicPhotoFrame::create([
                'dynamic_photo_frame_category_id' => $category->id,
                'zip_file'                        => $zipFilename,
                'input_count'                     => (int) $request->input_count,
                'thumbnail'                       => $thumbFilename,
            ]);

            return redirect()->route('dynamic-photo-frame.frames.index')->with('success', 'Dynamic Photo Frame added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add frame: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $frame      = DynamicPhotoFrame::findOrFail($id);
        $categories = DynamicPhotoFrameCategory::where('status', 1)->orderBy('category_name')->get();
        return view('dynamic_photo_frame.frames.form', compact('frame', 'categories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $frame = DynamicPhotoFrame::findOrFail($id);

            $request->validate([
                'dynamic_photo_frame_category_id' => 'required|exists:dynamic_photo_frame_categories,id',
                'zip_file'                        => 'nullable|file|mimes:zip|max:102400',
                'input_count'                     => 'required|integer|min:1',
                'thumbnail'                       => 'nullable|image|mimes:webp|max:10000',
            ], [
                'zip_file.mimes'  => 'File must be a .zip file.',
                'thumbnail.mimes' => 'Thumbnail must be a .webp image.',
            ]);

            $category        = DynamicPhotoFrameCategory::findOrFail($request->dynamic_photo_frame_category_id);
            $oldCategoryName = $frame->category ? $frame->category->category_name : null;

            $frame->dynamic_photo_frame_category_id = $category->id;
            $frame->input_count                     = (int) $request->input_count;

            $base    = 'upload/dynamic_photo_frame/' . $category->category_name;
            $oldBase = $oldCategoryName ? 'upload/dynamic_photo_frame/' . $oldCategoryName : $base;

            if ($request->hasFile('zip_file')) {
                if ($frame->zip_file && file_exists(public_path($oldBase . '/zip/' . $frame->zip_file))) {
                    @unlink(public_path($oldBase . '/zip/' . $frame->zip_file));
                }
                $file = $request->file('zip_file');
                $path = public_path($base . '/zip');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $frame->zip_file = $filename;
            }

            if ($request->hasFile('thumbnail')) {
                if ($frame->thumbnail && file_exists(public_path($oldBase . '/thumbnail/' . $frame->thumbnail))) {
                    @unlink(public_path($oldBase . '/thumbnail/' . $frame->thumbnail));
                }
                $file = $request->file('thumbnail');
                $path = public_path($base . '/thumbnail');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $frame->thumbnail = $filename;
            }

            if ($oldBase !== $base) {
                $this->rmIfEmpty(public_path($oldBase . '/zip'));
                $this->rmIfEmpty(public_path($oldBase . '/thumbnail'));
                $this->rmIfEmpty(public_path($oldBase));
            }

            $frame->save();

            return redirect()->route('dynamic-photo-frame.frames.index')->with('success', 'Dynamic Photo Frame updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update frame: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $frame    = DynamicPhotoFrame::findOrFail($id);
        $category = $frame->category;

        if ($category) {
            $base = 'upload/dynamic_photo_frame/' . $category->category_name;
            if ($frame->zip_file && file_exists(public_path($base . '/zip/' . $frame->zip_file))) {
                @unlink(public_path($base . '/zip/' . $frame->zip_file));
            }
            if ($frame->thumbnail && file_exists(public_path($base . '/thumbnail/' . $frame->thumbnail))) {
                @unlink(public_path($base . '/thumbnail/' . $frame->thumbnail));
            }

            $this->rmIfEmpty(public_path($base . '/zip'));
            $this->rmIfEmpty(public_path($base . '/thumbnail'));
            $this->rmIfEmpty(public_path($base));
        }

        $frame->delete();

        return redirect()->route('dynamic-photo-frame.frames.index')->with('success', 'Dynamic Photo Frame deleted successfully!');
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
}
