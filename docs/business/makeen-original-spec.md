# منصة مكين — نظام إدارة المبادرات والتمكين المؤسسي
**Makeen Platform — Initiatives & Institutional Empowerment Management System**

> منصة لارافل متكاملة لإدارة دورة حياة مبادرات التحسين المؤسسي للجمعيات غير الربحية، بدءاً من رفع البطاقة التعريفية مروراً بالاعتماد متعدد المراحل، ووصولاً إلى التنفيذ والمتابعة الشهرية والتقييم النهائي.

---

## 📑 جدول المحتويات

1. [نظرة عامة](#1-نظرة-عامة)
2. [الأطراف ذات العلاقة](#2-الأطراف-ذات-العلاقة)
3. [المنهجية المعتمدة](#3-المنهجية-المعتمدة)
4. [الستاك التقني](#4-الستاك-التقني)
5. [البنية المعمارية](#5-البنية-المعمارية)
6. [نموذج البيانات (Database Schema)](#6-نموذج-البيانات-database-schema)
7. [الأدوار والصلاحيات](#7-الأدوار-والصلاحيات)
8. [مسار سير العمل (Workflow / State Machine)](#8-مسار-سير-العمل-workflow--state-machine)
9. [الموديولات الوظيفية](#9-الموديولات-الوظيفية)
10. [لوحات التحكم حسب الدور](#10-لوحات-التحكم-حسب-الدور)
11. [نظام الإشعارات والتنبيهات](#11-نظام-الإشعارات-والتنبيهات)
12. [نظام الفلترة والبحث](#12-نظام-الفلترة-والبحث)
13. [إدارة النظام (Admin Control)](#13-إدارة-النظام-admin-control)
14. [هيكل المجلدات](#14-هيكل-المجلدات)
15. [التثبيت والتشغيل](#15-التثبيت-والتشغيل)
16. [الأمان والحوكمة](#16-الأمان-والحوكمة)
17. [استراتيجية الاختبار](#17-استراتيجية-الاختبار)
18. [خارطة طريق التطوير](#18-خارطة-طريق-التطوير)
19. [النشر (Deployment)](#19-النشر-deployment)

---

## 1. نظرة عامة

منصة **مكين** هي نظام ويب متكامل مبني بـ **Laravel 11 + Filament 3** يقوم بأتمتة المرحلة 3 من مشروع مكين الكلي وهي **«التنفيذ والمتابعة»** التي تستمر **32 شهراً**، عبر:

- إدارة **13 جمعية مشاركة** ومبادراتها التحسينية.
- نموذج موحَّد من **9 أقسام** يُملأ بالتسلسل بين 4 جهات (الجمعية / مسار الإجادة / المستشار / المؤسسة المانحة).
- **45 مؤشر أداء معرَّف مسبقاً** موزَّع على 3 مجالات و11 معياراً.
- **دورة شهرية متكررة** تشمل: جدولة الزيارة → اختيار الموعد → الزيارة → رفع الشواهد → التقرير الشهري.
- **عمليات داعمة** (تذاكر، طلب دفعات، تقييم الخدمة) في أي وقت.
- **نظام تنبيهات ذكي** عبر البريد + داخل المنصة.
- **لوحات تحكم متخصصة** لكل دور.

### الأهداف الرئيسية
| الهدف | المؤشر |
|---|---|
| أتمتة دورة اعتماد المبادرة | تقليل وقت الاعتماد من أسابيع إلى أيام |
| توحيد منهجية التقييم | اعتماد 45 KPI موحّد على كل المبادرات |
| متابعة فورية للتنفيذ | تنبيهات بزمن حقيقي + تقارير شهرية |
| شفافية كاملة | Activity Log + Audit Trail لكل تغيير |
| تمكين الأطراف | لوحات مخصصة لكل دور بصلاحيات دقيقة |

---

## 2. الأطراف ذات العلاقة

```
┌─────────────────────────────────────────────────────────────────┐
│                     منصة مكين                                  │
└─────────────────────────────────────────────────────────────────┘
        │           │              │              │
        ▼           ▼              ▼              ▼
  ┌─────────┐  ┌─────────┐  ┌──────────────┐  ┌──────────┐
  │ الجمعية │  │ المستشار │  │ مسار الإجادة │  │ المانحة  │
  │ (13)    │  │  (4)    │  │ (الفريق     │  │          │
  │         │  │         │  │ الاستشاري)  │  │          │
  └─────────┘  └─────────┘  └──────────────┘  └──────────┘
```

### تفصيل الأطراف
| الطرف | الدور في النظام | عدد المستخدمين المتوقع |
|---|---|---|
| **الجمعية** | رفع المبادرة، رفع الشواهد، اختيار الزيارات، طلب الدفعات | 13 جمعية × 2-3 مستخدم |
| **المستشار المتخصص** | مراجعة المخرجات، الزيارات، التقارير الشهرية، الأسئلة | 4 تخصصات (مالي / أوقاف / تخطيط / أثر تنموي) |
| **مسار الإجادة** (الفريق الاستشاري) | الاعتماد الأولي، إدارة العمليات، التقارير الفنية الدورية | 3-5 أعضاء |
| **المؤسسة المانحة** | الاعتماد النهائي، المتابعة العامة، تقييم الأثر | 1-2 مستخدم |
| **إدارة النظام** | تحكم كامل، إدارة المستخدمين والصلاحيات والبيانات المرجعية | 1-2 super-admin |

---

## 3. المنهجية المعتمدة

### 3.1 نموذج موحَّد + Multi-Step Wizard
- نموذج المبادرة **واحد** للجميع (الجمعيات الـ13 موحَّدة في البنية).
- يُقسَّم إلى **9 خطوات** كـ Wizard مع حفظ تلقائي بعد كل خطوة.
- صلاحيات على مستوى **القسم** عبر Policy خاصة لكل قسم.
- كل قسم له **Form Request** خاص يتحقق من القيم.

### 3.2 State Machine للمبادرة
- يدير الانتقال بين 11 حالة (draft → ... → closed).
- يحدد **من** يحرّر **ماذا** و**متى**.
- يولّد **Activity Log** تلقائياً عند كل انتقال.

### 3.3 RBAC + Multi-Tenancy خفيف
- **6 أدوار** عبر `spatie/laravel-permission`.
- **Tenant Scoping** على مستوى الجمعية: كل جمعية ترى مبادراتها فقط (Global Scope على Eloquent).
- **Granular Permissions** قابلة للتخصيص من لوحة الإدارة.

### 3.4 Modular Monolith
- موديولات منفصلة تحت `app/Domain/{ModuleName}` (Initiatives, Visits, Reports, Tickets, Payments, Notifications, Reference).
- اتصال بين الموديولات عبر **Events + Listeners** بدلاً من الاستدعاء المباشر.
- قابل للفصل لاحقاً إلى ميكروسيرفسز إذا احتجنا.

### 3.5 Domain-Driven principles خفيفة
- **Models** للنواة فقط (Initiative, Visit, Report, ...).
- **Actions** أو **Services** للسلوك المعقد (مثلاً: `SubmitInitiativeAction`, `ScheduleMonthlyVisitsAction`).
- **Events** لكل حدث مهم (`InitiativeApproved`, `EvidenceUploaded`, `VisitConducted`).
- **Listeners** تتعامل مع التنبيهات والمهام الخلفية.

---

## 4. الستاك التقني

### 4.1 Backend
| التقنية | الإصدار | الاستخدام |
|---|---|---|
| **PHP** | 8.2+ | Runtime |
| **Laravel** | 11.x | Framework |
| **MySQL** | 8.0+ (أو MariaDB 10.6+) | DB |
| **Redis** | 7.x | Cache + Queues + Sessions |
| **Horizon** | latest | إدارة الـ queues |

### 4.2 الحزم الأساسية (Packages)
```json
{
  "filament/filament": "^3.2",
  "spatie/laravel-permission": "^6.x",
  "spatie/laravel-activitylog": "^4.x",
  "spatie/laravel-medialibrary": "^11.x",
  "spatie/laravel-model-states": "^2.x",
  "spatie/laravel-backup": "^9.x",
  "spatie/laravel-query-builder": "^6.x",
  "barryvdh/laravel-dompdf": "^3.x",
  "maatwebsite/excel": "^3.x",
  "laravel/horizon": "^5.x",
  "laravel/scout": "^10.x",
  "league/flysystem-aws-s3-v3": "^3.x"
}
```

### 4.3 Frontend
| التقنية | الاستخدام |
|---|---|
| **Filament 3** | لوحات الإدارة + Resources + Forms + Tables |
| **Livewire 3** | تفاعلية ديناميكية (Wizard) |
| **Alpine.js** | تفاعل خفيف على المستوى DOM |
| **Tailwind CSS** | التصميم (مع RTL) |
| **FullCalendar** | عرض جداول الزيارات والاعتمادات |
| **Chart.js / ApexCharts** | الرسوم البيانية في Dashboards |

### 4.4 الأدوات المساعدة
| الأداة | الاستخدام |
|---|---|
| **Pint** | Code style (PSR-12) |
| **Larastan** | Static analysis |
| **Pest 3** | Testing framework |
| **Pail** | Real-time logs |
| **Telescope** | Debug في dev فقط |

---

## 5. البنية المعمارية

```
┌───────────────────────────────────────────────────────────────────┐
│                       طبقة العرض (Presentation)                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │ Filament     │  │ Public Web   │  │ API (مستقبلاً للموبايل)│  │
│  │ Panels (×4)  │  │ (Auth/Login) │  │ Sanctum              │  │
│  └──────────────┘  └──────────────┘  └──────────────────────┘  │
└───────────────────────────────────────────────────────────────────┘
                              │
┌───────────────────────────────────────────────────────────────────┐
│                    طبقة التطبيق (Application)                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │ Actions      │  │ Form Requests│  │ Policies             │  │
│  │ (Services)   │  │ (Validation) │  │ (Authorization)      │  │
│  └──────────────┘  └──────────────┘  └──────────────────────┘  │
└───────────────────────────────────────────────────────────────────┘
                              │
┌───────────────────────────────────────────────────────────────────┐
│                       طبقة المجال (Domain)                       │
│  ┌──────────┬──────────┬──────────┬──────────┬──────────┐      │
│  │Initiatives│Visits   │Reports  │Tickets  │Payments  │ ...    │
│  │           │         │         │         │           │        │
│  │Models     │Models   │Models   │Models   │Models    │        │
│  │States     │States   │States   │         │States    │        │
│  │Events     │Events   │Events   │Events   │Events    │        │
│  └──────────┴──────────┴──────────┴──────────┴──────────┘      │
└───────────────────────────────────────────────────────────────────┘
                              │
┌───────────────────────────────────────────────────────────────────┐
│                  طبقة البنية التحتية (Infrastructure)            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────────┐  │
│  │  MySQL   │  │  Redis   │  │   S3     │  │  Mail / SMS /  │  │
│  │          │  │ (Queues) │  │ (Files)  │  │  WhatsApp      │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────────┘  │
└───────────────────────────────────────────────────────────────────┘
```

### Filament Panels (4 لوحات منفصلة)
يستخدم النظام **multi-panel architecture** من Filament 3:

| Panel | الـ URL | المستهدف |
|---|---|---|
| `admin` | `/admin` | super_admin (تحكم كامل) |
| `excellence` | `/excellence` | الفريق الاستشاري (مسار الإجادة) |
| `donor` | `/donor` | المؤسسة المانحة |
| `consultant` | `/consultant` | المستشارون |
| `association` | `/association` | الجمعيات |

كل لوحة لها resources وdashboards وصلاحيات منفصلة، مع توحيد الـ Models والـ DB.

---

## 6. نموذج البيانات (Database Schema)

### 6.1 جداول الهوية والمنظمات (5 جداول)

#### `users`
```sql
id, name, email, password, phone, avatar, locale, two_factor_secret,
two_factor_recovery_codes, two_factor_confirmed_at, last_login_at,
last_login_ip, is_active, created_at, updated_at, deleted_at
```

#### `organizations`
```sql
id, type ENUM('association','donor','excellence_team','consultant_firm'),
name_ar, name_en, code (unique), logo_path, contact_email, contact_phone,
website, region, address, license_number, manager_user_id (FK users),
is_active, created_at, updated_at, deleted_at
```

#### `organization_user` (pivot)
```sql
id, organization_id, user_id, position, is_primary, joined_at
```

#### `consultants`
```sql
user_id PK (FK users), specialty ENUM(...), bio, hourly_rate,
years_of_experience, cv_path, is_active
```

#### `roles` + `permissions` + `model_has_*` (من spatie/permission)
- 6 أدوار رئيسية
- صلاحيات granular قابلة للتخصيص

### 6.2 جداول البيانات المرجعية (4 جداول)

#### `areas` (المجالات الثلاثة)
```sql
id, code (developmental_impact|sustainability|institutional_empowerment),
name_ar, name_en, description, icon, color, sort_order
```

#### `standards` (11 معياراً)
```sql
id, area_id (FK), code, name_ar, name_en, description, sort_order
```

#### `kpis` (45 مؤشراً)
```sql
id, standard_id (FK), code, name_ar, name_en, description,
unit_of_measurement, sort_order, is_active
```

#### `specialties`
```sql
id, code, name_ar, name_en, description
```

### 6.3 جداول المبادرة (10 جداول)

#### `initiatives` (الجدول الجذر)
```sql
id, organization_id (FK), code (unique), title, status (state machine),
current_step, submitted_at, excellence_approved_at, excellence_approved_by,
consultant_review_completed_at, consultant_reviewed_by,
donor_approved_at, donor_approved_by, started_at, expected_end_date,
actual_end_date, closed_at, total_budget, vat_amount, total_with_vat,
duration_weeks, assigned_excellence_manager_id, assigned_consultant_id,
metadata JSON, created_by, created_at, updated_at, deleted_at
```

#### `initiative_basic_info` (1:1 — القسم 2)
```sql
id, initiative_id (FK unique), project_name, justification,
general_goal, description, strategic_objectives, responsible_dept,
owner_name, implementation_partners, human_scope, financial_scope,
duration_weeks, start_date, end_date, extra_attributes JSON
```

#### `initiative_areas` (M:M مع areas)
```sql
initiative_id, area_id, PRIMARY KEY (initiative_id, area_id)
```

#### `initiative_outputs` (1:N — القسم 3)
```sql
id, initiative_id (FK), sort_order, main_phase, activities,
output_name, quantity, output_description, expected_completion_date,
status, created_at, updated_at
```

#### `initiative_timeline_items` (1:N — القسم 4)
```sql
id, initiative_id (FK), output_id (FK nullable), sort_order,
main_phase, output_ref, quantity, duration_text,
individual_cost DECIMAL, total_cost DECIMAL, notes
```

#### `initiative_timeline_months` (1:N — تفصيل أشهر)
```sql
id, timeline_item_id (FK), month_number (1-12), is_active
```

#### `initiative_payments` (1:N — القسم 5، حتى 5 دفعات)
```sql
id, initiative_id (FK), payment_no, percentage DECIMAL,
amount DECIMAL, due_date, linked_outputs JSON, description, status
```

#### `initiative_kpi_values` (1:N — القسم 6، 45 سطراً للمبادرة)
```sql
id, initiative_id (FK), kpi_id (FK), baseline DECIMAL,
target DECIMAL, current_value DECIMAL, last_updated_at, notes,
UNIQUE (initiative_id, kpi_id)
```

#### `initiative_risks` (1:N — القسم 7)
```sql
id, initiative_id (FK), sort_order, risk_description,
probability ENUM('high','medium','low'),
impact ENUM('high','medium','low'), mitigation_plan, status
```

#### `initiative_consultant_approval` (1:1 — القسم 8)
```sql
id, initiative_id (FK unique), linked_to_plan BOOLEAN,
description_complete BOOLEAN, budget_appropriate BOOLEAN,
notes, signature_path, approved_at, approved_by_user_id (FK)
```

#### `initiative_donor_approval` (1:1 — القسم 9)
```sql
id, initiative_id (FK unique), notes, signature_path,
approved_at, approved_by_user_id (FK)
```

### 6.4 جداول التنفيذ والمتابعة (12 جدولاً)

#### `output_evidences` (الشواهد على المخرجات)
```sql
id, initiative_output_id (FK), uploaded_by_user_id (FK), file_path,
file_type, file_size, caption, status ENUM('pending','approved','rejected'),
quality_rating (1-5), reviewed_by_user_id (FK nullable),
reviewed_at, consultant_feedback, created_at, updated_at
```

#### `visit_slots` (الأوقات المقترحة من المستشار)
```sql
id, consultant_user_id (FK), initiative_id (FK), proposed_at,
type ENUM('onsite','remote'), status ENUM('available','chosen','expired'),
created_at
```

#### `visits` (الزيارات الشهرية)
```sql
id, initiative_id (FK), consultant_user_id (FK), slot_id (FK nullable),
type ENUM('onsite','remote'), meeting_link (للبعد), scheduled_at,
actual_started_at, actual_ended_at,
status ENUM('proposed','scheduled','conducted','reported','cancelled'),
created_at, updated_at
```

#### `visit_reports` (تقارير الزيارة + قبل الزيارة)
```sql
id, visit_id (FK unique), pre_visit_notes, report_body,
recommendations, prepared_at, created_at
```

#### `visit_evidences` (شواهد الزيارة — منفصلة عن JSON)
```sql
id, visit_id (FK), file_path, file_type, file_size, caption,
uploaded_by_user_id (FK), uploaded_at
```

#### `monthly_reports` (التقرير الشهري — الملخص التنفيذي)
```sql
id, initiative_id (FK), consultant_user_id (FK), year, month,
executive_summary, achievements, challenges, recommendations,
answered_questions JSON,
status ENUM('draft','submitted','approved'),
submitted_at, approved_by_donor_at, approved_by_user_id (FK),
UNIQUE(initiative_id, year, month)
```

#### `meetings` (اللقاءات عن بُعد)
```sql
id, initiative_id (FK), title, scheduled_at, duration_minutes,
meeting_link, agenda, status, created_by_user_id (FK)
```

#### `meeting_attendees`
```sql
id, meeting_id (FK), user_id (FK), attended BOOLEAN, attended_at
```

#### `tickets` (تذاكر الأسئلة)
```sql
id, initiative_id (FK), code, raised_by_user_id (FK),
raised_by_organization_id (FK), subject, body,
priority ENUM('low','medium','high'),
category ENUM('inquiry','technical','consultation','other'),
routing ENUM('to_manager','to_specialist','to_consultant'),
assignee_user_id (FK nullable), specialty (FK specialties nullable),
status ENUM('open','assigned','answered','closed'),
mentioned_in_report_id (FK monthly_reports nullable),
opened_at, closed_at, created_at, updated_at
```

#### `ticket_replies` (ردود متعددة)
```sql
id, ticket_id (FK), user_id (FK), body, attachments JSON,
is_final_answer BOOLEAN, created_at
```

#### `payment_requests` (طلبات الصرف)
```sql
id, initiative_payment_id (FK), requested_by_user_id (FK),
requested_at, notes,
status ENUM('eligible','requested','approved','paid','rejected'),
approved_at, approved_by_user_id (FK), paid_at, paid_by_user_id (FK),
rejection_reason
```

#### `service_evaluations` (تقييم الخدمة)
```sql
id, evaluator_user_id (FK), target_user_id (FK), target_organization_id (FK),
target_type ENUM('consultant','excellence_team','donor','association'),
service_type ENUM('visit','ticket_response','approval','report','overall'),
related_id (polymorphic), related_type,
rating (1-5), comments, created_at
```

### 6.5 جداول الإشعارات وسجل النشاط (3 جداول)

#### `notifications` (Laravel default)
```sql
id (uuid), type, notifiable_type, notifiable_id, data JSON,
read_at, created_at, updated_at
```

#### `notification_preferences` (تفضيلات المستخدم)
```sql
id, user_id (FK), notification_type, via_database BOOLEAN,
via_mail BOOLEAN, via_sms BOOLEAN, via_whatsapp BOOLEAN
```

#### `activity_log` (من spatie/activitylog)
```sql
id, log_name, description, subject_type, subject_id, causer_type,
causer_id, properties JSON, batch_uuid, created_at, updated_at
```

### 6.6 جدول الاعتمادات (1 جدول polymorphic)

#### `approvals` (سجل كل اعتمادات النظام)
```sql
id, approvable_type, approvable_id, actor_user_id (FK),
action ENUM('submit','approve','reject','request_revision','sign'),
stage, notes, signature_path, ip_address, user_agent, created_at
```

> **هذا الجدول يخدم:** اعتماد المبادرة بمراحلها الـ4 + اعتماد المخرج + اعتماد الدفعة + اعتماد التقرير الشهري.

### 6.7 إجمالي الجداول
- **35 جدولاً** فعلياً (بما فيها pivots وspatie/permission)
- مفاهيمياً: **24 جدولاً رئيسياً** كما في وثيقة التحليل

---

## 7. الأدوار والصلاحيات

### 7.1 الأدوار الستة

| Role | الاسم العربي | الـ Panel | المسؤوليات |
|---|---|---|---|
| `super_admin` | إدارة النظام | `/admin` | تحكم كامل في كل شيء |
| `excellence_manager` | مدير مسار الإجادة | `/excellence` | الاعتماد + إدارة العمليات + التقارير الفنية |
| `excellence_member` | عضو مسار الإجادة | `/excellence` | مراجعة وملاحظات (بدون اعتماد نهائي) |
| `donor_admin` | مدير المؤسسة المانحة | `/donor` | الاعتماد النهائي + المتابعة العامة |
| `consultant` | مستشار | `/consultant` | المراجعة + الزيارات + التقارير + الإجابة على الأسئلة |
| `association_manager` | مدير جمعية | `/association` | تحرير المبادرة + الشواهد + الدفعات + التذاكر |
| `association_member` | عضو جمعية | `/association` | كـ association_manager لكن بدون submit |

### 7.2 مصفوفة الصلاحيات الكاملة

| الصلاحية / الدور | super_admin | excellence | donor | consultant | assoc_mgr |
|---|:-:|:-:|:-:|:-:|:-:|
| **المبادرات** | | | | | |
| view_any_initiative | ✓ | ✓ | ✓ | محدود | محدود (جمعيته) |
| create_initiative | ✓ | — | — | — | ✓ |
| edit_initiative_sections_1_7 | ✓ | — | — | — | ✓ (في draft/needs_revision) |
| approve_excellence_step | ✓ | ✓ | — | — | — |
| review_consultant_step | ✓ | — | — | ✓ | — |
| approve_donor_final | ✓ | — | ✓ | — | — |
| reject_initiative | ✓ | ✓ | ✓ | — | — |
| **المخرجات والشواهد** | | | | | |
| upload_evidence | ✓ | — | — | — | ✓ |
| review_evidence | ✓ | — | — | ✓ | — |
| **الزيارات** | | | | | |
| propose_visit_slots | ✓ | — | — | ✓ | — |
| choose_visit_slot | ✓ | — | — | — | ✓ |
| conduct_visit | ✓ | — | — | ✓ | — |
| write_visit_report | ✓ | — | — | ✓ | — |
| **التقارير الشهرية** | | | | | |
| write_monthly_report | ✓ | — | — | ✓ | — |
| approve_monthly_report | ✓ | — | ✓ | — | — |
| view_monthly_report | ✓ | ✓ | ✓ | ✓ | ✓ (جمعيته) |
| **التذاكر** | | | | | |
| raise_ticket | ✓ | — | — | — | ✓ |
| answer_ticket | ✓ | ✓ (route) | — | ✓ | — |
| close_ticket | ✓ | ✓ | — | ✓ | منشئها فقط |
| **الدفعات** | | | | | |
| request_payment | ✓ | — | — | — | ✓ |
| approve_payment | ✓ | — | ✓ | — | — |
| **تقييم الخدمة** | | | | | |
| submit_evaluation | ✓ | ✓ | ✓ | ✓ | ✓ |
| view_evaluations | ✓ | ✓ | ✓ | own | own |
| **إدارة النظام** | | | | | |
| manage_users | ✓ | — | — | — | — |
| manage_roles | ✓ | — | — | — | — |
| manage_organizations | ✓ | — | — | — | — |
| manage_reference_data | ✓ | — | — | — | — |
| view_activity_log | ✓ | محدود | محدود | محدود | محدود |
| manage_settings | ✓ | — | — | — | — |

### 7.3 آلية تطبيق الصلاحيات
1. **Spatie Permission** للأذونات الأساسية.
2. **Policies** على الـ Models للقرارات السياقية (مثلاً: «يمكن تحرير القسم 8 فقط لو الحالة `excellence_review` ودوره `excellence_manager`»).
3. **Global Scopes** على Eloquent للـ tenant scoping (الجمعية ترى مبادراتها فقط).
4. **Filament Policies** على الـ Resources (تظهر/تخفي actions حسب الدور).

---

## 8. مسار سير العمل (Workflow / State Machine)

### 8.1 حالات المبادرة (Initiative States)
```
draft → submitted → excellence_review → excellence_approved
                          │ ↑
                          │ │ resubmit
                          ▼ │
                    needs_revision
                          │
                          └─ (يعود الجمعية → submitted)

excellence_approved → consultant_review → consultant_approved →
   donor_review → donor_approved → in_execution → closed

أي مرحلة → rejected (نهائي)
```

### 8.2 الانتقالات المسموحة (Transitions)

| من | إلى | الفاعل | الشرط |
|---|---|---|---|
| `draft` | `submitted` | الجمعية | اكتمال الأقسام 1-7 |
| `submitted` | `excellence_review` | تلقائي | فور الإرسال |
| `excellence_review` | `excellence_approved` | excellence_manager | اكتمال القسم 8 |
| `excellence_review` | `needs_revision` | excellence_manager | مع ملاحظات |
| `needs_revision` | `submitted` | الجمعية | بعد التعديل |
| `excellence_approved` | `consultant_review` | تلقائي | — |
| `consultant_review` | `consultant_approved` | consultant | بعد المراجعة |
| `consultant_approved` | `donor_review` | تلقائي | — |
| `donor_review` | `donor_approved` | donor_admin | اكتمال القسم 9 |
| `donor_approved` | `in_execution` | تلقائي | فور الاعتماد + Hooks |
| `in_execution` | `closed` | super_admin / excellence_manager | اكتمال المخرجات + التقرير النهائي |

### 8.3 الـ Hooks التلقائية عند `donor_approved`
```php
// في DonorApprovalListener
1. توليد PDF رسمي للنموذج كاملاً مع كل التواقيع.
2. جدولة الـ payment_requests للدفعات الـ5 (status='scheduled').
3. إنشاء visit_slots افتراضية للأشهر القادمة.
4. تفعيل تنبيهات الجمعية برفع الشواهد.
5. إنشاء صفحة المبادرة العامة (داخل لوحة الجمعية والمستشار).
6. إرسال إشعارات لكل الأطراف بالاعتماد.
7. تسجيل event في Activity Log.
```

### 8.4 حالات أخرى
- **Visit States:** `proposed → scheduled → conducted → reported`
- **Output Evidence States:** `pending → approved | rejected`
- **Monthly Report States:** `draft → submitted → approved`
- **Payment Request States:** `scheduled → eligible → requested → approved → paid`
- **Ticket States:** `open → assigned → answered → closed`

---

## 9. الموديولات الوظيفية

### 9.1 Module: Identity & Access
- المستخدمون / الأدوار / الصلاحيات
- المنظمات (الجمعيات / المانحة / الفريق / المستشارون)
- 2FA / Login / Password Reset
- Activity Log للمستخدمين

### 9.2 Module: Reference Data
- المجالات (3) / المعايير (11) / المؤشرات (45) / التخصصات (4)
- Seeders كاملة من PDF + Word
- إدارة من super_admin فقط

### 9.3 Module: Initiatives (النواة)
- Wizard 9 خطوات + auto-save
- State Machine + Transitions
- Policies على مستوى القسم
- PDF Export
- التواقيع الرقمية

### 9.4 Module: Execution & Monitoring
- المخرجات والشواهد
- الزيارات (slots / scheduling / reports)
- التقارير الشهرية
- اللقاءات عن بُعد

### 9.5 Module: Communication
- التذاكر + الردود
- نظام إسناد ذكي حسب الفئة والتخصص
- ربط التذاكر بالتقارير الشهرية

### 9.6 Module: Finance
- الدفعات الـ5 المرتبطة بالمخرجات
- استحقاق تلقائي عند اعتماد المخرجات
- طلبات الصرف + الموافقة + الدفع

### 9.7 Module: Evaluation
- تقييم الخدمة (5 نجوم)
- تقييم متبادل بين الأطراف
- مؤشرات أداء للمستشارين

### 9.8 Module: Notifications
- Database + Mail (+ SMS / WhatsApp قابل للتفعيل)
- تفضيلات لكل مستخدم
- Scheduled reminders + real-time

### 9.9 Module: Reporting & Analytics
- Dashboards حسب الدور
- Exports (Excel + PDF)
- KPI heatmaps
- التقارير الفنية الدورية (الكفاءة / الأداء / الفعالية / الأثر)

### 9.10 Module: System Administration
- إدارة الإعدادات العامة
- النسخ الاحتياطي
- سجل النشاط الكامل
- إدارة الصلاحيات الديناميكية

---

## 10. لوحات التحكم حسب الدور

### 10.1 لوحة `super_admin` (Admin Panel — `/admin`)

**الصفحات الرئيسية:**
1. **نظرة عامة:** عدد المستخدمين، الجمعيات، المبادرات، الإحصاءات الكلية، أحدث الأنشطة.
2. **إدارة المستخدمين:** CRUD كامل + تعيين أدوار + تفعيل 2FA.
3. **إدارة الأدوار والصلاحيات:** إنشاء/تعديل أدوار جديدة + تخصيص صلاحيات.
4. **إدارة المنظمات:** CRUD للجمعيات والمانحة والفريق الاستشاري.
5. **البيانات المرجعية:** إدارة المجالات/المعايير/المؤشرات/التخصصات.
6. **سجل النشاط:** بحث متقدم في كل الأنشطة.
7. **إعدادات النظام:** SMTP / SMS / Storage / Backups / Maintenance Mode.
8. **النسخ الاحتياطي:** تفعيل/جدولة/تحميل.
9. **لوحة الإشعارات:** إدارة قوالب الإشعارات.

**Widgets:**
- إحصاءات سريعة (Counters)
- مخطط زمني للمبادرات
- خريطة حرارية للنشاط
- آخر 10 تنبيهات
- المستخدمون الأكثر نشاطاً

### 10.2 لوحة `excellence_manager` (Excellence Panel)

**الصفحات الرئيسية:**
1. **صف المبادرات:** Inbox للمبادرات بحالة `excellence_review`.
2. **كل المبادرات:** عرض مع فلاتر متقدمة.
3. **اعتماد القسم 8:** صفحة مخصصة للاعتماد.
4. **التقارير الفنية الدورية:**
   - تقرير الكفاءة والأداء
   - تقرير الفعالية والأثر
5. **إدارة المستشارين:** تخصيص المبادرات للمستشارين.
6. **التواصل:** عرض كل التذاكر والاتصال بين الأطراف.
7. **التقارير الشهرية:** كل التقارير من كل الجمعيات.

**Widgets:**
- مبادرات تنتظر الاعتماد
- مؤشر التقدم العام (شامل 13 جمعية)
- مقارنة أداء الجمعيات
- المهام المتأخرة

### 10.3 لوحة `donor_admin` (Donor Panel)

**الصفحات الرئيسية:**
1. **نظرة عامة على الأثر:** مؤشرات مجمّعة عبر 13 جمعية.
2. **صف الاعتماد النهائي:** المبادرات في `donor_review`.
3. **متابعة الأداء:** خريطة حرارية للـ 45 KPI عبر الجمعيات.
4. **التقارير الشهرية:** عرض + اعتماد.
5. **الدفعات:** الموافقة على طلبات الصرف.
6. **التقييم النهائي:** المبادرات المغلقة + التحليل.

**Widgets:**
- إجمالي المبالغ المعتمدة / المصروفة / المتبقية
- تطور KPI زمنياً
- أعلى وأدنى الجمعيات أداءً
- نسبة إنجاز الخطط

### 10.4 لوحة `consultant` (Consultant Panel)

**الصفحات الرئيسية:**
1. **مبادراتي:** المبادرات المسندة إليّ.
2. **جدول زياراتي:** Calendar (FullCalendar).
3. **اقتراح أوقات الزيارات.**
4. **تقارير الزيارة:** كتابة + رفع الشواهد.
5. **التقارير الشهرية:** كتابة + إرسال.
6. **التذاكر:** الموجّهة إليّ.
7. **مراجعة المخرجات:** قائمة الشواهد للمراجعة.

**Widgets:**
- زيارة اليوم/الأسبوع
- تذاكر مفتوحة
- مخرجات تنتظر المراجعة
- موعد التقرير الشهري القادم

### 10.5 لوحة `association_manager` (Association Panel)

**الصفحات الرئيسية:**
1. **مبادراتي:** قائمة + إنشاء جديدة.
2. **نموذج المبادرة (Wizard):** 9 خطوات.
3. **المخرجات والشواهد:** رفع + متابعة الموافقات.
4. **الزيارات:** اختيار من الأوقات المقترحة.
5. **الدفعات:** طلب صرف + متابعة.
6. **التذاكر:** رفع + متابعة الردود.
7. **التقارير الواردة:** اطّلاع.
8. **تقييم الخدمة:** تقييم المستشارين والفريق.

**Widgets:**
- تنبيهات معلّقة (شواهد للرفع، دفعات قادمة، زيارات قادمة)
- نسبة إنجاز الخطة
- آخر ملاحظات المستشار
- تذاكر مفتوحة

---

## 11. نظام الإشعارات والتنبيهات

### 11.1 القنوات المدعومة
- ✅ **Database** (داخل المنصة — bell icon)
- ✅ **Mail** (SMTP)
- 🔵 **SMS** (قابل للتفعيل عبر Twilio أو غيره)
- 🔵 **WhatsApp Business** (قابل للتفعيل)
- 🔵 **Push Notifications** (مستقبلاً للموبايل)

### 11.2 أنواع الإشعارات

| النوع | المستهدف | الترقية | المنبه |
|---|---|---|---|
| `InitiativeSubmitted` | excellence_team | فوري | الجمعية ترسل |
| `InitiativeNeedsRevision` | الجمعية | فوري | excellence يرفض |
| `InitiativeApproved` | كل الأطراف | فوري | donor يعتمد |
| `EvidenceUploaded` | المستشار المسند | فوري | الجمعية ترفع |
| `EvidenceReviewed` | الجمعية | فوري | المستشار يقيّم |
| `VisitSlotProposed` | الجمعية | فوري | المستشار يقترح |
| `VisitSlotChosen` | المستشار | فوري | الجمعية تختار |
| `VisitReminderD-7` | الطرفين | مجدول | قبل 7 أيام |
| `VisitReminderD-1` | الطرفين | مجدول | قبل يوم |
| `MonthlyReportDue` | المستشار | شهري | بداية الشهر |
| `MonthlyReportSubmitted` | المانحة | فوري | المستشار يرسل |
| `PaymentDueSoon` | الجمعية | مجدول | قبل أسبوع |
| `PaymentRequestSubmitted` | المانحة | فوري | الجمعية تطلب |
| `PaymentApproved` | الجمعية | فوري | المانحة توافق |
| `TicketRaised` | المسند | فوري | الجمعية ترفع |
| `TicketAnswered` | الجمعية | فوري | المسند يرد |
| `TicketEscalated` | excellence | شرطي | بعد 48س بدون رد |
| `EvaluationRequested` | الكل | بعد كل خدمة | تلقائي |

### 11.3 آلية العمل
```php
// عند حدث:
event(new EvidenceUploaded($evidence));

// Listener:
class NotifyConsultantListener {
  public function handle(EvidenceUploaded $event) {
    $consultant = $event->evidence->initiative->assignedConsultant;
    $consultant->notify(new EvidenceUploadedNotification($event->evidence));
  }
}

// Notification:
class EvidenceUploadedNotification extends Notification implements ShouldQueue {
  public function via($notifiable): array {
    return $notifiable->notification_preferences->channels_for($this);
  }
  public function toMail($notifiable): MailMessage { ... }
  public function toDatabase($notifiable): array { ... }
}
```

### 11.4 المهام المجدولة (Scheduled Reminders)
في `app/Console/Kernel.php`:
```php
$schedule->job(new RemindUpcomingVisits)->daily();
$schedule->job(new RemindOverdueOutputs)->daily();
$schedule->job(new RemindUpcomingPayments)->weekly();
$schedule->job(new RemindMonthlyReportDue)->monthly();
$schedule->job(new RemindOpenTickets)->dailyAt('09:00');
$schedule->job(new GenerateAutoEvaluations)->daily();
```

### 11.5 تفضيلات المستخدم
يمكن للمستخدم من إعدادات حسابه:
- تشغيل/إيقاف كل نوع إشعار
- اختيار القنوات لكل نوع
- ساعات «عدم الإزعاج»

---

## 12. نظام الفلترة والبحث

### 12.1 الفلاتر المعتمدة في كل القوائم
استخدام `spatie/laravel-query-builder` لتوحيد API للفلاتر.

#### فلاتر المبادرات
- بالحالة (draft / approved / in_execution / closed / ...)
- بالجمعية (multi-select)
- بالمجال (الأثر التنموي / الاستدامة / التمكين)
- بنطاق التاريخ (submitted_at, approved_at)
- بالمستشار المسند
- بمدير مسار الإجادة
- بالميزانية (range)
- بنسبة الإنجاز
- بالكلمات المفتاحية (Scout / MeiliSearch)

#### فلاتر الجمعيات
- بالمنطقة
- بالحالة (نشطة / متوقفة)
- بعدد المبادرات
- بنسبة الأداء

#### فلاتر التقارير الشهرية
- بالشهر/السنة
- بالجمعية
- بالحالة (draft / submitted / approved)
- بالمستشار

#### فلاتر التذاكر
- بالحالة
- بالأولوية
- بالفئة
- بالمسند
- بنطاق التاريخ

#### فلاتر الدفعات
- بالحالة
- بنطاق التاريخ المستحق
- بالمبلغ (range)

#### فلاتر الزيارات
- بالنوع (حضوري/عن بعد)
- بالحالة
- بالمستشار/الجمعية
- بالشهر

### 12.2 البحث الموحَّد (Global Search)
عبر **Laravel Scout + MeiliSearch** (أو Algolia):
- بحث في: عنوان المبادرة، اسم المخرج، نص التذكرة، التقرير الشهري، الجمعية.
- بحث instantaneous من شريط البحث في كل لوحة Filament.

### 12.3 Saved Filters
كل مستخدم يمكنه:
- حفظ فلتر بمسمى
- مشاركة فلتر مع زملاء بنفس الدور
- جعل فلتر افتراضياً

---

## 13. إدارة النظام (Admin Control)

### 13.1 إدارة المستخدمين
- CRUD كامل
- بحث متقدم
- تفعيل/تعطيل حساب
- إعادة تعيين كلمة مرور
- تفعيل 2FA إجباري لأدوار محددة
- Impersonation (تسجيل دخول بصلاحية مستخدم لاختبار) — مع لوغ كامل

### 13.2 إدارة الأدوار والصلاحيات
- إنشاء أدوار جديدة
- تعديل صلاحيات الأدوار الموجودة
- تخصيص صلاحيات لكل مستخدم بشكل فردي
- نسخ صلاحيات من دور لآخر

### 13.3 إدارة المنظمات
- إنشاء جمعيات / مانحة / فريق استشاري
- ربط المستخدمين بالمنظمات
- تعطيل / إعادة تفعيل

### 13.4 إعدادات النظام (Key-Value Settings)
- معلومات المنصة العامة (الاسم / الشعار / الألوان)
- إعدادات SMTP
- إعدادات SMS
- إعدادات Storage (Local / S3)
- ضريبة القيمة المضافة (15%)
- مسارات النماذج
- اللغة الافتراضية

### 13.5 إدارة البيانات المرجعية
- المجالات / المعايير / المؤشرات
- التخصصات
- إمكانية إضافة مؤشرات مخصصة

### 13.6 النسخ الاحتياطي والاسترجاع
- نسخ مجدول يومي / أسبوعي
- نسخ إلى S3 / FTP
- تنبيه عند فشل النسخ
- استرجاع من واجهة (مع تأكيد مزدوج)

### 13.7 سجل النشاط (Activity Log)
- بحث في كل الأنشطة
- فلترة بالمستخدم / النموذج / الإجراء / التاريخ
- تصدير Excel
- تنبيهات عند أنشطة حساسة (تعديل صلاحيات، حذف بيانات)

### 13.8 وضع الصيانة (Maintenance Mode)
- تفعيل من اللوحة
- رسالة مخصصة
- استثناءات IP

---

## 14. هيكل المجلدات

```
makeen-platform/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   └── Kernel.php
│   ├── Domain/                  ← Modules
│   │   ├── Identity/
│   │   │   ├── Models/         (User, Role, Permission)
│   │   │   ├── Actions/
│   │   │   └── Events/
│   │   ├── Reference/
│   │   │   └── Models/         (Area, Standard, Kpi, Specialty)
│   │   ├── Initiatives/
│   │   │   ├── Models/         (Initiative, BasicInfo, Output, ...)
│   │   │   ├── States/         (DraftState, ExcellenceReviewState, ...)
│   │   │   ├── Actions/        (SubmitInitiative, ApproveInitiative, ...)
│   │   │   ├── Events/
│   │   │   ├── Listeners/
│   │   │   └── Policies/
│   │   ├── Execution/
│   │   │   └── Models/         (OutputEvidence, Visit, MonthlyReport, ...)
│   │   ├── Communication/
│   │   │   └── Models/         (Ticket, TicketReply, Meeting, ...)
│   │   ├── Finance/
│   │   │   └── Models/         (Payment, PaymentRequest)
│   │   ├── Evaluation/
│   │   │   └── Models/         (ServiceEvaluation, Approval)
│   │   └── Notifications/
│   │       ├── Notifications/  (كل أنواع الإشعارات)
│   │       └── Channels/
│   ├── Filament/
│   │   ├── Admin/              ← Panel super_admin
│   │   │   ├── Resources/
│   │   │   ├── Pages/
│   │   │   └── Widgets/
│   │   ├── Excellence/         ← Panel excellence
│   │   ├── Donor/              ← Panel donor
│   │   ├── Consultant/         ← Panel consultant
│   │   └── Association/        ← Panel association
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/                  ← Eloquent models (re-export from Domain)
│   ├── Policies/
│   ├── Providers/
│   └── Support/                 ← Helpers
├── bootstrap/
├── config/
│   ├── makeen.php              ← إعدادات المنصة
│   ├── filament.php
│   └── permission.php
├── database/
│   ├── factories/
│   ├── migrations/             ← 35+ migration
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── AreasSeeder.php     (3 مجالات)
│       ├── StandardsSeeder.php (11 معياراً)
│       ├── KpisSeeder.php      (45 مؤشراً)
│       ├── SpecialtiesSeeder.php (4 تخصصات)
│       ├── RolesSeeder.php     (6 أدوار + صلاحيات)
│       ├── OrganizationsSeeder.php (13 جمعية + المانحة + الفريق)
│       └── DemoUsersSeeder.php
├── lang/
│   ├── ar/                     ← الافتراضية
│   └── en/
├── public/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── storage/
├── tests/
│   ├── Feature/
│   │   ├── InitiativeWorkflowTest.php
│   │   ├── PermissionsTest.php
│   │   └── NotificationsTest.php
│   ├── Unit/
│   └── Pest.php
├── .env.example
├── composer.json
├── package.json
├── pint.json
├── phpunit.xml
└── README.md
```

---

## 15. التثبيت والتشغيل

### 15.1 المتطلبات
- PHP 8.2+
- Composer 2.x
- Node 20+ / NPM
- MySQL 8.0+ أو MariaDB 10.6+
- Redis 7.x
- (اختياري) MeiliSearch للبحث المتقدم

### 15.2 خطوات التثبيت

```bash
# استنساخ المستودع
git clone https://github.com/RaafatAraby/makeen-platform.git
cd makeen-platform

# تثبيت الحزم
composer install
npm install

# إعداد البيئة
cp .env.example .env
php artisan key:generate

# تعديل .env بقاعدة البيانات الصحيحة
# DB_DATABASE=makeen
# DB_USERNAME=...
# DB_PASSWORD=...
# REDIS_HOST=127.0.0.1
# MAIL_MAILER=smtp ...

# تشغيل migrations + seeders
php artisan migrate --seed

# إنشاء storage link
php artisan storage:link

# بناء assets
npm run build

# تشغيل الـ queue
php artisan horizon

# تشغيل الـ scheduler (في cron)
* * * * * cd /path-to-makeen && php artisan schedule:run >> /dev/null 2>&1

# تشغيل التطبيق (محلياً)
php artisan serve
```

### 15.3 المستخدمون التجريبيون (بعد seeding)
| الدور | البريد | كلمة المرور |
|---|---|---|
| super_admin | admin@makeen.test | password |
| excellence | excellence@makeen.test | password |
| donor | donor@makeen.test | password |
| consultant | consultant@makeen.test | password |
| association | jamiya1@makeen.test | password |

### 15.4 الوصول للوحات
- `/admin` — لوحة الإدارة
- `/excellence` — لوحة مسار الإجادة
- `/donor` — لوحة المانحة
- `/consultant` — لوحة المستشار
- `/association` — لوحة الجمعية
- `/horizon` — مراقبة الـ queues (super_admin فقط)

---

## 16. الأمان والحوكمة

### 16.1 الحماية المطبَّقة
- ✅ **CSRF Protection** على كل الـ forms.
- ✅ **XSS Prevention** عبر Blade escaping + CSP headers.
- ✅ **SQL Injection** ممنوع عبر Eloquent.
- ✅ **Mass Assignment** محمي بـ `$fillable` / `$guarded`.
- ✅ **Rate Limiting** على login و API.
- ✅ **2FA** إجباري للأدوار الحساسة (donor, excellence, super_admin).
- ✅ **Encrypted Sensitive Fields** (signatures, secrets).
- ✅ **HTTPS-only** في الإنتاج.
- ✅ **Session Hijacking Prevention** عبر regenerate_on_login.

### 16.2 الحوكمة
- 📜 **Activity Log** كامل لكل الأنشطة الحساسة.
- 📜 **Audit Trail** للمبادرات (من غيّر ماذا متى).
- 📜 **IP Logging** على كل عملية اعتماد.
- 📜 **Soft Delete** + استرجاع لكل البيانات الحساسة.
- 📜 **Backup يومي** + Verification.

### 16.3 الامتثال
- متوافق مع **PDPL السعودي** (حماية البيانات الشخصية).
- بيانات الجمعيات المستفيدين مشفّرة عند الحاجة.

---

## 17. استراتيجية الاختبار

### 17.1 أنواع الاختبارات
- **Unit Tests:** للـ Actions والـ States.
- **Feature Tests:** للـ Workflows الكاملة (Pest).
- **Browser Tests:** للـ Filament panels (Dusk).
- **API Tests:** للنقاط النهائية المستقبلية.

### 17.2 السيناريوهات الحرجة
1. **InitiativeWorkflowTest:** المبادرة تمر بكل المراحل من draft → closed.
2. **PermissionsTest:** كل دور يستطيع/لا يستطيع الوصول للـ resources.
3. **NotificationsTest:** كل event يولّد الإشعارات الصحيحة.
4. **PaymentEligibilityTest:** الدفعة تصبح eligible عند اكتمال مخرجاتها.
5. **TenantScopingTest:** الجمعية ترى مبادراتها فقط.

### 17.3 التغطية المستهدفة
- **80%+** على الـ Domain layer.
- **70%+** على الـ Application layer.
- **اختبار ذكي** للـ Presentation (لا داعي لتغطية 100%).

```bash
# تشغيل الاختبارات
php artisan test
php artisan test --coverage --min=80

# Static analysis
./vendor/bin/phpstan analyse

# Code style
./vendor/bin/pint
```

---

## 18. خارطة طريق التطوير

### Sprint 0 — الأساس (3-4 أيام)
- [x] تحليل الملفات (Word + PDF + الصورة)
- [x] الموافقة على الهيكلية
- [ ] إعداد Laravel 11 + Filament 3
- [ ] حزم Spatie + Auth + DB

### Sprint 1 — البيانات المرجعية (2 أيام)
- [ ] Migrations: areas, standards, kpis, specialties
- [ ] Seeders: 3 مجالات + 11 معيار + 45 KPI + 4 تخصصات
- [ ] Seeders: 13 جمعية + 6 أدوار + الصلاحيات

### Sprint 2 — نموذج المبادرة (7-10 أيام)
- [ ] Migrations لكل أقسام المبادرة (10 جداول)
- [ ] Models + Relationships
- [ ] State Machine
- [ ] Wizard 9 steps في Filament
- [ ] Auto-save
- [ ] Form Requests + Policies

### Sprint 3 — Workflow + Approvals (4-5 أيام)
- [ ] State transitions
- [ ] Approval pages (sections 8 & 9)
- [ ] Signatures
- [ ] PDF Export
- [ ] Hooks بعد donor_approved

### Sprint 4 — اللوحات الـ4 (7-10 أيام)
- [ ] Filament Panels setup × 4
- [ ] Resources لكل لوحة
- [ ] Dashboards + Widgets
- [ ] Navigation + Permissions

### Sprint 5 — التنفيذ والمتابعة (7-10 أيام)
- [ ] Output evidences
- [ ] Visit slots + scheduling + reports
- [ ] Monthly reports
- [ ] Meetings

### Sprint 6 — التواصل والمالية (4-5 أيام)
- [ ] Tickets + replies + escalation
- [ ] Payment requests + approvals
- [ ] Service evaluations

### Sprint 7 — الإشعارات (3-4 أيام)
- [ ] كل أنواع الإشعارات
- [ ] Mail templates
- [ ] Scheduled reminders
- [ ] User preferences

### Sprint 8 — التقارير والـExports (4-5 أيام)
- [ ] التقارير الفنية الدورية
- [ ] KPI heatmaps
- [ ] Excel + PDF exports
- [ ] Saved filters

### Sprint 9 — الاختبارات والتشطيب (3-5 أيام)
- [ ] كتابة Tests
- [ ] CI/CD
- [ ] Documentation
- [ ] User Guides

### Sprint 10 — UAT والنشر (3-5 أيام)
- [ ] User Acceptance Testing
- [ ] Performance optimization
- [ ] Production deployment
- [ ] Monitoring setup

**الإجمالي المتوقع:** 45-65 يوم عمل لـ MVP صلب وقابل للنشر.

---

## 19. النشر (Deployment)

### 19.1 البنية الموصى بها
```
┌──────────────┐    ┌──────────────┐
│  Cloudflare  │───▶│  Load        │
│  (CDN + WAF) │    │  Balancer    │
└──────────────┘    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
        ┌─────────┐  ┌─────────┐  ┌─────────┐
        │ App-1   │  │ App-2   │  │ Worker  │
        │ Nginx + │  │ Nginx + │  │ Horizon │
        │ PHP-FPM │  │ PHP-FPM │  │         │
        └────┬────┘  └────┬────┘  └────┬────┘
             │            │            │
             └────────────┼────────────┘
                          ▼
              ┌──────────────────────┐
              │   MySQL Master/Slave │
              │   Redis              │
              │   S3 (Storage)       │
              └──────────────────────┘
```

### 19.2 منصات النشر المقترحة
- **Laravel Forge + DigitalOcean** (أسهل + كافٍ).
- **AWS (EC2 + RDS + ElastiCache + S3)** للحجم الأكبر.
- **VPS بسيط** للبداية: 4 cores / 8GB RAM / 80GB SSD.

### 19.3 CI/CD
- **GitHub Actions** للاختبار التلقائي.
- **Deployer** للنشر بدون انقطاع.

### 19.4 المراقبة
- **Sentry** للأخطاء.
- **Telescope** لـ debug في staging.
- **Horizon Dashboard** للـ queues.
- **Laravel Pulse** للأداء.

---

## 📞 الدعم والتواصل

- **المالك:** RaafatAraby
- **الخادم:** Laravel + Filament
- **اللغة الأساسية:** العربية (RTL)
- **الترخيص:** خاص (Proprietary)

---

> **هذا المشروع مستوحى من منهجية «مكين» للتمكين المؤسسي للمنظمات غير الربحية، ويهدف إلى أتمتة وتوحيد عمليات التحسين والتقييم لمدة 32 شهراً عبر 13 جمعية مشاركة و4 أطراف رئيسية.**
