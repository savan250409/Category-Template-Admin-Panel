<?php

namespace App\Http\Controllers;

use App\Models\StickerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class StickerCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = StickerCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('sticker.categories.table', compact('categories'))->render(),
                'total' => $categories->total(),
            ]);
        }

        return view('sticker.categories.index', compact('categories', 'perPage', 'search'));
    }

    public function create()
    {
        return view('sticker.categories.form');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255|unique:sticker_categories,category_name',
                'image'         => 'nullable|image|mimes:webp|max:10000',
            ], [
                'image.mimes' => 'Thumbnail must be a .webp image.',
            ]);

            $minOrder = StickerCategory::min('sort_order');
            $minOrder = is_null($minOrder) ? 0 : (int) $minOrder;

            $category = new StickerCategory();
            $category->category_name = $request->category_name;
            $category->is_premium    = $request->has('is_premium') ? 1 : 0;
            $category->status        = $request->has('status') ? 1 : 0;
            $category->sort_order    = $minOrder - 1;
            $category->save();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = public_path('upload/sticker/' . $category->category_name . '/category image');
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $category->image = $filename;
                $category->save();
            }

            return redirect()->route('sticker.categories.index')->with('success', 'Sticker Category created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $category = StickerCategory::findOrFail($id);
        return view('sticker.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        try {
            $category = StickerCategory::findOrFail($id);

            $request->validate([
                'category_name' => 'required|string|max:255|unique:sticker_categories,category_name,' . $category->id,
                'image'         => 'nullable|image|mimes:webp|max:10000',
            ], [
                'image.mimes' => 'Thumbnail must be a .webp image.',
            ]);

            $oldName = $category->category_name;
            $category->category_name = $request->category_name;
            $category->is_premium    = $request->has('is_premium') ? 1 : 0;
            $category->status        = $request->has('status') ? 1 : 0;

            if ($oldName !== $category->category_name) {
                $oldFolder = public_path('upload/sticker/' . $oldName);
                $newFolder = public_path('upload/sticker/' . $category->category_name);
                if (File::exists($oldFolder) && !File::exists($newFolder)) {
                    @rename($oldFolder, $newFolder);
                }
            }

            if ($request->hasFile('image')) {
                $oldImagePath = public_path('upload/sticker/' . $category->category_name . '/category image/' . $category->image);
                if ($category->image && file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }

                $file = $request->file('image');
                $path = public_path('upload/sticker/' . $category->category_name . '/category image');
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true);
                }
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $category->image = $filename;
            }

            $category->save();

            $this->rmIfEmpty(public_path('upload/sticker/' . $category->category_name . '/category image'));
            $this->rmIfEmpty(public_path('upload/sticker/' . $category->category_name));

            return redirect()->route('sticker.categories.index')->with('success', 'Sticker Category updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $category = StickerCategory::findOrFail($id);

        $folder = public_path('upload/sticker/' . $category->category_name);
        if (File::exists($folder)) {
            File::deleteDirectory($folder);
        }

        $category->delete();

        return redirect()->route('sticker.categories.index')->with('success', 'Sticker Category and all its stickers deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $category = StickerCategory::findOrFail($request->id);
        $category->status = $request->status ? 1 : 0;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully!', 'status' => $category->status]);
    }

    public function togglePremium(Request $request)
    {
        $category = StickerCategory::findOrFail($request->id);
        $category->is_premium = $request->is_premium ? 1 : 0;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Premium updated successfully!', 'is_premium' => $category->is_premium]);
    }

    public function indexing(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('sticker.categories.index');
        }

        $categories = StickerCategory::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $data = $categories->map(function ($category) {
            return [
                'id'            => $category->id,
                'category_name' => $category->category_name,
                'image_url'     => $category->image
                    ? asset('upload/sticker/' . rawurlencode($category->category_name) . '/category image/' . rawurlencode($category->image))
                    : null,
            ];
        });

        return response()->json(['categories' => $data]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order'              => 'required|array',
            'order.*.id'         => 'required|exists:sticker_categories,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            StickerCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully!']);
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

}
