# Spec 07 — Fornecedor: Portfólio Pago e Sincronização com o Instagram

- **Status:** Ativa
- **Dependências:** [06-vitrine-regional-destaque-randomico-e-banner-ciclos](06-vitrine-regional-destaque-randomico-e-banner-ciclos.md)

## 1. Objetivo
Transformar o **Portfólio do Fornecedor** em uma funcionalidade paga de alto valor percebido na Vitrine, permitindo que o profissional exiba uma galeria visual de trabalhos realizados com **sincronização automática do feed do Instagram** (além de upload manual).

## 2. Modelagem de Banco (PostgreSQL 18)
### Alteração em `vendor_profiles`
- `has_paid_portfolio` (`boolean`, default `false`, index)
- `portfolio_expires_at` (`timestamp`, `nullable`)
- `instagram_user_id` (`string`, `nullable`)
- `instagram_access_token` (`text`, `nullable`) — armazenado criptografado (`encrypted` cast no Laravel)
- `instagram_token_expires_at` (`timestamp`, `nullable`)
- `instagram_last_synced_at` (`timestamp`, `nullable`)

### Nova tabela `vendor_portfolio_items`
- `id`, `vendor_profile_id` (FK `vendor_profiles`, cascade)
- `source` (`string`, default `'instagram'`) — `'instagram' | 'manual'`
- `instagram_media_id` (`string`, `nullable`) — único por perfil (`unique(['vendor_profile_id', 'instagram_media_id'])`) para evitar duplicatas na ressincronização
- `media_type` (`string`, default `'IMAGE'`) — `'IMAGE' | 'CAROUSEL_ALBUM' | 'VIDEO'`
- `media_url` (`text`)
- `thumbnail_url` (`text`, `nullable`)
- `permalink` (`text`, `nullable`)
- `caption` (`text`, `nullable`)
- `display_order` (`integer`, default `0`)
- `is_visible` (`boolean`, default `true`)
- `published_at` (`timestamp`, `nullable`)
- `timestamps`

## 3. Regras de Negócio
1. **Diferenciação Gratuito vs. Portfólio Pago:**
   - Fornecedor gratuito exibe apenas cartão básico (nome, categoria, cidade/UF, descrição curta e WhatsApp).
   - Quando `has_paid_portfolio = true` e `portfolio_expires_at > now()`, o perfil público na Vitrine (`/vitrine/{slug}` ou modal de perfil) exibe a **galeria completa de Portfólio** com fotos, legendas e link opcional para o post original.
2. **Conexão e Sincronização com o Instagram:**
   - Fluxo OAuth com a API oficial do Instagram / Meta Graph (`instagram_business_basic` / `user_profile,user_media`) via service dedicado `App\Services\InstagramSyncService`.
   - Ao clicar em **"Sincronizar Agora"** (ou via job agendado no Laravel Scheduler), busca as mídias recentes do perfil conectado, faz `updateOrCreate` usando `instagram_media_id` e atualiza `instagram_last_synced_at`.
   - Fornecedor pode ocultar/exibir ou reordenar itens específicos do portfólio e complementar com uploads manuais.
   - Em ambiente de desenvolvimento/testes sem credenciais Meta configuradas, suportar driver/adapter simulado configurável via `.env`.

## 4. Interface & Rotas (Livewire 4)
- Aba **Portfólio & Instagram** em `/fornecedor/painel`:
  - Card de assinatura/ativação do módulo pago de Portfólio (`platform_settings.portfolio_monthly_price`).
  - Botão *"Conectar Conta do Instagram"*, botão *"Sincronizar Feed Agora"* com data da última sincronização e grade gerenciável (mostrar/ocultar/excluir/adicionar foto manual).
- Exibição da galeria no card/modal de detalhes do fornecedor em `/vitrine`.

## 5. Critérios de Aceite
- [ ] Fornecedor gratuito não exibe galeria de portfólio pública na Vitrine.
- [ ] Ao ativar o Portfólio Pago, o fornecedor consegue adicionar itens manuais e disparar a sincronização via `InstagramSyncService` sem duplicar itens já importados (`instagram_media_id` idempotente).
- [ ] `instagram_access_token` é persistido criptografado no PostgreSQL 18.
- [ ] Se a assinatura do portfólio expirar (`portfolio_expires_at < now()`), a galeria deixa de ser exibida na vitrine pública sem apagar os dados sincronizados.

