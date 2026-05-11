<?php

namespace App\Http\Controllers;

use App\Models\BabyAiHomeSlider;
use App\Models\Subcategory;
use App\Models\AiImageCategory;
use App\Models\AiVideoCategory;
use App\Models\DynamicPhotoFrameCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BabyAiHomeSliderController extends Controller
{
    private const SOURCE_TYPES = ['image', 'video', 'dynamic_frame'];

    public function index(Request $request)
    {
        $perPage    = (int) $request->input('per_page', 10);
        $search     = $request->input('search', '');
        $sourceType = $request->input('source_type', '');

        $query = BabyAiHomeSlider::query();

        if ($sourceType) {
            $query->where('source_type', $sourceType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $sliders = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $usedTypes  = BabyAiHomeSlider::pluck('source_type')->toArray();
        $canAddMore = count(array_diff(self::SOURCE_TYPES, $usedTypes)) > 0;

        return view('baby_ai_home_slider.index', compact('sliders', 'canAddMore', 'perPage', 'search', 'sourceType'));
    }

    public function create()
    {
        $used = BabyAiHomeSlider::pluck('source_type')->toArray();
        if (count(array_diff(self::SOURCE_TYPES, $used)) === 0) {
            return redirect()->route('baby-ai-home-slider.index')->with('error', 'All 3 source types are already added.');
        }

        $imageCategories    = AiImageCategory::select('id', 'name')->orderBy('name')->get();
        $imageSubcategories = Subcategory::select('id', 'title', 'category_name')->orderBy('title')->get();
        $videoCategories    = AiVideoCategory::select('id', 'category_name', 'category_image')->orderBy('category_name')->get();
        $dynamicFrameCategories = DynamicPhotoFrameCategory::select('id', 'category_name', 'image')->orderBy('category_name')->get();

        return view('baby_ai_home_slider.form', compact('imageCategories', 'imageSubcategories', 'videoCategories', 'dynamicFrameCategories', 'used'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'source_type'     => 'required|in:image,video,dynamic_frame',
                'source_id'       => 'required|integer',
                'title'           => 'nullable|string|max:255',
                'description'     => 'nullable|string',
                'image'           => 'nullable|image|mimes:webp|max:10000',
                'video'           => 'nullable|mimes:mp4,mov,ogg,qt|max:50000',
                'video_thumbnail' => 'nullable|image|mimes:webp|max:10000',
            ]);

            if (BabyAiHomeSlider::where('source_type', $request->source_type)->exists()) {
                return redirect()->back()->withInput()->with('error', 'A slider with this source type already exists. Edit the existing one instead.');
            }

            $this->validateSourceExists($request->source_type, $request->source_id);

            $slider = new BabyAiHomeSlider();
            $slider->source_type = $request->source_type;
            $slider->source_id   = $request->source_id;
            $slider->title       = $request->title;
            $slider->description = $request->description;
            $slider->is_on       = $request->has('is_on') ? 1 : 0;
            $slider->status      = 1;
            $slider->save();

            $base = 'upload/baby_ai_home_slider/' . $slider->source_type;
            $path = public_path($base);
            if (!File::exists($path)) File::makeDirectory($path, 0777, true);

            if ($request->source_type === 'video') {
                if ($request->hasFile('video')) {
                    $file = $request->file('video');
                    $filename = time() . '_vid_' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $slider->video = $filename;
                }
                if ($request->hasFile('video_thumbnail')) {
                    $file = $request->file('video_thumbnail');
                    $filename = time() . '_vidthumb_' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $slider->video_thumbnail = $filename;
                }
            } else {
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $filename = time() . '_img_' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $slider->image = $filename;
                }
            }

            $slider->save();

            return redirect()->route('baby-ai-home-slider.index')->with('success', 'Home Screen Slider created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $slider = BabyAiHomeSlider::findOrFail($id);

        $imageCategories    = AiImageCategory::select('id', 'name')->orderBy('name')->get();
        $imageSubcategories = Subcategory::select('id', 'title', 'category_name')->orderBy('title')->get();
        $videoCategories    = AiVideoCategory::select('id', 'category_name', 'category_image')->orderBy('category_name')->get();
        $dynamicFrameCategories = DynamicPhotoFrameCategory::select('id', 'category_name', 'image')->orderBy('category_name')->get();

        return view('baby_ai_home_slider.form', compact('slider', 'imageCategories', 'imageSubcategories', 'videoCategories', 'dynamicFrameCategories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $slider = BabyAiHomeSlider::findOrFail($id);

            $request->validate([
                'source_id'       => 'required|integer',
                'title'           => 'nullable|string|max:255',
                'description'     => 'nullable|string',
                'image'           => 'nullable|image|mimes:webp|max:10000',
                'video'           => 'nullable|mimes:mp4,mov,ogg,qt|max:50000',
                'video_thumbnail' => 'nullable|image|mimes:webp|max:10000',
            ]);

            $this->validateSourceExists($slider->source_type, $request->source_id);

            $slider->source_id   = $request->source_id;
            $slider->title       = $request->title;
            $slider->description = $request->description;
            $slider->is_on       = $request->has('is_on') ? 1 : 0;

            $base = 'upload/baby_ai_home_slider/' . $slider->source_type;
            $path = public_path($base);
            if (!File::exists($path)) File::makeDirectory($path, 0777, true);

            if ($slider->source_type === 'video') {
                if ($request->hasFile('video')) {
                    if ($slider->video && file_exists($path . '/' . $slider->video)) {
                        @unlink($path . '/' . $slider->video);
                    }
                    $file = $request->file('video');
                    $filename = time() . '_vid_' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $slider->video = $filename;
                }
                if ($request->hasFile('video_thumbnail')) {
                    if ($slider->video_thumbnail && file_exists($path . '/' . $slider->video_thumbnail)) {
                        @unlink($path . '/' . $slider->video_thumbnail);
                    }
                    $file = $request->file('video_thumbnail');
                    $filename = time() . '_vidthumb_' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $slider->video_thumbnail = $filename;
                }
            } else {
                if ($request->hasFile('image')) {
                    if ($slider->image && file_exists($path . '/' . $slider->image)) {
                        @unlink($path . '/' . $slider->image);
                    }
                    $file = $request->file('image');
                    $filename = time() . '_img_' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $slider->image = $filename;
                }
            }

            $slider->save();

            return redirect()->route('baby-ai-home-slider.index')->with('success', 'Home Screen Slider updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $slider = BabyAiHomeSlider::findOrFail($id);

        $folder = public_path('upload/baby_ai_home_slider/' . $slider->source_type);
        foreach ([$slider->image, $slider->video, $slider->video_thumbnail] as $f) {
            if ($f && file_exists($folder . '/' . $f)) {
                @unlink($folder . '/' . $f);
            }
        }

        $slider->delete();

        return redirect()->route('baby-ai-home-slider.index')->with('success', 'Home Screen Slider deleted successfully!');
    }

    public function toggleStatus(Request $request)
    {
        $slider = BabyAiHomeSlider::findOrFail($request->id);
        $slider->is_on = $request->is_on ? 1 : 0;
        $slider->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
    }

    private function validateSourceExists(string $type, $id): void
    {
        $exists = match ($type) {
            'image'         => Subcategory::where('id', $id)->exists(),
            'video'         => AiVideoCategory::where('id', $id)->exists(),
            'dynamic_frame' => DynamicPhotoFrameCategory::where('id', $id)->exists(),
            default         => false,
        };
        if (!$exists) {
            throw new \Exception('Selected source category does not exist.');
        }
    }
}
