<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetUploadLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Set PHP upload limits
        ini_set('max_file_uploads', config('upload.max_file_uploads', 1000));
        ini_set('max_input_vars', config('upload.max_input_vars', 10000));
        ini_set('upload_max_filesize', config('upload.upload_max_filesize', '100M'));
        ini_set('post_max_size', config('upload.post_max_size', '100M'));
        ini_set('memory_limit', config('upload.memory_limit', '512M'));
        ini_set('max_execution_time', config('upload.max_execution_time', 300));
        ini_set('max_input_time', config('upload.max_input_time', 300));

        return $next($request);
    }
}
