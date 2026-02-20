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
                ? str_replace(' ', '%20', "AI Baby Video/{$categoryName}/category thumbanail/{$category->category_image}")
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
                    $videoPath = str_replace(' ', '%20', "AI Baby Video/{$categoryName}/video/{$file}");
                    $videoThumbnailPath = $thumbnail ? str_replace(' ', '%20', "AI Baby Video/{$categoryName}/video thumbanail/{$thumbnail}") : null;

                    $formattedVideos[] = [
                        'url' => $videoPath,
                        'thumbnail' => $videoThumbnailPath,
                        'prompt' => $prompt,
                        'video_title' => $videoTitle,
                        'name_change' => $nameChange,
                    ];
                }
            }

            if ($categoryName === 'Baby Month Milestone') {
                $uniqueMonthVideos = [];
                foreach ($formattedVideos as $vid) {
                    if (preg_match('/^(\d+)\s*Month/i', $vid['video_title'], $matches)) {
                        $month = (int) $matches[1];
                        if (!isset($uniqueMonthVideos[$month])) {
                            $uniqueMonthVideos[$month] = $vid;
                        }
                    }
                }
                ksort($uniqueMonthVideos);
                $formattedVideos = array_values($uniqueMonthVideos);
            } else {
                $formattedVideos = array_slice($formattedVideos, 0, 4);
            }

            $response[] = [
                'id' => $category->id,
                'title' => $category->category_name,
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
                $videoPath = str_replace(' ', '%20', "AI Baby Video/{$categoryName}/video/{$file}");
                $videoThumbnailPath = $thumbnail ? str_replace(' ', '%20', "AI Baby Video/{$categoryName}/video thumbanail/{$thumbnail}") : null;

                $formattedVideos[] = [
                    'url' => $videoPath,
                    'thumbnail' => $videoThumbnailPath,
                    'prompt' => $prompt,
                    'video_title' => $videoTitle,
                    'name_change' => $nameChange,
                ];
            }
        }

        $subCategoryResponse = [
            'id' => $category->id,
            'title' => $category->category_name,
            'description' => '',
            'videos' => $formattedVideos,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Subcategories retrieved successfully',
            'model' => $videoModel,
            'main_category' => $categoryName,
            'category_name' => $categoryName,
            'subcategories' => [$subCategoryResponse],
        ]);
    }

    public function trending()
    {
        $trendingCategories = AiVideoCategory::where('trending', 1)->where('status', 1)->get();

        $data = $trendingCategories->map(function ($category) {
            $categoryName = trim($category->category_name);

            $thumbnailPath = $category->category_image
                ? str_replace(' ', '%20', "AI Baby Video/{$categoryName}/category thumbanail/{$category->category_image}")
                : null;

            return [
                'id' => $category->id,
                'main_category_name' => $categoryName,
                'name' => $categoryName,
                'thumbnail' => $thumbnailPath,
                'description' => '',
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Trending video categories retrieved successfully',
            'data' => $data,
        ]);
    }
}
