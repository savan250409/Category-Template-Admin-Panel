<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LipsSyncCategory;
use App\Models\LipsSyncItem;
use Illuminate\Http\Request;

class LipsSyncApiController extends Controller
{
    public function getLipsSyncCategories()
    {
        $categories = LipsSyncCategory::select('id', 'category_name', 'image', 'sort_order')
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
                    ? $this->buildAssetUrl(['upload', 'lips_sync', $category->category_name, 'category image', $category->image])
                    : null,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Categories fetched successfully',
            'data'    => $data,
        ]);
    }

    public function getLipsSyncByCategoryId(Request $request)
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

        $category = LipsSyncCategory::where('id', $data['category_id'])
            ->where('status', 1)
            ->first();

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found',
                'data'    => [],
            ], 404);
        }

        $items = LipsSyncItem::where('lips_sync_category_id', $category->id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No items found for this category',
                'data'    => [],
            ], 404);
        }

        $categoryName = $category->category_name;

        $transformed = $items->map(function ($item) use ($categoryName) {
            return [
                'id'                       => $item->id,
                'title'                    => $item->title,
                'song_full_url'            => $item->song
                    ? $this->buildAssetUrl(['upload', 'lips_sync', $categoryName, 'song', $item->song])
                    : null,
                'video_full_url'           => $item->video
                    ? $this->buildAssetUrl(['upload', 'lips_sync', $categoryName, 'video', $item->video])
                    : null,
                'video_thumbnail_full_url' => $item->video_thumbnail
                    ? $this->buildAssetUrl(['upload', 'lips_sync', $categoryName, 'thumbnail', $item->video_thumbnail])
                    : null,
            ];
        });

        return response()->json([
            'status'        => true,
            'message'       => 'Items fetched successfully',
            'category_name' => $category->category_name,
            'data'          => $transformed,
        ]);
    }

    /**
     * Build a full asset URL with each path segment URL-encoded
     * (so spaces become %20 and other special characters are safely encoded).
     */
    private function buildAssetUrl(array $segments): string
    {
        $encoded = array_map('rawurlencode', $segments);
        return asset(implode('/', $encoded));
    }
}
