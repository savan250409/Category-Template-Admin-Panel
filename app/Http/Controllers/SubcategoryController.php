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
        ]);

        $subcategory = $id ? Subcategory::findOrFail($id) : new Subcategory();
        $oldTitle = $id ? $subcategory->title : null;

        $subcategory->category_name = $request->category_name;
        $subcategory->title = $request->title;
        $subcategory->description = $request->description;

        $categoryFolder = $request->category_name;
        $subcategoryFolder = $request->title;

        if ($id && $oldTitle && $oldTitle !== $request->title) {
            $oldFolder = public_path('upload/' . $request->category_name . '/' . $oldTitle);
            $newFolder = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder);
            if (file_exists($oldFolder)) {
                rename($oldFolder, $newFolder);
            }
        }

        if ($request->hasFile('category_thumbnail_image')) {
            $file = $request->file('category_thumbnail_image');
            $originalName = $file->getClientOriginalName();

            $thumbFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail");
            if (!is_dir($thumbFolder)) {
                mkdir($thumbFolder, 0755, true);
            }

            if ($id && $subcategory->category_thumbnail_image) {
                $oldPath = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail/" . $subcategory->category_thumbnail_image);
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

        $imagesData = $subcategory->images ? json_decode($subcategory->images, true) : [];

        $uploadFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}");
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0755, true);
        }

        if ($request->remove_images && is_array($request->remove_images)) {
            foreach ($request->remove_images as $fileToRemove) {
                $filePath = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/" . $fileToRemove);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $imagesData = array_filter($imagesData, function ($img) use ($fileToRemove) {
                    return $img['file'] !== $fileToRemove;
                });
            }
            $imagesData = array_values($imagesData);
        }

        if ($request->existing_prompts && is_array($request->existing_prompts)) {
            foreach ($imagesData as &$img) {
                if (isset($request->existing_prompts[$img['file']])) {
                    $img['prompt'] = $request->existing_prompts[$img['file']];
                }
            }
        }

        if ($request->replace_images && is_array($request->replace_images)) {
            foreach ($request->replace_images as $oldFileName => $newFile) {
                if ($newFile && $newFile->isValid()) {
                    foreach ($imagesData as &$img) {
                        if ($img['file'] === $oldFileName) {
                            $oldPath = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/" . $oldFileName);

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

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $prompt = $request->prompts[$index] ?? '';

                    $file->move($uploadFolder, $originalName);

                    $imagesData[] = [
                        'file' => $originalName,
                        'prompt' => $prompt,
                    ];
                }
            }
        }

        $subcategory->images = !empty($imagesData) ? json_encode($imagesData) : null;

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

        $subcategory->images = null;
        $subcategory->category_thumbnail_image = null;

        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'Subcategory, its images & prompts deleted successfully!');
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
            'prompts.*' => 'nullable|string|max:255',
        ]);

        $subcategory->description = $request->description;

        $uploadFolder = public_path("upload/{$subcategory->category_name}/{$subcategory->title}");
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0755, true);
        }

        $imagesData = $subcategory->images ? json_decode($subcategory->images, true) : [];

        if ($request->remove_images) {
            foreach ($imagesData as $key => $img) {
                if (in_array($img['file'], $request->remove_images)) {
                    $originalPath = $uploadFolder . '/' . $img['file'];
                    if (file_exists($originalPath)) {
                        unlink($originalPath);
                    }
                    unset($imagesData[$key]);
                }
            }
            $imagesData = array_values($imagesData);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $prompt = $request->prompts[$index] ?? null;
                $originalName = $file->getClientOriginalName();

                $file->move($uploadFolder, $originalName);

                $imagesData[] = [
                    'file' => $originalName,
                    'prompt' => $prompt,
                ];
            }
        }

        $subcategory->images = json_encode($imagesData);

        $subcategory->save();

        return redirect()->route('subcategories.show', $subcategory->id)->with('success', 'Subcategory images & details saved successfully!');
    }
}
