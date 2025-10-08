<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\NgendevImageController;
use App\Http\Controllers\NgendevCategoryController;

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
        });

    // Ngendev Categories Routes
    Route::prefix('ngendev')
        ->name('ngendev.')
        ->group(function () {
            Route::get('/categories', [NgendevCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [NgendevCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [NgendevCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{id}/edit', [NgendevCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{id}', [NgendevCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{id}', [NgendevCategoryController::class, 'destroy'])->name('categories.destroy');
        });
});

