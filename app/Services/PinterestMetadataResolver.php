<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PinterestMetadataResolver
{
    private const MAX_REDIRECTS = 5;

    /**
     * Resolve cover image URL, title, and canonical URL from a Pinterest link.
     *
     * @return array{image_url: ?string, title: ?string, canonical_url: ?string}
     */
    public function resolve(?string $url): array
    {
        $empty = [
            'image_url' => null,
            'title' => null,
            'canonical_url' => null,
        ];

        $cleanUrl = trim((string) $url);

        if ($cleanUrl === '' || filter_var($cleanUrl, FILTER_VALIDATE_URL) === false) {
            return $empty;
        }

        $parsed = parse_url($cleanUrl);
        $scheme = Str::lower((string) ($parsed['scheme'] ?? ''));
        $host = Str::lower((string) ($parsed['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return $empty;
        }

        if ($host === 'i.pinimg.com') {
            $image = $this->sanitizeImageCandidate($cleanUrl);

            return [
                'image_url' => $image,
                'title' => null,
                'canonical_url' => $image !== null ? $cleanUrl : null,
            ];
        }

        if (! $this->isAllowedPinterestHost($host)) {
            return $empty;
        }

        if (! (bool) config('services.pinterest.fetch_metadata', true)) {
            return $empty;
        }

        $timeout = max(1, (int) config('services.pinterest.timeout', 4));
        $canonicalUrl = $cleanUrl;
        $html = null;

        try {
            $currentUrl = $cleanUrl;

            for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
                $response = Http::withoutRedirecting()
                    ->connectTimeout(min(2, $timeout))
                    ->timeout($timeout)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8',
                    ])
                    ->get($currentUrl);

                $status = $response->status();

                if ($status >= 300 && $status < 400) {
                    $location = trim((string) $response->header('Location'));

                    if ($location === '') {
                        break;
                    }

                    $nextUrl = $this->resolveRedirectUrl($currentUrl, $location);

                    if ($nextUrl === null) {
                        return $empty;
                    }

                    $nextHost = Str::lower((string) parse_url($nextUrl, PHP_URL_HOST));

                    if ($nextHost === 'i.pinimg.com') {
                        $image = $this->sanitizeImageCandidate($nextUrl);

                        return [
                            'image_url' => $image,
                            'title' => null,
                            'canonical_url' => $image !== null ? $nextUrl : null,
                        ];
                    }

                    if (! $this->isAllowedPinterestHost($nextHost)) {
                        return $empty;
                    }

                    $currentUrl = $nextUrl;
                    $canonicalUrl = $nextUrl;

                    continue;
                }

                if ($response->successful()) {
                    $html = (string) $response->body();
                }

                break;
            }
        } catch (Throwable) {
            return $empty;
        }

        $imageUrl = null;
        $title = null;

        if (filled($html)) {
            [$imageUrl, $title] = $this->extractMetadataFromHtml($html);
        }

        if ($imageUrl === null) {
            [$oembedImage, $oembedTitle] = $this->fetchFromOembed($canonicalUrl, $timeout);
            $imageUrl = $oembedImage;
            $title ??= $oembedTitle;
        }

        return [
            'image_url' => $imageUrl,
            'title' => $title,
            'canonical_url' => $canonicalUrl,
        ];
    }

    private function isAllowedPinterestHost(string $host): bool
    {
        $normalized = Str::lower(trim($host));

        if ($normalized === '' || $normalized === 's.pinimg.com') {
            return false;
        }

        if ($normalized === 'pin.it' || $normalized === 'i.pinimg.com') {
            return true;
        }

        return preg_match(
            '/^(?:[a-z0-9-]+\.)*pinterest\.(?:com(?:\.[a-z]{2})?|co\.[a-z]{2}|[a-z]{2,3})$/',
            $normalized,
        ) === 1;
    }

    private function resolveRedirectUrl(string $baseUrl, string $location): ?string
    {
        if (preg_match('#^https?://#i', $location) === 1) {
            return filter_var($location, FILTER_VALIDATE_URL) !== false ? $location : null;
        }

        $base = parse_url($baseUrl);

        if (! is_array($base) || empty($base['scheme']) || empty($base['host'])) {
            return null;
        }

        $port = isset($base['port']) ? ':'.$base['port'] : '';

        if (str_starts_with($location, '//')) {
            $candidate = "{$base['scheme']}:{$location}";

            return filter_var($candidate, FILTER_VALIDATE_URL) !== false ? $candidate : null;
        }

        if (str_starts_with($location, '/')) {
            $candidate = "{$base['scheme']}://{$base['host']}{$port}{$location}";

            return filter_var($candidate, FILTER_VALIDATE_URL) !== false ? $candidate : null;
        }

        $basePath = (string) ($base['path'] ?? '/');
        $dir = rtrim(dirname($basePath), '/');
        $candidate = "{$base['scheme']}://{$base['host']}{$port}{$dir}/{$location}";

        return filter_var($candidate, FILTER_VALIDATE_URL) !== false ? $candidate : null;
    }

    /**
     * Extract image URL and title from Pinterest HTML via meta tags, JSON-LD, and hydrated pinimg URLs.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function extractMetadataFromHtml(string $html): array
    {
        $metaValues = $this->parseMetaTags($html);

        $imageUrl = null;
        foreach (['og:image:secure_url', 'og:image', 'twitter:image:src', 'twitter:image'] as $key) {
            if (isset($metaValues[$key])) {
                $candidate = $this->sanitizeImageCandidate($metaValues[$key]);
                if ($candidate !== null) {
                    $imageUrl = $candidate;
                    break;
                }
            }
        }

        if ($imageUrl === null) {
            $imageUrl = $this->extractImageSrcLink($html);
        }

        if ($imageUrl === null) {
            $imageUrl = $this->extractJsonLdImage($html);
        }

        if ($imageUrl === null) {
            $imageUrl = $this->extractHydratedPinimgUrl($html);
        }

        $rawTitle = $metaValues['og:title'] ?? $metaValues['twitter:title'] ?? $this->extractHtmlTitle($html);
        $title = $this->cleanTitle($rawTitle);

        return [$imageUrl, $title];
    }

    /**
     * Parse <meta> tags regardless of attribute order or quote style.
     *
     * @return array<string, string>
     */
    private function parseMetaTags(string $html): array
    {
        $values = [];

        if (preg_match_all('/<meta\b[^>]*>/i', $html, $tags) < 1) {
            return $values;
        }

        foreach ($tags[0] as $tag) {
            if (preg_match_all('/([a-zA-Z_:.-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/', $tag, $attrs, PREG_SET_ORDER) < 1) {
                continue;
            }

            $map = [];
            foreach ($attrs as $attr) {
                $attrName = Str::lower($attr[1]);
                $attrValue = $attr[2] !== '' ? $attr[2] : ($attr[3] !== '' ? $attr[3] : ($attr[4] ?? ''));
                $map[$attrName] = html_entity_decode($attrValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $key = Str::lower($map['property'] ?? $map['name'] ?? $map['itemprop'] ?? '');
            $content = trim((string) ($map['content'] ?? ''));

            if ($key !== '' && $content !== '' && ! isset($values[$key])) {
                $values[$key] = $content;
            }
        }

        return $values;
    }

    private function extractImageSrcLink(string $html): ?string
    {
        if (preg_match_all('/<link\b[^>]*>/i', $html, $links) < 1) {
            return null;
        }

        foreach ($links[0] as $tag) {
            if (preg_match_all('/([a-zA-Z_:.-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/', $tag, $attrs, PREG_SET_ORDER) < 1) {
                continue;
            }

            $map = [];
            foreach ($attrs as $attr) {
                $attrName = Str::lower($attr[1]);
                $attrValue = $attr[2] !== '' ? $attr[2] : ($attr[3] !== '' ? $attr[3] : ($attr[4] ?? ''));
                $map[$attrName] = html_entity_decode($attrValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            if (Str::lower((string) ($map['rel'] ?? '')) === 'image_src' && filled($map['href'] ?? null)) {
                $candidate = $this->sanitizeImageCandidate($map['href']);
                if ($candidate !== null) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function extractJsonLdImage(string $html): ?string
    {
        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $scripts) < 1) {
            return null;
        }

        foreach ($scripts[1] as $jsonRaw) {
            $decoded = json_decode(trim($jsonRaw), true);

            if (! is_array($decoded)) {
                continue;
            }

            $image = $decoded['image'] ?? null;

            if (is_string($image)) {
                $candidate = $this->sanitizeImageCandidate($image);
                if ($candidate !== null) {
                    return $candidate;
                }
            }

            if (is_array($image)) {
                $urls = isset($image['url']) ? [$image['url']] : $image;

                foreach ($urls as $item) {
                    $raw = is_array($item) ? ($item['url'] ?? null) : $item;
                    if (is_string($raw)) {
                        $candidate = $this->sanitizeImageCandidate($raw);
                        if ($candidate !== null) {
                            return $candidate;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function extractHydratedPinimgUrl(string $html): ?string
    {
        $normalized = str_replace(['\\u002F', '\\u002f', '\\/'], '/', $html);

        if (preg_match_all('/https?:\/\/i\.pinimg\.com\/(?:originals|1200x|736x|564x|474x)\/[^\s"\'<>\\\\]+\.(?:jpe?g|png|webp)/i', $normalized, $matches) < 1) {
            return null;
        }

        foreach ($matches[0] as $match) {
            $candidate = $this->sanitizeImageCandidate($match);
            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractHtmlTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $match) === 1) {
            return html_entity_decode(trim($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    /**
     * Fall back to Pinterest's official oEmbed endpoint when HTML lacks OpenGraph metadata.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function fetchFromOembed(string $canonicalUrl, int $timeout): array
    {
        try {
            $targetUrl = $this->normalizeUrlForOembed($canonicalUrl);

            $response = Http::connectTimeout(min(2, $timeout))
                ->timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'NossoVotoBot/1.0',
                ])
                ->get('https://www.pinterest.com/oembed.json', [
                    'url' => $targetUrl,
                ]);

            if (! $response->successful()) {
                return [null, null];
            }

            $thumbnail = $this->sanitizeImageCandidate($response->json('thumbnail_url'));
            $title = $this->cleanTitle($response->json('title'));

            return [$thumbnail, $title];
        } catch (Throwable) {
            return [null, null];
        }
    }

    private function normalizeUrlForOembed(string $url): string
    {
        if (preg_match('#/pin/(\d+)#i', $url, $match) === 1) {
            return "https://www.pinterest.com/pin/{$match[1]}/";
        }

        return $url;
    }

    private function sanitizeImageCandidate(mixed $raw): ?string
    {
        if (! is_string($raw)) {
            return null;
        }

        $decoded = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($decoded === '' || filter_var($decoded, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parsed = parse_url($decoded);
        $scheme = Str::lower((string) ($parsed['scheme'] ?? ''));
        $host = Str::lower((string) ($parsed['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }

        if ($this->isRejectedImageUrl($decoded, $host)) {
            return null;
        }

        return $decoded;
    }

    private function isRejectedImageUrl(string $url, string $host): bool
    {
        if ($host === 's.pinimg.com') {
            return true;
        }

        $lower = Str::lower($url);

        foreach ([
            'pinterest_logo',
            'default_open_graph',
            'og_default',
            '/favicon',
            '/75x75_rs/',
            '/30x30_rs/',
            '/140x140_rs/',
        ] as $blockedFragment) {
            if (str_contains($lower, $blockedFragment)) {
                return true;
            }
        }

        return false;
    }

    private function cleanTitle(mixed $raw): ?string
    {
        if (! is_string($raw)) {
            return null;
        }

        $decoded = html_entity_decode(trim($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleaned = trim((string) preg_replace('/\s*[\|\-–—]\s*Pinterest.*$/iu', '', $decoded));

        if ($cleaned === '' || Str::lower($cleaned) === 'pinterest') {
            return null;
        }

        return Str::limit($cleaned, 255, '');
    }
}

