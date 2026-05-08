<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DynamicPhotoFrameCategory;
use App\Models\DynamicPhotoFrame;
use Illuminate\Http\Request;

class DynamicPhotoFrameApiController extends Controller
{
    public function getDynamicPhotoFrameCategories()
    {
        $categories = DynamicPhotoFrameCategory::select('id', 'category_name', 'image', 'sort_order')
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No categories found',
                'data'    => [],
            ], 404);
        }

        $data = $categories->map(function ($category) {
            return [
                'id'            => $category->id,
                'category_name' => $category->category_name,
                'full_url'      => $category->image
                    ? $this->buildAssetUrl(['upload', 'dynamic_photo_frame', $category->category_name, 'category image', $category->image])
                    : null,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Categories fetched successfully',
            'data'    => $data,
        ]);
    }

    public function getDynamicPhotoFrameByCategoryId(Request $request)
    {
        $data = $request->isJson() ? $request->json()->all() : $request->all();

        $validator = \Validator::make($data, [
            'category_id' => 'required|integer',
        ], [
            'category_id.required' => 'category_id is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ], 422);
        }

        $category = DynamicPhotoFrameCategory::where('id', $data['category_id'])
            ->where('status', 1)
            ->first();

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found',
                'data'    => [],
            ], 404);
        }

        $frames = DynamicPhotoFrame::where('dynamic_photo_frame_category_id', $category->id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($frames->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No frames found for this category',
                'data'    => [],
            ], 404);
        }

        $categoryName = $category->category_name;

        $transformed = $frames->map(function ($frame) use ($categoryName) {
            return [
                'id'                 => $frame->id,
                'zip_file_full_url'  => $frame->zip_file
                    ? $this->buildAssetUrl(['upload', 'dynamic_photo_frame', $categoryName, 'zip', $frame->zip_file])
                    : null,
                'input_count'        => (int) $frame->input_count,
                'thumbnail_full_url' => $frame->thumbnail
                    ? $this->buildAssetUrl(['upload', 'dynamic_photo_frame', $categoryName, 'thumbnail', $frame->thumbnail])
                    : null,
            ];
        });

        return response()->json([
            'status'        => true,
            'message'       => 'Frames fetched successfully',
            'category_name' => $category->category_name,
            'data'          => $transformed,
        ]);
    }

    private function buildAssetUrl(array $segments): string
    {
        $encoded = array_map('rawurlencode', $segments);
        return asset(implode('/', $encoded));
    }
}
