<?php

use App\Support\RuntimeConfigCache;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

$runtimeConfigCachePath = RuntimeConfigCache::prepare(dirname(__DIR__));

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

if ($runtimeConfigCachePath !== null) {
    $app->terminating(static function () use ($app, $runtimeConfigCachePath): void {
        RuntimeConfigCache::persist($app, $runtimeConfigCachePath);
    });
}

$app->handleRequest(Request::capture());
