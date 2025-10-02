<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use App\Models\NgendevImage;
use App\Models\NgendevCategory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class NgendevImageController extends Controller
{
    public function index(Request $request)
    {
        $categories = NgendevCategory::all();
        $query = NgendevImage::with('category')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ai_prompt', 'like', "%{$search}%")
                    ->orWhere('ai_model', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        $images = $query->paginate(10)->appends(['search' => $request->get('search')]);

        if ($request->ajax()) {
            $table = view('ngendev.images.index', compact('images', 'categories'))->renderSections()['table'];
            $pagination = view('ngendev.images.index', compact('images', 'categories'))->renderSections()['pagination'];

            return response()->json([
                'table' => $table,
                'pagination' => $pagination,
                'total' => $images->total(),
            ]);
        }

        return view('ngendev.images.index', compact('categories', 'images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:ngendev_categories,id',
            'ai_prompt' => 'required|string|max:1000',
            'ai_model' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $category = NgendevCategory::findOrFail($request->category_id);
            $categoryName = $category->category_name;

            $originalName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('upload/ngendev/images/' . $categoryName . '/category_image');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $request->file('image')->move($destinationPath, $originalName);
            $imageName = $originalName;
        }

        NgendevImage::create([
            'category_id' => $request->category_id,
            'ai_prompt' => $request->ai_prompt,
            'ai_model' => $request->ai_model ?? 'AI Image',
            'image_path' => $imageName,
        ]);

        return $request->ajax() ? response()->json(['success' => true, 'message' => 'Ngendev image added successfully!']) : redirect()->route('ngendev.images.index')->with('success', 'Ngendev image added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:ngendev_categories,id',
            'ai_prompt' => 'required|string|max:1000',
            'ai_model' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $image = NgendevImage::findOrFail($id);
        $category = NgendevCategory::findOrFail($request->category_id);
        $categoryName = $category->category_name;

        $imagePath = $image->image_path;

        if ($request->hasFile('image')) {
            $oldPath = public_path('upload/ngendev/images/' . $categoryName . '/category_image/' . $imagePath);
            if ($imagePath && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $originalName = $request->file('image')->getClientOriginalName();
            $destinationPath = public_path('upload/ngendev/images/' . $categoryName . '/category_image');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $request->file('image')->move($destinationPath, $originalName);
            $imagePath = $originalName;
        }

        $image->update([
            'category_id' => $request->category_id,
            'ai_prompt' => $request->ai_prompt,
            'ai_model' => $request->ai_model ?? $image->ai_model,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('ngendev.images.index')->with('success', 'Ngendev image updated successfully!');
    }

    public function destroy($id)
    {
        $image = NgendevImage::findOrFail($id);
        $category = $image->category;

        $filePath = public_path('upload/ngendev/images/' . $category->category_name . '/category_image/' . $image->image_path);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $image->delete();

        return redirect()->route('ngendev.images.index')->with('success', 'Selected image deleted successfully!');
    }
}
