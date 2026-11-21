<div class="max-w-lg mx-auto" x-data="{ role: $wire.entangle('role') }">
    <div class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 sm:p-8 shadow-sm space-y-6">
        <div class="space-y-2 text-center">
            <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                Novo Cadastro
            </span>
            <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                Crie sua conta no Nosso Voto
            </h1>
            <p class="text-sm text-[#6E625F]">
                Escolha seu perfil para começar a organizar ou atender casamentos.
            </p>
        </div>

        {{-- Role Selector --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Tipo de perfil">
            <button type="button"
                    @click="role = 'couple'"
                    :aria-pressed="(role === 'couple').toString()"
                    :class="role === 'couple' ? 'border-[#C25E38] bg-[#F9EBE6]/70 ring-1 ring-[#C25E38]' : 'border-[#EAE0DA] bg-[#FBF9F7] hover:border-[#C25E38]/50'"
                    class="flex flex-col items-start gap-1.5 rounded-2xl border p-4 min-h-[48px] text-left transition-all cursor-pointer">
                <div class="flex w-full items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-[#2C2523]">Sou Noivo/Noiva</span>
                    <span class="inline-flex items-center rounded-full bg-[#EBF3F0] px-2 py-0.5 text-[11px] font-semibold text-[#5A8F7B]">
                        Gratuito
                    </span>
                </div>
                <span class="text-xs text-[#6E625F]">
                    Orçamento, fornecedores, Pinterest e lista de presentes.
                </span>
            </button>

            <button type="button"
                    @click="role = 'vendor'"
                    :aria-pressed="(role === 'vendor').toString()"
                    :class="role === 'vendor' ? 'border-[#C25E38] bg-[#F9EBE6]/70 ring-1 ring-[#C25E38]' : 'border-[#EAE0DA] bg-[#FBF9F7] hover:border-[#C25E38]/50'"
                    class="flex flex-col items-start gap-1.5 rounded-2xl border p-4 min-h-[48px] text-left transition-all cursor-pointer">
                <div class="flex w-full items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-[#2C2523]">Sou Fornecedor</span>
                    <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-2 py-0.5 text-[11px] font-semibold text-[#C25E38]">
                        Profissional
                    </span>
                </div>
                <span class="text-xs text-[#6E625F]">
                    Agenda de próximos noivos, vitrine regional e portfólio.
                </span>
            </button>
        </div>

        @error('role')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror

        <form wire:submit="register" class="space-y-4">
            <div>
                <label for="register-name"
                       x-text="role === 'vendor' ? 'Seu nome ou nome do estúdio/empresa' : 'Seu nome ou nome do casal'"
                       class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    {{ $role === 'vendor' ? 'Seu nome ou nome do estúdio/empresa' : 'Seu nome ou nome do casal' }}
                </label>
                <input id="register-name"
                       type="text"
                       wire:model="name"
                       required
                       autocomplete="name"
                       :placeholder="role === 'vendor' ? 'Ex.: Ateliê Terracota Eventos' : 'Ex.: Ana & Lucas'"
                       placeholder="{{ $role === 'vendor' ? 'Ex.: Ateliê Terracota Eventos' : 'Ex.: Ana & Lucas' }}"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] placeholder:text-[#6E625F]/60 focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('name')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="register-email" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    E-mail
                </label>
                <input id="register-email"
                       type="email"
                       wire:model="email"
                       required
                       autocomplete="email"
                       placeholder="voce@exemplo.com.br"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] placeholder:text-[#6E625F]/60 focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('email')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="register-password" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Senha
                    </label>
                    <input id="register-password"
                           type="password"
                           wire:model="password"
                           required
                           autocomplete="new-password"
                           placeholder="Mínimo 8 caracteres"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] placeholder:text-[#6E625F]/60 focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    @error('password')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="register-password-confirmation" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Confirmar Senha
                    </label>
                    <input id="register-password-confirmation"
                           type="password"
                           wire:model="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="Repita a senha"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] placeholder:text-[#6E625F]/60 focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                </div>
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="register"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                <span wire:loading.remove wire:target="register"
                      x-text="role === 'vendor' ? 'Criar Conta de Fornecedor' : 'Criar Conta Gratuita'">
                    {{ $role === 'vendor' ? 'Criar Conta de Fornecedor' : 'Criar Conta Gratuita' }}
                </span>
                <span wire:loading wire:target="register">Criando conta...</span>
            </button>
        </form>

        <p class="text-center text-sm text-[#6E625F]">
            Já possui uma conta?
            <a href="{{ route('login') }}" wire:navigate.hover class="font-semibold text-[#C25E38] hover:text-[#A84D2B] underline underline-offset-4">
                Entrar agora
            </a>
        </p>
    </div>
</div>

