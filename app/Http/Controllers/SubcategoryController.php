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
            'category_thumbnail_image' => $id ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048' : 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $subcategory = $id ? Subcategory::findOrFail($id) : new Subcategory();
        $subcategory->category_name = $request->category_name;
        $subcategory->title = $request->title;

        // Handle Thumbnail
        if ($request->hasFile('category_thumbnail_image')) {
            $folderPath = public_path('upload/' . $request->category_name . '/' . $request->title . '/category_thumbnail');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            // Delete old thumbnail if exists
            if ($id && $subcategory->category_thumbnail_image) {
                $oldPath = $folderPath . '/' . $subcategory->category_thumbnail_image;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $thumbFile = $request->file('category_thumbnail_image');
            $thumbName = time() . '_' . $thumbFile->getClientOriginalName(); // unique name
            $thumbFile->move($folderPath, $thumbName);
            $subcategory->category_thumbnail_image = $thumbName;
        }

        $subcategory->save();

        return redirect()
            ->route('subcategories.index')
            ->with('success', $id ? 'Subcategory updated!' : 'Subcategory created!');
    }

    public function show($id)
    {
        $subcategory = Subcategory::findOrFail($id);

        // Decode images
        $imagesArray = json_decode($subcategory->images, true) ?? [];

        // Normalize images: make sure each image is ['file' => ..., 'prompt' => ...]
        $imageUrls = array_map(function ($img) use ($subcategory) {
            if (is_array($img)) {
                $file = $img['file'] ?? '';
                $prompt = $img['prompt'] ?? '';
            } else {
                $file = $img;
                $prompt = '';
            }

            return [
                'url' => asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $file),
                'prompt' => $prompt,
            ];
        }, $imagesArray);

        // Header image (optional)
        $headerImage = $subcategory->header_image ? asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $subcategory->header_image) : null;

        return view('category-template.show', compact('subcategory', 'imageUrls', 'headerImage'));
    }

    public function destroy($id)
    {
        $subcategory = Subcategory::findOrFail($id);

        if ($subcategory->images) {
            $images = json_decode($subcategory->images, true);

            foreach ($images as $img) {
                if (!empty($img['file'])) {
                    $path = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img['file']);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
        }

        $subcategory->delete();
        return redirect()->route('subcategories.index')->with('success', 'Subcategory deleted!');
    }

    // Show form for adding images & description
    public function addDetailsForm($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        return view('category-template.addDetailsForm', compact('subcategory'));
    }

    // Save images & description
    public function saveDetails(Request $request, $id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $request->validate([
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'prompts.*' => 'nullable|string|max:500',
        ]);

        $subcategory->description = $request->description;

        $imagesData = json_decode($subcategory->images, true) ?? [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $imgFile) {
                $folderPath = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title);
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                $fileName = $imgFile->getClientOriginalName();
                $imgFile->move($folderPath, $fileName);

                $imagesData[] = [
                    'file' => $fileName,
                    'prompt' => $request->prompts[$key] ?? '',
                ];
            }
        }

        $subcategory->images = json_encode($imagesData);
        $subcategory->save();

        return redirect()->route('subcategories.show', $id)->with('success', 'Details added successfully!');
    }
}
