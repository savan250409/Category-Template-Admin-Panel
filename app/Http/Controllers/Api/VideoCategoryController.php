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
        // Video module model
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        // Active categories
        $activeCategories = AiVideoCategory::where('status', 1)->pluck('name');

        $response = [];

        foreach ($activeCategories as $category) {

            $subcategories = AiVideoSubcategory::where('category_name', $category)
                ->orderBy('id', 'desc')
                // Limit to 4 records
                ->limit(4)
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

                // Decode videos
                $videos = json_decode($subcat->videos, true) ?? [];
                $formattedVideos = [];

                foreach ($videos as $vid) {
                    $file = $vid['file'] ?? null;
                    $prompt = $vid['prompt'] ?? '';
                    $videoTitle = $vid['video_title'] ?? ($vid['image_title'] ?? '');
                    $nameChange = isset($vid['name_change']) ? (bool) $vid['name_change'] : false;
                    $thumbnail = $vid['thumbnail'] ?? null;

                    if ($file) {
                        $videoPath = "AI Baby Video/{$categoryName}/{$subcatTitle}/video/{$file}";
                        $videoThumbnailPath = $thumbnail ? "AI Baby Video/{$categoryName}/{$subcatTitle}/video thumbnail/{$thumbnail}" : null;

                        $formattedVideos[] = [
                            'url' => $videoPath,
                            'thumbnail' => $videoThumbnailPath,
                            'prompt' => $prompt,
                            'video_title' => $videoTitle,
                            'name_change' => $nameChange,
                        ];
                    }
                }

                if ($subcatTitle === 'Baby Month Milestone') {
                    $uniqueMonthVideos = [];
                    foreach ($formattedVideos as $vid) {
                        if (preg_match('/^(\d+)\s*Month/i', $vid['video_title'], $matches)) {
                            $month = (int) $matches[1];
                            $uniqueMonthVideos[$month] = $vid;
                        }
                    }
                    ksort($uniqueMonthVideos);
                    $formattedVideos = array_values($uniqueMonthVideos);
                } else {
                    // Limit to last 4 videos
                    $formattedVideos = array_slice($formattedVideos, -4);
                }

                return [
                    'id' => $subcat->id,
                    'title' => $subcat->title,
                    'thumbnail' => $thumbnailPath,
                    'videos' => $formattedVideos,
                ];
            });

            $response = array_merge($response, $formattedSubcategories->toArray());
        }

        return response()->json([
            'status' => true,
            'message' => 'Video categories retrieved successfully',
            'model' => $videoModel,
            'data' => $response,
        ]);
    }

    public function getSubcategoriesByCategoryid(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'sub_category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $subCategoryId = $request->sub_category_id;

        // Get subcategory
        $subCategory = AiVideoSubcategory::where('id', $subCategoryId)->first(['id', 'title', 'description', 'videos', 'category_name']);

        if (!$subCategory) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No data found for the given sub category id',
                    'sub_category_id' => $subCategoryId,
                    'data' => [],
                ],
                200,
            );
        }

        $mainCategory = $subCategory->category_name;
        $subCategoryName = $subCategory->title;

        // Check if the main category is active
        $category = AiVideoCategory::where('name', $mainCategory)->first();

        if (!$category || $category->status != 1) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'This category is not active',
                    'main_category' => $mainCategory,
                    'data' => [],
                ],
                403,
            );
        }

        // Get Video module setting
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        // Format videos
        $videos = json_decode($subCategory->videos, true) ?? [];
        $formattedVideos = [];

        $categoryName = trim($subCategory->category_name);
        $subcatTitle = trim($subCategory->title);

        foreach ($videos as $vid) {
            $file = $vid['file'] ?? null;
            $prompt = $vid['prompt'] ?? '';
            $videoTitle = $vid['video_title'] ?? ($vid['image_title'] ?? '');
            $nameChange = isset($vid['name_change']) ? (bool) $vid['name_change'] : false;
            $thumbnail = $vid['thumbnail'] ?? null;

            if ($file) {
                $videoPath = "AI Baby Video/{$categoryName}/{$subcatTitle}/video/{$file}";
                $videoThumbnailPath = $thumbnail ? "AI Baby Video/{$categoryName}/{$subcatTitle}/video thumbnail/{$thumbnail}" : null;

                $formattedVideos[] = [
                    'url' => $videoPath,
                    'thumbnail' => $videoThumbnailPath,
                    'prompt' => $prompt,
                    'video_title' => $videoTitle,
                    'name_change' => $nameChange,
                ];
            }
        }

        $subCategory->videos = $formattedVideos;
        unset($subCategory->category_name);

        return response()->json([
            'status' => true,
            'message' => 'Subcategories retrieved successfully',
            'model' => $videoModel,
            'main_category' => $mainCategory,
            'category_name' => $subCategoryName,
            'subcategories' => [$subCategory],
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
                'id' => $subcat->id,
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
