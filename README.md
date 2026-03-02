# Foxyed Admin

> **IT**: Gestionale amministrativo per la piattaforma Foxyed.
>
> **EN**: Admin back-office for the Foxyed platform.

---

## Vision / Visione

**IT**
Foxyed nasce per creare un applicativo che permetta a tutti di imparare argomenti scolastici in modo semplice e coinvolgente, usando meccaniche di **gamification**.
Il modello di business è suddiviso in 3 macro aree:
1) **Libri personalizzati** scritti dal gestionale (in futuro: editor simile a Google Docs)
2) **Quiz gamificati** in stile Duolingo
3) **Ripetizioni** / tutoring sulla materia

**EN**
Foxyed aims to build an application that helps people learn school subjects in a simple and engaging way, leveraging **gamification**.
The business model is split into 3 macro areas:
1) **Custom books** authored in the admin (future: Google Docs-like editor)
2) **Gamified quizzes** (Duolingo-like)
3) **Private lessons / tutoring** for the subject

---

## Tech stack

- Backend: **Laravel 12**
- Frontend: **Inertia.js + Vue 3 + Vuetify**
- Auth: Laravel session guard
- Roles: **spatie/laravel-permission** (currently exposed as `groups` on `User`)
- Build: Vite

---

## Architecture: Plugins

**IT**
Le feature sono organizzate in plugin sotto `plugins/<PluginName>`.
I controller vengono registrati automaticamente dal `PluginProvider` tramite PHP Attributes:
- `#[Route("/prefix")]` (Symfony Routing Attribute)
- `#[IsGranted("auth")]` / `#[IsGranted("role:admin")]` (mappato a middleware Laravel)

**EN**
Features are organized as plugins under `plugins/<PluginName>`.
Controllers are auto-registered by `PluginProvider` using PHP Attributes:
- `#[Route("/prefix")]` (Symfony Routing Attribute)
- `#[IsGranted("auth")]` / `#[IsGranted("role:admin")]` (mapped to Laravel middleware)

---

## Development setup

### Requirements
- PHP (8.2+ recommended)
- Composer
- Node.js + npm
- MySQL/MariaDB (optional for local dev; tests can use SQLite)

### Install
```bash
composer install
npm install
```

### Run (dev)
```bash
npm run dev
# and in another shell
php artisan serve
```

### Tests
```bash
php artisan test
```

---

## Conventions

- Prefer small, focused PRs.
- Add/adjust tests for backend changes.
- Keep UI changes consistent with Vuetify components.

---

## Roadmap (high level)

- Users management (create/edit/block, roles)
- Settings/Dictionary management
- Courses + categories + slug format (e.g. `E-MAT-001`)
- Books editor
- Gamified quizzes
- Tutoring flows

