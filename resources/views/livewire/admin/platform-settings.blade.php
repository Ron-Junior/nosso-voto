<div class="space-y-6"
     x-data="{
         citiesMap: @js($citiesByState),
         syncCycleCities(selectEl, uf, preferredCity = '') {
             if (!selectEl) return;
             const normalizedUf = String(uf || '').trim().toUpperCase();
             const cities = this.citiesMap[normalizedUf] || [];
             const currentCity = preferredCity !== '' ? preferredCity : ($wire.cycle_city || '');
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
                 $wire.cycle_city = currentCity;
             } else {
                 selectEl.value = '';
                 $wire.cycle_city = '';
             }
         }
     }">
    <x-toast :message="$statusMessage" />

    <header class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-1.5">
        <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
            Painel Administrativo
        </p>
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
            Configurações Globais da Plataforma
        </h1>
        <p class="text-sm text-[#6E625F]">
            Gerencie feature flags, taxa da lista de presentes, valores dos módulos de monetização e ciclos do Banner Principal.
        </p>
    </header>

    {{-- Feature Flag: Paywall Pinterest --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <h2 class="font-serif text-lg font-semibold text-[#2C2523]">
                    Paywall das Inspirações do Pinterest
                </h2>
                <p class="text-sm text-[#6E625F]">
                    Chave liga/desliga para exigir desbloqueio pago quando Decorador, Maquiadora ou Penteadista acessam os moodboards do casal.
                </p>
            </div>

            <button type="button"
                    wire:click="togglePinterestPaywall"
                    wire:loading.attr="disabled"
                    wire:target="togglePinterestPaywall"
                    class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 min-h-[44px] text-sm font-semibold transition-colors cursor-pointer shrink-0 disabled:opacity-60 disabled:cursor-wait {{ $paywall_pinterest_inspirations_enabled ? 'bg-[#C25E38] text-white hover:bg-[#A84D2B]' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38]' }}">
                <span wire:loading.remove wire:target="togglePinterestPaywall">
                    {{ $paywall_pinterest_inspirations_enabled ? 'Desligar Paywall' : 'Ligar Paywall' }}
                </span>
                <span wire:loading wire:target="togglePinterestPaywall">Atualizando...</span>
            </button>
        </div>
    </section>

    {{-- Numeric Settings Form --}}
    <form wire:submit="save" class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-6">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="platform_gift_fee_percent" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Taxa da Lista de Presentes (%)
                </label>
                <input id="platform_gift_fee_percent"
                       type="number"
                       step="0.01"
                       min="0"
                       max="100"
                       wire:model="platform_gift_fee_percent"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('platform_gift_fee_percent')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="regional_highlight_monthly_price" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Destaque Regional Mensal (R$)
                </label>
                <input id="regional_highlight_monthly_price"
                       type="number"
                       step="0.01"
                       min="0"
                       wire:model="regional_highlight_monthly_price"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('regional_highlight_monthly_price')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="portfolio_monthly_price" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Portfólio Pago Mensal (R$)
                </label>
                <input id="portfolio_monthly_price"
                       type="number"
                       step="0.01"
                       min="0"
                       wire:model="portfolio_monthly_price"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('portfolio_monthly_price')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="banner_cycle_price" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Valor do Ciclo do Banner (R$)
                </label>
                <input id="banner_cycle_price"
                       type="number"
                       step="0.01"
                       min="0"
                       wire:model="banner_cycle_price"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('banner_cycle_price')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="banner_cycle_months" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Duração do Ciclo do Banner (meses)
                </label>
                <input id="banner_cycle_months"
                       type="number"
                       min="1"
                       max="24"
                       wire:model="banner_cycle_months"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('banner_cycle_months')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="banner_max_slots" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Máximo de Vagas no Banner (limite 5)
                </label>
                <input id="banner_max_slots"
                       type="number"
                       min="1"
                       max="5"
                       wire:model="banner_max_slots"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('banner_max_slots')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-[#EAE0DA]">
            <label class="inline-flex items-center gap-2.5 text-sm text-[#2C2523] cursor-pointer">
                <input type="checkbox"
                       wire:model="paywall_pinterest_inspirations_enabled"
                       class="h-4 w-4 rounded border-[#EAE0DA] text-[#C25E38] focus:ring-[#C25E38]">
                <span>Exigir pagamento nas inspirações do Pinterest</span>
            </label>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                <span wire:loading.remove wire:target="save">Salvar Configurações</span>
                <span wire:loading wire:target="save">Salvando...</span>
            </button>
        </div>
    </form>

    {{-- Banner Cycles & Submissions Management --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-6">
        <div class="space-y-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                Monetização Regional
            </span>
            <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                Gestão de Ciclos do Banner Principal (Máx. 5 Vagas)
            </h2>
            <p class="text-sm text-[#6E625F]">
                Crie ciclos regionais por cidade/UF e aprove candidaturas respeitando a trava rígida de até 5 vagas simultâneas.
            </p>
        </div>

        @error('banner_slot_limit')
            <div role="alert" class="rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-3 text-sm font-medium text-[#C25E38]">
                {{ $message }}
            </div>
        @enderror

        {{-- Create New Banner Cycle Form --}}
        <form wire:submit="createBannerCycle" class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-5 space-y-4">
            <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                Abrir Novo Ciclo Regional
            </h3>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="cycle_title" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Título do Ciclo
                    </label>
                    <input id="cycle_title"
                           type="text"
                           wire:model="cycle_title"
                           placeholder="Ex.: Ciclo Bimestral Mai-Jun • São Paulo/SP"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                    @error('cycle_title')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cycle_state" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Estado (UF)
                    </label>
                    <select id="cycle_state"
                            wire:model="cycle_state"
                            @change="syncCycleCities($refs.cycleCitySelect, $el.value)"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                        <option value="">Selecione</option>
                        @foreach ($states as $uf => $stateName)
                            <option value="{{ $uf }}">{{ $uf }} — {{ $stateName }}</option>
                        @endforeach
                    </select>
                    @error('cycle_state')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cycle_city" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Cidade
                    </label>
                    <select id="cycle_city"
                            x-ref="cycleCitySelect"
                            wire:model="cycle_city"
                            @disabled(empty($citiesForCycleState))
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:outline-none disabled:opacity-60">
                        <option value="">Selecione a cidade</option>
                        @foreach ($citiesForCycleState as $cityOption)
                            <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                        @endforeach
                    </select>
                    @error('cycle_city')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cycle_duration_months" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Duração (meses)
                    </label>
                    <input id="cycle_duration_months"
                           type="number"
                           min="1"
                           max="24"
                           wire:model.live="cycle_duration_months"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                    @error('cycle_duration_months')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cycle_max_slots" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Vagas (máx. 5)
                    </label>
                    <input id="cycle_max_slots"
                           type="number"
                           min="1"
                           max="5"
                           wire:model="cycle_max_slots"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                    @error('cycle_max_slots')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cycle_price" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Valor do Ciclo (R$)
                    </label>
                    <input id="cycle_price"
                           type="number"
                           step="0.01"
                           min="0"
                           wire:model="cycle_price"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                    @error('cycle_price')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="cycle_starts_at" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Início
                        </label>
                        <input id="cycle_starts_at"
                               type="date"
                               wire:model.live="cycle_starts_at"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-2.5 min-h-[44px] text-xs text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                    </div>
                    <div>
                        <label for="cycle_ends_at" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                            Término
                        </label>
                        <input id="cycle_ends_at"
                               type="date"
                               wire:model="cycle_ends_at"
                               class="w-full rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-2.5 min-h-[44px] text-xs text-[#2C2523] focus:border-[#C25E38] focus:outline-none">
                    </div>
                    @error('cycle_starts_at')
                        <p class="col-span-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                    @error('cycle_ends_at')
                        <p class="col-span-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="createBannerCycle"
                    class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60">
                <span wire:loading.remove wire:target="createBannerCycle">Criar Ciclo do Banner</span>
                <span wire:loading wire:target="createBannerCycle">Criando...</span>
            </button>
        </form>

        {{-- Existing Cycles & Submissions --}}
        @if ($bannerCycles->isEmpty())
            <div class="rounded-xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-5 text-sm text-[#6E625F]">
                Nenhum ciclo de banner cadastrado ainda. Crie um ciclo acima para receber candidaturas regionais.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($bannerCycles as $cycle)
                    @php
                        $activeCount = $cycle->activeSlotsCount();
                        $maxSlots = $cycle->effectiveMaxSlots();
                    @endphp
                    <article wire:key="admin-cycle-{{ $cycle->id }}"
                             class="rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold tabular-nums {{ $activeCount >= $maxSlots ? 'bg-[#F9EBE6] text-[#C25E38]' : 'bg-[#EBF3F0] text-[#5A8F7B]' }}">
                                        {{ $activeCount }} / {{ $maxSlots }} vagas ativas
                                    </span>
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium {{ $cycle->is_active ? 'bg-[#FFFFFF] border border-[#EAE0DA] text-[#2C2523]' : 'bg-[#EAE0DA]/60 text-[#6E625F]' }}">
                                        {{ $cycle->is_active ? 'Ciclo Ativo' : 'Pausado' }}
                                    </span>
                                </div>
                                <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                                    {{ $cycle->title }}
                                </h3>
                                <p class="text-xs text-[#6E625F] tabular-nums">
                                    {{ $cycle->city }} - {{ $cycle->state }} • {{ $cycle->periodLabel() }} • {{ $cycle->duration_months }} meses • R$ {{ number_format((float) $cycle->price, 2, ',', '.') }}
                                </p>
                            </div>

                            <button type="button"
                                    wire:click="toggleBannerCycleActive({{ $cycle->id }})"
                                    class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer shrink-0">
                                {{ $cycle->is_active ? 'Pausar Ciclo' : 'Reativar Ciclo' }}
                            </button>
                        </div>

                        @if ($cycle->submissions->isEmpty())
                            <p class="text-xs text-[#6E625F]">
                                Nenhuma candidatura enviada para este ciclo até o momento.
                            </p>
                        @else
                            <div class="space-y-2.5">
                                @foreach ($cycle->submissions as $submission)
                                    @php
                                        $subBadge = match ($submission->status) {
                                            \App\Models\BannerSubmission::STATUS_ACTIVE => 'bg-[#EBF3F0] text-[#5A8F7B]',
                                            \App\Models\BannerSubmission::STATUS_WAITLIST => 'bg-[#F9EBE6] text-[#C25E38]',
                                            \App\Models\BannerSubmission::STATUS_REJECTED => 'bg-[#EAE0DA]/70 text-[#6E625F]',
                                            default => 'bg-[#FFFFFF] border border-[#EAE0DA] text-[#2C2523]',
                                        };
                                    @endphp
                                    <div wire:key="admin-submission-{{ $submission->id }}"
                                         class="rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                        <div class="space-y-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[11px] font-semibold {{ $subBadge }}">
                                                    {{ $submissionStatusLabels[$submission->status] ?? $submission->status }}
                                                    @if ($submission->isActive() && $submission->slot_number !== null)
                                                        • Vaga #{{ $submission->slot_number }}
                                                    @endif
                                                </span>
                                                @if ($submission->vendorProfile !== null)
                                                    <span class="text-xs text-[#6E625F]">
                                                        {{ $submission->vendorProfile->city }} - {{ $submission->vendorProfile->state }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="font-serif text-base font-semibold text-[#2C2523]">
                                                {{ $submission->vendorProfile?->business_name ?? 'Fornecedor' }}
                                            </p>
                                            <p class="text-xs text-[#6E625F]">
                                                {{ $submission->headline }}
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                                            @if ($submission->status !== \App\Models\BannerSubmission::STATUS_ACTIVE)
                                                <button type="button"
                                                        wire:click="approveBannerSubmission({{ $submission->id }})"
                                                        class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                                                    Aprovar Vaga
                                                </button>
                                            @endif

                                            @if ($submission->status !== \App\Models\BannerSubmission::STATUS_WAITLIST)
                                                <button type="button"
                                                        wire:click="waitlistBannerSubmission({{ $submission->id }})"
                                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                                    Lista de Espera
                                                </button>
                                            @endif

                                            @if ($submission->status !== \App\Models\BannerSubmission::STATUS_REJECTED)
                                                <button type="button"
                                                        wire:click="rejectBannerSubmission({{ $submission->id }})"
                                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#6E625F] hover:text-red-600 transition-colors cursor-pointer">
                                                    Recusar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
