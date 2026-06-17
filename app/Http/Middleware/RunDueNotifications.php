<?php

namespace App\Http\Middleware;

use App\Models\ScheduledNotification;
use App\Support\BackgroundProcess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Fallback dispatcher: after any admin web response is sent, check whether
 * any pending notifications are overdue and trigger a background dispatch.
 *
 * Throttled to once per 60s via cache, so it's essentially free even on a
 * busy panel. Combined with BackgroundProcess::spawnArtisan() in the send
 * forms, this guarantees notifications fire even if the originally-spawned
 * process was killed.
 */
class RunDueNotifications
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        try {
            $lastRun = (int) Cache::get('notifications_dispatcher_last_run', 0);
            if ($lastRun > 0 && (time() - $lastRun) < 60) {
                return;
            }

            $hasDue = ScheduledNotification::where('status', 'pending')
                ->where('scheduled_at', '<=', Carbon::now())
                ->exists();

            if (!$hasDue) {
                return;
            }

            Cache::put('notifications_dispatcher_last_run', time(), 600);
            BackgroundProcess::spawnArtisan('notifications:dispatch-due');
        } catch (\Throwable $e) {
            Log::warning('RunDueNotifications middleware failed: ' . $e->getMessage());
        }
    }
}
