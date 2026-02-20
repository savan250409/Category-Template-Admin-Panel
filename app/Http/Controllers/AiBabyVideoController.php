<?php

namespace App\Http\Controllers;

use App\Models\AiBabyVideo;
use App\Models\AiVideoCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AiBabyVideoController extends Controller
{
    public function index(Request $request)
    {
        $categories = AiVideoCategory::where('status', 1)->get();

        $query = AiBabyVideo::with('category');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ai_prompt', 'like', "%{$search}%")
                    ->orWhere('video_title', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        $videos = $query->latest()->paginate(10); // Check if pagination works or needs adjustment

        return view('ai_baby_video.videos.index', compact('categories', 'videos'));
    }

    public function create()
    {
        $categories = AiVideoCategory::where('status', 1)->get();
        return view('ai_baby_video.videos.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:ai_baby_video_categories,id',
            'video_title' => 'required|string|max:255',
            'video_path' => 'required|mimes:mp4,mov,avi,wmv|max:51200',
            'ai_prompt' => 'nullable|string',
            'name_change' => 'boolean',
        ]);

        $category = AiVideoCategory::findOrFail($request->category_id);

        $videoPath = null;
        $thumbnailPath = null;

        if ($request->hasFile('video_path')) {
            $file = $request->file('video_path');
            $filename = $file->getClientOriginalName();

            $videoRelativePath = 'upload/Ai Baby Video/' . $category->category_name . '/video';
            $videoDestPath = public_path($videoRelativePath);

            if (!File::exists($videoDestPath)) {
                File::makeDirectory($videoDestPath, 0777, true);
            }

            $file->move($videoDestPath, $filename);
            $videoPath = $filename;

            // Handle Client-Generated Thumbnail
            if ($request->has('generated_thumbnail') && !empty($request->generated_thumbnail)) {
                $image = $request->generated_thumbnail;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';

                $thumbRelativePath = 'upload/Ai Baby Video/' . $category->category_name . '/video thumbanail';
                $thumbDestPath = public_path($thumbRelativePath);
                if (!File::exists($thumbDestPath)) {
                    File::makeDirectory($thumbDestPath, 0777, true);
                }

                File::put($thumbDestPath . '/' . $imageName, base64_decode($image));
                $thumbnailPath = $imageName;
            }
        }

        AiBabyVideo::create([
            'category_id' => $request->category_id,
            'video_title' => $request->video_title,
            'video_path' => $videoPath,
            'video_thumbnail' => $thumbnailPath,
            'ai_prompt' => $request->ai_prompt,
            'name_change' => $request->has('name_change') ? 1 : 0,
        ]);

        return redirect()->route('ai-baby-video.videos.index')->with('success', 'Video added successfully!');
    }

    public function edit($id)
    {
        $video = AiBabyVideo::findOrFail($id);
        $categories = AiVideoCategory::where('status', 1)->get();
        return view('ai_baby_video.videos.form', compact('video', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $video = AiBabyVideo::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:ai_baby_video_categories,id',
            'video_title' => 'required|string|max:255',
            'video_path' => 'nullable|mimes:mp4,mov,avi,wmv|max:51200',
            'ai_prompt' => 'nullable|string',
            'name_change' => 'boolean',
        ]);

        $category = AiVideoCategory::findOrFail($request->category_id);

        $videoPath = $video->video_path;
        $thumbnailPath = $video->video_thumbnail;
        $basePathLegacy = 'upload/AI Baby Video/' . $category->category_name . '/';

        if ($request->hasFile('video_path')) {
            // Delete old video and thumbnail
            if ($video->video_path) {
                if (file_exists(public_path($video->video_path))) {
                    unlink(public_path($video->video_path));
                } elseif (file_exists(public_path($basePathLegacy . $video->video_path))) {
                    unlink(public_path($basePathLegacy . $video->video_path));
                }
            }
            if ($video->video_thumbnail) {
                if (file_exists(public_path($video->video_thumbnail))) {
                    unlink(public_path($video->video_thumbnail));
                } elseif (file_exists(public_path($basePathLegacy . $video->video_thumbnail))) {
                    unlink(public_path($basePathLegacy . $video->video_thumbnail));
                }
            }

            $file = $request->file('video_path');
            $filename = $file->getClientOriginalName();

            $videoRelativePath = 'upload/Ai Baby Video/' . $category->category_name . '/video';
            $videoDestPath = public_path($videoRelativePath);

            if (!File::exists($videoDestPath)) {
                File::makeDirectory($videoDestPath, 0777, true);
            }

            $file->move($videoDestPath, $filename);
            $videoPath = $filename;

            // Handle Client-Generated Thumbnail
            if ($request->has('generated_thumbnail') && !empty($request->generated_thumbnail)) {
                $image = $request->generated_thumbnail;
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';

                $thumbRelativePath = 'upload/Ai Baby Video/' . $category->category_name . '/video thumbanail';
                $thumbDestPath = public_path($thumbRelativePath);
                if (!File::exists($thumbDestPath)) {
                    File::makeDirectory($thumbDestPath, 0777, true);
                }

                File::put($thumbDestPath . '/' . $imageName, base64_decode($image));
                $thumbnailPath = $imageName;
            }
        }

        $video->update([
            'category_id' => $request->category_id,
            'video_title' => $request->video_title,
            'video_path' => $videoPath,
            'video_thumbnail' => $thumbnailPath,
            'ai_prompt' => $request->ai_prompt,
            'name_change' => $request->has('name_change') ? 1 : 0,
        ]);

        return redirect()->route('ai-baby-video.videos.index')->with('success', 'Video updated successfully!');
    }

    public function destroy($id)
    {
        $video = AiBabyVideo::findOrFail($id);
        $category = AiVideoCategory::find($video->category_id);
        $categoryName = $category ? $category->category_name : '';

        $basePathLegacy = 'upload/AI Baby Video/' . $categoryName . '/';

        if ($video->video_path) {
            if (file_exists(public_path($video->video_path))) {
                unlink(public_path($video->video_path));
            } elseif (file_exists(public_path($basePathLegacy . $video->video_path))) {
                unlink(public_path($basePathLegacy . $video->video_path));
            }
        }

        if ($video->video_thumbnail) {
            if (file_exists(public_path($video->video_thumbnail))) {
                unlink(public_path($video->video_thumbnail));
            } elseif (file_exists(public_path($basePathLegacy . $video->video_thumbnail))) {
                unlink(public_path($basePathLegacy . $video->video_thumbnail));
            }
        }

        $video->delete();

        return redirect()->route('ai-baby-video.videos.index')->with('success', 'Video deleted successfully!');
    }
}
