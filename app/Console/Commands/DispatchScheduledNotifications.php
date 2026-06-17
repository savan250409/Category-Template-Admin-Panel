<?php

namespace App\Console\Commands;

use App\Models\FirebaseProject;
use App\Models\ScheduledNotification;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Sends every pending notification whose scheduled_at is due. Each push
 * is broadcast to the topic of *every* active Firebase project (this
 * admin panel serves multiple apps), so one row fans out to all apps.
 */
class DispatchScheduledNotifications extends Command
{
    protected $signature = 'notifications:dispatch-due
                            {--limit=50 : Max notifications to process per run}
                            {--delay=0 : Seconds to sleep before dispatching (used by the background spawner)}';

    protected $description = 'Send all pending scheduled notifications whose scheduled_at is now or earlier.';

    public function handle()
    {
        $delay = max(0, (int) $this->option('delay'));
        if ($delay > 0) {
            @set_time_limit(0);
            sleep($delay);
        }

        $limit = (int) $this->option('limit');

        $due = ScheduledNotification::where('status', 'pending')
            ->where('scheduled_at', '<=', Carbon::now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        if ($due->isEmpty()) {
            $this->info('No due notifications.');
            return self::SUCCESS;
        }

        // Active + usable projects, deduplicated by project_id + topic so
        // duplicate project rows pointing at the same app/topic don't cause
        // the same device to receive the push more than once.
        $projects = FirebaseProject::where('is_active', 1)->get()
            ->filter->isUsable()
            ->unique(fn ($p) => $p->project_id . '|' . $p->topic)
            ->values();

        if ($projects->isEmpty()) {
            $this->warn('No usable Firebase projects configured — marking due notifications as failed.');
            foreach ($due as $notif) {
                $notif->update([
                    'status'     => 'failed',
                    'sent_at'    => Carbon::now(),
                    'last_error' => 'No usable Firebase projects configured.',
                ]);
            }
            return self::SUCCESS;
        }

        $this->info("Dispatching {$due->count()} notification(s) across {$projects->count()} app(s)...");

        foreach ($due as $notif) {
            // Atomically claim the row: flip pending → sending only if it's
            // still pending. If another dispatcher already claimed it, the
            // update affects 0 rows and we skip — prevents double sends when
            // the spawned dispatcher and the middleware run at the same time.
            $claimed = ScheduledNotification::where('id', $notif->id)
                ->where('status', 'pending')
                ->update(['status' => 'sending']);

            if (!$claimed) {
                $this->line(" - #{$notif->id} already claimed by another worker — skipping.");
                continue;
            }

            // Honour per-notification project targeting: if target_project_ids
            // is set (global send form), only use those projects; otherwise
            // broadcast to all active projects (existing per-category behaviour).
            $targetIds = is_array($notif->target_project_ids) && count($notif->target_project_ids)
                ? $notif->target_project_ids
                : null;

            $notifProjects = $targetIds
                ? $projects->whereIn('id', $targetIds)->values()
                : $projects;

            $this->dispatchOne($notif, $notifProjects);
        }

        return self::SUCCESS;
    }

    protected function dispatchOne(ScheduledNotification $notif, $projects): void
    {
        $extra = is_array($notif->extra_data) ? $notif->extra_data : [];

        $data = array_merge([
            'category_id'  => (string) ($notif->category_id ?? ''),
            'type'         => (string) ($notif->type ?: 'global'),
            'image_url'    => (string) ($notif->image_url ?? ''),
            'module'       => (string) ($notif->module ?: 'global'),
            'screen'       => (string) ($notif->screen ?? ''),
            'click_action' => (string) ($notif->click_action ?? ''),
        ], $extra);

        $title = $notif->title;
        $body  = $notif->description ?? '';
        $img   = $notif->image_url ?: null;

        $sent   = 0;
        $failed = 0;
        $errors = [];

        foreach ($projects as $project) {
            $topic = $project->topic ?: null;
            if (!$topic) {
                $failed++;
                $errors[] = "{$project->key}: no topic set";
                continue;
            }

            try {
                $fcm  = FcmService::forProject($project);
                $meta = [
                    'scheduled_notification_id' => $notif->id,
                    'module' => ($notif->module ?: 'global') . '_scheduled',
                    'event'  => 'scheduled_send',
                ];
                $result = $fcm->sendToTopic($topic, $title, $body, $data, $img, $meta);

                if (!empty($result['success'])) {
                    $sent++;
                    $this->line(" - #{$notif->id} → {$project->key} (topic={$topic}) OK");
                } else {
                    $failed++;
                    $errors[] = "{$project->key}: " . ($result['error'] ?? 'unknown');
                    $this->line(" - #{$notif->id} → {$project->key} FAILED: " . ($result['error'] ?? 'unknown'));
                }
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "{$project->key}: " . $e->getMessage();
                $this->error(" - #{$notif->id} → {$project->key} EXCEPTION: " . $e->getMessage());
            }
        }

        $notif->update([
            'status'       => $sent > 0 ? 'sent' : 'failed',
            'sent_at'      => Carbon::now(),
            'sent_count'   => $sent,
            'failed_count' => $failed,
            'last_error'   => $errors ? mb_substr(implode(' | ', $errors), 0, 1000) : null,
        ]);
    }
}
