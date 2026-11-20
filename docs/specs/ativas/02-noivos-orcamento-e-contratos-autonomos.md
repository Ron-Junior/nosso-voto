# Spec 02 — Noivos: Orçamento Integrado e Contratos Autônomos

- **Status:** Ativa (Próxima para implementação)
- **Dependências:** [01-autenticacao-perfis-e-design-system](../arquivadas/01-autenticacao-perfis-e-design-system.md)

## 1. Objetivo
Entregar o painel central dos noivos para planejar o casamento, controlar o orçamento pretendido vs. gasto real e cadastrar fornecedores e contratos **com total autonomia**, mesmo quando o fornecedor não utiliza a plataforma.

## 2. Modelagem de Banco (PostgreSQL 18)
### Tabela `weddings`
- `id`, `user_id` (FK `users`, cascade)
- `slug` (`string`, `unique`) — ex.: `ana-e-lucas`
- `partner_1_name`, `partner_2_name` (`string`)
- `wedding_date` (`date`, `nullable`)
- `city` (`string`), `state` (`string(2)`), `venue_name` (`string`, `nullable`)
- `estimated_budget` (`decimal(12,2)`, default `0`) — orçamento pretendido total

### Tabela `wedding_vendors` (parte estrutural + visão dos noivos)
- `id`
- `wedding_id` (`nullable` FK `weddings` — preenchido pelos noivos; será nullable para suportar a Spec 04)
- `vendor_profile_id` (`nullable` FK `vendor_profiles` — **nulo quando o fornecedor não usa a plataforma**)
- `created_by_side` (`string`, default `'couple'`) — `'couple' | 'vendor'`
- `vendor_name` (`string`)
- `couple_names` (`string`, `nullable`)
- `event_date` (`date`, `nullable`)
- `event_city` (`string`, `nullable`), `event_venue` (`string`, `nullable`)
- `category` (`string`) — `cerimonialista`, `decoracao`, `maquiagem`, `penteado`, `fotografia`, `buffet`, `musica`, `espaco`, `outros`
- `contact_name`, `contact_phone`, `contact_email` (`nullable`)
- `estimated_amount` (`decimal(12,2)`, default `0`) — Teto Previsto
- `contracted_amount` (`decimal(12,2)`, default `0`) — Valor Fechado/Atual
- `paid_amount` (`decimal(12,2)`, default `0`) — Valor Já Pago
- `status` (`string`, default `'cotando'`) — `'cotando' | 'contratado' | 'quitado'`
- `notes` (`text`, `nullable`)
- `invite_token` (`string(64)`, `unique`) — gerado automaticamente na criação
- `is_pinterest_authorized` (`boolean`, default `true`)
- `has_paid_pinterest_unlock` (`boolean`, default `false`)

## 3. Regras de Negócio
1. **Autonomia Total do Casal:** Os noivos podem cadastrar qualquer fornecedor manualmente informando nome, categoria, contato, Teto Previsto, Valor Fechado, Valor Pago e observações — **sem depender do fornecedor ter conta na plataforma**.
2. **Orçamento Integrado em Tempo Real:**
   - `total_contracted = SUM(contracted_amount)`
   - `total_paid = SUM(paid_amount)`
   - `remaining_to_pay = total_contracted - total_paid`
   - `budget_balance = estimated_budget - total_contracted`
   - Exibir alerta visual quando `contracted_amount > estimated_amount` em um item ou quando `total_contracted > estimated_budget`.
3. **Convite Opcional (Viral):** Todo registro criado pelos noivos gera um `invite_token` único para que os noivos possam copiar e enviar o link ao profissional se desejarem convidá-lo.

## 4. Interface & Rotas (Livewire 4)
- `/painel` (`CoupleDashboard`):
  - Cabeçalho com dados do casal, contagem regressiva para `wedding_date` e edição do orçamento pretendido (`estimated_budget`).
  - Cards financeiros (*Orçamento Pretendido*, *Total Fechado*, *Total Pago*, *Saldo a Pagar*).
  - Lista/tabela responsiva de fornecedores com CRUD em modal/drawer e botão *"Copiar Link de Acesso"* para cada profissional.

## 5. Critérios de Aceite
- [ ] Casal cria e edita seu casamento (`weddings`) e orçamento pretendido.
- [ ] Casal cadastra fornecedor externo sem `vendor_profile_id` e registra valores de orçamento/contrato.
- [ ] Cálculos financeiros e alertas de estouro atualizam reativamente ao salvar/editar fornecedor.
- [ ] Cada fornecedor recebe um `invite_token` único e copiável.

