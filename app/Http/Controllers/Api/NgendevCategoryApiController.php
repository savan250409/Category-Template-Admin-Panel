<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NgendevCategory;
use App\Models\NgendevImage;
use Illuminate\Http\Request;
use App\Models\AiImageNgdSetting;

class NgendevCategoryApiController extends Controller
{
    // public function getCategories()
    // {
    //     $categories = NgendevCategory::select('id', 'category_name')->orderBy('id', 'desc')->get();

    //     if ($categories->isEmpty()) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'message' => 'No categories found',
    //                 'data' => [],
    //             ],
    //             404,
    //         );
    //     }

    //     $categories = $categories->map(function ($category) {
    //         $encodedCategory = str_replace(' ', '%20', $category->category_name);

    //         $images = NgendevImage::where('category_id', $category->id)->select('id', 'ai_prompt', 'ai_model', 'image_path')->orderBy('id', 'desc')->limit(5)->get();

    //         $images->transform(function ($image) use ($encodedCategory, $category) {
    //             $image->category_image = $image->image_path ? "ngendev/images/{$category->category_name}/category_image/{$image->image_path}" : null;
    //             $image->ai_model = $image->ai_model ?? 'Ngendev Image';
    //             unset($image->image_path);
    //             return $image;
    //         });

    //         return [
    //             'category_id' => $category->id,
    //             'category_name' => $category->category_name,
    //             'items' => $images,
    //         ];
    //     });

    //     $trending = $categories->firstWhere('category_name', 'Trending');
    //     if ($trending) {
    //         $categories = $categories->reject(function ($cat) {
    //             return $cat['category_name'] === 'Trending';
    //         });
    //         $categories->prepend($trending);
    //     }

    //     $latestImages = NgendevImage::select('id', 'ai_prompt', 'ai_model', 'image_path', 'category_id')->orderBy('id', 'desc')->limit(5)->get();

    //     $latestImages->transform(function ($image) {
    //         $category = NgendevCategory::find($image->category_id);
    //         $encodedCategory = $category ? str_replace(' ', '%20', $category->category_name) : 'Unknown';
    //         $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
    //         $image->ai_model = $image->ai_model ?? 'Ngendev Image';
    //         unset($image->image_path, $image->category_id);
    //         return $image;
    //     });

    //     $latestCategory = [
    //         'category_id' => 0,
    //         'category_name' => 'Latest',
    //         'items' => $latestImages,
    //     ];

    //     $categories->splice(1, 0, [$latestCategory]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Categories fetched successfully',
    //         'data' => $categories->values(),
    //     ]);
    // }

    // public function getAiImageByCategoryId(Request $request)
    // {
    //     $data = $request->json()->all();

    //     $validator = \Validator::make(
    //         $data,
    //         [
    //             'category_id' => 'required',
    //         ],
    //         [
    //             'category_id.required' => 'category_id is required',
    //         ],
    //     );

    //     if ($validator->fails()) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'message' => $validator->errors()->first(),
    //                 'data' => [],
    //             ],
    //             422,
    //         );
    //     }

    //     if ($data['category_id'] == 0) {
    //         $images = NgendevImage::select('id', 'ai_prompt', 'ai_model', 'image_path', 'category_id')->orderBy('id', 'desc')->limit(10)->get();

    //         $images->transform(function ($image) {
    //             $category = NgendevCategory::find($image->category_id);
    //             $encodedCategory = $category ? str_replace(' ', '%20', $category->category_name) : 'Unknown';
    //             $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
    //             $image->ai_model = $image->ai_model ?? 'Ngendev Image';
    //             unset($image->image_path, $image->category_id);
    //             return $image;
    //         });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Latest images fetched successfully',
    //             'data' => $images,
    //         ]);
    //     }

    //     $category = NgendevCategory::find($data['category_id']);
    //     if (!$category) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'message' => 'Category not found',
    //                 'data' => [],
    //             ],
    //             404,
    //         );
    //     }

    //     $encodedCategory = str_replace(' ', '%20', $category->category_name);

    //     $images = NgendevImage::where('category_id', $data['category_id'])->select('id', 'image_path', 'ai_prompt', 'ai_model')->orderBy('id', 'desc')->get();

    //     if ($images->isEmpty()) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'message' => 'No images found for this category',
    //                 'data' => [],
    //             ],
    //             404,
    //         );
    //     }

    //     $images->transform(function ($image) use ($encodedCategory) {
    //         $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
    //         $image->ai_model = $image->ai_model ?? 'Ngendev Image';
    //         unset($image->image_path);
    //         return $image;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Images fetched successfully',
    //         'data' => $images,
    //     ]);
    // }

    public function getCategories()
    {
        // Get NGD AI setting
        $ngdAiSetting = AiImageNgdSetting::first();
        $ngdAiModel = $ngdAiSetting ? $ngdAiSetting->model : null;

        $categories = NgendevCategory::select('id', 'category_name')->orderBy('id', 'desc')->get();

        if ($categories->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No categories found',
                    'model' => $ngdAiModel,
                    'data' => [],
                ],
                404,
            );
        }

        $categories = $categories->map(function ($category) {
            $encodedCategory = str_replace(' ', '%20', $category->category_name);

            $images = NgendevImage::where('category_id', $category->id)->select('id', 'ai_prompt', 'image_path')->orderBy('id', 'desc')->limit(5)->get();

            $images->transform(function ($image) use ($encodedCategory) {
                $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
                unset($image->image_path);
                return $image;
            });

            return [
                'category_id' => $category->id,
                'category_name' => $category->category_name,
                'items' => $images,
            ];
        });

        $trending = $categories->firstWhere('category_name', 'Trending');
        if ($trending) {
            $categories = $categories->reject(function ($cat) {
                return $cat['category_name'] === 'Trending';
            });
            $categories->prepend($trending);
        }

        $latestImages = NgendevImage::select('id', 'ai_prompt', 'image_path', 'category_id')->inRandomOrder()->limit(5)->get();

        $latestImages->transform(function ($image) {
            $category = NgendevCategory::find($image->category_id);
            $encodedCategory = $category ? str_replace(' ', '%20', $category->category_name) : 'Unknown';
            $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
            unset($image->image_path, $image->category_id);
            return $image;
        });

        $latestCategory = [
            'category_id' => 0,
            'category_name' => 'Latest',
            'items' => $latestImages,
        ];

        $categories->splice(1, 0, [$latestCategory]);

        return response()->json([
            'status' => true,
            'message' => 'Categories fetched successfully',
            'model' => $ngdAiModel,
            'data' => $categories->values(),
        ]);
    }

    public function getAiImageByCategoryId(Request $request)
    {
        $data = $request->json()->all();

        $validator = \Validator::make(
            $data,
            [
                'category_id' => 'required',
            ],
            [
                'category_id.required' => 'category_id is required',
            ],
        );

        // Get NGD AI setting
        $ngdAiSetting = AiImageNgdSetting::first();
        $ngdAiModel = $ngdAiSetting ? $ngdAiSetting->model : null;

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'model' => $ngdAiModel,
                    'data' => [],
                ],
                422,
            );
        }

        if ($data['category_id'] == 0) {
            $images = NgendevImage::select('id', 'ai_prompt', 'image_path', 'category_id')->inRandomOrder()->limit(10)->get();

            $images->transform(function ($image) {
                $category = NgendevCategory::find($image->category_id);
                $encodedCategory = $category ? str_replace(' ', '%20', $category->category_name) : 'Unknown';
                $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
                unset($image->image_path, $image->category_id);
                return $image;
            });

            return response()->json([
                'status' => true,
                'message' => 'Latest images fetched successfully',
                'model' => $ngdAiModel,
                'data' => $images,
            ]);
        }

        $category = NgendevCategory::find($data['category_id']);
        if (!$category) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Category not found',
                    'model' => $ngdAiModel,
                    'data' => [],
                ],
                404,
            );
        }

        $encodedCategory = str_replace(' ', '%20', $category->category_name);

        $images = NgendevImage::where('category_id', $data['category_id'])->select('id', 'image_path', 'ai_prompt')->orderBy('id', 'desc')->get();

        if ($images->isEmpty()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'No images found for this category',
                    'model' => $ngdAiModel,
                    'data' => [],
                ],
                404,
            );
        }

        $images->transform(function ($image) use ($encodedCategory) {
            $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
            unset($image->image_path);
            return $image;
        });

        return response()->json([
            'status' => true,
            'message' => 'Images fetched successfully',
            'model' => $ngdAiModel,
            'data' => $images,
        ]);
    }
}
