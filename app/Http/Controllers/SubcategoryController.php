<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::latest()->paginate(10);
        return view('category-template.index', compact('subcategories'));
    }

    public function form(Request $request, $id = null)
    {
        $subcategory = $id ? Subcategory::findOrFail($id) : new Subcategory();
        $categories = ['Newborn Baby', 'Baby Bumps', 'Toddler (1–3 Years Old)', 'Festival Frames', 'Birthday Photo', 'Unique Style', 'Invitation card'];

        if (!$id && $request->has('category_name')) {
            $subcategory->category_name = $request->get('category_name');
        }

        return view('category-template.form', compact('subcategory', 'categories'));
    }

    public function save(Request $request, $id = null)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'images.*' => $id ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048' : 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'existing_images.*' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $subcategory = $id ? Subcategory::findOrFail($id) : new Subcategory();
        $subcategory->category_name = $request->category_name;
        $subcategory->title = $request->title;
        $subcategory->description = $request->description;

        // Merge existing images
        $existingImages = $request->existing_images ?? [];

        // Handle new uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = $image->getClientOriginalName();

                $folderPath = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title);
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                $image->move($folderPath, $imageName);
                $existingImages[] = $imageName;
            }
        }

        $subcategory->images = json_encode($existingImages);
        $subcategory->save();

        // Redirect back to category detail page instead of index
        return redirect()
            ->route('subcategories.show', $subcategory->id)
            ->with('success', $id ? 'Subcategory updated!' : 'Subcategory created!');
    }

    public function show($id)
    {
        $subcategory = Subcategory::findOrFail($id);

        // Decode images
        $imagesArray = json_decode($subcategory->images, true) ?? [];

        // Generate image URLs
        $imageUrls = array_map(function ($img) use ($subcategory) {
            return asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img);
        }, $imagesArray);

        // Header image
        $headerImage = $subcategory->header_image ? asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $subcategory->header_image) : null;

        return view('category-template.show', compact('subcategory', 'imageUrls', 'headerImage'));
    }

    public function destroy($id)
    {
        $subcategory = Subcategory::findOrFail($id);

        if ($subcategory->images) {
            $images = json_decode($subcategory->images, true);
            foreach ($images as $img) {
                $path = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        $subcategory->delete();
        return redirect()->route('subcategories.index')->with('success', 'Subcategory deleted!');
    }
}
