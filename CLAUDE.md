# Diretrizes do Projeto — **Nosso Voto**

Este repositório contém a plataforma **Nosso Voto** (`PHP 8.5` + `Laravel 13` + `Livewire 4` + `Tailwind CSS` + `PostgreSQL 18`).

## Ambiente 100% Docker

- **Não** instalar PHP, Composer, Node ou PostgreSQL no host.
- Executar todos os comandos de backend via container `app`:
  - `docker compose exec -T app php artisan test`
  - `docker compose exec -T app ./vendor/bin/pint --test`
  - `docker compose exec -T app php artisan migrate --force`
- Executar comandos de frontend via container `vite`:
  - `docker compose exec -T vite npm run build`

## Diretrizes de Interface & Dashboard

Consulte as regras completas em [docs/DIRETRIZES_DASHBOARD.md](docs/DIRETRIZES_DASHBOARD.md) e na skill `.agents/skills/mobile-first-layout/SKILL.md`:

- **Separação de Layouts:**
  - `/` é uma Landing Page dedicada (`resources/views/welcome.blade.php`), sem estrutura de dashboard.
  - `/login` e `/cadastro` usam `layouts::guest` (`resources/views/layouts/guest.blade.php`).
  - Áreas logadas usam `layouts::app` (`resources/views/layouts/app.blade.php`).
- **Sem menções a "Plano 100% Gratuito" no Dashboard:**
  - **Não** exibir selos, badges ou textos dizendo que o plano é *"100% Gratuito"* ou *"Sem mensalidades"* dentro do dashboard/painel logado. Essa comunicação fica restrita à Landing Page e ao fluxo de cadastro.
- **Paleta Terracota & Areia:** `#FBF9F7` (fundo), `#FFFFFF` (cards), `#EAE0DA` (bordas), `#C25E38` (primária / `#A84D2B` hover / `#F9EBE6` suave), `#2C2523` (texto / `#6E625F` secundário), `#5A8F7B` (status positivo).
- **Mobile-first:** alvos de toque `min-h-[44px]`/`min-h-[48px]`, cards escaneáveis no celular em vez de tabelas largas, números financeiros com `tabular-nums`.

## Organização dos Testes e Specs

- **Testes por Módulo:** organize testes em `tests/Feature/<Modulo>/...Test.php` (nunca crie um arquivo de teste por spec).
- **Fluxo de Specs:** siga a ordem em `docs/specs/README.md` e mova cada spec concluída de `docs/specs/ativas/` para `docs/specs/arquivadas/`.
