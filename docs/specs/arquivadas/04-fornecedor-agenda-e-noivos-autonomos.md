# Spec 04 — Fornecedor: Agenda de Próximos Noivos e Cadastro Autônomo

- **Status:** Concluída (`arquivadas`)
- **Dependências:** [02-noivos-orcamento-e-contratos-autonomos](02-noivos-orcamento-e-contratos-autonomos.md)

## 1. Objetivo
Criar o perfil do fornecedor e sua **Agenda de Próximos Noivos**, garantindo **independência bidirecional**: o fornecedor pode gerenciar seus contratos e próximos casamentos mesmo quando os noivos **não utilizam a plataforma**.

## 2. Modelagem de Banco (PostgreSQL 18)
### Tabela `vendor_profiles`
- `id`, `user_id` (`nullable` FK `users`, nullOnDelete)
- `business_name` (`string`), `slug` (`string`, `unique`), `category` (`string`, index)
- `city` (`string`, index), `state` (`string(2)`, index)
- `whatsapp` (`string`), `instagram` (`string`, `nullable`)
- `description` (`text`, `nullable`), `cover_image_url` (`text`, `nullable`), `price_from` (`decimal(10,2)`, `nullable`)
- `is_regional_highlight` (`boolean`, default `false`, index)
- `highlight_expires_at` (`timestamp`, `nullable`)
- *(Campos de portfólio e Instagram detalhados na Spec 07)*

### Uso bidirecional de `wedding_vendors`
- Permite `wedding_id = NULL` quando criado pelo fornecedor para casais fora da plataforma.
- Campos-chave para a agenda:
  - `created_by_side` (`'couple' | 'vendor'`)
  - `couple_names` (`string` — preenchido manualmente pelo fornecedor ou derivado de `wedding->coupleTitle()`)
  - `event_date` (`date` — data do casamento para ordenação cronológica da agenda)
  - `event_city`, `event_venue` (`string`, `nullable`)
  - `contact_name`, `contact_phone`, `contact_email`
  - `contracted_amount`, `paid_amount`, `status`, `notes`

## 3. Regras de Negócio
1. **Autonomia do Fornecedor:** No seu painel, o fornecedor pode cadastrar diretamente um casal externo (`wedding_id = null`, `vendor_profile_id = $profile->id`, `created_by_side = 'vendor'`), informando nomes dos noivos, data do evento, local, telefone/WhatsApp, valor contratado, valor pago, status e observações.
2. **Agenda Unificada e Cronológica:** A query da agenda busca todos os registros onde `vendor_profile_id = $profile->id`, ordenando pelos **próximos casamentos** (`event_date >= hoje`, ou `wedding.wedding_date >= hoje` usando `COALESCE(wedding_vendors.event_date, weddings.wedding_date)`), com aba/filtro para eventos passados e busca por nome do casal.
3. **Vínculo Opcional Futuro:** O registro criado pelo fornecedor também possui `invite_token` (ou código de vínculo), permitindo convidar aquele casal para criar uma conta gratuita no Nosso Voto e conectar os dois painéis sem perder os dados já lançados.

## 4. Interface & Rotas (Livewire 4)
- `/fornecedor/agenda` ou aba **Agenda** dentro de `/fornecedor/painel`:
  - Timeline/cards cronológicos com os próximos noivos (*Ex.: "Em 18 dias • Ana & Lucas • São Paulo - SP"*), indicador de origem (*Casal na Plataforma* vs. *Cadastro Manual*) e saldo a receber do contrato.
  - Modal *"Novo Casamento na Agenda"* para o fornecedor cadastrar noivos que não estão no sistema.

## 5. Critérios de Aceite
- [x] Fornecedor consegue cadastrar um casamento e dados dos noivos mesmo sem existir `Wedding` no banco (`wedding_id = null`).
- [x] Agenda exibe tanto casamentos vinculados a noivos da plataforma quanto casamentos cadastrados manualmente pelo fornecedor, ordenados por data mais próxima.
- [x] Fornecedor pode atualizar valores recebidos (`paid_amount`), status e notas na agenda.
- [x] Exclusão ou edição de um registro criado manualmente pelo fornecedor não exige existência de usuário casal.

