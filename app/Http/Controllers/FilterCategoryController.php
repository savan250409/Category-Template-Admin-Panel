<?php

namespace App\Http\Controllers;

use App\Models\FilterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FilterCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = FilterCategory::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('filter.categories.table', compact('categories'))->render(),
                'total' => $categories->total(),
            ]);
        }

        return view('filter.categories.index', compact('categories', 'perPage', 'search'));
    }

    public function create()
    {
        return view('filter.categories.form');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'  => 'required|string|max:255|unique:filter_categories,name',
                'image' => 'nullable|file|mimes:webp|mimetypes:image/webp|max:10000',
            ], [
                'image.mimes'     => 'Thumbnail must be a .webp image.',
                'image.mimetypes' => 'Thumbnail must be a .webp image.',
            ]);

            $minOrder = FilterCategory::min('sort_order');
            $minOrder = is_null($minOrder) ? 0 : (int) $minOrder;

            $category = new FilterCategory();
            $category->name       = $request->name;
            $category->status     = $request->has('status') ? 1 : 0;
            $category->sort_order = $minOrder - 1;
            $category->save();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = public_path('upload/filter/' . $category->name . '/category image');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $category->image = $filename;
                $category->save();
            }

            return redirect()->route('filter.categories.index')->with('success', 'Filter Category created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save category: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $category = FilterCategory::findOrFail($id);
        return view('filter.categories.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        try {
            $category = FilterCategory::findOrFail($id);

            $request->validate([
                'name'  => 'required|string|max:255|unique:filter_categories,name,' . $category->id,
                'image' => 'nullable|file|mimes:webp|mimetypes:image/webp|max:10000',
            ], [
                'image.mimes'     => 'Thumbnail must be a .webp image.',
                'image.mimetypes' => 'Thumbnail must be a .webp image.',
            ]);

            $oldName = $category->name;
            $category->name   = $request->name;
            $category->status = $request->has('status') ? 1 : 0;

            if ($oldName !== $category->name) {
                $oldFolder = public_path('upload/filter/' . $oldName);
                $newFolder = public_path('upload/filter/' . $category->name);
                if (File::exists($oldFolder) && !File::exists($newFolder)) {
                    @rename($oldFolder, $newFolder);
                }
            }

            if ($request->hasFile('image')) {
                $oldImagePath = public_path('upload/filter/' . $category->name . '/category image/' . $category->image);
                if ($category->image && file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }

                $file = $request->file('image');
                $path = public_path('upload/filter/' . $category->name . '/category image');
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $category->image = $filename;
            }

            $category->save();

            return redirect()->route('filter.categories.index')->with('success', 'Filter Category updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $category = FilterCategory::findOrFail($id);

        $folder = public_path('upload/filter/' . $category->name);
        if (File::exists($folder)) {
            File::deleteDirectory($folder);
        }

        $category->delete();

        return redirect()->route('filter.categories.index')->with('success', 'Filter Category and all its filters deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $category = FilterCategory::findOrFail($request->id);
        $category->status = $request->status ? 1 : 0;
        $category->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully!', 'status' => $category->status]);
    }

    public function indexing(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('filter.categories.index');
        }

        $categories = FilterCategory::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        $data = $categories->map(function ($category) {
            return [
                'id'        => $category->id,
                'name'      => $category->name,
                'image_url' => $category->image
                    ? asset('upload/filter/' . rawurlencode($category->name) . '/category image/' . rawurlencode($category->image))
                    : null,
            ];
        });

        return response()->json(['categories' => $data]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order'              => 'required|array',
            'order.*.id'         => 'required|exists:filter_categories,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            FilterCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully!']);
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
