# Application Feature Audit & Roadmap

> **Stack:** Laravel 13 | Livewire 4 | PHP 8.3+
> **Audit Date:** 2026-07-24

---

## 1. Executive Summary

**MajidaStarter** is a multilingual admin-panel starter kit built on **Laravel 13.8**, **Livewire 4.3**, and **PHP 8.3+**. It ships a production-grade foundation: session authentication with email verification, role/permission-based authorization (Spatie Permission), a full CRUD admin for **Users**, **Posts**, and **Categories**, translatable content (Spatie Translatable, `en`/`ar`/`fr` including RTL), an audit trail (Spatie Activitylog + a bespoke `TracksUserActions` `created_by`/`updated_by` stamp trait), a settings panel with maintenance-mode toggle, and a parallel JSON API mirroring the same service layer.

**Architecture at a glance:**

- **View-based (single-file) Livewire 4 page components** — every screen is a `resources/views/pages/**/⚡*.blade.php` file combining an anonymous `Livewire\Component` class and its template, wired through `Route::livewire('…', 'pages::…')` and the `pages::` namespace. This is the modern Livewire 4 idiom and is applied consistently.
- **A clean service layer** (`app/Services/{User,Post,Category,Setting}Service.php`) that owns all domain logic. Both Livewire components and API controllers delegate to it, so business rules live in exactly one place. This is the single strongest architectural decision in the codebase.
- **Modern PHP attributes** on models (`#[Fillable]`, `#[Hidden]` on `User`), typed properties everywhere, `casts()` methods, and readonly constructor-promoted service dependencies in controllers.

**Overall assessment — code health: strong (B+).** Pattern consistency is high, the service layer is disciplined, tests cover most feature areas, and CI runs a PHP 8.3/8.4 matrix plus Pint and PHPStan (level 5). The gaps are not structural rot; they are **(a)** a handful of concrete defects (a mis-cased command directory that silently disables a shipped tool; missing rate-limiting on the API auth endpoints), **(b)** under-use of Livewire 4's *client-side* reactivity (a lot of UI-only state still makes server round-trips), **(c)** a few missing database indexes on filtered/ordered columns, and **(d)** absent "last mile" product features (there is no public-facing rendering of the posts the admin can create; email is sent synchronously; deletes are hard deletes). None are architecturally expensive to close.

---

## 2. Current Feature Index

| Module / Domain | Components / Controllers | Capabilities | Maturity / Status |
| :--- | :--- | :--- | :--- |
| **Auth & Sessions** | `pages/auth/⚡login`, `⚡register`, `⚡forgot-password`, `⚡reset-password`, `⚡verify-email`; `Auth\LogoutController`; `Api\AuthController` | Login (with per-email+IP rate limiting), registration, password reset, email verification, logout | Production Ready |
| **User Management (Admin)** | `pages/admin/⚡dashboard`, `admin/users/⚡create`, `⚡edit`; `Api\UserController`; `UserService` | List/search/paginate, create, edit, delete, grant/revoke admin, role sync, self-action guards | Production Ready |
| **Posts** | `pages/admin/posts/⚡index`, `⚡create`, `⚡edit`; `Api\PostController`; `PostService` | Translatable CRUD, unique-slug generation, draft/published status, `published_at` sync, category & author relations | Production Ready (admin only) |
| **Categories** | `pages/admin/categories/⚡index`, `⚡create`, `⚡edit`; `Api\CategoryController`; `CategoryService` | Translatable CRUD, unique slugs, active/inactive flag, `withCount('posts')`, options list | Production Ready |
| **Profile / Account** | `pages/settings/⚡profile`; `Api\ProfileController` | Update name/email (re-verify on change), change password (`current_password` gate), resend verification, self-delete with password confirm | Production Ready |
| **Settings** | `pages/admin/⚡settings`; `Api\SettingController`; `SettingService` | Singleton settings (`firstOrCreate`), translatable site name/description, support email, maintenance-mode flag | Production Ready |
| **Activity Log / Audit** | `pages/admin/⚡activity-log`; `TracksUserActions`; Spatie Activitylog | Per-model change log (old/new), causer/subject, filter by log name + description search, `created_by`/`updated_by` stamping | Production Ready |
| **Localization / i18n** | `SetLocale` middleware, `LocaleController`, `partials/locale-switcher*`, `console/Commands/SyncTranslationKeys` | Session-driven locale, RTL support, `en`/`ar`/`fr` line files + `translations:sync` tooling | Production Ready (see 3.2 defect) |
| **Theming** | `partials/theme-script`, `partials/theme-toggle` | Persisted dark/light toggle, FOUC-free inline script | Production Ready |
| **Public / Marketing** | `welcome`, `docs`, `blog` (static `Route::view`) | Landing, docs, and a **static** blog placeholder | Placeholder / Incomplete |
| **JSON API** | `routes/api.php`, `Api\*Controller` | Full service surface as JSON, gated by the same `can:` permissions | Beta (session-auth, un-throttled login — see 3.3) |

---

## 3. Recommended Codebase Improvements

### 3.1 Livewire 4 & UI/UX Enhancements

The app already uses the best structural Livewire 4 feature — **view-based page components** — and applies good primitives (`#[Computed]`, `#[Url(history: true)]` for shareable filters, `wire:navigate`, `wire:model.live.debounce.300ms`, `wire:confirm`, `wire:loading.attr`). The opportunity is to stop paying **server round-trips for state that is purely client-side**, and to isolate re-renders.

- **Locale tab switching is a server round-trip for a UI-only concern.** In `posts/⚡create`, `posts/⚡edit`, and `⚡settings`, the language tabs call `wire:click="$set('activeLocale', '…')"`. Every tab click hits the server just to change which translation panel is visible, even though all locale inputs are already bound and present in the DOM. Move `activeLocale` to Alpine (`x-data="{ locale: 'en' }"` + `wire:show`/`x-show`) or a Livewire 4 **`$js` action**, eliminating a network hop per keystroke-adjacent interaction.
- **Adopt `@island` to scope re-renders on list screens.** In `posts/⚡index`, `categories/⚡index`, and the admin `⚡dashboard`, typing in the search box re-renders the *entire* component (stat cards, toolbar, table, pagination). Wrapping the results table in an `@island` (or extracting stat cards into their own island) confines the re-render to the changed region and keeps the stat cards static between searches.
- **Use `wire:transition` for list churn and flash messages.** Table rows (`wire:key="post-{{ $post->id }}"`) and the flash/`session('status')` banners are prime candidates for `wire:transition` so paginated/searched rows animate in/out instead of snapping. Today the flash dismissal is hand-rolled with `x-data="{ show:true }" x-init="setTimeout(...)"` — repeated verbatim across `posts/⚡index`, `⚡dashboard`, etc. Replace with a small reusable component + `wire:transition`.
- **Form DX: expose `$dirty` / `wire:dirty` state.** None of the create/edit forms give the user "unsaved changes" feedback. Add `wire:dirty` affordances (enable the Save button only when dirty, show a dirty dot on the tab whose locale panel changed) to reduce accidental navigation loss — a natural pairing with the `wire:navigate` SPA flow.
- **Prefer `$js` client actions for ephemeral confirmations.** The "saved" toast in `⚡settings`/`⚡profile` uses `$this->dispatch('settings-saved')` → Alpine window listener. A Livewire 4 `$js('showSaved')` action keeps that entirely client-side without a Blade→Alpine event bridge.
- **Consider `#[Json]` for the stats payloads** surfaced to the client so numeric stat cards can update reactively without re-serializing the whole component state.

### 3.2 Performance & Database Optimization

- **`translations:sync` command is silently dead on Linux (case-sensitivity defect).** The file lives at `app/console/Commands/SyncTranslationKeys.php` (lowercase **`console`**) but declares namespace `App\Console\Commands`. PSR-4 (`"App\\": "app/"`) and Laravel's default command auto-discovery both resolve to `app/Console/Commands`, which does not exist on a case-sensitive filesystem. The command is therefore **not autoloadable and not registered** in CI or production (it may appear to work only on case-insensitive macOS/Windows dev machines). **Fix:** rename the directory to `app/Console/Commands`. *(High priority — it is a shipped DX tool that does not run where it matters.)*
- **Missing indexes on filtered/ordered columns.** Foreign keys (`category_id`, `user_id`, `created_by`, `updated_by`) get indexes automatically via `constrained()`, and slugs are `unique`. But these hot columns are **un-indexed**:
  - `posts.status` — filtered by `PostService::stats()` and `scopePublished()`.
  - `posts.published_at` — the natural ordering column for any public feed.
  - `categories.is_active` — filtered by `CategoryService::stats()`.
  - `users.created_at` — filtered by `UserService::stats()` (`whereDate today`, `>= subWeek`).
  Add a migration with `->index()` (or composite `['status', 'published_at']` for the eventual public feed).
- **Stats run N separate `COUNT` queries per render and are un-cached.** `UserService::stats()` fires 4 counts, `PostService::stats()` 3, `CategoryService::stats()` 3 — on every page load and, because they are `#[Computed]`, on every search re-render (unless islanded per 3.1). Collapse each into a single aggregate (`selectRaw('count(*) total, sum(status = "published") published, …')`) and/or wrap in a short `Cache::remember(…, 60, …)`. Bust the cache from model `saved`/`deleted` events.
- **Search only matches `slug`, not translatable content.** `PostService::searchPaginated()` and `CategoryService::searchPaginated()` filter with `where('slug', 'like', …)`. A user searching a post *title* gets nothing, because titles are JSON. Add a `whereRaw`/`->where('title->en', 'like', …)` (or a proper search index) so search covers human-visible text.
- **Queue the email pipeline.** `UserService::register()` fires `Registered`, and `⚡profile::resendVerification()` calls `sendEmailVerificationNotification()` — both send mail **synchronously** on the request thread. Implement `ShouldQueue` on the verification notification (or a queued listener) so registration/verification isn't blocked on SMTP latency. There is currently **no `app/Jobs`**, no queued notifications, and `QUEUE_CONNECTION` defaults to `sync`.
- **`activity-log` recomputes `logNames()` (a `DISTINCT` scan) on every render.** Wrap it in `#[Computed]` (it is called as a plain method today) and/or cache it briefly.

### 3.3 Security & Authorization

- **API auth endpoints are un-throttled.** The web `⚡login` component has solid per-`email|ip` `RateLimiter` logic, but `POST /api/auth/login` and `/api/auth/register` (`Api\AuthController`) have **no throttle middleware**, leaving a credential-stuffing / enumeration surface. Add `->middleware('throttle:…')` (or the same `RateLimiter` guard) to the API auth routes.
- **No model-level `Policy` classes; authorization is permission-string only.** Authorization works (`can:manage posts` on routes + `$this->authorize(...)` in components), but there are **no `app/Policies`** and no gates in `AppServiceProvider`. Consequently authorization is *coarse*: any user with `manage posts` can edit or delete **any** post regardless of authorship — `PostController::update/destroy` and the Livewire delete actions never check `created_by`/`user_id` ownership. If per-author ownership is ever desired, introduce `PostPolicy`/`CategoryPolicy` with `update`/`delete` abilities and switch to `authorize($post)`.
- **Password rules are weak (`min:8` only).** Login, register, reset, profile, and admin user-create all validate `min:8` with no complexity or breach check. Adopt `Password::defaults()` (in `AppServiceProvider::boot()`) with `->min(8)->mixedCase()->numbers()->uncompromised()` for a single, centrally-tunable policy.
- **`deleteOwnAccount()` logs out but does not invalidate/regenerate the session token.** `UserService::deleteOwnAccount()` calls `Auth::logout()` then deletes the user; it should also `session()->invalidate()` + `regenerateToken()` to fully tear down the session, consistent with the login flow's `session()->regenerate()`.
- **Duplicated `DomainException` handling instead of a renderable exception.** The "cannot delete/demote yourself" rule throws `DomainException`, then each caller (`Api\UserController::destroy`/`toggleAdmin`, `⚡dashboard::toggleAdmin`/`deleteUser`) re-implements a `try/catch`→422/`addError`. Make it a renderable exception (or a dedicated `SelfActionException`) so the mapping to a 422 lives in one place and can't drift.
- **Leverage `Route::metadata()` for admin route classification.** Laravel 13's `Route::metadata()` could tag admin routes (e.g. `->metadata(['audit' => true, 'area' => 'admin'])`) to drive centralized audit middleware, breadcrumbs, and nav highlighting instead of the current `request()->routeIs('admin.*')` string matching scattered through `layouts/app.blade.php`.
- **API is session-stateful, which constrains real API clients.** `routes/api.php` is guarded by the `auth` (web/session) middleware and `AuthController::login` regenerates the session — so the "API" is really an internal same-origin JSON surface, not a token API. If external/machine clients are a goal, add Laravel Sanctum token issuance; if not, document it as internal-only so consumers don't attempt bearer-token auth.

---

## 4. Unimplemented & Missed Functionalities

### High Priority
- **No public rendering of Posts.** The admin can author translatable, categorized, draft/published posts with `published_at`, but `/blog` is a **static** `Route::view('blog')` — nothing lists or shows published posts. The core content the app produces has no reader-facing surface. Build a public post index + detail (respecting `scopePublished()`, locale, and `published_at`).
- **Fix the mis-cased `console` directory** so `translations:sync` actually loads (see 3.2). It is the maintenance backbone of the i18n workflow.
- **Rate-limit the API auth endpoints** (see 3.3).
- **Queue verification/reset emails** so auth flows don't block on the mailer (see 3.2).

### Medium Priority
- **Soft deletes + restore.** Users, posts, and categories are **hard-deleted** (`->delete()` behind a `wire:confirm` only). Add `SoftDeletes` + a trash/restore view so an accidental admin delete is recoverable — especially valuable given the audit-log emphasis.
- **Search & filter depth.** Search titles/names (not just slugs); add status/category/date filters and column sorting to the admin tables; add bulk actions (bulk publish/delete) with `wire:model` row selection.
- **In-app notifications & richer flash system.** A unified toast/notification component (per 3.1) plus optional Laravel database notifications for admin events (new registration, role change).
- **Activity-log export & retention.** CSV/JSON export of filtered activity, plus a prune command/schedule (Spatie ships `activitylog:clean`) — neither is wired up.
- **Media/image handling for posts.** No cover image or embedded media; add `spatie/laravel-medialibrary` or a simple upload field with validation.
- **Scheduler & maintenance-mode enforcement.** A `maintenance_mode` setting exists on `Setting` but nothing consumes it (no middleware puts the app into maintenance based on it); wire it to a middleware or `php artisan down` bridge.

### Low Priority / Nice-to-Have
- **First-party AI assist (Laravel 13).** Auto-generate a post `excerpt` from `body`, suggest slugs, or machine-translate the `ar`/`fr` panels to seed the `[TODO]` placeholders produced by `translations:sync` — a natural fit for a queued action behind the existing service layer.
- **Advanced reporting dashboard.** Trend charts for posts published over time, users-per-week, category distribution (the counts already exist in the `*Service::stats()` methods).
- **DX tooling.** A `--fail-on-missing` flag for `translations:sync` to run in CI and fail when a locale is missing keys; a factory/seeder for demo posts+categories to make the empty admin tables demonstrable out of the box.
- **Raise PHPStan to level 6+** incrementally (currently level 5, with a comment inviting the increase) and add a test for the translation-sync command.

---

## 5. Prioritized Action Plan & Next Steps

### Phase 1 (Immediate Fixes) — low effort, high impact
- [ ] Rename `app/console/Commands/` → `app/Console/Commands/` so `App\Console\Commands\SyncTranslationKeys` autoloads and registers on Linux/CI.
- [ ] Add throttle middleware to `POST /api/auth/login` and `/api/auth/register` in `routes/api.php`.
- [ ] Add a migration indexing `posts.status`, `posts.published_at`, `categories.is_active`, and `users.created_at`.
- [ ] Centralize password rules via `Password::defaults()` in `AppServiceProvider::boot()`; apply to login/register/reset/profile/user-create.
- [ ] Harden `UserService::deleteOwnAccount()` with `session()->invalidate()` + `regenerateToken()`.

### Phase 2 (Architectural & Performance Refactoring)
- [ ] Move `activeLocale` tab state client-side (`wire:show`/`$js`) in `posts/⚡create`, `posts/⚡edit`, `⚡settings`; remove the `$set` round-trips.
- [ ] Introduce `@island` boundaries around the results tables + stat cards in `posts/⚡index`, `categories/⚡index`, admin `⚡dashboard`.
- [ ] Collapse `*Service::stats()` into single aggregate queries and cache them; bust on model `saved`/`deleted`.
- [ ] Extend `searchPaginated()` in `PostService`/`CategoryService` to match translatable `title`/`name`, not just `slug`.
- [ ] Convert email verification/reset to `ShouldQueue`; add a `Registered` queued listener.
- [ ] Replace duplicated `DomainException` try/catch with a renderable exception.
- [ ] Add `PostPolicy`/`CategoryPolicy` (even if permissive today) to make ownership rules a one-line change later.
- [ ] Extract the repeated Alpine flash/toast into one reusable `wire:transition` component.

### Phase 3 (New Feature Rollouts)
- [ ] Public post index + detail pages driven by `scopePublished()`, locale, and `published_at` (replaces the static `/blog`).
- [ ] Soft deletes + trash/restore for users, posts, categories.
- [ ] Admin table upgrades: status/category/date filters, sortable columns, bulk publish/delete.
- [ ] Wire `Setting::maintenance_mode` to real maintenance-mode enforcement middleware.
- [ ] Activity-log export (CSV/JSON) + scheduled `activitylog:clean` retention.
- [ ] Media/cover-image support for posts.
- [ ] AI assists (queued): excerpt generation, slug suggestion, seed-translation of `[TODO]` locale placeholders.

---

*Audit scope: static analysis only. No application logic was modified in producing this report — all file paths, class names, routes, and component references above reflect the repository as it currently stands on branch `claude/laravel-livewire-audit-exze5x`.*
