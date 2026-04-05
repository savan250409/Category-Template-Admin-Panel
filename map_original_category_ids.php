<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\TopSliderCategory;
use App\Models\NgendevCategory;
use App\Models\NgendevVideoCategory;

echo "Starting original_category_id mapping...\n";

$categories = TopSliderCategory::all();

foreach ($categories as $category) {
    if ($category->file_type === 'image') {
        $ngdCat = NgendevCategory::where('category_name', $category->category_name)->first();
        if ($ngdCat) {
            $category->original_category_id = $ngdCat->id;
            $category->save();
            echo "Mapped ID {$ngdCat->id} to Image Category '{$category->category_name}'.\n";
        } else {
            echo "Failed to map Image Category '{$category->category_name}'.\n";
        }
    } else {
        $ngdVidCat = NgendevVideoCategory::where('category_name', $category->category_name)->first();
        if ($ngdVidCat) {
            $category->original_category_id = $ngdVidCat->id;
            $category->save();
            echo "Mapped ID {$ngdVidCat->id} to Video Category '{$category->category_name}'.\n";
        } else {
            echo "Failed to map Video Category '{$category->category_name}'.\n";
        }
    }
}

echo "Mapping finished.\n";
