# Environnement de développement Docker

Un environnement de développement conteneurisé pour l'application Laravel MajidaStarter. L'application Laravel se trouve dans `src/`, et Docker fournit tout le reste : PHP-FPM, Nginx, MySQL, phpMyAdmin et Redis. Il n'est rien à installer sur votre machine à part Docker et Docker Compose.

> 🌐 Autres langues : [English](../en/README.docker.md) · [العربية](../ar/README.docker.md)

> **Important :** toutes les commandes de ce guide s'exécutent depuis la **racine du dépôt**, même si ce fichier se trouve dans `docs/fr/`.

> **Attention :** la pile Docker monte `./src` comme racine de l'application. Dans ce dépôt, l'application Laravel se trouve au niveau supérieur ; l'étape de première installation ci-dessous la copie donc dans `src/`. Node.js n'est pas présent dans l'image PHP : les ressources front-end sont compilées dans un conteneur Node séparé.

---

## Structure des dossiers

```
.
├── docker-compose.yml        # Définit tous les services (app, web, db, phpmyadmin, redis)
├── .env.docker.example       # Exemple de variables pour la pile Docker -> à copier vers .env
├── .env                      # Vos réglages Docker locaux (ignoré par git, à créer)
├── docs/
│   ├── en/README.docker.md   # Guide en anglais
│   ├── fr/README.docker.md   # Ce fichier
│   └── ar/README.docker.md   # Guide en arabe
├── docker/
│   ├── php/
│   │   ├── Dockerfile        # Image PHP-FPM avec les extensions Laravel et Composer (voir la note sur la version PHP ci-dessous)
│   │   └── php.ini           # Réglages PHP personnalisés pour le développement
│   └── nginx/
│       └── default.conf      # Hôte virtuel Nginx pointant vers src/public
└── src/                      # <-- L'application Laravel (montée dans les conteneurs)
    ├── public/               # Racine web (index.php)
    ├── .env                  # Le .env de Laravel (DB_HOST=db se trouve ici)
    └── ...
```

> Deux fichiers `.env`, deux rôles différents :
> - **`./.env`** configure la **pile Docker** (ports, identifiants MySQL, UID/GID).
> - **`./src/.env`** configure **Laravel lui-même** (sa connexion à la base de données, la clé de l'application, etc.).

---

## Première installation

### 1. Copier le fichier d'environnement Docker

```bash
cp .env.docker.example .env
```

Modifiez `.env` si l'un des ports hôte par défaut (`8080`, `8081`, `3306`, `6379`) est déjà utilisé sur votre machine.

### 2. Copier l'application Laravel dans `src/`

Exécutez cette commande depuis la racine du dépôt. Elle copie les fichiers de l'application et laisse les fichiers Docker en place :

```bash
cp -R app bootstrap config database lang public resources routes storage tests \
      artisan composer.json composer.lock package.json package-lock.json \
      vite.config.js phpunit.xml phpstan.neon.dist .env.example src/
```

Ensuite, `src/public/index.php` et `src/artisan` existent. N'exécutez pas `composer create-project` ici : cela créerait une application Laravel nouvelle et sans rapport.

### 3. Faire pointer Laravel vers le conteneur `db`

Créez le fichier d'environnement propre à Laravel :

```bash
cp src/.env.example src/.env
```

Modifiez ensuite `src/.env` pour que les valeurs de base de données et de Redis soient :

```dotenv
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# Facultatif : utiliser Redis pour le cache et les sessions au lieu de la base de données
# REDIS_HOST=redis
# CACHE_STORE=redis
# SESSION_DRIVER=redis
```

Les valeurs `DB_*` doivent correspondre à celles du `.env` racine (elles initialisent le conteneur MySQL). `DB_HOST=db` permet à Laravel de joindre MySQL sur le réseau Docker. Le service Redis est disponible sous le nom `redis`, mais la configuration par défaut ne l'utilise pas.

### 4. Construire et démarrer la pile

```bash
docker compose up -d --build
```

### 5. Installer les dépendances PHP

```bash
docker compose exec app composer install
```

> **Note sur la version de PHP :** `composer.lock` exige PHP 8.4, mais `docker/php/Dockerfile` construit à partir de `php:8.3-fpm-alpine`. Tant que l'image de base n'est pas remplacée par `php:8.4-fpm-alpine`, cette étape échoue avec une erreur de plateforme. Modifiez la ligne `FROM` puis exécutez `docker compose build app` avant d'installer.

### 6. Compiler les ressources front-end

Le conteneur `app` n'a pas Node.js : compilez les ressources dans un conteneur Node jetable. Node 20 correspond à la CI :

```bash
docker run --rm -v "$(pwd)/src":/app -w /app node:20-alpine sh -c "npm ci && npm run build"
```

### 7. Exécuter les migrations et créer le lien de stockage

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link
```

`storage:link` est nécessaire pour les images de couverture des articles. Ajoutez `--seed` à `migrate` si vous voulez les comptes de démonstration (voir la liste dans le [README](README.md)).

### 8. Démarrer les workers en arrière-plan

Les e-mails sont mis en file par défaut (`QUEUE_CONNECTION=database`), et le planificateur purge le journal d'activité et reconstruit `public/sitemap.xml`. Aucun des deux ne tourne automatiquement dans la pile : démarrez-les en arrière-plan :

```bash
docker compose exec -d app php artisan queue:work
docker compose exec -d app php artisan schedule:work
```

Vous avez terminé : ouvrez l'application dans votre navigateur.

---

## URL

| Service      | URL                      | Remarques                                   |
|--------------|--------------------------|---------------------------------------------|
| Application  | http://localhost:8080    | Servie par Nginx (`APP_PORT`)               |
| Contrôle de santé | http://localhost:8080/up | Point de contrôle de disponibilité utilisé par la carte de santé d'administration |
| phpMyAdmin   | http://localhost:8081    | Connexion avec l'utilisateur MySQL (`PMA_PORT`) |

Connexion à phpMyAdmin : serveur `db`, utilisateur `laravel`, mot de passe `secret` (ou votre utilisateur root `root` / `root`), selon les valeurs de votre `.env`.

---

## Commandes Docker Compose courantes

```bash
# Tout démarrer en arrière-plan (reconstruit les images si nécessaire)
docker compose up -d --build

# Arrêter la pile (conserve le volume de la base de données)
docker compose down

# Arrêter et SUPPRIMER le volume de la base de données (base neuve la prochaine fois)
docker compose down -v

# Voir les journaux (tous les services, ou un seul)
docker compose logs -f
docker compose logs -f app

# Lister les services en cours d'exécution
docker compose ps

# Ouvrir un shell dans le conteneur PHP
docker compose exec app bash

# Lancer des commandes artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker
docker compose exec app php artisan test

# Composer dans le conteneur
docker compose exec app composer install
docker compose exec app composer require vendor/package

# Compilation front-end (depuis la racine du dépôt, comme à l'étape 6)
docker run --rm -v "$(pwd)/src":/app -w /app node:20-alpine sh -c "npm ci && npm run build"

# Reconstruire uniquement l'image PHP après une modification du Dockerfile
docker compose build app

# Redémarrer un seul service
docker compose restart web
```

---

## Vue d'ensemble des services

| Service      | Image                | Rôle                                     |
|--------------|----------------------|------------------------------------------|
| `app`        | PHP-FPM personnalisé (8.3 actuellement, voir la note) | Exécute Laravel (PHP + Composer) |
| `web`        | nginx:1.27-alpine    | Serveur web, redirige PHP vers `app`     |
| `db`         | mysql:8.0            | Base de données (Laravel se connecte via `db`) |
| `phpmyadmin` | phpmyadmin:5         | Interface web de la base de données      |
| `redis`      | redis:7-alpine       | Stockage optionnel du cache / des sessions (non utilisé par défaut) |

L'image `app` inclut l'extension PHP `redis`, donc Redis fonctionne dès que `REDIS_HOST` et les pilotes sont définis dans `src/.env`.
