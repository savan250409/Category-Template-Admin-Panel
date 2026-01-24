<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\AiVideoSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use File;

class SubcategoryController extends Controller
{
    private function getModel(Request $request)
    {
        return $request->get('origin') === 'video' ? AiVideoSubcategory::class : Subcategory::class;
    }

    public function index(Request $request)
    {
        $model = $this->getModel($request);
        $subcategories = $model::latest()->paginate(10);
        return view('category-template.index', compact('subcategories'));
    }

    public function form(Request $request, $id = null)
    {
        $model = $this->getModel($request);
        $subcategory = $id ? $model::findOrFail($id) : new $model();

        $videoCategories = \App\Models\AiVideoCategory::where('status', 1)->pluck('name')->toArray();
        $imageCategories = \App\Models\AiImageCategory::pluck('name')->toArray();
        $hardcodedCategories = ['Newborn Baby', 'Baby Bumps', 'Toddler Photoshoot', 'Festival Photoshoot', 'Birthday Photo', 'Unique Style', 'Invitation card'];

        // If it's video origin, only show video categories
        if ($request->get('origin') === 'video') {
            $categories = $videoCategories;
        } else {
            // Otherwise show image categories (allowing for hardcoded ones for now)
            $categories = array_unique(array_merge($hardcodedCategories, $imageCategories));
        }

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

        $model = $this->getModel($request);
        $subcategory = $id ? $model::findOrFail($id) : new $model();
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

        if ($request->get('origin') === 'video') {
            $subcategory->videos = $subcategory->videos ?? json_encode([]);
        } else {
            $subcategory->images = $subcategory->images ?? json_encode([]);
        }
        $subcategory->save();

        $redirectParams = ['id' => $subcategory->id];
        if ($request->has('origin')) {
            $redirectParams['origin'] = $request->origin;
        }

        return redirect()
            ->route('subcategories.show', $redirectParams)
            ->with('success', $id ? 'Subcategory updated!' : 'Subcategory created!');
    }

    public function show(Request $request, $id)
    {
        $model = $this->getModel($request);
        $subcategory = $model::findOrFail($id);

        $is_video = $request->get('origin') === 'video';
        $dataJson = $is_video ? $subcategory->videos : $subcategory->images;
        $imagesArray = json_decode($dataJson, true) ?? [];

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

    public function destroy(Request $request, $id)
    {
        $model = $this->getModel($request);
        $subcategory = $model::findOrFail($id);
        $folderPath = public_path('upload/' . $subcategory->category_name . '/' . $subcategory->title);
        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }
        $subcategory->delete();

        $redirectParams = [];
        if ($request->has('origin')) {
            $redirectParams['origin'] = $request->origin;
        }

        $type = $request->get('origin') === 'video' ? 'videos' : 'images';
        return redirect()->route('subcategories.index', $redirectParams)->with('success', "Subcategory and its {$type} deleted successfully!");
    }

    public function addDetailsForm(Request $request, $id)
    {
        $model = $this->getModel($request);
        $subcategory = $model::findOrFail($id);
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
        $model = $this->getModel($request);
        $subcategory = $model::findOrFail($id);

        $is_video = $request->get('origin') === 'video';
        $titleField = $is_video ? 'video_title' : 'image_title';
        $existingTitleField = $is_video ? 'existing_video_title' : 'existing_image_title';

        $rules = [
            'description' => 'nullable|string',
            'category_thumbnail_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'images.*' => $is_video ? 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:51200' : 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'prompts.*' => 'nullable|string|max:2555',
            $titleField . '.*' => 'nullable|string|max:255',
            'name_change_row.*' => 'nullable|boolean',
            'existing_prompts.*' => 'nullable|string|max:2555',
            $existingTitleField . '.*' => 'nullable|string|max:255',
            'remove_images' => 'nullable|array',
        ];

        $request->validate($rules);

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
        $dataJson = $is_video ? $subcategory->videos : $subcategory->images;
        $imagesData = $dataJson ? json_decode($dataJson, true) : [];

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

                // Use the correct field for title
                if (isset($request->$existingTitleField[$fileName])) {
                    $img[$titleField] = $request->$existingTitleField[$fileName];
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
                // Get title from the dynamic field
                $titles = $request->$titleField;
                $imageTitle = $titles[$index] ?? '';
                $nameChange = $request->name_change_row[$index] ?? false;

                $imgFile->move($uploadFolder, $originalName);

                $imagesData[] = [
                    'file' => $originalName,
                    'prompt' => $prompt,
                    $titleField => $imageTitle,
                    'name_change' => $nameChange ? true : false,
                ];
            }
        }

        $finalJson = !empty($imagesData) ? json_encode(array_values($imagesData)) : null;

        if ($is_video) {
            $subcategory->videos = $finalJson;
        } else {
            $subcategory->images = $finalJson;
        }

        $subcategory->save();

        $redirectParams = ['id' => $subcategory->id];
        if ($request->has('origin')) {
            $redirectParams['origin'] = $request->origin;
        }

        return redirect()
            ->route('subcategories.show', $redirectParams)
            ->with('success', 'Subcategory details saved. Thumbnail updated.');
    }



    public function deleteImage(Request $request, $subcategoryId, $file)
    {
        $model = $this->getModel($request);
        $subcategory = $model::findOrFail($subcategoryId);
        $is_video = $request->get('origin') === 'video';
        $dataJson = $is_video ? $subcategory->videos : $subcategory->images;
        $images = json_decode($dataJson, true) ?? [];

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

        $redirectParams = ['id' => $subcategory->id];
        if ($request->has('origin')) {
            $redirectParams['origin'] = $request->origin;
        }

        if ($found) {
            $newJson = json_encode(array_values($images));
            if ($is_video) {
                $subcategory->videos = $newJson;
            } else {
                $subcategory->images = $newJson;
            }
            $subcategory->save();
            $type = $request->get('origin') === 'video' ? 'Video' : 'Image';
            return redirect()->route('subcategories.show', $redirectParams)->with('success', "{$type} deleted successfully!");
        }

        $type = $request->get('origin') === 'video' ? 'Video' : 'Image';
        return redirect()->route('subcategories.show', $redirectParams)->with('error', "{$type} not found!");
    }

    public function updateStatus(Request $request)
    {
        $model = $this->getModel($request);
        $subcategory = $model::findOrFail($request->id);
        $subcategory->trending = $request->trending;
        $subcategory->save();

        return response()->json(['success' => true]);
    }
}
