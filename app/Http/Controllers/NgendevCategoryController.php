<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgendevCategory;
use App\Models\NgendevImage;
use Illuminate\Support\Facades\File;

class NgendevCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = intval($request->input('per_page', 10));
        $search = $request->input('search', '');

        $query = \App\Models\NgendevCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('sort_order', 'asc')->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('ngendev.categories.table', compact('categories', 'perPage', 'search'));
        }

        return view('ngendev.categories.index', compact('categories', 'perPage', 'search'));
    }

    public function create()
    {
        return view('ngendev.categories.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:ngendev_categories,category_name',
            'status' => 'boolean',
            'category_image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10000',
        ]);

        $categoryFolder = $request->category_name;
        $destinationPath = public_path("upload/ngendev/images/{$categoryFolder}/category_thumbnail_image");

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $images = [];

        if ($request->hasFile('category_image')) {
            foreach ($request->file('category_image') as $file) {
                $fileName = $file->getClientOriginalName();

                if (file_exists($destinationPath . '/' . $fileName)) {
                    $fileName = time() . '_' . $fileName;
                }

                $file->move($destinationPath, $fileName);
                $images[] = $fileName;
            }
        }

        NgendevCategory::create([
            'category_name' => $request->category_name,
            'status' => $request->has('status') ? $request->status : 1, // Default to 1 (Active)
            'category_image' => json_encode($images),
        ]);

        return redirect()->route('ngendev.categories.index')->with('success', 'Category added successfully with multiple original images!');
    }

    public function edit($id)
    {
        $category = NgendevCategory::findOrFail($id);
        return view('ngendev.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = NgendevCategory::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:255|unique:ngendev_categories,category_name,' . $id,
            'status' => 'boolean',
            'category_image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10000',
        ]);

        $oldName = $category->category_name;
        $newName = $request->category_name;

        // Rename folder if category name changed
        $oldFolder = public_path('upload/ngendev/images/' . $oldName);
        $newFolder = public_path('upload/ngendev/images/' . $newName);

        if ($oldName !== $newName && File::exists($oldFolder)) {
            File::move($oldFolder, $newFolder);
        } elseif (!File::exists($newFolder)) {
            File::makeDirectory($newFolder, 0777, true);
        }

        $destinationPath = $newFolder . '/category_thumbnail_image';
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        // Delete old images if new images uploaded
        if ($request->hasFile('category_image')) {
            if ($category->category_image) {
                $oldImages = json_decode($category->category_image, true);
                foreach ((array) $oldImages as $img) {
                    $oldFile = $destinationPath . '/' . $img;
                    if (File::exists($oldFile)) {
                        File::delete($oldFile);
                    }
                }
            }

            $images = [];
            foreach ($request->file('category_image') as $file) {
                $fileName = $file->getClientOriginalName();
                $file->move($destinationPath, $fileName);
                $images[] = $fileName;
            }
        } else {
            // If no new images uploaded, keep old images
            $images = $category->category_image ? json_decode($category->category_image, true) : [];
        }

        $category->update([
            'category_name' => $newName,
            'status' => $request->has('status') ? $request->status : 0, // Handles unchecked checkbox
            'category_image' => json_encode($images),
        ]);

        return redirect()->route('ngendev.categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = NgendevCategory::with('images')->findOrFail($id);

        $categoryFolder = $category->category_name;
        $folderPath = public_path("upload/ngendev/images/{$categoryFolder}");

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        if ($category->images) {
            foreach ($category->images as $image) {
                $image->delete();
            }
        }

        $category->delete();

        return redirect()->route('ngendev.categories.index')->with('success', 'Category and all images deleted successfully!');
    }

    public function indexing(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('ngendev.categories.index');
        }

        $categories = NgendevCategory::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return response()->json([
            'categories' => $categories
        ]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:ngendev_categories,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            NgendevCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category order updated successfully!',
            'redirect_url' => route('ngendev.categories.index'),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ngendev_categories,id',
            'status' => 'required|boolean',
        ]);

        $category = NgendevCategory::find($request->id);
        $category->status = $request->status;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }
}
