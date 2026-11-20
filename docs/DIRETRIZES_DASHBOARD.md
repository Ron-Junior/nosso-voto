# Diretrizes do Projeto & Boas Práticas de Dashboard — **Nosso Voto**

Este documento define os padrões obrigatórios de interface, arquitetura e qualidade para todas as telas e módulos do **Nosso Voto**.

---

## 1. Separação Estrita de Contextos (Landing vs. Guest vs. Dashboard)

1. **Página Inicial (`/`):**
   - Deve ser uma **Landing Page dedicada** (`resources/views/welcome.blade.php`), voltada à apresentação do produto e conversão.
   - Nunca renderiza sidebar lateral nem barra inferior fixa de aplicativo.
2. **Telas de Autenticação (`/login`, `/cadastro`):**
   - Utilizam exclusivamente o layout `layouts::guest` (`resources/views/layouts/guest.blade.php`).
   - Foco limpo no formulário centralizado, sem estrutura de dashboard.
3. **Painéis Autenticados (`/painel`, `/fornecedor/painel`, `/admin/configuracoes` e próximos módulos):**
   - Utilizam o layout `layouts::app` (`resources/views/layouts/app.blade.php`), com sidebar fixa no desktop (`lg:w-72`) e barra inferior fixa no mobile (`lg:hidden`).

---

## 2. Boas Práticas para o Dashboard (UI/UX)

1. **Sem Menções Promocionais de "100% Gratuito" no Dashboard:**
   - **Não há necessidade de exibir selos, badges ou textos dizendo que o plano é *"100% Gratuito"*, *"Acesso 100% Gratuito"* ou *"Sem mensalidades"* dentro do painel logado.**
   - A gratuidade para o casal faz parte da comunicação externa (Landing Page e página de cadastro). Dentro do dashboard, o usuário já está trabalhando no planejamento do casamento e a interface deve priorizar apenas informações funcionais e operacionais.
2. **Identidade Visual (Paleta Terracota & Areia):**
   - **Canvas / Fundo:** `#FBF9F7`
   - **Superfícies / Cards:** `#FFFFFF` com bordas sutis `#EAE0DA` e cantos arredondados (`rounded-2xl`)
   - **Ação Primária / Destaque:** `#C25E38` (hover `#A84D2B`, fundo suave `#F9EBE6`)
   - **Tipografia Principal / Secundária:** `#2C2523` / `#6E625F` (títulos com `font-serif` e interface/valores com `font-sans`)
   - **Status Positivo / Saldo Disponível:** `#5A8F7B` (fundo suave `#EBF3F0`)
   - Evitar fundos escuros pesados ou cores neon que quebrem o tom acolhedor do sistema.
3. **Design Mobile-First e Escaneabilidade:**
   - Todo botão, link de navegação e item interativo deve ter altura mínima de toque de `44px` (`min-h-[44px]` ou `min-h-[48px]`).
   - Em viewports móveis (`375px`), substituir tabelas largas por cards empilhados com rótulos concisos e status claros.
   - Valores financeiros (orçamento, contratos, pagamentos, presentes) devem usar `tabular-nums` para facilitar leitura comparativa.
   - Manter espaçamento inferior no conteúdo (`pb-24 lg:pb-10`) para que a barra de navegação móvel fixa nunca cubra ações da página.
4. **Clareza Funcional por Perfil:**
   - **Noivos / Casal:** foco direto em métricas financeiras (previsto vs. fechado vs. pago), fornecedores, checklist e progresso dos itens do grande dia, inspirações do Pinterest e lista de presentes/carteira Pix.
   - **Fornecedor:** foco na agenda cronológica de próximos casamentos, vitrine regional e gestão do perfil/portfólio — exigindo no cadastro e no perfil a seleção de uma ou **múltiplas áreas de atuação** para exibição nas listagens filtradas por tipo de fornecedor.
   - **Admin:** foco operacional em feature flags, taxas globais e gestão de ciclos/vagas.
5. **Privacidade e Segurança entre Perfis:**
   - Cerimonialistas e profissionais convidados via `invite_token` nunca acessam dados financeiros sensíveis da carteira de presentes Pix do casal.
   - Tokens de acesso e API (`api_token`, `invite_token`) devem ser exibidos em áreas dedicadas, com ação explícita de regeneração quando aplicável.
6. **Feedback de Ações via Toast (Sem Badges de Confirmação):**
   - Quando o usuário completa uma ação (ex.: salvar configurações, regenerar token, cadastrar/editar fornecedor, marcar item do checklist), o sistema deve exibir um **Toast** flutuante temporário (com auto-dismiss e posicionado de forma a não obstruir a barra de navegação móvel inferior) em vez de renderizar um badge ou banner estático na página ou no card.

---

## 3. Boas Práticas de Engenharia e Testes

1. **Ambiente 100% Docker:**
   - Executar comandos PHP, Composer, Artisan, Pint e PHPUnit exclusivamente dentro do container `app` (`docker compose exec -T app ...`) e Vite/NPM no container `vite` (`docker compose exec -T vite ...`).
2. **Testes Organizados por Módulo de Domínio:**
   - **Nunca** criar um arquivo de teste por spec (ex.: evitar `Spec01...Test.php`).
   - Organizar os testes em `tests/Feature/<Modulo>/...Test.php` (ex.: `Auth/AuthenticationTest.php`, `Dashboard/UserPanelTest.php`, `Admin/PlatformSettingsTest.php`, `Wedding/BudgetTest.php`, `Wedding/ChecklistTest.php`, `Gifts/WalletTest.php`).
3. **Qualidade de Código:**
   - Rodar `./vendor/bin/pint --test` e `php artisan test` após alterações antes de concluir qualquer entrega.

