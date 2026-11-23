# Damas Guide — Backend (Laravel)

الباك اند الجديد لمشروع "دليل دمشق"، منقول من Django (`../backend`) إلى Laravel 13.
**قاعدة أساسية:** الـ API لازم يبقى مطابق للقديم (نفس المسارات ونفس شكل الـ JSON) حتى لا نعيد ربط الواجهات.

## المتطلبات

- PHP 8.3+ مع الامتدادات: `pdo_pgsql`, `pgsql`, `mbstring`, `intl`
- Composer
- Node.js + npm (للواجهة المدمجة)
- PostgreSQL

## التثبيت (أول مرة)

من مجلد `backend2`:

```
composer install
copy .env.example .env
php artisan key:generate
```

أنشئ قاعدة البيانات وعدّل بيانات `DB_*` بملف `.env`:

```
psql -U postgres -c "CREATE DATABASE damascus_guide_db;"
```

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=damascus_guide_db
DB_USERNAME=postgres
DB_PASSWORD=كلمة_السر
```

ثم:

```
php artisan migrate
php artisan migrate --seed
npm install
```

> بديل بخطوة وحدة بعد تجهيز القاعدة: `composer setup`

## التشغيل

```
php artisan serve
npm run dev
```

- الـ API: `http://localhost:8000/api/...`
- توثيق الـ API (Swagger): `http://localhost:8000/docs/api`
- افتح التوثيق بنفس الـ host الموجود في `APP_URL` حتى لا يظهر خطأ CORS في "Try It".

## الـ Seeders

شغّلها يدوياً بالترتيب. كل Seeder بيستخدم `updateOrCreate` فإعادة تشغيله آمنة.

| الترتيب | الأمر | يملأ جدول | ملف البيانات | يعتمد على |
|---|---|---|---|---|
| 1 | `php artisan db:seed --class=AdminAreaSeeder` | `admin_areas` | `database/data/damascus_admin_areas.geojson` | — |


## الاختبارات وجودة الكود

```
php artisan test                      # الاختبارات (Pest + SQLite in-memory)
composer lint                         # تنسيق الكود (Pint)
composer test                         # lint + phpstan + الاختبارات
```

الـ Controller ما فيه منطق: كل شيء بالـ Service.

## هيكلة الكود

تم تنظيم المشروع حسب نوع الـ API والجهة التي تستخدمه، لتسهيل توزيع العمل بين المطورين وفصل المسؤوليات.

### 1. Controllers

المسار: `app/Http/Controllers/Api/`

```text
Controllers/
└── Api/
    ├── Admin/       # APIs الخاصة بلوحة الإدارة
    ├── General/     # APIs العامة والمشتركة
    └── App/         # APIs الخاصة بتطبيق المستخدم
```

**مسؤولية Controller:**
- استقبال HTTP Request.
- استدعاء الـ Service المناسب.
- إرجاع HTTP Response.
- لا يحتوي على منطق الأعمال (Business Logic).

### 2. Form Requests

المسار: `app/Http/Requests/Api/`

```text
Requests/
└── Api/
    ├── Admin/       # Validation لطلبات الإدارة
    ├── General/     # Validation للطلبات العامة
    └── App/         # Validation لطلبات التطبيق
```

**مسؤولية Form Request:**
- التحقق من صحة البيانات القادمة من المستخدم (Validation).
- تحديد قواعد التحقق ورسائل الأخطاء.
- إبقاء منطق التحقق منفصلاً عن Controller.

### 3. Services

المسار: `app/Services/`

```text
Services/
├── Admin/           # منطق الأعمال الخاص بالإدارة
├── General/         # منطق الأعمال المشترك والعام
└── App/             # منطق الأعمال الخاص بالتطبيق
```

**مسؤولية Service:**
- تنفيذ منطق الأعمال (Business Logic).
- التعامل مع Models وقاعدة البيانات عند الحاجة.
- تنفيذ العمليات المطلوبة وإرجاع النتائج إلى Controller.
- عدم التعامل مباشرة مع HTTP Request أو Response.

> ملاحظة: مجلد Services لا يحتوي على مجلد Api؛ لأن تقسيمه يعتمد على مجال العمل والجهة المستفيدة، وليس على طبقة HTTP.

### 4. Models

المسار: `app/Models/`

```text
Models/
├── AdminArea.php
├── Street.php
└── ...
```

تمثل الـ Models جداول قاعدة البيانات والعلاقات بينها، وتُستخدم من قبل Services للتعامل مع البيانات.

### 5. API Resources

المسار: `app/Http/Resources/`

تُستخدم لتحديد شكل البيانات (JSON Response) التي يتم إرجاعها من الـ API، وفصل تنسيق المخرجات عن منطق الأعمال.

### 6. Support

المسار: `app/Support/`

يحتوي على الوظائف المساعدة المشتركة التي يمكن استخدامها في أكثر من مكان، مثل:

- `SearchIndex`: توحيد وتجهيز النصوص المستخدمة في البحث.
- `Geometry`: الوظائف المساعدة المتعلقة بالبيانات الجغرافية.

---

### آلية تدفق الطلب (Request Flow)

```text
HTTP Request
     │
     ▼
Form Request
(Validation)
     │
     ▼
Controller
(استقبال الطلب وتوجيهه)
     │
     ▼
Service
(Business Logic)
     │
     ▼
Model
(Database Operations)
     │
     ▼
API Resource
(JSON Formatting)
     │
     ▼
HTTP Response
```

### المبادئ المعتمدة في تنظيم المشروع

1. **Separation of Concerns:** كل طبقة مسؤولة عن مهمة محددة.
2. **Thin Controllers:** الـ Controllers تستقبل الطلبات وتستدعي الخدمات فقط.
3. **Business Logic in Services:** منطق الأعمال يكون داخل Services وليس Controllers.
4. **Request Validation:** التحقق من البيانات يتم عبر Form Requests.
5. **Consistent API Responses:** تنسيق JSON يتم عبر API Resources عند الحاجة.
6. **Code Reusability:** الوظائف المشتركة توضع داخل Support لتجنب تكرار الكود.

**ملاحظة:** تقسيم الملفات إلى مجلدات داخلية هو تنظيم للكود فقط، ولا يعني تغيير مسارات الـ API أو أسماء الـ Endpoints الحالية.
