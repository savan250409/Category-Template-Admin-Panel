<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Subcategory;
use App\Models\SubSubcategory;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        putenv("FFMPEG_BINARY=C:\\ffmpeg\\bin\\ffmpeg.exe");
        putenv("FFPROBE_BINARY=C:\\ffmpeg\\bin\\ffprobe.exe");

        Paginator::useBootstrap();

        View::composer('partials.layout', function ($view) {
            $categories = ['Newborn Baby', 'Baby Bumps', 'Toddler (1–3 Years Old)', 'Festival Frames', 'Birthday Photo', 'Unique Style', 'Invitation card'];

            $allSubs = Subcategory::all()->groupBy('category_name');

            $view->with(compact('categories', 'allSubs'));
        });
    }
}
