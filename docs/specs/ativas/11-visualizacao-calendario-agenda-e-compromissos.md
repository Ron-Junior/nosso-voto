# Spec 11 — Visualização com Calendário: Agenda do Fornecedor e Planejamento dos Noivos

- **Status:** Ativa
- **Dependências:**
  - [02-noivos-orcamento-e-contratos-autonomos](../arquivadas/02-noivos-orcamento-e-contratos-autonomos.md)
  - [03-cerimonialista-visao-consolidada-colaborativa](../arquivadas/03-cerimonialista-visao-consolidada-colaborativa.md)
  - [04-fornecedor-agenda-e-noivos-autonomos](../arquivadas/04-fornecedor-agenda-e-noivos-autonomos.md)
  - *(Integração opcional com [09-noivos-checklist-e-itens-do-grande-dia](09-noivos-checklist-e-itens-do-grande-dia.md) para exibir prazos `due_date` do checklist quando implementado)*

## 1. Objetivo
Entregar uma visualização interativa em **calendário mensal** (mobile-first e desktop) para **Fornecedores** e **Noivos (com colaboração do Cerimonialista)**, unificando os registros já existentes (casamentos da agenda, data do Grande Dia e prazos do checklist) com novos registros de **bloqueios de disponibilidade do fornecedor** e **compromissos de planejamento do casamento** (reuniões, degustações, provas, vencimentos e documentação), com detecção de conflito de datas e criação contextual a partir do dia selecionado (sem exportação `.ics`/Google Calendar neste escopo).

## 2. Modelagem de Banco (PostgreSQL 18)

### Tabela `vendor_blocked_dates` (Bloqueios de Disponibilidade do Fornecedor)
- `id` (`bigIncrements`)
- `vendor_profile_id` (FK `vendor_profiles`, `cascadeOnDelete`)
- `blocked_date` (`date`, index) — data indisponível na agenda do fornecedor
- `block_type` (`string`, default `'indisponivel'`) — `'indisponivel' | 'evento_externo' | 'pessoal'`
- `reason` (`string`, `nullable`) — motivo curto exibido no calendário (ex.: *"Férias"*, *"Evento externo"*, *"Folga"*)
- `notes` (`text`, `nullable`) — observações internas do fornecedor
- `timestamps`
- **Constraints e Índices:** `unique(['vendor_profile_id', 'blocked_date'])` e índice composto `['vendor_profile_id', 'blocked_date']` para consultas mensais por intervalo.

### Tabela `wedding_events` (Compromissos e Marcos do Casamento)
- `id` (`bigIncrements`)
- `wedding_id` (FK `weddings`, `cascadeOnDelete`)
- `wedding_vendor_id` (`nullable` FK `wedding_vendors`, `nullOnDelete`) — vínculo opcional com um fornecedor específico do casamento
- `title` (`string`) — ex.: *"Degustação do Buffet"*, *"Prova do vestido"*, *"Reunião com Cerimonialista"*
- `event_type` (`string`, default `'reuniao'`) — `'reuniao' | 'degustacao' | 'prova' | 'pagamento' | 'documento' | 'outro'`
- `event_date` (`date`, index)
- `start_time` (`time`, `nullable`)
- `end_time` (`time`, `nullable`)
- `location` (`string`, `nullable`) — endereço, espaço ou link de reunião online
- `assigned_to` (`string`, default `'ambos'`) — `'partner_1' | 'partner_2' | 'ambos' | 'cerimonialista'`
- `status` (`string`, default `'agendado'`) — `'agendado' | 'concluido' | 'cancelado'`
- `notes` (`text`, `nullable`)
- `created_by_side` (`string`, default `'couple'`) — `'couple' | 'cerimonialista'`
- `completed_at` (`timestamp`, `nullable`)
- `timestamps`
- **Índices compostos:** `['wedding_id', 'event_date']` e `['wedding_id', 'status']`.

### Reaproveitamento de Tabelas Existentes (Sem Alteração Estrutural)
- `wedding_vendors`: usa `COALESCE(wedding_vendors.event_date, weddings.wedding_date)` para plotar casamentos da plataforma e cadastros manuais na grade do fornecedor.
- `weddings`: usa `weddings.wedding_date` como marco especial **"Grande Dia"** no calendário dos noivos e do cerimonialista.
- `wedding_checklist_items` *(Spec 09 — integração opcional)*: quando a tabela existir no banco, exibe itens com `due_date` preenchido no calendário dos noivos e do cerimonialista com identificação visual **"Prazo do Checklist"**.

## 3. Regras de Negócio

### 3.1. Calendário na Agenda do Fornecedor (`/fornecedor/agenda` — `VendorSchedule`)
1. **Alternância Lista ↔ Calendário:**
   - Seletor `viewMode = 'list' | 'calendar'` (persistível via `#[Url(as: 'visualizacao')]`), mantendo `statusFilter` e `search` sincronizados entre os dois modos.
   - Navegação mensal com botões **Mês Anterior**, **Hoje**, **Próximo Mês** e seletor de mês/ano, exibindo grade semanal no padrão brasileiro (`Dom` a `Sáb`).
   - Registros da agenda sem data definida (`COALESCE(wedding_vendors.event_date, weddings.wedding_date) IS NULL`) permanecem acessíveis em seção separada **"Sem data definida"** abaixo ou ao lado da grade.
2. **Cores Semânticas por Status e Bloqueio:**
   - `quitado`: Verde Sálvia (`#5A8F7B` / fundo `#EBF3F0`).
   - `contratado`: Terracota (`#C25E38` / fundo `#F9EBE6`).
   - `cotando`: Areia/Neutro (`#6E625F` / fundo `#FBF9F7` com borda `#EAE0DA`).
   - Bloqueios (`vendor_blocked_dates`): estilo visual diferenciado com ícone de bloqueio/cadeado e rótulo do tipo/motivo.
3. **Detecção de Conflitos de Data:**
   - **Múltiplos casamentos no mesmo dia:** quando houver `>= 2` registros de casamento na mesma data, exibir selo/alerta visual de conflito na célula do calendário e no painel de detalhes do dia, **sem impedir** o cadastro (pois equipes podem atender mais de um evento no mesmo dia).
   - **Conflito com data bloqueada:** ao criar ou editar um casamento em uma data presente em `vendor_blocked_dates`, exibir aviso claro de indisponibilidade no modal (permitindo ajustar a data, desbloquear ou salvar conscientemente).
4. **Bloqueio de Datas Indisponíveis (`vendor_blocked_dates`):**
   - O fornecedor pode bloquear um único dia ou um intervalo contínuo (ex.: férias — limite máximo de `31` dias por ação), gerando/atualizando registros por dia via `upsert` respeitando a constraint `unique(['vendor_profile_id', 'blocked_date'])`.
   - Edição de tipo/motivo ou desbloqueio com 1 clique diretamente pelo painel do dia selecionado, com confirmação via Toast.
5. **Atalho Contextual na Data Selecionada:**
   - Ao selecionar um dia na grade, exibir todos os casamentos e bloqueios da data com ações rápidas (WhatsApp, editar, alterar status, desbloquear) e o botão **"+ Novo Casamento em DD/MM/AAAA"**, abrindo o modal de `VendorSchedule` já com `event_date` pré-preenchido.

### 3.2. Calendário de Planejamento dos Noivos (`/calendario` — `CoupleCalendar`)
1. **Visão Unificada do Casamento:**
   - Exibe na grade mensal (e com alternância opcional para modo **Lista**):
     - Marco em destaque do **Grande Dia** (`weddings.wedding_date`), acompanhado do atalho rápido **"Ir para o mês do Grande Dia"**.
     - Todos os **Compromissos (`wedding_events`)** ordenados por `start_time ASC NULLS LAST`.
     - **Prazos do Checklist (`wedding_checklist_items.due_date`)** quando o módulo da Spec 09 estiver ativo no banco.
2. **CRUD e Validações de Compromissos:**
   - Cadastro, edição, conclusão em 1 toque (`status = 'concluido'` + `completed_at = now()`), reabertura (`status = 'agendado'`, `completed_at = null`), cancelamento e exclusão.
   - O painel do dia selecionado oferece o atalho **"+ Novo Compromisso em DD/MM/AAAA"** com `event_date` pré-preenchido.
   - **Validações obrigatórias:**
     - Quando `end_time` for informado, `start_time` é obrigatório e `end_time` deve ser posterior a `start_time`.
     - Quando `wedding_vendor_id` for informado, o registro deve pertencer obrigatoriamente ao `wedding_id` autenticado.
3. **Filtros e Resumo no Painel:**
   - Filtros por tipo (`event_type` + `checklist`) e por status (`agendado`, `concluido`, `cancelado`).
   - No `/painel` (`CoupleDashboard`), exibir card compacto com os **próximos 3 compromissos agendados** e botão **"Abrir Calendário"** (`/calendario`).

### 3.3. Colaboração do Cerimonialista e Privacidade (`/acesso/profissional/{token}` — `VendorWeddingAccess`)
1. **Acesso Exclusivo do Cerimonialista (`category = 'cerimonialista'`):**
   - Exibir aba/seção **Calendário do Casamento** no painel consolidado do cerimonialista, permitindo visualizar o Grande Dia, os `wedding_events` e os prazos do checklist (quando existentes), além de **criar** (`created_by_side = 'cerimonialista'`), **editar** e **marcar compromissos como concluídos**; todas as alterações refletem imediatamente em `/calendario` dos noivos.
2. **Isolamento e Privacidade:**
   - Profissionais de outras categorias (`fotografia`, `decoracao`, `maquiagem`, `penteado`, `buffet`, `musica`, `espaco`, `outros`) **nunca** acessam o calendário consolidado ou compromissos internos do casal.
   - O cerimonialista continua sem acesso à carteira Pix, lista de presentes financeira ou solicitações de saque dos noivos.
   - `vendor_blocked_dates` é estritamente isolado por `vendor_profile_id`: um fornecedor nunca visualiza, edita ou remove bloqueios de outro profissional.

## 4. Interface, Mobile-First & Arquitetura (Livewire 4)
- Seguir integralmente [docs/DIRETRIZES_DASHBOARD.md](../../DIRETRIZES_DASHBOARD.md) e `.agents/skills/mobile-first-layout/SKILL.md`:
  - Layout autenticado `layouts::app`, paleta **Terracota & Areia** (`#FBF9F7` fundo, `#FFFFFF` cards, `#EAE0DA` bordas, `#C25E38` / `#F9EBE6` primária, `#5A8F7B` / `#EBF3F0` positivo, `#2C2523` / `#6E625F` tipografia), títulos em `font-serif`, datas/horários/valores em `font-sans tabular-nums`, ausência total de selos *"100% Gratuito"* no painel logado e feedback exclusivamente via **Toast** flutuante (`<x-toast>`).
  - **Mobile (`375px`):** grade mensal de 7 colunas com células tocáveis (`min-h-[44px]`) exibindo número do dia + **pontos coloridos (dots)** por categoria/status e indicador de conflito; logo abaixo da grade, painel **"Agenda do Dia Selecionado"** com cards empilhados e ações `min-h-[44px]`.
  - **Desktop (`>= 1024px`):** células amplas com até 2–3 chips de eventos (`+N mais` quando exceder) + painel lateral ou inferior com detalhes completos do dia selecionado.
- **Arquitetura Leve sem Dependências JS Pesadas:**
  - Utilitário puro em PHP `App\Support\CalendarMonthBuilder` (baseado em `CarbonImmutable`) compartilhado entre `VendorSchedule`, `CoupleCalendar` e `VendorWeddingAccess`, combinado com Livewire 4 + Alpine.js leve (sem bibliotecas externas como FullCalendar).
- **Rotas e Navegação:**
  - `/fornecedor/agenda` (`App\Livewire\Vendor\VendorSchedule`, rota `vendor.schedule`): alternância Lista ↔ Calendário e gestão de bloqueios de data.
  - `/calendario` (`App\Livewire\Wedding\CoupleCalendar`, rota `calendar.index`, middleware `auth`, acesso para `couple` e `admin`): calendário de planejamento dos noivos + link na navegação de `resources/views/layouts/app.blade.php` (sidebar desktop e organização mobile preservando botões `min-h-[48px]`).
  - `/painel` (`App\Livewire\Wedding\CoupleDashboard`): card resumo dos próximos 3 compromissos agendados.
  - `/acesso/profissional/{token}` (`App\Livewire\Wedding\VendorWeddingAccess`): seção colaborativa do calendário para `category = 'cerimonialista'`.

## 5. Critérios de Aceite & Testes
- **Organização dos Testes (por módulo de domínio, nunca por spec):**
  - `tests/Feature/Vendor/VendorCalendarTest.php`
  - `tests/Feature/Wedding/WeddingCalendarTest.php`
- [ ] Fornecedor consegue alternar entre modo **Lista** e **Calendário Mensal** em `/fornecedor/agenda`, navegando entre meses e mantendo filtros de status e busca sincronizados.
- [ ] Célula do calendário e painel do dia destacam visualmente quando há múltiplos casamentos na mesma data, sem impedir o salvamento.
- [ ] Fornecedor consegue bloquear um dia único ou um intervalo de até 31 dias (`vendor_blocked_dates`), editar o motivo e desbloquear com 1 clique.
- [ ] Ao criar ou editar um casamento em uma data bloqueada, o modal exibe alerta claro de conflito/indisponibilidade.
- [ ] Clicar em **"+ Novo Casamento em DD/MM/AAAA"** no dia selecionado abre o modal de cadastro já com `event_date` pré-preenchido.
- [ ] Um fornecedor não consegue visualizar, editar ou remover bloqueios (`vendor_blocked_dates`) pertencentes a outro fornecedor.
- [ ] Casal acessa `/calendario`, alterna entre grade mensal e lista, visualiza o marco **Grande Dia** com atalho *"Ir para o mês do Grande Dia"* e cadastra, edita, conclui (`completed_at`), reabre, cancela e exclui compromissos (`wedding_events`).
- [ ] Validação impede salvar compromisso com `end_time` sem `start_time`, com `end_time` anterior/igual a `start_time`, ou vinculado a `wedding_vendor_id` de outro casamento.
- [ ] Quando `wedding_checklist_items` existir no banco (Spec 09), itens com `due_date` são exibidos no calendário dos noivos e do cerimonialista como *"Prazo do Checklist"*.
- [ ] Painel dos noivos (`/painel`) exibe resumo com os próximos 3 compromissos agendados e link para `/calendario`.
- [ ] Cerimonialista com token válido visualiza o calendário do casamento em `/acesso/profissional/{token}` e cria (`created_by_side = 'cerimonialista'`), edita e conclui compromissos que refletem imediatamente em `/calendario` dos noivos.
- [ ] Profissionais de outras categorias não acessam o calendário consolidado dos noivos, e o cerimonialista permanece sem acesso à carteira Pix.

