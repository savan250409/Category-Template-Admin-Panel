<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\AiImageCategory;

class AiImageCategoryController extends Controller
{
    public function toggleStatus(Request $request)
    {
        $category = AiImageCategory::where('name', $request->name)->firstOrFail();
        $category->status = $request->status ? 1 : 0;
        $category->save();

        Cache::forget('sidebar.ai_image_categories');
        Cache::forget('sidebar.subcategories_grouped');

        return response()->json([
            'success' => true,
            'status' => $category->status
        ]);
    }
}

