<div align="center">

# 🚚 Food Trunk API

**Multi-tenant backend platform for managing food trucks, companies, subscriptions, and payments.**

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com)
[![License](https://img.shields.io/badge/license-Private-lightgrey.svg)](#license)

</div>

---

## 📋 Overview

**Food Trunk** is the technology platform that connects food truck companies
with their end customers. This repository contains the **administrative
backend and main API** of the ecosystem, built following a *Backend First*
strategy: establish a solid, scalable, reusable foundation first, so future
clients (Web Admin, Consumer App, Operator App) can connect without ever
touching the core business logic.

## ✨ Key Features

- 🏢 **Multi-tenant architecture** — every company operates in full isolation
  on top of the same database, with no risk of data leaking between tenants.
- 🔐 **API authentication with Laravel Sanctum** — personal access tokens,
  login, registration, password recovery.
- 🛡️ **Granular roles and permissions** (Spatie Permission) — Platform Owner,
  Company Admin, Operator.
- 🚚 **Full food truck management** — locations, menus, promotions.
- 💳 **Subscriptions and billing** — plans, payments, history, Stripe
  integration.
- 📦 **Documented REST API**, ready to be consumed from Web, iOS, Android, or
  external services.

## 🏗️ Architecture

```
Web Client (React)     ─┐
Consumer App (Flutter) ─┼──►  Food Trunk API (Laravel)  ──►  MySQL
Operator App (Flutter) ─┘            │
                                       ├──► Stripe (payments)
                                       ├──► Google Maps (geolocation)
                                       └──► Firebase (push notifications)
```

### Multi-tenancy

Every business model (`FoodTruck`, `Location`, `Menu`, `Promotion`,
`Subscription`, `Invoice`, `Payment`) uses the `BelongsToCompany` trait, which
applies an automatic **Global Scope**: every query is filtered by the
authenticated user's `company_id`. The `platform-owner` role is the only
exception — it sees every company with no restriction.

This guarantees that no endpoint can accidentally return data belonging to a
different company than the authenticated user's.

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Database | MySQL 8 |
| Authentication | Laravel Sanctum |
| Roles & Permissions | Spatie Laravel-Permission |
| Payments | Stripe |
| Admin frontend (separate repo) | React + Vite |
| Mobile apps (separate repo) | Flutter |

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/   # REST API controllers
│   └── Requests/          # Form request validation
├── Models/                # Eloquent models
├── Traits/                # BelongsToCompany (multi-tenant scope)
database/
├── migrations/            # Database schema
└── seeders/                # Roles, permissions, plans, initial user
routes/
└── api.php                # API endpoint definitions
```

## 👥 System Roles

| Role | Scope | Permissions |
|---|---|---|
| `platform-owner` | Entire platform | Full control: companies, plans, global reports |
| `company-admin` | Their own company | Food trucks, locations, menus, promotions, subscription, billing |
| `operator` | A specific food truck | Menus, locations (used in the mobile app) |

## 🚀 Installation

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 8.0

### Steps

```bash
git clone https://github.com/YOUR_USERNAME/foodtrunk-api.git
cd foodtrunk-api

composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=foodtrunk
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed initial data:

```bash
php artisan migrate
php artisan db:seed
```

Start the server:

```bash
php artisan serve
```

### Default Credentials (Platform Owner)

| Email | Password |
|---|---|
| `owner@foodtrunk.app` | `ChangeMe123!` |

> ⚠️ Change this password immediately in any environment other than local
> development.

## 📡 Available Endpoints

| Method | Route | Description | Auth |
|---|---|---|---|
| `POST` | `/api/auth/login` | Log in, returns a Bearer token | No |
| `POST` | `/api/auth/register-company` | Public company + admin registration | No |
| `POST` | `/api/auth/forgot-password` | Request a password reset link | No |
| `POST` | `/api/auth/reset-password` | Reset password with token | No |
| `POST` | `/api/auth/logout` | Revoke the current token | Yes |
| `GET` | `/api/auth/me` | Authenticated user + role + company | Yes |

> Full documentation for business endpoints (Food Trucks, Menus,
> Subscriptions, Payments) will be added to `docs/api.md` as they're built.

## 🗺️ Roadmap

- [x] Multi-tenant architecture
- [x] Authentication with Sanctum
- [x] Roles and permissions with Spatie
- [ ] Companies CRUD (Platform Owner approval flow)
- [ ] Food Trucks and Locations CRUD
- [ ] Menus and Promotions CRUD
- [ ] Subscriptions and Plans
- [ ] Stripe payment integration
- [ ] OpenAPI / Postman documentation
- [ ] Consumer App (Flutter)
- [ ] Operator App (Flutter)

## 📄 License

This is a private, proprietary project. All rights reserved.

---

<div align="center">

Built with ❤️ for the **Food Trunk** ecosystem

</div>
