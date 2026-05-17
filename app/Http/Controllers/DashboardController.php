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
use App\Models\Font;
use App\Models\Doodle;
use App\Models\FilterCategory;
use App\Models\Filter;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $group = session('dashboard_group');

        if (!$group) {
            return view('dashboard', ['view' => 'chooser']);
        }

        $modules = $this->modulesForGroup($group);

        return view('dashboard', [
            'view'    => 'modules',
            'group'   => $group,
            'modules' => $modules,
        ]);
    }

    public function module(Request $request, $module)
    {
        $group = session('dashboard_group');
        if (!$group) {
            return redirect()->route('dashboard');
        }

        $config = $this->moduleConfig($group, $module);
        if (!$config) {
            return redirect()->route('dashboard');
        }

        return view('dashboard', [
            'view'   => 'module',
            'group'  => $group,
            'module' => $module,
            'config' => $config,
        ]);
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

    private function modulesForGroup(string $group): array
    {
        if ($group === 'baby') {
            return [
                ['key' => 'baby-image',     'title' => 'AI Baby Image Module',      'icon' => 'bi-image',              'gradient' => 'bg-grad-1', 'description' => 'Manage AI Baby image categories and subcategories.'],
                ['key' => 'baby-video',     'title' => 'AI Baby Video Module',      'icon' => 'bi-camera-video-fill',  'gradient' => 'bg-grad-4', 'description' => 'Manage AI Baby video categories and videos.'],
                ['key' => 'dynamic-frame',  'title' => 'Dynamic Photo Frame',       'icon' => 'bi-easel-fill',         'gradient' => 'bg-grad-7', 'description' => 'Manage dynamic photo frame categories and frames.'],
                ['key' => 'home-slider',    'title' => 'Home Screen Slider',        'icon' => 'bi-house-heart-fill',   'gradient' => 'bg-grad-9', 'description' => 'Manage Baby AI home screen sliders.'],
                ['key' => 'sticker',        'title' => 'Sticker Module',            'icon' => 'bi-stickies-fill',      'gradient' => 'bg-grad-10','description' => 'Manage sticker categories and stickers.'],
                ['key' => 'font',           'title' => 'Font Module',               'icon' => 'bi-fonts',              'gradient' => 'bg-grad-5', 'description' => 'Manage fonts available in the app.'],
                ['key' => 'doodle',         'title' => 'Doodle Module',             'icon' => 'bi-stars',              'gradient' => 'bg-grad-2', 'description' => 'Manage doodles available in the app.'],
                ['key' => 'filter',         'title' => 'Filter Module',             'icon' => 'bi-funnel-fill',        'gradient' => 'bg-grad-6', 'description' => 'Manage filter categories and filters with adjustment values.'],
            ];
        }

        if ($group === 'ngd') {
            return [
                ['key' => 'ngd-image',    'title' => 'AI Image NGD Module',  'icon' => 'bi-image',           'gradient' => 'bg-grad-1', 'description' => 'Manage NGD AI image categories and images.'],
                ['key' => 'ngd-video',    'title' => 'AI Video NGD Module',  'icon' => 'bi-camera-reels',    'gradient' => 'bg-grad-3', 'description' => 'Manage NGD AI video categories and videos.'],
                ['key' => 'filter-image', 'title' => 'Filter AI Image',      'icon' => 'bi-filter-circle',   'gradient' => 'bg-grad-5', 'description' => 'Manage Filter AI categories and images.'],
                ['key' => 'lips-sync',    'title' => 'Lips Sync Module',     'icon' => 'bi-music-note-beamed','gradient' => 'bg-grad-7', 'description' => 'Manage Lips Sync categories and items.'],
                ['key' => 'top-slider',   'title' => 'Top Slider Module',    'icon' => 'bi-images',          'gradient' => 'bg-grad-8', 'description' => 'Manage Top Slider categories.'],
            ];
        }

        return [];
    }

    private function moduleConfig(string $group, string $module): ?array
    {
        $configs = [
            'baby' => [
                'baby-image' => [
                    'title'    => 'AI Baby Image Module',
                    'icon'     => 'bi-image',
                    'subtitle' => 'AI Baby image categories and subcategories',
                    'cards'    => [
                        ['title' => 'Image Categories', 'icon' => 'bi-tags',   'count' => AiImageCategory::count(), 'route' => url('subcategories'), 'gradient' => 'bg-grad-1'],
                        ['title' => 'Subcategories',    'icon' => 'bi-images', 'count' => Subcategory::count(),      'route' => url('subcategories'), 'gradient' => 'bg-grad-2'],
                    ],
                ],
                'baby-video' => [
                    'title'    => 'AI Baby Video Module',
                    'icon'     => 'bi-camera-video-fill',
                    'subtitle' => 'AI Baby video categories and videos',
                    'cards'    => [
                        ['title' => 'Video Categories', 'icon' => 'bi-collection-play',  'count' => AiVideoCategory::count(), 'route' => route('ai-baby-video.categories.index'), 'gradient' => 'bg-grad-3'],
                        ['title' => 'Videos',           'icon' => 'bi-camera-video-fill','count' => AiBabyVideo::count(),     'route' => route('ai-baby-video.videos.index'),     'gradient' => 'bg-grad-4'],
                    ],
                ],
                'dynamic-frame' => [
                    'title'    => 'Dynamic Photo Frame Module',
                    'icon'     => 'bi-easel-fill',
                    'subtitle' => 'Dynamic photo frame categories and frames',
                    'cards'    => [
                        ['title' => 'Frame Categories', 'icon' => 'bi-tags',   'count' => DynamicPhotoFrameCategory::count(), 'route' => route('dynamic-photo-frame.categories.index'), 'gradient' => 'bg-grad-7'],
                        ['title' => 'Frames',           'icon' => 'bi-images', 'count' => DynamicPhotoFrame::count(),         'route' => route('dynamic-photo-frame.frames.index'),     'gradient' => 'bg-grad-8'],
                    ],
                ],
                'home-slider' => [
                    'title'    => 'Home Screen Slider Module',
                    'icon'     => 'bi-house-heart-fill',
                    'subtitle' => 'Baby AI home screen slider entries',
                    'cards'    => [
                        ['title' => 'Home Sliders', 'icon' => 'bi-house-heart-fill', 'count' => BabyAiHomeSlider::count(), 'route' => route('baby-ai-home-slider.index'), 'gradient' => 'bg-grad-9'],
                    ],
                ],
                'sticker' => [
                    'title'    => 'Sticker Module',
                    'icon'     => 'bi-stickies-fill',
                    'subtitle' => 'Sticker categories and stickers',
                    'cards'    => [
                        ['title' => 'Sticker Categories', 'icon' => 'bi-tags',              'count' => StickerCategory::count(), 'route' => route('sticker.categories.index'), 'gradient' => 'bg-grad-10'],
                        ['title' => 'Stickers',           'icon' => 'bi-emoji-smile-fill',  'count' => (int) StickerCategory::sum(DB::raw('COALESCE(JSON_LENGTH(stickers), 0)')), 'route' => route('sticker.stickers.index'), 'gradient' => 'bg-grad-6'],
                    ],
                ],
                'font' => [
                    'title'    => 'Font Module',
                    'icon'     => 'bi-fonts',
                    'subtitle' => 'Fonts available in the app',
                    'cards'    => [
                        ['title' => 'Fonts', 'icon' => 'bi-fonts', 'count' => Font::count(), 'route' => route('fonts.index'), 'gradient' => 'bg-grad-5'],
                    ],
                ],
                'doodle' => [
                    'title'    => 'Doodle Module',
                    'icon'     => 'bi-stars',
                    'subtitle' => 'Doodles available in the app',
                    'cards'    => [
                        ['title' => 'Doodles', 'icon' => 'bi-stars', 'count' => Doodle::count(), 'route' => route('doodles.index'), 'gradient' => 'bg-grad-2'],
                    ],
                ],
                'filter' => [
                    'title'    => 'Filter Module',
                    'icon'     => 'bi-funnel-fill',
                    'subtitle' => 'Filter categories and filters with adjustment values',
                    'cards'    => [
                        ['title' => 'Filter Categories', 'icon' => 'bi-tags',     'count' => FilterCategory::count(), 'route' => route('filter.categories.index'), 'gradient' => 'bg-grad-6'],
                        ['title' => 'Filters',           'icon' => 'bi-sliders',  'count' => Filter::count(),         'route' => route('filter.filters.index'),    'gradient' => 'bg-grad-9'],
                    ],
                ],
            ],
            'ngd' => [
                'ngd-image' => [
                    'title'    => 'AI Image NGD Module',
                    'icon'     => 'bi-image',
                    'subtitle' => 'NGD AI image categories and images',
                    'cards'    => [
                        ['title' => 'Categories', 'icon' => 'bi-tags',   'count' => NgendevCategory::count(), 'route' => route('ngendev.categories.index'), 'gradient' => 'bg-grad-1'],
                        ['title' => 'Images',     'icon' => 'bi-images', 'count' => NgendevImage::count(),    'route' => route('ngendev.images.index'),     'gradient' => 'bg-grad-2'],
                    ],
                ],
                'ngd-video' => [
                    'title'    => 'AI Video NGD Module',
                    'icon'     => 'bi-camera-reels',
                    'subtitle' => 'NGD AI video categories and videos',
                    'cards'    => [
                        ['title' => 'Video Categories', 'icon' => 'bi-collection-play',  'count' => NgendevVideoCategory::count(), 'route' => route('ngendev-video-categories.index'), 'gradient' => 'bg-grad-3'],
                        ['title' => 'Videos',           'icon' => 'bi-camera-video-fill','count' => NgendevVideo::count(),         'route' => route('ngendev-videos.index'),           'gradient' => 'bg-grad-4'],
                    ],
                ],
                'filter-image' => [
                    'title'    => 'Filter AI Image Module',
                    'icon'     => 'bi-filter-circle',
                    'subtitle' => 'Filter AI categories and images',
                    'cards'    => [
                        ['title' => 'Filter Categories', 'icon' => 'bi-tags',   'count' => FilterAiImageCategory::count(), 'route' => route('filter-ai-image.categories.index'), 'gradient' => 'bg-grad-5'],
                        ['title' => 'Filter Images',     'icon' => 'bi-images', 'count' => FilterAiImage::count(),         'route' => route('filter-ai-image.images.index'),     'gradient' => 'bg-grad-9'],
                    ],
                ],
                'lips-sync' => [
                    'title'    => 'Lips Sync Module',
                    'icon'     => 'bi-music-note-beamed',
                    'subtitle' => 'Lips Sync categories and items',
                    'cards'    => [
                        ['title' => 'Lips Sync Categories', 'icon' => 'bi-tags',       'count' => LipsSyncCategory::count(), 'route' => route('lips-sync.categories.index'), 'gradient' => 'bg-grad-7'],
                        ['title' => 'Lips Sync Items',      'icon' => 'bi-music-note', 'count' => LipsSyncItem::count(),     'route' => route('lips-sync.items.index'),      'gradient' => 'bg-grad-6'],
                    ],
                ],
                'top-slider' => [
                    'title'    => 'Top Slider Module',
                    'icon'     => 'bi-images',
                    'subtitle' => 'Top Slider categories',
                    'cards'    => [
                        ['title' => 'Top Slider Categories', 'icon' => 'bi-images', 'count' => TopSliderCategory::count(), 'route' => route('top-slider.categories.index'), 'gradient' => 'bg-grad-8'],
                    ],
                ],
            ],
        ];

        return $configs[$group][$module] ?? null;
    }
}
