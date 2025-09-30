<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;

class DashboardController extends Controller
{
    public function index()
    {
        $categories = Subcategory::select('category_name')
            ->distinct()
            ->get()
            ->map(function ($cat) {
                // Get first subcategory ID for this category
                $firstSub = Subcategory::where('category_name', $cat->category_name)
                    ->orderByDesc('id') // optional: latest subcategory first
                    ->first();

                return [
                    'category_name' => $cat->category_name,
                    'subcategories_count' => Subcategory::where('category_name', $cat->category_name)->count(),
                    'redirect_url' => $firstSub ? route('subcategories.show', $firstSub->id) : '#', // fallback if no subcategories exist
                ];
            });

        return view('dashboard', compact('categories'));
    }
}
