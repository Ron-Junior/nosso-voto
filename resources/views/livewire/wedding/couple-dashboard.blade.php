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
         openWeddingModal(data) {
             $wire.partner_1_name = data.partner_1_name || '';
             $wire.partner_2_name = data.partner_2_name || '';
             $wire.wedding_date = data.wedding_date || null;
             $wire.state = data.state || '';
             $wire.city = data.city || '';
             $wire.venue_name = data.venue_name ?? null;
             $wire.estimated_budget = data.estimated_budget ?? '0.00';
             $wire.showWeddingModal = true;
             $nextTick(() => {
                 this.syncCityOptions(this.$refs.modalCitySelect, $wire.state, data.city || '');
             });
         },
         closeWeddingModal() {
             $wire.showWeddingModal = false;
         },
         openNewVendor() {
             $wire.editingVendorId = null;
             $wire.vendor_name = '';
             $wire.category = 'espaco';
             $wire.contact_name = null;
             $wire.contact_phone = null;
             $wire.contact_email = null;
             $wire.estimated_amount = '0.00';
             $wire.contracted_amount = '0.00';
             $wire.paid_amount = '0.00';
             $wire.status = 'cotando';
             $wire.notes = null;
             $wire.showVendorModal = true;
         },
         openEditVendor(v) {
             $wire.editingVendorId = v.id;
             $wire.vendor_name = v.vendor_name || '';
             $wire.category = v.category || 'espaco';
             $wire.contact_name = v.contact_name ?? null;
             $wire.contact_phone = v.contact_phone ?? null;
             $wire.contact_email = v.contact_email ?? null;
             $wire.estimated_amount = v.estimated_amount ?? '0.00';
             $wire.contracted_amount = v.contracted_amount ?? '0.00';
             $wire.paid_amount = v.paid_amount ?? '0.00';
             $wire.status = v.status || 'cotando';
             $wire.notes = v.notes ?? null;
             $wire.showVendorModal = true;
         },
         closeVendorModal() {
             $wire.showVendorModal = false;
         }
     }"
     @keydown.escape.window="if ($wire.showVendorModal) { closeVendorModal(); } else if ($wire.showWeddingModal) { closeWeddingModal(); }">
    <x-toast :message="$statusMessage" />

    @if ($wedding === null)
        {{-- First-time Couple Onboarding --}}
        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                {{ $user->roleLabel() }}
            </p>
            <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                Olá, {{ $user->name }}
            </h1>
            <p class="text-sm text-[#6E625F]">
                Configure os dados do seu casamento e o orçamento pretendido para começar a organizar contratos e fornecedores com autonomia.
            </p>
        </section>

        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm">
            <h2 class="font-serif text-xl font-semibold text-[#2C2523] mb-1">
                Cadastrar Meu Casamento
            </h2>
            <p class="text-sm text-[#6E625F] mb-6">
                Essas informações ajudam a calcular a contagem regressiva e acompanhar o orçamento em tempo real.
            </p>

            <form wire:submit="saveWedding" class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="onboard_partner_1" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Noivo(a) 1
                        </label>
                        <input id="onboard_partner_1"
                               type="text"
                               wire:model="partner_1_name"
                               placeholder="Ex.: Ana"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @error('partner_1_name')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="onboard_partner_2" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Noivo(a) 2
                        </label>
                        <input id="onboard_partner_2"
                               type="text"
                               wire:model="partner_2_name"
                               placeholder="Ex.: Lucas"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @error('partner_2_name')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="onboard_wedding_date" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Data do Casamento
                        </label>
                        <input id="onboard_wedding_date"
                               type="date"
                               wire:model="wedding_date"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @error('wedding_date')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="onboard_estimated_budget" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Orçamento Pretendido (R$)
                        </label>
                        <input id="onboard_estimated_budget"
                               type="number"
                               step="0.01"
                               min="0"
                               wire:model="estimated_budget"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @error('estimated_budget')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="onboard_state" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Estado (UF)
                        </label>
                        <select id="onboard_state"
                                wire:model="state"
                                @change="syncCityOptions($refs.onboardCitySelect, $el.value)"
                                class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
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
                        <label for="onboard_city" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Cidade
                        </label>
                        <select id="onboard_city"
                                x-ref="onboardCitySelect"
                                wire:model="city"
                                @disabled(empty($citiesForSelectedState))
                                class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
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

                    <div class="sm:col-span-2">
                        <label for="onboard_venue_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Local / Espaço (opcional)
                        </label>
                        <input id="onboard_venue_name"
                               type="text"
                               wire:model="venue_name"
                               placeholder="Ex.: Fazenda Vila Rica"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @error('venue_name')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveWedding"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="saveWedding">Criar Planejamento do Casamento</span>
                        <span wire:loading wire:target="saveWedding">Salvando...</span>
                    </button>
                </div>
            </form>
        </section>
    @else
        {{-- Wedding Header + Countdown --}}
        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                            {{ $user->roleLabel() }}
                        </span>
                        <span class="text-[#EAE0DA]">•</span>
                        <span class="text-xs text-[#6E625F]">Olá, {{ $user->name }}</span>
                    </div>

                    <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                        {{ $wedding->coupleTitle() }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-2.5 text-sm text-[#6E625F]">
                        @if ($wedding->wedding_date !== null)
                            <span class="inline-flex items-center gap-1.5 rounded-xl bg-[#F9EBE6] px-3 py-1.5 text-xs font-semibold text-[#C25E38]">
                                @if ($daysUntilWedding > 1)
                                    Faltam {{ $daysUntilWedding }} dias
                                @elseif ($daysUntilWedding === 1)
                                    Falta 1 dia
                                @elseif ($daysUntilWedding === 0)
                                    É hoje! Grande Dia
                                @else
                                    Realizado há {{ abs($daysUntilWedding) }} {{ abs($daysUntilWedding) === 1 ? 'dia' : 'dias' }}
                                @endif
                            </span>
                            <span>{{ $wedding->wedding_date->format('d/m/Y') }}</span>
                        @else
                            <span class="inline-flex items-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3 py-1.5 text-xs font-medium text-[#6E625F]">
                                Data a definir
                            </span>
                        @endif

                        <span>•</span>
                        <span>{{ $wedding->city }} - {{ $wedding->state }}</span>

                        @if (filled($wedding->venue_name))
                            <span>•</span>
                            <span>{{ $wedding->venue_name }}</span>
                        @endif
                    </div>

                    <p class="text-xs text-[#6E625F]">
                        Endereço do casal: <span class="font-mono text-[#2C2523]">/c/{{ $wedding->slug }}</span>
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5 shrink-0">
                    <a href="{{ route('inspirations.index') }}"
                       wire:navigate.hover
                       class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors">
                        Inspirações Pinterest
                    </a>

                    <button type="button"
                            @click="openWeddingModal(@js([
                                'partner_1_name' => $wedding->partner_1_name,
                                'partner_2_name' => $wedding->partner_2_name,
                                'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
                                'state' => $wedding->state,
                                'city' => $wedding->city,
                                'venue_name' => $wedding->venue_name,
                                'estimated_budget' => number_format((float) $wedding->estimated_budget, 2, '.', ''),
                            ]))"
                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                        Editar Casamento
                    </button>

                    <button type="button"
                            @click="openNewVendor()"
                            class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                        + Novo Fornecedor
                    </button>
                </div>
            </div>

            {{-- Inline Quick Budget Edit --}}
            <form wire:submit="saveBudget" class="pt-4 border-t border-[#EAE0DA] flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="flex-1 max-w-xs">
                    <label for="quick_estimated_budget" class="block text-xs font-semibold uppercase tracking-wider text-[#6E625F] mb-1.5">
                        Orçamento Pretendido (R$)
                    </label>
                    <input id="quick_estimated_budget"
                           type="number"
                           step="0.01"
                           min="0"
                           wire:model="estimated_budget"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-semibold tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    @error('estimated_budget')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveBudget"
                        class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                    <span wire:loading.remove wire:target="saveBudget">Atualizar Orçamento</span>
                    <span wire:loading wire:target="saveBudget">Salvando...</span>
                </button>
            </form>
        </section>

        {{-- 4 Financial Summary Cards --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumo financeiro do casamento">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Orçamento Pretendido</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#2C2523]">
                    R$ {{ number_format((float) $wedding->estimated_budget, 2, ',', '.') }}
                </p>
                <p class="text-xs font-medium tabular-nums {{ $budgetBalance < 0 ? 'text-[#C25E38]' : 'text-[#5A8F7B]' }}">
                    @if ($budgetBalance < 0)
                        Estouro de R$ {{ number_format(abs($budgetBalance), 2, ',', '.') }}
                    @else
                        Saldo disponível: R$ {{ number_format($budgetBalance, 2, ',', '.') }}
                    @endif
                </p>
            </article>

            <article class="rounded-2xl border {{ $isOverBudget ? 'border-[#C25E38] bg-[#F9EBE6]/40' : 'border-[#EAE0DA] bg-[#FFFFFF]' }} p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total Fechado</p>
                <p class="font-serif text-2xl font-semibold tabular-nums {{ $isOverBudget ? 'text-[#C25E38]' : 'text-[#2C2523]' }}">
                    R$ {{ number_format($totalContracted, 2, ',', '.') }}
                </p>
                <p class="text-xs text-[#6E625F] tabular-nums">
                    Teto previsto nos itens: R$ {{ number_format($totalEstimatedVendors, 2, ',', '.') }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total Pago</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#5A8F7B]">
                    R$ {{ number_format($totalPaid, 2, ',', '.') }}
                </p>
                @php
                    $paidPercent = $totalContracted > 0 ? min(100, (int) round(($totalPaid / $totalContracted) * 100)) : 0;
                @endphp
                <p class="text-xs text-[#6E625F] tabular-nums">
                    {{ $paidPercent }}% do valor fechado quitado
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Saldo a Pagar</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#2C2523]">
                    R$ {{ number_format($remainingToPay, 2, ',', '.') }}
                </p>
                <p class="text-xs text-[#6E625F] tabular-nums">
                    {{ $allVendorsCount }} {{ $allVendorsCount === 1 ? 'fornecedor cadastrado' : 'fornecedores cadastrados' }}
                </p>
            </article>
        </section>

        {{-- Global Budget Overrun Alert --}}
        @if ($isOverBudget)
            <section data-budget-alert="global"
                     role="alert"
                     class="rounded-2xl border border-[#C25E38] bg-[#F9EBE6] p-4 sm:p-5 flex items-start gap-3.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#C25E38] text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </span>
                <div class="space-y-1">
                    <h2 class="font-serif text-base font-semibold text-[#2C2523]">
                        Alerta de estouro no orçamento geral
                    </h2>
                    <p class="text-sm text-[#2C2523] tabular-nums">
                        O total fechado (<strong>R$ {{ number_format($totalContracted, 2, ',', '.') }}</strong>) ultrapassou o orçamento pretendido (<strong>R$ {{ number_format((float) $wedding->estimated_budget, 2, ',', '.') }}</strong>) em <strong>R$ {{ number_format(abs($budgetBalance), 2, ',', '.') }}</strong>.
                    </p>
                </div>
            </section>
        @endif

        {{-- Vendors & Autonomous Contracts List --}}
        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                        Fornecedores e Contratos
                    </h2>
                    <p class="text-sm text-[#6E625F]">
                        Cadastre e acompanhe fornecedores externos mesmo que eles ainda não utilizem a plataforma.
                    </p>
                </div>

                <button type="button"
                        @click="openNewVendor()"
                        class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer shrink-0">
                    + Adicionar Fornecedor
                </button>
            </div>

            {{-- Filters --}}
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label for="vendor_search" class="sr-only">Buscar fornecedor</label>
                    <input id="vendor_search"
                           type="search"
                           wire:model.live.debounce.350ms="search"
                           placeholder="Buscar por nome, contato ou nota..."
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                </div>

                <div>
                    <label for="vendor_category_filter" class="sr-only">Filtrar por categoria</label>
                    <select id="vendor_category_filter"
                            wire:model.live="categoryFilter"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        <option value="all">Todas as categorias</option>
                        @foreach ($categoryLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="vendor_status_filter" class="sr-only">Filtrar por status</label>
                    <select id="vendor_status_filter"
                            wire:model.live="statusFilter"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        <option value="all">Todos os status</option>
                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Scannable Mobile-First Vendor Cards --}}
            <div wire:loading.class="opacity-60 transition-opacity" wire:target="search,categoryFilter,statusFilter">
                @if ($vendors->isEmpty())
                    <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-8 text-center space-y-3">
                        <p class="font-serif text-lg font-semibold text-[#2C2523]">
                            {{ $allVendorsCount === 0 ? 'Nenhum fornecedor cadastrado ainda' : 'Nenhum fornecedor encontrado para este filtro' }}
                        </p>
                        <p class="text-sm text-[#6E625F] max-w-md mx-auto">
                            {{ $allVendorsCount === 0
                                ? 'Adicione seu primeiro fornecedor manualmente com teto previsto, valor fechado, pagamentos e link exclusivo de convite.'
                                : 'Tente limpar a busca ou selecionar outra categoria/status.' }}
                        </p>
                        @if ($allVendorsCount === 0)
                            <button type="button"
                                    @click="openNewVendor()"
                                    class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                                Cadastrar Primeiro Fornecedor
                            </button>
                        @endif
                    </div>
                @else
                <div class="space-y-4">
                    @foreach ($vendors as $vendor)
                        @php
                            $statusBadgeClasses = match ($vendor->status) {
                                \App\Models\WeddingVendor::STATUS_PAID => 'bg-[#EBF3F0] text-[#5A8F7B]',
                                \App\Models\WeddingVendor::STATUS_CONTRACTED => 'bg-[#F9EBE6] text-[#C25E38]',
                                default => 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]',
                            };
                        @endphp

                        <article wire:key="vendor-{{ $vendor->id }}"
                                 class="rounded-2xl border {{ $vendor->isOverEstimate() ? 'border-[#C25E38]/70 bg-[#FFFFFF]' : 'border-[#EAE0DA] bg-[#FFFFFF]' }} p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-lg bg-[#FBF9F7] border border-[#EAE0DA] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                                            {{ $vendor->categoryLabel() }}
                                        </span>
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses }}">
                                            {{ $vendor->statusLabel() }}
                                        </span>
                                        @if ($vendor->isCerimonialista())
                                            <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-1 text-xs font-semibold text-[#C25E38]">
                                                Visão consolidada
                                            </span>
                                        @endif
                                        @if ($vendor->vendor_profile_id === null)
                                            <span class="inline-flex items-center rounded-lg bg-[#FBF9F7] px-2.5 py-1 text-xs text-[#6E625F]">
                                                Cadastro manual
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                                        {{ $vendor->vendor_name }}
                                    </h3>

                                    @if (filled($vendor->contact_name) || filled($vendor->contact_phone) || filled($vendor->contact_email))
                                        <p class="text-xs text-[#6E625F] flex flex-wrap items-center gap-x-3 gap-y-1">
                                            @if (filled($vendor->contact_name))
                                                <span>Contato: <strong class="font-medium text-[#2C2523]">{{ $vendor->contact_name }}</strong></span>
                                            @endif
                                            @if (filled($vendor->contact_phone))
                                                <span>{{ $vendor->contact_phone }}</span>
                                            @endif
                                            @if (filled($vendor->contact_email))
                                                <span>{{ $vendor->contact_email }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                @if ($vendor->isOverEstimate())
                                    <div data-vendor-over-estimate="{{ $vendor->id }}"
                                         class="inline-flex items-center gap-1.5 rounded-xl border border-[#C25E38]/30 bg-[#F9EBE6] px-3 py-2 text-xs font-semibold text-[#C25E38] tabular-nums shrink-0">
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376-.866 1.5c-.866 1.5-1.948 1.5-3.898 1.5H7.461c-1.95 0-3.032 0-3.898-1.5l-.866-1.5c-.866-1.5.217-3.374 1.948-3.374h14.71c1.73 0 2.813 1.874 1.948 3.374Z" />
                                        </svg>
                                        <span>Acima do teto previsto (+R$ {{ number_format($vendor->overEstimateAmount(), 2, ',', '.') }})</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Vendor Financial Grid --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-3.5">
                                <div>
                                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Teto Previsto</span>
                                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
                                        R$ {{ number_format((float) $vendor->estimated_amount, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Fechado</span>
                                    <span class="mt-0.5 block text-sm font-semibold tabular-nums {{ $vendor->isOverEstimate() ? 'text-[#C25E38]' : 'text-[#2C2523]' }}">
                                        R$ {{ number_format((float) $vendor->contracted_amount, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Pago</span>
                                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#5A8F7B]">
                                        R$ {{ number_format((float) $vendor->paid_amount, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Falta Pagar</span>
                                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
                                        R$ {{ number_format($vendor->remainingAmount(), 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            @if (filled($vendor->notes))
                                <p class="text-sm text-[#6E625F] leading-relaxed">
                                    {{ $vendor->notes }}
                                </p>
                            @endif

                            {{-- Invite Link & Actions --}}
                            <div class="pt-3 border-t border-[#EAE0DA] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 min-w-0 flex-1">
                                    <span class="text-xs font-medium text-[#6E625F] shrink-0">Link de convite:</span>
                                    <code class="rounded-lg border border-[#EAE0DA] bg-[#FBF9F7] px-2.5 py-1.5 text-xs font-mono text-[#2C2523] truncate select-all">
                                        {{ $vendor->inviteUrl() }}
                                    </code>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    <button type="button"
                                            x-data="{ url: @js($vendor->inviteUrl()), vendorName: @js($vendor->vendor_name) }"
                                            @click="
                                                (async () => {
                                                    try {
                                                        if (navigator.clipboard && window.isSecureContext) {
                                                            await navigator.clipboard.writeText(url);
                                                        } else {
                                                            const el = document.createElement('textarea');
                                                            el.value = url;
                                                            el.style.position = 'fixed';
                                                            el.style.opacity = '0';
                                                            document.body.appendChild(el);
                                                            el.select();
                                                            document.execCommand('copy');
                                                            document.body.removeChild(el);
                                                        }
                                                    } catch (e) {}
                                                    window.dispatchEvent(new CustomEvent('client-toast', { detail: { message: 'Link de acesso de ' + vendorName + ' copiado para a área de transferência.' } }));
                                                })()
                                            "
                                            class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer">
                                        Copiar Link de Acesso
                                    </button>

                                    <button type="button"
                                            wire:click="regenerateVendorInviteToken({{ $vendor->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="regenerateVendorInviteToken({{ $vendor->id }})"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2 min-h-[44px] text-xs font-medium text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                        Novo Link
                                    </button>

                                    <button type="button"
                                            @click="openEditVendor(@js([
                                                'id' => $vendor->id,
                                                'vendor_name' => $vendor->vendor_name,
                                                'category' => $vendor->category,
                                                'contact_name' => $vendor->contact_name,
                                                'contact_phone' => $vendor->contact_phone,
                                                'contact_email' => $vendor->contact_email,
                                                'estimated_amount' => number_format((float) $vendor->estimated_amount, 2, '.', ''),
                                                'contracted_amount' => number_format((float) $vendor->contracted_amount, 2, '.', ''),
                                                'paid_amount' => number_format((float) $vendor->paid_amount, 2, '.', ''),
                                                'status' => $vendor->status,
                                                'notes' => $vendor->notes,
                                            ]))"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                        Editar
                                    </button>

                                    <button type="button"
                                            wire:click="deleteVendor({{ $vendor->id }})"
                                            wire:confirm="Tem certeza que deseja remover este fornecedor do orçamento?"
                                            wire:loading.attr="disabled"
                                            wire:target="deleteVendor({{ $vendor->id }})"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2 min-h-[44px] text-xs font-medium text-red-700 hover:bg-red-50 hover:border-red-300 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                @endif
            </div>
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

    {{-- Edit Wedding Modal --}}
    @if ($wedding !== null || $showWeddingModal)
        <div x-cloak
             x-show="$wire.showWeddingModal"
             @click.self="closeWeddingModal()"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#2C2523]/50 p-0 sm:p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="wedding-modal-title">
            <div class="w-full sm:max-w-xl max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="wedding-modal-title" class="font-serif text-xl font-semibold text-[#2C2523]">
                        Dados do Casamento
                    </h2>
                    <button type="button"
                            @click="closeWeddingModal()"
                            aria-label="Fechar modal"
                            class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] text-[#6E625F] hover:text-[#2C2523] cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveWedding" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="modal_partner_1" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Noivo(a) 1
                            </label>
                            <input id="modal_partner_1"
                                   type="text"
                                   wire:model="partner_1_name"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('partner_1_name')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="modal_partner_2" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Noivo(a) 2
                            </label>
                            <input id="modal_partner_2"
                                   type="text"
                                   wire:model="partner_2_name"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('partner_2_name')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="modal_wedding_date" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Data do Casamento
                            </label>
                            <input id="modal_wedding_date"
                                   type="date"
                                   wire:model="wedding_date"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('wedding_date')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="modal_estimated_budget" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Orçamento Pretendido (R$)
                            </label>
                            <input id="modal_estimated_budget"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="estimated_budget"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('estimated_budget')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="modal_state" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Estado (UF)
                            </label>
                            <select id="modal_state"
                                    wire:model="state"
                                    @change="syncCityOptions($refs.modalCitySelect, $el.value)"
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
                            <label for="modal_city" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Cidade
                            </label>
                            <select id="modal_city"
                                    x-ref="modalCitySelect"
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
                    </div>

                    <div>
                        <label for="modal_venue_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Local / Espaço (opcional)
                        </label>
                        <input id="modal_venue_name"
                               type="text"
                               wire:model="venue_name"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @error('venue_name')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-3 border-t border-[#EAE0DA] flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                        <button type="button"
                                @click="closeWeddingModal()"
                                class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-5 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveWedding"
                                class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="saveWedding">Salvar Casamento</span>
                            <span wire:loading wire:target="saveWedding">Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Create/Edit Vendor Modal --}}
    @if ($wedding !== null || $showVendorModal)
        <div x-cloak
             x-show="$wire.showVendorModal"
             @click.self="closeVendorModal()"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#2C2523]/50 p-0 sm:p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="vendor-modal-title">
            <div class="w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="vendor-modal-title"
                        x-text="$wire.editingVendorId !== null ? 'Editar Fornecedor' : 'Novo Fornecedor / Contrato'"
                        class="font-serif text-xl font-semibold text-[#2C2523]">
                        {{ $editingVendorId !== null ? 'Editar Fornecedor' : 'Novo Fornecedor / Contrato' }}
                    </h2>
                    <button type="button"
                            @click="closeVendorModal()"
                            aria-label="Fechar modal"
                            class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] text-[#6E625F] hover:text-[#2C2523] cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveVendor" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="vendor_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Nome do Fornecedor ou Empresa
                            </label>
                            <input id="vendor_name"
                                   type="text"
                                   wire:model="vendor_name"
                                   placeholder="Ex.: Atelier Flor & Folha"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('vendor_name')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_category" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Categoria
                            </label>
                            <select id="vendor_category"
                                    wire:model="category"
                                    class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                @foreach ($categoryLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_status" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Status do Contrato
                            </label>
                            <select id="vendor_status"
                                    wire:model="status"
                                    class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                @foreach ($statusLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_estimated_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Teto Previsto (R$)
                            </label>
                            <input id="vendor_estimated_amount"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="estimated_amount"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('estimated_amount')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_contracted_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Valor Fechado (R$)
                            </label>
                            <input id="vendor_contracted_amount"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="contracted_amount"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contracted_amount')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_paid_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Valor Já Pago (R$)
                            </label>
                            <input id="vendor_paid_amount"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="paid_amount"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('paid_amount')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_contact_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Responsável / Contato (opcional)
                            </label>
                            <input id="vendor_contact_name"
                                   type="text"
                                   wire:model="contact_name"
                                   placeholder="Ex.: Camila"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contact_name')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_contact_phone" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Telefone / WhatsApp (opcional)
                            </label>
                            <input id="vendor_contact_phone"
                                   type="text"
                                   wire:model="contact_phone"
                                   placeholder="(11) 99999-9999"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contact_phone')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor_contact_email" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                E-mail (opcional)
                            </label>
                            <input id="vendor_contact_email"
                                   type="email"
                                   wire:model="contact_email"
                                   placeholder="contato@fornecedor.com.br"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contact_email')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="vendor_notes" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Observações do Contrato (opcional)
                        </label>
                        <textarea id="vendor_notes"
                                  rows="3"
                                  wire:model="notes"
                                  placeholder="Parcelas, entregas combinadas, horários..."
                                  class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none"></textarea>
                        @error('notes')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-3 border-t border-[#EAE0DA] flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                        <button type="button"
                                @click="closeVendorModal()"
                                class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-5 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveVendor"
                                class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="saveVendor"
                                  x-text="$wire.editingVendorId !== null ? 'Salvar Alterações' : 'Cadastrar Fornecedor'">
                                {{ $editingVendorId !== null ? 'Salvar Alterações' : 'Cadastrar Fornecedor' }}
                            </span>
                            <span wire:loading wire:target="saveVendor">Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

