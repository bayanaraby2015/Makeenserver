# تحليل ملفات منصة مكين — مطابقة المتطلبات وبناء الهيكلية في Laravel
**Repo:** [RaafatAraby/makeen-platform](https://github.com/RaafatAraby/makeen-platform) (فارغ حالياً — تم استنساخه ولا يحتوي على أي كود/Branches)
**التاريخ:** 2026-05-05
**الغرض:** تقرير قبل-البناء (Pre-implementation Analysis) ليكون أرضية للنقاش معك على الهيكلية والمنهجية النهائية قبل كتابة أول سطر كود.

---

## 0. ملخص تنفيذي (TL;DR)

- الملفات الأربعة (DOCX، PDF، الصورة، الـ SVG) **متسقة تماماً** مع `makeen-README.md` ولا يوجد بينها تناقض جوهري — README هو التوثيق الأكثر تفصيلاً ويغطي كل ما ورد في الملفات الأخرى ويزيد عليها (35 جدول DB، 6 أدوار، State Machine، 4 لوحات Filament، إلخ).
- الفهم الذي طرحته في رسالتك بأن **«كل جهة لها مدخلات خاصة بها للنموذج تختلف عن جهة أخرى»** يحتاج تصحيحاً مهماً قبل البدء في الكود — لأنه يوجِّه القرار المعماري في اتجاه خاطئ:
  - النموذج **واحد قانونياً** (نموذج بطاقة المبادرة الرسمي — 9 أقسام).
  - الاختلاف ليس في **بنية الحقول** بين الجهات، بل في **مالك القسم** (من يحرر، من يعتمد) و**توقيت الكتابة** (في أي حالة State).
  - أي: الجمعية تكتب الأقسام 1–7، المستشار يراجع، الشريك الاستشاري يكتب القسم 8، المؤسسة المانحة تكتب القسم 9 — كل ذلك على **سجل initiative واحد** في نفس الجداول.
  - الجمعيات الـ13 تختلف فقط في **بياناتها** (Tenant data) لا في **مخطط النموذج** (Schema).
- بناءً على ذلك، **لا يُنصح** بحلول EAV أو JSON-Schema-per-tenant أو نماذج ديناميكية، بل بالحل المعماري المُوصى به في README:
  **Single canonical schema + Multi-Step Wizard + State Machine + Section-level Policies + Tenant Scope on Association**.
- المقترح يطابق README كلياً مع ملاحظات تحسين بسيطة (مذكورة في القسم 7 و8).

---

## 1. ما تم تحليله

| الملف | المحتوى المختصر | الحجم |
|---|---|---|
| `makeen-README.md` | توثيق هندسي شامل (1297 سطر) — 19 قسماً يغطي كل شيء من Stack إلى Deployment | 56 KB |
| `نموذج بطاقة المبادرة.docx` | النموذج الرسمي من 9 أقسام مع الجداول والحقول (مصدر "حقيقة" للحقول) | 522 KB |
| `وصف نظام مكين (1).pdf` | تفصيل العمليات والصلاحيات لكل دور (الجمعية/المستشار/المانحة/الإدارة) | 356 KB |
| `WhatsApp Image 12.18.07 PM.jpeg` (Mind Map) | خريطة ذهنية لمراحل مشروع مكين الأربعة + الأطراف ذات العلاقة + المستشارون الأربعة | 212 KB |
| `makan_full_system_roles_workflow.svg` | مخطط Workflow كامل لـ3 مراحل تشغيلية + جدول صلاحيات + قائمة إشعارات + التقنيات | 85 KB |
| ريبو `RaafatAraby/makeen-platform` | **فارغ** — لا README ولا commit | — |

---

## 2. مطابقة الملفات بعضها ببعض

### 2.1 ما تتفق عليه كل المصادر
| العنصر | DOCX | PDF | Mind Map | SVG | README |
|---|:-:|:-:|:-:|:-:|:-:|
| الأطراف الأربعة (الجمعية/المستشار/الإجادة/المانحة) | ✓ | ✓ | ✓ | ✓ | ✓ |
| 9 أقسام للنموذج | ✓ (تفصيلياً) | ✓ (إشارة) | — | — | ✓ |
| 13 جمعية مشاركة | — | — | ✓ | — | ✓ |
| 4 تخصصات للمستشارين (مالي/أوقاف/تخطيط/أثر) | — | — | ✓ | — | ✓ |
| 3 مجالات + 11 معيار + 45 KPI | ✓ (KPIs مذكورة كاملة في القسم 6) | — | ✓ (المجالات) | — | ✓ |
| 4 مراحل لمشروع مكين الكلي | — | — | ✓ | — | ✓ |
| Workflow الاعتماد متعدد المراحل | — | ✓ | — | ✓ | ✓ |
| الدورة الشهرية (Visits + Monthly Report) | — | ✓ | — | ✓ | ✓ |
| 5 دفعات مالية مرتبطة بالمخرجات | ✓ (القسم 5) | ✓ | — | ✓ | ✓ |
| التذاكر (Tickets) + التوجيه | — | ✓ | — | ✓ | ✓ |
| تقييم الخدمة | — | ✓ | — | ✓ | ✓ |
| Notifications (5 أنواع رئيسية) | — | ✓ (القائمة) | — | ✓ | ✓ |
| المدة 32 شهر | — | — | ✓ | — | ✓ |

### 2.2 ما يضيفه README (ولا يوجد في باقي الملفات صراحة)
- **State Machine بـ11 حالة** للمبادرة + Hooks تلقائية بعد الاعتماد النهائي.
- **35 جدول DB** بتفصيل الأعمدة.
- **مصفوفة الصلاحيات الكاملة** (~30 صلاحية × 6 أدوار).
- **6 أدوار** (تفصّل أكثر من 4 «أطراف» في الـSVG).
- **4 لوحات Filament منفصلة** + Widgets.
- **خارطة طريق عبر 11 Sprint**.
- **استراتيجية اختبار + أمن + نشر**.
- **اعتماد التواقيع الرقمية** + **Activity Log** + **Audit Trail**.

### 2.3 ما يضيفه DOCX (ولا يفصّله README)
- **القيم/الـenums الفعلية** لبعض الحقول (مثلاً: «مرتبطة | غير مرتبطة»، «مكتمل | غير مكتمل»، «مناسبة | غير مناسبة» في القسم 8).
- **النصوص العربية الرسمية** للـ45 KPI (يجب نسخها بالحرف للسيدر — تحققت منها وموجودة في DOCX).
- **القسم 4 المعقد** (المخطط الزمني والمالي) فيه 12 عمود لأشهر التنفيذ — أي أن `initiative_timeline_months` في README صحيح كحل لجدول M:N بين العنصر والشهر.

### 2.4 ما يضيفه PDF (ولا يفصّله README)
- **توجيه التذكرة** له مساران رسميان: «للمدير ثم يحدد المختص» أو «للمختص مباشرة» — وفي README يوجد عمود `routing` بالقيم `to_manager|to_specialist|to_consultant` ✓ مطابق.
- **«تعديل ملاحظات المستشار وإعادة الرفع»** كصلاحية للجمعية — وفي README يوجد State `needs_revision` ✓.

### 2.5 لا تناقضات جوهرية اكتُشفت
كل الملفات متسقة. README هو الأشمل.

---

## 3. تصحيح فهم سؤال الطرح

### 3.1 سؤالك الأصلي
> «كل جهة لها مدخلات خاصة بها للنموذج تختلف عن جهة أخرى — شو افضل طريقة للتعامل مع النموذج في لارافل…»

### 3.2 ما يكشفه التحليل الفعلي للنموذج
بقراءة DOCX قسماً قسماً:

| القسم | المالك (من يحرر) | المراجع/المعتمد | الحقول |
|---|---|---|---|
| 1 — بيانات الجمعية | الجمعية | — | اسم/مدير/تواصل/إيميل |
| 2 — البطاقة التعريفية | الجمعية | المستشار | اسم المشروع، المجال (multi-select من 3)، المعايير، المبررات، الأهداف، النطاق البشري/المالي، المدة |
| 3 — المخرجات | الجمعية | المستشار | جدول 1:N (مرحلة، نشاط، مخرج، كمية، وصف) |
| 4 — المخطط الزمني والمالي | الجمعية | المستشار | جدول 1:N + 12 عمود شهر (أيها مفعّل) + التكلفة الفردية والإجمالية + VAT 15% |
| 5 — الدفعات | الجمعية | المانحة (لاحقاً) | حتى 5 دفعات (نسبة، قيمة، تاريخ، مخرجات مرتبطة) |
| 6 — مؤشرات الأداء | الجمعية | — | 45 سطر (KPI، خط الأساس، المستهدف) |
| 7 — إدارة المخاطر | الجمعية | المستشار | جدول 1:N (الخطر، الاحتمال، الأثر، الإجراء) |
| 8 — اعتماد الشريك الاستشاري | الفريق الاستشاري (مسار الإجادة) | — | 3 enums + ملاحظات + توقيع + تاريخ |
| 9 — اعتماد المؤسسة المانحة | المانحة | — | ملاحظات + توقيع + تاريخ |

> **النتيجة:** النموذج بنية واحدة موحدة (Single Canonical Form). الاختلاف ليس في «حقول مختلفة لكل جهة» بل في **«ملكية القسم»** و**«حالة المبادرة عند تحريره»**.

### 3.3 لماذا هذا التمييز مهم تقنياً؟

| الفهم | الحل التقني المضلل | المشاكل |
|---|---|---|
| ❌ «كل جهة لها حقول مختلفة» | EAV (`form_definitions` + `form_fields` + `field_values`) أو JSON Schema ديناميكي per tenant | – استعلامات Reporting كابوس<br>– فقدان Type Safety<br>– صعوبة الـ Validation<br>– صعوبة بناء PDF رسمي ثابت<br>– يصعب اعتماد KPI موحدة |
| ✅ «نموذج واحد بأقسام مختلفة المالكين» | Migrations ثابتة (35 جدول) + State Machine + Section Policies + Tenant Scope | – استعلامات قياسية<br>– Type-safe Eloquent<br>– PDF موحد<br>– KPIs مقارنة بين الجمعيات |

> ✅ **التوصية:** اعتماد الحل الثاني (الذي يطابق README بالضبط).

### 3.4 متى يكون هناك «اختلاف بين الجهات» فعلياً؟
- **بيانات وليس مخطط:** كل جمعية ترى مبادراتها الخاصة فقط (Tenant Scope).
- **صلاحيات:** كل دور يرى أزراراً وحقولاً مختلفة بحسب State + Role + Section Ownership.
- **لوحات Filament:** كل جهة لها Panel منفصل (`/association`، `/consultant`، إلخ).
- **مكونات نادرة من اختلاف فعلي:**
  - **بيانات استشاري إضافية** (`consultants` table فيه `specialty`, `bio`, `cv_path` لا توجد للجمعية).
  - **بيانات منظمة** (`organizations.type` مع 4 قيم: association/donor/excellence_team/consultant_firm) → يُستحسن اعتماد **STI خفيف** أو **separate profile tables** (موجود في README).

---

## 4. الخيارات المعمارية الممكنة (مقارنة)

### الخيار أ — Single Canonical Schema (المنصوح به ✅)
**كما في README بالضبط.**
- 35 جدول ثابت Schema، تتعامل مع كل أقسام النموذج بجداول علاقات (1:1، 1:N، M:N).
- State Machine بـ11 حالة + Section Policies.
- Tenant Scope على `organization_id` للجمعيات.
- Wizard 9 خطوات في Filament 3 + auto-save.

**مميزات:**
- مطابق 100% للنموذج الرسمي.
- استعلامات BI بسيطة.
- Type Safety كامل (Eloquent + casts).
- PDF Export موحد.
- توسعة سهلة (إضافة حقل = migration واحد).

**عيوب:**
- إذا تطلبت الجمعيات لاحقاً «حقول مخصصة لكل جمعية» نحتاج طبقة إضافية (`extra_attributes JSON` موجود في README — `initiative_basic_info.extra_attributes` و `initiatives.metadata`، وهذا حل وسط مقبول).

### الخيار ب — JSON-First Schema (Single table + JSON columns)
- جدول `initiatives` واحد فيه عمود `data JSON` يحوي كل الأقسام الـ9.
- استخدام Laravel JSON casts.

**مميزات:**
- مرونة عالية في تغيير الحقول.
- أقل migrations.

**عيوب:**
- استعلامات KPI/Reporting صعبة جداً.
- لا توجد Foreign Keys حقيقية (مثل ربط `kpi_values` بـ`kpis.id`).
- صعوبة Indexing.
- صعوبة Validation الصارم.
- Audit log غير دقيق.
- ❌ **لا يُنصح به** للنطاق المؤسسي والكميات التشغيلية (32 شهراً + 13 جمعية + 45 KPI).

### الخيار ج — EAV (Entity-Attribute-Value)
- جداول `form_definitions`, `form_fields`, `field_values`.

**مميزات:**
- مرونة قصوى (يمكن إنشاء حقول من اللوحة).

**عيوب:**
- استعلامات Reporting بطيئة وكابوسية.
- فقدان Type Safety كاملاً.
- صعوبة Validation.
- صعوبة الـ Filament Forms (يحتاج بناء Form Builder ديناميكي).
- ❌ **لا يُنصح به** إلا لو كان مطلوباً «إنشاء نماذج ديناميكية من قبل المستخدم» — وهذا ليس متطلباً هنا.

### الخيار د — Hybrid (canonical core + JSON metadata)
- نفس الخيار أ لكن مع `metadata JSON` و `extra_attributes JSON` لاستيعاب التوسع المستقبلي.

✅ **هذا فعلياً ما يقترحه README** (`initiatives.metadata`, `initiative_basic_info.extra_attributes`, `payment_requests` مع `linked_outputs JSON`، إلخ).
**هذا هو الاختيار النهائي الموصى به.**

---

## 5. المنهجية المقترحة لـ«النموذج الواحد لجهات متعددة»

### 5.1 المبدأ المعماري
```
┌─────────────────────────────────────────────────────────────┐
│  Canonical Initiative (نموذج واحد على مستوى DB)             │
│  + 1 سجل في `initiatives`                                   │
│  + سجل في `initiative_basic_info` (1:1)                     │
│  + سجلات في `initiative_outputs` (1:N)                      │
│  + سجلات في `initiative_timeline_items` (1:N)               │
│  + سجلات في `initiative_kpi_values` (1:N — 45 سطر)         │
│  + سجلات في `initiative_risks` (1:N)                        │
│  + سجل في `initiative_consultant_approval` (1:1)            │
│  + سجل في `initiative_donor_approval` (1:1)                 │
└─────────────────────────────────────────────────────────────┘
              │
              │  مَن يحرر ماذا؟
              ▼
┌─────────────────────────────────────────────────────────────┐
│  3 طبقات تحكم متراكبة:                                      │
│                                                             │
│  1) Spatie Permission                                       │
│     → الدور لديه الإذن الأساسي؟ (مثلاً approve_donor_final) │
│                                                             │
│  2) Section-Level Policy (InitiativePolicy)                 │
│     → الدور + الحالة الحالية + ملكية القسم                 │
│     مثال: editSection8 = role==excellence && state==review  │
│                                                             │
│  3) Tenant Global Scope                                     │
│     → الجمعية ترى مبادراتها فقط                            │
│     auth()->user()->organization_id == initiative.org_id    │
└─────────────────────────────────────────────────────────────┘
```

### 5.2 آلية «Wizard متعدد الجهات» (Multi-Actor Wizard)
**فكرة جوهرية:** الـ Wizard ليس linear UI واحد، بل **Wizard موزع زمنياً على عدة مستخدمين** بحسب State Machine:

| الخطوة | عند الحالة | يظهر لـ |
|---|---|---|
| 1 — بيانات الجمعية | `draft` فقط | association_manager (read-only لاحقاً) |
| 2 — البطاقة التعريفية | `draft, needs_revision` | association_manager |
| 3 — المخرجات | `draft, needs_revision` | association_manager |
| 4 — المخطط الزمني/المالي | `draft, needs_revision` | association_manager |
| 5 — الدفعات | `draft, needs_revision` | association_manager |
| 6 — مؤشرات الأداء (45 KPI) | `draft, needs_revision` | association_manager |
| 7 — المخاطر | `draft, needs_revision` | association_manager |
| **(submit → excellence_review)** | تلقائي | — |
| 8 — اعتماد الشريك الاستشاري | `excellence_review` | excellence_manager فقط |
| **(approve → consultant_review → consultant_approved → donor_review)** | تلقائي مع مراجعة المستشار | — |
| 9 — اعتماد المؤسسة المانحة | `donor_review` | donor_admin فقط |
| **(approve → in_execution + Hooks تلقائية)** | تلقائي | — |

> كل قسم يُحرَّر في **نفس صفحة الـResource** لكن:
> - الحقول `disabled` بحسب الـ Policy.
> - الأزرار (Submit / Approve / Reject) تظهر بحسب State + Role.
> - زر «تعديل ملاحظات المستشار» للجمعية يعمل فقط في `needs_revision`.

### 5.3 دعم الاختلافات الحقيقية بين الجهات (إن وُجدت)
لو ظهر لاحقاً متطلب بأن **بعض الجمعيات تحتاج حقول إضافية** (مثلاً جمعية أوقاف تريد حقل خاص بالعقار)، الحل المُوصى به:
- **لا** نضيف جداول جديدة.
- نستخدم `metadata JSON` على `initiatives` أو `extra_attributes JSON` على `initiative_basic_info`.
- نسجّل تعريف الحقول الإضافية في جدول `custom_field_definitions` (per organization_type أو per organization_id).
- Filament يبني النموذج ديناميكياً لها فقط.

> هذا **اختياري** ولا يُنفَّذ في الـ MVP. يُفعَّل لاحقاً إذا طلبه العميل.

---

## 6. هيكلية الكود المقترحة (متطابقة مع README)

### 6.1 الـ Stack (بدون تغيير)
- **PHP 8.2+ / Laravel 11.x / MySQL 8 / Redis 7**
- **Filament 3.2** (4 لوحات منفصلة)
- **Spatie:** permission، activitylog، model-states، medialibrary، query-builder، backup
- **Tools:** Pest 3، Larastan، Pint

### 6.2 شجرة المجلدات (مطابقة README مع تحسين)
```
app/
├── Domain/                    ← Modular Monolith
│   ├── Identity/              (Users, Orgs, Roles)
│   ├── Reference/             (Areas, Standards, KPIs, Specialties)
│   ├── Initiatives/           ← القلب
│   │   ├── Models/
│   │   │   ├── Initiative.php
│   │   │   ├── InitiativeBasicInfo.php
│   │   │   ├── InitiativeOutput.php
│   │   │   ├── InitiativeTimelineItem.php
│   │   │   ├── InitiativeKpiValue.php
│   │   │   ├── InitiativeRisk.php
│   │   │   ├── InitiativeConsultantApproval.php
│   │   │   └── InitiativeDonorApproval.php
│   │   ├── States/            ← Spatie\ModelStates
│   │   │   ├── DraftState.php
│   │   │   ├── SubmittedState.php
│   │   │   ├── ExcellenceReviewState.php
│   │   │   ├── NeedsRevisionState.php
│   │   │   ├── ExcellenceApprovedState.php
│   │   │   ├── ConsultantReviewState.php
│   │   │   ├── ConsultantApprovedState.php
│   │   │   ├── DonorReviewState.php
│   │   │   ├── DonorApprovedState.php
│   │   │   ├── InExecutionState.php
│   │   │   ├── ClosedState.php
│   │   │   └── RejectedState.php
│   │   ├── Actions/
│   │   │   ├── SubmitInitiativeAction.php
│   │   │   ├── ExcellenceApproveAction.php
│   │   │   ├── ConsultantApproveAction.php
│   │   │   ├── DonorApproveAction.php
│   │   │   ├── RejectInitiativeAction.php
│   │   │   ├── RequestRevisionAction.php
│   │   │   └── CloseInitiativeAction.php
│   │   ├── Events/
│   │   │   ├── InitiativeSubmitted.php
│   │   │   ├── InitiativeApproved.php
│   │   │   ├── InitiativeNeedsRevision.php
│   │   │   └── InitiativeClosed.php
│   │   ├── Listeners/
│   │   │   └── DonorApprovalHooksListener.php  ← الـ7 Hooks المذكورة في README §8.3
│   │   ├── Policies/
│   │   │   └── InitiativePolicy.php  ← Section-level methods
│   │   └── Scopes/
│   │       └── TenantScope.php
│   ├── Execution/             (Visits, Outputs Evidences, Monthly Reports, Meetings)
│   ├── Communication/         (Tickets)
│   ├── Finance/               (Payments)
│   ├── Evaluation/            (Service Evaluations)
│   └── Notifications/
├── Filament/
│   ├── Admin/                 (super_admin)
│   ├── Excellence/
│   ├── Donor/
│   ├── Consultant/
│   └── Association/
├── Http/
├── Policies/                  (الـ Policies المركزية)
├── Providers/
└── Support/
```

### 6.3 State Machine — تنفيذ ملموس
**لماذا `spatie/laravel-model-states` بدلاً من ENUM يدوي؟**
- يمنع انتقالات غير قانونية على مستوى DB (Validation تلقائي).
- يربط كل State بصلاحياته (`canBeApproved()`, `canBeEdited()`).
- يولد Activity Log تلقائياً لكل انتقال.
- يخلّص الـ Controller من شجرة `if-else`.

**مثال استخدام:**
```php
// التحقق
$initiative->status->canTransitionTo(ExcellenceApprovedState::class);

// التطبيق
$initiative->status->transitionTo(ExcellenceApprovedState::class);

// السماح فقط من خلال State Machine config:
public function registerStates(): void {
    $this->default(DraftState::class);
    $this->allowTransition(DraftState::class, SubmittedState::class);
    $this->allowTransition(SubmittedState::class, ExcellenceReviewState::class);
    // ...
}
```

### 6.4 Section-Level Policy — تنفيذ ملموس
```php
class InitiativePolicy
{
    public function editSection1to7(User $user, Initiative $initiative): bool
    {
        return $user->hasRole('association_manager')
            && $user->organization_id === $initiative->organization_id
            && in_array($initiative->status::class, [DraftState::class, NeedsRevisionState::class]);
    }

    public function editSection8(User $user, Initiative $initiative): bool
    {
        return $user->hasRole('excellence_manager')
            && $initiative->status::class === ExcellenceReviewState::class;
    }

    public function editSection9(User $user, Initiative $initiative): bool
    {
        return $user->hasRole('donor_admin')
            && $initiative->status::class === DonorReviewState::class;
    }

    public function submit(User $user, Initiative $initiative): bool { /* ... */ }
    public function approveAsExcellence(User $user, Initiative $initiative): bool { /* ... */ }
    public function approveAsDonor(User $user, Initiative $initiative): bool { /* ... */ }
}
```

داخل Filament Resource:
```php
TextInput::make('project_name')
    ->disabled(fn ($record) => !auth()->user()->can('editSection1to7', $record));

Textarea::make('consultant_approval.notes')
    ->visible(fn ($record) => auth()->user()->can('editSection8', $record));
```

### 6.5 Tenant Scope (الجمعية ترى مبادراتها فقط)
```php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (!$user) return;

        if ($user->hasRole('association_manager') || $user->hasRole('association_member')) {
            $builder->where('organization_id', $user->organization_id);
        }
        // super_admin / excellence / donor / consultant → لا قيد (لكن مع تخصيص consultant)
    }
}
```

### 6.6 Hooks بعد `donor_approved` — Listener واحد
كل العمليات الـ7 المذكورة في README §8.3 تُنفَّذ في `DonorApprovalHooksListener`:
```php
public function handle(InitiativeApproved $event): void
{
    DB::transaction(function () use ($event) {
        $initiative = $event->initiative;

        // 1. PDF
        GenerateOfficialPdfJob::dispatch($initiative);

        // 2. Schedule 5 payments
        SchedulePaymentRequestsAction::run($initiative);

        // 3. Default visit slots
        CreateDefaultVisitSlotsAction::run($initiative);

        // 4. Activate evidence reminders
        ActivateEvidenceRemindersAction::run($initiative);

        // 5. Generate public initiative page
        $initiative->update(['public_page_generated_at' => now()]);

        // 6. Notify all parties
        Notification::send($initiative->allStakeholders(), new InitiativeApprovedNotification($initiative));

        // 7. Log
        activity()->on($initiative)->log('initiative_approved');
    });
}
```

---

## 7. ملاحظات/تحسينات على README

### 7.1 ملاحظات إيجابية (مهمة الإبقاء عليها)
1. ✅ **اعتماد `spatie/laravel-model-states`** — قرار ممتاز.
2. ✅ **Modular Monolith بـ`app/Domain`** — أفضل من Service/Repository التقليدي للـ Laravel.
3. ✅ **Multi-panel Filament 3** — الـ best practice الحالي للنطاق المؤسسي.
4. ✅ **Tenant Scope على Eloquent Global Scope** — أبسط وأقوى من Multi-DB.
5. ✅ **Hybrid Schema (Canonical + JSON metadata)** — توازن صحيح.
6. ✅ **توافق RTL + ar/en lang files** مذكور.

### 7.2 ملاحظات تحتاج توضيح/تحسين قبل البدء

#### (أ) **organizations vs associations**
README يحوي `organizations.type ENUM(...)` ومع ذلك يستخدم في أماكن أخرى `Association` و `Donor` كأنها كيانات منفصلة. **التوصية:** الإبقاء على `organizations` كجدول موحد (Single Table Inheritance) واستخدام `Association`, `Donor`, `ExcellenceTeam`, `ConsultantFirm` كـ child models أو STI scopes — مع توضيح هذا في القرار الهندسي.

#### (ب) **assigned_consultant_id واحد فقط؟**
README يضع `assigned_consultant_id` على `initiatives` (1:1)، لكن الـ Mind Map يذكر **4 تخصصات للمستشارين** (مالي/أوقاف/تخطيط/أثر) — هل المبادرة تحتاج أكثر من مستشار في نفس الوقت (واحد لكل تخصص)؟
**التوصية:** اعتماد جدول pivot `initiative_consultants(initiative_id, consultant_user_id, specialty_id, is_lead)` بدل العلاقة 1:1. هذا أكثر مرونة ولا يكلف كثيراً.
**هذه نقطة تحتاج قرارك.**

#### (ج) **`metadata JSON` على initiatives**
محتواه غير محدد. **التوصية:** توثيق ما يدخل فيه (مثلاً: `pdf_paths`, `signature_status`, `tags`, `external_refs`) لمنع تحوله إلى «سلة قمامة».

#### (د) **45 KPI seeder من DOCX**
الـ KPIs في DOCX **بالعربية الكاملة** (تحققت — موجودة كلها). يجب نسخها بالحرف للسيدر `KpisSeeder.php` بدون تعديل لأنها رسمية. **سأقوم بإعداد ملف JSON جاهز للسيدر من DOCX قبل البدء بالكود** (مهمة Sprint 1).

#### (هـ) **اختيار MeiliSearch**
README يقترح Scout + MeiliSearch. **التوصية:** **اختياري للـ MVP**؛ الفلاتر العادية + LIKE تكفي لـ13 جمعية × عدة مبادرات. تأجيله لـ Sprint 8.

#### (و) **2FA للأدوار الحساسة**
README يقترح 2FA إجباري لـ donor/excellence/super_admin. **التوصية:** اعتماد `filament/breezy` أو `stechstudio/laravel-google-2fa` — مدمج مع Filament بسهولة.

#### (ز) **التواقيع الرقمية**
README يذكر `signature_path` فقط. السؤال: **توقيع مرفوع كصورة، أم توقيع رقمي (Digital Signature بـ X.509)؟**
- لو الأول (الأرجح): SignaturePad على Frontend → upload → store path.
- لو الثاني: يحتاج تكاملاً مع مزود رسمي (مثل علامة، Sadiq، Adobe Sign).
**هذه نقطة تحتاج قرارك.**

#### (ح) **استحقاق الدفعة**
PDF يقول «استحقاق الدفعة تلقائي بناء على المخرجات المرتبطة». README فيه `payment_requests.status` يبدأ من `eligible`. **التوصية:** Listener على `OutputEvidenceApproved` يفحص هل كل مخرجات الدفعة `approved` ⇒ يُحوِّل الدفعة إلى `eligible` ويُنشئ تنبيه.

---

## 8. خارطة طريق مقترحة (مختصرة، 9 Sprints)

> **ملاحظة:** أبسط من 11 sprint في README، مع نفس التغطية.

| Sprint | المحتوى | المدة |
|---|---|---|
| **0 — التأسيس** | Laravel 11 + Filament 3 + Spatie + Pest + Pint + Larastan + Horizon + .env + CI | 2 أيام |
| **1 — Reference + Identity** | Migrations + Seeders للـ45 KPI و3 مجالات و11 معيار و4 تخصصات و13 جمعية و6 أدوار | 2 أيام |
| **2 — Initiative Core (Sections 1-7)** | 8 جداول + Models + الـ Wizard 7 خطوات + auto-save + Form Requests + Tenant Scope | 5-6 أيام |
| **3 — Workflow + Approvals** | State Machine 11 حالة + Section Policies + اعتماد القسم 8 (excellence) + اعتماد القسم 9 (donor) + التواقيع + PDF Export + الـ7 Hooks | 4 أيام |
| **4 — Multi-Panel Filament** | 4 لوحات + Resources + Dashboards + Widgets + Navigation | 5 أيام |
| **5 — Execution & Monitoring** | Output evidences + Visit slots + Visits + Visit reports + Monthly reports + Meetings | 5-6 أيام |
| **6 — Communication + Finance + Evaluation** | Tickets + replies + escalation + Payments eligibility + Service evaluations | 4 أيام |
| **7 — Notifications + Scheduler** | كل 18 نوع إشعار + Scheduled reminders + User preferences + Mail templates | 3 أيام |
| **8 — Reports + Exports + Search + UAT** | التقارير الفنية + Excel/PDF + Saved filters + (MeiliSearch اختياري) + UAT + Tests + Documentation + Deployment | 5 أيام |

**الإجمالي:** ~35-40 يوم عمل لـ MVP صلب.

---

## 9. مخاطر ونقاط نقاش مفتوحة

| # | السؤال | لماذا مهم | تأثير على البناء |
|---|---|---|---|
| 1 | هل المبادرة تحتاج **أكثر من مستشار** (4 تخصصات) في نفس الوقت أم واحد فقط؟ | تحدد بنية العلاقة | جدول pivot vs FK مفرد |
| 2 | **التواقيع** صور SignaturePad أم رقمية معتمدة؟ | تكامل خارجي | مزود توقيع أم لا |
| 3 | هل هناك **جهات لها حقول مخصصة** فعلاً؟ | يفعّل أو يلغي طبقة `metadata JSON` المرنة | Sprint إضافي أم لا |
| 4 | الـ **PDF الرسمي** يجب أن يكون مطابقاً DOCX 100% (نفس التنسيق العربي/RTL والشعار)؟ | تأثير على templating | dompdf مع RTL settings |
| 5 | هل **اللوحة الإدارية** (super_admin) يجب أن ترى البيانات Cross-tenant بدون قيد؟ | يحدد سياسة Tenant Scope | استثناء صريح للسوبر |
| 6 | هل **WhatsApp/SMS** متطلب MVP أم لاحق؟ | تكلفة تكامل | تأجيل أو لا |
| 7 | لغة النظام: **عربي فقط** أم ثنائي (ar/en)؟ | يؤثر على lang files و DB columns (name_ar/name_en موجودة) | تكلفة ترجمة |
| 8 | **الاستضافة المستهدفة:** VPS عادي / AWS / Forge / Vapor؟ | يحدد config DB/Redis/S3 | Sprint 8 |
| 9 | **متطلبات الأداء:** كم مستخدم متزامن؟ كم Initiative بالشهر؟ | يحدد Caching strategy | Indexing + Redis |
| 10 | هل هناك **نظام قائم سابق** (Excel/Access/أخرى) نحتاج استيراد بياناته؟ | data migration | Sprint إضافي |

---

## 10. الخطوة التالية المقترحة

**أنا الآن في وضع «تحليل ولا أكتب كود».** قبل المتابعة:

1. **راجع هذا التقرير** وأكد/صحح الـ10 نقاط في القسم 9 + ملاحظات القسم 7.2.
2. أكد أو عدّل **خارطة الطريق** (9 Sprints).
3. عند موافقتك، سأبدأ بـ:
   - **PR #1 — Sprint 0:** هيكلة المشروع الأساسية (Laravel 11 + Filament 3 + Spatie + Pest + .env.example + GitHub Actions CI). PR صغير قابل للمراجعة.
   - **PR #2 — Sprint 1:** Reference data + Seeders (45 KPI من DOCX بالحرف).
   - وهكذا — كل Sprint = PR منفصل.
4. سأحافظ على **README الحالي كما هو** كـ "Source of truth" وأضيف فقط `docs/architecture/decisions/*.md` (ADRs) لكل قرار جديد.

---

## ملحق: ملخص جداول الـ DB (35 جدول)

| الفئة | الجداول |
|---|---|
| الهوية والمنظمات (5) | users, organizations, organization_user, consultants, (spatie roles+permissions+pivots) |
| البيانات المرجعية (4) | areas, standards, kpis, specialties |
| المبادرة (10) | initiatives, initiative_basic_info, initiative_areas, initiative_outputs, initiative_timeline_items, initiative_timeline_months, initiative_payments, initiative_kpi_values, initiative_risks, initiative_consultant_approval, initiative_donor_approval |
| التنفيذ والمتابعة (12) | output_evidences, visit_slots, visits, visit_reports, visit_evidences, monthly_reports, meetings, meeting_attendees, tickets, ticket_replies, payment_requests, service_evaluations |
| الإشعارات والـ Logs (3) | notifications, notification_preferences, activity_log |
| Polymorphic (1) | approvals |

**الإجمالي:** ~35 جدول. مطابق README §6.7.

---

> **خلاصة الموقف:** الفهم في README صحيح ومتقن — لا يوجد سبب لتغيير الـ Skeleton. التصحيح الوحيد المهم هو في **فهم سؤالك:** «الجهات لا تختلف في حقول النموذج بل في ملكية أقسامه وحالة كتابتها». بهذا التصحيح، الحل المقترح في README مثالي ولا يحتاج Refactor كبيراً.
>
> جاهز لاستلام موافقتك أو تعديلاتك على الـ10 نقاط في القسم 9 + 7.2، ثم البدء بـ Sprint 0 كـPR منفصل قابل للمراجعة.
