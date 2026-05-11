<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BabyAiHomeSlider;

class BabyAiHomeSliderApiController extends Controller
{
    public function getHomeScreenSlider()
    {
        $sliders = BabyAiHomeSlider::where('status', 1)
            ->where('is_on', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($sliders->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No home screen slider data found',
                'data'    => [],
            ], 404);
        }

        $data = $sliders->map(function ($slider) {
            $base = ['upload', 'baby_ai_home_slider', $slider->source_type];

            $row = [
                'id'            => $slider->id,
                'source_type'   => $slider->source_type,
                'source_id'     => (int) $slider->source_id,
                'source_name'   => $slider->source_name,
                'title'         => $slider->title,
                'description'   => $slider->description,
            ];

            if ($slider->source_type === 'video') {
                $row['video_url']           = $slider->video ? $this->buildAssetUrl(array_merge($base, [$slider->video])) : null;
                $row['video_thumbnail_url'] = $slider->video_thumbnail ? $this->buildAssetUrl(array_merge($base, [$slider->video_thumbnail])) : null;
            } else {
                $row['image_url'] = $slider->image ? $this->buildAssetUrl(array_merge($base, [$slider->image])) : null;
            }

            return $row;
        });

        return response()->json([
            'status'  => true,
            'message' => 'Home screen slider data fetched successfully',
            'data'    => $data,
        ]);
    }

    private function buildAssetUrl(array $segments): string
    {
        $encoded = array_map('rawurlencode', $segments);
        return asset(implode('/', $encoded));
    }
}
