<div class="space-y-6"
     x-data="{
         activePeriod: $wire.entangle('periodFilter'),
         openNewSchedule() {
             $wire.editingEntryId = null;
             $wire.couple_names = '';
             $wire.event_date = null;
             $wire.event_city = null;
             $wire.event_venue = null;
             $wire.contact_name = null;
             $wire.contact_phone = null;
             $wire.contact_email = null;
             $wire.contracted_amount = '0.00';
             $wire.paid_amount = '0.00';
             $wire.status = 'contratado';
             $wire.notes = null;
             $wire.showScheduleModal = true;
         },
         openEditSchedule(entry) {
             $wire.editingEntryId = entry.id;
             $wire.couple_names = entry.couple_names || '';
             $wire.event_date = entry.event_date || null;
             $wire.event_city = entry.event_city ?? null;
             $wire.event_venue = entry.event_venue ?? null;
             $wire.contact_name = entry.contact_name ?? null;
             $wire.contact_phone = entry.contact_phone ?? null;
             $wire.contact_email = entry.contact_email ?? null;
             $wire.contracted_amount = entry.contracted_amount ?? '0.00';
             $wire.paid_amount = entry.paid_amount ?? '0.00';
             $wire.status = entry.status || 'contratado';
             $wire.notes = entry.notes ?? null;
             $wire.showScheduleModal = true;
         },
         closeScheduleModal() {
             $wire.showScheduleModal = false;
         }
     }"
     @keydown.escape.window="if ($wire.showScheduleModal) closeScheduleModal()">
    <x-toast :message="$statusMessage" />

    {{-- Agenda Header --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="space-y-1.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                        {{ $user->roleLabel() }}
                    </p>
                    @if ($vendorProfile !== null)
                        <span class="text-[#EAE0DA]">•</span>
                        <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-0.5 text-xs font-semibold text-[#C25E38]">
                            {{ $vendorProfile->business_name }}
                        </span>
                        <span class="text-xs text-[#6E625F]">
                            {{ $vendorProfile->categoryLabel() }} • {{ $vendorProfile->city }} - {{ $vendorProfile->state }}
                        </span>
                    @endif
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                    Agenda de Próximos Noivos
                </h1>
                <p class="text-sm text-[#6E625F]">
                    Linha do tempo unificada com casais da plataforma e noivos cadastrados manualmente por você.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-2.5 shrink-0">
                <a href="{{ route('vendor.dashboard') }}"
                   wire:navigate
                   class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                    Voltar ao Painel
                </a>

                <button type="button"
                        @click="openNewSchedule()"
                        class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                    + Novo Casamento na Agenda
                </button>
            </div>
        </div>
    </section>

    {{-- 4 Agenda Financial & Operational Summary Cards --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumo da agenda do fornecedor">
        <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Próximos Casamentos</p>
            <p class="font-serif text-2xl font-semibold tabular-nums text-[#2C2523]">
                {{ $scheduleSummary['upcomingCount'] }}
            </p>
            <p class="text-xs text-[#6E625F] tabular-nums">
                {{ $scheduleSummary['pastCount'] }} {{ $scheduleSummary['pastCount'] === 1 ? 'evento passado' : 'eventos passados' }}
            </p>
        </article>

        <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total Contratado</p>
            <p class="font-serif text-2xl font-semibold tabular-nums text-[#2C2523]">
                R$ {{ number_format($scheduleSummary['totalContracted'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-[#6E625F] tabular-nums">
                {{ $scheduleSummary['totalEntriesCount'] }} {{ $scheduleSummary['totalEntriesCount'] === 1 ? 'casamento na agenda' : 'casamentos na agenda' }}
            </p>
        </article>

        <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total Recebido</p>
            <p class="font-serif text-2xl font-semibold tabular-nums text-[#5A8F7B]">
                R$ {{ number_format($scheduleSummary['totalPaid'], 2, ',', '.') }}
            </p>
            @php
                $receivedPercent = $scheduleSummary['totalContracted'] > 0
                    ? min(100, (int) round(($scheduleSummary['totalPaid'] / $scheduleSummary['totalContracted']) * 100))
                    : 0;
            @endphp
            <p class="text-xs text-[#6E625F] tabular-nums">
                {{ $receivedPercent }}% dos contratos recebidos
            </p>
        </article>

        <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Saldo a Receber</p>
            <p class="font-serif text-2xl font-semibold tabular-nums text-[#C25E38]">
                R$ {{ number_format($scheduleSummary['remainingToReceive'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-[#6E625F]">
                Valores em aberto na agenda
            </p>
        </article>
    </section>

    {{-- Unified Chronological Vendor Agenda --}}
    <section id="agenda" class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
        {{-- Period Tabs --}}
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filtro de período da agenda">
            <button type="button"
                    @click="activePeriod = 'upcoming'"
                    wire:click="setPeriodFilter('upcoming')"
                    :class="activePeriod === 'upcoming' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#2C2523]'"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 min-h-[44px] text-sm font-semibold transition-colors cursor-pointer">
                Próximos Casamentos ({{ $scheduleSummary['upcomingCount'] }})
            </button>

            <button type="button"
                    @click="activePeriod = 'past'"
                    wire:click="setPeriodFilter('past')"
                    :class="activePeriod === 'past' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#2C2523]'"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 min-h-[44px] text-sm font-semibold transition-colors cursor-pointer">
                Eventos Passados ({{ $scheduleSummary['pastCount'] }})
            </button>

            <button type="button"
                    @click="activePeriod = 'all'"
                    wire:click="setPeriodFilter('all')"
                    :class="activePeriod === 'all' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#2C2523]'"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 min-h-[44px] text-sm font-semibold transition-colors cursor-pointer">
                Todos ({{ $scheduleSummary['totalEntriesCount'] }})
            </button>
        </div>

        {{-- Search & Status Filters --}}
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="schedule_search" class="sr-only">Buscar na agenda</label>
                <input id="schedule_search"
                       type="search"
                       wire:model.live.debounce.350ms="search"
                       placeholder="Buscar por nome do casal, cidade, local ou nota..."
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
            </div>

            <div>
                <label for="schedule_status_filter" class="sr-only">Filtrar por status</label>
                <select id="schedule_status_filter"
                        wire:model.live="statusFilter"
                        class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    <option value="all">Todos os status</option>
                    @foreach ($statusLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Chronological Agenda Cards --}}
        <div wire:loading.class="opacity-60 transition-opacity" wire:target="setPeriodFilter,search,statusFilter">
            @if ($scheduleEntries->isEmpty())
                <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-8 text-center space-y-3">
                    <p class="font-serif text-lg font-semibold text-[#2C2523]">
                        @if ($scheduleSummary['totalEntriesCount'] === 0)
                            Nenhum casamento na sua agenda ainda
                        @elseif ($periodFilter === 'past' && blank($search) && $statusFilter === 'all')
                            Nenhum evento passado encontrado
                        @else
                            Nenhum casamento encontrado para este filtro
                        @endif
                    </p>
                    <p class="text-sm text-[#6E625F] max-w-md mx-auto">
                        @if ($scheduleSummary['totalEntriesCount'] === 0)
                            Cadastre seus próximos casamentos manualmente com data, local, contato e valores do contrato, mesmo que os noivos ainda não usem a plataforma.
                        @else
                            Tente alterar o período selecionado, limpar a busca ou escolher outro status.
                        @endif
                    </p>
                    @if ($scheduleSummary['totalEntriesCount'] === 0)
                        <button type="button"
                                @click="openNewSchedule()"
                                class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                            Cadastrar Primeiro Casamento
                        </button>
                    @endif
                </div>
            @else
            <div class="space-y-4">
                @foreach ($scheduleEntries as $entry)
                    @php
                        $statusBadgeClasses = match ($entry->status) {
                            \App\Models\WeddingVendor::STATUS_PAID => 'bg-[#EBF3F0] text-[#5A8F7B]',
                            \App\Models\WeddingVendor::STATUS_CONTRACTED => 'bg-[#F9EBE6] text-[#C25E38]',
                            default => 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]',
                        };
                        $resolvedDate = $entry->resolvedEventDate();
                        $resolvedVenue = $entry->resolvedEventVenue();
                        $whatsappHref = $entry->whatsappUrl();
                        $phoneHref = $entry->phoneHref();
                    @endphp

                    <article wire:key="schedule-entry-{{ $entry->id }}"
                             class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $entry->isPlatformCouple() ? 'bg-[#EBF3F0] text-[#5A8F7B]' : 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]' }}">
                                        {{ $entry->originLabel() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses }}">
                                        {{ $entry->statusLabel() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-lg bg-[#FBF9F7] border border-[#EAE0DA] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                                        {{ $entry->categoryLabel() }}
                                    </span>
                                </div>

                                <h2 class="font-serif text-lg font-semibold text-[#2C2523]">
                                    {{ $entry->timelineHeadline() }}
                                </h2>

                                <p class="text-xs text-[#6E625F] flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span>{{ $resolvedDate?->format('d/m/Y') ?? 'Data a definir' }}</span>
                                    @if (filled($resolvedVenue))
                                        <span>•</span>
                                        <span>{{ $resolvedVenue }}</span>
                                    @endif
                                    @if (filled($entry->contact_name) || filled($entry->contact_phone) || filled($entry->contact_email))
                                        <span>•</span>
                                        <span>
                                            Contato:
                                            @if (filled($entry->contact_name))
                                                <strong class="font-medium text-[#2C2523]">{{ $entry->contact_name }}</strong>
                                            @endif
                                            @if (filled($entry->contact_phone))
                                                <span>({{ $entry->contact_phone }})</span>
                                            @endif
                                            @if (filled($entry->contact_email))
                                                <span>• {{ $entry->contact_email }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                @if ($whatsappHref !== null)
                                    <a href="{{ $whatsappHref }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center justify-center rounded-xl border border-[#5A8F7B]/40 bg-[#EBF3F0] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#5A8F7B] hover:bg-[#5A8F7B] hover:text-white transition-colors">
                                        WhatsApp
                                    </a>
                                @elseif ($phoneHref !== null)
                                    <a href="{{ $phoneHref }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                                        Ligar
                                    </a>
                                @endif

                                @if ($entry->isPlatformCouple())
                                    <a href="{{ route('vendor.wedding.access', $entry->invite_token) }}"
                                       wire:navigate
                                       class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                        {{ $entry->isCerimonialista() ? 'Painel Consolidado' : 'Ver Casamento' }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Contract Financial Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-3.5">
                            <div>
                                <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Contratado</span>
                                <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
                                    R$ {{ number_format((float) $entry->contracted_amount, 2, ',', '.') }}
                                </span>
                            </div>

                            <div>
                                <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Recebido</span>
                                <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#5A8F7B]">
                                    R$ {{ number_format((float) $entry->paid_amount, 2, ',', '.') }}
                                </span>
                            </div>

                            <div>
                                <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Saldo a Receber</span>
                                <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#C25E38]">
                                    R$ {{ number_format($entry->remainingAmount(), 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        @if (filled($entry->notes))
                            <p class="text-sm text-[#6E625F] leading-relaxed">
                                {{ $entry->notes }}
                            </p>
                        @endif

                        {{-- Optional Future Link Token + Actions --}}
                        <div class="pt-3 border-t border-[#EAE0DA] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 min-w-0 flex-1">
                                <span class="text-xs font-medium text-[#6E625F] shrink-0">
                                    {{ $entry->isPlatformCouple() ? 'Vínculo ativo:' : 'Código de vínculo futuro:' }}
                                </span>
                                <code class="rounded-lg border border-[#EAE0DA] bg-[#FBF9F7] px-2.5 py-1.5 text-xs font-mono text-[#2C2523] truncate select-all">
                                    {{ $entry->invite_token }}
                                </code>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                @foreach ($statusLabels as $statusKey => $statusLabel)
                                    @if ($entry->status !== $statusKey)
                                        <button type="button"
                                                wire:click="updateScheduleStatus({{ $entry->id }}, '{{ $statusKey }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="updateScheduleStatus({{ $entry->id }}, '{{ $statusKey }}')"
                                                class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-2 min-h-[44px] text-xs font-medium text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                            Marcar {{ $statusLabel }}
                                        </button>
                                    @endif
                                @endforeach

                                @if (! $entry->isPlatformCouple())
                                    <button type="button"
                                            x-data="{ token: @js($entry->invite_token), coupleNames: @js($entry->resolvedCoupleNames()) }"
                                            @click="
                                                (async () => {
                                                    try {
                                                        if (navigator.clipboard && window.isSecureContext) {
                                                            await navigator.clipboard.writeText(token);
                                                        } else {
                                                            const el = document.createElement('textarea');
                                                            el.value = token;
                                                            el.style.position = 'fixed';
                                                            el.style.opacity = '0';
                                                            document.body.appendChild(el);
                                                            el.select();
                                                            document.execCommand('copy');
                                                            document.body.removeChild(el);
                                                        }
                                                    } catch (e) {}
                                                    window.dispatchEvent(new CustomEvent('client-toast', { detail: { message: 'Código de vínculo de ' + coupleNames + ' copiado para a área de transferência.' } }));
                                                })()
                                            "
                                            class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer">
                                        Copiar Código
                                    </button>

                                    <button type="button"
                                            wire:click="regenerateEntryInviteToken({{ $entry->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="regenerateEntryInviteToken({{ $entry->id }})"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2 min-h-[44px] text-xs font-medium text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                        Novo Código
                                    </button>
                                @endif

                                @php
                                    $resolvedEntryNames = $entry->resolvedCoupleNames();
                                @endphp
                                <button type="button"
                                        @click="openEditSchedule(@js([
                                            'id' => $entry->id,
                                            'couple_names' => $resolvedEntryNames === 'Casal a definir' ? ($entry->couple_names ?? '') : $resolvedEntryNames,
                                            'event_date' => $resolvedDate?->format('Y-m-d'),
                                            'event_city' => $entry->resolvedEventCity(),
                                            'event_venue' => $resolvedVenue,
                                            'contact_name' => $entry->contact_name,
                                            'contact_phone' => $entry->contact_phone,
                                            'contact_email' => $entry->contact_email,
                                            'contracted_amount' => number_format((float) $entry->contracted_amount, 2, '.', ''),
                                            'paid_amount' => number_format((float) $entry->paid_amount, 2, '.', ''),
                                            'status' => $entry->status,
                                            'notes' => $entry->notes,
                                        ]))"
                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                    Editar
                                </button>

                                <button type="button"
                                        wire:click="deleteScheduleEntry({{ $entry->id }})"
                                        wire:confirm="{{ $entry->isPlatformCouple() && $entry->created_by_side === \App\Models\WeddingVendor::SIDE_COUPLE ? 'Desvincular este casamento da sua agenda? Os dados do casal serão mantidos.' : 'Tem certeza que deseja excluir este casamento da sua agenda?' }}"
                                        wire:loading.attr="disabled"
                                        wire:target="deleteScheduleEntry({{ $entry->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2 min-h-[44px] text-xs font-medium text-red-700 hover:bg-red-50 hover:border-red-300 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                    {{ $entry->isPlatformCouple() && $entry->created_by_side === \App\Models\WeddingVendor::SIDE_COUPLE ? 'Desvincular' : 'Excluir' }}
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    {{-- New / Edit Schedule Entry Modal --}}
    <div x-cloak
         x-show="$wire.showScheduleModal"
         @click.self="closeScheduleModal()"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#2C2523]/50 p-0 sm:p-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="schedule-modal-title">
        <div class="w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-xl space-y-5">
            <div class="flex items-center justify-between gap-3">
                <h2 id="schedule-modal-title"
                    x-text="$wire.editingEntryId !== null ? 'Editar Casamento na Agenda' : 'Novo Casamento na Agenda'"
                    class="font-serif text-xl font-semibold text-[#2C2523]">
                    {{ $editingEntryId !== null ? 'Editar Casamento na Agenda' : 'Novo Casamento na Agenda' }}
                </h2>
                <button type="button"
                        @click="closeScheduleModal()"
                        aria-label="Fechar modal"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] text-[#6E625F] hover:text-[#2C2523] cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

                <form wire:submit="saveScheduleEntry" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="schedule_couple_names" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Nomes dos Noivos
                            </label>
                            <input id="schedule_couple_names"
                                   type="text"
                                   wire:model="couple_names"
                                   placeholder="Ex.: Ana & Lucas"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('couple_names')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule_event_date" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Data do Casamento
                            </label>
                            <input id="schedule_event_date"
                                   type="date"
                                   wire:model="event_date"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('event_date')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule_status" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Status do Contrato
                            </label>
                            <select id="schedule_status"
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
                            <label for="schedule_event_city" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Cidade / UF do Evento
                            </label>
                            <input id="schedule_event_city"
                                   type="text"
                                   wire:model="event_city"
                                   placeholder="Ex.: São Paulo - SP"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('event_city')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule_event_venue" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Local / Espaço (opcional)
                            </label>
                            <input id="schedule_event_venue"
                                   type="text"
                                   wire:model="event_venue"
                                   placeholder="Ex.: Fazenda Vila Rica"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('event_venue')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule_contact_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Pessoa de Contato
                            </label>
                            <input id="schedule_contact_name"
                                   type="text"
                                   wire:model="contact_name"
                                   placeholder="Ex.: Ana Paula"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contact_name')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule_contact_phone" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Telefone / WhatsApp
                            </label>
                            <input id="schedule_contact_phone"
                                   type="text"
                                   wire:model="contact_phone"
                                   placeholder="(11) 99999-8888"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contact_phone')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="schedule_contact_email" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                E-mail de Contato (opcional)
                            </label>
                            <input id="schedule_contact_email"
                                   type="email"
                                   wire:model="contact_email"
                                   placeholder="noivos@exemplo.com.br"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('contact_email')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="schedule_contracted_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Valor Contratado (R$)
                            </label>
                            <input id="schedule_contracted_amount"
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
                            <label for="schedule_paid_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Valor Já Recebido (R$)
                            </label>
                            <input id="schedule_paid_amount"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="paid_amount"
                                   class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm tabular-nums text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                            @error('paid_amount')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="schedule_notes" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Observações do Contrato
                            </label>
                            <textarea id="schedule_notes"
                                      rows="3"
                                      wire:model="notes"
                                      placeholder="Horários combinados, entregas, parcelas ou pedidos especiais dos noivos..."
                                      class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none"></textarea>
                            @error('notes')
                                <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-2">
                        <button type="button"
                                @click="closeScheduleModal()"
                                class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-5 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveScheduleEntry"
                                class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="saveScheduleEntry"
                                  x-text="$wire.editingEntryId !== null ? 'Salvar Alterações' : 'Adicionar à Agenda'">
                                {{ $editingEntryId !== null ? 'Salvar Alterações' : 'Adicionar à Agenda' }}
                            </span>
                            <span wire:loading wire:target="saveScheduleEntry">Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>

