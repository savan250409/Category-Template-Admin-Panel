<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Fire-and-forget background process helper that works on both Windows
 * (XAMPP) and Linux. Used to dispatch scheduled notifications at their
 * exact time without a permanently-running scheduler or cron job.
 *
 * The caller spawns a detached `php artisan ...` process which the HTTP
 * request never waits for.
 */
class BackgroundProcess
{
    /**
     * Spawn `php artisan <command>` as a detached background process.
     *
     * @param string $command Artisan command + args (e.g. "notifications:dispatch-due --delay=120")
     * @return bool true if spawn was attempted, false on failure
     */
    public static function spawnArtisan(string $command): bool
    {
        $php     = self::phpBinary();
        $artisan = base_path('artisan');

        if (!is_file($artisan)) {
            Log::warning('BackgroundProcess: artisan not found at ' . $artisan);
            return false;
        }

        try {
            if (self::isWindows()) {
                self::spawnWindows($php, $artisan, $command);
            } else {
                self::spawnUnix($php, $artisan, $command);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('BackgroundProcess spawn failed: ' . $e->getMessage());
            return false;
        }
    }

    protected static function spawnWindows(string $php, string $artisan, string $command): void
    {
        // `start /B` runs the new process in the same console without a window
        // and returns immediately. We then pclose to fully detach.
        $line = sprintf('start /B "" "%s" "%s" %s', $php, $artisan, $command);

        // Wrap with cmd /C so START works; redirect output to NUL.
        $full = 'cmd /C "' . $line . ' > NUL 2>&1"';

        $handle = popen($full, 'r');
        if ($handle !== false) {
            pclose($handle);
        }
    }

    protected static function spawnUnix(string $php, string $artisan, string $command): void
    {
        // nohup + & fully detaches; redirect stdout/stderr so the parent
        // isn't kept alive by I/O.
        $line = sprintf(
            'nohup %s %s %s > /dev/null 2>&1 &',
            escapeshellarg($php),
            escapeshellarg($artisan),
            $command
        );

        @shell_exec($line);
    }

    protected static function phpBinary(): string
    {
        if (defined('PHP_BINARY') && PHP_BINARY) {
            return PHP_BINARY;
        }
        return 'php';
    }

    protected static function isWindows(): bool
    {
        return strncasecmp(PHP_OS, 'WIN', 3) === 0;
    }
}
