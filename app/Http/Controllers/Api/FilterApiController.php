<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;

class FilterApiController extends Controller
{
    public function getAllFilters()
    {
        $categories = FilterCategory::with(['filters' => function ($q) {
            $q->where('status', 1)
              ->orderBy('sort_order', 'asc')
              ->orderBy('id', 'asc');
        }])
        ->where('status', 1)
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'asc')
        ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No filters found',
                'data'    => [],
            ], 404);
        }

        $data = $categories->map(function ($category) {
            return [
                'id'        => $category->id,
                'name'      => $category->name,
                'image_url' => $category->image
                    ? $this->buildAssetUrl(['filter', $category->name, 'category image', $category->image])
                    : null,
                'filters'   => $category->filters->map(function ($filter) {
                    return [
                        'id'         => $filter->id,
                        'name'       => $filter->name,
                        'is_premium' => (bool) $filter->is_premium,
                        'values'     => [
                            'saturation' => (float) $filter->saturation,
                            'brightness' => (float) $filter->brightness,
                            'contrast'   => (float) $filter->contrast,
                            'red'        => (float) $filter->red,
                            'green'      => (float) $filter->green,
                            'blue'       => (float) $filter->blue,
                        ],
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Filters fetched successfully',
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
