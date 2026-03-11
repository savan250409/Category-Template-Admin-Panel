<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NgendevVideoCategory;
use App\Models\NgendevVideo;
use Illuminate\Http\Request;
use App\Models\AiVideoNgdSetting;

class NgendevVideoApiController extends Controller
{
    public function getAiVideoCategories()
    {
        $ngdAiSetting = AiVideoNgdSetting::first();
        $ngdAiModel = $ngdAiSetting ? $ngdAiSetting->model : null;
        $coupleActive = $ngdAiSetting ? $ngdAiSetting->couple_active : 1;

        $query = NgendevVideoCategory::select('id', 'category_name', 'sort_order', 'type')
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

        // Map categories with videos
        $categories = $categories->map(function ($category) {
            $encodedCategory = str_replace(' ', '%20', $category->category_name);

            $videos = NgendevVideo::where('category_id', $category->id)
                ->select('id', 'ai_prompt', 'video_thumbnail', 'video_path', 'no_of_video', 'name_change')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $videos->transform(function ($video) use ($encodedCategory) {
                $video->video_thumbnail = $video->video_thumbnail ? "ngendev/videos/{$encodedCategory}/video_thumbnail/{$video->video_thumbnail}" : null;
                $video->category_video = $video->video_path ? "ngendev/videos/{$encodedCategory}/category_video/{$video->video_path}" : null;
                $video->name_change = (bool) $video->name_change;
                unset($video->video_path);
                return $video;
            });

            return [
                'category_id' => $category->id,
                'category_name' => $category->category_name,
                'items' => $videos,
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
        $latestVideos = $categories
            ->filter(fn($cat) => $cat['items']->isNotEmpty())
            ->map(fn($cat) => $cat['items']->first()) // latest record per category
            ->filter()
            ->values();

        if ($exclusive && $exclusive['items']->isNotEmpty()) {
            $exclusiveLastRecord = $exclusive['items']->first(); // latest record
            $latestVideos->push($exclusiveLastRecord);
        }

        if ($trending && $trending['items']->isNotEmpty()) {
            $trendingLastRecord = $trending['items']->first(); // latest record
            $latestVideos->push($trendingLastRecord);
        }

        $latestCategory = [
            'category_id' => 0,
            'category_name' => 'Latest',
            'items' => $latestVideos,
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

    public function getAiVideoByCategoryId(Request $request)
    {
        $data = $request->json()->all();

        $validator = \Validator::make($data, [
            'category_id' => 'required',
        ], [
            'category_id.required' => 'category_id is required',
        ]);

        $ngdAiSetting = AiVideoNgdSetting::first();
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

            $categories = NgendevVideoCategory::where('status', 1)
                ->orderBy('id', 'asc')
                ->get();

            $trendingCategory = $categories->firstWhere('category_name', 'Trending');
            $categories = $categories->reject(fn($cat) => $cat->category_name === 'Trending');

            $latestVideos = collect();

            foreach ($categories as $category) {
                $latestVideo = NgendevVideo::where('category_id', $category->id)
                    ->select('id', 'ai_prompt', 'video_thumbnail', 'video_path', 'category_id', 'no_of_video', 'name_change')
                    ->orderBy('sort_order', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestVideo) {
                    $encodedCategory = str_replace(' ', '%20', $category->category_name);
                    $latestVideos->push([
                        'id' => $latestVideo->id,
                        'ai_prompt' => $latestVideo->ai_prompt,
                        'no_of_video' => $latestVideo->no_of_video,
                        'name_change' => (bool) $latestVideo->name_change,
                        'video_thumbnail' => $latestVideo->video_thumbnail
                            ? "ngendev/videos/{$encodedCategory}/video_thumbnail/{$latestVideo->video_thumbnail}"
                            : null,
                        'category_video' => $latestVideo->video_path
                            ? "ngendev/videos/{$encodedCategory}/category_video/{$latestVideo->video_path}"
                            : null,
                    ]);
                }
            }

            if ($trendingCategory) {
                $trendingVideo = NgendevVideo::where('category_id', $trendingCategory->id)
                    ->select('id', 'ai_prompt', 'video_thumbnail', 'video_path', 'category_id', 'no_of_video', 'name_change')
                    ->orderBy('sort_order', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($trendingVideo) {
                    $encodedTrending = str_replace(' ', '%20', $trendingCategory->category_name);
                    $latestVideos->push([
                        'id' => $trendingVideo->id,
                        'ai_prompt' => $trendingVideo->ai_prompt,
                        'no_of_video' => $trendingVideo->no_of_video,
                        'name_change' => (bool) $trendingVideo->name_change,
                        'video_thumbnail' => $trendingVideo->video_thumbnail
                            ? "ngendev/videos/{$encodedTrending}/video_thumbnail/{$trendingVideo->video_thumbnail}"
                            : null,
                        'category_video' => $trendingVideo->video_path
                            ? "ngendev/videos/{$encodedTrending}/category_video/{$trendingVideo->video_path}"
                            : null,
                    ]);
                }
            }

            $latestVideos = $latestVideos->reverse()->values();

            return response()->json([
                'status' => true,
                'message' => 'Latest videos fetched successfully',
                'model' => $ngdAiModel,
                'data' => $latestVideos,
            ]);
        }

        /**
         * ===========================
         * SINGLE CATEGORY
         * ===========================
         */
        $category = NgendevVideoCategory::where('id', $data['category_id'])
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

        $videos = NgendevVideo::where('category_id', $data['category_id'])
            ->select('id', 'video_thumbnail', 'video_path', 'ai_prompt', 'no_of_video', 'name_change')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($videos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No videos found for this category',
                'model' => $ngdAiModel,
                'data' => [],
            ], 404);
        }

        $videos->transform(fn($video) => [
            'id' => $video->id,
            'ai_prompt' => $video->ai_prompt,
            'no_of_video' => $video->no_of_video,
            'name_change' => (bool) $video->name_change,
            'video_thumbnail' => $video->video_thumbnail
                ? "ngendev/videos/{$encodedCategory}/video_thumbnail/{$video->video_thumbnail}"
                : null,
            'category_video' => $video->video_path
                ? "ngendev/videos/{$encodedCategory}/category_video/{$video->video_path}"
                : null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Videos fetched successfully',
            'model' => $ngdAiModel,
            'data' => $videos,
        ]);
    }

    public function getAllCategoryNames()
    {
        $categories = NgendevVideoCategory::select('id', 'category_name')
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
