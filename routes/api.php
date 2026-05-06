<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SyncController;
use App\Models\Admin;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NgendevCategoryApiController;
use App\Http\Controllers\Api\NgendevVideoApiController;
use App\Http\Controllers\Api\VideoCategoryController;
use App\Http\Controllers\Api\FilterAiImageApiController;
use App\Http\Controllers\Api\TopSliderApiController;
use App\Http\Controllers\Api\LipsSyncApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('/getAllCategories', [CategoryController::class, 'getAllCategories']);
    Route::get('/v2/getAllCategories', [CategoryController::class, 'getAllCategoriesv2']);
    Route::get('/v3/getAllCategories', [CategoryController::class, 'getAllCategoriesv3']);
    Route::post('/getSubcategoriesByCategory', [CategoryController::class, 'getSubcategoriesByCategory']);
    Route::post('/getSubcategoriesByCategoryid', [CategoryController::class, 'getSubcategoriesByCategoryid']);
    Route::get('/trending', [CategoryController::class, 'trending']);

    // Video Module API
    Route::get('/video/getAllCategories', [VideoCategoryController::class, 'getAllCategories']);
    Route::post('/video/getSubcategoriesByCategoryid', [VideoCategoryController::class, 'getSubcategoriesByCategoryid']);
    Route::get('/video/trending', [VideoCategoryController::class, 'trending']);

    Route::get('/v1/ngd/getAiCategories', [NgendevCategoryApiController::class, 'getCategories']);
    Route::post('/v1/ngd/getAiImageByCategoryId', [NgendevCategoryApiController::class, 'getAiImageByCategoryId']);

    Route::get('/v1/ngd/getAiVideoCategories', [NgendevVideoApiController::class, 'getAiVideoCategories']);
    Route::post('/v1/ngd/getAiVideoByCategoryId', [NgendevVideoApiController::class, 'getAiVideoByCategoryId']);

    // Filter AI Image Module API
    Route::get('/v1/filter/getFilterAiCategories', [FilterAiImageApiController::class, 'getCategories']);
    Route::post('/v1/filter/getFilterAiImageByCategoryId', [FilterAiImageApiController::class, 'getAiImageByCategoryId']);

    // Lips Sync Module API
    Route::get('/v1/getLipsSyncCategories', [LipsSyncApiController::class, 'getLipsSyncCategories']);
    Route::post('/v1/getLipsSyncByCategoryId', [LipsSyncApiController::class, 'getLipsSyncByCategoryId']);
});


Route::get('getAllCategoryNames', [NgendevCategoryApiController::class, 'getAllCategoryNames']);
Route::get('baby/getAllCategoryNames', [CategoryController::class, 'getAllCategoryNames']);
Route::get('video/getAllCategoryNames', [VideoCategoryController::class, 'getAllCategoryNames']);
Route::get('v1/ngd/video/getAllCategoryNames', [NgendevVideoApiController::class, 'getAllCategoryNames']);
Route::get('v1/top-slider/getTopSlider', [TopSliderApiController::class, 'getTopSlider']);

// Simple Test API without token
Route::get('test-api', function (Request $request) {
    try {
        // To simulate a failure, you can pass ?fail=true in the URL
        if ($request->query('fail')) {
            throw new \Exception("Simulated API failure");
        }

        return response()->json([
            'status_code' => 200,
            'message' => 'Success! The API is working correctly.'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => 'Failed! Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
});
