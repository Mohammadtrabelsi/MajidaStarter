<div dir="rtl">

# بيئة التطوير باستخدام Docker

بيئة تطوير مُحوّاة (containerized) لتطبيق MajidaStarter المبني على Laravel. يوجد تطبيق Laravel داخل `src/`، ويوفّر Docker كل ما يحيط به: PHP-FPM وNginx وMySQL وphpMyAdmin وRedis. لا تحتاج إلى تثبيت أي شيء على جهازك سوى Docker وDocker Compose.

> 🌐 لغات أخرى: [English](../en/README.docker.md) · [Français](../fr/README.docker.md)

> **مهم:** نفّذ جميع الأوامر في هذا الدليل من **جذر المستودع**، حتى وإن كان هذا الملف موجودًا في `docs/ar/`.

> **تنبيه:** يركّب مكدس Docker المجلد `./src` كجذر للتطبيق. وفي هذا المستودع يوجد تطبيق Laravel في المستوى الأعلى، لذلك تنسخه خطوة الإعداد الأولى إلى `src/`. Node.js غير موجود في صورة PHP، لذلك تُبنى ملفات الواجهة الأمامية في حاوية Node منفصلة.

---

## هيكل المجلدات

```
.
├── docker-compose.yml        # يعرّف كل الخدمات (app وweb وdb وphpmyadmin وredis)
├── .env.docker.example       # مثال لمتغيرات Docker -> انسخه إلى .env
├── .env                      # إعداداتك المحلية لـ Docker (مُستثنى من git، وتنشئه بنفسك)
├── docs/
│   ├── en/README.docker.md   # الدليل بالإنجليزية
│   ├── fr/README.docker.md   # الدليل بالفرنسية
│   └── ar/README.docker.md   # هذا الملف
├── docker/
│   ├── php/
│   │   ├── Dockerfile        # صورة PHP-FPM مع إضافات Laravel وComposer (انظر ملاحظة إصدار PHP أدناه)
│   │   └── php.ini           # إعدادات PHP مخصصة للتطوير
│   └── nginx/
│       └── default.conf      # إعداد مضيف Nginx الذي يشير إلى src/public
└── src/                      # <-- تطبيق Laravel (يُربط بالحاويات)
    ├── public/               # جذر الويب (index.php)
    ├── .env                  # ملف البيئة الخاص بـ Laravel (DB_HOST=db موجود هنا)
    └── ...
```

> ملفان من `.env` ولكل منهما وظيفة مختلفة:
> - **`./.env`** يضبط **مكدس Docker** (المنافذ، وبيانات اعتماد MySQL، وUID/GID).
> - **`./src/.env`** يضبط **Laravel نفسه** (اتصال قاعدة البيانات، ومفتاح التطبيق، وغيرها).

---

## الإعداد لأول مرة

### 1. انسخ ملف بيئة Docker

```bash
cp .env.docker.example .env
```

عدّل `.env` إذا كانت أي من المنافذ الافتراضية (`8080` و`8081` و`3306` و`6379`) مستخدمة بالفعل على جهازك.

### 2. انسخ تطبيق Laravel إلى `src/`

نفّذ هذا من جذر المستودع. ينسخ ملفات التطبيق ويترك ملفات Docker في مكانها:

```bash
cp -R app bootstrap config database lang public resources routes storage tests \
      artisan composer.json composer.lock package.json package-lock.json \
      vite.config.js phpunit.xml phpstan.neon.dist .env.example src/
```

بعد ذلك يوجد الملفان `src/public/index.php` و`src/artisan`. لا تشغّل `composer create-project` هنا، فسيُنشئ تطبيق Laravel جديدًا لا علاقة له بالمشروع.

### 3. وجّه Laravel إلى حاوية `db`

أنشئ ملف البيئة الخاص بـ Laravel:

```bash
cp src/.env.example src/.env
```

ثم عدّل `src/.env` ليحمل قيم قاعدة البيانات وRedis التالية:

```dotenv
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# اختياري: استخدم Redis للذاكرة المؤقتة والجلسات بدلًا من قاعدة البيانات
# REDIS_HOST=redis
# CACHE_STORE=redis
# SESSION_DRIVER=redis
```

يجب أن تطابق قيم `DB_*` تلك الموجودة في `.env` في الجذر (فهي تُهيّئ حاوية MySQL). و`DB_HOST=db` هو ما يسمح لـ Laravel بالوصول إلى MySQL عبر شبكة Docker. خدمة Redis متاحة باسم `redis`، لكن الإعداد الافتراضي لا يستخدمها.

### 4. ابنِ المكدس وشغّله

```bash
docker compose up -d --build
```

### 5. ثبّت اعتماديات PHP

```bash
docker compose exec app composer install
```

> **ملاحظة حول إصدار PHP:** يتطلب `composer.lock` الإصدار PHP 8.4، لكن `docker/php/Dockerfile` يُبنى من `php:8.3-fpm-alpine`. وإلى أن تُستبدل الصورة الأساسية بـ`php:8.4-fpm-alpine`، ستفشل هذه الخطوة بخطأ في المنصّة. غيّر سطر `FROM` ثم نفّذ `docker compose build app` قبل التثبيت.

### 6. ابنِ ملفات الواجهة الأمامية

لا تحتوي حاوية `app` على Node.js، لذلك ابنِ الملفات في حاوية Node مؤقتة. يتوافق Node 20 مع CI:

```bash
docker run --rm -v "$(pwd)/src":/app -w /app node:20-alpine sh -c "npm ci && npm run build"
```

### 7. نفّذ الترحيلات وأنشئ رابط التخزين

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link
```

الأمر `storage:link` ضروري لصور غلاف المقالات. أضف `--seed` إلى `migrate` إذا أردت الحسابات التجريبية (انظر القائمة في [README](README.md)).

### 8. شغّل عمليات الخلفية

تُوضع رسائل البريد في الطابور افتراضيًا (`QUEUE_CONNECTION=database`)، ويحذف المُجدول سجل النشاط القديم ويعيد بناء `public/sitemap.xml`. ولا يعمل أيٌّ منهما تلقائيًا داخل المكدس، لذلك شغّلهما في الخلفية:

```bash
docker compose exec -d app php artisan queue:work
docker compose exec -d app php artisan schedule:work
```

انتهيت. افتح التطبيق في متصفحك.

---

## الروابط

| الخدمة       | الرابط                   | ملاحظات                                     |
|--------------|--------------------------|---------------------------------------------|
| التطبيق      | http://localhost:8080    | يقدّمه Nginx (`APP_PORT`)                   |
| فحص الصحة    | http://localhost:8080/up | نقطة فحص الحياة التي تستخدمها بطاقة الصحة في الإدارة |
| phpMyAdmin   | http://localhost:8081    | سجّل الدخول بمستخدم MySQL (`PMA_PORT`)      |

بيانات الدخول إلى phpMyAdmin: الخادم `db`، والمستخدم `laravel`، وكلمة المرور `secret` (أو مستخدم الجذر `root` / `root`)، وفق القيم الموجودة في `.env`.

---

## أوامر Docker Compose الشائعة

```bash
# تشغيل كل شيء في الخلفية (إعادة بناء الصور عند الحاجة)
docker compose up -d --build

# إيقاف المكدس (يحتفظ بمجلد قاعدة البيانات)
docker compose down

# الإيقاف مع حذف مجلد قاعدة البيانات (قاعدة جديدة في المرة القادمة)
docker compose down -v

# عرض السجلات (كل الخدمات، أو خدمة واحدة)
docker compose logs -f
docker compose logs -f app

# عرض الخدمات قيد التشغيل
docker compose ps

# فتح shell داخل حاوية PHP
docker compose exec app bash

# تشغيل أوامر artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker
docker compose exec app php artisan test

# Composer داخل الحاوية
docker compose exec app composer install
docker compose exec app composer require vendor/package

# بناء الواجهة الأمامية (من جذر المستودع، كما في الخطوة 6)
docker run --rm -v "$(pwd)/src":/app -w /app node:20-alpine sh -c "npm ci && npm run build"

# إعادة بناء صورة PHP فقط بعد تغيير Dockerfile
docker compose build app

# إعادة تشغيل خدمة واحدة
docker compose restart web
```

---

## نظرة سريعة على الخدمات

| الخدمة       | الصورة               | الغرض                                    |
|--------------|----------------------|------------------------------------------|
| `app`        | PHP-FPM مخصص (8.3 حاليًا، انظر الملاحظة) | يشغّل Laravel (PHP + Composer) |
| `web`        | nginx:1.27-alpine    | خادم الويب، يُحيل طلبات PHP إلى `app`    |
| `db`         | mysql:8.0            | قاعدة البيانات (يتصل Laravel بها عبر `db`) |
| `phpmyadmin` | phpmyadmin:5         | واجهة ويب لقاعدة البيانات                |
| `redis`      | redis:7-alpine       | مخزن اختياري للذاكرة المؤقتة والجلسات (غير مستخدم افتراضيًا) |

تتضمن صورة `app` امتداد PHP الخاص بـ `redis`، لذلك يعمل Redis بمجرد ضبط `REDIS_HOST` والمشغّلات في `src/.env`.

</div>
