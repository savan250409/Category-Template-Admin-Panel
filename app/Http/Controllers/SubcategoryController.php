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
        $categories = ['Newborn Baby', 'Baby Bumps', 'Toddler Photoshoot', 'Festival Photoshoot', 'Birthday Photo', 'Unique Style', 'Invitation card'];

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
            'description' => 'nullable|string',
            'category_thumbnail_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $subcategory = $id ? Subcategory::findOrFail($id) : new Subcategory();
        $oldTitle = $id ? $subcategory->title : null;

        $subcategory->category_name = $request->category_name;
        $subcategory->title = $request->title;
        $subcategory->description = $request->description;

        $categoryFolder = $request->category_name;
        $subcategoryFolder = $request->title;

        // Rename folder if title changed
        if ($id && $oldTitle && $oldTitle !== $request->title) {
            $oldFolder = public_path('upload/' . $request->category_name . '/' . $oldTitle);
            $newFolder = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder);
            if (file_exists($oldFolder)) {
                rename($oldFolder, $newFolder);
            }
        }

        // Handle category thumbnail image
        if ($request->hasFile('category_thumbnail_image')) {
            $file = $request->file('category_thumbnail_image');
            $originalName = $file->getClientOriginalName();

            $thumbFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail");
            if (!is_dir($thumbFolder)) {
                mkdir($thumbFolder, 0755, true);
            }

            if ($id && $subcategory->category_thumbnail_image) {
                $oldPath = $thumbFolder . '/' . $subcategory->category_thumbnail_image;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file->move($thumbFolder, $originalName);
            $subcategory->category_thumbnail_image = $originalName;
        } elseif ($request->remove_thumbnail && $id) {
            if ($subcategory->category_thumbnail_image) {
                $oldPath = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail/" . $subcategory->category_thumbnail_image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $subcategory->category_thumbnail_image = null;
        }

        $subcategory->images = $subcategory->images ?? json_encode([]); // initialize empty JSON
        $subcategory->save();

        return redirect()
            ->route('subcategories.show', $subcategory->id)
            ->with('success', $id ? 'Subcategory updated!' : 'Subcategory created!');
    }

    public function show($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $imagesArray = json_decode($subcategory->images, true) ?? [];

        $imageUrls = array_map(function ($img) use ($subcategory) {
            return [
                'url' => asset('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . ($img['file'] ?? '')),
                'prompt' => $img['prompt'] ?? '',
                'image_title' => $img['image_title'] ?? '',
                'name_change' => $img['name_change'] ?? false,
            ];
        }, $imagesArray);

        return view('category-template.show', compact('subcategory', 'imageUrls'));
    }

    public function destroy($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $folderPath = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title);
        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }
        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'Subcategory and its images deleted successfully!');
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
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'prompts.*' => 'nullable|string|max:2555',
            'image_title.*' => 'nullable|string|max:255',
            'name_change_row.*' => 'nullable|boolean',
            'existing_prompts.*' => 'nullable|string|max:2555',
            'existing_image_title.*' => 'nullable|string|max:255',
            'remove_images' => 'nullable|array',
            'replace_images' => 'nullable|array',
        ]);

        $subcategory->description = $request->description;

        $uploadFolder = public_path("upload/{$subcategory->category_name}/{$subcategory->title}");
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0755, true);
        }

        $imagesData = $subcategory->images ? json_decode($subcategory->images, true) : [];

        // Remove selected images
        if ($request->remove_images) {
            foreach ($imagesData as $key => $img) {
                if (in_array($img['file'], $request->remove_images)) {
                    $filePath = $uploadFolder . '/' . $img['file'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    unset($imagesData[$key]);
                }
            }
            $imagesData = array_values($imagesData);
        }

        // Update existing images
        if ($imagesData && is_array($imagesData)) {
            foreach ($imagesData as $index => &$img) {
                $fileName = $img['file'];
                if ($request->existing_prompts[$fileName] ?? false) {
                    $img['prompt'] = $request->existing_prompts[$fileName];
                }
                if ($request->existing_image_title[$fileName] ?? false) {
                    $img['image_title'] = $request->existing_image_title[$fileName];
                }
            }
        }

        // Replace existing images
        if ($request->replace_images && is_array($request->replace_images)) {
            foreach ($request->replace_images as $oldFileName => $newFile) {
                if ($newFile && $newFile->isValid()) {
                    foreach ($imagesData as &$img) {
                        if ($img['file'] === $oldFileName) {
                            $oldPath = $uploadFolder . '/' . $oldFileName;
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                            $newFileName = $newFile->getClientOriginalName();
                            $newFile->move($uploadFolder, $newFileName);
                            $img['file'] = $newFileName;
                            break;
                        }
                    }
                }
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $prompt = $request->prompts[$index] ?? '';
                $imageTitle = $request->image_title[$index] ?? '';
                $nameChange = $request->name_change_row[$index] ?? false;

                $file->move($uploadFolder, $originalName);

                $imagesData[] = [
                    'file' => $originalName,
                    'prompt' => $prompt,
                    'image_title' => $imageTitle,
                    'name_change' => $nameChange ? true : false,
                ];
            }
        }

        $subcategory->images = !empty($imagesData) ? json_encode($imagesData) : null;
        $subcategory->save();

        return redirect()->route('subcategories.show', $subcategory->id)->with('success', 'Subcategory images & details saved successfully!');
    }
}
