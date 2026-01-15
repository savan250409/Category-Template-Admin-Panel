<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $oldThumbnail = $id ? $subcategory->category_thumbnail_image : null;
        $oldCategory = $id ? $subcategory->category_name : null;

        $subcategory->category_name = $request->category_name;
        $subcategory->title = $request->title;
        $subcategory->description = $request->description;
        $subcategory->trending = $request->has('trending') ? 1 : 0;

        $categoryFolder = $request->category_name;
        $subcategoryFolder = $request->title;

        // Handle folder rename if category or title changed
        if ($id && $oldTitle && ($oldTitle !== $request->title || $oldCategory !== $request->category_name)) {
            $oldFolder = public_path('upload/' . $oldCategory . '/' . $oldTitle);
            $newFolder = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder);
            $newFolderParent = dirname($newFolder);

            if (!is_dir($newFolderParent)) {
                mkdir($newFolderParent, 0755, true);
            }

            if (file_exists($oldFolder)) {
                rename($oldFolder, $newFolder);
            }
        }

        // Create thumbnail folder if it doesn't exist
        $thumbFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail");
        if (!is_dir($thumbFolder)) {
            mkdir($thumbFolder, 0755, true);
        }

        // Handle thumbnail image upload
        if ($request->hasFile('category_thumbnail_image')) {
            $file = $request->file('category_thumbnail_image');
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $generatedName = Str::slug($baseName) ?: 'thumbnail';
            $originalName = $generatedName . '-' . time() . '-' . Str::random(6) . '.' . $extension;

            // Remove old thumbnail if exists
            if ($id && $oldThumbnail) {
                $oldThumbPath = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder . '/category_thumbnail/' . $oldThumbnail);
                if (file_exists($oldThumbPath)) {
                    unlink($oldThumbPath);
                }
            }

            $file->move($thumbFolder, $originalName);
            $subcategory->category_thumbnail_image = $originalName;
        } elseif ($request->remove_thumbnail && $id) {
            // Remove thumbnail if checkbox is checked
            if ($subcategory->category_thumbnail_image) {
                $currentThumbPath = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder . '/category_thumbnail/' . $subcategory->category_thumbnail_image);
                if (file_exists($currentThumbPath)) {
                    unlink($currentThumbPath);
                }
            }
            $subcategory->category_thumbnail_image = null;
        }

        $subcategory->images = $subcategory->images ?? json_encode([]);
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

    // public function saveDetails(Request $request, $id)
    // {
    //     $subcategory = Subcategory::findOrFail($id);

    //     $request->validate([
    //         'category_name' => 'required|string|max:255',
    //         'title' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'category_thumbnail_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
    //         'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
    //         'prompts.*' => 'nullable|string|max:2555',
    //         'image_title.*' => 'nullable|string|max:255',
    //         'name_change_row.*' => 'nullable|boolean',
    //         'existing_prompts.*' => 'nullable|string|max:2555',
    //         'existing_image_title.*' => 'nullable|string|max:255',
    //         'remove_images' => 'nullable|array',
    //         'replace_images' => 'nullable|array',
    //     ]);

    //     $oldTitle = $subcategory->title;
    //     $oldCategory = $subcategory->category_name;
    //     $oldThumbnail = $subcategory->category_thumbnail_image;

    //     $subcategory->category_name = $request->category_name;
    //     $subcategory->title = $request->title;
    //     $subcategory->description = $request->description;

    //     $categoryFolder = $subcategory->category_name;
    //     $subcategoryFolder = $subcategory->title;

    //     if (($oldCategory !== $categoryFolder || $oldTitle !== $subcategoryFolder) && $oldCategory && $oldTitle) {
    //         $oldFolder = public_path('upload/' . $oldCategory . '/' . $oldTitle);
    //         $newFolder = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder);
    //         $newFolderParent = dirname($newFolder);
    //         if (!is_dir($newFolderParent)) {
    //             mkdir($newFolderParent, 0755, true);
    //         }
    //         if (file_exists($oldFolder)) {
    //             rename($oldFolder, $newFolder);
    //         }
    //     }

    //     $thumbFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail");
    //     if (!is_dir($thumbFolder)) {
    //         mkdir($thumbFolder, 0755, true);
    //     }

    //     if ($request->hasFile('category_thumbnail_image')) {
    //         $file = $request->file('category_thumbnail_image');
    //         $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //         $extension = $file->getClientOriginalExtension();
    //         $generatedName = Str::slug($baseName) ?: 'thumbnail';
    //         $originalName = $generatedName . '-' . time() . '-' . Str::random(6) . '.' . $extension;

    //         // Remove old thumbnail
    //         if ($oldThumbnail) {
    //             $oldThumbPath = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder . '/category_thumbnail/' . $oldThumbnail);
    //             if (file_exists($oldThumbPath)) {
    //                 unlink($oldThumbPath);
    //             }
    //         }

    //         $file->move($thumbFolder, $originalName);
    //         $subcategory->category_thumbnail_image = $originalName;
    //     } elseif ($request->remove_thumbnail) {
    //         if ($subcategory->category_thumbnail_image) {
    //             $currentThumbPath = public_path('upload/' . $categoryFolder . '/' . $subcategoryFolder . '/category_thumbnail/' . $subcategory->category_thumbnail_image);
    //             if (file_exists($currentThumbPath)) {
    //                 unlink($currentThumbPath);
    //             }
    //         }
    //         $subcategory->category_thumbnail_image = null;
    //     }

    //     $uploadFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}");
    //     if (!is_dir($uploadFolder)) {
    //         mkdir($uploadFolder, 0755, true);
    //     }

    //     $imagesData = $subcategory->images ? json_decode($subcategory->images, true) : [];

    //     if ($request->remove_images) {
    //         foreach ($imagesData as $key => $img) {
    //             if (in_array($img['file'], $request->remove_images)) {
    //                 $filePath = $uploadFolder . '/' . $img['file'];
    //                 if (file_exists($filePath)) {
    //                     unlink($filePath);
    //                 }
    //                 unset($imagesData[$key]);
    //             }
    //         }
    //         $imagesData = array_values($imagesData);
    //     }

    //     if ($imagesData && is_array($imagesData)) {
    //         foreach ($imagesData as $index => &$img) {
    //             $fileName = $img['file'];
    //             if ($request->existing_prompts[$fileName] ?? false) {
    //                 $img['prompt'] = $request->existing_prompts[$fileName];
    //             }
    //             if ($request->existing_image_title[$fileName] ?? false) {
    //                 $img['image_title'] = $request->existing_image_title[$fileName];
    //             }
    //         }
    //     }

    //     if ($request->replace_images && is_array($request->replace_images)) {
    //         foreach ($request->replace_images as $oldFileName => $newFile) {
    //             if ($newFile && $newFile->isValid()) {
    //                 foreach ($imagesData as &$img) {
    //                     if ($img['file'] === $oldFileName) {
    //                         $oldPath = $uploadFolder . '/' . $oldFileName;
    //                         if (file_exists($oldPath)) {
    //                             unlink($oldPath);
    //                         }
    //                         $newFileName = $newFile->getClientOriginalName();
    //                         $newFile->move($uploadFolder, $newFileName);
    //                         $img['file'] = $newFileName;
    //                         break;
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $index => $file) {
    //             $originalName = $file->getClientOriginalName();
    //             $prompt = $request->prompts[$index] ?? '';
    //             $imageTitle = $request->image_title[$index] ?? '';
    //             $nameChange = $request->name_change_row[$index] ?? false;

    //             $file->move($uploadFolder, $originalName);

    //             $imagesData[] = [
    //                 'file' => $originalName,
    //                 'prompt' => $prompt,
    //                 'image_title' => $imageTitle,
    //                 'name_change' => $nameChange ? true : false,
    //             ];
    //         }
    //     }

    //     $subcategory->images = !empty($imagesData) ? json_encode($imagesData) : null;
    //     $subcategory->save();

    //     return redirect()->route('subcategories.show', $subcategory->id)->with('success', 'Subcategory images & details saved successfully!');
    // }

    public function saveDetails(Request $request, $id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $request->validate([
            'description' => 'nullable|string',
            'category_thumbnail_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'prompts.*' => 'nullable|string|max:2555',
            'image_title.*' => 'nullable|string|max:255',
            'name_change_row.*' => 'nullable|boolean',
            'existing_prompts.*' => 'nullable|string|max:2555',
            'existing_image_title.*' => 'nullable|string|max:255',
            'remove_images' => 'nullable|array',
        ]);

        // Keep track of old values for safe cleanup
        $oldCategory = $subcategory->getOriginal('category_name');
        $oldTitle = $subcategory->getOriginal('title');
        $oldThumbnail = $subcategory->getOriginal('category_thumbnail_image');

        // Update simple fields
        $subcategory->description = $request->description;
        $subcategory->trending = $request->has('trending') ? 1 : 0;

        // Current resolved folders
        $categoryFolder = $subcategory->category_name;
        $subcategoryFolder = $subcategory->title;

        $uploadFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}");
        $thumbFolder = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail");

        // Ensure folders exist
        if (!is_dir($uploadFolder)) {
            @mkdir($uploadFolder, 0755, true);
        }
        if (!is_dir($thumbFolder)) {
            @mkdir($thumbFolder, 0755, true);
        }

        // === THUMBNAIL: Auto-replace old on upload ===
        if ($request->hasFile('category_thumbnail_image')) {
            $file = $request->file('category_thumbnail_image');
            if ($file->isValid()) {
                $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $generated = \Illuminate\Support\Str::slug($baseName) ?: 'thumbnail';
                $newFileName = $generated . '-' . time() . '-' . \Illuminate\Support\Str::random(6) . '.' . $extension;

                // Delete any old thumbnail in both the previous and current paths
                if (!empty($oldThumbnail)) {
                    $oldThumbInOldPath = public_path("upload/{$oldCategory}/{$oldTitle}/category_thumbnail/{$oldThumbnail}");
                    $oldThumbInNewPath = public_path("upload/{$categoryFolder}/{$subcategoryFolder}/category_thumbnail/{$oldThumbnail}");

                    if (file_exists($oldThumbInOldPath)) {
                        @unlink($oldThumbInOldPath);
                    }
                    if (file_exists($oldThumbInNewPath)) {
                        @unlink($oldThumbInNewPath);
                    }
                }

                // Move new thumbnail
                $file->move($thumbFolder, $newFileName);
                $subcategory->category_thumbnail_image = $newFileName;
            }
        }

        // === GALLERY IMAGES ===
        $imagesData = $subcategory->images ? json_decode($subcategory->images, true) : [];

        // Remove selected gallery images
        if ($request->filled('remove_images')) {
            foreach ($imagesData as $key => $img) {
                if (in_array($img['file'], $request->remove_images)) {
                    $path = $uploadFolder . '/' . $img['file'];
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                    unset($imagesData[$key]);
                }
            }
            $imagesData = array_values($imagesData);
        }

        // Update existing gallery image meta
        if (is_array($imagesData)) {
            foreach ($imagesData as &$img) {
                $fileName = $img['file'];
                if (isset($request->existing_prompts[$fileName])) {
                    $img['prompt'] = $request->existing_prompts[$fileName];
                }
                if (isset($request->existing_image_title[$fileName])) {
                    $img['image_title'] = $request->existing_image_title[$fileName];
                }
                $img['name_change'] = isset($request->existing_name_change[$fileName]);
            }
            unset($img);
        }

        // Add new gallery images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imgFile) {
                if (!$imgFile || !$imgFile->isValid())
                    continue;

                $originalName = $imgFile->getClientOriginalName();
                $prompt = $request->prompts[$index] ?? '';
                $imageTitle = $request->image_title[$index] ?? '';
                $nameChange = $request->name_change_row[$index] ?? false;

                $imgFile->move($uploadFolder, $originalName);

                $imagesData[] = [
                    'file' => $originalName,
                    'prompt' => $prompt,
                    'image_title' => $imageTitle,
                    'name_change' => $nameChange ? true : false,
                ];
            }
        }

        $subcategory->images = !empty($imagesData) ? json_encode(array_values($imagesData)) : null;

        $subcategory->save();

        return redirect()
            ->route('subcategories.show', $subcategory->id)
            ->with('success', 'Subcategory details saved. Thumbnail updated.');
    }



    public function deleteImage($subcategoryId, $file)
    {
        $subcategory = Subcategory::findOrFail($subcategoryId);
        $images = json_decode($subcategory->images, true) ?? [];

        $found = false;
        foreach ($images as $key => $img) {
            if ($img['file'] === $file) {
                $imagePath = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title . '/' . $file);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
                unset($images[$key]);
                $found = true;
                break;
            }
        }

        if ($found) {
            $subcategory->images = json_encode(array_values($images));
            $subcategory->save();
            return redirect()->route('subcategories.show', $subcategory->id)->with('success', 'Image deleted successfully!');
        }

        return redirect()->route('subcategories.show', $subcategory->id)->with('error', 'Image not found!');
    }

    public function updateStatus(Request $request)
    {
        $subcategory = Subcategory::findOrFail($request->id);
        $subcategory->trending = $request->trending;
        $subcategory->save();

        return response()->json(['success' => true]);
    }
}
