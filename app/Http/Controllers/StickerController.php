<?php

namespace App\Http\Controllers;

use App\Models\StickerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StickerController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->input('per_page', 10);
        $search     = $request->input('search', '');
        $categoryId = $request->input('category_id', '');

        $categories = StickerCategory::orderBy('category_name')->get();

        $catQuery = StickerCategory::query()
            ->whereNotNull('stickers')
            ->whereRaw('JSON_LENGTH(stickers) > 0');

        if ($search) {
            $catQuery->where('category_name', 'like', '%' . $search . '%');
        }
        if ($categoryId) {
            $catQuery->where('id', $categoryId);
        }

        $stickerCategories = $catQuery
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('sticker.stickers.table', compact('stickerCategories'))->render(),
                'total' => $stickerCategories->total(),
            ]);
        }

        $totalStickers = (int) StickerCategory::sum(DB::raw('COALESCE(JSON_LENGTH(stickers), 0)'));

        return view('sticker.stickers.index', compact('stickerCategories', 'categories', 'perPage', 'search', 'categoryId', 'totalStickers'));
    }

    public function create()
    {
        $categories = StickerCategory::where('status', 1)->orderBy('category_name')->get();
        return view('sticker.stickers.form', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'sticker_category_id' => 'required|exists:sticker_categories,id',
                'images'              => 'required|array|min:1',
                'images.*'            => 'image|mimes:webp|max:10000',
            ], [
                'images.*.mimes' => 'Each sticker must be a .webp image.',
            ]);

            $category = StickerCategory::findOrFail($request->sticker_category_id);
            $path     = $this->ensureFolder($category->category_name);

            $newNames = [];
            foreach (array_values($request->file('images')) as $file) {
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $newNames[] = $filename;
            }

            // Newest uploads first
            $existing = is_array($category->stickers) ? $category->stickers : [];
            $category->stickers = array_values(array_merge($newNames, $existing));
            $category->save();

            return view('partials.history_redirect', ['fallback' => route('sticker.stickers.index'), 'message' => 'Stickers added successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add stickers: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $category   = StickerCategory::findOrFail($id);
        $categories = StickerCategory::where('status', 1)->orderBy('category_name')->get();
        return view('sticker.stickers.form', compact('category', 'categories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $category = StickerCategory::findOrFail($id);

            $request->validate([
                'sticker_category_id' => 'required|exists:sticker_categories,id',
                'images'              => 'nullable|array',
                'images.*'            => 'image|mimes:webp|max:10000',
            ], [
                'images.*.mimes' => 'Each sticker must be a .webp image.',
            ]);

            $targetCategory = StickerCategory::findOrFail($request->sticker_category_id);
            $path           = $this->ensureFolder($targetCategory->category_name);

            if ($request->hasFile('images')) {
                $newNames = [];
                foreach (array_values($request->file('images')) as $file) {
                    $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                    $file->move($path, $filename);
                    $newNames[] = $filename;
                }

                $existing = is_array($targetCategory->stickers) ? $targetCategory->stickers : [];
                $targetCategory->stickers = array_values(array_merge($newNames, $existing));
                $targetCategory->save();
            }

            return view('partials.history_redirect', ['fallback' => route('sticker.stickers.index'), 'message' => 'Stickers updated successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update stickers: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $category = StickerCategory::findOrFail($id);

        $existing = is_array($category->stickers) ? $category->stickers : [];
        $base     = public_path('upload/sticker/' . $category->category_name . '/stickers');

        foreach ($existing as $filename) {
            $filePath = $base . DIRECTORY_SEPARATOR . $filename;
            if ($filename && file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $category->stickers = [];
        $category->save();

        $this->rmIfEmpty($base);

        return redirect()->route('sticker.stickers.index')->with('success', 'All stickers for this category deleted successfully!');
    }

    public function destroyOne(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:sticker_categories,id',
            'image'       => 'required|string',
        ]);

        $category = StickerCategory::findOrFail($request->category_id);
        $existing = is_array($category->stickers) ? $category->stickers : [];

        if (!in_array($request->image, $existing, true)) {
            return response()->json(['success' => false, 'message' => 'Sticker not found in this category.'], 404);
        }

        $filePath = public_path('upload/sticker/' . $category->category_name . '/stickers/' . $request->image);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $category->stickers = array_values(array_filter($existing, fn($f) => $f !== $request->image));
        $category->save();

        return response()->json(['success' => true, 'message' => 'Sticker deleted successfully!']);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:sticker_categories,id',
            'order'       => 'required|array',
            'order.*'     => 'required|string',
        ]);

        $category = StickerCategory::findOrFail($request->category_id);
        $existing = is_array($category->stickers) ? $category->stickers : [];

        // Keep only filenames that actually exist in the current category, in submitted order
        $reordered = array_values(array_filter($request->order, fn($f) => in_array($f, $existing, true)));

        // Append any existing stickers that were not in the submitted order (safety net)
        foreach ($existing as $f) {
            if (!in_array($f, $reordered, true)) {
                $reordered[] = $f;
            }
        }

        $category->stickers = $reordered;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Order updated successfully!']);
    }

    private function ensureFolder(string $categoryName): string
    {
        $path = public_path('upload/sticker/' . $categoryName . '/stickers');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }
        return $path;
    }

    private function rmIfEmpty(string $folder): void
    {
        if (!is_dir($folder)) return;
        $items = @scandir($folder);
        $items = array_diff($items ?: [], ['.', '..']);
        if (empty($items)) {
            @rmdir($folder);
        }
    }

    private function uniqueFilename(string $folder, string $originalName): string
    {
        if (!file_exists($folder . DIRECTORY_SEPARATOR . $originalName)) {
            return $originalName;
        }
        $info = pathinfo($originalName);
        $base = $info['filename'] ?? $originalName;
        $ext  = isset($info['extension']) ? '.' . $info['extension'] : '';
        return $base . '_' . time() . $ext;
    }
}
