<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('ADMIN_LOGIN')) {
            return redirect('/')->with('error', 'Access Denied! Please login first.');
        }

        return $next($request);
    }
}
