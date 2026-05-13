<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\AiImageCategory;
use App\Models\AiVideoCategory;
use App\Models\AiBabyVideo;
use App\Models\DynamicPhotoFrameCategory;
use App\Models\DynamicPhotoFrame;
use App\Models\BabyAiHomeSlider;
use App\Models\NgendevCategory;
use App\Models\NgendevImage;
use App\Models\NgendevVideoCategory;
use App\Models\NgendevVideo;
use App\Models\FilterAiImageCategory;
use App\Models\FilterAiImage;
use App\Models\TopSliderCategory;
use App\Models\LipsSyncCategory;
use App\Models\LipsSyncItem;
use App\Models\StickerCategory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $group  = session('dashboard_group');
        $counts = [];

        if ($group === 'baby') {
            $counts = [
                'image_categories'    => AiImageCategory::count(),
                'image_subcategories' => Subcategory::count(),
                'video_categories'    => AiVideoCategory::count(),
                'videos'              => AiBabyVideo::count(),
                'frame_categories'    => DynamicPhotoFrameCategory::count(),
                'frames'              => DynamicPhotoFrame::count(),
                'sliders'             => BabyAiHomeSlider::count(),
                'sticker_categories'  => StickerCategory::count(),
                'stickers'            => (int) StickerCategory::sum(DB::raw('COALESCE(JSON_LENGTH(stickers), 0)')),
            ];
        } elseif ($group === 'ngd') {
            $counts = [
                'ngd_image_categories' => NgendevCategory::count(),
                'ngd_images'           => NgendevImage::count(),
                'ngd_video_categories' => NgendevVideoCategory::count(),
                'ngd_videos'           => NgendevVideo::count(),
                'filter_categories'    => FilterAiImageCategory::count(),
                'filter_images'        => FilterAiImage::count(),
                'top_slider_categories'=> TopSliderCategory::count(),
                'lips_sync_categories' => LipsSyncCategory::count(),
                'lips_sync_items'      => LipsSyncItem::count(),
            ];
        }

        return view('dashboard', compact('group', 'counts'));
    }

    public function selectGroup(Request $request, $group)
    {
        if (!in_array($group, ['baby', 'ngd'])) {
            return redirect()->route('dashboard');
        }

        $request->session()->put('dashboard_group', $group);

        return redirect()->route('dashboard');
    }

    public function clearGroup(Request $request)
    {
        $request->session()->forget('dashboard_group');
        return redirect()->route('dashboard');
    }
}
