<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FilterAiImageCategory;
use App\Models\FilterAiImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilterAiImageApiController extends Controller
{
    public function getCategories()
    {
        $query = FilterAiImageCategory::select('id', 'category_name', 'category_image', 'sort_order')
            ->where('status', 1);

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No categories found',
                'data' => [],
            ], 404);
        }

        $categoriesData = $categories->map(function ($category) {
            $images = FilterAiImage::where('category_id', $category->id)
                ->select('id', 'name', 'ai_prompt', 'image_path', 'created_at', 'updated_at')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $images->transform(function ($image) use ($category) {
                return [
                    'id' => $image->id,
                    'category_id' => $category->id,
                    'name' => $image->name,
                    'ai_prompt' => $image->ai_prompt,
                    'category_image_full_url' => asset('upload/filter_ai_image/images/' . rawurlencode($category->category_name) . '/category_image/' . rawurlencode($image->image_path)),
                    'created_at' => optional($image->created_at)->toDateTimeString(),
                    'updated_at' => optional($image->updated_at)->toDateTimeString(),
                ];
            });

            $catImages = json_decode($category->category_image, true);
            $catImageUrl = (!empty($catImages) && isset($catImages[0]))
                ? asset('upload/filter_ai_image/images/' . rawurlencode($category->category_name) . '/category_thumbnail_image/' . rawurlencode($catImages[0]))
                : null;

            return [
                'category_id' => $category->id,
                'category_name' => $category->category_name,
                'category_image_full_url' => $catImageUrl,
                'items' => $images,
            ];
        })->filter(function ($cat) {
            return $cat['items']->isNotEmpty();
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'data' => $categoriesData,
        ]);
    }

    public function getAiImageByCategoryId(Request $request)
    {
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'data' => [],
            ], 422);
        }

        $category = FilterAiImageCategory::where('id', $data['category_id'])
            ->where('status', 1)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
                'data' => [],
            ], 404);
        }

        $images = FilterAiImage::where('category_id', $data['category_id'])
            ->select('id', 'name', 'image_path', 'ai_prompt', 'created_at', 'updated_at')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($images->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No images found for this category',
                'data' => [],
            ], 404);
        }

        $images->transform(function($image) use ($category) {
            return [
                'id' => $image->id,
                'category_id' => $category->id,
                'name' => $image->name,
                'ai_prompt' => $image->ai_prompt,
                'category_image_full_url' => asset('upload/filter_ai_image/images/' . rawurlencode($category->category_name) . '/category_image/' . rawurlencode($image->image_path)),
                'created_at' => optional($image->created_at)->toDateTimeString(),
                'updated_at' => optional($image->updated_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Images fetched successfully',
            'data' => $images,
        ]);
    }
}
