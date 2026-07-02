# ✅ Sistema de Tickets

`Sistema de Tickets` é uma aplicação web em Laravel para gestão de solicitações e suporte técnico, com funcionalidades para gestão de tickets, utilizadores, caixas de entrada (inboxes), anexos, cópia (CC), respostas, notificações e interface responsiva.

---

## 📋 Funcionalidades

### 🎫 Gestão de Tickets
- ✅ Criação, consulta, edição e remoção de tickets
- 🏷️ Definição de tipo e estado do ticket (ex.: Aberto, Em Progresso, Resolved)
- 📅 Data de criação e possibilidade de ordenar por prioridade ou data
- 📎 Anexos em respostas e tickets (TicketAttachment)
- 📩 Cópia (CC) de utilizadores em tickets (TicketCc)

### 🗂️ Inboxes e Contactos
- 📥 Gestão de múltiplas caixas de entrada (Inbox) para receber solicitações
- 👥 Gestão de contactos e funções de contacto (Contact, ContactFunction)

### 💬 Respostas e Histórico
- 📨 Registro de respostas por utilizadores (TicketReply)
- 📜 Registo de atividade e histórico (ActivityLog)

### 🔔 Notificações e Colaboração
- 📧 Notificações por email para criação/atribuição/resposta de tickets
- 🤝 Atribuição de tickets a utilizadores e notificações de atribuição

### 👤 Autenticação e Perfil
- 🔐 Registo, login e proteção de rotas autenticadas
- 👤 Gestão de perfil de utilizador com atualização de nome, email e password

### 🖥️ Interface e Experiência
- 🌗 Suporte a temas claro/escuro (se implementado)
- 📱 Interface responsiva para desktop e mobile
- 🛠️ APIs JSON para operações de tickets (para consumo por SPA)

## 🛠️ Tecnologias Utilizadas

- Laravel
- PHP 8.2+ (ou conforme configurado no projeto)
- Blade + (opcional) Vue 3 para componentes SPA
- Tailwind CSS
- Vite
- Pest / PHPUnit para testes
- SQLite ou MySQL

## ⚙️ Como Executar o Projeto

### ✅ Pré-requisitos
Tenha instalado PHP, Composer, Node.js e npm. Configure a base de dados no ficheiro `.env`.

### 1️⃣ Clonar o repositório
```bash
git clone <url-do-repositorio>
cd Sistema-de-Tickets
```

### 2️⃣ Instalação e configuração rápida
Se existir um script de setup nos `composer scripts`, utilize-o para automatizar a instalação, migrações e compilação de assets:
```bash
composer run setup
```

### 3️⃣ Passos manuais
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Se quiser executar em modo de desenvolvimento com live reload:
```bash
composer run dev
```

### 4️⃣ Aceder à aplicação
Abra http://localhost:8000 (ou a porta definida pelo `php artisan serve`).

## 🧪 Testes

Executar testes com:
```bash
composer test
```

Ou:
```bash
php artisan test
```

## 📁 Estrutura do Projeto

```text
📁 Sistema-de-Tickets/
├── 📄 .editorconfig
├── 📄 .env
├── 📄 .env.example
├── 📁 .git/
├── 📄 .gitattributes
├── 📄 .gitignore
├── 📄 .npmrc
├── 📁 app/
│   ├── 📁 Http/
│   │   └── 📁 Controllers/
│   │       ├── 📁 Api/
│   │       │   ├── 📄 ContactController.php
│   │       │   ├── 📄 ContactFunctionController.php
│   │       │   ├── 📄 EntityController.php
│   │       │   ├── 📄 LookupController.php
│   │       │   ├── 📄 ProfileController.php
│   │       │   ├── 📄 TicketController.php
│   │       │   ├── 📄 TicketReplyController.php
│   │       │   └── 📄 UserController.php
│   │       ├── 📄 AuthController.php
│   │       └── 📄 Controller.php
│   ├── 📁 Models/
│   │   ├── 📄 ActivityLog.php
│   │   ├── 📄 Contact.php
│   │   ├── 📄 ContactFunction.php
│   │   ├── 📄 Entity.php
│   │   ├── 📄 Inbox.php
│   │   ├── 📄 Ticket.php
│   │   ├── 📄 TicketAttachment.php
│   │   ├── 📄 TicketCc.php
│   │   ├── 📄 TicketReply.php
│   │   ├── 📄 TicketStatus.php
│   │   ├── 📄 TicketType.php
│   │   └── 📄 User.php
│   ├── 📁 Notifications/
│   ├── 📁 Providers/
│   └── 📁 Support/
├── 📄 artisan
├── 📁 bootstrap/
├── 📄 composer.json
├── 📄 composer.lock
├── 📁 config/
├── 📁 database/
│   ├── 📁 factories/
│   ├── 📁 migrations/
│   │   ├── 📄 0001_01_01_000000_create_users_table.php
│   │   ├── 📄 0001_01_01_000001_create_cache_table.php
│   │   ├── 📄 0001_01_01_000002_create_jobs_table.php
│   │   ├── 📄 2026_06_11_111739_add_role_to_users_table.php
│   │   ├── 📄 2026_06_11_111742_create_inboxes_table.php
│   │   └── 📄 ...
│   └── 📁 seeders/
├── 📁 node_modules/
├── 📄 package.json
├── 📄 package-lock.json
├── 📄 phpunit.xml
├── 📁 public/
├── 📄 README.md
├── 📁 resources/
│   ├── 📁 css/
│   ├── 📁 js/
│   │   ├── 📄 app.js
│   │   ├── 📄 App.vue
│   │   ├── 📁 components/
│   │   │   └── 📄 MainLayout.vue
│   │   ├── 📁 layouts/
│   │   ├── 📁 router/
│   │   ├── 📁 services/
│   │   ├── 📁 stores/
│   │   └── 📁 views/
│   └── 📁 views/
├── 📁 routes/
│   ├── 📄 api.php
│   ├── 📄 console.php
│   └── 📄 web.php
├── 📁 scripts/
├── 📁 storage/
├── 📁 tests/
│   ├── 📁 Feature/
│   ├── 📁 Unit/
│   ├── 📄 Pest.php
│   └── 📄 TestCase.php
├── 📁 vendor/
└── 📄 vite.config.js
```

## 📝 Observações

- As rotas de tickets normalmente devolvem JSON para integração com SPAs.
- O sistema organiza tickets por `Inbox` e suporta anexos e CC.
- Notificações e templates podem ser encontrados em `app/Notifications` e `app/Support`.

---
