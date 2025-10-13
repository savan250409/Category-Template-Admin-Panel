<?php

namespace App\Http\Controllers\Api;
use App\Models\AiImageCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\AiImageBabyPhotoSetting;

class CategoryController extends Controller
{
    public function getAllCategories()
    {
        // Get Baby AI setting
        $babyAiSetting = AiImageBabyPhotoSetting::first();
        $babyAiModel = $babyAiSetting ? $babyAiSetting->model : null;

        // Get only active categories from AiImageCategory table
        $activeCategories = AiImageCategory::where('status', 1)->pluck('name');

        $response = [];

        foreach ($activeCategories as $category) {
            $subcategories = Subcategory::where('category_name', $category)
                ->orderBy('id', 'desc')
                ->get(['id', 'title', 'category_name', 'category_thumbnail_image']);

            $subcategories->transform(function ($subcat) {
                $categoryName = trim($subcat->category_name);
                $subcatTitle = trim($subcat->title);

                $thumbnailPath = $subcat->category_thumbnail_image ? "{$categoryName}/{$subcatTitle}/category_thumbnail/{$subcat->category_thumbnail_image}" : null;

                return [
                    'id' => $subcat->id,
                    'title' => $subcat->title,
                    'thumbnail' => $thumbnailPath,
                ];
            });

            $response[] = [
                'category_name' => $category,
                'subcategories' => $subcategories,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'model' => $babyAiModel,
            'data' => $response,
        ]);
    }

    public function getSubcategoriesByCategory(Request $request)
    {
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

        // Check if the main category is active
        $category = AiImageCategory::where('name', $mainCategory)->first();

        if (!$category || $category->status != 1) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'This category is not active',
                    'main_category' => $mainCategory,
                    'subcategories' => [],
                ],
                403,
            ); // 403 Forbidden
        }

        // Get Baby AI setting
        $babyAiSetting = AiImageBabyPhotoSetting::first();
        $babyAiModel = $babyAiSetting ? $babyAiSetting->model : null;

        // Get subcategory
        $subcategories = Subcategory::where('category_name', $mainCategory)
            ->where('title', $subCategoryName)
            ->get(['id', 'title', 'description', 'images', 'category_name']);

        if ($subcategories->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No data found for the given main category and subcategory',
                    'model' => $babyAiModel,
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
            unset($subcat->category_name);

            return $subcat;
        });

        return response()->json([
            'status' => true,
            'message' => 'Subcategories retrieved successfully',
            'model' => $babyAiModel,
            'main_category' => $mainCategory,
            'category_name' => $subCategoryName,
            'subcategories' => $subcategories,
        ]);
    }
}
