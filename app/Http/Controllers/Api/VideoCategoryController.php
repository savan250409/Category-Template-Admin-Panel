<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiVideoCategory;
use App\Models\AiVideoSubcategory;
use App\Models\AiBabyVideoModuleSetting;

class VideoCategoryController extends Controller
{
    public function getAllCategories()
    {
        // Get Video module setting
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        // Get only active categories from AiVideoCategory table
        $activeCategories = AiVideoCategory::where('status', 1)->pluck('name');

        $response = [];

        foreach ($activeCategories as $category) {
            $subcategories = AiVideoSubcategory::where('category_name', $category)
                ->orderBy('id', 'desc')
                ->get(['id', 'title', 'category_name', 'category_thumbnail_image']);

            $subcategories->transform(function ($subcat) {
                $categoryName = trim($subcat->category_name);
                $subcatTitle = trim($subcat->title);

                $thumbnailPath = $subcat->category_thumbnail_image ? "AI Baby Video/{$categoryName}/{$subcatTitle}/category_thumbnail/{$subcat->category_thumbnail_image}" : null;

                return [
                    'id' => $subcat->id,
                    'title' => $subcat->title,
                    'thumbnail' => $thumbnailPath,
                ];
            });

            $response[] = [
                'category_name' => $category,
                'subcategories' => $subcategories,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Video categories retrieved successfully',
            'model' => $videoModel,
            'data' => $response,
        ]);
    }

    public function getAllCategoriesV2()
    {
        // Video module model
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        // Active categories
        $activeCategories = AiVideoCategory::where('status', 1)->pluck('name');

        $response = [];

        foreach ($activeCategories as $category) {

            $subcategories = AiVideoSubcategory::where('category_name', $category)
                ->orderBy('id', 'desc')
                ->get([
                    'id',
                    'title',
                    'category_name',
                    'category_thumbnail_image',
                    'videos'
                ]);

            $formattedSubcategories = $subcategories->map(function ($subcat) {

                $categoryName = trim($subcat->category_name);
                $subcatTitle = trim($subcat->title);

                // Thumbnail path
                $thumbnailPath = $subcat->category_thumbnail_image
                    ? "AI Baby Video/{$categoryName}/{$subcatTitle}/category_thumbnail/{$subcat->category_thumbnail_image}"
                    : null;

                // Decode images
                $images = json_decode($subcat->videos, true) ?? [];

                // Total images count
                $totalCount = count($images);

                // Random video thumbnail (DOMAIN REMOVED)
                $subcategoryVideoThumbnail = null;
                if (!empty($images)) {
                    $randomImage = collect($images)->random();
                    if (isset($randomImage['thumbnail']) && $randomImage['thumbnail']) {
                        $subcategoryVideoThumbnail =
                            'AI Baby Video/' .
                            $categoryName . '/' .
                            $subcatTitle . '/video thumbnail/' .
                            $randomImage['thumbnail'];
                    }
                }

                return [
                    'id' => $subcat->id,
                    'title' => $subcat->title,
                    'thumbnail' => $thumbnailPath,
                    'total_count' => $totalCount,
                    'subcategories_video_thumbnail' => $subcategoryVideoThumbnail,
                ];
            });

            $response[] = [
                'category_name' => $category,
                'subcategories' => $formattedSubcategories,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Video categories retrieved successfully',
            'model' => $videoModel,
            'data' => $response,
        ]);
    }


    public function getSubcategoriesByCategory(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'main_category' => 'required|string',
            'category_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ],
                422
            );
        }

        $mainCategory = $request->main_category;
        $subCategoryName = $request->category_name;

        // Check if the main category is active
        $category = AiVideoCategory::where('name', $mainCategory)->first();

        if (!$category || $category->status != 1) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'This category is not active',
                    'main_category' => $mainCategory,
                    'subcategories' => [],
                ],
                403
            );
        }

        // Get Video module setting
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        // Get subcategory
        $subcategories = AiVideoSubcategory::where('category_name', $mainCategory)
            ->where('title', $subCategoryName)
            ->get(['id', 'title', 'description', 'videos', 'category_name']);

        if ($subcategories->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No data found for the given main category and subcategory',
                    'model' => $videoModel,
                    'main_category' => $mainCategory,
                    'category_name' => $subCategoryName,
                    'subcategories' => [],
                ],
                200
            );
        }

        // Format images (DOMAIN REMOVED ONLY)
        $subcategories->transform(function ($subcat) {

            $images = json_decode($subcat->videos, true) ?? [];
            $formattedImages = [];

            $categoryName = trim($subcat->category_name);
            $subcatTitle = trim($subcat->title);

            foreach ($images as $img) {

                $file = $img['file'] ?? null;
                $prompt = $img['prompt'] ?? '';
                $videoTitle = $img['video_title'] ?? ($img['image_title'] ?? '');
                $nameChange = isset($img['name_change']) ? (bool) $img['name_change'] : false;

                if ($file) {

                    // RELATIVE VIDEO PATH
                    $videoUrl =
                        'AI Baby Video/' .
                        $categoryName . '/' .
                        $subcatTitle . '/video/' .
                        $file;

                    // RELATIVE THUMBNAIL PATH
                    $thumbnailUrl = null;
                    if (!empty($img['thumbnail'])) {
                        $thumbnailUrl =
                            'AI Baby Video/' .
                            $categoryName . '/' .
                            $subcatTitle . '/video thumbnail/' .
                            $img['thumbnail'];
                    }

                    $formattedImages[] = [
                        'url' => $videoUrl,
                        'thumbnail' => $thumbnailUrl,
                        'prompt' => $prompt,
                        'video_title' => $videoTitle,
                        'name_change' => $nameChange,
                    ];
                }
            }

            $subcat->videos = $formattedImages;
            unset($subcat->category_name);

            return $subcat;
        });

        return response()->json([
            'status' => true,
            'message' => 'Video subcategories retrieved successfully',
            'model' => $videoModel,
            'main_category' => $mainCategory,
            'category_name' => $subCategoryName,
            'subcategories' => $subcategories,
        ]);
    }



    public function trending()
    {
        $trendingSubcategories = AiVideoSubcategory::where('trending', 1)->get();

        $data = $trendingSubcategories->map(function ($subcat) {
            $categoryName = trim($subcat->category_name);
            $subcatTitle = trim($subcat->title);
            $thumbnailPath = $subcat->category_thumbnail_image
                ? "AI Baby Video/{$categoryName}/{$subcatTitle}/category_thumbnail/{$subcat->category_thumbnail_image}"
                : null;

            return [
                'main_category_name' => $categoryName,
                'name' => $subcatTitle,
                'thumbnail' => $thumbnailPath,
                'description' => $subcat->description,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Trending video subcategories retrieved successfully',
            'data' => $data,
        ]);
    }
}
