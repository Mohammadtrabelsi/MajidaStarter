<div dir="rtl">

# MajidaStarter

MajidaStarter هو قالب انطلاق جاهز للاستخدام مبني على **Laravel 13 + Livewire 4**. يتضمن كل ما يُعاد بناؤه عادةً في بداية كل مشروع: المصادقة مع دعم التحقق بخطوتين، ولوحة إدارة، والتحكم في الصلاحيات حسب الأدوار، وتسجيل النشاط، ودعم متعدد اللغات (من اليسار إلى اليمين ومن اليمين إلى اليسار)، ووضع فاتح/داكن، وقسم للمحتوى (المقالات والتصنيفات)، وواجهة REST API مطابقة، وطبقة خدمات منظمة، حتى تتجاوز الأعمال المتكررة وتبدأ مباشرةً ببناء الميزات.

> 🌐 لغات أخرى: [English](../en/README.md) · [Français](../fr/README.md)

## أبرز الميزات

- **تدفق مصادقة متكامل**: التسجيل وتسجيل الدخول والخروج وإعادة تعيين كلمة المرور والتحقق من البريد الإلكتروني، وكلها مبنية كمكونات Livewire بملف واحد. يخضع تسجيل الدخول لحد للمحاولات حسب البريد الإلكتروني وعنوان IP.
- **التحقق بخطوتين**: تطبيقات TOTP (وفق RFC 6238) مع رموز استرداد تُستخدم مرة واحدة، وتُدار من **الإعدادات ← التحقق بخطوتين**، وتُفرض عند تسجيل الدخول عبر الويب وعبر API بعد تفعيلها. لا تحتاج إلى أي حزمة إضافية.
- **لوحة الإدارة**: مكتب خلفي مخصص ومحمي بالصلاحيات لإدارة المستخدمين والمقالات والتصنيفات ومراجعة النشاط وتعديل إعدادات الموقع.
- **قسم المحتوى**: عمليات إنشاء وقراءة وتعديل وحذف كاملة للـ**مقالات** و**التصنيفات** في لوحة الإدارة. تظهر المقالات المنشورة في صفحة `/blog` العامة مع البحث وصفحات التفاصيل وصور الغلاف، و`/docs` هو الدليل المدمج. تُنقل عمليات الحذف إلى **سلة المهملات** ويمكن استعادتها، والنشر أو الحذف الجماعي والتصفية والفرز مدمجة في قوائم الإدارة.
- **التحكم في الوصول بالأدوار والصلاحيات**: مبني على [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)، مع دور `admin` مُنشأ جاهزًا.
- **تسجيل النشاط**: كل تغيير مهم على المستخدمين والمقالات والتصنيفات والإعدادات يُسجَّل باستخدام [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog) ويمكن استعراضه من لوحة الإدارة.
- **تصدير سجل النشاط والاحتفاظ به**: تنزيل السجل المُصفّى بصيغة CSV أو JSON؛ ويعمل الأمر `activitylog:clean` يوميًا عبر المُجدول.
- **إشعارات عمليات CRUD**: يتلقى المسؤولون إشعارًا محفوظًا عند إنشاء المقالات أو التصنيفات أو المستخدمين أو الإعدادات أو تعديلها أو حذفها أو استعادتها، ويمكنهم مراجعتها في صفحة الإشعارات المرتبطة بالجرس. لا يُرسَل الإشعار أبدًا إلى المستخدم الذي أجرى التغيير. انظر [ملاحظات حول الميزات](#ملاحظات-حول-الميزات).
- **خريطة الموقع بصيغة XML**: يكتب الأمر `php artisan sitemap:generate` الملف `/sitemap.xml` (عبر [spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap)) من الصفحات العامة والمقالات المنشورة؛ يُعاد بناؤه كل ساعة عبر المُجدول وبعد تغييرات المقالات، ويُعلَن عنه في ملف `/robots.txt` ديناميكي. انظر [ملاحظات حول الميزات](#ملاحظات-حول-الميزات).
- **الوسوم**: وسوم مقالات قابلة للترجمة عبر [spatie/laravel-tags](https://spatie.be/docs/laravel-tags): منتقي وسوم قابل لإعادة الاستخدام في نموذج المقال، وشاشة وسوم في الإدارة، وتصفية في قائمة الإدارة وعلى `/blog?tag=` وفي API.
- **بيانات منظمة لتحسين محركات البحث و RSS**: بيانات JSON-LD من schema.org (Organization وWebSite وBreadcrumbList وBlogPosting) عبر [spatie/schema-org](https://github.com/spatie/schema-org)، ووسوم canonical وOpen Graph، وخلاصة RSS على `/feed` عبر [spatie/laravel-feed](https://github.com/spatie/laravel-feed). تُولَّد الروابط الثابتة (slugs) وتُحفَظ مستقرة بواسطة [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable).
- **الحماية من الرسائل المزعجة**: نموذجا التسجيل ونسيان كلمة المرور يحتويان على حقل مصيدة للروبوتات (honeypot) وفحص للزمن ([spatie/laravel-honeypot](https://github.com/spatie/laravel-honeypot)).
- **النسخ الاحتياطي والمراقبة الصحية**: نسخ احتياطية مجدولة ومراقبة لقاعدة البيانات وملفات الرفع ([spatie/laravel-backup](https://spatie.be/docs/laravel-backup))، وفحوصات صحة التطبيق ([spatie/laravel-health](https://spatie.be/docs/laravel-health))، ولكل منهما صفحة في الإدارة؛ ونقطة `/health.json` محمية برمز للمراقبين الخارجيين.
- **ملفات PDF وجداول البيانات**: ملفات PDF للمقالات من قالب Blade مشترك ([spatie/laravel-pdf](https://spatie.be/docs/laravel-pdf)، ومحرك DomPDF)، وتصدير CSV/XLSX بالتدفق، واستيراد التصنيفات مع التحقق من الصحة ([spatie/simple-excel](https://github.com/spatie/simple-excel)). للتفاصيل راجع **[docs/spatie-integrations.md](../spatie-integrations.md)**.
- **إحصاءات الزيارات**: تُحتسب مشاهدات الصفحات العامة (مع استثناء الروبوتات ولوحة الإدارة وطلبات API والملفات) وتُعرض في لوحات التحكم.
- **صحة النظام**: تفحص لوحة الإدارة قاعدة البيانات والذاكرة المؤقتة وقائمة الانتظار، ويعرض المسار `/up` نقطة فحص للمراقبين الخارجيين.
- **واجهة REST API**: كل خدمة مكشوفة كنقاط نهاية JSON تحت `/api`، ومحمية بنفس بوابات التفويض المستخدمة في واجهة الويب. تُقبل جلسات الويب ورموز الوصول الشخصية من Sanctum على حد سواء (انظر [API](#api)).
- **الترجمة (i18n) مع دعم RTL**: الإنجليزية والعربية والفرنسية متوفرة افتراضيًا، مع مبدِّل للغة، وحفظ اللغة لكل جلسة، ومعالجة تلقائية لاتجاه النص.
- **إعدادات قابلة للترجمة**: يُخزَّن اسم الموقع ووصفه لكل لغة عبر [spatie/laravel-translatable](https://spatie.be/docs/laravel-translatable).
- **مفتاح الوضع الفاتح / الداكن**: مبدِّل دائم بدون وميض عند التحميل.
- **وضع الصيانة**: يُفعَّل من إعدادات الإدارة؛ يرى الزوار صفحة 503 (أو JSON على `/api`)، بينما يبقى المسؤولون وتسجيل الدخول وتبديل اللغة و`/up` متاحين.
- **الحذف المؤقت والسلة**: يُحذف المستخدمون والمقالات والتصنيفات حذفًا مؤقتًا، ويمكن استعادتها أو حذفها نهائيًا.
- **السياسات (Policies)**: يُفرض التحكم في الوصول إلى المقالات والتصنيفات عبر `PostPolicy` و`CategoryPolicy` (لا يستطيع محررو المقالات تعديل مقالات غيرهم إلا إذا كانوا مسؤولين).
- **البريد عبر قائمة الانتظار**: تُرسل رسائل التحقق وإعادة تعيين كلمة المرور عبر قائمة الانتظار.
- **توليد هياكل CRUD**: يُنشئ الأمر `php artisan starter:make-crud Article` النموذج والترحيل والمصنع والخدمة والسياسة واختبارًا لمورد جديد.
- **بنية موجّهة بالخدمات**: منطق العمل موجود في فئات خدمات مخصصة مثل `UserService` و`PostService` و`CategoryService` و`SettingService` وغيرها، بدلًا من وضعه في المتحكمات أو المكونات.
- **واجهة أمامية حديثة**: Tailwind CSS 4 وVite 8، مُعدّة وجاهزة للاستخدام.
- **اختبارات وتحليل**: مجموعة اختبارات وظيفية PHPUnit تغطي المصادقة والتحقق بخطوتين والملفات الشخصية وصفحات الإدارة وإدارة المستخدمين والمقالات والتصنيفات وAPI والإشعارات ووضع الصيانة والترجمة، بالإضافة إلى Pint (نمط الكود) وLarastan (التحليل الساكن)، وكلها تعمل على CI.

## التقنيات المستخدمة

| الطبقة              | التقنية                                                            |
| ------------------- | ------------------------------------------------------------------ |
| إطار العمل          | Laravel 13 (PHP 8.4، انظر [المتطلبات](#المتطلبات))                  |
| الواجهة والتفاعل    | Livewire 4 (مكونات بملف واحد)                                      |
| التنسيق والبناء     | Tailwind CSS 4، Vite 8                                             |
| المصادقة والصلاحيات | Laravel Auth، وSanctum (رموز API)، وspatie/laravel-permission      |
| التدقيق             | spatie/laravel-activitylog                                         |
| الترجمة             | spatie/laravel-translatable + وسيط لغة مخصص                        |
| قاعدة البيانات      | أي مشغّل تدعمه Laravel. يعود `config/database.php` إلى SQLite؛ و`.env.example` مُعدّ مسبقًا لـ MySQL |
| الذاكرة المؤقتة / الطابور / الجلسة | `database` افتراضيًا (ويُدعم Redis عبر `.env`)  |
| الاختبارات          | PHPUnit 12                                                         |
| جودة الكود          | Laravel Pint + Larastan (PHPStan)                                  |

## المتطلبات

- **PHP 8.4** ليتطابق مع `composer.lock`. تتطلب الإصدارات المقفلة من Symfony 8.1 و`spatie/laravel-activitylog` 5 و`spatie/laravel-sitemap` 8.2 الإصدار PHP 8.4، لذلك يفشل `composer install` على PHP 8.3. لا يزال `composer.json` يقبل `^8.3`؛ وعلى PHP 8.3 شغّل `composer update` لتحديد إصدارات متوافقة. يعمل CI على PHP 8.4.
- الإضافات: `mbstring` و`pdo` و`intl` و`bcmath`. تحتاج مجموعة الاختبارات أيضًا إلى `pdo_sqlite` و`sqlite3`.
- Composer
- Node.js 20 أو أحدث وnpm (يبني CI باستخدام Node 20)

## البدء

```bash
# 1. ثبّت اعتماديات PHP وJS، وأنشئ .env، وولّد المفتاح،
#    ونفّذ الترحيلات، وابنِ الملفات — كل ذلك في خطوة واحدة:
composer setup

# 2. املأ قاعدة البيانات: حساب المسؤول والأدوار والصلاحيات (دائمًا)،
#    بالإضافة إلى مستخدم تجريبي و10 مستخدمين نموذجيين
php artisan db:seed

# 3. شغّل بيئة التطوير الكاملة
#    (الخادم + عامل الطابور + عارض السجلات + Vite)
composer dev
```

ينفّذ السكربت `composer setup` الأمر `composer install`، وينسخ `.env.example` إلى `.env` (إذا لم يكن موجودًا)، ويولّد مفتاح التطبيق، وينفّذ الترحيلات، ويبني ملفات الواجهة الأمامية. ويستخدم السكربت `composer dev` الأداة [concurrently](https://www.npmjs.com/package/concurrently) لتشغيل خادم PHP ومستمع الطابور وعارض السجلات `pail` وخادم Vite للتطوير معًا.

إعداد `.env.example` مُعدّ مسبقًا لـ MySQL على `127.0.0.1`، وينفّذ `composer setup` الترحيلات، لذلك اضبط قاعدة البيانات أولًا. للتشغيل المحلي السريع باستخدام SQLite، عدّل `.env` قبل تشغيل الإعداد:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

ثم أنشئ الملف باستخدام `touch database/database.sqlite` وشغّل `composer setup`.

هل تفضّل الحاويات؟ مكدس Docker (PHP-FPM وNginx وMySQL وphpMyAdmin وRedis) موثّق في [README.docker.md](README.docker.md).

### ملاحظات الإنتاج

- **صور الغلاف** تُخزَّن على القرص `public`. شغّل `php artisan storage:link` مرة واحدة لكل بيئة.
- **الطوابير**: تُوضع رسائل البريد في الطابور افتراضيًا (`QUEUE_CONNECTION=database`). شغّل `php artisan queue:work` كعملية تعمل باستمرار؛ يبدأ `composer dev` واحدة محليًا. بدون عامل طابور لا تُرسل رسائل التحقق وإعادة التعيين.
- **المُجدول**: شغّل `php artisan schedule:work` محليًا، أو أضف `* * * * * php /path/to/artisan schedule:run` إلى cron في الإنتاج. يحذف المُجدول سجل النشاط القديم، ويعيد بناء خريطة الموقع، ويشغّل فحوصات الصحة كل دقيقة والنسخة الاحتياطية اليومية.
- **خريطة الموقع**: اضبط `APP_URL` على النطاق الأساسي للإنتاج (مثل `https://www.example.com`)، فكل عناوين خريطة الموقع وrobots.txt مبنية عليه، ويرفض التوليد عناوين `localhost` و`.test` في الإنتاج. يجب أن يكون مستخدم PHP الذي يشغّل المُجدول وعامل الطابور قادرًا على الكتابة في `public/`. شغّل `php artisan sitemap:generate` مرة واحدة بعد النشر.
- **صلاحيات جديدة**: بعد الترقية، شغّل `php artisan migrate --force` و`php artisan db:seed --class=RolesAndPermissionsSeeder --force` ليحصل دور المسؤول على `manage tags` و`view system health` و`manage backups`.
- **النسخ الاحتياطية**: يحتاج الخادم إلى `mysqldump` أو `pg_dump` أو `sqlite3` حسب مشغّل قاعدة البيانات. اضبط `BACKUP_NOTIFICATION_EMAIL` وللأقراص البعيدة `BACKUP_DISKS` و`BACKUP_ARCHIVE_PASSWORD`. تستثني الأرشيفات ملف `.env` والكود المصدري. خطوات الاستعادة: [docs/spatie-integrations.md#restoring](../spatie-integrations.md#restoring).
- **مراقبة الصحة**: يشغّل المُجدول الفحوصات كل دقيقة. اضبط `HEALTH_NOTIFICATION_EMAIL` لتلقي التنبيهات و`HEALTH_SECRET_TOKEN` لتفعيل `/health.json` للمراقبين الخارجيين.
- **وضع الصيانة** يُدار من **الإدارة ← الإعدادات**، ولا يحتاج إلى `php artisan down`.
- **فحوصات الصحة**: وجّه أدوات المراقبة إلى `/up`. تعرض بطاقة صحة النظام في لوحة الإدارة الفحوصات نفسها بتفصيل أكبر.
- **كلمات المرور**: تستخدم كل قواعد كلمة المرور `Password::defaults()`. وفي الإنتاج تشترط أيضًا أحرفًا كبيرة وصغيرة وأرقامًا، وألّا تكون كلمة المرور في قوائم التسريبات المعروفة. خارج الإنتاج الحد الأدنى 8 أحرف.
- **حدود الطلبات**: تسمح نقاط `auth/login` و`auth/register` و`auth/token` في API بـ6 طلبات في الدقيقة لكل بريد إلكتروني وعنوان IP.

### متغيرات البيئة

هذه المفاتيح تغيّر سلوك قالب الانطلاق نفسه. أما البقية فهي إعدادات Laravel القياسية.

| المتغير | القيمة الافتراضية | الغرض |
| --- | --- | --- |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `en` | لغة الواجهة الافتراضية والاحتياطية |
| `APP_MAINTENANCE_DRIVER` | `file` | مشغّل الصيانة في Laravel (`php artisan down`)؛ مفتاح الإدارة لا يستخدمه |
| `GITHUB_URL` | مستودع المشروع | روابط GitHub في الشريط العلوي وصفحة الترحيب وصفحة الوثائق |
| `QUEUE_CONNECTION` | `database` | مكان تشغيل البريد والإشعارات في الطابور |
| `CACHE_STORE` | `database` | مخزن الذاكرة المؤقتة، ويستخدمه فحص الصحة وتحديد المعدل |
| `SESSION_DRIVER` | `database` | مخزن الجلسات لتسجيل الدخول عبر الويب |
| `SITEMAP_SCHEDULE` | `0 * * * *` | تعبير cron لإعادة توليد خريطة الموقع المجدولة |
| `SITEMAP_REGENERATE_ON_CHANGE` | `true` | إضافة إعادة بناء خريطة الموقع إلى الطابور عند تغيّر مقال منشور |
| `SITEMAP_MAX_URLS_PER_FILE` | `50000` | تقسيم الخريطة إلى فهرس خرائط فوق هذا العدد من العناوين |
| `FEED_ENABLED` / `FEED_LIMIT` | `true` / `20` | خلاصة RSS على `/feed` |
| `SEO_STRUCTURED_DATA` | `true` | JSON-LD في الصفحات العامة (`SEO_ORGANIZATION_LOGO` و`SEO_SAME_AS` للمؤسسة) |
| `HONEYPOT_ENABLED` / `HONEYPOT_SECONDS` | `true` / `1` | الحماية من الرسائل المزعجة في التسجيل ونسيان كلمة المرور |
| `BACKUP_DISKS` | `backups` | وجهات النسخ الاحتياطي (أقراص مفصولة بفواصل) |
| `BACKUP_SCHEDULE_ENABLED` / `BACKUP_RUN_AT` | `true` / `01:30` | نسخة يومية، ثم التنظيف (+1 ساعة) والمراقبة (+1 ساعة و30 دقيقة) |
| `BACKUP_NOTIFICATION_EMAIL` | `ADMIN_EMAIL` | يستقبل رسائل فشل النسخ الاحتياطي |
| `BACKUP_ARCHIVE_PASSWORD` | فارغ | يُشفّر الأرشيفات (يُنصح به للأقراص البعيدة) |
| `HEALTH_NOTIFICATION_EMAIL` | فارغ | يستقبل رسائل فشل فحوصات الصحة (لا تُرسل شيئًا إذا كان فارغًا) |
| `HEALTH_SECRET_TOKEN` | فارغ | رمز Bearer لـ`/health.json` (تعيد النقطة 404 إذا كان فارغًا) |
| `LARAVEL_PDF_DRIVER` | `dompdf` | محرك PDF؛ استخدم `browsershot` للمستندات العربية/RTL |
| `PDF_FORMAT` / `PDF_ORIENTATION` | `a4` / `portrait` | إعداد صفحة PDF الافتراضي |
| `IMPORT_MAX_KB` / `IMPORT_MAX_ROWS` | `5120` / `5000` | حدود الرفع لاستيراد CSV/XLSX |
| `MAIL_MAILER` | `log` في `.env.example` | اضبطه على خدمة بريد حقيقية قبل إرسال رسائل التحقق أو إعادة التعيين |
| `CRUD_NOTIFICATIONS_ENABLED` | `true` | يشغّل أو يوقف مراقبي إشعارات CRUD |
| `ADMIN_EMAIL` / `ADMIN_NAME` / `ADMIN_PASSWORD` | `admin@majidastarter.com` / `Admin User` / `password` | يقرأها `AdminUserSeeder` فقط |

### تسجيل دخول المسؤول

سجّل الدخول عبر `/login` باستخدام حساب المسؤول المُنشأ مسبقًا:

| الحقل    | القيمة الافتراضية         |
| -------- | ------------------------- |
| البريد   | `admin@majidastarter.com` |
| كلمة المرور | `password`             |

يُنشأ المسؤول بواسطة `database/seeders/AdminUserSeeder.php` ويُملأ دائمًا، بما في ذلك عند تشغيل `php artisan db:seed --class=MinimalDatabaseSeeder` و`ConfigDatabaseSeeder`. لتغيير القيم الافتراضية قبل الملء، اضبط هذه المتغيرات في `.env`:

```dotenv
ADMIN_NAME="Admin User"
ADMIN_EMAIL=admin@majidastarter.com
ADMIN_PASSWORD=change-me-now
```

> **غيّر كلمة مرور المسؤول قبل النشر.** كلمة `password` معروفة جدًا. إعادة تشغيل الـ seeder تحدّث كلمة مرور المسؤول الحالي إلى القيمة الحالية لـ`ADMIN_PASSWORD`، لذلك يمكنك أيضًا تعيين كلمة جديدة بالأمر `php artisan db:seed --class=AdminUserSeeder`.

### حسابات تجريبية أخرى

ينشئ `php artisan db:seed` أيضًا حسابات العرض التالية (لا تُنشئها الـ seeders الأساسية):

| الدور | البريد الإلكتروني  | كلمة المرور | ملاحظات                |
| ----- | ------------------ | ----------- | ---------------------- |
| مستخدم | `test@example.com` | `password` | مستخدم عادي موثّق       |

يُنشأ عشرة مستخدمين عشوائيين إضافيين بالكلمة نفسها، وتأتي بياناتهم من `UserFactory`.

## هيكل المشروع

```
app/
├── Console/Commands/      # starter:make-crud و translations:sync
├── Exceptions/            # DomainActionException (يعيد استجابة JSON برمز 422 لطلبات JSON)
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # متحكمات JSON لواجهة API، متحكم لكل خدمة
│   │   ├── Auth/          # تسجيل الخروج
│   │   ├── ActivityLogExportController.php  # تنزيل سجل النشاط بصيغة CSV/JSON
│   │   ├── BlogController.php     # قائمة /blog العامة وصفحة تفاصيل المقال
│   │   ├── ExportController.php   # تصدير CSV/XLSX للمقالات والتصنيفات
│   │   ├── LocaleController.php   # /language/{locale}
│   │   ├── PostPdfController.php  # ملف PDF لمقال في الإدارة
│   │   └── RobotsController.php   # robots.txt ديناميكي
│   └── Middleware/
│       ├── EnforceMaintenanceMode.php  # يعيد 503 لغير المسؤولين أثناء الصيانة
│       ├── EnsureUserIsAdmin.php       # يحمي مسارات الإدارة (الاسم المستعار 'admin')
│       ├── RequireHealthToken.php      # يحمي /health.json برمز HEALTH_SECRET_TOKEN
│       ├── SetLocale.php               # يطبّق لغة الجلسة في كل طلب
│       └── TrackVisit.php              # يسجّل مشاهدات الصفحات بعد إرسال الاستجابة
├── Models/                # User وPost وCategory وTag وSetting وVisit، بالإضافة إلى Concerns/
├── Notifications/         # رسائل التحقق من البريد وإعادة التعيين عبر الطابور، وإشعارات CRUD
├── Observers/             # CrudNotificationObserver (مُعدّ في config/crud_notifications.php)
├── Policies/              # PostPolicy وCategoryPolicy
├── Providers/             # AppServiceProvider: قواعد كلمات المرور، ومحدد المعدل، والمراقبون
├── Services/              # كل منطق العمل (انظر أدناه)
└── Support/               # مساعد رسائل Livewire المنبثقة، وتحديد مستلمي الإشعارات

config/
├── crud_notifications.php # أي النماذج تُرسل إشعارات، وعلى أي أحداث، ومن يستلمها
├── navigation.php         # روابط الشريط العلوي العام
└── ...                    # إعدادات Laravel القياسية، بالإضافة إلى activitylog وpermission وlivewire

database/
├── factories/             # User وPost وCategory
├── migrations/
└── seeders/               # DatabaseSeeder وConfigDatabaseSeeder وMinimalDatabaseSeeder وAdminUserSeeder وRolesAndPermissionsSeeder

resources/views/
├── pages/                 # مكونات صفحات Livewire بملف واحد (بادئة ⚡)
│   ├── auth/              # تسجيل الدخول، والتسجيل، ونسيان/إعادة تعيين كلمة المرور، والتحقق من البريد، وتحدي التحقق بخطوتين
│   ├── admin/             # لوحة التحكم، والمستخدمون، والمقالات، والتصنيفات، وسجل النشاط، والإعدادات
│   ├── settings/          # الملف الشخصي، والتحقق بخطوتين
│   └── dashboard          # لوحة تحكم المستخدم المسجّل
├── components/            # مكونات Blade مشتركة، بما فيها قالب الصفحة التسويقية
├── partials/              # مبدِّل الوضع، ومبدِّل اللغة، والأيقونات
├── welcome.blade.php      # الصفحة الرئيسية التسويقية
├── blog.blade.php         # قائمة المدونة العامة (مع البحث)
├── blog-post.blade.php    # مقال المدونة العام
└── docs.blade.php         # صفحة الوثائق العامة

app/Services/
├── ActivityLogService.php # استعلامات سجل النشاط وصفوف التصدير
├── CategoryService.php    # إنشاء وقراءة وتعديل وحذف التصنيفات، والخيارات، والإحصاءات
├── PostService.php        # إنشاء وقراءة وتعديل وحذف المقالات، والبحث، والقائمة العامة، والإحصاءات
├── BackupService.php      # حالة وجهات النسخ الاحتياطي، وعمليات النسخ في الطابور
├── HealthCheckService.php # أحدث نتائج spatie/laravel-health والنتائج الحديثة
├── PdfService.php         # الإعدادات والتخطيط المشتركان لملفات PDF
├── SitemapService.php     # يبني sitemap.xml وينشره بشكل ذرّي
├── SpreadsheetService.php # تصدير CSV/XLSX بالتدفق، واستيراد مع التحقق
├── StructuredDataService.php # رسوم JSON-LD من schema.org للصفحات العامة
├── TagService.php         # توحيد الوسوم ومزامنتها وإدارتها
├── SettingService.php     # قراءة إعدادات الموقع وتحديثها
├── SystemHealthService.php# فحوصات بطاقة الصحة في الإدارة
├── TwoFactorService.php   # إعداد TOTP والتحقق منه ورموز الاسترداد
├── UserService.php        # التسجيل، والعمليات الأساسية، وتبديل الأدوار، والإحصاءات
└── VisitService.php       # تسجيل الزيارات وإحصاءات لوحة التحكم

routes/
├── web.php                # مجموعات المسارات العامة والضيوف والمسجّلين والموثّقين والإدارة
├── api.php                # نقاط JSON لواجهة API (انظر أدناه)
└── console.php            # المهام المجدولة: activitylog:clean وsitemap:generate وفحوصات الصحة والنسخ اليومية

lang/
└── en/  ar/  fr/          # ملفات ترجمة PHP لكل لغة

docs/
├── feature-audit-and-improvements.md  # تدقيق الميزات وقائمة التحسينات
└── spatie-integrations.md             # الروابط الثابتة، وSEO، والوسوم، وRSS، والحماية من الرسائل المزعجة، والنسخ الاحتياطي، والصحة، وPDF، وجداول البيانات
```

## مسارات التطبيق

### الويب

- **العامة**: `/` (الصفحة الرئيسية)، `/about`، `/docs`، `/blog` (القائمة، والبحث `?q=`، والتصفية `?tag=`)، `/feed` (RSS)، `/health.json` (محمي برمز)، `/blog/{slug}` (مقال منشور)، `/up` (فحص الصحة)، `/robots.txt`، `/sitemap.xml` (ملف ثابت يكتبه `sitemap:generate`)
- **مسارات الضيوف**: `/login`، `/register`، `/forgot-password`، `/reset-password/{token}`، `/two-factor-challenge` (الخطوة الثانية من تسجيل الدخول)
- **المسارات المصادَق عليها**: `/verify-email`، `/verify-email/{id}/{hash}` (رابط موقّع)، `/settings/profile`، `/settings/two-factor`، `/logout`
- **المسارات الموثّقة**: `/dashboard`
- **مسارات الإدارة** (وسيطا `verified` + `admin`):
  `/admin/dashboard`،
  `/admin/users`، `/admin/users/create`، `/admin/users/{user}/edit`،
  `/admin/categories`، `/admin/categories/create`، `/admin/categories/{category}/edit`،
  `/admin/posts`، `/admin/posts/create`، `/admin/posts/{post}/edit`،
  `/admin/posts/{post}/pdf`، `/admin/posts/export/{csv|xlsx}`،
  `/admin/categories/import`، `/admin/categories/export/{csv|xlsx}`، `/admin/tags`،
  `/admin/health`، `/admin/backups`،
  `/admin/activity-log`، `/admin/activity-log/export/{csv|json}`، `/admin/settings`، `/admin/notifications`
- **تبديل اللغة**: `/language/{locale}` يحفظ اللغة المختارة في الجلسة

### API

تبدأ كل نقاط النهاية بالمسار `/api` وتُعرَّف في `routes/api.php`. تُربط كل نقطة بدالة في خدمة، وتُحمى بالبوابات نفسها (`can:manage users` و`can:manage posts` وغيرها) المستخدمة في واجهة الويب.

**المصادقة.** تقبل نقاط النهاية المصادَق عليها أحد الخيارين:

- رمز Bearer: `Authorization: Bearer <token>`، يُصدر عبر `POST /api/auth/token` مع `email` و`password` و`device_name`، و`two_factor_code` إذا كان التحقق بخطوتين مفعّلًا. ألغِه عبر `DELETE /api/auth/token`.
- ملف تعريف الارتباط للجلسة، يُنشأ عبر `POST /api/auth/login` مع `email` و`password`، و`two_factor_code` إذا كان التحقق بخطوتين مفعّلًا.

يقتصر التسجيل وتسجيل الدخول وإصدار الرموز على 6 طلبات في الدقيقة لكل بريد إلكتروني وعنوان IP.

| الطريقة والمسار                              | الحارس                  | الخدمة المرتبطة                    |
| -------------------------------------------- | ----------------------- | ---------------------------------- |
| `POST   /api/auth/register`                  | محدود المعدل            | `UserService::register`            |
| `POST   /api/auth/login`                     | محدود المعدل            | `UserService::attempt`             |
| `POST   /api/auth/token`                     | محدود المعدل            | إصدار رمز Sanctum                  |
| `DELETE /api/auth/token`                     | مصادقة                  | يلغي الرمز المستخدم                |
| `GET    /api/profile`                        | مصادقة                  | المستخدم الحالي                    |
| `PUT    /api/profile`                        | مصادقة                  | `UserService::updateProfile`       |
| `PUT    /api/profile/password`               | مصادقة                  | `UserService::updatePassword`      |
| `DELETE /api/profile`                        | مصادقة                  | `UserService::deleteOwnAccount`    |
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

تقبل نقاط القوائم `trashed=only|with` و`sort` و`direction`. وتقبل المقالات أيضًا `status` و`category_id` و`tag` (معرّف الوسم)، وتقبل `POST`/`PUT /api/posts` مصفوفة اختيارية `tags` من الأسماء؛ ويقبل المستخدمون `role=admin|member`. تغذّي نقاط `stats` و`options` القوائم المنسدلة وبطاقات لوحة التحكم.

الأخطاء على `/api` دائمًا بصيغة JSON. يعيد الموقع في وضع الصيانة `503` لجميع الزوار ما عدا المسؤولين.

## ملاحظات حول الميزات

هذه الميزات تعمل فور التثبيت، لكنها سهلة الإغفال عند قراءة الكود.

- **إحصاءات الزيارات.** يسجّل `TrackVisit` طلبات الصفحات `GET` الناجحة بعد إرسال الاستجابة، فلا يبطئ التتبع الصفحة أبدًا. يتجاهل منطقة الإدارة و`/api` وطلبات Livewire وتبديل اللغة وتسجيل الخروج و`/up` والروبوتات الشائعة. تُعرض الإجماليات في لوحتي تحكم الإدارة والمستخدم.
- **إشعارات CRUD.** يراقب `CrudNotificationObserver` النماذج المدرجة في `config/crud_notifications.php` (المقالات والتصنيفات والمستخدمون والإعدادات افتراضيًا). عند الإنشاء أو التعديل أو الحذف أو الاستعادة، يحفظ `CrudActionNotification` في جدول `notifications` لكل مستخدم يملك دور `admin`، باستثناء الشخص الذي أجرى التغيير. تُتجاهل التحديثات التي تمسّ فقط `updated_at` أو `updated_by` أو `remember_token` أو `deleted_at`، فلا يُرسل تسجيل الدخول بخيار "تذكّرني" أي إشعار. يرى المسؤولون عدد الإشعارات غير المقروءة في جرس الشريط العلوي، ويديرون القائمة في `/admin/notifications` (التصفية، وتعليم إشعار أو كلها كمقروءة). لمراقبة نموذج آخر، أضفه إلى ملف الإعدادات.
- **صحة النظام.** يشغّل `SystemHealthService` فحوصات مستقلة لقاعدة البيانات والذاكرة المؤقتة والطابور، ويعرضها في بطاقة صحة النظام في لوحة الإدارة. فشل فحص واحد لا يكسر الصفحة.
- **وضع الصيانة.** يُفعَّل من **الإدارة ← الإعدادات**. يعرض `EnforceMaintenanceMode` صفحة 503 للزوار وJSON على `/api`. يبقى المسؤولون وصفحتا تسجيل الدخول والتحقق بخطوتين وتبديل اللغة و`/up` متاحين.
- **تنسيق التاريخ.** تختار إعدادات الإدارة تنسيقًا واحدًا للتواريخ المعروضة في سجل النشاط وقائمة المستخدمين. الخيارات معرّفة في `Setting::DATE_FORMATS`.
- **صور الغلاف.** يمكن أن تملك المقالات صورة غلاف تُرفع من نموذج المقال وتُخزَّن على القرص `public`. تظهر في `/blog` وفي صفحة المقال. شغّل `php artisan storage:link` أولًا.
- **خريطة الموقع بصيغة XML.** يسرد `php artisan sitemap:generate` (عبر `SitemapService`) المسارات المسماة في `config/sitemap.php` (`home` و`about` و`docs` و`blog`) وكل مقال يقدّمه فعليًا `/blog/{slug}` (منشور وغير محذوف). تُبنى العناوين من `APP_URL`، ولا تحتوي على سلاسل استعلام، وتُزال منها التكرارات. يكون `<lastmod>` هو `updated_at` للمقال (وأحدثها في `/blog`)؛ ولا تحصل الصفحات الثابتة عليه، ويُحذف `changefreq`/`priority`. تُولَّد الملفات وتُتحقق ثم تُكتب في ملف مؤقت وتُنقل إلى مكانها، فإذا فشل التشغيل بقيت الخريطة السابقة سليمة وخرج الأمر برمز خطأ. وفوق 50,000 عنوان يكتب `sitemap-1.xml` و`sitemap-2.xml` … خلف فهرس خرائط. تُعاد بناؤها كل ساعة (`SITEMAP_SCHEDULE`، مع `withoutOverlapping`)، ويضع `PostSitemapObserver` في الطابور `GenerateSitemapJob` بلا تكرار عند كل تغيير في مقال منشور. لا تُدرج مسارات المصادقة والحساب والإدارة وAPI واللغة أبدًا؛ والتصنيفات ليس لها صفحة عامة فلا تُدرج أيضًا. يقدّم `RobotsController` الملف `/robots.txt`، ويضيف سطر `Sitemap:` بعد وجود الملف.
- **بريد الدعم.** يُضبط في إعدادات الإدارة ويُخزَّن مع إعدادات الموقع.

## الترجمة

اللغات المتاحة مُعرّفة في `config/app.php` ضمن `available_locales`:

```php
'available_locales' => [
    'en' => ['label' => 'English', 'dir' => 'ltr'],
    'ar' => ['label' => 'العربية', 'dir' => 'rtl'],
    'fr' => ['label' => 'Français', 'dir' => 'ltr'],
],
```

يطبّق وسيط `SetLocale` لغة الجلسة في كل طلب، ويتولى `LocaleController` التبديل، ويُستنتج اتجاه النص (`ltr`/`rtl`) تلقائيًا لعرض RTL صحيح. لإضافة لغة، أضف مدخلًا هنا ومجلد `lang/{locale}` يحتوي على الملفات نفسها الموجودة في `lang/en`.

يفحص الأمر `php artisan translations:sync` (`app/Console/Commands/SyncTranslationKeys.php`) مجلد `resources/` بحثًا عن مفاتيح الترجمة، وينشئ ملفات اللغة الناقصة، ويملأ المفاتيح الناقصة في اللغات الأخرى. الخيارات:

- `--locales=en,fr,ar` — اللغات المراد مزامنتها (الافتراضي: الكل)
- `--base=en` — اللغة المرجعية

تُسبق القيم غير المترجمة بـ`[TODO]` لتسهيل العثور عليها وإكمالها.

## الأدوار والصلاحيات

تُملأ الأدوار والصلاحيات بواسطة `RolesAndPermissionsSeeder`. يحصل دور `admin` على الصلاحيات التالية:

- `view admin dashboard`
- `manage users`
- `manage posts`
- `manage categories`
- `view activity log`
- `manage settings`
- `manage tags`
- `view system health`
- `manage backups`

تحقّق من صلاحية المسؤول في الكود عبر `$user->isAdmin()` أو عبر الاسم المستعار `admin` لوسيط المسار.

تمر إجراءات المقالات والتصنيفات بالإضافة إلى ذلك عبر `App\Policies\PostPolicy` و`App\Policies\CategoryPolicy`. عرض المقالات وإنشاؤها يتطلب `manage posts`. أما تعديل المقال أو نقله إلى السلة أو استعادته أو حذفه نهائيًا فيتطلب أيضًا أن يكون المستخدم كاتب المقال، إلا إذا كان مسؤولًا. التصنيفات مشتركة، لذلك تتطلب كل عملية على تصنيف `manage categories` فقط.

## إنشاء مورد جديد

```bash
php artisan starter:make-crud Article
```

ينشئ هذا الأمر النموذج `Article` والترحيل والمصنع و`ArticleService` و`ArticlePolicy` و`ArticleModuleTest`، مع الحذف المؤقت وتسجيل النشاط. ولا يستبدل أي ملف موجود. بعد ذلك أضف الصلاحية `manage articles` إلى `RolesAndPermissionsSeeder`، ثم أضف المسارات ومتحكم API والشاشات التي تحتاجها.

## الاختبارات وجودة الكود

```bash
# شغّل مجموعة الاختبارات
composer test          # أو: php artisan test

# تحقق من نمط الكود (Laravel Pint)
vendor/bin/pint --test # أضف --dirty لفحص الملفات المعدّلة فقط، واحذف --test للإصلاح

# التحليل الساكن (Larastan / PHPStan)
composer analyse
```

توجد المجموعة في `tests/Feature` وتغطي المصادقة والتحقق بخطوتين، والتحقق من البريد، وإدارة الملف الشخصي، وإدارة المستخدمين والمقالات والتصنيفات، وصفحات الإدارة، وواجهة REST API، وإشعارات CRUD، ووضع الصيانة، والبحث، والحذف المؤقت، والترجمة. تعمل الاختبارات على قاعدة بيانات SQLite في الذاكرة مع ذاكرة مؤقتة من نوع array وبريد array وطابور متزامن (انظر `phpunit.xml`).

## التكامل المستمر

يعمل سير عمل GitHub Actions (`.github/workflows/ci.yml`) عند كل push إلى `develop` و`main` وعند كل Pull Request. يستخدم PHP 8.4 ويقوم بما يلي:

- تشغيل مجموعة الاختبارات (مع بناء ملفات الواجهة الأمامية أولًا باستخدام Node 20)، و
- فحص نمط الكود باستخدام Pint وتشغيل التحليل الساكن باستخدام Larastan.

## الرخصة

MajidaStarter برنامج مفتوح المصدر مرخّص بموجب [رخصة MIT](https://opensource.org/licenses/MIT). وهو مبني على [إطار عمل Laravel](https://laravel.com)، وهو أيضًا مرخّص بموجب MIT.

</div>
