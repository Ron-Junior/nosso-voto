<?php

namespace App\Support;

use Illuminate\Foundation\Application;
use Throwable;

class RuntimeConfigCache
{
    /**
     * Prepare a process-safe runtime config cache path for production requests
     * when no build-time config cache exists.
     */
    public static function prepare(string $basePath, ?string $cacheDir = null, bool $force = false): ?string
    {
        if (! $force) {
            if (PHP_SAPI === 'cli') {
                return null;
            }

            $appEnv = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? getenv('APP_ENV');

            if ($appEnv !== 'production') {
                return null;
            }

            if (is_file($basePath.'/bootstrap/cache/config.php')) {
                return null;
            }

            $existingCachePath = $_SERVER['APP_CONFIG_CACHE'] ?? $_ENV['APP_CONFIG_CACHE'] ?? getenv('APP_CONFIG_CACHE');

            if (is_string($existingCachePath) && $existingCachePath !== '') {
                return null;
            }
        }

        $dir = ($cacheDir !== null && $cacheDir !== '') ? $cacheDir : sys_get_temp_dir();

        if (! is_dir($dir) || ! is_writable($dir)) {
            return null;
        }

        $envValues = [];

        foreach ([getenv() ?: [], $_ENV, $_SERVER] as $source) {
            if (! is_array($source)) {
                continue;
            }

            foreach ($source as $key => $value) {
                if (! is_string($key) || $key === 'APP_CONFIG_CACHE') {
                    continue;
                }

                if (! preg_match('/^(APP|DB|SESSION|CACHE|LOG|MAIL|QUEUE|LIVEWIRE|BROADCAST|FILESYSTEM|REDIS|MEMCACHED|AWS|BCRYPT|VITE)_/', $key)) {
                    continue;
                }

                if (is_scalar($value)) {
                    $envValues[$key] = (string) $value;
                }
            }
        }

        ksort($envValues);

        $fingerprint = substr(hash('sha256', json_encode([
            'env' => $envValues,
            'mtimes' => [
                @filemtime($basePath.'/config/app.php') ?: 0,
                @filemtime($basePath.'/config/database.php') ?: 0,
            ],
        ], JSON_THROW_ON_ERROR)), 0, 16);

        $cachePath = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'nossovoto-config-'.$fingerprint.'.php';

        if (is_file($cachePath)) {
            $_ENV['APP_CONFIG_CACHE'] = $cachePath;
            $_SERVER['APP_CONFIG_CACHE'] = $cachePath;
            putenv("APP_CONFIG_CACHE={$cachePath}");
        }

        return $cachePath;
    }

    /**
     * Persist the booted configuration array atomically after handling a request.
     */
    public static function persist(Application $app, string $cachePath): void
    {
        if (is_file($cachePath) || $app->configurationIsCached()) {
            return;
        }

        $tmpPath = null;

        try {
            $config = $app->make('config')->all();
            $exported = '<?php return '.var_export($config, true).';'.PHP_EOL;
            $tmpPath = $cachePath.'.tmp.'.getmypid().'.'.bin2hex(random_bytes(4));

            if (@file_put_contents($tmpPath, $exported) === false) {
                @unlink($tmpPath);

                return;
            }

            $loaded = (static fn (string $path): mixed => require $path)($tmpPath);

            if (! is_array($loaded)) {
                @unlink($tmpPath);

                return;
            }

            if (! @rename($tmpPath, $cachePath)) {
                @unlink($tmpPath);
            }
        } catch (Throwable) {
            if (is_string($tmpPath) && is_file($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}
