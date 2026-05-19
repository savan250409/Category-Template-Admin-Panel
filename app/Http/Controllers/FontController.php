<?php

namespace App\Http\Controllers;

use App\Models\Font;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FontController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $query = Font::query();

        if ($search) {
            $query->where('font_name', 'like', '%' . $search . '%');
        }

        $fonts = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('font.table', compact('fonts'))->render(),
                'total' => $fonts->total(),
            ]);
        }

        return view('font.index', compact('fonts', 'perPage', 'search'));
    }

    public function create()
    {
        return view('font.form');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'font_name'     => 'required|string|max:255|unique:fonts,font_name',
                'font_file'     => 'required|file|mimetypes:font/ttf,font/otf,font/sfnt,application/font-sfnt,application/x-font-ttf,application/x-font-otf,application/octet-stream|max:20480',
                'preview_image' => 'nullable|image|mimes:webp|max:10000',
                'is_premium'    => 'nullable|boolean',
            ], [
                'font_file.mimetypes'  => 'Font file must be a .ttf or .otf file.',
                'preview_image.mimes'  => 'Preview image must be a .webp image.',
            ]);

            if (!$this->isFontExtension($request->file('font_file'))) {
                return redirect()->back()->withInput()->with('error', 'Font file must be a .ttf or .otf file.');
            }

            $minOrder = Font::min('sort_order');
            $minOrder = is_null($minOrder) ? 0 : (int) $minOrder;

            $font = new Font();
            $font->font_name  = $request->font_name;
            $font->is_premium = $request->has('is_premium') ? 1 : 0;
            $font->status     = 1;
            $font->sort_order = $minOrder - 1;
            $font->save();

            $base = 'upload/font/' . $font->font_name;

            if ($request->hasFile('font_file')) {
                $file = $request->file('font_file');
                $path = public_path($base);
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $file->getClientOriginalName();
                if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                    @unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
                $file->move($path, $filename);
                $font->font_file = $filename;
            }

            if ($request->hasFile('preview_image')) {
                $file = $request->file('preview_image');
                $path = public_path($base);
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $file->getClientOriginalName();
                if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                    @unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
                $file->move($path, $filename);
                $font->preview_image = $filename;
            }

            $font->save();

            return view('partials.history_redirect', ['fallback' => route('fonts.index'), 'message' => 'Font created successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save font: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $font = Font::findOrFail($id);
        return view('font.form', compact('font'));
    }

    public function update(Request $request, $id)
    {
        try {
            $font = Font::findOrFail($id);

            $request->validate([
                'font_name'     => 'required|string|max:255|unique:fonts,font_name,' . $font->id,
                'font_file'     => 'nullable|file|mimetypes:font/ttf,font/otf,font/sfnt,application/font-sfnt,application/x-font-ttf,application/x-font-otf,application/octet-stream|max:20480',
                'preview_image' => 'nullable|image|mimes:webp|max:10000',
                'is_premium'    => 'nullable|boolean',
            ], [
                'font_file.mimetypes'  => 'Font file must be a .ttf or .otf file.',
                'preview_image.mimes'  => 'Preview image must be a .webp image.',
            ]);

            if ($request->hasFile('font_file') && !$this->isFontExtension($request->file('font_file'))) {
                return redirect()->back()->withInput()->with('error', 'Font file must be a .ttf or .otf file.');
            }

            $oldName = $font->font_name;
            $font->font_name  = $request->font_name;
            $font->is_premium = $request->has('is_premium') ? 1 : 0;

            if ($oldName !== $font->font_name) {
                $oldFolder = public_path('upload/font/' . $oldName);
                $newFolder = public_path('upload/font/' . $font->font_name);
                if (File::exists($oldFolder) && !File::exists($newFolder)) {
                    @rename($oldFolder, $newFolder);
                }
            }

            $base = 'upload/font/' . $font->font_name;

            if ($request->hasFile('font_file')) {
                if ($font->font_file && file_exists(public_path($base . '/' . $font->font_file))) {
                    @unlink(public_path($base . '/' . $font->font_file));
                }
                $file = $request->file('font_file');
                $path = public_path($base);
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $file->getClientOriginalName();
                if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                    @unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
                $file->move($path, $filename);
                $font->font_file = $filename;
            }

            if ($request->hasFile('preview_image')) {
                if ($font->preview_image && file_exists(public_path($base . '/' . $font->preview_image))) {
                    @unlink(public_path($base . '/' . $font->preview_image));
                }
                $file = $request->file('preview_image');
                $path = public_path($base);
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                $filename = $file->getClientOriginalName();
                if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                    @unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
                $file->move($path, $filename);
                $font->preview_image = $filename;
            }

            $font->save();

            return view('partials.history_redirect', ['fallback' => route('fonts.index'), 'message' => 'Font updated successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update font: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $font   = Font::findOrFail($id);
        $folder = public_path('upload/font/' . $font->font_name);
        if (File::exists($folder)) {
            File::deleteDirectory($folder);
        }
        $font->delete();
        return redirect()->route('fonts.index')->with('success', 'Font deleted successfully!');
    }

    public function togglePremium(Request $request)
    {
        $font = Font::findOrFail($request->id);
        $font->is_premium = $request->is_premium ? 1 : 0;
        $font->save();
        return response()->json(['success' => true, 'message' => 'Premium updated successfully!', 'is_premium' => $font->is_premium]);
    }

    private function isFontExtension($file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return in_array($ext, ['ttf', 'otf'], true);
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
