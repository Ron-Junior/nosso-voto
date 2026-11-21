<div class="space-y-6"
     x-data="{
         activeInspCat: $wire.entangle('inspirationCategoryFilter'),
         openNewVendor() {
             $wire.editingVendorId = null;
             $wire.vendor_name = '';
             $wire.category = 'buffet';
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
             $wire.category = v.category || 'buffet';
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
     @keydown.escape.window="if ($wire.showVendorModal) closeVendorModal()">
    <x-toast :message="$statusMessage" />

    {{-- Wedding Event Header --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-xl bg-[#F9EBE6] px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                        {{ $isCerimonialista ? 'Visão Consolidada do Cerimonialista' : 'Acesso do Profissional • '.$accessVendor->categoryLabel() }}
                    </span>
                    <span class="text-xs text-[#6E625F]">
                        Profissional: <strong class="font-medium text-[#2C2523]">{{ $accessVendor->vendor_name }}</strong>
                    </span>
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
            </div>

            @if ($isCerimonialista)
                <div class="flex shrink-0">
                    <button type="button"
                            @click="openNewVendor()"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                        + Adicionar Fornecedor para o Casal
                    </button>
                </div>
            @endif
        </div>
    </section>

    @if ($isCerimonialista)
        {{-- Executive Summary Cards --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumo executivo do casamento">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total de Fornecedores</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#2C2523]">
                    {{ $totalVendorsCount }}
                </p>
                <p class="text-xs text-[#6E625F]">
                    {{ $totalVendorsCount === 1 ? 'Serviço vinculado ao evento' : 'Serviços vinculados ao evento' }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Em Cotação</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#2C2523]">
                    {{ $quotingCount }}
                </p>
                <p class="text-xs text-[#6E625F]">Aguardando definição</p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Contratados</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#C25E38]">
                    {{ $contractedCount }}
                </p>
                <p class="text-xs text-[#6E625F]">Com contrato em andamento</p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Quitados</p>
                <p class="font-serif text-2xl font-semibold tabular-nums text-[#5A8F7B]">
                    {{ $paidCount }}
                </p>
                <p class="text-xs text-[#6E625F]">Pagamentos integralizados</p>
            </article>
        </section>

        {{-- Consolidated Contract Financial Summary --}}
        <section class="grid gap-4 sm:grid-cols-3" aria-label="Resumo financeiro dos contratos">
            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total Fechado nos Contratos</p>
                <p class="font-serif text-xl font-semibold tabular-nums text-[#2C2523]">
                    R$ {{ number_format($totalContracted, 2, ',', '.') }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Total Já Pago</p>
                <p class="font-serif text-xl font-semibold tabular-nums text-[#5A8F7B]">
                    R$ {{ number_format($totalPaid, 2, ',', '.') }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 shadow-sm space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Saldo a Pagar</p>
                <p class="font-serif text-xl font-semibold tabular-nums text-[#2C2523]">
                    R$ {{ number_format($remainingToPay, 2, ',', '.') }}
                </p>
            </article>
        </section>

        {{-- Consolidated Vendor List & Collaborative Actions --}}
        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                        Fornecedores do Casamento
                    </h2>
                    <p class="text-sm text-[#6E625F]">
                        Acompanhe contatos, status e alinhamentos operacionais de toda a equipe contratada pelo casal.
                    </p>
                </div>

                <button type="button"
                        @click="openNewVendor()"
                        class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer shrink-0">
                    + Adicionar Fornecedor para o Casal
                </button>
            </div>

            {{-- Search & Filters --}}
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label for="cerimonial_vendor_search" class="sr-only">Buscar fornecedor</label>
                    <input id="cerimonial_vendor_search"
                           type="search"
                           wire:model.live.debounce.350ms="search"
                           placeholder="Buscar por nome, contato ou nota..."
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                </div>

                <div>
                    <label for="cerimonial_category_filter" class="sr-only">Filtrar por categoria</label>
                    <select id="cerimonial_category_filter"
                            wire:model.live="categoryFilter"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        <option value="all">Todas as categorias</option>
                        @foreach ($categoryLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cerimonial_status_filter" class="sr-only">Filtrar por status</label>
                    <select id="cerimonial_status_filter"
                            wire:model.live="statusFilter"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        <option value="all">Todos os status</option>
                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div wire:loading.class="opacity-60 transition-opacity" wire:target="search,categoryFilter,statusFilter">
            @if ($vendors->isEmpty())
                <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-8 text-center space-y-3">
                    <p class="font-serif text-lg font-semibold text-[#2C2523]">
                        Nenhum fornecedor encontrado para este filtro
                    </p>
                    <p class="text-sm text-[#6E625F] max-w-md mx-auto">
                        Limpe a busca, escolha outro status/categoria ou cadastre um novo fornecedor para ajudar os noivos.
                    </p>
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
                            $whatsappUrl = $vendor->whatsappUrl();
                            $phoneHref = $vendor->phoneHref();
                        @endphp

                        <article wire:key="cerimonial-vendor-{{ $vendor->id }}"
                                 class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-5 space-y-4">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-lg bg-[#FBF9F7] border border-[#EAE0DA] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                                            {{ $vendor->categoryLabel() }}
                                        </span>
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses }}">
                                            {{ $vendor->statusLabel() }}
                                        </span>
                                        @if ($vendor->id === $accessVendor->id)
                                            <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-1 text-xs font-semibold text-[#C25E38]">
                                                Você
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

                                @if ($whatsappUrl !== null || $phoneHref !== null)
                                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                                        @if ($whatsappUrl !== null)
                                            <a href="{{ $whatsappUrl }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#EBF3F0] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#5A8F7B] hover:bg-[#5A8F7B] hover:text-white transition-colors">
                                                WhatsApp
                                            </a>
                                        @endif

                                        @if ($phoneHref !== null)
                                            <a href="{{ $phoneHref }}"
                                               class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                                                Ligar
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Contract Values --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-3.5">
                                <div>
                                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Teto Previsto</span>
                                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
                                        R$ {{ number_format((float) $vendor->estimated_amount, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Fechado</span>
                                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
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

                            {{-- Quick Status Actions & Edit --}}
                            <div class="pt-3 border-t border-[#EAE0DA] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-medium text-[#6E625F] mr-1">Atualizar status:</span>
                                    @foreach ($statusLabels as $statusKey => $statusLabel)
                                        <button type="button"
                                                wire:click="updateVendorStatus({{ $vendor->id }}, '{{ $statusKey }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="updateVendorStatus"
                                                class="inline-flex items-center justify-center rounded-xl px-3 py-2 min-h-[44px] text-xs font-semibold transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait {{ $vendor->status === $statusKey ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38]' }}">
                                            {{ $statusLabel }}
                                        </button>
                                    @endforeach
                                </div>

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
                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer shrink-0">
                                    Editar Fornecedor
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
            </div>
        </section>

        {{-- Collaborative Create/Edit Vendor Modal --}}
        <div x-cloak
             x-show="$wire.showVendorModal"
             @click.self="closeVendorModal()"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#2C2523]/50 p-0 sm:p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="cerimonial-vendor-modal-title">
            <div class="w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="cerimonial-vendor-modal-title"
                        x-text="$wire.editingVendorId !== null ? 'Editar Fornecedor do Casal' : 'Adicionar Fornecedor para o Casal'"
                        class="font-serif text-xl font-semibold text-[#2C2523]">
                        {{ $editingVendorId !== null ? 'Editar Fornecedor do Casal' : 'Adicionar Fornecedor para o Casal' }}
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
                                <label for="collab_vendor_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Nome do Fornecedor ou Empresa
                                </label>
                                <input id="collab_vendor_name"
                                       type="text"
                                       wire:model="vendor_name"
                                       placeholder="Ex.: Som & Luz Eventos"
                                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                @error('vendor_name')
                                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="collab_vendor_category" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Categoria
                                </label>
                                <select id="collab_vendor_category"
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
                                <label for="collab_vendor_status" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Status do Contrato
                                </label>
                                <select id="collab_vendor_status"
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
                                <label for="collab_vendor_estimated_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Teto Previsto (R$)
                                </label>
                                <input id="collab_vendor_estimated_amount"
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
                                <label for="collab_vendor_contracted_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Valor Fechado (R$)
                                </label>
                                <input id="collab_vendor_contracted_amount"
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
                                <label for="collab_vendor_paid_amount" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Valor Já Pago (R$)
                                </label>
                                <input id="collab_vendor_paid_amount"
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
                                <label for="collab_vendor_contact_name" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Responsável / Contato (opcional)
                                </label>
                                <input id="collab_vendor_contact_name"
                                       type="text"
                                       wire:model="contact_name"
                                       placeholder="Ex.: Mariana"
                                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                @error('contact_name')
                                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="collab_vendor_contact_phone" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    Telefone / WhatsApp (opcional)
                                </label>
                                <input id="collab_vendor_contact_phone"
                                       type="text"
                                       wire:model="contact_phone"
                                       placeholder="(11) 99999-9999"
                                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                                @error('contact_phone')
                                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="collab_vendor_contact_email" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                    E-mail (opcional)
                                </label>
                                <input id="collab_vendor_contact_email"
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
                            <label for="collab_vendor_notes" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                                Observações e Alinhamentos (opcional)
                            </label>
                            <textarea id="collab_vendor_notes"
                                      rows="3"
                                      wire:model="notes"
                                      placeholder="Horário de montagem, requisitos técnicos, briefing..."
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
                                      x-text="$wire.editingVendorId !== null ? 'Salvar Alterações' : 'Adicionar Fornecedor'">
                                    {{ $editingVendorId !== null ? 'Salvar Alterações' : 'Adicionar Fornecedor' }}
                                </span>
                                <span wire:loading wire:target="saveVendor">Salvando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    @else
        {{-- Restricted Single-Vendor Access View (non-Cerimonialista categories) --}}
        <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
            @php
                $statusBadgeClasses = match ($accessVendor->status) {
                    \App\Models\WeddingVendor::STATUS_PAID => 'bg-[#EBF3F0] text-[#5A8F7B]',
                    \App\Models\WeddingVendor::STATUS_CONTRACTED => 'bg-[#F9EBE6] text-[#C25E38]',
                    default => 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]',
                };
            @endphp

            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-lg bg-[#FBF9F7] border border-[#EAE0DA] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                        {{ $accessVendor->categoryLabel() }}
                    </span>
                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses }}">
                        {{ $accessVendor->statusLabel() }}
                    </span>
                </div>

                <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                    {{ $accessVendor->vendor_name }}
                </h2>

                @if (filled($accessVendor->contact_name) || filled($accessVendor->contact_phone) || filled($accessVendor->contact_email))
                    <p class="text-xs text-[#6E625F] flex flex-wrap items-center gap-x-3 gap-y-1">
                        @if (filled($accessVendor->contact_name))
                            <span>Contato: <strong class="font-medium text-[#2C2523]">{{ $accessVendor->contact_name }}</strong></span>
                        @endif
                        @if (filled($accessVendor->contact_phone))
                            <span>{{ $accessVendor->contact_phone }}</span>
                        @endif
                        @if (filled($accessVendor->contact_email))
                            <span>{{ $accessVendor->contact_email }}</span>
                        @endif
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4">
                <div>
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Fechado</span>
                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
                        R$ {{ number_format((float) $accessVendor->contracted_amount, 2, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Valor Pago</span>
                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#5A8F7B]">
                        R$ {{ number_format((float) $accessVendor->paid_amount, 2, ',', '.') }}
                    </span>
                </div>

                <div>
                    <span class="block text-[11px] font-medium uppercase tracking-wider text-[#6E625F]">Saldo a Receber</span>
                    <span class="mt-0.5 block text-sm font-semibold tabular-nums text-[#2C2523]">
                        R$ {{ number_format($accessVendor->remainingAmount(), 2, ',', '.') }}
                    </span>
                </div>
            </div>

            @if (filled($accessVendor->notes))
                <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Observações dos Noivos</p>
                    <p class="text-sm text-[#2C2523] leading-relaxed">{{ $accessVendor->notes }}</p>
                </div>
            @endif

            <p class="text-xs text-[#6E625F]">
                Este link exibe exclusivamente os detalhes do seu serviço para este casamento. A visão consolidada dos demais fornecedores é restrita à assessoria/cerimonialista do casal.
            </p>
        </section>

        @if ($supportsPinterestMoodboard)
            <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5" aria-label="Moodboard de inspirações do Pinterest">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                            Moodboard Segmentado • Pinterest
                        </p>
                        <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                            Inspirações da Noiva para {{ $accessVendor->categoryLabel() }}
                        </h2>
                        <p class="text-sm text-[#6E625F]">
                            Referências de
                            @if ($accessVendor->category === 'decoracao')
                                <strong class="font-medium text-[#2C2523]">Decoração</strong>
                            @else
                                <strong class="font-medium text-[#2C2523]">Maquiagem</strong> e <strong class="font-medium text-[#2C2523]">Penteado</strong>
                            @endif
                            salvas para o grande dia.
                        </p>
                    </div>

                    <div class="inline-flex items-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 text-xs font-semibold tabular-nums text-[#2C2523] shrink-0">
                        {{ $pinterestTotalCount }} {{ $pinterestTotalCount === 1 ? 'referência salva' : 'referências salvas' }}
                    </div>
                </div>

                @if (! $accessVendor->is_pinterest_authorized)
                    <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-6 text-center space-y-2">
                        <p class="font-serif text-base font-semibold text-[#2C2523]">
                            Acesso ao moodboard não autorizado no momento
                        </p>
                        <p class="text-sm text-[#6E625F] max-w-md mx-auto">
                            Os noivos ainda não liberaram ou pausaram temporariamente a visualização das inspirações do Pinterest para este link.
                        </p>
                    </div>
                @elseif ($requiresPinterestUnlock)
                    <div data-pinterest-paywall
                         class="rounded-2xl border border-[#C25E38]/40 bg-[#F9EBE6]/50 p-6 space-y-4">
                        <div class="space-y-2">
                            <span class="inline-flex items-center rounded-lg bg-[#FFFFFF] border border-[#C25E38]/30 px-2.5 py-1 text-xs font-semibold text-[#C25E38]">
                                Desbloqueio de Moodboard Ativo
                            </span>
                            <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                                {{ $pinterestTotalCount }} {{ $pinterestTotalCount === 1 ? 'referência pronta' : 'referências prontas' }} para você visualizar
                            </h3>
                            <p class="text-sm text-[#6E625F]">
                                A noiva salvou referências visuais e observações específicas para este casamento. Desbloqueie este moodboard para acessar os links dos pins e pastas.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($allowedPinterestCategories as $allowedCat)
                                <span class="inline-flex items-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3 py-1.5 text-xs font-semibold tabular-nums text-[#2C2523]">
                                    {{ $inspirationCategoryLabels[$allowedCat] ?? $allowedCat }}: {{ $pinterestCountsByCategory[$allowedCat] ?? 0 }}
                                </span>
                            @endforeach
                        </div>

                        <div>
                            <button type="button"
                                    wire:click="unlockPinterestMoodboard"
                                    wire:loading.attr="disabled"
                                    wire:target="unlockPinterestMoodboard"
                                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-3 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                <span wire:loading.remove wire:target="unlockPinterestMoodboard">Desbloquear Inspirações do Pinterest</span>
                                <span wire:loading wire:target="unlockPinterestMoodboard">Desbloqueando...</span>
                            </button>
                        </div>
                    </div>
                @elseif ($canViewPinterestInspirations)
                    @if (count($allowedPinterestCategories) > 1)
                        <div class="flex flex-wrap gap-2">
                            <button type="button"
                                    @click="activeInspCat = 'all'"
                                    wire:click="selectInspirationCategory('all')"
                                    :class="activeInspCat === 'all' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38]'"
                                    class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 min-h-[44px] text-xs font-semibold transition-colors cursor-pointer">
                                <span>Todas</span>
                                <span class="tabular-nums">({{ $pinterestTotalCount }})</span>
                            </button>

                            @foreach ($allowedPinterestCategories as $allowedCat)
                                <button type="button"
                                        @click="activeInspCat = '{{ $allowedCat }}'"
                                        wire:click="selectInspirationCategory('{{ $allowedCat }}')"
                                        :class="activeInspCat === '{{ $allowedCat }}' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38]'"
                                        class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 min-h-[44px] text-xs font-semibold transition-colors cursor-pointer">
                                    <span>{{ $inspirationCategoryLabels[$allowedCat] ?? $allowedCat }}</span>
                                    <span class="tabular-nums">({{ $pinterestCountsByCategory[$allowedCat] ?? 0 }})</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div wire:loading.class="opacity-60 transition-opacity" wire:target="selectInspirationCategory">
                    @if ($pinterestInspirations->isEmpty())
                        <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-6 text-center space-y-1.5">
                            <p class="font-serif text-base font-semibold text-[#2C2523]">
                                Nenhuma referência salva nesta especialidade ainda
                            </p>
                            <p class="text-sm text-[#6E625F]">
                                Assim que a noiva salvar pins ou pastas do Pinterest para este tema, eles aparecerão aqui automaticamente.
                            </p>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($pinterestInspirations as $inspiration)
                                <article wire:key="vendor-inspiration-{{ $inspiration->id }}"
                                         class="flex flex-col rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] overflow-hidden">
                                    @if (filled($inspiration->image_url))
                                        <div class="aspect-[4/3] w-full overflow-hidden bg-[#FFFFFF] border-b border-[#EAE0DA]">
                                            <img src="{{ $inspiration->image_url }}"
                                                 alt="{{ $inspiration->title }}"
                                                 loading="lazy"
                                                 class="h-full w-full object-cover">
                                        </div>
                                    @endif

                                    <div class="flex-1 p-4 flex flex-col justify-between gap-3">
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-1 text-xs font-semibold text-[#C25E38]">
                                                    {{ $inspiration->categoryLabel() }}
                                                </span>
                                                <span class="inline-flex items-center rounded-lg border border-[#EAE0DA] bg-[#FFFFFF] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                                                    {{ $inspiration->typeLabel() }}
                                                </span>
                                            </div>

                                            <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                                                {{ $inspiration->title }}
                                            </h3>

                                            @if (filled($inspiration->bride_notes))
                                                <p class="rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] p-3 text-sm text-[#6E625F] leading-relaxed">
                                                    {{ $inspiration->bride_notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <a href="{{ $inspiration->pinterest_url }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-xs font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                            Abrir no Pinterest
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                    </div>
                @endif
            </section>
        @endif
    @endif
</div>

