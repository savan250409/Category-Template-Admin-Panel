<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = \App\Models\NgendevVideoCategory::where('status',1)->get(['id', 'category_name'])->toArray();
$videos = \App\Models\NgendevVideo::orderBy('category_id')->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get(['id', 'category_id', 'sort_order', 'video_path'])->toArray();

file_put_contents('db_dump.json', json_encode(['categories' => $cats, 'videos' => $videos], JSON_PRETTY_PRINT));
echo "Dumped to db_dump.json\n";
