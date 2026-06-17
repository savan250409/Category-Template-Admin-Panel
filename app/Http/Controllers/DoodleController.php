<?php

namespace App\Http\Controllers;

use App\Models\Doodle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DoodleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = Doodle::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $doodles = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('doodle.table', compact('doodles'))->render(),
                'total' => $doodles->total(),
            ]);
        }

        return view('doodle.index', compact('doodles', 'perPage', 'search'));
    }

    public function create()
    {
        return view('doodle.form');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'        => 'required|string|max:255|unique:doodles,name',
                'doodle_type' => 'required|in:image,line',
                'image'       => 'required|file|mimes:webp|mimetypes:image/webp|max:10000',
                'is_premium'  => 'nullable|boolean',
            ], [
                'image.mimes'     => 'Doodle image must be a .webp file.',
                'image.mimetypes' => 'Doodle image must be a .webp file.',
            ]);

            $minOrder = Doodle::min('sort_order');
            $minOrder = is_null($minOrder) ? 0 : (int) $minOrder;

            $doodle = new Doodle();
            $doodle->name        = $request->name;
            $doodle->doodle_type = $request->doodle_type;
            $doodle->is_premium  = $request->has('is_premium') ? 1 : 0;
            $doodle->status      = 1;
            $doodle->sort_order  = $minOrder - 1;
            $doodle->save();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = public_path('upload/doodle/' . $doodle->name);
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $doodle->image = $filename;
                $doodle->save();
            }

            return redirect()->route('doodles.index')->with('success', 'Doodle created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save doodle: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $doodle = Doodle::findOrFail($id);
        return view('doodle.form', compact('doodle'));
    }

    public function update(Request $request, $id)
    {
        try {
            $doodle = Doodle::findOrFail($id);

            $request->validate([
                'name'        => 'required|string|max:255|unique:doodles,name,' . $doodle->id,
                'doodle_type' => 'required|in:image,line',
                'image'       => 'nullable|file|mimes:webp|mimetypes:image/webp|max:10000',
                'is_premium'  => 'nullable|boolean',
            ], [
                'image.mimes'     => 'Doodle image must be a .webp file.',
                'image.mimetypes' => 'Doodle image must be a .webp file.',
            ]);

            $oldName = $doodle->name;
            $doodle->name        = $request->name;
            $doodle->doodle_type = $request->doodle_type;
            $doodle->is_premium  = $request->has('is_premium') ? 1 : 0;

            if ($oldName !== $doodle->name) {
                $oldFolder = public_path('upload/doodle/' . $oldName);
                $newFolder = public_path('upload/doodle/' . $doodle->name);
                if (File::exists($oldFolder) && !File::exists($newFolder)) {
                    @rename($oldFolder, $newFolder);
                }
            }

            $base = 'upload/doodle/' . $doodle->name;

            if ($request->hasFile('image')) {
                if ($doodle->image && file_exists(public_path($base . '/' . $doodle->image))) {
                    @unlink(public_path($base . '/' . $doodle->image));
                }
                $file = $request->file('image');
                $path = public_path($base);
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $this->uniqueFilename($path, $file->getClientOriginalName());
                $file->move($path, $filename);
                $doodle->image = $filename;
            }

            $doodle->save();

            return redirect()->route('doodles.index')->with('success', 'Doodle updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update doodle: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $doodle = Doodle::findOrFail($id);
        $folder = public_path('upload/doodle/' . $doodle->name);
        if (File::exists($folder)) {
            File::deleteDirectory($folder);
        }
        $doodle->delete();
        return redirect()->route('doodles.index')->with('success', 'Doodle deleted successfully!');
    }

    public function togglePremium(Request $request)
    {
        $doodle = Doodle::findOrFail($request->id);
        $doodle->is_premium = $request->is_premium ? 1 : 0;
        $doodle->save();
        return response()->json(['success' => true, 'message' => 'Premium updated successfully!', 'is_premium' => $doodle->is_premium]);
    }

    public function indexing(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->route('doodles.index');
        }

        $doodles = Doodle::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $data = $doodles->map(function ($doodle) {
            return [
                'id'        => $doodle->id,
                'name'      => $doodle->name,
                'image_url' => $doodle->image
                    ? asset('upload/doodle/' . rawurlencode($doodle->name) . '/' . rawurlencode($doodle->image))
                    : null,
            ];
        });

        return response()->json(['doodles' => $data]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order'              => 'required|array',
            'order.*.id'         => 'required|exists:doodles,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Doodle::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully!']);
    }
}
