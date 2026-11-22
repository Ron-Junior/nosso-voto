@php
    $user = auth()->user();
    $pageTitle = $title ?? 'Nosso Voto — Planejamento de Casamentos';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
@include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen bg-[#FBF9F7] text-[#2C2523] font-sans antialiased selection:bg-[#F9EBE6] selection:text-[#C25E38]">
    <div class="min-h-screen flex flex-col lg:flex-row">
        @if ($user)
            <aside class="hidden lg:flex lg:w-72 lg:flex-col lg:fixed lg:inset-y-0 bg-[#FFFFFF] border-r border-[#EAE0DA] z-30">
                <div class="flex flex-col h-full px-6 py-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-8 group">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#C25E38] text-white shadow-sm group-hover:bg-[#A84D2B] transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                        </span>
                        <div>
                            <span class="block font-serif text-xl font-semibold tracking-tight text-[#2C2523]">Nosso Voto</span>
                            <span class="block text-xs text-[#6E625F]">Gestão de Casamentos</span>
                        </div>
                    </a>

                    <div class="mb-6 rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                            {{ $user->roleLabel() }}
                        </p>
                        <p class="mt-1.5 truncate text-sm font-semibold text-[#2C2523]">{{ $user->name }}</p>
                        <p class="truncate text-xs text-[#6E625F]">{{ $user->email }}</p>
                    </div>

                    <nav class="flex-1 space-y-1.5" aria-label="Menu principal">
                        <a href="{{ route('home') }}"
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span>Início</span>
                        </a>

                        <a href="{{ route('showcase.index') }}"
                           wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('showcase.*') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                            </svg>
                            <span>Vitrine Regional</span>
                        </a>

                        @if ($user->isCouple() || $user->isAdmin())
                            <a href="{{ route('dashboard') }}"
                               wire:navigate
                               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                                <span>Painel dos Noivos</span>
                            </a>

                            <a href="{{ route('inspirations.index') }}"
                               wire:navigate
                               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('inspirations.*') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                                </svg>
                                <span>Inspirações Pinterest</span>
                            </a>
                        @endif

                        @if ($user->isVendor() || $user->isAdmin())
                            <a href="{{ route('vendor.dashboard') }}"
                               wire:navigate
                               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('vendor.dashboard') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                                </svg>
                                <span>Área do Fornecedor</span>
                            </a>

                            <a href="{{ route('vendor.schedule') }}"
                               wire:navigate
                               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('vendor.schedule') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <span>Agenda de Noivos</span>
                            </a>
                        @endif

                        @if ($user->isAdmin())
                            <a href="{{ route('admin.settings') }}"
                               wire:navigate
                               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 min-h-[44px] text-sm font-medium transition-colors {{ request()->routeIs('admin.settings') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523]' }}">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <span>Configurações Globais</span>
                            </a>
                        @endif
                    </nav>

                    <div class="pt-4 border-t border-[#EAE0DA]">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                </svg>
                                <span>Sair da Conta</span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
        @endif

        {{-- Content Shell --}}
        <div class="flex-1 flex flex-col min-w-0 {{ $user ? 'lg:pl-72' : '' }}">
            {{-- Top Header --}}
            <header class="sticky top-0 z-30 bg-[#FFFFFF]/95 backdrop-blur border-b border-[#EAE0DA]">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 lg:hidden">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#C25E38] text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            </span>
                            <span class="font-serif text-lg font-semibold text-[#2C2523]">Nosso Voto</span>
                        </a>

                        @if ($user)
                            <div class="hidden lg:flex items-center gap-3 min-w-0">
                                <span class="text-sm font-medium text-[#6E625F]">Conectado como</span>
                                <span class="text-sm font-semibold text-[#2C2523] truncate">{{ $user->name }}</span>
                                <span class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                                    {{ $user->roleLabel() }}
                                </span>
                            </div>
                        @else
                            <div class="hidden lg:flex items-center gap-2 text-sm text-[#6E625F]">
                                <span>Nosso Voto — Gestão de Casamentos</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        @if ($user)
                            <div class="flex lg:hidden items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                                    {{ $user->roleLabel() }}
                                </span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] px-3 py-1.5 min-h-[38px] text-xs font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] cursor-pointer">
                                        Sair
                                    </button>
                                </form>
                            </div>
                            <span class="hidden lg:inline-block text-xs text-[#6E625F]">{{ $user->email }}</span>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                                Entrar
                            </a>
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                Criar Conta
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-6 sm:px-6 lg:px-8 pb-24 lg:pb-10">
                <x-toast :message="session('status')" :listen="true" />

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile-first Fixed Bottom Navigation --}}
    @if ($user)
        <nav class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-[#FFFFFF] border-t border-[#EAE0DA] shadow-[0_-4px_20px_rgba(44,37,35,0.06)]" aria-label="Navegação móvel">
            <div class="grid {{ $user->isAdmin() ? 'grid-cols-5' : 'grid-cols-4' }} gap-1 px-2 py-1.5">
                <a href="{{ route('home') }}"
                   class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('home') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                    </svg>
                    <span>Início</span>
                </a>

                @if ($user->isCouple() || $user->isAdmin())
                    <a href="{{ route('dashboard') }}"
                       wire:navigate
                       class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('dashboard') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z" />
                        </svg>
                        <span>Painel Noivos</span>
                    </a>

                    @if ($user->isCouple())
                        <a href="{{ route('inspirations.index') }}"
                           wire:navigate
                           class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('inspirations.*') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                            </svg>
                            <span>Inspirações</span>
                        </a>
                    @endif
                @endif

                <a href="{{ route('showcase.index') }}"
                   wire:navigate
                   class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('showcase.*') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614" />
                    </svg>
                    <span>Vitrine</span>
                </a>

                @if ($user->isVendor() || $user->isAdmin())
                    <a href="{{ route('vendor.dashboard') }}"
                       wire:navigate
                       class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('vendor.dashboard') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387" />
                        </svg>
                        <span>Fornecedor</span>
                    </a>
                @endif

                @if ($user->isVendor() && ! $user->isAdmin())
                    <a href="{{ route('vendor.schedule') }}"
                       wire:navigate
                       class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('vendor.schedule') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span>Agenda</span>
                    </a>
                @endif

                @if ($user->isAdmin())
                    <a href="{{ route('admin.settings') }}"
                       wire:navigate
                       class="flex flex-col items-center justify-center gap-0.5 rounded-xl min-h-[48px] px-2 py-1 text-[11px] font-medium {{ request()->routeIs('admin.settings') ? 'bg-[#F9EBE6] text-[#C25E38] font-semibold' : 'text-[#6E625F]' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>Admin</span>
                    </a>
                @endif
            </div>
        </nav>
    @endif

    @livewireScripts
</body>
</html>

