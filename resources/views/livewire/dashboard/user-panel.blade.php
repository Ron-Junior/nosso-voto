<div class="space-y-6">
    <x-toast :message="$statusMessage" />

    {{-- Header / Profile Card --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                    {{ $user->roleLabel() }}
                </p>

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

    {{-- Linked Weddings for Authenticated Vendor --}}
    @if ($user->isVendor())
        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-4">
            <div class="space-y-1">
                <h2 class="font-serif text-lg font-semibold text-[#2C2523]">
                    Casamentos Vinculados à Sua Conta
                </h2>
                <p class="text-sm text-[#6E625F]">
                    Casamentos em que o seu e-mail (<span class="font-medium text-[#2C2523]">{{ $user->email }}</span>) foi cadastrado pelos noivos ou cerimonialista.
                </p>
            </div>

            @if ($linkedWeddingVendors->isEmpty())
                <div class="rounded-xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-5 text-sm text-[#6E625F]">
                    Nenhum casamento vinculado ao seu e-mail no momento. Quando um casal adicionar o seu contato, o acesso direto aparecerá aqui.
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($linkedWeddingVendors as $linkedVendor)
                        @php
                            $linkedWedding = $linkedVendor->wedding;
                            $statusBadgeClasses = match ($linkedVendor->status) {
                                \App\Models\WeddingVendor::STATUS_PAID => 'bg-[#EBF3F0] text-[#5A8F7B]',
                                \App\Models\WeddingVendor::STATUS_CONTRACTED => 'bg-[#F9EBE6] text-[#C25E38]',
                                default => 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]',
                            };
                        @endphp

                        <article wire:key="linked-wedding-{{ $linkedVendor->id }}"
                                 class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-lg bg-[#FFFFFF] border border-[#EAE0DA] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                                        {{ $linkedVendor->categoryLabel() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses }}">
                                        {{ $linkedVendor->statusLabel() }}
                                    </span>
                                    @if ($linkedVendor->isCerimonialista())
                                        <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-1 text-xs font-semibold text-[#C25E38]">
                                            Visão consolidada
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                                    {{ $linkedWedding->coupleTitle() }}
                                </h3>

                                <p class="text-xs text-[#6E625F]">
                                    {{ $linkedWedding->wedding_date?->format('d/m/Y') ?? 'Data a definir' }}
                                    • {{ $linkedWedding->city }} - {{ $linkedWedding->state }}
                                    @if (filled($linkedWedding->venue_name))
                                        • {{ $linkedWedding->venue_name }}
                                    @endif
                                </p>
                            </div>

                            <a href="{{ route('vendor.wedding.access', $linkedVendor->invite_token) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors shrink-0">
                                {{ $linkedVendor->isCerimonialista() ? 'Abrir Painel Consolidado' : 'Acessar Casamento' }}
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

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
        <section class="grid gap-4 sm:grid-cols-2">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 02</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Orçamento & Contratos</h3>
                <p class="text-sm text-[#6E625F]">
                    Controle teto previsto, valor fechado, pagamentos e links de convite para cerimonialista e fornecedores.
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 09</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Checklist do Grande Dia</h3>
                <p class="text-sm text-[#6E625F]">
                    Organize tudo o que precisam para o casamento e marque o que está completando e o que já têm.
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 05</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Inspirações Pinterest</h3>
                <p class="text-sm text-[#6E625F]">
                    Salve referências de Decoração, Maquiagem e Penteado via web, extensão Chrome ou compartilhamento PWA.
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Spec 08</span>
                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">Lista de Presentes Pix</h3>
                <p class="text-sm text-[#6E625F]">
                    Taxa atual configurada na plataforma: {{ number_format((float) $settings->platform_gift_fee_percent, 2, ',', '.') }}% (opção de repassar ou absorver).
                </p>
            </article>
        </section>
    @endif
</div>

