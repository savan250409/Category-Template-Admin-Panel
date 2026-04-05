<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    /**
     * Clear all application cache and logs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            // Clear Laravel Cache, Config, Route, and View
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            // Clear all log files in storage/logs/
            $logPath = storage_path('logs');
            $files = File::glob($logPath . '/*.log');
            
            foreach ($files as $file) {
                File::put($file, ''); // Truncate the file content
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache and logs cleared successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
