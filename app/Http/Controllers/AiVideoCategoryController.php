<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AiVideoCategory;

class AiVideoCategoryController extends Controller
{
    public function toggleStatus(Request $request)
    {
        $category = AiVideoCategory::where('name', $request->name)->firstOrFail();
        $category->status = $request->status ? 1 : 0;
        $category->save();

        return response()->json([
            'success' => true,
            'status' => $category->status
        ]);
    }
}
