<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Nosso Voto — Plataforma Completa para Planejamento de Casamentos</title>
    <meta name="description" content="Organize orçamento integrado a fornecedores, salve inspirações do Pinterest, colabore com sua cerimonialista e receba presentes em dinheiro via Pix. 100% gratuito para noivos.">
@include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#FBF9F7] text-[#2C2523] font-sans antialiased selection:bg-[#F9EBE6] selection:text-[#C25E38]">
    {{-- Landing Page Navbar --}}
    <header class="sticky top-0 z-40 border-b border-[#EAE0DA] bg-[#FFFFFF]/95 backdrop-blur">
        <div class="mx-auto flex h-18 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#C25E38] text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </span>
                <div>
                    <span class="block font-serif text-xl font-semibold tracking-tight text-[#2C2523]">Nosso Voto</span>
                    <span class="hidden sm:block text-[11px] text-[#6E625F]">Gestão de Casamentos</span>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-[#6E625F]" aria-label="Navegação da página inicial">
                <a href="#funcionalidades" class="hover:text-[#C25E38] transition-colors">Funcionalidades</a>
                <a href="#como-funciona" class="hover:text-[#C25E38] transition-colors">Como Funciona</a>
                <a href="#noivos" class="hover:text-[#C25E38] transition-colors">Para Noivos</a>
                <a href="#fornecedores" class="hover:text-[#C25E38] transition-colors">Para Fornecedores</a>
                <a href="{{ route('showcase.index') }}" class="hover:text-[#C25E38] transition-colors">Vitrine</a>
            </nav>

            <div class="flex items-center gap-2.5 shrink-0">
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}"
                       class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 sm:px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                        Ir para Meu Painel
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 sm:px-4 py-2 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 sm:px-5 py-2 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                        Criar Conta Grátis
                    </a>
                @endauth
            </div>
        </div>

        {{-- Mobile Anchor Strip --}}
        <div class="lg:hidden border-t border-[#EAE0DA] bg-[#FBF9F7]">
            <nav class="mx-auto flex max-w-6xl items-center justify-between gap-2 overflow-x-auto px-4 py-2 text-xs font-medium text-[#6E625F]" aria-label="Atalhos da página inicial">
                <a href="#funcionalidades" class="whitespace-nowrap rounded-lg px-2.5 py-2 min-h-[38px] inline-flex items-center hover:text-[#C25E38]">Funcionalidades</a>
                <a href="#como-funciona" class="whitespace-nowrap rounded-lg px-2.5 py-2 min-h-[38px] inline-flex items-center hover:text-[#C25E38]">Como Funciona</a>
                <a href="#noivos" class="whitespace-nowrap rounded-lg px-2.5 py-2 min-h-[38px] inline-flex items-center hover:text-[#C25E38]">Para Noivos</a>
                <a href="#fornecedores" class="whitespace-nowrap rounded-lg px-2.5 py-2 min-h-[38px] inline-flex items-center hover:text-[#C25E38]">Fornecedores</a>
                <a href="{{ route('showcase.index') }}" class="whitespace-nowrap rounded-lg px-2.5 py-2 min-h-[38px] inline-flex items-center hover:text-[#C25E38]">Vitrine</a>
            </nav>
        </div>
    </header>

    <main>
        {{-- Hero Section Editorial --}}
        <section class="relative overflow-hidden border-b border-[#EAE0DA] bg-gradient-to-b from-[#FFFFFF] via-[#FBF9F7] to-[#FBF9F7] py-12 sm:py-16 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-6 space-y-6">
                        <span class="inline-flex items-center gap-2 rounded-full bg-[#EBF3F0] px-3.5 py-1.5 text-xs font-semibold text-[#5A8F7B]">
                            <span class="h-2 w-2 rounded-full bg-[#5A8F7B]"></span>
                            100% Gratuito para Noivos
                        </span>

                        <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-[#2C2523] leading-[1.15]">
                            Planeje cada detalhe do seu casamento com leveza, clareza e zero mensalidade.
                        </h1>

                        <p class="text-base sm:text-lg text-[#6E625F] leading-relaxed">
                            O <strong class="font-semibold text-[#2C2523]">Nosso Voto</strong> conecta orçamento integrado a fornecedores, referências capturadas direto do Pinterest, colaboração com cerimonialista e lista de presentes convertida em dinheiro via Pix.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3 pt-1">
                            @auth
                                <a href="{{ auth()->user()->homeRoute() }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3.5 min-h-[48px] text-sm sm:text-base font-semibold text-white shadow-sm hover:bg-[#A84D2B] transition-colors">
                                    Acessar Meu Painel
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3.5 min-h-[48px] text-sm sm:text-base font-semibold text-white shadow-sm hover:bg-[#A84D2B] transition-colors">
                                    Começar Gratuitamente
                                </a>
                            @endauth

                            <a href="#como-funciona"
                               class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-6 py-3.5 min-h-[48px] text-sm sm:text-base font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                                Ver Como Funciona
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 pt-2 text-xs sm:text-sm text-[#6E625F]">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#C25E38]"></span>
                                Sem cartão no cadastro
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#C25E38]"></span>
                                Funciona no celular (PWA) e desktop
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#C25E38]"></span>
                                Cadastro livre de fornecedores
                            </span>
                        </div>
                    </div>

                    {{-- Visual Showcase Illustration --}}
                    <div class="lg:col-span-6">
                        <div class="rounded-3xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 sm:p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-[#EAE0DA] pb-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Prévia do Casamento</p>
                                    <p class="font-serif text-xl font-semibold text-[#2C2523]">Ana &amp; Lucas</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                                    Faltam 142 dias
                                </span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-[#6E625F]">Orçamento Previsto</span>
                                        <span class="text-[11px] font-semibold text-[#5A8F7B]">Dentro da meta</span>
                                    </div>
                                    <p class="text-xl font-bold tabular-nums text-[#2C2523]">R$ 65.000,00</p>
                                    <div class="h-2 w-full rounded-full bg-[#EAE0DA] overflow-hidden">
                                        <div class="h-full w-3/4 rounded-full bg-[#C25E38]"></div>
                                    </div>
                                    <p class="text-xs text-[#6E625F]">Fechado: <strong class="text-[#2C2523]">R$ 48.200,00</strong></p>
                                </div>

                                <div class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-[#6E625F]">Carteira Lista Pix</span>
                                        <span class="inline-flex items-center rounded-full bg-[#EBF3F0] px-2 py-0.5 text-[11px] font-semibold text-[#5A8F7B]">
                                            Disponível
                                        </span>
                                    </div>
                                    <p class="text-xl font-bold tabular-nums text-[#5A8F7B]">R$ 12.450,00</p>
                                    <p class="text-xs text-[#6E625F]">18 cotas recebidas com recados dos convidados</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-[#EAE0DA] bg-[#F9EBE6]/45 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="space-y-1">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">Moodboard Pinterest</span>
                                    <p class="text-sm font-medium text-[#2C2523]">
                                        32 inspirações salvas em Decoração, Maquiagem e Penteado
                                    </p>
                                </div>
                                <span class="inline-flex items-center justify-center rounded-xl bg-[#FFFFFF] border border-[#EAE0DA] px-3 py-2 text-xs font-semibold text-[#2C2523] shrink-0">
                                    Extensão Chrome &amp; PWA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section id="funcionalidades" class="py-14 sm:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="max-w-2xl space-y-3">
                    <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                        Funcionalidades
                    </span>
                    <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-semibold text-[#2C2523]">
                        Tudo o que o casal e os profissionais precisam em um só lugar
                    </h2>
                    <p class="text-sm sm:text-base text-[#6E625F]">
                        Chega de planilhas perdidas, prints espalhados no celular e links desconectados.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 space-y-3 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#F9EBE6] text-[#C25E38]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Orçamento Integrado</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Registre teto previsto, valor fechado e pagamentos. Cadastre qualquer fornecedor externo com autonomia total, mesmo que ele não use o sistema.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 space-y-3 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#F9EBE6] text-[#C25E38]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Inspirações do Pinterest</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Capture pins e pastas de Decoração, Maquiagem e Penteado com 1 clique pelo navegador (Extensão Chrome) ou compartilhando direto do app no celular.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 space-y-3 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#EBF3F0] text-[#5A8F7B]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                        <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Presentes em Dinheiro</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Crie cotas personalizadas com página elegante para convidados (Pix ou Cartão), escolha quem paga a taxa e resgate o saldo via Chave Pix.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 space-y-3 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#F9EBE6] text-[#C25E38]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Cerimonialista Colaborativo</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Gere um link exclusivo de convite para o cerimonialista acompanhar status de fornecedores e colaborar na organização sem acessar sua carteira Pix.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- How it Works --}}
        <section id="como-funciona" class="border-y border-[#EAE0DA] bg-[#FFFFFF] py-14 sm:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="max-w-2xl space-y-3">
                    <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                        Como Funciona
                    </span>
                    <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-semibold text-[#2C2523]">
                        Do primeiro orçamento ao grande dia em 3 passos simples
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-6 space-y-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#C25E38] text-sm font-bold text-white">1</span>
                        <h3 class="font-serif text-xl font-semibold text-[#2C2523]">Crie sua conta gratuita</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Escolha o perfil <strong>Noivos / Casal</strong> ou <strong>Fornecedor</strong> em menos de 1 minuto, sem taxas escondidas ou cartão de crédito.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-6 space-y-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#C25E38] text-sm font-bold text-white">2</span>
                        <h3 class="font-serif text-xl font-semibold text-[#2C2523]">Organize contratos e inspirações</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Defina seu orçamento total, registre seus fornecedores (da vitrine ou externos), salve pins do Pinterest e convide sua equipe.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-6 space-y-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#C25E38] text-sm font-bold text-white">3</span>
                        <h3 class="font-serif text-xl font-semibold text-[#2C2523]">Compartilhe sua lista e celebre</h3>
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            Envie o link da sua lista de presentes aos convidados, receba mensagens carinhosas e resgate tudo direto para sua Chave Pix.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Dual Audience Section: Couples & Vendors --}}
        <section class="py-14 sm:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- For Couples --}}
                    <article id="noivos" class="rounded-3xl border border-[#EAE0DA] bg-[#FFFFFF] p-7 sm:p-9 shadow-sm flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <span class="inline-flex items-center rounded-full bg-[#EBF3F0] px-3 py-1 text-xs font-semibold text-[#5A8F7B]">
                                Para Noivos &amp; Casais • 100% Gratuito
                            </span>
                            <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                                Planejamento completo sem pagar assinatura
                            </h2>
                            <p class="text-sm sm:text-base text-[#6E625F] leading-relaxed">
                                Todos os recursos de gestão do casamento são liberados gratuitamente para o casal do início ao fim.
                            </p>
                            <ul class="space-y-3 text-sm text-[#2C2523]">
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#5A8F7B] shrink-0"></span>
                                    <span>Controle de Orçamento Previsto vs. Valor Fechado com alertas automáticos de estouro.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#5A8F7B] shrink-0"></span>
                                    <span>Pastas de referências do Pinterest segmentadas para Decoração, Maquiagem e Penteado.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#5A8F7B] shrink-0"></span>
                                    <span>Links individuais para cerimonialista e profissionais acompanharem o evento.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#5A8F7B] shrink-0"></span>
                                    <span>Lista de presentes em dinheiro com opção de repassar ou absorver a taxa em 1 clique.</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <a href="{{ route('register') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                Criar Conta Gratuita de Noivos
                            </a>
                        </div>
                    </article>

                    {{-- For Vendors --}}
                    <article id="fornecedores" class="rounded-3xl border border-[#EAE0DA] bg-[#FFFFFF] p-7 sm:p-9 shadow-sm flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                                Para Fornecedores &amp; Profissionais
                            </span>
                            <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                                Vitrine justa, agenda própria e portfólio conectado
                            </h2>
                            <p class="text-sm sm:text-base text-[#6E625F] leading-relaxed">
                                Entre gratuitamente na vitrine da sua cidade e ative recursos estratégicos quando quiser acelerar sua agenda.
                            </p>
                            <ul class="space-y-3 text-sm text-[#2C2523]">
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#C25E38] shrink-0"></span>
                                    <span><strong>Agenda de Próximos Noivos:</strong> cadastre até casamentos de noivos que ainda não usam o sistema.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#C25E38] shrink-0"></span>
                                    <span><strong>Destaque Regional Justo:</strong> exibição em ordem randômica entre assinantes da mesma cidade/categoria.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#C25E38] shrink-0"></span>
                                    <span><strong>Banner Principal por Ciclo:</strong> espaço nobre limitado a no máximo 5 fornecedores simultâneos.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 h-2 w-2 rounded-full bg-[#C25E38] shrink-0"></span>
                                    <span><strong>Portfólio Pago com Sync Instagram:</strong> importe seus trabalhos e mantenha sua galeria sempre atualizada.</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <a href="{{ route('register') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-6 py-3 min-h-[48px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors">
                                Cadastrar Meu Serviço
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- Final CTA --}}
        <section class="pb-16 sm:pb-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-[#EAE0DA] bg-[#2C2523] px-6 py-10 sm:p-12 text-center text-white space-y-5">
                    <span class="inline-flex items-center rounded-full bg-[#C25E38] px-3.5 py-1 text-xs font-semibold text-white">
                        Comece Hoje Mesmo
                    </span>
                    <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-semibold max-w-2xl mx-auto">
                        Pronto para transformar o planejamento do seu casamento?
                    </h2>
                    <p class="text-sm sm:text-base text-[#EAE0DA] max-w-xl mx-auto">
                        Crie sua conta gratuita agora e organize orçamento, fornecedores, inspirações e presentes em um só espaço acolhedor.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                        @auth
                            <a href="{{ auth()->user()->homeRoute() }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-7 py-3.5 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                Acessar Meu Painel
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-7 py-3.5 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                Criar Conta Gratuita
                            </a>
                            <a href="{{ route('login') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-white/25 bg-white/10 px-7 py-3.5 min-h-[48px] text-sm font-semibold text-white hover:bg-white/20 transition-colors">
                                Entrar na Minha Conta
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Landing Page Footer --}}
    <footer class="border-t border-[#EAE0DA] bg-[#FFFFFF]">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-[#6E625F]">
            <div class="flex items-center gap-2">
                <span class="font-serif font-semibold text-[#2C2523]">Nosso Voto</span>
                <span>•</span>
                <span>Gestão de Casamentos</span>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-5">
                <a href="#funcionalidades" class="hover:text-[#C25E38] transition-colors">Funcionalidades</a>
                <a href="#como-funciona" class="hover:text-[#C25E38] transition-colors">Como Funciona</a>
                <a href="{{ route('showcase.index') }}" class="hover:text-[#C25E38] transition-colors">Vitrine</a>
                <a href="{{ route('login') }}" class="hover:text-[#C25E38] transition-colors">Entrar</a>
                <a href="{{ route('register') }}" class="hover:text-[#C25E38] transition-colors">Criar Conta</a>
            </div>
        </div>
    </footer>
</body>
</html>
