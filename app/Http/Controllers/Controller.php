<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Return a filename that does not collide with an existing file in $folder.
     * If $originalName is free, returns it unchanged.
     * Otherwise appends _<unix-timestamp> before the extension, and adds a
     * counter suffix when multiple uploads land in the same second.
     */
    protected function uniqueFilename(string $folder, string $originalName): string
    {
        $sep = DIRECTORY_SEPARATOR;
        if (!file_exists($folder . $sep . $originalName)) {
            return $originalName;
        }
        $info = pathinfo($originalName);
        $base = $info['filename'] ?? $originalName;
        $ext  = isset($info['extension']) ? '.' . $info['extension'] : '';

        $candidate = $base . '_' . time() . $ext;
        $counter = 1;
        while (file_exists($folder . $sep . $candidate)) {
            $candidate = $base . '_' . time() . '_' . $counter . $ext;
            $counter++;
        }
        return $candidate;
    }
}
