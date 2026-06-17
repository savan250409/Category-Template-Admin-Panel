<?php

namespace App\Http\Controllers;

use App\Models\FirebaseProject;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\ScheduledNotification;
use App\Services\FcmService;
use App\Support\BackgroundProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Notify module controller. Drives:
 *   - the notifications list (index/show)
 *   - the shared per-category send form (form/store) used by every module
 *   - the global broadcast form (global/globalStore)
 *   - default-delay settings (settings/saveSettings)
 *
 * Every push is broadcast to the topic of all active Firebase projects by
 * the background dispatcher, so one notification reaches all apps at once.
 */
class NotificationController extends Controller
{
    /**
     * Module registry. To onboard a module, add one entry here and drop
     * the "send push after save" toggle into its create form.
     *
     *   slug => [model, redirect route name, display label, app type]
     */
    protected $modules = [
        'ngendev_image' => [
            'model'        => \App\Models\NgendevCategory::class,
            'redirect'     => 'ngendev.categories.index',
            'display_name' => 'AI Image NGD',
            'app_type'     => 'img',
        ],
        'ngendev_video' => [
            'model'        => \App\Models\NgendevVideoCategory::class,
            'redirect'     => 'ngendev-video-categories.index',
            'display_name' => 'AI Video NGD',
            'app_type'     => 'vid',
        ],
        'filter_ai_image' => [
            'model'        => \App\Models\FilterAiImageCategory::class,
            'redirect'     => 'filter-ai-image.categories.index',
            'display_name' => 'Filter AI Image',
            'app_type'     => 'img',
        ],
        'ai_baby_video' => [
            'model'        => \App\Models\AiVideoCategory::class,
            'redirect'     => 'ai-baby-video.categories.index',
            'display_name' => 'AI Baby Video',
            'app_type'     => 'vid',
        ],
        'lips_sync' => [
            'model'        => \App\Models\LipsSyncCategory::class,
            'redirect'     => 'lips-sync.categories.index',
            'display_name' => 'Lips Sync',
            'app_type'     => 'vid',
        ],
        'dynamic_photo_frame' => [
            'model'        => \App\Models\DynamicPhotoFrameCategory::class,
            'redirect'     => 'dynamic-photo-frame.categories.index',
            'display_name' => 'Dynamic Photo Frame',
            'app_type'     => 'img',
        ],
        'sticker' => [
            'model'        => \App\Models\StickerCategory::class,
            'redirect'     => 'sticker.categories.index',
            'display_name' => 'Sticker',
            'app_type'     => 'img',
        ],
        'filter' => [
            'model'        => \App\Models\FilterCategory::class,
            'redirect'     => 'filter.categories.index',
            'display_name' => 'Filter',
            'app_type'     => 'img',
        ],
    ];

    /* -----------------------------------------------------------------
     | List + detail
     |-----------------------------------------------------------------*/

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');
        $perPage = (int) $request->input('per_page', 10);

        $query = ScheduledNotification::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if (in_array($status, ['pending', 'sent', 'failed'], true)) {
            $query->where('status', $status);
        }

        $notifications = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
        $total = ScheduledNotification::count();

        $moduleLabels = collect($this->modules)->map(fn ($m) => $m['display_name']);

        return view('notifications.index', compact('notifications', 'total', 'search', 'status', 'perPage', 'moduleLabels'));
    }

    public function show($id)
    {
        $notification = ScheduledNotification::findOrFail($id);
        $logs = NotificationLog::with('firebaseProject')
            ->where('scheduled_notification_id', $id)
            ->orderByDesc('id')
            ->get();

        return view('notifications.show', compact('notification', 'logs'));
    }

    /* -----------------------------------------------------------------
     | Per-category send form (shared by all modules)
     |-----------------------------------------------------------------*/

    public function form(string $module, $id)
    {
        $config     = $this->moduleConfig($module);
        $modelClass = $config['model'];
        $category   = $modelClass::findOrFail($id);

        $projects       = FirebaseProject::where('is_active', 1)
            ->orderByDesc('is_default')->orderBy('display_name')->get();
        $preSelectedIds = array_map('intval', (array) session('notify_project_ids', []));

        return view('notifications.form', [
            'module'          => $module,
            'moduleName'      => $config['display_name'],
            'category'        => $category,
            'categoryName'    => $this->resolveCategoryName($category),
            'defaultDelayMin' => $this->defaultDelay(),
            'projects'        => $projects,
            'preSelectedIds'  => $preSelectedIds,
        ]);
    }

    public function store(Request $request, string $module, $id)
    {
        $config     = $this->moduleConfig($module);
        $modelClass = $config['model'];
        $category   = $modelClass::findOrFail($id);

        $request->validate([
            'title'                  => 'required|string|max:191',
            'description'            => 'nullable|string|max:2990',
            'notification_image_url' => 'nullable|url|max:500',
            'scheduled_at'           => 'nullable|date',
            'firebase_project_ids'   => 'nullable|array',
            'firebase_project_ids.*' => 'integer|exists:firebase_projects,id',
        ]);

        $categoryName = $this->resolveCategoryName($category);
        $scheduledAt  = $this->resolveScheduledAt($request);

        $targetIds = $request->filled('firebase_project_ids')
            ? array_values(array_map('intval', (array) $request->firebase_project_ids))
            : null;

        ScheduledNotification::create([
            'module'             => $module,
            'type'               => $config['app_type'] ?? 'img',
            'category_id'        => $category->id,
            'title'              => $request->title,
            'description'        => $request->description,
            'image_url'          => $this->cleanImageUrl($request),
            'click_action'       => 'OPEN_' . strtoupper($module) . '_CATEGORY',
            'screen'             => $module . '_category_detail',
            'extra_data'         => array_merge([
                'category_name' => $categoryName,
                'deep_link'     => 'app://' . $module . '/category/' . $category->id,
            ], $this->collectExtraFields($request)),
            'target_project_ids' => $targetIds,
            'scheduled_at'       => $scheduledAt,
            'status'             => 'pending',
        ]);

        $this->spawnDispatcher($scheduledAt);

        return redirect()->route($config['redirect'])->with('success', $this->scheduledMessage($scheduledAt));
    }

    /* -----------------------------------------------------------------
     | Global broadcast
     |-----------------------------------------------------------------*/

    public function globalForm()
    {
        $projects = FirebaseProject::where('is_active', 1)
            ->orderByDesc('is_default')->orderBy('display_name')->get();

        return view('notifications.global', [
            'defaultDelayMin' => $this->defaultDelay(),
            'projects'        => $projects,
        ]);
    }

    public function globalStore(Request $request)
    {
        $request->validate([
            'title'                    => 'required|string|max:191',
            'description'              => 'nullable|string|max:2990',
            'notification_image_url'   => 'nullable|url|max:500',
            'scheduled_at'             => 'nullable|date',
            'firebase_project_ids'     => 'nullable|array',
            'firebase_project_ids.*'   => 'integer|exists:firebase_projects,id',
        ]);

        $scheduledAt = $this->resolveScheduledAt($request);

        $targetIds = $request->filled('firebase_project_ids')
            ? array_values(array_map('intval', (array) $request->firebase_project_ids))
            : null;

        ScheduledNotification::create([
            'module'             => 'global',
            'type'               => 'global',
            'category_id'        => null,
            'title'              => $request->title,
            'description'        => $request->description,
            'image_url'          => $this->cleanImageUrl($request),
            'click_action'       => 'OPEN_HOME',
            'screen'             => 'home',
            'extra_data'         => array_merge(['global' => true], $this->collectExtraFields($request)),
            'target_project_ids' => $targetIds,
            'scheduled_at'       => $scheduledAt,
            'status'             => 'pending',
        ]);

        $this->spawnDispatcher($scheduledAt);

        return redirect()->route('notifications.index')->with('success', $this->scheduledMessage($scheduledAt));
    }

    /* -----------------------------------------------------------------
     | Image upload (Upload-from-device button)
     |-----------------------------------------------------------------*/

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5 MB
        ]);

        $dir = public_path('upload/notifications');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $request->file('image');
        $name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $file->getClientOriginalName());
        $file->move($dir, $name);

        return response()->json([
            'url' => url('upload/notifications/' . $name),
        ]);
    }

    /* -----------------------------------------------------------------
     | Device-token tester (diagnostics)
     |-----------------------------------------------------------------*/

    public function testForm()
    {
        $projects = FirebaseProject::where('is_active', 1)
            ->orderByDesc('is_default')->orderBy('display_name')->get();
        return view('notifications.test', compact('projects'));
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'token'               => 'required|string',
            'firebase_project_id' => 'nullable|integer',
            'title'               => 'required|string|max:191',
            'description'         => 'nullable|string|max:500',
        ]);

        $project = $request->filled('firebase_project_id')
            ? FirebaseProject::find($request->firebase_project_id)
            : FirebaseProject::where('is_active', 1)->orderByDesc('is_default')->first();

        if (!$project || !$project->isUsable()) {
            return back()->with('error', 'No usable Firebase project found — add one with a valid service-account JSON first.');
        }

        try {
            $fcm    = FcmService::forProject($project);
            $result = $fcm->sendToToken(
                trim($request->token),
                $request->title,
                $request->description ?? '',
                ['type' => 'test', 'click_action' => 'OPEN_HOME'],
                null,
                ['module' => 'test', 'event' => 'token_test']
            );

            if (!empty($result['success'])) {
                return back()->with('success', "✅ Delivered to the device via '{$project->key}'. FCM id: " . ($result['name'] ?? ''));
            }

            $err = $result['error'] ?? 'unknown error';
            $hint = $this->tokenErrorHint($err);
            return back()->withInput()->with('error', "❌ FCM rejected it via '{$project->key}': {$err}{$hint}");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

    public function testLookup(Request $request)
    {
        $request->validate([
            'token'               => 'required|string',
            'firebase_project_id' => 'nullable|integer',
        ]);

        $project = $request->filled('firebase_project_id')
            ? FirebaseProject::find($request->firebase_project_id)
            : FirebaseProject::where('is_active', 1)->orderByDesc('is_default')->first();

        if (!$project || !$project->isUsable()) {
            return back()->withInput()->with('error', 'No usable Firebase project found.');
        }

        $res = FcmService::forProject($project)->getTokenInfo(trim($request->token));

        if (empty($res['success'])) {
            return back()->withInput()->with('error', 'Lookup failed: ' . ($res['error'] ?? 'unknown'));
        }

        $info     = $res['info'];
        $appPkg   = $info['application']      ?? '(unknown)';
        $sender   = $info['authorizedEntity'] ?? '(unknown)';
        $platform = $info['platform']         ?? '(unknown)';
        $topics   = array_keys($info['rel']['topics'] ?? []);

        $senderMatch = ((string) $sender === (string) $project->sender_id);
        $topicMatch  = in_array($project->topic, $topics, true);

        $lines = [
            "Device token belongs to:",
            "  • App (package): {$appPkg}",
            "  • Project sender id: {$sender}   " . ($senderMatch ? '✅ matches this project' : "❌ does NOT match this project ({$project->sender_id})"),
            "  • Platform: {$platform}",
            "  • Subscribed topics: " . ($topics ? implode(', ', $topics) : '(none)'),
            "",
            "This project sends to topic: {$project->topic}  " . ($topicMatch ? '✅ subscribed' : '❌ NOT in the subscribed list'),
            "",
            "VERDICT: " . (
                !$senderMatch
                    ? "PROJECT MISMATCH — the app is built with a different google-services.json than the project configured here. Re-upload THIS app's google-services.json + service-account JSON in Firebase Projects, or rebuild the app with the matching google-services.json."
                    : (!$topicMatch
                        ? "WRONG/NO SUBSCRIPTION — same project, but the device is not subscribed to '{$project->topic}'. Make sure the app calls subscribeToTopic('{$project->topic}') and is opened once with internet."
                        : "ALL GOOD — same project and subscribed. Topic delivery should work; if it still doesn't show, it's a display/channel issue on the device.")
            ),
        ];

        return back()->withInput()->with('lookup', implode("\n", $lines));
    }

    /** Translate common FCM token errors into a plain-language hint. */
    protected function tokenErrorHint(string $error): string
    {
        $e = strtolower($error);
        if (str_contains($e, 'senderid') || str_contains($e, 'mismatch') || str_contains($e, 'mismatched-credential')) {
            return "  →  The token belongs to a DIFFERENT Firebase project than this one. The app's google-services.json must match the project you uploaded here.";
        }
        if (str_contains($e, 'not a valid fcm') || str_contains($e, 'invalid') || str_contains($e, 'not found') || str_contains($e, 'unregistered')) {
            return '  →  The token is invalid/expired or from another app. Copy a fresh token from the app log.';
        }
        return '';
    }

    /* -----------------------------------------------------------------
     | Settings
     |-----------------------------------------------------------------*/

    public function settings()
    {
        $setting = NotificationSetting::current();
        return view('notifications.settings', compact('setting'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'default_delay_minutes' => 'required|integer|min:0|max:1440',
        ]);

        $setting = NotificationSetting::current();
        $setting->update(['default_delay_minutes' => (int) $request->default_delay_minutes]);

        return redirect()->route('notifications.settings')->with('success', 'Settings saved.');
    }

    /* -----------------------------------------------------------------
     | Helpers
     |-----------------------------------------------------------------*/

    protected function moduleConfig(string $module): array
    {
        if (!isset($this->modules[$module])) {
            abort(404, 'Unknown notification module: ' . $module);
        }
        return $this->modules[$module];
    }

    protected function resolveCategoryName($category): string
    {
        foreach (['category_name', 'name', 'title', 'slider_name'] as $attr) {
            if (!empty($category->$attr)) {
                return (string) $category->$attr;
            }
        }
        return 'Category #' . $category->id;
    }

    protected function defaultDelay(): int
    {
        return (int) NotificationSetting::current()->default_delay_minutes;
    }

    /**
     * Build an associative array from the repeated extra_keys[]/extra_vals[]
     * inputs. Rows with an empty key OR value are skipped so they don't
     * overwrite the dispatcher's defaults (e.g. an empty category_id).
     */
    protected function collectExtraFields(Request $request): array
    {
        $keys = (array) $request->input('extra_keys', []);
        $vals = (array) $request->input('extra_vals', []);

        $out = [];
        foreach ($keys as $i => $k) {
            $k = trim((string) $k);
            $v = isset($vals[$i]) ? trim((string) $vals[$i]) : '';
            if ($k === '' || $v === '') {
                continue;
            }
            $out[$k] = $v;
        }
        return $out;
    }

    protected function cleanImageUrl(Request $request): ?string
    {
        return $request->filled('notification_image_url')
            ? trim($request->input('notification_image_url'))
            : null;
    }

    protected function resolveScheduledAt(Request $request): Carbon
    {
        if ($request->filled('scheduled_at')) {
            $scheduledAt = Carbon::parse($request->scheduled_at);
            return $scheduledAt->isPast() ? Carbon::now()->addSeconds(30) : $scheduledAt;
        }
        return Carbon::now()->addMinutes($this->defaultDelay());
    }

    /**
     * Spawn a detached dispatcher that sleeps until the scheduled time and
     * then fires. This delivers future-scheduled pushes even with no further
     * admin activity. The RunDueNotifications middleware is the safety net if
     * this process is ever lost (machine restart, etc.).
     */
    protected function spawnDispatcher(Carbon $scheduledAt): void
    {
        $delaySeconds = max(1, Carbon::now()->diffInSeconds($scheduledAt, false));
        BackgroundProcess::spawnArtisan('notifications:dispatch-due --delay=' . $delaySeconds);
        // Mark the cache so RunDueNotifications middleware skips spawning a
        // second dispatcher for this same request cycle (prevents double-send).
        Cache::put('notifications_dispatcher_last_run', time(), 600);
    }

    protected function scheduledMessage(Carbon $at): string
    {
        return 'Notification scheduled for ' . $at->format('d M Y, h:i A') . ' (' . $at->diffForHumans() . ').';
    }
}
