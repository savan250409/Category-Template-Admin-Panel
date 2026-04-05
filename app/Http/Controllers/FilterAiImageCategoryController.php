<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FilterAiImageCategory;
use App\Models\FilterAiImage;
use Illuminate\Support\Facades\File;

class FilterAiImageCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = intval($request->input('per_page', 10));
        $search = $request->input('search', '');

        $query = FilterAiImageCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('sort_order', 'asc')->latest()->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('filter_ai_image.categories.table', compact('categories', 'perPage', 'search'));
        }

        return view('filter_ai_image.categories.index', compact('categories', 'perPage', 'search'));
    }

    public function create()
    {
        return view('filter_ai_image.categories.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:filter_ai_image_categories,category_name',
            'status' => 'boolean',
            'category_image' => 'required',
            'category_image.*' => 'image|mimes:webp|max:10000',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $category = FilterAiImageCategory::create([
            'category_name' => $request->category_name,
            'status' => $status,
            'category_image' => json_encode([]), // Temporary empty array
        ]);

        $destinationPath = public_path("upload/filter_ai_image/images/{$category->category_name}/category_thumbnail_image");

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

        $category->update(['category_image' => json_encode($images)]);

        return redirect()->route('filter-ai-image.categories.index')->with('success', 'Category added successfully!');
    }

    public function edit($id)
    {
        $category = FilterAiImageCategory::findOrFail($id);
        return view('filter_ai_image.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = FilterAiImageCategory::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:255|unique:filter_ai_image_categories,category_name,' . $id,
            'status' => 'boolean',
            'category_image' => 'nullable',
            'category_image.*' => 'image|mimes:webp|max:10000',
        ]);

        $destinationPath = public_path("upload/filter_ai_image/images/{$category->category_name}/category_thumbnail_image");

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $images = $request->input('existing_images') ? json_decode($category->category_image, true) : [];

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

        $status = $request->has('status') ? 1 : 0;

        $category->update([
            'category_name' => $request->category_name,
            'status' => $status,
            'category_image' => json_encode($images),
        ]);

        return redirect()->route('filter-ai-image.categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = FilterAiImageCategory::findOrFail($id);
        
        // Delete folder from filesystem
        $destinationPath = public_path("upload/filter_ai_image/images/{$category->category_name}");
        if (File::exists($destinationPath)) {
            File::deleteDirectory($destinationPath);
        }

        // Delete items from database
        FilterAiImage::where('category_id', $id)->delete();
        
        // Delete category
        $category->delete();

        return redirect()->route('filter-ai-image.categories.index')->with('success', 'Category and its related images deleted successfully!');
    }

    public function indexing()
    {
        $categories = FilterAiImageCategory::orderBy('sort_order', 'asc')->get();
        return response()->json(['categories' => $categories]);
    }

    public function updateStatus(Request $request)
    {
        $category = FilterAiImageCategory::findOrFail($request->id);
        $category->status = $request->status;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!'
        ]);
    }


    public function updateOrder(Request $request)
    {
        foreach ($request->order as $item) {
            FilterAiImageCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'redirect_url' => route('filter-ai-image.categories.index')
        ]);
    }
}
