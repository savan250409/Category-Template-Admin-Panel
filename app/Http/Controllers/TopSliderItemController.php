<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TopSliderItem;
use App\Models\TopSliderCategory;
use Illuminate\Support\Facades\File;

class TopSliderItemController extends Controller
{
    public function index(Request $request)
    {
        $perPage = intval($request->input('per_page', 10));
        $search = $request->input('search', '');
        $categoryId = $request->input('category_id', '');

        $query = TopSliderItem::with('category');

        if ($search) {
            $query->where('prompt', 'like', '%' . $search . '%');
        }
        
        if ($categoryId) {
            $query->where('top_slider_category_id', $categoryId);
        }

        $items = $query->orderBy('sort_order', 'asc')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
            
        $categories = TopSliderCategory::all();

        return view('top_slider.items.index', compact('items', 'categories', 'perPage', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = TopSliderCategory::all();
        return view('top_slider.items.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'top_slider_category_id' => 'required|exists:top_slider_categories,id',
            'prompt' => 'nullable|string',
            'file' => 'nullable|file', // Can be image or video based on category
            'video_thumbnail' => 'nullable|image|mimes:webp|max:10000',
        ]);

        $category = TopSliderCategory::findOrFail($request->top_slider_category_id);

        $item = new TopSliderItem();
        $item->top_slider_category_id = $request->top_slider_category_id;
        $item->prompt = $request->prompt;
        $item->file_type = $category->file_type;
        $item->status = $request->has('status') ? 1 : 0;
        $basePath = 'upload/top_slider/items/' . $category->category_name;

        // Validation for file based on category type
        if ($category->file_type == 'image') {
            $request->validate(['file' => 'nullable|image|mimes:webp|max:10000']);
        } else {
            $request->validate(['file' => 'nullable|mimes:mp4,mov,ogg,qt|max:50000']);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_item_' . $file->getClientOriginalName();
            $path = public_path($basePath);
            if (!File::exists($path)) File::makeDirectory($path, 0777, true);
            $file->move($path, $filename);
            $item->file = $filename;
        }

        if ($category->file_type == 'video' && $request->hasFile('video_thumbnail')) {
            $file = $request->file('video_thumbnail');
            $filename = time() . '_itemthumb_' . $file->getClientOriginalName();
            $path = public_path($basePath);
            if (!File::exists($path)) File::makeDirectory($path, 0777, true);
            $file->move($path, $filename);
            $item->video_thumbnail = $filename;
        }

        $item->save();

        return redirect()->route('top-slider.items.index')->with('success', 'Item created successfully!');
    }

    public function edit($id)
    {
        $item = TopSliderItem::findOrFail($id);
        $categories = TopSliderCategory::all();
        return view('top_slider.items.form', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = TopSliderItem::findOrFail($id);
        
        $request->validate([
            'top_slider_category_id' => 'required|exists:top_slider_categories,id',
            'prompt' => 'nullable|string',
            'file' => 'nullable|file', // Can be image or video based on category
            'video_thumbnail' => 'nullable|image|mimes:webp|max:10000',
        ]);

        $category = TopSliderCategory::findOrFail($request->top_slider_category_id);

        $item->top_slider_category_id = $request->top_slider_category_id;
        $item->prompt = $request->prompt;
        // Also update file_type so it matches the current category if it was changed
        $item->file_type = $category->file_type;
        $item->status = $request->has('status') ? 1 : 0;
        $basePath = 'upload/top_slider/items/' . $category->category_name;

        // Validation for file based on category type
        if ($category->file_type == 'image') {
            $request->validate(['file' => 'nullable|image|mimes:webp|max:10000']);
        } else {
            $request->validate(['file' => 'nullable|mimes:mp4,mov,ogg,qt|max:50000']);
        }

        if ($request->hasFile('file')) {
            if ($item->file && file_exists(public_path('upload/top_slider/items/' . $category->category_name . '/' . $item->file))) {
                unlink(public_path('upload/top_slider/items/' . $category->category_name . '/' . $item->file));
            }
            $file = $request->file('file');
            $filename = time() . '_item_' . $file->getClientOriginalName();
            $path = public_path($basePath);
            if (!File::exists($path)) File::makeDirectory($path, 0777, true);
            $file->move($path, $filename);
            $item->file = $filename;
        }

        if ($category->file_type == 'video' && $request->hasFile('video_thumbnail')) {
            if ($item->video_thumbnail && file_exists(public_path('upload/top_slider/items/' . $category->id . '/' . $item->video_thumbnail))) {
                unlink(public_path('upload/top_slider/items/' . $category->id . '/' . $item->video_thumbnail));
            }
            $file = $request->file('video_thumbnail');
            $filename = time() . '_itemthumb_' . $file->getClientOriginalName();
            $path = public_path($basePath);
            if (!File::exists($path)) File::makeDirectory($path, 0777, true);
            $file->move($path, $filename);
            $item->video_thumbnail = $filename;
        } elseif ($category->file_type == 'image') {
            if ($item->video_thumbnail && file_exists(public_path('upload/top_slider/items/' . $category->id . '/' . $item->video_thumbnail))) {
                unlink(public_path('upload/top_slider/items/' . $category->id . '/' . $item->video_thumbnail));
            }
            $item->video_thumbnail = null;
        }

        $item->save();

        return redirect()->route('top-slider.items.index')->with('success', 'Item updated successfully!');
    }

    public function destroy($id)
    {
        $item = TopSliderItem::findOrFail($id);

        $itemBasePath = 'upload/top_slider/items/' . $item->category->category_name;
        if ($item->file && file_exists(public_path($itemBasePath . '/' . $item->file))) {
            unlink(public_path($itemBasePath . '/' . $item->file));
        }
        if ($item->video_thumbnail && file_exists(public_path($itemBasePath . '/' . $item->video_thumbnail))) {
            unlink(public_path($itemBasePath . '/' . $item->video_thumbnail));
        }

        $item->delete();

        return redirect()->route('top-slider.items.index')->with('success', 'Item deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $item = TopSliderItem::findOrFail($request->id);
        $item->status = $request->status ? 1 : 0;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }

    public function indexing(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('top-slider.items.index');
        }
        
        $categoryId = $request->input('category_id');
        $query = TopSliderItem::orderBy('sort_order', 'asc')->orderBy('updated_at', 'desc');
        
        if ($categoryId) {
            $query->where('top_slider_category_id', $categoryId);
        }
        
        $items = $query->with('category')->get();
        return response()->json(['items' => $items]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:top_slider_items,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);
        foreach ($request->order as $item) {
            TopSliderItem::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['success' => true, 'message' => 'Order updated successfully!']);
    }
    
    public function getCategoryFileType(Request $request)
    {
        $category = TopSliderCategory::find($request->category_id);
        if ($category) {
            return response()->json(['success' => true, 'file_type' => $category->file_type]);
        }
        return response()->json(['success' => false], 404);
    }
}
