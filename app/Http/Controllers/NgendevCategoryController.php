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

        $categories = $query->latest()->paginate($perPage)->withQueryString();

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
            'category_image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'category_image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $oldName = $category->category_name;
        $newName = $request->category_name;

        // Rename folder if category name changed
        $oldFolder = public_path('upload/ngendev/images/' . str_replace(' ', '_', $oldName));
        $newFolder = public_path('upload/ngendev/images/' . str_replace(' ', '_', $newName));

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
}
