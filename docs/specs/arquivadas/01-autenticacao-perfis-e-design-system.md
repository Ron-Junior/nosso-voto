# Spec 01 — Autenticação Multi-Perfil, Configurações Globais e Design System

- **Status:** Concluída (`arquivadas`)
- **Dependências:** [00-setup-inicial-docker-stack](../arquivadas/00-setup-inicial-docker-stack.md)

## 1. Objetivo
Criar a base de usuários multi-perfil (Noivos, Fornecedores e Admin), a tabela de configurações globais da plataforma (incluindo feature flags e preços padrão) e o Layout Principal responsivo na paleta **Terracota & Areia**.

## 2. Modelagem de Banco (PostgreSQL 18)
### Alteração em `users`
- `role` (`string`, default `'couple'`) — valores: `'couple' | 'vendor' | 'admin'`
- `api_token` (`string(80)`, `nullable`, `unique`) — utilizado por integrações externas como a Extensão do Chrome.

### Nova tabela `platform_settings`
- `id` (`bigIncrements`)
- `paywall_pinterest_inspirations_enabled` (`boolean`, default `false`) — **Chave Liga/Desliga** de cobrança das inspirações do Pinterest para decoradores/beleza.
- `platform_gift_fee_percent` (`decimal(5,2)`, default `4.99`)
- `regional_highlight_monthly_price` (`decimal(10,2)`, default `69.90`)
- `portfolio_monthly_price` (`decimal(10,2)`, default `39.90`)
- `banner_cycle_months` (`integer`, default `2`)
- `banner_max_slots` (`integer`, default `5`)
- `banner_cycle_price` (`decimal(10,2)`, default `149.90`)
- `timestamps`

## 3. Regras de Negócio
- **Acesso Gratuito para Noivos:** cadastro de usuários com `role = 'couple'` nunca possui cobrança de assinatura.
- **Singleton de Configuração (`PlatformSetting::current()`):** garante existência de um registro padrão caso o banco esteja recém-migrado.
- **API Token Automático:** ao criar usuário ou ao clicar em "Gerar Novo Token", gera token único de 40+ caracteres.

## 4. Interface & Design System (Livewire 4 + Tailwind CSS)
- Criar layout base `resources/views/layouts/app.blade.php` (ou `components/layouts/app.blade.php` compatível com Livewire 4):
  - Fundo `#FBF9F7`, cards `#FFFFFF` com borda sutil areia (`#EAE0DA`), botões primários `#C25E38` (hover `#A84D2B`), texto `#2C2523`.
  - **Mobile-first:** barra de navegação inferior fixa (`bottom nav`) em telas pequenas.
  - **Desktop:** menu lateral (`sidebar`) limpa e cabeçalho com identificação do perfil logado.
- Telas de Login e Cadastro com seleção de perfil (*Sou Noivo/Noiva* vs. *Sou Fornecedor*) e Painel Admin (`/admin/configuracoes`) para editar `platform_settings`.

## 5. Critérios de Aceite
- [x] Migrations rodam sem erro no PostgreSQL 18.
- [x] Usuário consegue criar conta como `couple` ou `vendor` e autenticar normalmente.
- [x] Admin consegue ligar/desligar `paywall_pinterest_inspirations_enabled` e alterar taxas/preços em `/admin/configuracoes`.
- [x] Layout renderiza perfeitamente em viewport mobile (`375px`) e desktop (`1280px`) utilizando a paleta Terracota & Areia.

