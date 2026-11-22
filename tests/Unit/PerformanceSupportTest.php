<?php

namespace Tests\Unit;

use App\Support\BrazilLocations;
use App\Support\RequestTiming;
use App\Support\RuntimeConfigCache;
use Collator;
use Tests\TestCase;

class PerformanceSupportTest extends TestCase
{
    public function test_brazil_locations_all_cities_by_state_is_pre_sorted_for_pt_br(): void
    {
        $collator = class_exists(Collator::class) ? Collator::create('pt_BR') : null;
        $citiesByState = BrazilLocations::allCitiesByState();

        $this->assertCount(27, $citiesByState);

        foreach ($citiesByState as $uf => $cities) {
            $expected = $cities;

            if ($collator !== null) {
                $collator->sort($expected);
            } else {
                sort($expected, SORT_LOCALE_STRING | SORT_FLAG_CASE);
            }

            $this->assertSame(array_values($expected), $cities, "State {$uf} cities must stay sorted in pt_BR order.");
        }
    }

    public function test_runtime_config_cache_prepares_persists_and_reuses_config_file(): void
    {
        $tempDir = sys_get_temp_dir().'/nv-cfg-test-'.bin2hex(random_bytes(4));
        mkdir($tempDir, 0775, true);

        $hadServer = array_key_exists('APP_CONFIG_CACHE', $_SERVER);
        $hadEnv = array_key_exists('APP_CONFIG_CACHE', $_ENV);
        $prevServer = $_SERVER['APP_CONFIG_CACHE'] ?? null;
        $prevEnv = $_ENV['APP_CONFIG_CACHE'] ?? null;
        $prevGetEnv = getenv('APP_CONFIG_CACHE');

        try {
            $cachePath = RuntimeConfigCache::prepare(base_path(), $tempDir, force: true);

            $this->assertNotNull($cachePath);
            $this->assertFileDoesNotExist($cachePath);

            RuntimeConfigCache::persist($this->app, $cachePath);

            $this->assertFileExists($cachePath);
            $loaded = require $cachePath;
            $this->assertIsArray($loaded);
            $this->assertSame(config('app.name'), $loaded['app']['name']);

            $reusedPath = RuntimeConfigCache::prepare(base_path(), $tempDir, force: true);

            $this->assertSame($cachePath, $reusedPath);
            $this->assertSame($cachePath, getenv('APP_CONFIG_CACHE'));
            $this->assertSame($cachePath, $_SERVER['APP_CONFIG_CACHE'] ?? null);
            $this->assertSame($cachePath, $_ENV['APP_CONFIG_CACHE'] ?? null);
        } finally {
            if ($hadServer) {
                $_SERVER['APP_CONFIG_CACHE'] = $prevServer;
            } else {
                unset($_SERVER['APP_CONFIG_CACHE']);
            }

            if ($hadEnv) {
                $_ENV['APP_CONFIG_CACHE'] = $prevEnv;
            } else {
                unset($_ENV['APP_CONFIG_CACHE']);
            }

            if ($prevGetEnv !== false) {
                putenv("APP_CONFIG_CACHE={$prevGetEnv}");
            } else {
                putenv('APP_CONFIG_CACHE');
            }

            foreach (glob($tempDir.'/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($tempDir);
        }
    }

    public function test_request_timing_is_scoped_and_resets_when_scoped_instances_are_forgotten(): void
    {
        $first = app(RequestTiming::class);
        $first->recordQuery(12.5);

        $this->assertSame(1, app(RequestTiming::class)->queryCount());
        $this->assertSame(12.5, app(RequestTiming::class)->queryTimeMs());

        $this->app->forgetScopedInstances();

        $next = app(RequestTiming::class);

        $this->assertNotSame($first, $next);
        $this->assertSame(0, $next->queryCount());
        $this->assertSame(0.0, $next->queryTimeMs());
    }
}
