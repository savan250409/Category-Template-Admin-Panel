<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NgendevCategory;
use App\Models\NgendevImage;
use Illuminate\Http\Request;

class NgendevCategoryApiController extends Controller
{
    public function getCategories()
    {
        $categories = NgendevCategory::select('id', 'category_name')->orderBy('id', 'desc')->get();

        if ($categories->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No categories found',
                    'data' => [],
                ],
                404,
            );
        }

        $categories->transform(function ($category) {
            $encodedCategory = str_replace(' ', '%20', $category->category_name);

            // Fetch 3 AI images per category
            $images = NgendevImage::where('category_id', $category->id)->select('id', 'ai_prompt', 'ai_model', 'image_path')->orderBy('id', 'desc')->limit(3)->get();

            // Format images
            $images->transform(function ($image) use ($encodedCategory) {
                $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
                $image->ai_model = $image->ai_model ?? 'AI Image';
                unset($image->image_path);
                return $image;
            });

            // Return category with items
            return [
                'category_id' => $category->id,
                'category_name' => $category->category_name,
                'items' => $images,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'data' => $categories,
        ]);
    }

    public function getAiImageByCategoryId(Request $request)
    {
        $data = $request->json()->all();

        $validator = \Validator::make(
            $data,
            [
                'category_id' => 'required|exists:ngendev_categories,id',
            ],
            [
                'category_id.required' => 'category_id is required',
                'category_id.exists' => 'category_id is invalid',
            ],
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => [],
                ],
                422,
            );
        }

        $category = NgendevCategory::find($data['category_id']);
        $encodedCategory = str_replace(' ', '%20', $category->category_name);

        $images = NgendevImage::where('category_id', $data['category_id'])->select('id', 'image_path', 'ai_prompt', 'ai_model')->orderBy('id', 'desc')->get();

        if ($images->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No images found for this category',
                    'data' => [],
                ],
                404,
            );
        }

        $images->transform(function ($image) use ($encodedCategory) {
            $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
            $image->ai_model = $image->ai_model ?? 'AI Image';
            unset($image->image_path);
            return $image;
        });

        return response()->json([
            'status' => true,
            'message' => 'Images fetched successfully',
            'data' => $images,
        ]);
    }
}
