<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Font;

class FontApiController extends Controller
{
    public function getFonts()
    {
        $fonts = Font::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($fonts->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No fonts found',
                'data'    => [],
            ], 404);
        }

        $data = $fonts->map(function ($font) {
            $name = $font->font_name;
            return [
                'id'                => $font->id,
                'font_name'         => $font->font_name,
                'is_premium'        => (bool) $font->is_premium,
                'font_file_url'     => $font->font_file
                    ? $this->buildAssetUrl(['font', $name, $font->font_file])
                    : null,
                'preview_image_url' => $font->preview_image
                    ? $this->buildAssetUrl(['font', $name, $font->preview_image])
                    : null,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Fonts fetched successfully',
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
