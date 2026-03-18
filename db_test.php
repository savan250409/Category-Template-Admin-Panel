<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = \App\Models\NgendevVideoCategory::where('status',1)->pluck('id', 'category_name')->toArray();
echo "Categories:\n";
print_r($cats);

$videos = \App\Models\NgendevVideo::orderBy('category_id')->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get(['id', 'category_id', 'sort_order'])->toArray();
echo "\nVideos sorted by sort_order ASC, id ASC:\n";
print_r($videos);
