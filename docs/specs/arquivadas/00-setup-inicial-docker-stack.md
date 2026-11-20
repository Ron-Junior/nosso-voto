# Spec 00 — Setup Inicial Docker, Stack Base e Identidade Visual

- **Status:** Concluída (`arquivadas`)
- **Dependências:** Nenhuma

## 1. Objetivo
Estruturar o ambiente de desenvolvimento 100% containerizado com as versões mais recentes e estáveis da stack escolhida, sem instalar dependências no sistema host, deixando o esqueleto inicial pronto para receber as próximas specs.

## 2. Entregas Realizadas
- **Docker Compose & PHP 8.5:** `docker/php/Dockerfile` (`php:8.5-cli-alpine` com extensões `pdo_pgsql`, `pgsql`, `zip`, `intl`, `bcmath`, `mbstring`, `pcntl` e `composer:2`) e `docker-compose.yml` com os serviços `app` (`nossovoto_app`, porta `8000`) e `db` (`nossovoto_db` com `postgres:18-alpine`, porta `5435`).
- **Framework Base:** Laravel `13.43.0` + Livewire `v4.4.8` instalados em `/root/projetos/nosso-voto`.
- **Banco PostgreSQL 18:** `.env`, `.env.example` e `phpunit.xml` configurados para PostgreSQL (`nossovoto` e `nossovoto_test`), com migrations padrão iniciais executadas (`users`, `cache`, `jobs`).
- **Tokens Terracota & Areia:** `resources/css/app.css` configurado com as variáveis `@theme` (`#FBF9F7`, `#FFFFFF`, `#C25E38`, `#2C2523`).
- **Manifest PWA Base:** `public/manifest.json` configurado com `share_target` apontando para `/compartilhar-pin`.

## 3. Critérios de Aceite (Verificados)
- [x] Nenhum binário `php`, `composer` ou `psql` instalado no host.
- [x] Containers `nossovoto_app` e `nossovoto_db` saudáveis e rodando via `docker compose up -d`.
- [x] `php artisan test` padrão passando sobre o banco PostgreSQL 18 (`nossovoto_test`).
- [x] `http://localhost:8000` respondendo `HTTP 200`.

