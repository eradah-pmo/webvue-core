# Modular Admin Dashboard

A scalable modular admin dashboard system built with Laravel 11 + Inertia.js + React.

## 🚀 Features

- **SPA Architecture**: Single Page Application using Inertia.js + React
- **Modular System**: Isolated modules in `app/Modules/*` with auto-registration
- **Advanced RBAC**: Horizontal + Vertical permissions using spatie/laravel-permission
- **Full i18n Support**: English/Arabic with RTL layout support
- **Comprehensive Logging**: Activity logs + Audit trails
- **Modern UI**: Tailwind CSS with IBM Plex Sans font
- **Enterprise Ready**: Safe mode, dependency checking, versioning

## 📦 Tech Stack

- **Backend**: Laravel 11, Sanctum Auth
- **Frontend**: React 18, Inertia.js, Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Testing**: PHPUnit/Pest, Cypress/Playwright
- **Deployment**: Docker + Laravel Sail

## 🏗️ Architecture

```
app/
├── Modules/                    # Modular system
│   ├── Users/                 # User management module
│   ├── Roles/                 # Role/Permission module  
│   └── Departments/           # Department module
├── Core/                      # Core system components
│   ├── Services/              # Core business logic
│   ├── Traits/                # Shared traits
│   └── Contracts/             # Interfaces
└── Http/
    ├── Controllers/           # Base controllers
    └── Middleware/            # Custom middleware

resources/
├── js/
│   ├── Components/            # Shared React components
│   ├── Layouts/               # Layout components
│   ├── Pages/                 # Inertia pages
│   ├── Hooks/                 # Custom React hooks
│   └── i18n/                  # Localization files
└── lang/                      # Laravel language files
```

## 🔧 Installation

```bash
# Clone and setup
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run dev
```

## 📋 Module System

Create new modules using:
```bash
php artisan make:module ModuleName
```

Each module includes:
- Controllers, Models, Services
- Routes and middleware
- Permissions and policies
- Localization files
- Frontend components

## 🔐 RBAC System

- **Horizontal Roles**: admin, manager, user
- **Vertical Scopes**: department, business_unit, project
- **Access Patterns**: `hasRole()`, `hasPermission()`, `hasAccess(scope)`

## 🌐 Localization

- Default: English (EN)
- Fallback: Arabic (AR) with RTL support
- Frontend: i18next with namespaces
- Backend: Laravel lang files

## 🧪 Testing

```bash
# Backend tests
php artisan test

# Frontend tests  
npm run test:e2e
```

## 📝 License

MIT License
