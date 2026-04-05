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
                ->select('id', 'name', 'ai_prompt', 'image_path')
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
        });

        // Add Latest category (ID: 0) matching Ngendev logic
        $latestImages = $categoriesData
            ->filter(function($cat) { return $cat['items']->isNotEmpty(); })
            ->map(function($cat) { return $cat['items']->last(); })
            ->values();

        $latestCategory = [
            'category_id' => 0,
            'category_name' => 'Latest',
            'items' => $latestImages,
        ];

        $finalData = collect([$latestCategory])->merge($categoriesData);

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'data' => $finalData,
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

        if ($data['category_id'] == 0) {
            $categories = FilterAiImageCategory::where('status', 1)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            $latestImages = collect();

            foreach ($categories as $cat) {
                $latestImage = FilterAiImage::where('category_id', $cat->id)
                    ->select('id', 'name', 'image_path', 'ai_prompt')
                    ->orderBy('sort_order', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestImage) {
                    $latestImages->push([
                        'id' => $latestImage->id,
                        'category_id' => $cat->id,
                        'name' => $latestImage->name,
                        'ai_prompt' => $latestImage->ai_prompt,
                        'category_image_full_url' => asset('upload/filter_ai_image/images/' . rawurlencode($cat->category_name) . '/category_image/' . rawurlencode($latestImage->image_path)),
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Latest images fetched successfully',
                'data' => $latestImages->values(),
            ]);
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
            ->select('id', 'name', 'image_path', 'ai_prompt')
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
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Images fetched successfully',
            'data' => $images,
        ]);
    }

}
