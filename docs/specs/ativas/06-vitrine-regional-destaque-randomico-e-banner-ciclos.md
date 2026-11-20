# Spec 06 — Vitrine Regional, Destaque Randômico e Banner de Ciclos (Máx. 5 Vagas)

- **Status:** Ativa
- **Dependências:** [04-fornecedor-agenda-e-noivos-autonomos](../arquivadas/04-fornecedor-agenda-e-noivos-autonomos.md)

## 1. Objetivo
Construir a Vitrine Regional de Fornecedores com foco na **monetização B2B justa**: destaque regional em **ordem randômica** (sem privilégios fixos) e **Banner Principal** limitado a **no máximo 5 fornecedores** por período configurável (ex.: ciclos de 2 meses).

## 2. Modelagem de Banco (PostgreSQL 18)
### Alteração em `vendor_profiles`
- `categories` (`jsonb`, default `'[]'`) — lista de **múltiplas áreas de atuação** selecionadas pelo fornecedor (ex.: `["maquiagem", "penteado"]`), com índice GIN para filtragem por tipo na Vitrine; `category` segue representando a área principal exibida como destaque no perfil.

### Tabela `banner_cycles`
- `id`, `title` (`string` — ex.: `"Ciclo Bimestral Mai-Jun • São Paulo/SP"`)
- `city` (`string`, index), `state` (`string(2)`, index)
- `duration_months` (`integer`, default `2`) — período definido pela plataforma
- `max_slots` (`integer`, default `5`) — **limite máximo de 5 fornecedores no banner**
- `price` (`decimal(10,2)`, default `149.90`)
- `starts_at` (`timestamp`), `ends_at` (`timestamp`)
- `is_active` (`boolean`, default `true`, index)

### Tabela `banner_submissions`
- `id`, `banner_cycle_id` (FK `banner_cycles`, cascade)
- `vendor_profile_id` (FK `vendor_profiles`, cascade)
- `headline` (`string`), `banner_image_url` (`text`, `nullable`), `cta_link` (`string`, `nullable`)
- `status` (`string`, default `'pending'`, index) — `'pending' | 'active' | 'waitlist' | 'rejected'`
- `slot_number` (`integer`, `nullable`) — `1..5`
- `paid_at` (`timestamp`, `nullable`)
- Constraint: `unique(['banner_cycle_id', 'vendor_profile_id'])`

## 3. Regras de Negócio
1. **Cadastro Obrigatório com Múltiplas Áreas de Atuação:**
   - Ao se cadastrar como fornecedor (`/cadastro`, perfil *Sou Fornecedor*), o usuário deve selecionar obrigatoriamente ao menos **uma área de atuação**, podendo marcar **várias áreas** (ex.: *Maquiagem* + *Penteado*, *Buffet* + *Espaço*), também editáveis no seu perfil em `/fornecedor/painel`.
   - Na Vitrine (`/vitrine`), a filtragem por categoria consulta todas as áreas cadastradas no fornecedor (`category = ? OR categories @> ?`), garantindo que o profissional seja exibido na lista de fornecedores de cada tipo em que atua.
2. **Listagem Justa com Ordem Randômica:**
   - Ao filtrar por Cidade/UF e/ou Categoria na Vitrine (`/vitrine`), a consulta executa `orderByDesc('is_regional_highlight')->inRandomOrder()` (`ORDER BY is_regional_highlight DESC, RANDOM()` no PostgreSQL 18).
   - Fornecedores com `is_regional_highlight = true` (e `highlight_expires_at` válido) aparecem no topo, porém **embaralhados a cada carregamento** para não privilegiar um assinante sobre outro.
   - Fornecedores gratuitos aparecem abaixo em seguida.
3. **Banner Principal com Trava Rígida de 5 Vagas por Ciclo:**
   - Todos os fornecedores podem submeter candidatura no ciclo ativo da sua região (ex.: ciclo de 2 meses configurado em `platform_settings.banner_cycle_months`).
   - Um `BannerCycle` **nunca pode ter mais que `max_slots` (5) submissões com `status = 'active'`**. A ativação/aprovação executa dentro de transação (`DB::transaction` + `lockForUpdate()`), lançando erro ou encaminhando para `waitlist` se todas as 5 vagas já estiverem preenchidas.
4. **Vínculo com 1 Clique para Noivos:**
   - Na Vitrine, o casal autenticado pode clicar em *"Vincular ao Meu Casamento"* para importar o fornecedor direto para sua lista em `wedding_vendors`.

## 4. Interface & Rotas (Livewire 4)
- `/cadastro` (perfil *Sou Fornecedor*): seleção obrigatória de uma ou mais áreas de atuação com checkboxes/chips acessíveis.
- `/vitrine` (`VendorShowcase`): Banner rotativo/grid de até 5 vagas no topo + filtros Cidade/UF e Categoria + listagem randômica dos destaques e gratuitos exibindo fornecedores em todas as áreas selecionadas.
- `/fornecedor/painel`: edição das múltiplas áreas de atuação no perfil profissional, contratação do Destaque Regional mensal e formulário de submissão ao ciclo ativo do Banner (exibindo quantas das 5 vagas ainda estão disponíveis e o período vigente).
- `/admin/configuracoes`: gestão dos ciclos de banner, aprovação das submissões respeitando o limite de 5 slots e configuração da duração do ciclo (`banner_cycle_months`).

## 5. Critérios de Aceite
- [ ] Fornecedor ao se cadastrar escolhe obrigatoriamente ao menos uma área de atuação, podendo selecionar várias áreas.
- [ ] Fornecedor cadastrado em múltiplas áreas aparece na listagem de fornecedores de cada tipo correspondente na Vitrine (`/vitrine`).
- [ ] Fornecedores com Destaque Regional aparecem acima dos gratuitos e mudam de ordem aleatoriamente entre requisições.
- [ ] Tentativa de ativar um 6º fornecedor no mesmo `BannerCycle` (com `max_slots = 5`) é bloqueada pela regra de negócio e testes automatizados.
- [ ] Todos os fornecedores da região conseguem submeter candidatura para o ciclo do banner.
- [ ] Noivos conseguem vincular um fornecedor da vitrine diretamente ao orçamento do seu casamento.

