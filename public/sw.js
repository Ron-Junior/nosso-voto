const CACHE_PREFIX = 'nosso-voto-pwa-';
const CACHE_NAME = `${CACHE_PREFIX}v1`;

const PRECACHE_ASSETS = [
    '/manifest.json',
    '/favicon.svg',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
];

const OFFLINE_HTML = `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#C25E38">
    <title>Nosso Voto — Sem conexão</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #FBF9F7;
            color: #2C2523;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        .card {
            max-width: 24rem;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #EAE0DA;
            border-radius: 1.25rem;
            padding: 2rem 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(44, 37, 35, 0.06);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            background-color: #C25E38;
            color: #FFFFFF;
            margin-bottom: 1rem;
        }
        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #2C2523;
        }
        p {
            margin: 0 0 1.5rem;
            font-size: 0.9375rem;
            line-height: 1.5;
            color: #6E625F;
        }
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 0.75rem;
            background-color: #C25E38;
            color: #FFFFFF;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="badge" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="#FFFFFF">
                <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
        </div>
        <h1>Você está sem conexão</h1>
        <p>Não foi possível conectar ao Nosso Voto no momento. Verifique sua internet e tente novamente.</p>
        <button type="button" onclick="window.location.reload()">Tentar novamente</button>
    </main>
</body>
</html>`;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith(CACHE_PREFIX) && key !== CACHE_NAME)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

function isStaticPwaAsset(pathname) {
    return (
        pathname.startsWith('/icons/') ||
        pathname.startsWith('/splash/') ||
        pathname === '/manifest.json' ||
        pathname === '/apple-touch-icon.png' ||
        pathname === '/favicon.ico' ||
        pathname === '/favicon.svg' ||
        pathname === '/favicon-16x16.png' ||
        pathname === '/favicon-32x32.png'
    );
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.startsWith('/livewire/') || url.pathname.startsWith('/api/')) {
        return;
    }

    if (isStaticPwaAsset(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.ok) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }

                    return networkResponse;
                });
            }),
        );

        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(
                () =>
                    new Response(OFFLINE_HTML, {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: {
                            'Content-Type': 'text/html; charset=utf-8',
                        },
                    }),
            ),
        );
    }
});

