<div class="space-y-6"
     x-data="{
         citiesMap: @js($citiesByState),
         syncCityOptions(selectEl, uf, preferredCity = '') {
             if (!selectEl) return;
             const normalizedUf = String(uf || '').trim().toUpperCase();
             const cities = this.citiesMap[normalizedUf] || [];
             const currentCity = preferredCity !== '' ? preferredCity : ($wire.city || '');
             selectEl.innerHTML = '';
             const placeholder = document.createElement('option');
             placeholder.value = '';
             placeholder.textContent = cities.length === 0 ? 'Selecione o estado primeiro' : 'Selecione a cidade';
             selectEl.appendChild(placeholder);
             for (const cityName of cities) {
                 const opt = document.createElement('option');
                 opt.value = cityName;
                 opt.textContent = cityName;
                 selectEl.appendChild(opt);
             }
             selectEl.disabled = cities.length === 0;
             if (cities.includes(currentCity)) {
                 selectEl.value = currentCity;
                 $wire.city = currentCity;
             } else {
                 selectEl.value = '';
                 $wire.city = '';
             }
         },
         openProfileModal(data) {
             $wire.business_name = data.business_name || '';
             $wire.profile_category = data.profile_category || 'fotografia';
             $wire.profile_categories = data.profile_categories || [data.profile_category || 'fotografia'];
             $wire.state = data.state || 'SP';
             $wire.city = data.city || '';
             $wire.whatsapp = data.whatsapp || '';
             $wire.instagram = data.instagram ?? null;
             $wire.description = data.description ?? null;
             $wire.cover_image_url = data.cover_image_url ?? null;
             $wire.price_from = data.price_from ?? null;
             $wire.showProfileModal = true;
             $nextTick(() => {
                 this.syncCityOptions(this.$refs.profileCitySelect, $wire.state, data.city || '');
             });
         },
         closeProfileModal() {
             $wire.showProfileModal = false;
         }
     }"
     @keydown.escape.window="if ($wire.showProfileModal) closeProfileModal()">
    <x-toast :message="$statusMessage" />

    {{-- Header / Profile Card --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="space-y-1.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                        {{ $user->roleLabel() }}
                    </p>
                    @if ($isVendorArea && $vendorProfile !== null)
                        <span class="text-[#EAE0DA]">•</span>
                        @foreach ($vendorProfile->categoryLabelsList() as $catSlug => $catLabel)
                            <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-semibold {{ $catSlug === $vendorProfile->category ? 'bg-[#F9EBE6] text-[#C25E38]' : 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]' }}">
                                {{ $catLabel }}
                            </span>
                        @endforeach
                        <span class="text-xs text-[#6E625F]">
                            {{ $vendorProfile->city }} - {{ $vendorProfile->state }}
                        </span>
                    @endif
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                    Olá, {{ $user->name }}
                </h1>

                @if ($isVendorArea && $vendorProfile !== null)
                    <p class="text-sm text-[#6E625F]">
                        <strong class="font-semibold text-[#2C2523]">{{ $vendorProfile->business_name }}</strong>
                        @if (filled($vendorProfile->whatsapp))
                            • WhatsApp: {{ $vendorProfile->whatsapp }}
                        @endif
                        @if (filled($vendorProfile->instagram))
                            • {{ $vendorProfile->instagram }}
                        @endif
                        @if ($vendorProfile->price_from !== null)
                            • A partir de <span class="tabular-nums font-medium text-[#2C2523]">R$ {{ number_format((float) $vendorProfile->price_from, 2, ',', '.') }}</span>
                        @endif
                    </p>
                    @if (filled($vendorProfile->description))
                        <p class="text-sm text-[#6E625F] leading-relaxed">
                            {{ $vendorProfile->description }}
                        </p>
                    @endif
                    <p class="text-xs text-[#6E625F]">
                        Perfil na Vitrine Regional:
                        <a href="{{ route('showcase.show', $vendorProfile->slug) }}"
                           wire:navigate
                           class="font-mono text-[#C25E38] hover:text-[#A84D2B] underline underline-offset-2">
                            /vitrine/{{ $vendorProfile->slug }}
                        </a>
                    </p>
                @else
                    <p class="text-sm text-[#6E625F]">
                        @if ($isVendorArea)
                            Configure seu perfil comercial, acompanhe casamentos vinculados por e-mail e gerencie seus módulos profissionais.
                        @else
                            Bem-vindo ao seu espaço de planejamento de casamento no Nosso Voto.
                        @endif
                    </p>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-2.5 shrink-0">
                @if ($isVendorArea)
                    <button type="button"
                            @click="openProfileModal(@js([
                                'business_name' => $vendorProfile?->business_name ?? $user->name,
                                'profile_category' => $vendorProfile?->category ?? 'fotografia',
                                'profile_categories' => $vendorProfile?->allCategories() ?? ['fotografia'],
                                'state' => $vendorProfile?->state ?? 'SP',
                                'city' => $vendorProfile?->city ?? '',
                                'whatsapp' => $vendorProfile?->whatsapp ?? '',
                                'instagram' => $vendorProfile?->instagram,
                                'description' => $vendorProfile?->description,
                                'cover_image_url' => $vendorProfile?->cover_image_url,
                                'price_from' => $vendorProfile?->price_from !== null ? number_format((float) $vendorProfile->price_from, 2, '.', '') : null,
                            ]))"
                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                        {{ $vendorProfile !== null ? 'Editar Perfil Profissional' : 'Configurar Perfil Profissional' }}
                    </button>

                    <a href="{{ route('vendor.schedule') }}"
                       wire:navigate
                       class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                        Abrir Agenda de Noivos
                    </a>
                @endif

                @if ($user->isAdmin())
                    <a href="{{ route('admin.settings') }}"
                       wire:navigate
                       class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors shrink-0">
                        Abrir Configurações Globais
                    </a>
                @endif
            </div>
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

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                @if ($linkedVendor->vendor_profile_id === null)
                                    <button type="button"
                                            wire:click="linkEmailWeddingToAgenda({{ $linkedVendor->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="linkEmailWeddingToAgenda({{ $linkedVendor->id }})"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                        Adicionar à Agenda
                                    </button>
                                @elseif ($vendorProfile !== null && $linkedVendor->vendor_profile_id === $vendorProfile->id)
                                    <span class="inline-flex items-center rounded-xl bg-[#EBF3F0] px-3 py-2 text-xs font-semibold text-[#5A8F7B]">
                                        Na sua agenda
                                    </span>
                                @endif

                                <a href="{{ route('vendor.wedding.access', $linkedVendor->invite_token) }}"
                                   wire:navigate
                                   class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors shrink-0">
                                    {{ $linkedVendor->isCerimonialista() ? 'Abrir Painel Consolidado' : 'Acessar Casamento' }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Role Contextual Overview --}}
    @if ($isVendorArea)
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Agenda de Noivos</span>
                    <h2 class="font-serif text-lg font-semibold text-[#2C2523] tabular-nums">
                        {{ $scheduleSummary['upcomingCount'] }} {{ $scheduleSummary['upcomingCount'] === 1 ? 'Próximo Casamento' : 'Próximos Casamentos' }}
                    </h2>
                    <p class="text-sm text-[#6E625F] tabular-nums">
                        {{ $scheduleSummary['totalEntriesCount'] }} {{ $scheduleSummary['totalEntriesCount'] === 1 ? 'contrato registrado' : 'contratos registrados' }} • R$ {{ number_format($scheduleSummary['remainingToReceive'], 2, ',', '.') }} a receber.
                    </p>
                </div>

                <a href="{{ route('vendor.schedule') }}"
                   wire:navigate
                   class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors">
                    Gerenciar na Agenda
                </a>
            </article>

            {{-- Destaque Regional Mensal --}}
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Destaque Regional</span>
                        @if ($vendorProfile !== null && $vendorProfile->hasActiveRegionalHighlight())
                            <span class="inline-flex items-center rounded-full bg-[#EBF3F0] px-2.5 py-0.5 text-[11px] font-semibold text-[#5A8F7B]">
                                Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-[#FBF9F7] border border-[#EAE0DA] px-2.5 py-0.5 text-[11px] font-medium text-[#6E625F]">
                                Inativo
                            </span>
                        @endif
                    </div>

                    <h2 class="font-serif text-lg font-semibold text-[#2C2523] tabular-nums">
                        R$ {{ number_format((float) $settings->regional_highlight_monthly_price, 2, ',', '.') }}/mês
                    </h2>

                    <p class="text-sm text-[#6E625F]">
                        Exibição em ordem randômica no topo da sua cidade e áreas de atuação na Vitrine.
                    </p>

                    @if ($vendorProfile !== null && $vendorProfile->hasActiveRegionalHighlight() && $vendorProfile->highlight_expires_at !== null)
                        <p class="text-xs font-medium text-[#5A8F7B]">
                            Válido até {{ $vendorProfile->highlight_expires_at->format('d/m/Y') }}.
                        </p>
                    @endif

                    @error('highlight')
                        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            wire:click="activateRegionalHighlight"
                            wire:loading.attr="disabled"
                            wire:target="activateRegionalHighlight"
                            class="flex-1 inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60">
                        <span wire:loading.remove wire:target="activateRegionalHighlight">
                            {{ $vendorProfile !== null && $vendorProfile->hasActiveRegionalHighlight() ? 'Renovar +30 dias' : 'Ativar Destaque Regional' }}
                        </span>
                        <span wire:loading wire:target="activateRegionalHighlight">Ativando...</span>
                    </button>

                    <a href="{{ route('showcase.index') }}"
                       wire:navigate
                       class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2.5 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                        Ver Vitrine
                    </a>
                </div>
            </article>

            {{-- Portfólio & Banner Info --}}
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-2 sm:col-span-2 lg:col-span-1 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Portfólio &amp; Banner</span>
                    <h2 class="font-serif text-lg font-semibold text-[#2C2523] tabular-nums">
                        Portfólio R$ {{ number_format((float) $settings->portfolio_monthly_price, 2, ',', '.') }}/mês
                    </h2>
                    <p class="text-sm text-[#6E625F] tabular-nums">
                        Banner em ciclos de {{ $settings->banner_cycle_months }} meses (máx. {{ min((int) $settings->banner_max_slots, \App\Models\BannerCycle::HARD_MAX_SLOTS) }} vagas) por R$ {{ number_format((float) $settings->banner_cycle_price, 2, ',', '.') }}.
                    </p>
                </div>

                <a href="#banner-ciclo-regional"
                   class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                    Candidatar ao Banner
                </a>
            </article>
        </section>

        {{-- Banner Principal por Ciclo (Máx. 5 Vagas) --}}
        <section id="banner-ciclo-regional" class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="space-y-1">
                    <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                        Banner Principal da Vitrine (Máx. 5 Vagas)
                    </h2>
                    <p class="text-sm text-[#6E625F]">
                        Espaço rotativo no topo da Vitrine da sua região com limite rígido de 5 vagas por ciclo vigente.
                    </p>
                </div>
                @if ($vendorProfile !== null)
                    <span class="inline-flex items-center self-start rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3 py-1.5 text-xs font-medium text-[#2C2523]">
                        Região: {{ $vendorProfile->city }} - {{ $vendorProfile->state }}
                    </span>
                @endif
            </div>

            @if ($vendorProfile === null)
                <div class="rounded-xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-5 text-sm text-[#6E625F]">
                    Configure primeiro o seu perfil profissional (cidade, estado e áreas de atuação) para visualizar e enviar candidatura aos ciclos do Banner Principal da sua região.
                </div>
            @elseif ($activeBannerCycles->isEmpty())
                <div class="rounded-xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-5 text-sm text-[#6E625F] space-y-1">
                    <p class="font-medium text-[#2C2523]">Nenhum ciclo de banner aberto em {{ $vendorProfile->city }} - {{ $vendorProfile->state }} no momento.</p>
                    <p class="tabular-nums">Quando um ciclo de {{ $settings->banner_cycle_months }} meses (até {{ min((int) $settings->banner_max_slots, \App\Models\BannerCycle::HARD_MAX_SLOTS) }} vagas por R$ {{ number_format((float) $settings->banner_cycle_price, 2, ',', '.') }}) for aberto para sua cidade, você poderá enviar sua candidatura aqui.</p>
                </div>
            @else
                <div class="grid gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-5 space-y-3">
                        @foreach ($activeBannerCycles as $cycle)
                            @php
                                $submission = $vendorBannerSubmissions->get($cycle->id);
                                $remainingSlots = $cycle->remainingSlotsCount();
                                $maxSlots = $cycle->effectiveMaxSlots();
                            @endphp
                            <article wire:key="vendor-cycle-{{ $cycle->id }}"
                                     class="rounded-xl border p-4 space-y-2.5 {{ (int) $selected_banner_cycle_id === $cycle->id ? 'border-[#C25E38] bg-[#F9EBE6]/40' : 'border-[#EAE0DA] bg-[#FBF9F7]' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                                            {{ $cycle->title }}
                                        </h3>
                                        <p class="text-xs text-[#6E625F] tabular-nums">
                                            {{ $cycle->periodLabel() }} • {{ $cycle->duration_months }} {{ $cycle->duration_months === 1 ? 'mês' : 'meses' }} • R$ {{ number_format((float) $cycle->price, 2, ',', '.') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-semibold tabular-nums {{ $remainingSlots > 0 ? 'bg-[#EBF3F0] text-[#5A8F7B]' : 'bg-[#F9EBE6] text-[#C25E38]' }}">
                                        {{ $remainingSlots }} de {{ $maxSlots }} vagas disponíveis
                                    </span>
                                </div>

                                @if ($submission !== null)
                                    <div class="rounded-lg border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-2 text-xs flex items-center justify-between gap-2">
                                        <span class="text-[#6E625F]">Sua candidatura:</span>
                                        <span class="font-semibold text-[#2C2523]">
                                            {{ $submission->statusLabel() }}
                                            @if ($submission->isActive() && $submission->slot_number !== null)
                                                (Vaga #{{ $submission->slot_number }})
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if ($activeBannerCycles->count() > 1)
                                    <button type="button"
                                            wire:click="$set('selected_banner_cycle_id', {{ $cycle->id }})"
                                            class="w-full inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] cursor-pointer">
                                        Selecionar este ciclo
                                    </button>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    <form wire:submit="submitToBannerCycle" class="lg:col-span-7 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 sm:p-5 space-y-4">
                        <div class="space-y-1">
                            <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                                Enviar Candidatura ao Banner Principal
                            </h3>
                            <p class="text-xs text-[#6E625F]">
                                Todos os fornecedores da região podem se candidatar. Se as 5 vagas estiverem preenchidas, sua inscrição entra automaticamente na Lista de Espera.
                            </p>
                        </div>

                        @error('banner_submission')
                            <div class="rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-3 text-xs font-medium text-[#C25E38]">
                                {{ $message }}
                            </div>
                        @enderror

                        @if ($activeBannerCycles->count() > 1)
                            <div>
                                <label for="selected_banner_cycle_id" class="block text-xs font-semibold uppercase tracking-wider text-[#6E625F] mb-1.5">
                                    Ciclo da Região
                                </label>
                                <select id="selected_banner_cycle_id"
                                        wire:model.live="selected_banner_cycle_id"
                                        class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                                    @foreach ($activeBannerCycles as $cycle)
                                        <option value="{{ $cycle->id }}">
                                            {{ $cycle->title }} ({{ $cycle->remainingSlotsCount() }} de {{ $cycle->effectiveMaxSlots() }} vagas)
                                        </option>
                                    @endforeach
                                </select>
                                @error('selected_banner_cycle_id')
                                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div>
                            <label for="banner_headline" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Título / Chamada do Banner
                            </label>
                            <input id="banner_headline"
                                   type="text"
                                   wire:model="banner_headline"
                                   placeholder="Ex.: Condição especial para casamentos 2027 em São Paulo"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                            @error('banner_headline')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="banner_image_url" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    URL da Imagem do Banner (opcional)
                                </label>
                                <input id="banner_image_url"
                                       type="url"
                                       wire:model="banner_image_url"
                                       placeholder="https://..."
                                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                                @error('banner_image_url')
                                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="banner_cta_link" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Link de Destino (opcional)
                                </label>
                                <input id="banner_cta_link"
                                       type="url"
                                       wire:model="banner_cta_link"
                                       placeholder="https://instagram.com/..."
                                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                                @error('banner_cta_link')
                                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submitToBannerCycle"
                                class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60">
                            <span wire:loading.remove wire:target="submitToBannerCycle">Enviar Candidatura ao Banner</span>
                            <span wire:loading wire:target="submitToBannerCycle">Enviando...</span>
                        </button>
                    </form>
                </div>
            @endif
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
                    wire:loading.attr="disabled"
                    wire:target="regenerateToken"
                    class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer shrink-0 disabled:opacity-60 disabled:cursor-wait">
                <span wire:loading.remove wire:target="regenerateToken">Gerar Novo Token</span>
                <span wire:loading wire:target="regenerateToken">Gerando...</span>
            </button>
        </div>

        <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 font-mono text-xs sm:text-sm text-[#2C2523] break-all select-all">
            {{ $user->api_token }}
        </div>
    </section>

    {{-- Edit Vendor Profile Modal --}}
    @if ($isVendorArea || $showProfileModal)
        <div x-cloak
             x-show="$wire.showProfileModal"
             @click.self="closeProfileModal()"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#2C2523]/50 p-0 sm:p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="vendor-profile-modal-title">
            <div class="w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="vendor-profile-modal-title" class="font-serif text-xl font-semibold text-[#2C2523]">
                        Perfil do Fornecedor
                    </h2>
                    <button type="button"
                            @click="closeProfileModal()"
                            aria-label="Fechar modal"
                            class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] text-[#6E625F] hover:text-[#2C2523] cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveVendorProfile" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="profile_business_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Nome Comercial / Fantasia
                            </label>
                            <input id="profile_business_name"
                                   type="text"
                                   wire:model="business_name"
                                   placeholder="Ex.: Ateliê Luz & Arte Fotografia"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('business_name')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2 rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 space-y-3">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-medium text-[#2C2523]">
                                    Áreas de Atuação na Vitrine <span class="text-[#C25E38]">*</span>
                                </span>
                                <p class="text-xs text-[#6E625F]">
                                    Marque uma ou mais áreas em que você atua (ex.: Maquiagem e Penteado).
                                </p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2" role="group" aria-label="Áreas de atuação">
                                @foreach ($categoryLabels as $key => $label)
                                    <label class="relative flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               wire:model.live="profile_categories"
                                               value="{{ $key }}"
                                               class="peer sr-only">
                                        <span class="w-full inline-flex items-center gap-2 rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-2 min-h-[44px] text-xs font-medium text-[#2C2523] transition-colors peer-checked:border-[#C25E38] peer-checked:bg-[#F9EBE6] peer-checked:text-[#C25E38] peer-checked:font-semibold">
                                            <span class="h-2 w-2 rounded-full bg-[#EAE0DA] peer-checked:bg-[#C25E38] shrink-0"></span>
                                            <span class="truncate">{{ $label }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @error('profile_categories')
                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                            @error('profile_categories.*')
                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_category" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Área Principal (Destaque)
                            </label>
                            <select id="profile_category"
                                    wire:model.live="profile_category"
                                    class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                @foreach ($categoryLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('profile_category')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_whatsapp" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                WhatsApp Comercial
                            </label>
                            <input id="profile_whatsapp"
                                   type="text"
                                   wire:model="whatsapp"
                                   placeholder="(11) 99999-8888"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('whatsapp')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_state" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Estado (UF)
                            </label>
                            <select id="profile_state"
                                    wire:model="state"
                                    @change="syncCityOptions($refs.profileCitySelect, $el.value)"
                                    class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                <option value="">Selecione o estado</option>
                                @foreach ($states as $uf => $stateName)
                                    <option value="{{ $uf }}">{{ $uf }} — {{ $stateName }}</option>
                                @endforeach
                            </select>
                            @error('state')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_city" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Cidade
                            </label>
                            <select id="profile_city"
                                    x-ref="profileCitySelect"
                                    wire:model="city"
                                    @disabled(empty($citiesForSelectedState))
                                    class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                                <option value="">
                                    {{ empty($citiesForSelectedState) ? 'Selecione o estado primeiro' : 'Selecione a cidade' }}
                                </option>
                                @foreach ($citiesForSelectedState as $cityOption)
                                    <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                @endforeach
                            </select>
                            @error('city')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_instagram" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Instagram (opcional)
                            </label>
                            <input id="profile_instagram"
                                   type="text"
                                   wire:model="instagram"
                                   placeholder="@seuatelie"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('instagram')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile_price_from" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Pacotes a Partir de (R$, opcional)
                            </label>
                            <input id="profile_price_from"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="price_from"
                                   placeholder="3500.00"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('price_from')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="profile_cover_image_url" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                URL da Imagem de Capa (opcional)
                            </label>
                            <input id="profile_cover_image_url"
                                   type="url"
                                   wire:model="cover_image_url"
                                   placeholder="https://..."
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('cover_image_url')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="profile_description" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Apresentação do Serviço (opcional)
                            </label>
                            <textarea id="profile_description"
                                      rows="3"
                                      wire:model="description"
                                      placeholder="Descreva seu estilo de atendimento, diferenciais e região de atuação..."
                                      class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none"></textarea>
                            @error('description')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                        <button type="button"
                                @click="closeProfileModal()"
                                class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-5 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveVendorProfile"
                                class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="saveVendorProfile">Salvar Perfil</span>
                            <span wire:loading wire:target="saveVendorProfile">Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
