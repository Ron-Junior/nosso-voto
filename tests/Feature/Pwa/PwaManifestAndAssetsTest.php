<?php

namespace Tests\Feature\Pwa;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaManifestAndAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_manifest_declares_valid_pwa_icons_and_share_target(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('/', $manifest['id'] ?? null);
        $this->assertSame('/', $manifest['scope'] ?? null);
        $this->assertSame('/', $manifest['start_url'] ?? null);
        $this->assertSame('standalone', $manifest['display'] ?? null);
        $this->assertSame('#FBF9F7', $manifest['background_color'] ?? null);
        $this->assertSame('#C25E38', $manifest['theme_color'] ?? null);
        $this->assertSame('/compartilhar-pin', $manifest['share_target']['action'] ?? null);

        $icons = $manifest['icons'] ?? [];
        $this->assertCount(4, $icons);

        $expectedPurposes = [
            '192x192|any' => false,
            '512x512|any' => false,
            '192x192|maskable' => false,
            '512x512|maskable' => false,
        ];

        foreach ($icons as $icon) {
            $this->assertSame('image/png', $icon['type'] ?? null);
            $relativeSrc = ltrim((string) ($icon['src'] ?? ''), '/');
            $fullPath = public_path($relativeSrc);

            $this->assertFileExists($fullPath);

            [$expectedW, $expectedH] = array_map('intval', explode('x', (string) ($icon['sizes'] ?? '0x0')));
            $imageInfo = getimagesize($fullPath);

            $this->assertIsArray($imageInfo);
            $this->assertSame($expectedW, $imageInfo[0]);
            $this->assertSame($expectedH, $imageInfo[1]);
            $this->assertSame(IMAGETYPE_PNG, $imageInfo[2]);

            $key = ($icon['sizes'] ?? '').'|'.($icon['purpose'] ?? '');
            if (array_key_exists($key, $expectedPurposes)) {
                $expectedPurposes[$key] = true;
            }
        }

        foreach ($expectedPurposes as $key => $found) {
            $this->assertTrue($found, "Missing manifest icon declaration for {$key}");
        }
    }

    public function test_favicon_apple_touch_icon_splash_screens_and_service_worker_exist(): void
    {
        $icoPath = public_path('favicon.ico');
        $this->assertFileExists($icoPath);
        $icoBytes = (string) file_get_contents($icoPath);
        $this->assertGreaterThan(0, strlen($icoBytes));
        $this->assertStringStartsWith("\x00\x00\x01\x00", $icoBytes);

        $svgPath = public_path('favicon.svg');
        $this->assertFileExists($svgPath);
        $svgContent = (string) file_get_contents($svgPath);
        $this->assertStringContainsString('#C25E38', $svgContent);
        $this->assertStringContainsString('#FFFFFF', $svgContent);

        $pngExpectations = [
            'favicon-16x16.png' => [16, 16],
            'favicon-32x32.png' => [32, 32],
            'apple-touch-icon.png' => [180, 180],
        ];

        foreach ($pngExpectations as $relativePath => [$expectedW, $expectedH]) {
            $fullPath = public_path($relativePath);
            $this->assertFileExists($fullPath);
            $imageInfo = getimagesize($fullPath);
            $this->assertIsArray($imageInfo);
            $this->assertSame($expectedW, $imageInfo[0]);
            $this->assertSame($expectedH, $imageInfo[1]);
            $this->assertSame(IMAGETYPE_PNG, $imageInfo[2]);
        }

        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);
        $swContent = (string) file_get_contents($swPath);
        $this->assertStringContainsString("addEventListener('install'", $swContent);
        $this->assertStringContainsString("addEventListener('activate'", $swContent);
        $this->assertStringContainsString("addEventListener('fetch'", $swContent);

        $partialPath = resource_path('views/partials/pwa-head.blade.php');
        $this->assertFileExists($partialPath);
        $partialContent = (string) file_get_contents($partialPath);

        preg_match_all('/href="\/splash\/(apple-splash-(\d+)-(\d+)\.png)"/', $partialContent, $matches, PREG_SET_ORDER);
        $this->assertCount(15, $matches);

        foreach ($matches as $match) {
            $splashFile = public_path('splash/'.$match[1]);
            $expectedW = (int) $match[2];
            $expectedH = (int) $match[3];

            $this->assertFileExists($splashFile);
            $imageInfo = getimagesize($splashFile);
            $this->assertIsArray($imageInfo);
            $this->assertSame($expectedW, $imageInfo[0]);
            $this->assertSame($expectedH, $imageInfo[1]);
            $this->assertSame(IMAGETYPE_PNG, $imageInfo[2]);
        }
    }

    public function test_public_guest_and_authenticated_layouts_render_pwa_head_and_sw_registration(): void
    {
        $couple = User::factory()->couple()->create();

        $responses = [
            'landing' => $this->get('/'),
            'login' => $this->get('/login'),
            'dashboard' => $this->actingAs($couple)->get('/painel'),
        ];

        foreach ($responses as $label => $response) {
            $response->assertOk();
            $content = $response->getContent();

            $this->assertSame(1, substr_count($content, '<meta name="theme-color"'), "Duplicate theme-color in {$label}");
            $this->assertSame(1, substr_count($content, '<link rel="manifest"'), "Duplicate manifest link in {$label}");

            $response->assertSee('/manifest.json', false)
                ->assertSee('/favicon.ico', false)
                ->assertSee('/favicon.svg', false)
                ->assertSee('/apple-touch-icon.png', false)
                ->assertSee('apple-touch-startup-image', false)
                ->assertSee('apple-mobile-web-app-capable', false)
                ->assertSee("navigator.serviceWorker.register('/sw.js'", false);
        }
    }
}

