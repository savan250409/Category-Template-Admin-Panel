<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use File;

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
        $oldTitle = $id ? $subcategory->title : null;

        $subcategory->category_name = $request->category_name;
        $subcategory->title = $request->title;
        $subcategory->description = $request->description;

        if ($id && $oldTitle && $oldTitle !== $request->title) {
            $oldFolder = public_path('upload/' . $request->category_name . '/' . $oldTitle);
            $newFolder = public_path('upload/' . $request->category_name . '/' . $request->title);
            if (file_exists($oldFolder)) {
                rename($oldFolder, $newFolder);
            }
        }

        if ($request->hasFile('category_thumbnail_image')) {
            $folder = public_path('upload/' . $request->category_name . '/' . $request->title . '/category_thumbnail');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            if ($id && $subcategory->category_thumbnail_image) {
                $oldPath = $folder . '/' . $subcategory->category_thumbnail_image;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('category_thumbnail_image');
            $name = $file->getClientOriginalName();
            $file->move($folder, $name);
            $subcategory->category_thumbnail_image = $name;
        } elseif ($request->remove_thumbnail && $id) {
            $oldPath = public_path('upload/' . $request->category_name . '/' . $request->title . '/category_thumbnail/' . $subcategory->category_thumbnail_image);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $subcategory->category_thumbnail_image = null;
        }

        if ($id && $request->existing_prompts) {
            $imagesData = json_decode($subcategory->images, true) ?? [];
            foreach ($imagesData as $index => &$img) {
                if (isset($request->existing_prompts[$index])) {
                    $img['prompt'] = $request->existing_prompts[$index];
                }
                if ($request->remove_images && in_array($img['file'], $request->remove_images)) {
                    $path = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $img['file']);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    unset($imagesData[$index]);
                }
            }
            $subcategory->images = json_encode(array_values($imagesData));
        }

        $subcategory->save();
        return redirect()->route('subcategories.show', $subcategory->id)->with('success', 'Subcategory updated!');
    }

    public function show($id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $imagesArray = json_decode($subcategory->images, true) ?? [];

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

        $headerImage = $subcategory->header_image ? asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $subcategory->header_image) : null;

        return view('category-template.show', compact('subcategory', 'imageUrls', 'headerImage'));
    }

    public function destroy($id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $folderPath = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title);

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'Subcategory and its folder/images deleted successfully.');
    }

    public function addDetailsForm($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        return view('category-template.addDetailsForm', compact('subcategory'));
    }

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
