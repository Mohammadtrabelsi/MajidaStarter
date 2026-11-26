# MajidaStarter

MajidaStarter est un kit de démarrage **Laravel 13 + Livewire 4** prêt à l'emploi. Il fournit tout ce qu'on reconstruit habituellement au début de chaque projet : authentification avec prise en charge de la double authentification, panneau d'administration, contrôle d'accès basé sur les rôles, journalisation d'activité, prise en charge multilingue (LTR/RTL), thème clair/sombre, module de contenu (articles et catégories), API REST correspondante et couche de services claire. Vous pouvez ainsi passer la partie répétitive et commencer directement les fonctionnalités.

> 🌐 Autres langues : [English](../en/README.md) · [العربية](../ar/README.md)

## Points forts

- **Flux d'authentification complet** : inscription, connexion, déconnexion, réinitialisation du mot de passe et vérification de l'e-mail, le tout sous forme de composants Livewire à fichier unique. La connexion est limitée en nombre de tentatives par e-mail et par adresse IP.
- **Double authentification** : applications TOTP (RFC 6238) avec codes de récupération à usage unique, gérée dans **Paramètres → Double authentification** et exigée à la connexion web et API une fois activée. Aucune dépendance supplémentaire n'est nécessaire.
- **Panneau d'administration** : un back-office dédié, protégé par des permissions, pour gérer les utilisateurs, les articles et les catégories, consulter l'activité et modifier les paramètres du site.
- **Module de contenu** : CRUD complet pour les **articles** et les **catégories** dans le panneau d'administration. Les articles publiés apparaissent sur la page publique `/blog`, avec recherche, pages de détail et images de couverture ; `/docs` est le guide intégré. Les suppressions vont dans une **corbeille** et peuvent être restaurées ; la publication/suppression groupée, les filtres et le tri sont intégrés aux listes d'administration.
- **Contrôle d'accès par rôles et permissions** : basé sur [spatie/laravel-permission](https://spatie.be/docs/laravel-permission), avec un rôle `admin` créé par défaut.
- **Journalisation d'activité** : chaque modification significative des utilisateurs, articles, catégories et paramètres est enregistrée avec [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) et consultable depuis le panneau d'administration.
- **Export et conservation du journal d'activité** : téléchargement du journal filtré en CSV ou JSON ; `activitylog:clean` s'exécute chaque jour via le planificateur.
- **Notifications CRUD** : les administrateurs reçoivent une notification enregistrée lorsque des articles, catégories, utilisateurs ou paramètres sont créés, modifiés, supprimés ou restaurés, et peuvent les consulter sur une page de notifications accessible depuis la cloche. L'auteur d'une modification n'est jamais notifié de son propre changement. Voir [Notes sur les fonctionnalités](#notes-sur-les-fonctionnalités).
- **Plan du site XML** : `php artisan sitemap:generate` écrit `/sitemap.xml` (via [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap)) à partir des pages publiques et des articles publiés ; il est reconstruit chaque heure par le planificateur et après chaque modification d'article, et déclaré dans un `/robots.txt` dynamique. Voir [Notes sur les fonctionnalités](#notes-sur-les-fonctionnalités).
- **Étiquettes** : étiquettes d'article traduisibles via [spatie/laravel-tags](https://spatie.be/docs/laravel-tags) : sélecteur réutilisable dans le formulaire d'article, écran d'administration dédié et filtrage dans la liste d'administration, sur `/blog?tag=` et dans l'API.
- **Données structurées SEO et RSS** : JSON-LD schema.org (Organization, WebSite, BreadcrumbList, BlogPosting) avec [spatie/schema-org](https://github.com/spatie/schema-org), balises canonical et Open Graph, et flux RSS sur `/feed` avec [spatie/laravel-feed](https://github.com/spatie/laravel-feed). Les slugs sont générés et conservés grâce à [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable).
- **Protection anti-spam** : les formulaires d'inscription et de mot de passe oublié comportent un piège à robots (honeypot) et une vérification de délai ([spatie/laravel-honeypot](https://github.com/spatie/laravel-honeypot)).
- **Sauvegardes et supervision** : sauvegardes planifiées et surveillées de la base de données et des fichiers envoyés ([spatie/laravel-backup](https://spatie.be/docs/laravel-backup)) et contrôles de santé de l'application ([spatie/laravel-health](https://spatie.be/docs/laravel-health)), chacun avec une page d'administration ; un `/health.json` protégé par jeton pour les outils de surveillance.
- **PDF et tableurs** : PDF des articles à partir d'une mise en page Blade commune ([spatie/laravel-pdf](https://spatie.be/docs/laravel-pdf), pilote DomPDF), exports CSV/XLSX en flux et import validé des catégories ([spatie/simple-excel](https://github.com/spatie/simple-excel)). Voir **[docs/spatie-integrations.md](../spatie-integrations.md)** pour le détail de ces fonctionnalités.
- **Statistiques de visites** : les pages vues sur le site public sont comptées (robots, administration, API et requêtes de ressources sont exclus) et affichées sur les tableaux de bord.
- **État du système** : le tableau de bord d'administration vérifie la base de données, le cache et la file d'attente, et `/up` expose un point de contrôle de disponibilité pour les outils de surveillance.
- **API REST** : chaque service est exposé sous forme de points de terminaison JSON sous `/api`, protégés par les mêmes règles d'autorisation que l'interface web. Les sessions et les jetons d'accès personnels Sanctum sont tous deux acceptés (voir [API](#api)).
- **Localisation (i18n) avec prise en charge du RTL** : anglais, arabe et français sont fournis par défaut, avec un sélecteur de langue, la mémorisation de la langue par session et la gestion automatique du sens d'écriture.
- **Paramètres traduisibles** : le nom et la description du site sont stockés par langue grâce à [spatie/laravel-translatable](https://spatie.be/docs/laravel-translatable).
- **Bascule thème clair / sombre** : un sélecteur persistant, sans clignotement au chargement.
- **Mode maintenance** : activable dans les paramètres d'administration ; les visiteurs voient une page 503 (ou du JSON sur `/api`), tandis que les administrateurs, la connexion, le changement de langue et `/up` restent accessibles.
- **Suppressions réversibles et corbeille** : les utilisateurs, articles et catégories sont supprimés logiquement, peuvent être restaurés et peuvent être supprimés définitivement.
- **Politiques d'accès** : l'accès aux articles et aux catégories est contrôlé par `PostPolicy` / `CategoryPolicy` (les éditeurs ne peuvent modifier que leurs propres articles, sauf s'ils sont administrateurs).
- **E-mails en file d'attente** : les e-mails de vérification et de réinitialisation du mot de passe sont envoyés via la file d'attente.
- **Génération de ressources CRUD** : `php artisan starter:make-crud Article` génère le modèle, la migration, la fabrique, le service, la politique et un test pour une nouvelle ressource.
- **Architecture orientée services** : la logique métier est dans des classes de service dédiées (`UserService`, `PostService`, `CategoryService`, `SettingService`, etc.), et non dans les contrôleurs ou les composants.
- **Front-end moderne** : Tailwind CSS 4 et Vite 8, déjà configurés.
- **Tests et analyse** : une suite de tests fonctionnels PHPUnit couvre l'authentification, la double authentification, les profils, les pages d'administration, la gestion des utilisateurs, articles et catégories, l'API, les notifications, le mode maintenance et la localisation ; plus Pint (style de code) et Larastan (analyse statique), tous exécutés sur la CI.

## Technologies

| Couche              | Technologie                                                        |
| ------------------- | ------------------------------------------------------------------ |
| Framework           | Laravel 13 (PHP 8.4, voir [Prérequis](#prérequis))                  |
| Interface / interactivité | Livewire 4 (composants à fichier unique)                     |
| Style / build       | Tailwind CSS 4, Vite 8                                             |
| Authentification et accès | Laravel Auth, Sanctum (jetons API), spatie/laravel-permission |
| Audit               | spatie/laravel-activitylog                                         |
| i18n                | spatie/laravel-translatable + middleware de locale personnalisé    |
| Base de données     | Tout pilote pris en charge par Laravel. `config/database.php` bascule sur SQLite ; `.env.example` est préconfiguré pour MySQL |
| Cache / file / session | `database` par défaut (Redis est pris en charge via `.env`)    |
| Tests               | PHPUnit 12                                                         |
| Qualité du code     | Laravel Pint + Larastan (PHPStan)                                  |

## Prérequis

- **PHP 8.4** pour correspondre à `composer.lock`. Les versions verrouillées de Symfony 8.1, `spatie/laravel-activitylog` 5 et `spatie/laravel-sitemap` 8.2 exigent PHP 8.4, donc `composer install` échoue sur PHP 8.3. `composer.json` accepte toujours `^8.3` ; sur PHP 8.3, exécutez `composer update` pour résoudre des versions compatibles. La CI tourne sur PHP 8.4.
- Extensions : `mbstring`, `pdo`, `intl` et `bcmath`. La suite de tests nécessite aussi `pdo_sqlite` et `sqlite3`.
- Composer
- Node.js 20 ou plus récent et npm (la CI compile avec Node 20)

## Démarrage

```bash
# 1. Installer les dépendances PHP et JS, créer .env, générer une clé,
#    exécuter les migrations et compiler les ressources — en une seule étape :
composer setup

# 2. Alimenter la base de données : le compte administrateur, les rôles et
#    permissions (toujours), plus un utilisateur de test et 10 utilisateurs d'exemple
php artisan db:seed

# 3. Démarrer l'environnement de développement complet
#    (serveur + worker de file + visionneuse de journaux + Vite)
composer dev
```

Le script `composer setup` exécute `composer install`, copie `.env.example` vers `.env` (s'il n'existe pas), génère la clé de l'application, exécute les migrations et compile les ressources front-end. Le script `composer dev` utilise [concurrently](https://www.npmjs.com/package/concurrently) pour lancer ensemble le serveur PHP, l'écouteur de file, la visionneuse `pail` et le serveur de développement Vite.

`.env.example` est préconfiguré pour MySQL sur `127.0.0.1`, et `composer setup` exécute les migrations : configurez donc d'abord la base de données. Pour un démarrage local rapide avec SQLite, modifiez `.env` avant d'exécuter l'installation :

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Créez ensuite le fichier avec `touch database/database.sqlite` et exécutez `composer setup`.

Vous préférez les conteneurs ? La pile Docker (PHP-FPM, Nginx, MySQL, phpMyAdmin, Redis) est documentée dans [README.docker.md](README.docker.md).

### Notes de production

- **Images de couverture** : stockées sur le disque `public`. Exécutez `php artisan storage:link` une fois par environnement.
- **Files d'attente** : les e-mails sont mis en file (`QUEUE_CONNECTION=database` par défaut). Lancez `php artisan queue:work` comme processus de longue durée ; `composer dev` en démarre un en local. Sans worker, les e-mails de vérification et de réinitialisation ne sont pas envoyés.
- **Planificateur** : exécutez `php artisan schedule:work` en local, ou ajoutez `* * * * * php /chemin/vers/artisan schedule:run` au cron en production. Le planificateur purge le journal d'activité, reconstruit le plan du site, lance les contrôles de santé chaque minute et la sauvegarde quotidienne.
- **Plan du site** : définissez `APP_URL` avec le domaine de production canonique (par ex. `https://www.example.com`) — toutes les URL du plan du site et de robots.txt en sont dérivées, et la génération refuse les hôtes `localhost`/`.test` en production. L'utilisateur PHP qui exécute le planificateur et le worker doit pouvoir écrire dans `public/`. Exécutez `php artisan sitemap:generate` une fois après le déploiement.
- **Nouvelles permissions** : après une mise à jour, exécutez `php artisan migrate --force` et `php artisan db:seed --class=RolesAndPermissionsSeeder --force` pour que le rôle admin reçoive `manage tags`, `view system health` et `manage backups`.
- **Sauvegardes** : le serveur a besoin de `mysqldump`/`pg_dump`/`sqlite3` selon votre pilote de base de données. Définissez `BACKUP_NOTIFICATION_EMAIL` et, pour les disques distants, `BACKUP_DISKS` et `BACKUP_ARCHIVE_PASSWORD`. Les archives excluent `.env` et le code source. Étapes de restauration : [docs/spatie-integrations.md#restoring](../spatie-integrations.md#restoring).
- **Supervision** : le planificateur exécute les contrôles chaque minute. Définissez `HEALTH_NOTIFICATION_EMAIL` pour les alertes et `HEALTH_SECRET_TOKEN` pour activer `/health.json` pour les outils externes.
- **Mode maintenance** : il se gère depuis **Admin → Paramètres** ; il n'a pas besoin de `php artisan down`.
- **Contrôles de santé** : pointez vos outils de surveillance vers `/up`. La carte de santé du tableau de bord affiche les mêmes sondes plus en détail.
- **Mots de passe** : toutes les règles utilisent `Password::defaults()`. En production, elles exigent aussi des majuscules et minuscules, des chiffres, et un mot de passe absent des listes de fuites connues. Hors production, le minimum est de 8 caractères.
- **Limites de requêtes** : les endpoints `auth/login`, `auth/register` et `auth/token` de l'API autorisent 6 requêtes par minute et par e-mail et adresse IP.

### Variables d'environnement

Ce sont les clés qui modifient le comportement du kit lui-même. Le reste correspond à la configuration Laravel standard.

| Variable | Défaut | Rôle |
| --- | --- | --- |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `en` | Langue par défaut et de repli de l'interface |
| `APP_MAINTENANCE_DRIVER` | `file` | Pilote de maintenance Laravel (`php artisan down`) ; le bouton d'administration ne l'utilise pas |
| `GITHUB_URL` | le dépôt du projet | Liens vers GitHub dans la barre de navigation, la page d'accueil et la page de docs |
| `QUEUE_CONNECTION` | `database` | Lieu d'exécution des e-mails et notifications en file |
| `CACHE_STORE` | `database` | Stockage du cache, utilisé par les sondes de santé et la limitation de débit |
| `SESSION_DRIVER` | `database` | Stockage des sessions de connexion web |
| `SITEMAP_SCHEDULE` | `0 * * * *` | Expression cron de régénération planifiée du plan du site |
| `SITEMAP_REGENERATE_ON_CHANGE` | `true` | Met en file une reconstruction du plan lorsqu'un article publié change |
| `SITEMAP_MAX_URLS_PER_FILE` | `50000` | Découpe en index de plans au-delà de ce nombre d'URL |
| `FEED_ENABLED` / `FEED_LIMIT` | `true` / `20` | Flux RSS sur `/feed` |
| `SEO_STRUCTURED_DATA` | `true` | JSON-LD sur les pages publiques (`SEO_ORGANIZATION_LOGO`, `SEO_SAME_AS` pour l'organisation) |
| `HONEYPOT_ENABLED` / `HONEYPOT_SECONDS` | `true` / `1` | Protection anti-spam à l'inscription et au mot de passe oublié |
| `BACKUP_DISKS` | `backups` | Destinations des sauvegardes (disques séparés par des virgules) |
| `BACKUP_SCHEDULE_ENABLED` / `BACKUP_RUN_AT` | `true` / `01:30` | Sauvegarde quotidienne, puis nettoyage (+1 h) et supervision (+1 h 30) |
| `BACKUP_NOTIFICATION_EMAIL` | `ADMIN_EMAIL` | Destinataire des e-mails d'échec de sauvegarde |
| `BACKUP_ARCHIVE_PASSWORD` | vide | Chiffre les archives (recommandé pour les disques distants) |
| `HEALTH_NOTIFICATION_EMAIL` | vide | Destinataire des e-mails d'échec de contrôle (aucun envoi si vide) |
| `HEALTH_SECRET_TOKEN` | vide | Jeton Bearer pour `/health.json` (le point de terminaison renvoie 404 si vide) |
| `LARAVEL_PDF_DRIVER` | `dompdf` | Moteur PDF ; `browsershot` pour les documents arabes/RTL |
| `PDF_FORMAT` / `PDF_ORIENTATION` | `a4` / `portrait` | Mise en page PDF par défaut |
| `IMPORT_MAX_KB` / `IMPORT_MAX_ROWS` | `5120` / `5000` | Limites d'envoi pour les imports CSV/XLSX |
| `MAIL_MAILER` | `log` dans `.env.example` | À définir sur un vrai transport avant d'envoyer les e-mails de vérification ou de réinitialisation |
| `CRUD_NOTIFICATIONS_ENABLED` | `true` | Active ou désactive les observateurs de notifications CRUD |
| `ADMIN_EMAIL` / `ADMIN_NAME` / `ADMIN_PASSWORD` | `admin@majidastarter.com` / `Admin User` / `password` | Lues uniquement par `AdminUserSeeder` |

### Connexion administrateur

Connectez-vous sur `/login` avec le compte administrateur créé par défaut :

| Champ    | Valeur par défaut         |
| -------- | ------------------------- |
| E-mail   | `admin@majidastarter.com` |
| Mot de passe | `password`            |

L'administrateur est créé par `database/seeders/AdminUserSeeder.php` et est toujours alimenté, y compris par `php artisan db:seed --class=MinimalDatabaseSeeder` et `ConfigDatabaseSeeder`. Pour remplacer les valeurs par défaut avant l'alimentation, définissez ces variables dans `.env` :

```dotenv
ADMIN_NAME="Admin User"
ADMIN_EMAIL=admin@majidastarter.com
ADMIN_PASSWORD=change-me-now
```

> **Changez le mot de passe administrateur avant le déploiement.** `password` est une valeur par défaut bien connue. Relancer le seeder met à jour le mot de passe de l'administrateur existant avec la valeur actuelle de `ADMIN_PASSWORD` ; vous pouvez donc aussi définir un nouveau mot de passe avec `php artisan db:seed --class=AdminUserSeeder`.

### Autres comptes d'exemple

`php artisan db:seed` crée aussi les comptes de démonstration suivants (ils ne sont pas créés par les seeders minimaux) :

| Rôle | E-mail             | Mot de passe | Remarques              |
| ---- | ------------------ | ------------ | ---------------------- |
| Utilisateur | `test@example.com` | `password` | Utilisateur standard vérifié |

Dix utilisateurs aléatoires supplémentaires sont créés avec le même mot de passe. Leurs données proviennent de `UserFactory`.

## Structure du projet

```
app/
├── Console/Commands/      # starter:make-crud, translations:sync
├── Exceptions/            # DomainActionException (renvoie une réponse JSON 422 aux requêtes JSON)
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # Contrôleurs JSON de l'API, un par service
│   │   ├── Auth/          # Déconnexion
│   │   ├── ActivityLogExportController.php  # Téléchargement du journal d'activité en CSV/JSON
│   │   ├── BlogController.php     # Liste publique /blog et détail d'article
│   │   ├── ExportController.php   # Export CSV/XLSX des articles et catégories
│   │   ├── LocaleController.php   # /language/{locale}
│   │   ├── PostPdfController.php  # PDF d'un article pour l'administration
│   │   └── RobotsController.php   # robots.txt dynamique
│   └── Middleware/
│       ├── EnforceMaintenanceMode.php  # 503 pour les non-administrateurs en mode maintenance
│       ├── EnsureUserIsAdmin.php       # Protège les routes d'administration (alias 'admin')
│       ├── RequireHealthToken.php      # Protège /health.json avec HEALTH_SECRET_TOKEN
│       ├── SetLocale.php               # Applique la langue de session à chaque requête
│       └── TrackVisit.php              # Enregistre les pages vues après l'envoi de la réponse
├── Models/                # User, Post, Category, Tag, Setting, Visit, ainsi que Concerns/
├── Notifications/         # E-mails de vérification et de réinitialisation en file, notifications CRUD
├── Observers/             # CrudNotificationObserver (configuré dans config/crud_notifications.php)
├── Policies/              # PostPolicy, CategoryPolicy
├── Providers/             # AppServiceProvider : règles de mot de passe, limiteur de débit, observateurs
├── Services/              # Toute la logique métier (voir ci-dessous)
└── Support/               # Aide pour les toasts Livewire, résolution des destinataires de notifications

config/
├── crud_notifications.php # Modèles qui notifient, sur quels événements et qui reçoit la notification
├── navigation.php         # Liens de la barre de navigation publique
└── ...                    # Configuration Laravel standard, plus activitylog, permission, livewire

database/
├── factories/             # User, Post, Category
├── migrations/
└── seeders/               # DatabaseSeeder, ConfigDatabaseSeeder, MinimalDatabaseSeeder, AdminUserSeeder, RolesAndPermissionsSeeder

resources/views/
├── pages/                 # Composants de page Livewire à fichier unique (préfixe ⚡)
│   ├── auth/              # connexion, inscription, mot de passe oublié/réinitialisation, vérification de l'e-mail, défi double authentification
│   ├── admin/             # tableau de bord, utilisateurs, articles, catégories, journal d'activité, paramètres
│   ├── settings/          # profil, double authentification
│   └── dashboard          # tableau de bord de l'utilisateur connecté
├── components/            # Composants Blade partagés, dont la mise en page vitrine
├── partials/              # Bascule de thème, sélecteur de langue, favicônes
├── welcome.blade.php      # page d'accueil vitrine
├── blog.blade.php         # liste publique du blog (recherche)
├── blog-post.blade.php    # article public du blog
└── docs.blade.php         # page de documentation publique

app/Services/
├── ActivityLogService.php # Requêtes du journal d'activité et lignes d'export
├── CategoryService.php    # CRUD des catégories, options, statistiques
├── PostService.php        # CRUD des articles, recherche, liste publique, statistiques
├── BackupService.php      # État des destinations de sauvegarde, sauvegardes en file
├── HealthCheckService.php # Derniers résultats spatie/laravel-health, valides
├── PdfService.php         # Réglages et mise en page PDF communs
├── SitemapService.php     # Construit et publie sitemap.xml de façon atomique
├── SpreadsheetService.php # Export CSV/XLSX en flux, import validé
├── StructuredDataService.php # Graphes JSON-LD schema.org des pages publiques
├── TagService.php         # Normalisation, synchronisation et gestion des étiquettes
├── SettingService.php     # Lecture et mise à jour des paramètres du site
├── SystemHealthService.php# Sondes de la carte de santé d'administration
├── TwoFactorService.php   # Configuration TOTP, vérification, codes de récupération
├── UserService.php        # Inscription, CRUD, bascule de rôle, statistiques
└── VisitService.php       # Enregistrement des visites et statistiques du tableau de bord

routes/
├── web.php                # Groupes de routes publics, invités, authentifiés, vérifiés et d'administration
├── api.php                # Points de terminaison JSON de l'API (voir ci-dessous)
└── console.php            # Tâches planifiées : activitylog:clean, sitemap:generate, contrôles de santé, sauvegardes quotidiennes

lang/
└── en/  ar/  fr/          # Fichiers de traduction PHP par langue

docs/
├── feature-audit-and-improvements.md  # Audit des fonctionnalités et liste des améliorations
└── spatie-integrations.md             # Slugs, SEO, étiquettes, RSS, honeypot, sauvegardes, santé, PDF, tableurs
```

## Vue d'ensemble des routes

### Web

- **Publiques** : `/` (accueil), `/about`, `/docs`, `/blog` (liste, recherche `?q=`, filtre `?tag=`), `/feed` (RSS), `/health.json` (protégé par jeton), `/blog/{slug}` (article publié), `/up` (contrôle de disponibilité), `/robots.txt`, `/sitemap.xml` (fichier statique écrit par `sitemap:generate`)
- **Routes invité** : `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/two-factor-challenge` (deuxième étape de la connexion)
- **Routes authentifiées** : `/verify-email`, `/verify-email/{id}/{hash}` (lien signé), `/settings/profile`, `/settings/two-factor`, `/logout`
- **Routes vérifiées** : `/dashboard`
- **Routes d'administration** (middlewares `verified` + `admin`) :
  `/admin/dashboard`,
  `/admin/users`, `/admin/users/create`, `/admin/users/{user}/edit`,
  `/admin/categories`, `/admin/categories/create`, `/admin/categories/{category}/edit`,
  `/admin/posts`, `/admin/posts/create`, `/admin/posts/{post}/edit`,
  `/admin/posts/{post}/pdf`, `/admin/posts/export/{csv|xlsx}`,
  `/admin/categories/import`, `/admin/categories/export/{csv|xlsx}`, `/admin/tags`,
  `/admin/health`, `/admin/backups`,
  `/admin/activity-log`, `/admin/activity-log/export/{csv|json}`, `/admin/settings`, `/admin/notifications`
- **Changement de langue** : `/language/{locale}` enregistre la langue choisie dans la session

### API

Tous les points de terminaison sont préfixés par `/api` et définis dans `routes/api.php`. Chacun correspond à une méthode de service et est protégé par les mêmes règles d'autorisation (`can:manage users`, `can:manage posts`, etc.) que l'interface web.

**Authentification.** Les points de terminaison authentifiés acceptent :

- un jeton Bearer : `Authorization: Bearer <token>`, délivré par `POST /api/auth/token` avec `email`, `password`, `device_name`, et `two_factor_code` si la double authentification est active. Révoquez-le avec `DELETE /api/auth/token`.
- le cookie de session, établi par `POST /api/auth/login` avec `email`, `password`, et `two_factor_code` si la double authentification est active.

L'inscription, la connexion et la délivrance de jetons sont limitées à 6 requêtes par minute et par e-mail et adresse IP.

| Méthode et chemin                            | Garde                   | Service                            |
| -------------------------------------------- | ----------------------- | ---------------------------------- |
| `POST   /api/auth/register`                  | limité                  | `UserService::register`            |
| `POST   /api/auth/login`                     | limité                  | `UserService::attempt`             |
| `POST   /api/auth/token`                     | limité                  | Délivrance de jeton Sanctum        |
| `DELETE /api/auth/token`                     | auth                    | Révoque le jeton utilisé           |
| `GET    /api/profile`                        | auth                    | utilisateur courant                |
| `PUT    /api/profile`                        | auth                    | `UserService::updateProfile`       |
| `PUT    /api/profile/password`               | auth                    | `UserService::updatePassword`      |
| `DELETE /api/profile`                        | auth                    | `UserService::deleteOwnAccount`    |
| `GET    /api/categories`                     | `can:manage categories` | `CategoryService::searchPaginated` |
| `GET    /api/categories/options`             | `can:manage categories` | `CategoryService::options`         |
| `GET    /api/categories/stats`               | `can:manage categories` | `CategoryService::stats`           |
| `POST   /api/categories`                     | `can:manage categories` | `CategoryService::create`          |
| `PUT    /api/categories/{category}`          | `can:manage categories` | `CategoryService::update`          |
| `DELETE /api/categories/{category}`          | `can:manage categories` | `CategoryService::delete`          |
| `POST   /api/categories/{category}/restore`  | `can:manage categories` | `CategoryService::restore`         |
| `DELETE /api/categories/{category}/force`    | `can:manage categories` | `CategoryService::forceDelete`     |
| `GET    /api/posts`                          | `can:manage posts`      | `PostService::searchPaginated`     |
| `GET    /api/posts/stats`                    | `can:manage posts`      | `PostService::stats`               |
| `POST   /api/posts`                          | `can:manage posts`      | `PostService::create`              |
| `PUT    /api/posts/{post}`                   | `can:manage posts`      | `PostService::update`              |
| `DELETE /api/posts/{post}`                   | `can:manage posts`      | `PostService::delete`              |
| `POST   /api/posts/{post}/restore`           | `can:manage posts`      | `PostService::restore`             |
| `DELETE /api/posts/{post}/force`             | `can:manage posts`      | `PostService::forceDelete`         |
| `GET    /api/users`                          | `can:manage users`      | `UserService::searchPaginated`     |
| `GET    /api/users/roles`                    | `can:manage users`      | `UserService::roleNames`           |
| `GET    /api/users/stats`                    | `can:manage users`      | `UserService::stats`               |
| `POST   /api/users`                          | `can:manage users`      | `UserService::create`              |
| `PUT    /api/users/{user}`                   | `can:manage users`      | `UserService::updateUser`          |
| `DELETE /api/users/{user}`                   | `can:manage users`      | `UserService::delete`              |
| `POST   /api/users/{user}/restore`           | `can:manage users`      | `UserService::restore`             |
| `DELETE /api/users/{user}/force`             | `can:manage users`      | `UserService::forceDelete`         |
| `POST   /api/users/{user}/toggle-admin`      | `can:manage users`      | `UserService::toggleAdminRole`     |
| `GET    /api/settings`                       | `can:manage settings`   | `SettingService::current`          |
| `PUT    /api/settings`                       | `can:manage settings`   | `SettingService::update`           |

Les points de terminaison de liste acceptent `trashed=only|with`, `sort` et `direction`. Les articles acceptent aussi `status`, `category_id` et `tag` (identifiant d'étiquette), et `POST`/`PUT /api/posts` acceptent un tableau facultatif `tags` de noms ; les utilisateurs acceptent `role=admin|member`. Les points `stats` et `options` alimentent les listes déroulantes et les widgets du tableau de bord.

Les erreurs sur `/api` sont toujours en JSON. Un site en mode maintenance renvoie `503` à tous sauf aux administrateurs.

## Notes sur les fonctionnalités

Ces fonctionnalités fonctionnent dès l'installation, mais sont faciles à manquer dans le code.

- **Statistiques de visites.** `TrackVisit` enregistre les requêtes `GET` de pages réussies après l'envoi de la réponse, pour que le suivi ne ralentisse jamais une page. Il ignore la zone d'administration, `/api`, les requêtes Livewire, les changements de langue, la déconnexion, `/up` et les robots courants. Les totaux sont affichés sur les tableaux de bord d'administration et utilisateur.
- **Notifications CRUD.** `CrudNotificationObserver` surveille les modèles listés dans `config/crud_notifications.php` (articles, catégories, utilisateurs et paramètres par défaut). À la création, modification, suppression ou restauration, il enregistre une `CrudActionNotification` dans la table `notifications` pour chaque utilisateur ayant le rôle `admin`, sauf l'auteur de la modification. Les mises à jour qui ne touchent que `updated_at`, `updated_by`, `remember_token` ou `deleted_at` sont ignorées, donc une connexion « se souvenir de moi » ne notifie personne. Les administrateurs voient le nombre de notifications non lues dans la cloche de la barre supérieure et gèrent la liste sur `/admin/notifications` (filtrer, marquer une ou toutes comme lues). Pour surveiller un autre modèle, ajoutez-le à ce fichier de configuration.
- **État du système.** `SystemHealthService` exécute des sondes indépendantes pour la base de données, le cache et la file d'attente, et les affiche sur la carte de santé du tableau de bord. Une sonde en échec ne bloque pas la page.
- **Mode maintenance.** Activé depuis **Admin → Paramètres**. `EnforceMaintenanceMode` sert une page 503 aux visiteurs et du JSON sur `/api`. Les administrateurs, la connexion et la double authentification, le changement de langue et `/up` restent accessibles.
- **Format de date.** Les paramètres d'administration choisissent un format unique pour les dates affichées dans le journal d'activité et la liste des utilisateurs. Les choix sont définis dans `Setting::DATE_FORMATS`.
- **Images de couverture.** Les articles peuvent avoir une image de couverture, envoyée depuis le formulaire d'article et stockée sur le disque `public`. Elle est affichée sur `/blog` et sur la page de l'article. Exécutez d'abord `php artisan storage:link`.
- **Plan du site XML.** `php artisan sitemap:generate` (`SitemapService`) liste les routes nommées de `config/sitemap.php` (`home`, `about`, `docs`, `blog`) et chaque article réellement servi par `/blog/{slug}` (publié, non supprimé). Les URL sont construites à partir de `APP_URL`, ne contiennent jamais de chaîne de requête et sont dédupliquées. `<lastmod>` correspond à `updated_at` de l'article (et au plus récent pour `/blog`) ; les pages statiques n'en ont pas, et `changefreq`/`priority` sont omis. Les fichiers sont générés, validés, écrits dans un fichier temporaire, puis renommés à leur place : une exécution échouée laisse le plan précédent intact et la commande se termine avec un code d'erreur. Au-delà de 50 000 URL, il écrit `sitemap-1.xml`, `sitemap-2.xml`, … derrière un index de plans. Il est régénéré chaque heure (`SITEMAP_SCHEDULE`, `withoutOverlapping`) et `PostSitemapObserver` met en file un `GenerateSitemapJob` dédupliqué à chaque modification d'article publié. Les routes d'authentification, de compte, d'administration, d'API et de langue ne sont jamais listées ; les catégories n'ont pas de page publique et ne sont donc pas listées non plus. `/robots.txt` est servi par `RobotsController` et ajoute la ligne `Sitemap:` une fois le fichier créé.
- **E-mail d'assistance.** Défini dans les paramètres d'administration et stocké avec les paramètres du site.

## Localisation

Les langues disponibles sont configurées dans `config/app.php` sous `available_locales` :

```php
'available_locales' => [
    'en' => ['label' => 'English', 'dir' => 'ltr'],
    'ar' => ['label' => 'العربية', 'dir' => 'rtl'],
    'fr' => ['label' => 'Français', 'dir' => 'ltr'],
],
```

Le middleware `SetLocale` applique la langue choisie par la session à chaque requête, `LocaleController` gère le changement, et le sens d'écriture (`ltr`/`rtl`) est déduit automatiquement pour un rendu RTL correct. Pour ajouter une langue, ajoutez une entrée ici et un dossier `lang/{locale}` avec les mêmes fichiers que `lang/en`.

`php artisan translations:sync` (`app/Console/Commands/SyncTranslationKeys.php`) parcourt `resources/` à la recherche de clés de traduction, crée les fichiers de langue manquants et complète les clés absentes des autres langues. Options :

- `--locales=en,fr,ar` — langues à synchroniser (par défaut : toutes)
- `--base=en` — langue de référence

Les valeurs non traduites sont préfixées par `[TODO]` pour être faciles à repérer et à compléter.

## Rôles et permissions

Les rôles et permissions sont créés par `RolesAndPermissionsSeeder`. Le rôle `admin` reçoit les permissions suivantes :

- `view admin dashboard`
- `manage users`
- `manage posts`
- `manage categories`
- `view activity log`
- `manage settings`
- `manage tags`
- `view system health`
- `manage backups`

Vérifiez l'accès administrateur dans le code avec `$user->isAdmin()` ou avec l'alias de middleware de route `admin`.

Les actions sur les articles et les catégories passent en plus par `App\Policies\PostPolicy` et `App\Policies\CategoryPolicy`. Voir et créer un article exige `manage posts`. Modifier, mettre à la corbeille, restaurer ou supprimer définitivement un article exige aussi que l'utilisateur en soit l'auteur, sauf s'il est administrateur. Les catégories sont partagées : chaque action sur une catégorie exige seulement `manage categories`.

## Créer une nouvelle ressource

```bash
php artisan starter:make-crud Article
```

Cela crée un modèle `Article`, une migration, une fabrique, `ArticleService`, `ArticlePolicy` et `ArticleModuleTest`, avec suppression logique et journalisation d'activité. Aucun fichier existant n'est écrasé. Ensuite, ajoutez la permission `manage articles` dans `RolesAndPermissionsSeeder`, puis ajoutez les routes, le contrôleur d'API et les écrans nécessaires.

## Tests et qualité du code

```bash
# Lancer la suite de tests
composer test          # ou : php artisan test

# Vérifier le style du code (Laravel Pint)
vendor/bin/pint --test # ajoutez --dirty pour ne vérifier que les fichiers modifiés, retirez --test pour corriger

# Analyse statique (Larastan / PHPStan)
composer analyse
```

La suite se trouve dans `tests/Feature` et couvre l'authentification et la double authentification, la vérification de l'e-mail, la gestion du profil, la gestion des utilisateurs, articles et catégories, les pages d'administration, l'API REST, les notifications CRUD, le mode maintenance, la recherche, les suppressions logiques et la localisation. Les tests utilisent une base SQLite en mémoire, le cache et l'envoi de mails en mode tableau, et une file synchrone (voir `phpunit.xml`).

## Intégration continue

Un workflow GitHub Actions (`.github/workflows/ci.yml`) s'exécute à chaque push sur `develop` et `main` et à chaque pull request. Il utilise PHP 8.4 et :

- exécute la suite de tests (en compilant d'abord les ressources front-end avec Node 20), et
- vérifie le style du code avec Pint et exécute l'analyse statique Larastan.

## Licence

MajidaStarter est un logiciel open source distribué sous la [licence MIT](https://opensource.org/licenses/MIT). Il repose sur le [framework Laravel](https://laravel.com), également sous licence MIT.
