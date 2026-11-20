# Spec 03 — Cerimonialista: Visão Consolidada e Colaborativa

- **Status:** Ativa
- **Dependências:** [02-noivos-orcamento-e-contratos-autonomos](02-noivos-orcamento-e-contratos-autonomos.md)

## 1. Objetivo
Permitir que o **Cerimonialista** acesse a lista consolidada de todos os fornecedores do casamento de um casal específico (por link de convite ou vínculo em conta), visualize status e contatos, e **colabore atualizando informações operacionais e cadastrando novos fornecedores** para ajudar os noivos.

## 2. Regras de Negócio
1. **Identificação do Acesso:**
   - Acesso via `/acesso/profissional/{token}` quando o `WeddingVendor` correspondente tiver `category = 'cerimonialista'`, ou via conta autenticada do fornecedor vinculada ao casamento.
2. **Visão Consolidada do Casamento:**
   - Exibe dados do evento (`coupleTitle`, data, local/espaço, cidade/UF).
   - Lista todos os fornecedores vinculados ao `wedding_id` agrupados ou filtráveis por categoria e status (`cotando`, `contratado`, `quitado`), com atalho direto para WhatsApp e telefone.
3. **Permissão Colaborativa:**
   - Pode **editar** `status`, `contact_name`, `contact_phone`, `contact_email`, `notes` e valores contratuais (ou registrar observações de alinhamento).
   - Pode **adicionar** novos fornecedores à lista do casamento para auxiliar o casal.
   - **Proteção de dados sensíveis do casal:** o cerimonialista não acessa a carteira de presentes em dinheiro nem solicitações de saque Pix dos noivos.

## 3. Interface & Rotas (Livewire 4)
- `/acesso/profissional/{token}` (componente `VendorWeddingAccess` detectando `category === 'cerimonialista'`):
  - Resumo executivo do casamento (total de fornecedores contratados vs. em cotação vs. quitados).
  - Lista de fornecedores com edição rápida inline/modal e formulário *"Adicionar Fornecedor para o Casal"*.

## 4. Critérios de Aceite
- [ ] Token de `category = 'cerimonialista'` abre o painel consolidado apenas daquele casamento.
- [ ] Cerimonialista consegue atualizar status/notas de outros fornecedores do casamento e incluir um novo fornecedor.
- [ ] Token de outra categoria (ex.: fotografia) **não** tem acesso à lista consolidada dos demais fornecedores.
- [ ] Alterações feitas pelo cerimonialista refletem imediatamente no `/painel` dos noivos.

