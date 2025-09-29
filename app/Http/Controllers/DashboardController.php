<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all main categories
        $categories = Subcategory::select('category_name')
            ->distinct()
            ->get()
            ->map(function ($cat) {
                // Count subcategories for this main category
                $subcategoriesCount = Subcategory::where('category_name', $cat->category_name)->count();

                return [
                    'category_name' => $cat->category_name,
                    'subcategories_count' => $subcategoriesCount,
                    // You can pass main_category id/slug here if you need a redirect link
                    'redirect_url' => url('/categories/' . $cat->category_name),
                ];
            });

        return view('dashboard', compact('categories'));
    }
}
