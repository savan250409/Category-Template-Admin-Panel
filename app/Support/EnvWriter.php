<?php

namespace App\Support;

/**
 * Minimal, safe .env editor. Updates or inserts keys without disturbing
 * the rest of the file, and can remove keys (exact name or by prefix).
 *
 * Used by the Firebase Projects screen to mirror DB config into .env so
 * everything is generated automatically — no manual config editing.
 */
class EnvWriter
{
    public static function path(): string
    {
        return base_path('.env');
    }

    /** Set (update or append) many KEY => value pairs. */
    public static function setMany(array $pairs): bool
    {
        $path = self::path();
        if (!is_file($path) || !is_writable($path)) {
            return false;
        }

        $lines = self::readLines($path);
        foreach ($pairs as $key => $value) {
            $lines = self::applyKey($lines, (string) $key, $value);
        }
        return self::writeLines($path, $lines);
    }

    /** Remove keys by exact name. */
    public static function remove(array $keys): bool
    {
        $path = self::path();
        if (!is_file($path) || !is_writable($path)) {
            return false;
        }

        $lines = self::readLines($path);
        $lines = array_values(array_filter($lines, function ($line) use ($keys) {
            foreach ($keys as $key) {
                if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/', $line)) {
                    return false;
                }
            }
            return true;
        }));
        return self::writeLines($path, $lines);
    }

    /** Remove every key whose name starts with the given prefix. */
    public static function removeByPrefix(string $prefix): bool
    {
        $path = self::path();
        if (!is_file($path) || !is_writable($path)) {
            return false;
        }

        $lines = self::readLines($path);
        $lines = array_values(array_filter($lines, function ($line) use ($prefix) {
            return !preg_match('/^\s*' . preg_quote($prefix, '/') . '[A-Za-z0-9_]*\s*=/', $line);
        }));
        return self::writeLines($path, $lines);
    }

    /* -----------------------------------------------------------------
     | Internals
     |-----------------------------------------------------------------*/

    protected static function readLines(string $path): array
    {
        return preg_split('/\r\n|\r|\n/', (string) file_get_contents($path));
    }

    protected static function writeLines(string $path, array $lines): bool
    {
        // Collapse trailing blank lines to exactly one terminating newline.
        while (count($lines) > 1 && end($lines) === '') {
            array_pop($lines);
        }
        return file_put_contents($path, implode("\n", $lines) . "\n") !== false;
    }

    protected static function applyKey(array $lines, string $key, $value): array
    {
        $newLine = $key . '=' . self::format($value);
        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/', $line)) {
                $lines[$i] = $newLine;
                return $lines;
            }
        }
        $lines[] = $newLine;
        return $lines;
    }

    protected static function format($value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }
        // Quote when the value contains characters that would break parsing.
        if (preg_match('/\s|#|"|\'|=|\$/', $value)) {
            return '"' . addcslashes($value, "\"\\$") . '"';
        }
        return $value;
    }
}
