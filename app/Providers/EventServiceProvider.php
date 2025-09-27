<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\GenericObserver;

// Import models
use App\Models\Admin;
use App\Models\AiCategory;
use App\Models\AiImage;
use App\Models\AiVideo;
use App\Models\AiVideoCategory;
use App\Models\Appadd;
use App\Models\Asm013Category;
use App\Models\Asm013Image;
use App\Models\Asm013Video;
use App\Models\Asm013VideoCategory;
use App\Models\Category;
use App\Models\Frame;
use App\Models\Language;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $models = [
            Admin::class,
            AiCategory::class,
            AiImage::class,
            AiVideo::class,
            AiVideoCategory::class,
            Appadd::class,
            Asm013Category::class,
            Asm013Image::class,
            Asm013Video::class,
            Asm013VideoCategory::class,
            Category::class,
            Frame::class,
            Language::class,
        ];

        // foreach ($models as $model) {
        //     $model::observe(GenericObserver::class);
        // }
    }
}
