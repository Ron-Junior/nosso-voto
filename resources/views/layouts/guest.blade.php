@php
    $pageTitle = $title ?? 'Nosso Voto — Planejamento de Casamentos';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#C25E38">
    <title>{{ $pageTitle }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-[#FBF9F7] text-[#2C2523] font-sans antialiased selection:bg-[#F9EBE6] selection:text-[#C25E38]">
    {{-- Clean Guest Header --}}
    <header class="border-b border-[#EAE0DA] bg-[#FFFFFF]/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#C25E38] text-white shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </span>
                <div>
                    <span class="block font-serif text-lg font-semibold tracking-tight text-[#2C2523]">Nosso Voto</span>
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('home') }}"
                   class="hidden sm:inline-flex items-center rounded-xl px-3 py-2 min-h-[44px] text-sm font-medium text-[#6E625F] hover:text-[#C25E38] transition-colors">
                    Voltar para a página inicial
                </a>

                @if (request()->routeIs('login'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                        Criar Conta
                    </a>
                @elseif (request()->routeIs('register'))
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                        Entrar
                    </a>
                @endif
            </div>
        </div>
    </header>

    {{-- Centered Auth Content --}}
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl">
            <x-toast :message="session('status')" />

            {{ $slot }}
        </div>
    </main>

    {{-- Discreet Footer --}}
    <footer class="border-t border-[#EAE0DA] bg-[#FFFFFF]/70 py-5">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#6E625F]">
            <span>Nosso Voto — Planejamento de casamentos leve e gratuito para noivos</span>
            <a href="{{ route('home') }}" class="font-medium text-[#C25E38] hover:text-[#A84D2B]">
                Página inicial
            </a>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

