<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\NgendevImageController;
use App\Http\Controllers\NgendevCategoryController;
use App\Http\Controllers\AiImageBabyPhotoSettingController;
use App\Http\Controllers\AiImageNgdSettingController;
use App\Http\Controllers\AiImageCategoryController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\FilterAiImageController;
use App\Http\Controllers\FilterAiImageCategoryController;
use App\Http\Controllers\FilterAiImageSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Cache cleared successfully!');
})->name('clear.cache');

Route::get('/', [AdminController::class, 'index'])->name('login');
Route::post('/login', [AdminController::class, 'login'])->name('admin-auth');

Route::middleware(['admin_auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/apiList', function () {
        return view('apiList');
    });

    Route::post('/logout', [AdminController::class, 'logout'])->name('auth.logout');

    // Maintenance Routes
    Route::post('/clear-cache', [SystemController::class, 'clearCache'])->name('system.clearCache');

    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubcategoryController::class, 'index'])->name('subcategories.index');
        Route::get('/form/{id?}', [SubcategoryController::class, 'form'])->name('subcategories.form');
        Route::post('/save/{id?}', [SubcategoryController::class, 'save'])->name('subcategories.save');
        Route::get('/{id}', [SubcategoryController::class, 'show'])->name('subcategories.show');
        Route::delete('/{id}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy');

        Route::get('/subcategory/{id}', [SubcategoryController::class, 'show'])->name('subcategories.show');

        Route::get('subcategories/{id}/add-details', [SubcategoryController::class, 'addDetailsForm'])->name('subcategories.addDetailsForm');
        Route::post('subcategories/{id}/save-details', [SubcategoryController::class, 'saveDetails'])->name('subcategories.saveDetails');
    });

    Route::prefix('subcategories')
        ->name('subcategories.')
        ->group(function () {
            Route::get('/', [SubcategoryController::class, 'index'])->name('index');
            Route::get('form/{id?}', [SubcategoryController::class, 'form'])->name('form');
            Route::post('save/{id?}', [SubcategoryController::class, 'save'])->name('save');
            Route::get('{id}', [SubcategoryController::class, 'show'])->name('show');
            Route::delete('{id}', [SubcategoryController::class, 'destroy'])->name('destroy');
            Route::get('image/delete/{subcategoryId}/{file}', [SubcategoryController::class, 'deleteImage'])->name('deleteImage');
            Route::post('update-status', [SubcategoryController::class, 'updateStatus'])->name('updateStatus');
        });

    // Ngendev Images Routes
    Route::prefix('ngendev')
        ->name('ngendev.')
        ->group(function () {
            Route::get('/images', [NgendevImageController::class, 'index'])->name('images.index');
            Route::post('/images', [NgendevImageController::class, 'store'])->name('images.store');
            Route::get('/images/create', [NgendevImageController::class, 'create'])->name('images.create');
            Route::get('/images/{id}/edit', [NgendevImageController::class, 'edit'])->name('images.edit');
            Route::put('/images/{id}', [NgendevImageController::class, 'update'])->name('images.update');
            Route::delete('/images/{id}', [NgendevImageController::class, 'destroy'])->name('images.destroy');
            Route::get('/images-indexing', [NgendevImageController::class, 'indexing'])->name('images.indexing');
            Route::post('/images-update-order', [NgendevImageController::class, 'updateOrder'])->name('images.updateOrder');
            Route::get('/images-name-change-stats', [NgendevImageController::class, 'categoryNameChangeStats'])->name('images.nameChangeStats');
            Route::post('/images-bulk-name-change', [NgendevImageController::class, 'bulkToggleNameChange'])->name('images.bulkNameChange');
        });

    // Ngendev Categories Routes
    Route::prefix('ngendev')
        ->name('ngendev.')
        ->group(function () {
            Route::get('/categories', [NgendevCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories-indexing', [NgendevCategoryController::class, 'indexing'])->name('categories.indexing');
            Route::post('/categories-update-order', [NgendevCategoryController::class, 'updateOrder'])->name('categories.updateOrder');
            Route::get('/categories/create', [NgendevCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [NgendevCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{id}/edit', [NgendevCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{id}', [NgendevCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{id}', [NgendevCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('/categories/update-status', [NgendevCategoryController::class, 'updateStatus'])->name('categories.updateStatus');
            Route::post('/categories/update-type', [NgendevCategoryController::class, 'updateType'])->name('categories.updateType');
            Route::post('/categories/update-couple-status', [NgendevCategoryController::class, 'updateCoupleStatus'])->name('categories.updateCoupleStatus');
        });

    // Filter AI Image Images Routes
    Route::prefix('filter-ai-image')
        ->name('filter-ai-image.')
        ->group(function () {
            Route::get('/images', [FilterAiImageController::class, 'index'])->name('images.index');
            Route::post('/images', [FilterAiImageController::class, 'store'])->name('images.store');
            Route::get('/images-indexing', [FilterAiImageController::class, 'indexing'])->name('images.indexing');
            Route::post('/images-update-order', [FilterAiImageController::class, 'updateOrder'])->name('images.updateOrder');
            Route::put('/images/{id}', [FilterAiImageController::class, 'update'])->name('images.update');
            Route::delete('/images/{id}', [FilterAiImageController::class, 'destroy'])->name('images.destroy');
        });

    // Filter AI Image Categories Routes
    Route::prefix('filter-ai-image')
        ->name('filter-ai-image.')
        ->group(function () {
            Route::get('/categories', [FilterAiImageCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories-indexing', [FilterAiImageCategoryController::class, 'indexing'])->name('categories.indexing');
            Route::post('/categories-update-order', [FilterAiImageCategoryController::class, 'updateOrder'])->name('categories.updateOrder');
            Route::get('/categories/create', [FilterAiImageCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [FilterAiImageCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{id}/edit', [FilterAiImageCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{id}', [FilterAiImageCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{id}', [FilterAiImageCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('/categories/update-status', [FilterAiImageCategoryController::class, 'updateStatus'])->name('categories.updateStatus');
        });


    /*
|--------------------------------------------------------------------------
| AI Image Baby Photo Setting Routes
|--------------------------------------------------------------------------
*/
    Route::prefix('ai-image-baby-photo-setting')
        ->name('ai-image-baby-photo-setting.')
        ->group(function () {
            Route::get('/', [AiImageBabyPhotoSettingController::class, 'index'])->name('index');
            Route::get('/create', [AiImageBabyPhotoSettingController::class, 'create'])->name('create');
            Route::post('/', [AiImageBabyPhotoSettingController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AiImageBabyPhotoSettingController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AiImageBabyPhotoSettingController::class, 'update'])->name('update');
            Route::delete('/{id}', [AiImageBabyPhotoSettingController::class, 'destroy'])->name('destroy');
        });

    /*
|--------------------------------------------------------------------------
| AI Image NGD Setting Routes
|--------------------------------------------------------------------------
*/
    Route::prefix('ai-image-ngd-setting')
        ->name('ai-image-ngd-setting.')
        ->group(function () {
            Route::get('/', [AiImageNgdSettingController::class, 'index'])->name('index');
            Route::post('/', [AiImageNgdSettingController::class, 'store'])->name('store');
            Route::put('/{id}', [AiImageNgdSettingController::class, 'update'])->name('update');
            Route::delete('/{id}', [AiImageNgdSettingController::class, 'destroy'])->name('destroy');
        });
    Route::post('/ai-image-categories/toggle-status', [AiImageCategoryController::class, 'toggleStatus'])->name('ai-image-categories.toggle-status');

    /*
|--------------------------------------------------------------------------
| AI Video NGD Setting Routes
|--------------------------------------------------------------------------
*/
    Route::prefix('ai-video-ngd-setting')
        ->name('ai-video-ngd-setting.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\AiVideoNgdSettingController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\AiVideoNgdSettingController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\AiVideoNgdSettingController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\AiVideoNgdSettingController::class, 'destroy'])->name('destroy');
        });

    // Ngendev Video Categories Routes
    Route::prefix('ngendev')
        ->name('ngendev-video-categories.')
        ->group(function () {
            Route::get('/video-categories', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'index'])->name('index');
            Route::get('/video-categories-indexing', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'indexing'])->name('indexing');
            Route::post('/video-categories-update-order', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'updateOrder'])->name('updateOrder');
            Route::get('/video-categories/create', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'create'])->name('create');
            Route::post('/video-categories', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'store'])->name('store');
            Route::get('/video-categories/{id}/edit', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'edit'])->name('edit');
            Route::put('/video-categories/{id}', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'update'])->name('update');
            Route::delete('/video-categories/{id}', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'destroy'])->name('destroy');
            Route::post('/video-categories/update-status', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/video-categories/update-type', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'updateType'])->name('updateType');
            Route::post('/video-categories/update-couple-status', [\App\Http\Controllers\NgendevVideoCategoryController::class, 'updateCoupleStatus'])->name('updateCoupleStatus');
        });

    // Ngendev Videos Routes
    Route::prefix('ngendev')
        ->name('ngendev-videos.')
        ->group(function () {
            Route::get('/videos', [\App\Http\Controllers\NgendevVideoController::class, 'index'])->name('index');
            Route::post('/videos', [\App\Http\Controllers\NgendevVideoController::class, 'store'])->name('store');
            Route::get('/videos/create', [\App\Http\Controllers\NgendevVideoController::class, 'create'])->name('create');
            Route::get('/videos/{id}/edit', [\App\Http\Controllers\NgendevVideoController::class, 'edit'])->name('edit');
            Route::put('/videos/{id}', [\App\Http\Controllers\NgendevVideoController::class, 'update'])->name('update');
            Route::delete('/videos/{id}', [\App\Http\Controllers\NgendevVideoController::class, 'destroy'])->name('destroy');
            Route::get('/videos-indexing', [\App\Http\Controllers\NgendevVideoController::class, 'indexing'])->name('indexing');
            Route::post('/videos-update-order', [\App\Http\Controllers\NgendevVideoController::class, 'updateOrder'])->name('updateOrder');
            Route::get('/videos-name-change-stats', [\App\Http\Controllers\NgendevVideoController::class, 'categoryNameChangeStats'])->name('nameChangeStats');
            Route::post('/videos-bulk-name-change', [\App\Http\Controllers\NgendevVideoController::class, 'bulkToggleNameChange'])->name('bulkNameChange');
        });

    /*
    |--------------------------------------------------------------------------
    | AI Baby Video Module Routes
    |--------------------------------------------------------------------------
    */
    // Category Management
    Route::prefix('ai-baby-video')
        ->name('ai-baby-video.')
        ->group(function () {
            // Categories
            Route::get('/categories', [App\Http\Controllers\AiVideoCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/indexing', [App\Http\Controllers\AiVideoCategoryController::class, 'indexing'])->name('categories.indexing');
            Route::post('/categories/update-order', [App\Http\Controllers\AiVideoCategoryController::class, 'updateOrder'])->name('categories.updateOrder');
            Route::get('/categories/create', [App\Http\Controllers\AiVideoCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [App\Http\Controllers\AiVideoCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{id}/edit', [App\Http\Controllers\AiVideoCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{id}', [App\Http\Controllers\AiVideoCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{id}', [App\Http\Controllers\AiVideoCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('/categories/update-status', [App\Http\Controllers\AiVideoCategoryController::class, 'toggleStatus'])->name('categories.updateStatus');

            // Videos (Subcategories used as Video Items)
            Route::get('/videos', [App\Http\Controllers\AiBabyVideoController::class, 'index'])->name('videos.index');
            Route::get('/videos/create', [App\Http\Controllers\AiBabyVideoController::class, 'create'])->name('videos.create');
            Route::post('/videos', [App\Http\Controllers\AiBabyVideoController::class, 'store'])->name('videos.store');
            Route::get('/videos/{id}/edit', [App\Http\Controllers\AiBabyVideoController::class, 'edit'])->name('videos.edit');
            Route::put('/videos/{id}', [App\Http\Controllers\AiBabyVideoController::class, 'update'])->name('videos.update');
            Route::delete('/videos/{id}', [App\Http\Controllers\AiBabyVideoController::class, 'destroy'])->name('videos.destroy');
        });

    Route::prefix('ai-baby-video-module-setting')
        ->name('ai-baby-video-module-setting.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\AiBabyVideoModuleSettingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\AiBabyVideoModuleSettingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\AiBabyVideoModuleSettingController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\AiBabyVideoModuleSettingController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\AiBabyVideoModuleSettingController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\AiBabyVideoModuleSettingController::class, 'destroy'])->name('destroy');
        });
    Route::post('/ai-video-categories/toggle-status', [\App\Http\Controllers\AiVideoCategoryController::class, 'toggleStatus'])->name('ai-video-categories.toggle-status');
    /*
    |--------------------------------------------------------------------------
    | Lips Sync Module Routes
    |--------------------------------------------------------------------------
    */
    // Lips Sync Categories — URL: /ai/lips-sync-categories
    Route::prefix('ai/lips-sync-categories')->name('lips-sync.categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LipsSyncCategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\LipsSyncCategoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\LipsSyncCategoryController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\LipsSyncCategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\LipsSyncCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\LipsSyncCategoryController::class, 'destroy'])->name('destroy');
    });

    // Lips Sync (Items) — URL: /ai/lips-sync
    Route::prefix('ai/lips-sync')->name('lips-sync.items.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LipsSyncController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\LipsSyncController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\LipsSyncController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\LipsSyncController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\LipsSyncController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\LipsSyncController::class, 'destroy'])->name('destroy');
    });

    // Top Slider Module
    Route::prefix('top-slider')->name('top-slider.')->group(function () {
        // Categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\TopSliderCategoryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\TopSliderCategoryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\TopSliderCategoryController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\TopSliderCategoryController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\TopSliderCategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\TopSliderCategoryController::class, 'destroy'])->name('destroy');
            Route::post('/update-topslider-status', [\App\Http\Controllers\TopSliderCategoryController::class, 'toggleTopSliderStatus'])->name('updateTopSliderStatus');
            Route::get('/indexing', [\App\Http\Controllers\TopSliderCategoryController::class, 'indexing'])->name('indexing');
            Route::post('/update-order', [\App\Http\Controllers\TopSliderCategoryController::class, 'updateOrder'])->name('updateOrder');
        });
    });
});
