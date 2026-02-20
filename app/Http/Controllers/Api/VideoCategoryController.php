<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiVideoCategory;
use App\Models\AiBabyVideo;
use App\Models\AiBabyVideoModuleSetting;

class VideoCategoryController extends Controller
{
    public function getAllCategories()
    {
        // Video module model
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        // Get active categories
        $activeCategories = AiVideoCategory::where('status', 1)->get();

        $response = [];

        foreach ($activeCategories as $category) {
            $categoryName = trim($category->category_name);

            // Thumbnail path
            $thumbnailPath = $category->category_image
                ? "AI Baby Video/Category/{$category->category_image}"
                : null;

            // Get videos for this category
            $videos = AiBabyVideo::where('category_id', $category->id)->orderBy('id', 'desc')->get(); // Get all videos initially to process

            $formattedVideos = [];

            foreach ($videos as $vid) {
                $file = $vid->video_path;
                $prompt = $vid->ai_prompt ?? '';
                $videoTitle = $vid->video_title;
                $nameChange = (bool) $vid->name_change;
                $thumbnail = $vid->video_thumbnail;

                if ($file) {
                    $videoPath = "AI Baby Video/{$categoryName}/{$file}";
                    $videoThumbnailPath = $thumbnail ? "AI Baby Video/{$categoryName}/{$thumbnail}" : null;

                    $formattedVideos[] = [
                        'url' => $videoPath,
                        'thumbnail' => $videoThumbnailPath,
                        'prompt' => $prompt,
                        'video_title' => $videoTitle,
                        'name_change' => $nameChange,
                    ];
                }
            }

            // Special logic only if Category Name is EXACTLY 'Baby Month Milestone'
            if ($categoryName === 'Baby Month Milestone') {
                $uniqueMonthVideos = [];
                foreach ($formattedVideos as $vid) {
                    // Try to extract month number from title like "1 Month", "2 Month" etc.
                    if (preg_match('/^(\d+)\s*Month/i', $vid['video_title'], $matches)) {
                        $month = (int) $matches[1];
                        // If multiple videos exist for same month, this will take the last one processed (which is latest due to orderBy id desc?)
                        // Wait, orderBy id desc means latest first. So this takes the first one encountered (latest).
                        // Array key overwrite.
                        if (!isset($uniqueMonthVideos[$month])) {
                            $uniqueMonthVideos[$month] = $vid;
                        }
                    }
                }
                ksort($uniqueMonthVideos);
                $formattedVideos = array_values($uniqueMonthVideos);
            } else {
                // Limit to last 4 videos for the main list view (as per original logic)
                // Original logic: ->limit(4) on database query or array_slice.
                // Since we fetched all to process, we slice here.
                $formattedVideos = array_slice($formattedVideos, 0, 4);
            }

            $response[] = [
                'id' => $category->id,
                'title' => $category->category_name, // Mapping category_name to title
                'thumbnail' => $thumbnailPath,
                'videos' => $formattedVideos,
            ];
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

        // Get category (Acting as subcategory in this context)
        $category = AiVideoCategory::find($subCategoryId);

        if (!$category) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No data found for the given category id',
                    'sub_category_id' => $subCategoryId,
                    'data' => [],
                ],
                200,
            );
        }

        if ($category->status != 1) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'This category is not active',
                    'main_category' => $category->category_name, // Best guess for main_category
                    'data' => [],
                ],
                403,
            );
        }

        // Get Video module setting
        $videoSetting = AiBabyVideoModuleSetting::first();
        $videoModel = $videoSetting ? $videoSetting->model : null;

        $categoryName = trim($category->category_name);

        // Get videos
        $videos = AiBabyVideo::where('category_id', $category->id)->orderBy('id', 'desc')->get();
        $formattedVideos = [];

        foreach ($videos as $vid) {
            $file = $vid->video_path;
            $prompt = $vid->ai_prompt ?? '';
            $videoTitle = $vid->video_title;
            $nameChange = (bool) $vid->name_change;
            $thumbnail = $vid->video_thumbnail;

            if ($file) {
                $videoPath = "AI Baby Video/{$categoryName}/{$file}";
                $videoThumbnailPath = $thumbnail ? "AI Baby Video/{$categoryName}/{$thumbnail}" : null;

                $formattedVideos[] = [
                    'url' => $videoPath,
                    'thumbnail' => $videoThumbnailPath,
                    'prompt' => $prompt,
                    'video_title' => $videoTitle,
                    'name_change' => $nameChange,
                ];
            }
        }

        // Prepare the response structure to match old API
        // Old API returned a 'subcategories' array with one item (the detailed subcategory)
        $subCategoryResponse = [
            'id' => $category->id,
            'title' => $category->category_name,
            'description' => '', // Description not in new schema, return empty string
            'videos' => $formattedVideos,
            // category_name removed in old code via unset, so not including it here inside the object
        ];

        return response()->json([
            'status' => true,
            'message' => 'Subcategories retrieved successfully', // Keeping message same
            'model' => $videoModel,
            'main_category' => $categoryName, // Using category name as main category
            'category_name' => $categoryName, // Using category name as sub category name
            'subcategories' => [$subCategoryResponse],
        ]);
    }

    public function trending()
    {
        $trendingCategories = AiVideoCategory::where('trending', 1)->where('status', 1)->get();

        $data = $trendingCategories->map(function ($category) {
            $categoryName = trim($category->category_name);

            $thumbnailPath = $category->category_image
                ? "AI Baby Video/Category/{$category->category_image}"
                : null;

            return [
                'id' => $category->id,
                'main_category_name' => $categoryName, // Mapping category_name to main_category_name
                'name' => $categoryName, // Mapping as subcategory name
                'thumbnail' => $thumbnailPath,
                'description' => '', // No description column
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Trending video categories retrieved successfully',
            'data' => $data,
        ]);
    }
}
