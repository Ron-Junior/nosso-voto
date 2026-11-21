# Especificações Evolutivas (Specs) — **Nosso Voto**

Esta pasta organiza todo o escopo do **Nosso Voto** em especificações técnicas menores para serem implementadas **gradualmente**, uma etapa por vez, mantendo foco de desenvolvedor solo e validação rápida.

## Estrutura de Pastas
- **`ativas/`** — Especificações prontas ou em andamento para as próximas entregas do MVP.
- **`arquivadas/`** — Especificações já implementadas, testadas e entregues no código.

## Fluxo de Trabalho
1. Escolha a próxima spec em `ativas/` respeitando a ordem de dependências abaixo.
2. Implemente as migrations, models, componentes Livewire, rotas e testes automatizados no ambiente Docker (`docker compose exec app ...`).
   - **Organização dos Testes:** os testes automatizados devem ser criados e mantidos **por módulo de domínio** (ex.: `tests/Feature/Auth/AuthenticationTest.php`, `tests/Feature/Dashboard/UserPanelTest.php`, `tests/Feature/Admin/PlatformSettingsTest.php`), e **nunca** com um arquivo de teste separado para cada spec.
3. Ao concluir todos os critérios de aceite da spec, atualize o status no cabeçalho e **mova o arquivo de `ativas/` para `arquivadas/`**.

---

## Mapa de Specs e Dependências

| Ordem | Spec | Pasta | Dependências | Foco Principal |
| :---: | :--- | :--- | :--- | :--- |
| `00` | [Setup Inicial Docker & Stack](arquivadas/00-setup-inicial-docker-stack.md) | `arquivadas` | — | PHP 8.5, Laravel 13, Livewire 4, PostgreSQL 18, Tailwind e PWA base |
| `01` | [Autenticação, Perfis e Design System](arquivadas/01-autenticacao-perfis-e-design-system.md) | `arquivadas` | `00` | `users`, `platform_settings`, auth multi-perfil e layout Terracota & Areia |
| `02` | [Noivos: Orçamento e Contratos Autônomos](arquivadas/02-noivos-orcamento-e-contratos-autonomos.md) | `arquivadas` | `01` | `weddings`, `wedding_vendors` (visão casal), orçamento vs. gasto e convites |
| `03` | [Cerimonialista: Visão Consolidada Colaborativa](arquivadas/03-cerimonialista-visao-consolidada-colaborativa.md) | `arquivadas` | `02` | Acesso por `invite_token` e colaboração nos fornecedores do casamento |
| `04` | [Fornecedor: Agenda e Noivos Autônomos](arquivadas/04-fornecedor-agenda-e-noivos-autonomos.md) | `arquivadas` | `02` | `vendor_profiles`, agenda de próximos noivos e cadastro sem depender do casal |
| `05` | [Inspirações Pinterest: Web, Chrome e PWA](arquivadas/05-inspiracoes-pinterest-extensao-chrome-e-pwa.md) | `arquivadas` | `02` | `pinterest_inspirations`, API + Extensão Chrome MV3 + PWA Share Target + Feature Flag |
| `06` | [Vitrine Regional, Destaque Randômico e Banner](ativas/06-vitrine-regional-destaque-randomico-e-banner-ciclos.md) | `ativas` | `04` | Busca por Cidade/UF, ordem randômica e Banner (máx. 5 vagas / ciclos de 2 meses) |
| `07` | [Fornecedor: Portfólio Pago e Sync Instagram](ativas/07-fornecedor-portfolio-pago-e-sync-instagram.md) | `ativas` | `06` | `vendor_portfolio_items`, paywall de portfólio e importação via Instagram API |
| `08` | [Lista de Presentes, Taxas e Carteira Pix](ativas/08-lista-de-presentes-taxas-e-carteira-pix.md) | `ativas` | `02` | `gift_items`, `gift_purchases`, `wallet_withdrawals`, repasse/absorção e saque Pix |
| `09` | [Noivos: Checklist e Lista do Grande Dia](ativas/09-noivos-checklist-e-itens-do-grande-dia.md) | `ativas` | `02` | `wedding_checklist_items`, controle do que precisa, está completando e já tem |
| `10` | [Inspirações Pinterest: Categoria Fotografia](ativas/10-inspiracoes-pinterest-categoria-fotografia.md) | `ativas` | `05` | Nova categoria `fotografia` (inspirações de fotos) para captura e moodboard do Fotógrafo |

