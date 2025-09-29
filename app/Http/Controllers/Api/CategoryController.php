<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;

class CategoryController extends Controller
{
    public function getAllCategories()
    {
        // Get all distinct category names
        $categories = Subcategory::select('category_name')->distinct()->pluck('category_name');

        $response = [];

        foreach ($categories as $category) {
            // Fetch max 5 subcategories per category
            $subcategories = Subcategory::where('category_name', $category)
                ->limit(5)
                ->get(['id', 'title', 'description', 'images', 'category_name']);

            // Transform images
            $subcategories->transform(function ($subcat) {
                $images = json_decode($subcat->images, true) ?? [];
                $formattedImages = [];

                $categoryName = trim($subcat->category_name);
                $subcatTitle = trim($subcat->title);

                foreach ($images as $img) {
                    $file = $img['file'] ?? null;
                    $prompt = $img['prompt'] ?? '';

                    if ($file) {
                        $formattedImages[] = [
                            'url' => "{$categoryName}/{$subcatTitle}/{$file}",
                            'prompt' => $prompt,
                        ];
                    }
                }

                $subcat->images = $formattedImages;
                unset($subcat->category_name); // remove category_name from response
                return $subcat;
            });

            // Limit to only 1 subcategory per category in the response
            $response[] = [
                'category_name' => $category,
                'subcategories' => $subcategories->take(1), // take only first subcategory
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $response,
        ]);
    }

    public function getSubcategoriesByCategory(Request $request)
    {
        // Validate input
        $validator = \Validator::make($request->all(), [
            'main_category' => 'required|string',
            'category_name' => 'required|string',
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

        $mainCategory = $request->main_category;
        $subCategoryName = $request->category_name;

        // Fetch subcategories
        $subcategories = Subcategory::where('category_name', $mainCategory)
            ->where('title', $subCategoryName)
            ->get(['id', 'title', 'description', 'images', 'category_name']);

        // Check if no data found
        if ($subcategories->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No data found for the given main category and subcategory',
                    'main_category' => $mainCategory,
                    'category_name' => $subCategoryName,
                    'subcategories' => [],
                ],
                200,
            );
        }

        // Format images
        $subcategories->transform(function ($subcat) {
            $images = json_decode($subcat->images, true) ?? [];
            $formattedImages = [];

            $categoryName = trim($subcat->category_name);
            $subcatTitle = trim($subcat->title);

            foreach ($images as $img) {
                $file = $img['file'] ?? null;
                $prompt = $img['prompt'] ?? '';

                if ($file) {
                    $formattedImages[] = [
                        'url' => "{$categoryName}/{$subcatTitle}/{$file}",
                        'prompt' => $prompt,
                    ];
                }
            }

            $subcat->images = $formattedImages;
            unset($subcat->category_name); // remove category_name from response
            return $subcat;
        });

        return response()->json([
            'status' => true,
            'message' => 'Subcategories retrieved successfully',
            'main_category' => $mainCategory,
            'category_name' => $subCategoryName,
            'subcategories' => $subcategories,
        ]);
    }
}
