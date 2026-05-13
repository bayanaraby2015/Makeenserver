# دليل تثبيت منصة مكين على الاستضافة المشتركة (Shared Hosting / cPanel)

> هذا الدليل لـ **Sprint 0 — Skeleton فقط**. لا يحتوي بعد على وحدات الأعمال (المبادرات/الزيارات/الدفعات…). الهدف الآن: التأكد من أن الإطار يعمل على بيئتك ثم نُكمل البناء سبرنت بسبرنت.

---

## المتطلبات الأساسية على الاستضافة

| المتطلب | الحد الأدنى | للتحقق في cPanel |
|---|---|---|
| PHP | 8.2 أو 8.3 | "MultiPHP Manager" — اختر 8.3 |
| إضافات PHP | mbstring, xml, intl, zip, gd, bcmath, exif, curl, openssl, pdo, pdo_mysql (أو pdo_sqlite), fileinfo | "Select PHP Version" → Extensions |
| MySQL | 8.0+ (أو MariaDB 10.6+) | "MySQL Databases" |
| SSH (مفضّل) | للتشغيل عبر `php artisan` | "SSH Access" |
| Cron Jobs | لتشغيل المهام المجدولة | "Cron Jobs" |

> **بدون SSH:** يمكن العمل لكن مع قيود (لا artisan مباشر). الأفضل تفعيل SSH ولو مؤقتاً.

---

## الخيار أ — رفع نسخة كاملة (vendor متضمَّن)

> الأبسط للاستضافات المشتركة بدون composer.

### 1) رفع الملفات

1. حمّل ملف `makeen-platform-full.zip` على جهازك.
2. ارفعه عبر File Manager في cPanel إلى مجلد `home/<USER>/` (وليس داخل `public_html`).
3. فُكّ الضغط → سيُنشئ مجلد `makeen-platform/`.
4. أعِد تسمية المجلد إلى ما يناسبك (مثلاً `makeen/`).

### 2) إعادة هيكلة المجلدات لتفصل `public_html` عن باقي المشروع

في الاستضافة المشتركة `public_html` هو الـ document root. لارافل يحتاج فقط `public/` معروضاً للويب، والباقي **خارج** `public_html`.

```
home/<USER>/
├── makeen/                  ← مشروع لارافل كاملاً (app, config, vendor...)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── lang/
│   ├── public/              ← لن نستعمل هذا داخلياً
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                 ← (نُنشئه في الخطوة 3)
│   ├── artisan
│   └── composer.json
└── public_html/             ← الـ document root
    ├── .htaccess
    └── index.php            ← (نعدّله في الخطوة 4)
```

#### خطوات النقل:
1. انقل **محتويات** `makeen/public/` (وليس المجلد نفسه) إلى `public_html/`. لا تنسَ الملف المخفي `.htaccess`.
2. احذف `makeen/public/` بعد النقل، أو اتركه فارغاً.

### 3) إنشاء ملف `.env`

```bash
cd ~/makeen
cp .env.example .env
nano .env
```

عدِّل القيم التالية:
```env
APP_NAME="منصة مكين"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_makeen      ← من cPanel "MySQL Databases"
DB_USERNAME=cpaneluser_makeen
DB_PASSWORD=YOUR_STRONG_PASSWORD

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@yourdomain.com
MAIL_PASSWORD=YOUR_MAIL_PASS
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"

ACTIVITY_LOG_LOCKED=true
```

### 4) تعديل `public_html/index.php`

افتحه عبر File Manager → Edit:

```php
// قبل:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// بعد (لاحظ المسار الجديد):
require __DIR__.'/../makeen/vendor/autoload.php';
$app = require_once __DIR__.'/../makeen/bootstrap/app.php';
```

### 5) تشغيل أوامر التهيئة (يحتاج SSH)

```bash
cd ~/makeen
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:assets
```

> **بدون SSH:** يمكنك زيارة رابط مؤقت ينفّذ هذه الأوامر — تواصل معي وأضيف لك راوت setup مؤقت.

### 6) ضبط الصلاحيات

عبر File Manager (يمين-كليك → Permissions):
- `storage/` ⇒ 775 (recursive)
- `bootstrap/cache/` ⇒ 775 (recursive)
- جميع الملفات ⇒ 644
- المجلدات ⇒ 755

أو عبر SSH:
```bash
cd ~/makeen
chmod -R 775 storage bootstrap/cache
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

### 7) إنشاء أول حساب super_admin والأدوار (يحتاج SSH — Sprint 1)

```bash
cd ~/makeen
# عيّن بيانات المدير الافتراضية في .env قبل التنفيذ:
#   SUPER_ADMIN_EMAIL=admin@yourdomain.com
#   SUPER_ADMIN_PASSWORD=ChangeMe!2026

php artisan db:seed --force
```

السيدر يُنشئ:
1. الأدوار السبعة الأساسية (super_admin, excellence_manager/member, donor_admin, consultant, association_manager/member).
2. الصلاحيات الأساسية وربطها بالأدوار.
3. مستخدم super_admin واحد ببيانات الـ `.env`. **لو نسيت تعيين كلمة المرور، السيدر يولِّد كلمة عشوائية ويطبعها مرة واحدة على الشاشة — احفظها فوراً.**

اختبر بعدها:
- `https://yourdomain.com/` → يحوِّل إلى `/admin`
- `https://yourdomain.com/admin/login` → ادخل ببيانات super_admin
- `https://yourdomain.com/register/association` → نموذج تسجيل الجمعيات الفعلي
- `https://yourdomain.com/excellence|donor|consultant|association/login` → لوحات الأدوار الأخرى

### 8) Cron Job

في cPanel → Cron Jobs → أضف:

```
* * * * * cd /home/<USER>/makeen && php artisan schedule:run >> /dev/null 2>&1
```

---

## الخيار ب — رفع نسخة source فقط (مع SSH + composer)

```bash
# على جهازك:
unzip makeen-platform-src.zip
# ارفع المجلد عبر FTP/SFTP إلى ~/makeen

# عبر SSH على السيرفر:
cd ~/makeen
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env  # عدّل كما في الخيار أ
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan filament:assets

# باقي الخطوات (نقل public/* إلى public_html، تعديل index.php، الصلاحيات، الكرون) كما في الخيار أ
```

---

## استكشاف الأخطاء الشائعة

| الخطأ | السبب | الحل |
|---|---|---|
| `500 Internal Server Error` | صلاحيات أو .env مفقود | راجع الخطوة 6 + تحقق وجود `.env` بـ `APP_KEY` |
| `Class not found` | autoloader غير محدَّث | `composer dump-autoload -o` |
| `Permission denied` على `storage/logs` | صلاحيات | `chmod -R 775 storage` |
| `Database not found` | اسم DB خاطئ في `.env` | راجع cPanel ⇒ MySQL Databases |
| صفحة بيضاء على `/admin` | كاش قديم | `php artisan optimize:clear` |
| الـ CSS مكسور في Filament | الأصول غير منشورة | `php artisan filament:assets` ثم انسخ `public/build` و`public/css` و`public/js` إلى `public_html/` |

---

## ما المتوفّر فعلياً في هذه النسخة (Sprint 0)؟

- [x] هيكل Laravel 12 + Filament 4 جاهز
- [x] 5 لوحات Filament (admin, excellence, donor, consultant, association) بألوان مختلفة
- [x] حزم Spatie كاملة (permission, activitylog, model-states, medialibrary, query-builder)
- [x] ملفات ترجمة `lang/ar` + `lang/en`
- [x] مسار التسجيل العام للجمعيات `/register/association` (Stub)
- [x] فحص الجودة (Pint + Larastan + Pest 9 tests passing)
- [x] CI workflow في `.github/workflows/ci.yml` (للاستخدام لاحقاً مع GitHub)
- [x] هيكل `app/Domain/{Module}` فارغ (سيُملأ في Sprints 1-9)
- [x] ADRs الثلاثة في `docs/architecture/decisions/`
- [x] التحليل الكامل في `docs/business/`

## ما **ليس** متوفراً بعد (سيأتي في Sprints لاحقة)

- [ ] جداول قاعدة البيانات الخاصة بالأعمال (المبادرات، الزيارات، …)
- [ ] حقول نموذج التسجيل العامة للجمعيات (بانتظار قائمة الحقول منك)
- [ ] State Machine للمبادرات
- [ ] Initiative Workspace UI
- [ ] دور super_admin مع Seeder افتراضي

أرسلها لي بعد التركيب وأبدأ Sprint 1 مباشرة.
