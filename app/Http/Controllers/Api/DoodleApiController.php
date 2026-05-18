<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doodle;

class DoodleApiController extends Controller
{
    public function getDoodles()
    {
        $doodles = Doodle::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        if ($doodles->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No doodles found',
                'data'    => [],
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        $data = $doodles->map(function ($doodle) {
            return [
                'id'          => $doodle->id,
                'name'        => $doodle->name,
                'doodle_type' => $doodle->doodle_type,
                'is_premium'  => (bool) $doodle->is_premium,
                'image_url'   => $doodle->image
                    ? $this->buildAssetUrl(['doodle', $doodle->name, $doodle->image])
                    : null,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Doodles fetched successfully',
            'data'    => $data,
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    private function buildAssetUrl(array $segments): string
    {
        $segments = array_merge(['upload'], $segments);
        $encoded  = array_map('rawurlencode', $segments);
        return asset(implode('/', $encoded));
    }
}
