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

    // public function getCategories()
    // {
    //     // Get NGD AI setting
    //     $ngdAiSetting = AiImageNgdSetting::first();
    //     $ngdAiModel = $ngdAiSetting ? $ngdAiSetting->model : null;

    //     $categories = NgendevCategory::select('id', 'category_name')->orderBy('id', 'desc')->get();

    //     if ($categories->isEmpty()) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'message' => 'No categories found',
    //                 'model' => $ngdAiModel,
    //                 'data' => [],
    //             ],
    //             404,
    //         );
    //     }

    //     $categories = $categories->map(function ($category) {
    //         $encodedCategory = str_replace(' ', '%20', $category->category_name);

    //         $images = NgendevImage::where('category_id', $category->id)->select('id', 'ai_prompt', 'image_path')->orderBy('id', 'desc')->limit(5)->get();

    //         $images->transform(function ($image) use ($encodedCategory) {
    //             $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
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

    //     $latestImages = NgendevImage::select('id', 'ai_prompt', 'image_path', 'category_id')->inRandomOrder()->limit(5)->get();

    //     $latestImages->transform(function ($image) {
    //         $category = NgendevCategory::find($image->category_id);
    //         $encodedCategory = $category ? str_replace(' ', '%20', $category->category_name) : 'Unknown';
    //         $image->category_image = $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null;
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
    //         'model' => $ngdAiModel,
    //         'data' => $categories->values(),
    //     ]);
    // }

    public function getCategories()
    {
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

        // Map categories with images
        $categories = $categories->map(function ($category) {
            $encodedCategory = str_replace(' ', '%20', $category->category_name);

            $images = NgendevImage::where('category_id', $category->id)
                ->select('id', 'ai_prompt', 'image_path')
                ->orderBy('id', 'desc') // latest first
                ->get();

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

        // Separate Trending category
        $trending = $categories->firstWhere('category_name', 'Trending');
        if ($trending) {
            $categories = $categories->reject(fn($cat) => $cat['category_name'] === 'Trending');
        }

        // Latest category: last record from all other categories
        $latestImages = $categories
            ->filter(fn($cat) => $cat['items']->isNotEmpty())
            ->map(fn($cat) => $cat['items']->first()) // latest record per category
            ->filter()
            ->values();

        // 🔥 Add Trending's last record at the end of Latest category
        if ($trending && $trending['items']->isNotEmpty()) {
            $trendingLastRecord = $trending['items']->first(); // latest record
            $latestImages->push($trendingLastRecord); // add to end
        }

        $latestCategory = [
            'category_id' => 0,
            'category_name' => 'Latest',
            'items' => $latestImages,
        ];

        // Insert Latest as second category
        $categories->splice(0, 0, [$trending]); // Trending first
        $categories->splice(1, 0, [$latestCategory]); // Latest second

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
            // Fetch all categories
            $categories = NgendevCategory::orderBy('id', 'asc')->get();

            // Separate trending category
            $trendingCategory = $categories->firstWhere('category_name', 'Trending');
            $categories = $categories->reject(fn($cat) => $cat->category_name === 'Trending');

            // Fetch latest image per category (excluding Trending)
            $latestImages = collect();
            foreach ($categories as $category) {
                $latestImage = NgendevImage::where('category_id', $category->id)->select('id', 'ai_prompt', 'image_path', 'category_id')->orderBy('id', 'desc')->first();

                if ($latestImage) {
                    $encodedCategory = str_replace(' ', '%20', $category->category_name);
                    $latestImages->push([
                        'id' => $latestImage->id,
                        'ai_prompt' => $latestImage->ai_prompt,
                        'category_image' => $latestImage->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$latestImage->image_path}" : null,
                    ]);
                }
            }

            // Add Trending category last (static)
            if ($trendingCategory) {
                $trendingImage = NgendevImage::where('category_id', $trendingCategory->id)->select('id', 'ai_prompt', 'image_path', 'category_id')->orderBy('id', 'desc')->first();

                if ($trendingImage) {
                    $encodedTrending = str_replace(' ', '%20', $trendingCategory->category_name);
                    $latestImages->push([
                        'id' => $trendingImage->id,
                        'ai_prompt' => $trendingImage->ai_prompt,
                        'category_image' => $trendingImage->image_path ? "ngendev/images/{$encodedTrending}/category_image/{$trendingImage->image_path}" : null,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Latest images fetched successfully',
                'model' => $ngdAiModel,
                'data' => $latestImages,
            ]);
        }

        // When category_id != 0
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

        $images->transform(
            fn($image) => [
                'id' => $image->id,
                'ai_prompt' => $image->ai_prompt,
                'category_image' => $image->image_path ? "ngendev/images/{$encodedCategory}/category_image/{$image->image_path}" : null,
            ],
        );

        return response()->json([
            'status' => true,
            'message' => 'Images fetched successfully',
            'model' => $ngdAiModel,
            'data' => $images,
        ]);
    }
}
