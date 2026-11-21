# Spec 10 — Inspirações do Pinterest: Categoria Fotografia (Fotos)

- **Status:** Ativa
- **Dependências:** [05-inspiracoes-pinterest-extensao-chrome-e-pwa](../arquivadas/05-inspiracoes-pinterest-extensao-chrome-e-pwa.md)

## 1. Objetivo
Expandir o módulo de **Inspirações do Pinterest** para incluir a categoria **`fotografia`** (referências de fotos, poses, enquadramentos, *making of* e ensaios pré-wedding/casamento), permitindo que a noiva centralize inspirações fotográficas pelos três canais de captura e compartilhe o moodboard segmentado com o **Fotógrafo** (`category = 'fotografia'`).

## 2. Modelagem de Banco (PostgreSQL 18)
### Tabela `pinterest_inspirations` (sem alteração estrutural)
- A coluna `category` (`string`, index) passa a aceitar o novo valor `'fotografia'`, mantendo os existentes:
  - `'decoracao' | 'maquiagem' | 'penteado' | 'fotografia'`
- Nenhuma migration estrutural é necessária, pois `category` já utiliza `string`.
- Atualizar as constantes e labels em `App\Models\PinterestInspiration` (`CATEGORY_FOTOGRAFIA = 'fotografia'`, `CATEGORIES`, `categoryLabels()`) e os estados da `PinterestInspirationFactory`.

## 3. Regras de Negócio e Canais de Captura
1. **Captura de Inspirações de Fotos nos 3 Canais:**
   - **Canal 1 (Painel Web `/inspiracoes` — `PinterestBoard`):**
     - Incluir a aba e o seletor da categoria **Fotografia** (`fotografia`), com contagem específica, filtros por tipo/busca e cadastro/edição manual de Pins e Pastas.
     - Exibir fornecedores vinculados de `category = 'fotografia'` na seção de compartilhamento e controle de autorização (`is_pinterest_authorized`).
   - **Canal 2 (API REST `POST /api/v1/inspirations/clip` & Extensão Chrome MV3):**
     - `Api\InspirationClipController` passa a validar e aceitar `category = 'fotografia'`.
     - Atualizar `chrome-extension/content.js` (popover seletor de especialidade) e `chrome-extension/popup.html` (select de categoria padrão) para oferecer **Fotografia** ao lado de *Decoração*, *Maquiagem* e *Penteado*.
   - **Canal 3 (PWA Web Share Target `/compartilhar-pin` — `PwaShareTarget`):**
     - Incluir o botão de 1 toque **"Salvar em Fotografia"** e a opção **Fotografia** no seletor do formulário.
2. **Acesso Segmentado do Fotógrafo e Feature Flag Liga/Desliga:**
   - Atualizar `WeddingVendor::pinterestCategories()` para mapear `'fotografia' => [PinterestInspiration::CATEGORY_FOTOGRAFIA]`.
   - Em `/acesso/profissional/{token}` (`VendorWeddingAccess`), o **Fotógrafo** (`category = 'fotografia'`) visualiza exclusivamente as referências da categoria `fotografia`, sem acesso às categorias `decoracao`, `maquiagem` ou `penteado`.
   - O acesso respeita `is_pinterest_authorized` e a feature flag global `PlatformSetting::current()->paywall_pinterest_inspirations_enabled`:
     - Quando **`false` (padrão MVP):** acesso liberado diretamente se `is_pinterest_authorized = true`.
     - Quando **`true`:** exige `has_paid_pinterest_unlock = true` no `WeddingVendor` (exibindo preview com contagem de referências e botão de desbloqueio caso ainda não pago).

## 4. Interface & Rotas
- `/inspiracoes` (`PinterestBoard`): aba **Fotografia**, cadastro manual, filtros e compartilhamento com o Fotógrafo.
- `/compartilhar-pin` (`PwaShareTarget`): botão de 1 toque **"Salvar em Fotografia"**.
- `POST /api/v1/inspirations/clip` (`Api\InspirationClipController`) e `chrome-extension/`: suporte completo à categoria `fotografia`.
- `/acesso/profissional/{token}` (`VendorWeddingAccess`): exibição do moodboard de `fotografia` e paywall quando ativado pelo Admin.

## 5. Critérios de Aceite & Testes
- **Organização dos Testes:** atualizar a suíte existente em `tests/Feature/Wedding/PinterestInspirationsTest.php` (e incluir referência demo de `fotografia` no `DatabaseSeeder`).
- [ ] Noiva salva pins e pastas na categoria `fotografia` pelo painel web (`/inspiracoes`), via API `POST /api/v1/inspirations/clip` e via PWA Share Target (`/compartilhar-pin`).
- [ ] Extensão Chrome (Manifest V3) disponibiliza a especialidade **Fotografia** no popover e no popup (`content.js` e `popup.html`).
- [ ] Em `/acesso/profissional/{token}`, o **Fotógrafo** visualiza apenas referências de `fotografia` (nunca `decoracao`, `maquiagem` ou `penteado`).
- [ ] Acesso do Fotógrafo respeita `is_pinterest_authorized` e a feature flag global `paywall_pinterest_inspirations_enabled` (`false` liberado / `true` exige `has_paid_pinterest_unlock = true`).

