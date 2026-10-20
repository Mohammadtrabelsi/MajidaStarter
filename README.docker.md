# Docker Development Environment

A containerised local development stack for a Laravel application. The Laravel
project lives in `src/`, and Docker provides everything around it: PHP-FPM,
Nginx, MySQL, phpMyAdmin, and Redis. Nothing needs to be installed on your host
except Docker and Docker Compose.

---

## Folder structure

```
.
├── docker-compose.yml        # Defines all services (app, web, db, phpmyadmin, redis)
├── .env.docker.example       # Sample env for the Docker stack -> copy to .env
├── .env                       # Your local Docker settings (git-ignored, you create it)
├── README.docker.md          # This file
├── docker/
│   ├── php/
│   │   ├── Dockerfile        # PHP 8.3-FPM image with Laravel extensions + Composer
│   │   └── php.ini           # Custom PHP settings for development
│   └── nginx/
│       └── default.conf      # Nginx vhost pointing at src/public
└── src/                       # <-- The Laravel application goes here
    ├── public/               # Web root (index.php)
    ├── .env                  # Laravel's OWN env (DB_HOST=db lives here)
    └── ...
```

> Two `.env` files, two different jobs:
> - **`./.env`** configures the **Docker stack** (ports, MySQL credentials).
> - **`./src/.env`** configures **Laravel itself** (its DB connection, app key, etc.).

---

## First-time setup

### 1. Copy the Docker environment file

```bash
cp .env.docker.example .env
```

Edit `.env` if any of the default host ports (`8080`, `8081`, `3306`, `6379`)
are already in use on your machine.

### 2. Put a Laravel project in `src/`

**Option A — scaffold a fresh Laravel app** (uses the PHP/Composer image, so you
don't need PHP locally):

```bash
docker run --rm -v "$(pwd)/src":/app -w /app composer:2 \
    composer create-project laravel/laravel .
```

**Option B — place an existing project**: copy or clone your Laravel app into
`src/` so that `src/public/index.php` and `src/artisan` exist.

### 3. Point Laravel at the `db` container

Create Laravel's own env file and set the database host to the compose service
name `db` (not `localhost`):

```bash
cp src/.env.example src/.env
```

Then edit `src/.env` so the database block reads:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# If you use Redis:
REDIS_HOST=redis
```

The `DB_*` values must match those in the root `.env` (they seed the MySQL
container). `DB_HOST=db` is what lets Laravel reach MySQL over the Docker network.

### 4. Build and start the stack

```bash
docker compose up -d --build
```

### 5. Install dependencies & finish Laravel setup

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

### 6. Run migrations

```bash
docker compose exec app php artisan migrate
```

You're done — open the app in your browser.

---

## URLs

| Service      | URL                     | Notes                                   |
|--------------|-------------------------|-----------------------------------------|
| Application  | http://localhost:8080   | Served by Nginx (`APP_PORT`)            |
| phpMyAdmin   | http://localhost:8081   | Log in with the MySQL user (`PMA_PORT`) |

phpMyAdmin login: server `db`, user `laravel`, password `secret` (or your root
user `root` / `root`), matching the values in your `.env`.

---

## Common Docker Compose commands

```bash
# Start everything in the background (rebuild images if needed)
docker compose up -d --build

# Stop the stack (keeps the database volume)
docker compose down

# Stop and DELETE the database volume (fresh DB next time)
docker compose down -v

# View logs (all services, or one)
docker compose logs -f
docker compose logs -f app

# List running services
docker compose ps

# Open a shell in the PHP container
docker compose exec app bash

# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker

# Composer inside the container
docker compose exec app composer install
docker compose exec app composer require vendor/package

# Rebuild just the PHP image after changing the Dockerfile
docker compose build app

# Restart a single service
docker compose restart web
```

---

## Services at a glance

| Service      | Image                | Purpose                                  |
|--------------|----------------------|------------------------------------------|
| `app`        | custom PHP 8.3-FPM   | Runs Laravel (PHP + Composer)            |
| `web`        | nginx:1.27-alpine    | Web server, proxies PHP to `app`         |
| `db`         | mysql:8.0            | Database (Laravel connects via `db`)     |
| `phpmyadmin` | phpmyadmin:5         | Database web UI                          |
| `redis`      | redis:7-alpine       | Cache / queue / session store            |
