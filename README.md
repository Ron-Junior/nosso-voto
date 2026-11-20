# Nosso Voto

Plataforma web (mobile-first e desktop) para gestão completa de casamentos — orçamento integrado a fornecedores, captura de inspirações do Pinterest, colaboração com cerimonialistas e lista de presentes em dinheiro.

## Stack (100% Docker)
- **PHP:** `8.5` (`php:8.5-cli-alpine`)
- **Framework:** `Laravel 13.x` + `Livewire 4.x`
- **Estilização:** `Tailwind CSS` (Paleta **Terracota & Areia**: `#FBF9F7`, `#FFFFFF`, `#C25E38`, `#2C2523`)
- **Frontend / HMR:** `Node 22 + Vite (HMR)` (`node:22-alpine`)
- **Banco de Dados:** `PostgreSQL 18` (`postgres:18-alpine`)

## Documentação Geral
Consulte a documentação completa de produto, modelo de negócio, fluxos e modelagem do banco em:
- [docs/DOCUMENTACAO_GERAL.md](docs/DOCUMENTACAO_GERAL.md)

## Como Subir o Ambiente Inicial via Docker
```bash
cd /root/projetos/nosso-voto
docker compose up -d
docker compose exec app php -v
docker compose exec app php artisan --version
```
- Aplicação web: `http://localhost:8000`
- Vite Dev Server (HMR): `http://localhost:5173`
- PostgreSQL 18 (porta exposta no host): `localhost:5435` (usuário/senha/banco: `nossovoto`)
