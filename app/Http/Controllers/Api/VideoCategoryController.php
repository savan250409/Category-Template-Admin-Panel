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
                    $videoPath = "AI Baby Video/{$categoryName}/video/{$file}";
                    $videoPathArr = explode('/', $videoPath);
                    $encodedVideoPath = implode('/', array_map('rawurlencode', $videoPathArr));

                    $videoThumbnailPath = $thumbnail ? "AI Baby Video/{$categoryName}/video thumbanail/{$thumbnail}" : null;
                    if ($videoThumbnailPath) {
                        $thumbnailPathArr = explode('/', $videoThumbnailPath);
                        $encodedThumbnailPath = implode('/', array_map('rawurlencode', $thumbnailPathArr));
                    } else {
                        $encodedThumbnailPath = null;
                    }

                    $formattedVideos[] = [
                        'url' => str_replace(' ', '%20', $videoPath),
                        'url_full_url' => asset('upload/' . $encodedVideoPath),
                        'thumbnail' => $videoThumbnailPath ? str_replace(' ', '%20', $videoThumbnailPath) : null,
                        'thumbnail_full_url' => $encodedThumbnailPath ? asset('upload/' . $encodedThumbnailPath) : null,
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
                $formattedVideos = array_slice($formattedVideos, 0, 3);
            }

            $thumbnailPathSegments = $category->category_image ? explode('/', "AI Baby Video/{$categoryName}/category thumbanail/{$category->category_image}") : [];
            $encodedThumbnailPathMain = $category->category_image ? implode('/', array_map('rawurlencode', $thumbnailPathSegments)) : null;

            $response[] = [
                'id' => $category->id,
                'title' => $category->category_name,
                'thumbnail' => $thumbnailPath,
                'thumbnail_full_url' => $encodedThumbnailPathMain ? asset('upload/' . $encodedThumbnailPathMain) : null,
                'description' => $category->description ?? '',
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
                $videoPath = "AI Baby Video/{$categoryName}/video/{$file}";
                $videoPathArr = explode('/', $videoPath);
                $encodedVideoPath = implode('/', array_map('rawurlencode', $videoPathArr));

                $videoThumbnailPath = $thumbnail ? "AI Baby Video/{$categoryName}/video thumbanail/{$thumbnail}" : null;
                if ($videoThumbnailPath) {
                    $thumbnailPathArr = explode('/', $videoThumbnailPath);
                    $encodedThumbnailPath = implode('/', array_map('rawurlencode', $thumbnailPathArr));
                } else {
                    $encodedThumbnailPath = null;
                }

                $formattedVideos[] = [
                    'url' => str_replace(' ', '%20', $videoPath),
                    'url_full_url' => asset('upload/' . $encodedVideoPath),
                    'thumbnail' => $videoThumbnailPath ? str_replace(' ', '%20', $videoThumbnailPath) : null,
                    'thumbnail_full_url' => $encodedThumbnailPath ? asset('upload/' . $encodedThumbnailPath) : null,
                    'prompt' => $prompt,
                    'video_title' => $videoTitle,
                    'name_change' => $nameChange,
                ];
            }
        }

        $subCategoryResponse = [
            'id' => $category->id,
            'title' => $category->category_name,
            'description' => $category->description ?? '',
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

            $thumbnailPathSegments = $category->category_image ? explode('/', "AI Baby Video/{$categoryName}/category thumbanail/{$category->category_image}") : [];
            $encodedThumbnailPathMain = $category->category_image ? implode('/', array_map('rawurlencode', $thumbnailPathSegments)) : null;

            return [
                'id' => $category->id,
                'main_category_name' => $categoryName,
                'name' => $categoryName,
                'thumbnail' => $thumbnailPath,
                'thumbnail_full_url' => $encodedThumbnailPathMain ? asset('upload/' . $encodedThumbnailPathMain) : null,
                'description' => $category->description ?? '',
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Trending video categories retrieved successfully',
            'data' => $data,
        ]);
    }
    public function getAllCategoryNames()
    {
        $categories = AiVideoCategory::select('id', 'category_name')
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
