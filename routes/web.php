<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubcategoryController;

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

    Route::post('/logout', [AdminController::class, 'logout'])->name('auth.logout');

    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubcategoryController::class, 'index'])->name('subcategories.index'); // list
        Route::get('/form/{id?}', [SubcategoryController::class, 'form'])->name('subcategories.form'); // add/edit form
        Route::post('/save/{id?}', [SubcategoryController::class, 'save'])->name('subcategories.save'); // save (create/update)
        Route::get('/{id}', [SubcategoryController::class, 'show'])->name('subcategories.show'); // show single
        Route::delete('/{id}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy'); // delete

        Route::get('/subcategory/{id}', [SubcategoryController::class, 'show'])->name('subcategories.show');

        // Step 3: Add Images & Description (from show page)
Route::get('subcategories/{id}/add-details', [SubcategoryController::class, 'addDetailsForm'])->name('subcategories.addDetailsForm');
Route::post('subcategories/{id}/save-details', [SubcategoryController::class, 'saveDetails'])->name('subcategories.saveDetails');
    });

    Route::prefix('subcategories')->name('subcategories.')->group(function() {
    Route::get('/', [SubcategoryController::class, 'index'])->name('index');
    Route::get('form/{id?}', [SubcategoryController::class, 'form'])->name('form');
    Route::post('save/{id?}', [SubcategoryController::class, 'save'])->name('save');
    Route::get('{id}', [SubcategoryController::class, 'show'])->name('show');
    Route::delete('{id}', [SubcategoryController::class, 'destroy'])->name('destroy');
});
});
