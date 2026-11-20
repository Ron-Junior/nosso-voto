<div class="space-y-6">
    @if ($statusMessage)
        <div class="rounded-2xl border border-[#5A8F7B]/30 bg-[#EBF3F0] px-4 py-3 text-sm font-medium text-[#2C2523]">
            {{ $statusMessage }}
        </div>
    @endif

    {{-- Header / Profile Card --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                        {{ $user->roleLabel() }}
                    </span>

                    @if ($user->hasFreeAccess())
                        <span class="inline-flex items-center rounded-full bg-[#EBF3F0] px-3 py-1 text-xs font-semibold text-[#5A8F7B]">
                            Plano Casal 100% Gratuito — Sem mensalidades
                        </span>
                    @endif
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                    Olá, {{ $user->name }}
                </h1>
                <p class="text-sm text-[#6E625F]">
                    @if ($user->isVendor())
                        Gerencie seus próximos casamentos, vitrine regional e módulos profissionais no Nosso Voto.
                    @else
                        Bem-vindo ao seu espaço de planejamento de casamento no Nosso Voto.
                    @endif
                </p>
            </div>

            @if ($user->isAdmin())
                <a href="{{ route('admin.settings') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-3 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors shrink-0">
                    Abrir Configurações Globais
                </a>
            @endif
        </div>
    </section>

    {{-- API Token Card --}}
    <section id="api-token" class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-lg font-semibold text-[#2C2523]">
                    Token de Integração (Extensão Chrome & API)
                </h2>
                <p class="text-sm text-[#6E625F]">
                    Utilize este token exclusivo para autenticar a extensão do Chrome e integrações externas.
                </p>
            </div>

            <button type="button"
                    wire:click="regenerateToken"
                    class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer shrink-0">
                <span wire:loading.remove wire:target="regenerateToken">Gerar Novo Token</span>
                <span wire:loading wire:target="regenerateToken">Gerando...</span>
            </button>
        </div>

        <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 font-mono text-xs sm:text-sm text-[#2C2523] break-all select-all">
            {{ $user->api_token }}
        </div>
    </section>

    {{-- Role Contextual Overview --}}
    @if ($user->isVendor())
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Agenda de Próximos Noivos</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Cadastro Autônomo</h3>
                <p class="text-sm text-[#6E625F]">
                    Em breve na Spec 04: controle seus contratos e datas mesmo quando os noivos ainda não usam a plataforma.
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Destaque Regional</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                    R$ {{ number_format((float) $settings->regional_highlight_monthly_price, 2, ',', '.') }}/mês
                </h3>
                <p class="text-sm text-[#6E625F]">
                    Exibição randômica justa no topo da sua cidade/categoria (Spec 06).
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2 sm:col-span-2 lg:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Portfólio & Banner</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                    Portfólio R$ {{ number_format((float) $settings->portfolio_monthly_price, 2, ',', '.') }}/mês
                </h3>
                <p class="text-sm text-[#6E625F]">
                    Banner em ciclos de {{ $settings->banner_cycle_months }} meses (máx. {{ $settings->banner_max_slots }} vagas) por R$ {{ number_format((float) $settings->banner_cycle_price, 2, ',', '.') }}.
                </p>
            </article>
        </section>
    @else
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 02</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Orçamento & Contratos</h3>
                <p class="text-sm text-[#6E625F]">
                    Controle teto previsto, valor fechado, pagamentos e links de convite para cerimonialista e fornecedores.
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 05</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Inspirações Pinterest</h3>
                <p class="text-sm text-[#6E625F]">
                    Salve referências de Decoração, Maquiagem e Penteado via web, extensão Chrome ou compartilhamento PWA.
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2 sm:col-span-2 lg:col-span-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 08</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Lista de Presentes Pix</h3>
                <p class="text-sm text-[#6E625F]">
                    Taxa atual configurada na plataforma: {{ number_format((float) $settings->platform_gift_fee_percent, 2, ',', '.') }}% (opção de repassar ou absorver).
                </p>
            </article>
        </section>
    @endif
</div>

