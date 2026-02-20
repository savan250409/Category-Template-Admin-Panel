<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NgendevCategory;
use App\Models\NgendevImage;
use Illuminate\Http\Request;
use App\Models\AiImageNgdSetting;

class NgendevCategoryApiController extends Controller
{
    public function getCategories()
    {
        $ngdAiSetting = AiImageNgdSetting::first();
        $ngdAiModel = $ngdAiSetting ? $ngdAiSetting->model : null;
        $coupleActive = $ngdAiSetting ? $ngdAiSetting->couple_active : 1;

        $query = NgendevCategory::select('id', 'category_name', 'sort_order', 'type')
            ->where('status', 1);

        if (!$coupleActive) {
            $query->where('type', '!=', 'Couple');
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No categories found',
                    'model' => $ngdAiModel,
                    'data' => [],
                ],
                404,
            );
        }

        // Map categories with images
        $categories = $categories->map(function ($category) {
            $encodedCategory = str_replace(' ', '%20', $category->category_name);

            $images = NgendevImage::where('category_id', $category->id)
                ->select('id', 'ai_prompt', 'image_path', 'no_of_image', 'name_change') // removed sort_order
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $images->transform(function ($image) use ($encodedCategory) {
                $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
                $image->name_change = (bool) $image->name_change;
                unset($image->image_path);
                return $image;
            });

            return [
                'category_id' => $category->id,
                'category_name' => $category->category_name,
                'items' => $images,
            ];
        });

        // Separate Exclusive category
        $exclusive = $categories->firstWhere('category_name', 'Exclusive');
        if ($exclusive) {
            $categories = $categories->reject(fn($cat) => $cat['category_name'] === 'Exclusive');
        }

        // Separate Trending category
        $trending = $categories->firstWhere('category_name', 'Trending');
        if ($trending) {
            $categories = $categories->reject(fn($cat) => $cat['category_name'] === 'Trending');
        }

        // Latest category: last record from all other categories
        $latestImages = $categories
            ->filter(fn($cat) => $cat['items']->isNotEmpty())
            ->map(fn($cat) => $cat['items']->first()) // latest record per category
            ->filter()
            ->values();

        // 🔥 Add Exclusive's last record at the end of Latest category
        if ($exclusive && $exclusive['items']->isNotEmpty()) {
            $exclusiveLastRecord = $exclusive['items']->first(); // latest record
            $latestImages->push($exclusiveLastRecord);
        }

        // 🔥 Add Trending's last record at the end of Latest category
        if ($trending && $trending['items']->isNotEmpty()) {
            $trendingLastRecord = $trending['items']->first(); // latest record
            $latestImages->push($trendingLastRecord);
        }

        $latestCategory = [
            'category_id' => 0,
            'category_name' => 'Latest',
            'items' => $latestImages,
        ];

        // Reassemble categories: Exclusive -> Trending -> Latest -> Rest
        $sortedCategories = collect([]);

        if ($exclusive) {
            $sortedCategories->push($exclusive);
        }

        if ($trending) {
            $sortedCategories->push($trending);
        }

        $sortedCategories->push($latestCategory);

        // Merge remaining categories
        $sortedCategories = $sortedCategories->merge($categories);

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'model' => $ngdAiModel,
            'data' => $sortedCategories->values(),
        ]);
    }

    public function getAiImageByCategoryId(Request $request)
    {
        $data = $request->json()->all();

        $validator = \Validator::make($data, [
            'category_id' => 'required',
        ], [
            'category_id.required' => 'category_id is required',
        ]);

        $ngdAiSetting = AiImageNgdSetting::first();
        $ngdAiModel = $ngdAiSetting ? $ngdAiSetting->model : null;

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'model' => $ngdAiModel,
                'data' => [],
            ], 422);
        }

        /**
         * ===========================
         * LATEST CATEGORY (ID = 0)
         * ===========================
         */
        if ($data['category_id'] == 0) {

            $categories = NgendevCategory::where('status', 1)
                ->orderBy('id', 'asc')
                ->get();

            $trendingCategory = $categories->firstWhere('category_name', 'Trending');
            $categories = $categories->reject(fn($cat) => $cat->category_name === 'Trending');

            $latestImages = collect();

            foreach ($categories as $category) {
                $latestImage = NgendevImage::where('category_id', $category->id)
                    ->select('id', 'ai_prompt', 'image_path', 'category_id', 'no_of_image', 'name_change')
                    ->orderBy('sort_order', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestImage) {
                    $encodedCategory = str_replace(' ', '%20', $category->category_name);
                    $latestImages->push([
                        'id' => $latestImage->id,
                        'ai_prompt' => $latestImage->ai_prompt,
                        'no_of_image' => $latestImage->no_of_image,
                        'name_change' => (bool) $latestImage->name_change,
                        'category_image' => $latestImage->image_path
                            ? "ngendev/images/{$encodedCategory}/category_image/{$latestImage->image_path}"
                            : null,
                    ]);
                }
            }

            if ($trendingCategory) {
                $trendingImage = NgendevImage::where('category_id', $trendingCategory->id)
                    ->select('id', 'ai_prompt', 'image_path', 'category_id', 'no_of_image', 'name_change')
                    ->orderBy('sort_order', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($trendingImage) {
                    $encodedTrending = str_replace(' ', '%20', $trendingCategory->category_name);
                    $latestImages->push([
                        'id' => $trendingImage->id,
                        'ai_prompt' => $trendingImage->ai_prompt,
                        'no_of_image' => $trendingImage->no_of_image,
                        'name_change' => (bool) $trendingImage->name_change,
                        'category_image' => $trendingImage->image_path
                            ? "ngendev/images/{$encodedTrending}/category_image/{$trendingImage->image_path}"
                            : null,
                    ]);
                }
            }

            // ðŸ”¥ Show last category last record FIRST
            $latestImages = $latestImages->reverse()->values();

            return response()->json([
                'status' => true,
                'message' => 'Latest images fetched successfully',
                'model' => $ngdAiModel,
                'data' => $latestImages,
            ]);
        }

        /**
         * ===========================
         * SINGLE CATEGORY
         * ===========================
         */
        $category = NgendevCategory::where('id', $data['category_id'])
            ->where('status', 1)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
                'model' => $ngdAiModel,
                'data' => [],
            ], 404);
        }

        $encodedCategory = str_replace(' ', '%20', $category->category_name);

        $images = NgendevImage::where('category_id', $data['category_id'])
            ->select('id', 'image_path', 'ai_prompt', 'no_of_image', 'name_change')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($images->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No images found for this category',
                'model' => $ngdAiModel,
                'data' => [],
            ], 404);
        }

        $images->transform(fn($image) => [
            'id' => $image->id,
            'ai_prompt' => $image->ai_prompt,
            'no_of_image' => $image->no_of_image,
            'name_change' => (bool) $image->name_change,
            'category_image' => $image->image_path
                ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}"
                : null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Images fetched successfully',
            'model' => $ngdAiModel,
            'data' => $images,
        ]);
    }
    public function getAllCategoryNames()
    {
        $categories = NgendevCategory::select('id', 'category_name')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No categories found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'data' => $categories,
        ]);
    }
}
