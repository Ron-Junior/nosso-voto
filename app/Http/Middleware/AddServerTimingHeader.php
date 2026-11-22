<?php

namespace App\Http\Middleware;

use App\Support\RequestTiming;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddServerTimingHeader
{
    /**
     * Append lightweight Server-Timing metrics to help diagnose TTFB stages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timing = app(RequestTiming::class);
        $timing->reset();

        $middlewareStart = microtime(true);
        $requestStart = defined('LARAVEL_START') ? (float) LARAVEL_START : $middlewareStart;

        $response = $next($request);

        $now = microtime(true);
        $bootMs = max(0.0, ($middlewareStart - $requestStart) * 1000);
        $totalMs = max(0.0, ($now - $requestStart) * 1000);
        $dbMs = $timing->queryTimeMs();
        $queries = $timing->queryCount();

        $cfg = app()->configurationIsCached() ? 1 : 0;
        $opcacheStatus = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
        $opc = is_array($opcacheStatus) && ($opcacheStatus['opcache_enabled'] ?? false) ? 1 : 0;
        $ses = preg_replace('/[^a-z0-9_-]/i', '', (string) config('session.driver', 'unknown')) ?: 'unknown';

        $response->headers->set(
            'Server-Timing',
            sprintf(
                'boot;dur=%.1f, db;dur=%.1f;desc="%dq", app;dur=%.1f, runtime;desc="cfg:%d opc:%d ses:%s"',
                $bootMs,
                $dbMs,
                $queries,
                $totalMs,
                $cfg,
                $opc,
                $ses,
            ),
        );

        return $response;
    }
}
