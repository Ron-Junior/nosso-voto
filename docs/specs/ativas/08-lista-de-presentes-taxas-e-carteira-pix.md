# Spec 08 — Lista de Presentes em Dinheiro, Taxas e Carteira Pix

- **Status:** Ativa
- **Dependências:** [02-noivos-orcamento-e-contratos-autonomos](02-noivos-orcamento-e-contratos-autonomos.md)

## 1. Objetivo
Implementar o motor principal de receita rápida do **Nosso Voto**: a **Lista de Presentes convertida em dinheiro**, com escolha dos noivos sobre quem paga a taxa da plataforma, checkout elegante para os convidados (Pix e Cartão) e carteira virtual para resgate via **Chave Pix**.

## 2. Modelagem de Banco (PostgreSQL 18)
### Alterações em `weddings`
- `gift_fee_mode` (`string`, default `'pass_to_guest'`) — `'pass_to_guest' | 'absorb'`
- `pix_key_type` (`string`, default `'cpf'`) — `'cpf' | 'email' | 'phone' | 'random'`
- `pix_key` (`string`, `nullable`)
- `wallet_balance_available` (`decimal(12,2)`, default `0`)
- `wallet_total_received` (`decimal(12,2)`, default `0`)
- `wallet_total_withdrawn` (`decimal(12,2)`, default `0`)

### Tabela `gift_items`
- `id`, `wedding_id` (FK `weddings`, cascade)
- `title` (`string`), `description` (`text`, `nullable`), `category` (`string`, `nullable`), `image_url` (`text`, `nullable`)
- `base_price` (`decimal(10,2)`)
- `quota_total` (`integer`, default `1`), `quota_sold` (`integer`, default `0`)
- `is_active` (`boolean`, default `true`)

### Tabela `gift_purchases`
- `id`, `wedding_id` (FK), `gift_item_id` (FK)
- `guest_name`, `guest_email` (`nullable`), `guest_message` (`text`, `nullable`)
- `payment_method` (`string` — `'pix' | 'credit_card'`)
- `gross_amount`, `platform_fee_amount`, `net_couple_amount` (`decimal(10,2)`)
- `fee_mode_snapshot` (`string`)
- `gateway_provider` (`string`, default `'asaas'`) — `'asaas' | 'mercadopago'`
- `gateway_transaction_id` (`string`, `nullable`)
- `status` (`string`, default `'pending'`) — `'pending' | 'paid'`
- `paid_at` (`timestamp`, `nullable`)

### Tabela `wallet_withdrawals`
- `id`, `wedding_id` (FK)
- `amount` (`decimal(12,2)`), `pix_key_type` (`string`), `pix_key` (`string`)
- `status` (`string`, default `'requested'`) — `'requested' | 'approved_paid'`
- `requested_at` (`timestamp`), `processed_at` (`timestamp`, `nullable`)

## 3. Regras de Negócio
1. **Cálculo Exato da Taxa (`platform_gift_fee_percent`, ex: `4.99%`):**
   - **Modo `pass_to_guest` (Repassar ao convidado):**
     - `gross_amount = round(base_price * (1 + fee_percent / 100), 2)`
     - `platform_fee_amount = round(gross_amount - base_price, 2)`
     - `net_couple_amount = base_price` (noivos recebem 100% do valor do presente)
   - **Modo `absorb` (Noivos absorvem a taxa):**
     - `gross_amount = base_price`
     - `platform_fee_amount = round(base_price * (fee_percent / 100), 2)`
     - `net_couple_amount = round(base_price - platform_fee_amount, 2)`
2. **Transação Segura (PostgreSQL Locks):**
   - Confirmação de compra (via checkout / webhook do gateway) executa em `DB::transaction` com `lockForUpdate()` no `GiftItem` e no `Wedding`, impedindo venda acima de `quota_total` e creditando `wallet_balance_available` e `wallet_total_received`.
3. **Resgate via Chave Pix:**
   - Casal cadastra sua chave Pix e solicita saque de até `wallet_balance_available`, decrementando saldo disponível e criando registro em `wallet_withdrawals` para liquidação/auditoria.

## 4. Interface & Rotas (Livewire 4)
- `/presentes` (`CoupleGiftsWallet`): CRUD de presentes/cotas, seletor 1-clique entre `pass_to_guest` e `absorb` com simulação ao vivo, mural de recados dos convidados e formulário de saque Pix.
- `/c/{slug}` (`PublicWeddingGifts`): página pública elegante para convidados comprarem presentes com Pix ou Cartão e deixarem mensagem aos noivos.
- Webhook endpoint `/api/v1/webhooks/payments/{provider}` preparado para Asaas / Mercado Pago.

## 5. Critérios de Aceite
- [ ] Simulação e checkout calculam corretamente `gross_amount`, `platform_fee_amount` e `net_couple_amount` nos modos `pass_to_guest` e `absorb`.
- [ ] Compra confirmada incrementa `quota_sold` e credita exatamente `net_couple_amount` na carteira dos noivos.
- [ ] Não é permitido comprar presente esgotado (`quota_sold >= quota_total`) nem solicitar saque maior que `wallet_balance_available`.
- [ ] Mensagens deixadas pelos convidados no checkout aparecem no mural do casal em `/presentes`.

