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
    Route::post('/getSubcategoriesByCategory', [CategoryController::class, 'getSubcategoriesByCategory']);
    Route::get('/trending', [CategoryController::class, 'trending']);

    Route::get('/v1/ngd/getAiCategories', [NgendevCategoryApiController::class, 'getCategories']);
    Route::post('/v1/ngd/getAiImageByCategoryId', [NgendevCategoryApiController::class, 'getAiImageByCategoryId']);
    Route::get('/v1/ngd/getAiCategories', [NgendevCategoryApiController::class, 'getCategories']);
    Route::post('/v1/ngd/getAiImageByCategoryId', [NgendevCategoryApiController::class, 'getAiImageByCategoryId']);
});
