# MajidaStarter

MajidaStarter is a batteries-included **Laravel 13 + Livewire 4** starter kit. It ships with everything you usually rebuild at the start of every project — authentication, an admin panel, role-based access control, activity logging, multilingual (LTR/RTL) support, light/dark theming, a content module (posts & categories), a matching REST API, and a clean service layer — so you can skip the boilerplate and start building features.

## Highlights

- **Full authentication flow** — registration, login, logout, password reset, and email verification, all built as single-file Livewire components.
- **Admin panel** — a dedicated, permission-gated back office for managing users, posts, categories, reviewing activity, and editing site settings.
- **Content module** — full CRUD for **posts** and **categories** in the admin panel, with a public `/blog` (listing, search, detail pages and cover images) and `/docs`. Deletes go to a **trash** and can be restored; bulk publish/delete and filters/sorting are built into the admin lists.
- **Role & permission based access control** — powered by [spatie/laravel-permission](https://spatie.be/docs/laravel-permission), with an `admin` role seeded out of the box.
- **Activity logging** — every meaningful change to users, posts, categories, and settings is recorded with [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) and browsable from the admin panel.
- **REST API** — every service is exposed as JSON endpoints under `/api`, protected by the same authorization gates as the web UI (see [API](#api)).
- **Localization (i18n) with RTL support** — English, Arabic, and French ship by default, with a language switcher, per-session locale persistence, and automatic text-direction handling.
- **Translatable settings** — site name and description are stored per-locale via [spatie/laravel-translatable](https://spatie.be/docs/laravel-translatable).
- **Light / dark theme toggle** — a persistent, no-flash theme switcher.
- **Two-factor authentication** — TOTP apps (RFC 6238) with single-use recovery codes, managed from **Settings → Two-factor**. No extra dependency is needed.
- **API tokens** — Laravel Sanctum personal access tokens for bearer-token clients, alongside the session login the web UI uses.
- **Maintenance mode** — toggle it in the admin settings; visitors get a 503 page (or JSON on `/api`), while administrators and sign-in keep working.
- **Soft deletes & trash** — users, posts, and categories are soft-deleted, can be restored, and can be deleted permanently.
- **Policies** — post and category access is enforced by `PostPolicy` / `CategoryPolicy` (post editors can only change their own posts unless they are admins).
- **Queued mail** — verification and password-reset emails are sent on the queue.
- **Activity log export & retention** — download the filtered activity log as CSV or JSON; `activitylog:clean` runs daily via the scheduler.
- **CRUD scaffolding** — `php artisan starter:make-crud Article` generates the model, migration, factory, service, policy and a test for a new resource.
- **Service-oriented architecture** — business logic lives in dedicated service classes (`UserService`, `PostService`, `CategoryService`, `SettingService`) instead of controllers or components.
- **Modern front-end** — Tailwind CSS 4 and Vite 8, wired up and ready to go.
- **Tested & analysed** — a PHPUnit feature/unit suite covering auth, profiles, admin pages, user/post/category management, the API, and localization, plus Pint (code style) and Larastan (static analysis), all run on CI.

## Tech Stack

| Layer            | Technology                                             |
| ---------------- | ------------------------------------------------------ |
| Framework        | Laravel 13 (PHP 8.3+)                                   |
| UI / Interactivity | Livewire 4 (single-file components)                  |
| Styling / Build  | Tailwind CSS 4, Vite 8                                  |
| Auth & Access    | Laravel Auth + spatie/laravel-permission               |
| Auditing         | spatie/laravel-activitylog                             |
| i18n             | spatie/laravel-translatable + custom locale middleware |
| Database         | SQLite by default (any Laravel-supported driver works) |
| Testing          | PHPUnit 12                                              |
| Code quality     | Laravel Pint + Larastan (PHPStan)                      |

## Requirements

- PHP 8.3 or higher
- Composer
- Node.js & npm

## Getting Started

```bash
# 1. Install PHP and JS dependencies, create .env, generate a key,
#    run migrations, and build assets — all in one step:
composer setup

# 2. Seed the database: the admin account, roles & permissions (always),
#    plus a test user and 10 sample users (demo data)
php artisan db:seed

# 3. Start the full development environment
#    (server + queue worker + log tailer + Vite)
composer dev
```

The `composer setup` script runs `composer install`, copies `.env.example` to `.env`, generates the application key, runs migrations, and builds the front-end assets. The `composer dev` script uses [concurrently](https://www.npmjs.com/package/concurrently) to run the PHP server, queue listener, `pail` log viewer, and the Vite dev server together.

### Production notes

- **Cover images** are stored on the `public` disk. Run `php artisan storage:link` once per environment.
- **Queues:** mail is queued (`QUEUE_CONNECTION=database` by default). Run `php artisan queue:work` as a long-running process; `composer dev` starts one for you locally.
- **Scheduler:** run `php artisan schedule:work` locally, or add `* * * * * php /path/to/artisan schedule:run` to cron in production. The scheduler prunes the activity log.
- **Maintenance mode** is controlled from **Admin → Settings**; it does not need `php artisan down`.

### Admin login

Sign in at `/login` with the seeded admin account:

| Field    | Default value            |
| -------- | ------------------------ |
| Email    | `admin@majidastarter.com` |
| Password | `password`               |

The admin is created by `database/seeders/AdminUserSeeder.php` and is always seeded, including by `php artisan db:seed --class=MinimalDatabaseSeeder` and `ConfigDatabaseSeeder`. Override the defaults before seeding by setting these in `.env`:

```dotenv
ADMIN_NAME="Admin User"
ADMIN_EMAIL=admin@majidastarter.com
ADMIN_PASSWORD=change-me-now
```

> **Change the admin password before deploying.** `password` is a well-known default. Re-running the seeder updates the existing admin's password to the current `ADMIN_PASSWORD`, so you can also set a new one with `php artisan db:seed --class=AdminUserSeeder`.

### Other seeded accounts

`php artisan db:seed` also creates the following demo accounts (these are not created by the minimal seeders):

| Role | Email              | Password   | Notes                  |
| ---- | ------------------ | ---------- | ---------------------- |
| User | `test@example.com` | `password` | Standard verified user |

Ten additional random users are created with the same `password`. Their details come from `UserFactory`.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # JSON API controllers backing each service
│   │   ├── Auth/          # Logout
│   │   └── LocaleController.php
│   └── Middleware/
│       ├── SetLocale.php          # Applies the session locale on every request
│       └── EnsureUserIsAdmin.php  # Guards admin routes ('admin' alias)
├── Models/
│   ├── User.php           # Roles, activity logging, email verification
│   ├── Post.php           # Blog/content posts
│   ├── Category.php       # Post categories
│   └── Setting.php        # Translatable, singleton-style site settings
└── Services/
    ├── UserService.php    # Registration, CRUD, role toggling, stats
    ├── PostService.php    # Post CRUD, search, stats
    ├── CategoryService.php# Category CRUD, options, stats
    └── SettingService.php # Reads/updates the site settings record

resources/views/
├── pages/                 # Livewire single-file page components (⚡ prefixed)
│   ├── auth/              # login, register, forgot/reset password, verify email
│   ├── admin/            # dashboard, users, posts, categories, activity log, settings
│   ├── settings/profile   # self-service profile management
│   └── dashboard          # authenticated user dashboard
├── welcome.blade.php      # marketing landing page
├── blog.blade.php         # public blog page
└── docs.blade.php         # public documentation page

routes/
├── web.php                # Guest, authenticated, verified, and admin route groups
├── api.php                # JSON API endpoints (see below)
└── console.php

lang/
├── en/  ar/  fr/          # PHP translation files per locale
└── *.json                 # JSON translation strings per locale
```

## Routing Overview

### Web

- **Public** — `/` (landing), `/blog` (listing with search), `/blog/{slug}` (post), `/docs`
- **Guest routes** — `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/two-factor-challenge` (second step of sign-in)
- **Authenticated routes** — `/verify-email`, `/settings/profile`, `/settings/two-factor`, `/logout`
- **Verified routes** — `/dashboard`
- **Admin routes** (`verified` + `admin` middleware) —
  `/admin/dashboard`, `/admin/users/create`, `/admin/users/{user}/edit`,
  `/admin/categories`, `/admin/categories/create`, `/admin/categories/{category}/edit`,
  `/admin/posts`, `/admin/posts/create`, `/admin/posts/{post}/edit`,
  `/admin/activity-log`, `/admin/activity-log/export/{csv|json}`, `/admin/settings`
- **Locale switch** — `/language/{locale}` persists the chosen locale to the session

### API

All endpoints are prefixed with `/api` and defined in `routes/api.php`. Each maps
directly onto a service method and is protected by the same authorization gates
(`can:manage users`, `can:manage posts`, etc.) as the web UI. Authentication is
session/cookie based (`auth` middleware) — call `POST /api/auth/login` first to
establish a session.

| Method & path                       | Guard                  | Backed by                       |
| ----------------------------------- | ---------------------- | ------------------------------- |
| `POST   /api/auth/register`         | guest                  | `UserService::register`         |
| `POST   /api/auth/login`            | guest                  | `UserService::attempt`          |
| `GET    /api/profile`               | auth                   | current user                    |
| `PUT    /api/profile`               | auth                   | `UserService::updateProfile`    |
| `PUT    /api/profile/password`      | auth                   | `UserService::updatePassword`   |
| `DELETE /api/profile`               | auth                   | `UserService::deleteOwnAccount` |
| `GET    /api/categories`            | `can:manage categories`| `CategoryService::searchPaginated` |
| `POST   /api/categories`            | `can:manage categories`| `CategoryService::create`       |
| `PUT    /api/categories/{category}` | `can:manage categories`| `CategoryService::update`       |
| `DELETE /api/categories/{category}` | `can:manage categories`| `CategoryService::delete`       |
| `GET    /api/posts`                 | `can:manage posts`     | `PostService::searchPaginated`  |
| `POST   /api/posts`                 | `can:manage posts`     | `PostService::create`           |
| `PUT    /api/posts/{post}`          | `can:manage posts`     | `PostService::update`           |
| `DELETE /api/posts/{post}`          | `can:manage posts`     | `PostService::delete`           |
| `GET    /api/users`                 | `can:manage users`     | `UserService::searchPaginated`  |
| `POST   /api/users`                 | `can:manage users`     | `UserService::create`           |
| `PUT    /api/users/{user}`          | `can:manage users`     | `UserService::updateUser`       |
| `DELETE /api/users/{user}`          | `can:manage users`     | `UserService::delete`           |
| `POST   /api/users/{user}/toggle-admin` | `can:manage users` | `UserService::toggleAdminRole`  |
| `GET    /api/settings`              | `can:manage settings`  | `SettingService::current`       |
| `PUT    /api/settings`              | `can:manage settings`  | `SettingService::update`        |

#### Authentication, trash & restore

| Method & path                          | Guard                   | Purpose                                              |
| -------------------------------------- | ----------------------- | ---------------------------------------------------- |
| `POST   /api/auth/token`               | throttled               | Issue a Sanctum token (`email`, `password`, `device_name`, and `two_factor_code` when 2FA is on) |
| `DELETE /api/auth/token`               | bearer token or session | Revoke the token used for this request               |
| `POST   /api/{posts,categories,users}/{id}/restore` | `can:manage …` | Restore a trashed record                             |
| `DELETE /api/{posts,categories,users}/{id}/force`   | `can:manage …` | Permanently delete a trashed record                   |

Authenticated endpoints accept either `Authorization: Bearer <token>` or the session cookie. List endpoints accept `trashed=only|with`, plus `status`/`category_id`/`role` filters and `sort`/`direction`.

> Several resources also expose helper endpoints — `.../options`, `.../stats`,
> and `users/roles` — used to populate select boxes and dashboard widgets.

## Localization

Available locales are configured in `config/app.php` under `available_locales`:

```php
'available_locales' => [
    'en' => ['label' => 'English', 'dir' => 'ltr'],
    'ar' => ['label' => 'العربية', 'dir' => 'rtl'],
    'fr' => ['label' => 'Français', 'dir' => 'ltr'],
],
```

The `SetLocale` middleware applies the session's chosen locale on every request, the `LocaleController` handles switching, and text direction (`ltr`/`rtl`) is derived automatically for correct RTL rendering. Add a new language by adding an entry here and a matching `lang/{locale}` directory (and/or `lang/{locale}.json` file).

The `php artisan translations:sync` command (`app/console/Commands/SyncTranslationKeys.php`) scaffolds missing keys for non-base locales, prefixing untranslated values with `[TODO]` so they are easy to find and complete.

## Roles & Permissions

Roles and permissions are seeded by `RolesAndPermissionsSeeder`. The `admin` role is granted the following permissions:

- `view admin dashboard`
- `manage users`
- `manage posts`
- `manage categories`
- `view activity log`
- `manage settings`

Check for admin access in code via `$user->isAdmin()` or the `admin` route middleware alias.

Post and category actions additionally go through `App\Policies\PostPolicy` and `App\Policies\CategoryPolicy`. Editing or trashing a post requires `manage posts` and, unless the user is an admin, that they are the post's author.

## Scaffolding a New Resource

```bash
php artisan starter:make-crud Article
```

This creates `Article` model, migration, factory, `ArticleService`, `ArticlePolicy` and `ArticleModuleTest`, using soft deletes and activity logging. It never overwrites existing files. Afterwards add the `manage articles` permission to `RolesAndPermissionsSeeder`, then add the routes and screens you need.

## Testing & Code Quality

```bash
# Run the test suite
composer test          # or: php artisan test

# Check code style (Laravel Pint)
vendor/bin/pint --test # add --dirty to only check changed files, drop --test to fix

# Static analysis (Larastan / PHPStan)
composer analyse
```

The suite (in `tests/Feature` and `tests/Unit`) covers authentication, email verification, profile management, user/post/category management, admin pages, the REST API, and localization. Tests run against an in-memory SQLite database (see `phpunit.xml`).

## Continuous Integration

A GitHub Actions workflow (`.github/workflows/ci.yml`) runs on every push to `develop`/`main` and on every pull request. It:

- runs the test suite on PHP 8.3 and 8.4 (building front-end assets first), and
- checks code style with Pint and runs Larastan static analysis.

## License

MajidaStarter is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). It is built on the [Laravel framework](https://laravel.com), which is also MIT licensed.
</content>
</invoke>
