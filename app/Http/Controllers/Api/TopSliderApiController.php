<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TopSliderCategory;
use Illuminate\Http\Request;

class TopSliderApiController extends Controller
{
    public function getTopSlider()
    {
        $categories = TopSliderCategory::where('status', 1)
            ->where('top_slider_is_on', 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No top slider data found',
                'data' => [],
            ], 404);
        }

        $formattedData = $categories->map(function ($category) {
            $categoryData = [
                'id' => $category->id,
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'title' => $category->title,
                'description' => $category->description,
                'file_type' => $category->file_type,
            ];

            if ($category->file_type === 'image') {
                $categoryData['image_url'] = $category->image ? asset('upload/top_slider/categories/' . rawurlencode($category->category_name) . '/' . rawurlencode($category->image)) : null;
            } elseif ($category->file_type === 'video') {
                $categoryData['video_url'] = $category->video ? asset('upload/top_slider/categories/' . rawurlencode($category->category_name) . '/' . rawurlencode($category->video)) : null;
                $categoryData['video_thumbnail_url'] = $category->video_thumbnail ? asset('upload/top_slider/categories/' . rawurlencode($category->category_name) . '/' . rawurlencode($category->video_thumbnail)) : null;
            }

            return $categoryData;
        });

        return response()->json([
            'status' => true,
            'message' => 'Top slider data fetched successfully',
            'data' => $formattedData,
        ]);
    }
}
