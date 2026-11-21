<div class="space-y-6">
    <x-toast :message="$statusMessage" />

    <header class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-1.5">
        <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
            Painel Administrativo
        </p>
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
            Configurações Globais da Plataforma
        </h1>
        <p class="text-sm text-[#6E625F]">
            Gerencie feature flags, taxa da lista de presentes e valores dos módulos de monetização para fornecedores.
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
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
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
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
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
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
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
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
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
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('banner_cycle_months')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="banner_max_slots" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Máximo de Vagas no Banner
                </label>
                <input id="banner_max_slots"
                       type="number"
                       min="1"
                       max="20"
                       wire:model="banner_max_slots"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
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
</div>

