# Spec 09 — Noivos: Checklist e Lista do Grande Dia

- **Status:** Ativa
- **Dependências:** [02-noivos-orcamento-e-contratos-autonomos](02-noivos-orcamento-e-contratos-autonomos.md)

## 1. Objetivo
Entregar aos noivos uma central prática e mobile-first para listar tudo o que precisam para o Grande Dia (tanto itens físicos/enxoval do evento quanto preparativos e tarefas), permitindo marcar com 1 toque o que **já têm (`concluido`)**, o que estão **completando aos poucos (`em_andamento`)** e o que **ainda precisam (`pendente`)**, com suporte a controle de quantidades (ex.: *12 de 20 lembrancinhas*) e barra de progresso geral.

## 2. Modelagem de Banco (PostgreSQL 18)
### Tabela `wedding_checklist_items`
- `id` (`bigIncrements`)
- `wedding_id` (FK `weddings`, cascade on delete)
- `title` (`string`) — ex.: *"Alianças gravadas"*, *"Lembrancinhas dos padrinhos"*, *"Kit toalete para os banheiros"*
- `category` (`string`, default `'itens_grande_dia'`) — categorias:
  - `'documentos_cerimonia'` (*Cerimônia & Documentos*)
  - `'trajes_beleza'` (*Trajes, Alianças & Beleza*)
  - `'recepcao_festa'` (*Festa, Recepção & Decoração*)
  - `'itens_grande_dia'` (*Itens Físicos para Levar no Grande Dia*)
  - `'convidados_convites'` (*Convidados, Padrinhos & Convites*)
  - `'outros'` (*Outros Preparativos*)
- `status` (`string`, default `'pendente'`) — `'pendente' | 'em_andamento' | 'concluido'`
  - `'pendente'`: *Precisa / Falta providenciar*
  - `'em_andamento'`: *Completando / Em andamento*
  - `'concluido'`: *Já temos / Concluído*
- `quantity_needed` (`integer`, default `1`) — quantidade total necessária (ex.: `1` para item único ou `50` para lembrancinhas/garrafas)
- `quantity_acquired` (`integer`, default `0`) — quantidade que os noivos já têm/completaram
- `assigned_to` (`string`, default `'ambos'`) — `'partner_1' | 'partner_2' | 'ambos' | 'cerimonialista'`
- `due_date` (`date`, `nullable`) — prazo opcional
- `notes` (`text`, `nullable`) — observações (ex.: *"Já compramos metade, guardado na casa da mãe"*)
- `display_order` (`integer`, default `0`)
- `completed_at` (`timestamp`, `nullable`)
- `timestamps`

## 3. Regras de Negócio
1. **Estados Claros ("Precisa", "Completando" e "Já Tem"):**
   - Os noivos podem alternar o status diretamente com 1 toque ou atualizar a quantidade adquirida (`quantity_acquired` de `quantity_needed`).
   - Sincronização inteligente entre quantidade e status:
     - Se `quantity_acquired <= 0` ao atualizar quantidade -> `status = 'pendente'`, `completed_at = null`.
     - Se `0 < quantity_acquired < quantity_needed` -> `status = 'em_andamento'` (*Completando*), `completed_at = null`.
     - Se `quantity_acquired >= quantity_needed` (ou ao marcar o checkbox rápido *"Já temos"*) -> `status = 'concluido'`, `quantity_acquired = quantity_needed`, `completed_at = now()`.
     - Ao clicar em *"Marcar como Completando"* em um item unitário (`quantity_needed = 1`), `status` passa para `'em_andamento'`.
2. **Lista Sugerida Inicial (1-Clique / Seed Inteligente):**
   - Caso a lista do casamento esteja vazia, exibir botão *"Carregar sugestões essenciais do Grande Dia"* que popula uma lista inicial curada por categoria (ex.: *Alianças*, *Vestido e sapato da noiva*, *Traje do noivo*, *Votos escritos*, *Topo de bolo*, *Lembrancinhas*, *Kit toalete*, *Chinelos/mimos de pista*, *Documentação do cartório/religioso*, *Caixas dos padrinhos*), evitando que o casal comece de uma tela em branco.
3. **Indicadores de Progresso do Grande Dia:**
   - Cálculo em tempo real:
     - `total_items`: total de itens da lista
     - `completed_items`: contagem com `status = 'concluido'`
     - `in_progress_items`: contagem com `status = 'em_andamento'`
     - `pending_items`: contagem com `status = 'pendente'`
     - `completion_percentage`: `round((completed_items / max(total_items, 1)) * 100)` (ou ponderado pelo progresso dos itens).
4. **Apoio do Cerimonialista (Integração com Spec 03):**
   - Quando o Cerimonialista acessa o casamento via `/acesso/profissional/{token}` (`category = 'cerimonialista'`), pode visualizar o checklist do Grande Dia e ajudar o casal a marcar itens conferidos/recebidos para o evento.

## 4. Interface & Rotas (Livewire 4)
- `/checklist` (`CoupleChecklist`) dentro do layout autenticado `layouts::app` (com resumo rápido também no `/painel` dos noivos):
  - **Cabeçalho de Progresso:** barra de progresso em Terracota (`#C25E38`) e Verde Sálvia (`#5A8F7B` para concluídos) + 3 cards compactos (`tabular-nums`): *"Falta Providenciar"*, *"Completando"* e *"Já Temos / Pronto"*.
  - **Filtros Rápidos (Pills Mobile-First `min-h-[44px]`):** *Todos*, *Falta (`pendente`)*, *Completando (`em_andamento`)*, *Já Temos (`concluido`)* e filtro por categoria.
  - **Cards Escaneáveis de Itens:**
    - Checkbox rápido (`min-h-[44px] min-w-[44px]`) para marcar *"Já temos / Concluído"*.
    - Seletor/botão rápido de status (*Precisa* -> *Completando* -> *Já Tem*) e controles `+` / `-` (`tabular-nums`) quando `quantity_needed > 1` (ex.: `12 / 20`).
    - Modal/drawer ou formulário inline para criar/editar item (título, categoria, quantidade necessária, quantidade já adquirida, responsável, prazo e observações).

## 5. Critérios de Aceite & Testes
- **Organização dos Testes:** manter a suíte automatizada deste módulo em `tests/Feature/Wedding/ChecklistTest.php`.
- [ ] Casal consegue cadastrar, editar e remover itens na lista do Grande Dia com categoria, quantidade necessária, responsável, prazo e observações.
- [ ] Casal consegue carregar com 1 clique a lista inicial sugerida de itens essenciais quando o checklist estiver vazio.
- [ ] Atualização de quantidade (`+` / `-`) transiciona automaticamente entre `pendente`, `em_andamento` (*Completando*) e `concluido` (*Já temos*).
- [ ] Marcar checkbox rápido de *"Já temos"* preenche `quantity_acquired = quantity_needed`, define `status = 'concluido'` e registra `completed_at`.
- [ ] Indicadores e barra de progresso atualizam reativamente conforme os itens são marcados ou completados.
- [ ] Cerimonialista com token válido visualiza e consegue conferir/atualizar status de itens do checklist sem acessar dados da carteira Pix.

