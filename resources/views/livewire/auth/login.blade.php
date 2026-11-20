<div class="max-w-md mx-auto">
    <div class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 sm:p-8 shadow-sm space-y-6">
        <div class="space-y-2 text-center">
            <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                Acesso à Plataforma
            </span>
            <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                Bem-vindo de volta
            </h1>
            <p class="text-sm text-[#6E625F]">
                Entre com seu e-mail e senha para acessar seu painel.
            </p>
        </div>

        <form wire:submit="login" class="space-y-4">
            <div>
                <label for="login-email" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    E-mail
                </label>
                <input id="login-email"
                       type="email"
                       wire:model="email"
                       required
                       autofocus
                       autocomplete="email"
                       placeholder="voce@exemplo.com.br"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] placeholder:text-[#6E625F]/60 focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('email')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="login-password" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Senha
                </label>
                <input id="login-password"
                       type="password"
                       wire:model="password"
                       required
                       autocomplete="current-password"
                       placeholder="Sua senha"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] placeholder:text-[#6E625F]/60 focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('password')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2.5 text-sm text-[#6E625F] cursor-pointer select-none">
                <input type="checkbox"
                       wire:model="remember"
                       class="h-4 w-4 rounded border-[#EAE0DA] text-[#C25E38] focus:ring-[#C25E38]">
                <span>Manter sessão conectada</span>
            </label>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-3 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                <span wire:loading.remove wire:target="login">Entrar no Painel</span>
                <span wire:loading wire:target="login">Autenticando...</span>
            </button>
        </form>

        <p class="text-center text-sm text-[#6E625F]">
            Ainda não tem conta?
            <a href="{{ route('register') }}" class="font-semibold text-[#C25E38] hover:text-[#A84D2B] underline underline-offset-4">
                Criar conta agora
            </a>
        </p>
    </div>
</div>

