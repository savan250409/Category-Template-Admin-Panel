<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use App\Models\FilterAiImage;
use App\Models\FilterAiImageCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FilterAiImageController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->input('per_page', 10);
        $search     = (string) $request->input('search', '');
        $categoryId = (string) $request->input('category_id', '');

        $categories = FilterAiImageCategory::orderBy('created_at', 'desc')->get();

        $query = FilterAiImage::with('category')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc');

        if ($categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ai_prompt', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($q2) use ($search) {
                      $q2->where('category_name', 'like', "%{$search}%");
                  });
            });
        }

        $images = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('filter_ai_image.images.table', compact('images'))->render(),
                'total' => $images->total(),
            ]);
        }

        return view('filter_ai_image.images.index', compact('categories', 'images', 'perPage', 'search', 'categoryId'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:filter_ai_image_categories,id',
                'name'        => 'required|string|max:255',
                'ai_prompt'   => 'required|string|max:10000',
                'image'       => 'required|image|mimes:webp|max:10000',
            ]);

            $imageName = null;

            if ($request->hasFile('image')) {
                $category        = FilterAiImageCategory::findOrFail($request->category_id);
                $originalName    = $request->file('image')->getClientOriginalName();
                $destinationPath = public_path('upload/filter_ai_image/images/' . $category->category_name . '/category_image');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $request->file('image')->move($destinationPath, $originalName);
                $imageName = $originalName;
            }

            FilterAiImage::create([
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'ai_prompt'   => $request->ai_prompt,
                'image_path'  => $imageName,
            ]);

            return $request->ajax()
                ? response()->json(['success' => true, 'message' => 'Filter AI image added successfully!'])
                : view('partials.history_redirect', ['fallback' => route('filter-ai-image.images.index'), 'message' => 'Filter AI image added successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $errors], 422);
            }
            return redirect()->back()->withInput()->withErrors($e->errors());

        } catch (\Exception $e) {
            $msg = 'Something went wrong while adding the image. Please try again.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:filter_ai_image_categories,id',
                'name'        => 'required|string|max:255',
                'ai_prompt'   => 'required|string|max:10000',
                'image'       => 'nullable|image|mimes:webp|max:4096',
            ]);

            $image        = FilterAiImage::findOrFail($id);
            $imagePath    = $image->image_path;
            $categoryId   = $request->category_id;

            if ($request->hasFile('image')) {
                $oldCategory = FilterAiImageCategory::findOrFail($image->category_id);
                $oldPath = public_path('upload/filter_ai_image/images/' . $oldCategory->category_name . '/category_image/' . $imagePath);
                if ($imagePath && file_exists($oldPath)) {
                    unlink($oldPath);
                }

                $originalName    = $request->file('image')->getClientOriginalName();
                $newCategory = FilterAiImageCategory::findOrFail($categoryId);
                $destinationPath = public_path('upload/filter_ai_image/images/' . $newCategory->category_name . '/category_image');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $request->file('image')->move($destinationPath, $originalName);
                $imagePath = $originalName;
            }

            $image->update([
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'ai_prompt'   => $request->ai_prompt,
                'image_path'  => $imagePath,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Filter AI image updated successfully!']);
            }

            return view('partials.history_redirect', ['fallback' => route('filter-ai-image.images.index'), 'message' => 'Filter AI image updated successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $errors], 422);
            }
            return redirect()->back()->withInput()->withErrors($e->errors());

        } catch (\Exception $e) {
            $msg = 'Something went wrong while updating the image. Please try again.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }
    }

    public function destroy($id)
    {
        $image = FilterAiImage::findOrFail($id);
        $category = $image->category;

        $filePath = public_path('upload/filter_ai_image/images/' . $category->category_name . '/category_image/' . $image->image_path);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $image->delete();

        return redirect()->route('filter-ai-image.images.index')->with('success', 'Selected image deleted successfully!');
    }

    public function indexing(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return response()->json(['images' => []]);
        }

        $images = FilterAiImage::with('category')->where('category_id', $categoryId)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        $transformedImages = $images->map(function ($image) {
            $imageData = $image->toArray();

            if ($image->image_path) {
                $imageData['image_url'] = asset('upload/filter_ai_image/images/' . $image->category->category_name . '/category_image/' . $image->image_path);
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
            'category_id' => 'required|exists:filter_ai_image_categories,id',
            'order' => 'required|array',
            'order.*.id' => 'required|exists:filter_ai_images,id',
            'order.*.sort_order' => 'required|integer|min:1',
        ]);

        $categoryId = $request->category_id;
        $orderData = $request->order;

        foreach ($orderData as $item) {
            FilterAiImage::where('id', $item['id'])
                ->where('category_id', $categoryId)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Image order updated successfully!']);
    }
}
