<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StickerCategory;
use Illuminate\Http\Request;

class StickerApiController extends Controller
{
    public function getStickers()
    {
        $categories = StickerCategory::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No sticker categories found',
                'data'    => [],
            ], 404);
        }

        $data = $categories->map(function ($category) {
            $categoryName = $category->category_name;
            $stickers     = is_array($category->stickers) ? $category->stickers : [];

            $stickerUrls = array_values(array_map(function ($filename) use ($categoryName) {
                return $this->buildAssetUrl(['sticker', $categoryName, 'stickers', $filename]);
            }, $stickers));

            return [
                'id'            => $category->id,
                'category_name' => $category->category_name,
                'is_premium'    => (bool) $category->is_premium,
                'thumbnail_url' => $category->image
                    ? $this->buildAssetUrl(['sticker', $categoryName, 'category image', $category->image])
                    : null,
                'stickers'      => $stickerUrls,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Stickers fetched successfully',
            'data'    => $data,
        ]);
    }

    private function buildAssetUrl(array $segments): string
    {
        $segments = array_merge(['upload'], $segments);
        $encoded  = array_map('rawurlencode', $segments);
        return asset(implode('/', $encoded));
    }
}
