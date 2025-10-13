<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use App\Models\NgendevImage;
use App\Models\NgendevCategory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class NgendevImageController extends Controller
{
    public function index(Request $request)
    {
        $categories = NgendevCategory::all();
        $query = NgendevImage::with('category')->orderBy('sort_order', 'asc')->orderBy('id', 'desc');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ai_prompt', 'like', "%{$search}%")
                    ->orWhere('ai_model', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        $images = $query->paginate(10)->appends(['search' => $request->get('search')]);

        if ($request->ajax()) {
            $table = view('ngendev.images.index', compact('images', 'categories'))->renderSections()['table'];
            $pagination = view('ngendev.images.index', compact('images', 'categories'))->renderSections()['pagination'];

            return response()->json([
                'table' => $table,
                'pagination' => $pagination,
                'total' => $images->total(),
            ]);
        }

        return view('ngendev.images.index', compact('categories', 'images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:ngendev_categories,id',
            'ai_prompt' => 'required|string|max:10000',
            'ai_model' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10000',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $category = NgendevCategory::findOrFail($request->category_id);
            $categoryName = $category->category_name;

            $originalName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('upload/ngendev/images/' . $categoryName . '/category_image');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $request->file('image')->move($destinationPath, $originalName);
            $imageName = $originalName;
        }

        NgendevImage::create([
            'category_id' => $request->category_id,
            'ai_prompt' => $request->ai_prompt,
            'ai_model' => $request->ai_model ?? 'AI Image',
            'image_path' => $imageName,
        ]);

        return $request->ajax() ? response()->json(['success' => true, 'message' => 'Ngendev image added successfully!']) : redirect()->route('ngendev.images.index')->with('success', 'Ngendev image added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:ngendev_categories,id',
            'ai_prompt' => 'required|string|max:1000',
            'ai_model' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10000',
        ]);

        $image = NgendevImage::findOrFail($id);
        $category = NgendevCategory::findOrFail($request->category_id);
        $categoryName = $category->category_name;

        $imagePath = $image->image_path;

        if ($request->hasFile('image')) {
            $oldPath = public_path('upload/ngendev/images/' . $categoryName . '/category_image/' . $imagePath);
            if ($imagePath && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $originalName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('upload/ngendev/images/' . $categoryName . '/category_image');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $request->file('image')->move($destinationPath, $originalName);
            $imagePath = $originalName;
        }

        $image->update([
            'category_id' => $request->category_id,
            'ai_prompt' => $request->ai_prompt,
            'ai_model' => $request->ai_model ?? $image->ai_model,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('ngendev.images.index')->with('success', 'Ngendev image updated successfully!');
    }

    public function destroy($id)
    {
        $image = NgendevImage::findOrFail($id);
        $category = $image->category;

        $filePath = public_path('upload/ngendev/images/' . $category->category_name . '/category_image/' . $image->image_path);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $image->delete();

        return redirect()->route('ngendev.images.index')->with('success', 'Selected image deleted successfully!');
    }

    public function indexing(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return response()->json(['images' => []]);
        }

        $images = NgendevImage::with('category')->where('category_id', $categoryId)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        // If no images have sort_order set, initialize them
        if ($images->count() > 0 && $images->where('sort_order', 0)->count() > 0) {
            foreach ($images as $index => $image) {
                if ($image->sort_order == 0) {
                    $image->update(['sort_order' => $index + 1]);
                }
            }
            // Refresh the collection
            $images = NgendevImage::with('category')->where('category_id', $categoryId)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        }

        // Transform the images to include proper image URLs
        $transformedImages = $images->map(function ($image) {
            $imageData = $image->toArray();

            // Build the correct image URL
            if ($image->image_path && $image->category) {
                $imageData['image_url'] = asset('upload/ngendev/images/' . $image->category->category_name . '/category_image/' . $image->image_path);
            } else {
                $imageData['image_url'] = null;
            }

            return $imageData;
        });

        return response()->json(['images' => $transformedImages]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:ngendev_categories,id',
            'order' => 'required|array',
            'order.*.id' => 'required|exists:ngendev_images,id',
            'order.*.sort_order' => 'required|integer|min:1',
        ]);

        $categoryId = $request->category_id;
        $orderData = $request->order;

        foreach ($orderData as $item) {
            NgendevImage::where('id', $item['id'])
                ->where('category_id', $categoryId)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Image order updated successfully!']);
    }
    public function addSortOrderColumn()
    {
        $tableName = 'ngendev_images';
        $columnName = 'sort_order';

        // Check if column exists
        $columnExists = Schema::hasColumn($tableName, $columnName);

        if (!$columnExists) {
            // Add the column dynamically
            DB::statement("ALTER TABLE {$tableName} ADD COLUMN {$columnName} INT DEFAULT 0 AFTER ai_model");
        }

        // Fill sort_order per category
        $categories = \App\Models\NgendevCategory::all();

        foreach ($categories as $category) {
            $images = \App\Models\NgendevImage::where('category_id', $category->id)->orderBy('id', 'asc')->get();

            $i = 1;
            foreach ($images as $image) {
                $image->sort_order = $i;
                $image->save();
                $i++;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'sort_order column added and populated successfully for all images.',
        ]);
    }
}
