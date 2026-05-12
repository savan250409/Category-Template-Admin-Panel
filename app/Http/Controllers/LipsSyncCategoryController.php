<?php

namespace App\Http\Controllers;

use App\Models\LipsSyncCategory;
use App\Models\LipsSyncItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LipsSyncCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = LipsSyncCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('lips_sync.categories.table', compact('categories'))->render(),
                'total' => $categories->total(),
            ]);
        }

        return view('lips_sync.categories.index', compact('categories', 'perPage', 'search'));
    }

    public function create()
    {
        return view('lips_sync.categories.form');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255|unique:lips_sync_categories,category_name',
                'image'         => 'nullable|image|mimes:webp|max:10000',
            ]);

            $category = new LipsSyncCategory();
            $category->category_name = $request->category_name;
            $category->status        = 1;
            $category->save();

            if ($request->hasFile('image')) {
                $file     = $request->file('image');
                $path     = public_path('upload/lips_sync/' . $category->category_name . '/category image');
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $category->image = $filename;
                $category->save();
            }

            return redirect()->route('lips-sync.categories.index')->with('success', 'Lips Sync Category created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $category = LipsSyncCategory::findOrFail($id);
        return view('lips_sync.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        try {
            $category = LipsSyncCategory::findOrFail($id);

            $request->validate([
                'category_name' => 'required|string|max:255|unique:lips_sync_categories,category_name,' . $category->id,
                'image'         => 'nullable|image|mimes:webp|max:10000',
            ]);

            $oldName = $category->category_name;
            $category->category_name = $request->category_name;

            // Rename items folder if category name changed (so song/video/thumbnail/category image stay together)
            if ($oldName !== $category->category_name) {
                $oldItemsFolder = public_path('upload/lips_sync/' . $oldName);
                $newItemsFolder = public_path('upload/lips_sync/' . $category->category_name);
                if (File::exists($oldItemsFolder) && !File::exists($newItemsFolder)) {
                    @rename($oldItemsFolder, $newItemsFolder);
                }
            }

            if ($request->hasFile('image')) {
                $oldImagePath = public_path('upload/lips_sync/' . $category->category_name . '/category image/' . $category->image);
                if ($category->image && file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }

                $file     = $request->file('image');
                $path     = public_path('upload/lips_sync/' . $category->category_name . '/category image');
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $category->image = $filename;
            }

            $category->save();

            // Clean up empty folders if the category-image folder ended up with less than 1 image
            $this->rmIfEmpty(public_path('upload/lips_sync/' . $category->category_name . '/category image'));
            $this->rmIfEmpty(public_path('upload/lips_sync/' . $category->category_name));

            return redirect()->route('lips-sync.categories.index')->with('success', 'Lips Sync Category updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $category = LipsSyncCategory::findOrFail($id);

        // Delete items folder (which now also contains the category image)
        $itemFolder = public_path('upload/lips_sync/' . $category->category_name);
        if (File::exists($itemFolder)) {
            File::deleteDirectory($itemFolder);
        }

        // Items will be cascaded via FK, but ensure cleanup
        LipsSyncItem::where('lips_sync_category_id', $id)->delete();

        $category->delete();

        return redirect()->route('lips-sync.categories.index')->with('success', 'Lips Sync Category and all its items deleted successfully!');
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
