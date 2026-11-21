# Spec 05 — Inspirações do Pinterest (Web, Extensão Chrome MV3, PWA Share Target e Feature Flag)

- **Status:** Concluída (`arquivadas`)
- **Dependências:** [02-noivos-orcamento-e-contratos-autonomos](02-noivos-orcamento-e-contratos-autonomos.md)

## 1. Objetivo
Permitir que a noiva centralize referências do Pinterest por especialidade (**Decoração**, **Maquiagem** e **Penteado**) de forma prática (Web, Extensão Chrome e App Celular via PWA) e compartilhe o moodboard segmentado com **Decorador**, **Maquiadora** e **Penteadista**.

## 2. Modelagem de Banco (PostgreSQL 18)
### Tabela `pinterest_inspirations`
- `id`, `wedding_id` (FK `weddings`, cascade)
- `category` (`string`, index) — `'decoracao' | 'maquiagem' | 'penteado'`
- `type` (`string`, default `'pin'`) — `'pin' | 'board'`
- `title` (`string`)
- `pinterest_url` (`text`)
- `image_url` (`text`, `nullable`)
- `bride_notes` (`text`, `nullable`)
- `source` (`string`, default `'web'`) — `'web' | 'chrome_extension' | 'pwa_share'`
- `timestamps`

## 3. Regras de Negócio e Canais de Captura
1. **Sem OAuth Burocrático do Pinterest:**
   - **Canal 1 (Painel Web `/inspiracoes`):** noiva cola link de Pin ou Pasta, escolhe categoria e escreve nota.
   - **Canal 2 (Extensão do Chrome - Manifest V3):** pasta `chrome-extension/` (`manifest.json`, `content.js`, `content.css`, `popup.html`, `popup.js`) injeta botão flutuante **"Salvar no Nosso Voto"** sobre os pins no domínio `pinterest.com` / `pinterest.com.br` e envia via `POST /api/v1/inspirations/clip` autenticado pelo `api_token` da noiva (com CORS habilitado).
   - **Canal 3 (PWA Web Share Target `/compartilhar-pin`):** recebe `title`, `text` e `url` do menu *Compartilhar* do app oficial do Pinterest no celular, extrai a URL e salva com 1 toque na categoria escolhida.
2. **Acesso dos Profissionais e Feature Flag Liga/Desliga:**
   - Em `/acesso/profissional/{token}`, o **Decorador** visualiza a categoria `decoracao`; **Maquiadora** e **Penteadista** visualizam `maquiagem` e `penteado`.
   - Verificação pela flag global `PlatformSetting::current()->paywall_pinterest_inspirations_enabled`:
     - Quando **`false` (padrão MVP):** acesso 100% liberado para profissionais autorizados (`is_pinterest_authorized = true`), sem paywall.
     - Quando **`true`:** exige `has_paid_pinterest_unlock = true` no `WeddingVendor` (exibindo preview com contagem de referências e botão de desbloqueio caso ainda não pago).

## 4. Interface & Rotas
- `/inspiracoes` (`PinterestBoard`): abas por categoria, cadastro manual, exibição do `api_token` e instruções da extensão.
- `/compartilhar-pin` (`PwaShareTarget`): tela mobile-first otimizada para compartilhamento vindo do celular.
- `POST /api/v1/inspirations/clip` (`Api\InspirationClipController`): endpoint JSON para a Extensão Chrome.
- `/acesso/profissional/{token}` (`VendorWeddingAccess`): moodboard segmentado para Decorador, Maquiadora e Penteadista.

## 5. Critérios de Aceite
- [x] Noiva salva pins/pastas nas 3 categorias via web, via API `POST /api/v1/inspirations/clip` e via `/compartilhar-pin`.
- [x] Extensão Chrome (Manifest V3) injeta o botão interativo sobre pins do Pinterest e autentica via `api_token`.
- [x] Com `paywall_pinterest_inspirations_enabled = false`, Decorador/Maquiadora/Penteadista veem as referências diretamente.
- [x] Ao ligar a flag no Admin (`true`), o acesso passa a respeitar `has_paid_pinterest_unlock`.

> **Evolução Planejada:** A inclusão da categoria adicional `fotografia` (inspirações de fotos compartilhadas com o **Fotógrafo**) está especificada em [10-inspiracoes-pinterest-categoria-fotografia](../ativas/10-inspiracoes-pinterest-categoria-fotografia.md).

