@php
    /** @var \App\Models\Initiative $initiative */
    $initiative = $this->getRecord();

    $statusLabels = [
        'draft' => 'مسودة',
        'submitted' => 'مرسلة للمراجعة',
        'under_review' => 'قيد المراجعة',
        'approved' => 'معتمدة',
        'rejected' => 'مرفوضة',
        'revisions_requested' => 'تحتاج مراجعة',
    ];

    $domainLabels = [
        'developmental_impact' => 'الأثر التنموي',
        'sustainability' => 'الاستدامة',
        'institutional_empowerment' => 'التمكين المؤسسي',
    ];

    $severityLabels = [
        'high' => 'مرتفع',
        'medium' => 'متوسط',
        'low' => 'منخفض',
    ];

    $date = fn ($value) => $value ? $value->format('Y-m-d') : '-';
    $dateTime = fn ($value) => $value ? $value->format('Y-m-d h:i A') : '-';
    $text = fn ($value) => filled(trim(strip_tags((string) $value)))
        ? new \Illuminate\Support\HtmlString(nl2br(e(strip_tags((string) $value))))
        : new \Illuminate\Support\HtmlString('<span class="muted">-</span>');
    $money = fn ($value) => \App\Support\DisplayNumber::riyalHtml($value);
    $plain = fn ($value) => \App\Support\DisplayNumber::plain($value);
    $percent = fn ($value) => \App\Support\DisplayNumber::percentage($value);
    $logoMakeen = asset(config('brand.logo.makeen_header', '/brand/makeen-logo-header.png'));
    $logoMasar = asset(config('brand.logo.masar_header', '/brand/masar-logo-header.png'));
    $generatedAt = now()->format('Y-m-d h:i A');
    $showConsultantNotes = request()->boolean('consultant_notes');
    $consultantNotes = $initiative->evaluations
        ->filter(fn ($evaluation) => filled(trim(strip_tags((string) $evaluation->strengths)))
            || filled(trim(strip_tags((string) $evaluation->improvements)))
            || filled(trim(strip_tags((string) $evaluation->recommendation))))
        ->values();
@endphp

<x-filament-panels::page>
    <style>
        .initiative-print {
            --navy: #283979;
            --teal: #21b2b8;
            --slate: #2b354f;
            --gold: #f9ad1c;
            --ink: #172033;
            --muted: #64748b;
            --line: #e5eaf2;
            --soft: #f6fafb;
            direction: rtl;
            max-width: 1120px;
            margin: 0 auto;
            color: var(--ink);
            display: grid;
            gap: 1rem;
            font-family: 'Alexandria', system-ui, -apple-system, sans-serif;
        }

        .initiative-print * {
            box-sizing: border-box;
            letter-spacing: 0;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .print-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
        }

        .print-toolbar button {
            border: 0;
            border-radius: 10px;
            min-height: 2.55rem;
            padding: .55rem 1rem;
            background: var(--navy);
            color: #fff;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 30px rgba(40, 57, 121, .18);
        }

        .print-toolbar button.secondary {
            background: #fff;
            color: var(--navy);
            border: 1px solid rgba(40, 57, 121, .18);
            box-shadow: 0 10px 24px rgba(43, 53, 79, .08);
        }

        .report-page {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(43, 53, 79, .08);
        }

        .report-header {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1.25rem;
            align-items: start;
            padding: 1.45rem 1.55rem;
            background:
                linear-gradient(135deg, rgba(40, 57, 121, .96), rgba(43, 53, 79, .96)),
                var(--navy);
            color: #fff;
        }

        .report-header::before {
            content: '';
            position: absolute;
            inset-block-start: 0;
            inset-inline-end: 2rem;
            width: 150px;
            height: 6px;
            border-radius: 0 0 999px 999px;
            background: var(--gold);
        }

        .brand-logos {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            padding: .55rem .75rem;
            background: rgba(255, 255, 255, .96);
            border-radius: 14px;
            min-width: 190px;
            justify-content: center;
        }

        .brand-logos img {
            display: block;
            width: auto;
            max-width: 96px;
            object-fit: contain;
        }

        .brand-logos img:first-child {
            height: 5rem;
        }

        .brand-logos img:last-child {
            height: 3.5rem;
        }

        .brand-separator {
            width: 1px;
            height: 3.5rem;
            background: #d7dde8;
        }

        .report-kicker {
            margin: 0 0 .45rem;
            font-size: .82rem;
            font-weight: 800;
            color: rgba(255, 255, 255, .78);
        }

        .report-title {
            margin: 0;
            font-size: clamp(1.9rem, 4vw, 3.1rem);
            line-height: 1.18;
            font-weight: 900;
        }

        .report-subtitle {
            margin: .65rem 0 0;
            color: rgba(255, 255, 255, .82);
            font-weight: 700;
            line-height: 1.8;
        }

        .status-strip {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: 1rem;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            min-height: 2rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .18);
            font-size: .82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .report-meta {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(90deg, rgba(33, 178, 184, .08), rgba(249, 173, 28, .08));
            border-bottom: 1px solid var(--line);
        }

        .metric {
            background: #fff;
            border: 1px solid rgba(40, 57, 121, .10);
            border-radius: 14px;
            padding: .9rem;
            min-height: 5.1rem;
        }

        .metric small,
        .field-label {
            display: block;
            color: var(--muted);
            font-size: .76rem;
            font-weight: 800;
            margin-bottom: .35rem;
        }

        .metric strong,
        .field-value {
            display: block;
            color: var(--slate);
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.65;
            word-break: break-word;
        }

        .section {
            padding: 1.25rem;
            border-bottom: 1px solid var(--line);
        }

        .section:last-child {
            border-bottom: 0;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin: 0 0 1rem;
        }

        .section-heading h2 {
            margin: 0;
            color: var(--navy);
            font-size: 1.15rem;
            font-weight: 900;
        }

        .section-icon {
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 10px;
            display: inline-grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(40, 57, 121, .10), rgba(33, 178, 184, .12));
            color: var(--navy);
            flex: 0 0 auto;
        }

        .section-icon svg {
            width: 1.1rem;
            height: 1.1rem;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .85rem;
        }

        .field-card {
            border: 1px solid var(--line);
            background: #fbfdff;
            border-radius: 14px;
            padding: .9rem 1rem;
            min-height: 5rem;
        }

        .field-card.is-wide {
            grid-column: 1 / -1;
        }

        .money-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
        }

        .money-card {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(33, 178, 184, .05)),
                #fff;
            border: 1px solid rgba(33, 178, 184, .18);
            border-radius: 14px;
            padding: 1rem;
        }

        .money-card strong {
            color: var(--navy);
            font-size: 1.15rem;
        }

        .print-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 14px;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .88rem;
            background: #fff;
        }

        .print-table th,
        .print-table td {
            padding: .75rem .65rem;
            border-bottom: 1px solid var(--line);
            text-align: right;
            vertical-align: top;
            line-height: 1.75;
        }

        .print-table th {
            background: #f7fafc;
            color: var(--slate);
            font-weight: 900;
            font-size: .78rem;
            white-space: nowrap;
        }

        .print-table tr:last-child td {
            border-bottom: 0;
        }

        .muted {
            color: #94a3b8;
            font-weight: 700;
        }

        .footer-note {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            color: var(--muted);
            background: #f8fafc;
            font-size: .78rem;
            font-weight: 700;
        }

        .consultant-notes-section.is-hidden {
            display: none;
        }

        .notes-grid {
            display: grid;
            gap: .85rem;
        }

        .note-card {
            border: 1px solid rgba(40, 57, 121, .12);
            border-radius: 14px;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(40, 57, 121, .04), rgba(33, 178, 184, .04)), #fff;
        }

        .note-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: .85rem;
            padding-bottom: .65rem;
            border-bottom: 1px solid var(--line);
        }

        .note-head strong {
            color: var(--navy);
            font-weight: 900;
        }

        .note-head span {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 800;
        }

        .note-columns {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        @page {
            size: A4;
            margin: 12mm;
        }

        @media print {
            body {
                background: #fff !important;
            }

            body * {
                visibility: hidden !important;
            }

            .initiative-print,
            .initiative-print * {
                visibility: visible !important;
            }

            .initiative-print {
                position: absolute;
                inset: 0;
                max-width: none;
                margin: 0;
                display: block;
            }

            .print-toolbar {
                display: none !important;
            }

            .report-page {
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .report-header {
                border-radius: 0;
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .report-meta {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .field-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .money-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .note-columns {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .section,
            .report-meta,
            .field-card,
            .money-card,
            .print-table-wrap,
            .note-card {
                break-inside: avoid;
            }

            .print-table {
                page-break-inside: auto;
            }

            .print-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }

        @media screen and (max-width: 900px) {
            .report-header,
            .report-meta,
            .field-grid,
            .money-grid,
            .note-columns {
                grid-template-columns: 1fr;
            }

            .brand-logos {
                justify-content: flex-start;
            }
        }
    </style>

    <div class="initiative-print">
        <div class="print-toolbar">
            <button
                type="button"
                class="secondary"
                data-toggle-consultant-notes
                data-show-label="إظهار ملاحظات المستشار"
                data-hide-label="إخفاء ملاحظات المستشار"
            >
                {{ $showConsultantNotes ? 'إخفاء ملاحظات المستشار' : 'إظهار ملاحظات المستشار' }}
            </button>
            <button type="button" onclick="window.print()">تصدير PDF</button>
        </div>

        <article class="report-page">
            <header class="report-header">
                <div>
                    <p class="report-kicker">ملف مبادرة - منصة مكين</p>
                    <h1 class="report-title">{{ $initiative->name_ar }}</h1>
                    <p class="report-subtitle">
                        تقرير منظم لبيانات المبادرة ومدخلاتها ومخرجاتها وجدولها الزمني والمالي.
                    </p>
                    <div class="status-strip">
                        <span class="pill">{{ $statusLabels[$initiative->status] ?? $initiative->status }}</span>
                        <span class="pill">{{ $domainLabels[$initiative->domain] ?? $initiative->domain }}</span>
                        @forelse ($initiative->specializationLabels() as $specialization)
                            <span class="pill">{{ $specialization }}</span>
                        @empty
                            <span class="pill">بدون تخصص محدد</span>
                        @endforelse
                    </div>
                </div>

                <div class="brand-logos" aria-label="شعارات المنصة">
                    <img src="{{ $logoMakeen }}" alt="مكين">
                    <span class="brand-separator" aria-hidden="true"></span>
                    <img src="{{ $logoMasar }}" alt="مسار الإجادة">
                </div>
            </header>

            <div class="report-meta">
                <div class="metric">
                    <small>الجهة المنفذة</small>
                    <strong>{{ $initiative->organization?->name_ar ?? '-' }}</strong>
                </div>
                <div class="metric">
                    <small>تاريخ البداية</small>
                    <strong>{{ $date($initiative->start_date) }}</strong>
                </div>
                <div class="metric">
                    <small>تاريخ النهاية</small>
                    <strong>{{ $date($initiative->end_date) }}</strong>
                </div>
                <div class="metric">
                    <small>الإجمالي شامل الضريبة</small>
                    <strong>{!! $money($initiative->grand_total) !!}</strong>
                </div>
            </div>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2>البطاقة التعريفية</h2>
                </div>
                <div class="field-grid">
                    <div class="field-card">
                        <span class="field-label">اسم المبادرة بالعربية</span>
                        <span class="field-value">{{ $initiative->name_ar ?: '-' }}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">اسم المبادرة بالإنجليزية</span>
                        <span class="field-value">{{ $initiative->name_en ?: '-' }}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">الإدارة المسؤولة</span>
                        <span class="field-value">{{ $initiative->responsible_department ?: '-' }}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">مالك المبادرة</span>
                        <span class="field-value">{{ $initiative->owner_name ?: '-' }}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">مدة التنفيذ</span>
                        <span class="field-value">{{ $plain($initiative->duration_weeks) }} أسبوع</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">تاريخ الإرسال للمراجعة</span>
                        <span class="field-value">{{ $dateTime($initiative->submitted_at) }}</span>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 6h16M4 12h16M4 18h10" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <h2>الوصف والأهداف</h2>
                </div>
                <div class="field-grid">
                    <div class="field-card is-wide">
                        <span class="field-label">الهدف العام</span>
                        <span class="field-value">{!! $text($initiative->main_goal) !!}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">الوصف العام</span>
                        <span class="field-value">{!! $text($initiative->description) !!}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">الأهداف الاستراتيجية</span>
                        <span class="field-value">{!! $text($initiative->strategic_objectives) !!}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">المعايير المرتبطة</span>
                        <span class="field-value">{!! $text($initiative->related_criteria) !!}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">مبررات التطوير</span>
                        <span class="field-value">{!! $text($initiative->development_justification) !!}</span>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM17 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM3 21a4 4 0 0 1 8 0M13 21a4 4 0 0 1 8 0" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <h2>الإدارة والشركاء</h2>
                </div>
                <div class="field-grid">
                    <div class="field-card">
                        <span class="field-label">شركاء التنفيذ</span>
                        <span class="field-value">{!! $text($initiative->partners) !!}</span>
                    </div>
                    <div class="field-card">
                        <span class="field-label">النطاق البشري</span>
                        <span class="field-value">{!! $text($initiative->beneficiaries_scope) !!}</span>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 7h16v10H4zM7 11h.01M17 13h.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2>الملخص المالي</h2>
                </div>
                <div class="money-grid">
                    <div class="money-card">
                        <span class="field-label">الإجمالي قبل الضريبة</span>
                        <strong>{!! $money($initiative->total_cost) !!}</strong>
                    </div>
                    <div class="money-card">
                        <span class="field-label">ضريبة القيمة المضافة</span>
                        <strong>{!! $money($initiative->vat_amount) !!}</strong>
                    </div>
                    <div class="money-card">
                        <span class="field-label">الإجمالي شامل الضريبة</span>
                        <strong>{!! $money($initiative->grand_total) !!}</strong>
                    </div>
                    <div class="money-card">
                        <span class="field-label">العملة</span>
                        <strong>ريال سعودي</strong>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 7h10v10H7zM4 4h16v16H4z" stroke-width="2" stroke-linejoin="round"/></svg>
                    </span>
                    <h2>المخرجات</h2>
                </div>
                <div class="print-table-wrap">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>المرحلة</th>
                                <th>المخرج</th>
                                <th>الكمية</th>
                                <th>وصف المخرج</th>
                                <th>الأنشطة والإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($initiative->outputs as $output)
                                <tr>
                                    <td>{{ $output->phase ?: '-' }}</td>
                                    <td>{{ $output->output ?: '-' }}</td>
                                    <td>{{ $plain($output->quantity) }}</td>
                                    <td>{!! $text($output->output_description) !!}</td>
                                    <td>{!! $text($output->activities) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="muted">لا توجد مخرجات مسجلة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 3v4M17 3v4M4 9h16M6 13h5M6 17h8" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <h2>المخطط الزمني والمالي</h2>
                </div>
                <div class="print-table-wrap">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>المرحلة</th>
                                <th>مخرجات المرحلة</th>
                                <th>الكمية</th>
                                <th>تاريخ البداية</th>
                                <th>تاريخ النهاية</th>
                                <th>التكلفة الفردية</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($initiative->milestones as $milestone)
                                <tr>
                                    <td>{{ $milestone->phase ?: '-' }}</td>
                                    <td>{!! $text($milestone->outputs) !!}</td>
                                    <td>{{ $plain($milestone->quantity) }}</td>
                                    <td>{{ $date($milestone->start_date) }}</td>
                                    <td>{{ $date($milestone->end_date) }}</td>
                                    <td>{!! $money($milestone->unit_cost) !!}</td>
                                    <td>{!! $money($milestone->total_cost) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="muted">لا توجد مراحل مسجلة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 8h18M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zM7 14h4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2>جدول الدفعات</h2>
                </div>
                <div class="print-table-wrap">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>النسبة</th>
                                <th>المبلغ</th>
                                <th>تاريخ الاستحقاق</th>
                                <th>المخرجات المرتبطة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($initiative->payments as $payment)
                                <tr>
                                    <td>{{ $percent($payment->percentage) }}</td>
                                    <td>{!! $money($payment->amount) !!}</td>
                                    <td>{{ $date($payment->due_date) }}</td>
                                    <td>{{ \App\Support\DisplayNumber::listText($payment->linked_outputs) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="muted">لا توجد دفعات مسجلة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19V5M4 19h16M8 16v-5M12 16V8M16 16v-8" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <h2>مؤشرات الأداء</h2>
                </div>
                <div class="print-table-wrap">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>المؤشر</th>
                                <th>خط الأساس</th>
                                <th>المستهدف</th>
                                <th>الدرجة</th>
                                <th>ملاحظات المراجع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($initiative->kpiValues as $kpi)
                                <tr>
                                    <td>{{ $kpi->definition?->indicator ?: '-' }}</td>
                                    <td>{!! $text($kpi->baseline) !!}</td>
                                    <td>{!! $text($kpi->target) !!}</td>
                                    <td>{{ $plain($kpi->score) }}</td>
                                    <td>{!! $text($kpi->reviewer_notes) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="muted">لا توجد مؤشرات أداء مسجلة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="section">
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3l9 16H3L12 3zM12 9v4M12 17h.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2>سجل المخاطر</h2>
                </div>
                <div class="print-table-wrap">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th>الخطر</th>
                                <th>احتمالية الحدوث</th>
                                <th>أثر الخطر</th>
                                <th>إجراء التعامل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($initiative->risks as $risk)
                                <tr>
                                    <td>{!! $text($risk->risk) !!}</td>
                                    <td>{{ $severityLabels[$risk->likelihood] ?? ($risk->likelihood ?: '-') }}</td>
                                    <td>{{ $severityLabels[$risk->impact] ?? ($risk->impact ?: '-') }}</td>
                                    <td>{!! $text($risk->mitigation) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="muted">لا توجد مخاطر مسجلة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="section consultant-notes-section {{ $showConsultantNotes ? '' : 'is-hidden' }}" data-consultant-notes>
                <div class="section-heading">
                    <span class="section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 10h8M8 14h5M5 4h14v13H8l-3 3z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2>ملاحظات المستشار</h2>
                </div>

                @if ($consultantNotes->isEmpty())
                    <div class="field-card">
                        <span class="field-value muted">لا توجد ملاحظات مستشار مسجلة.</span>
                    </div>
                @else
                    <div class="notes-grid">
                        @foreach ($consultantNotes as $note)
                            <article class="note-card">
                                <div class="note-head">
                                    <strong>{{ $note->evaluator?->name ?: 'مستشار' }}</strong>
                                    <span>{{ $dateTime($note->finalized_at ?? $note->updated_at) }}</span>
                                </div>
                                <div class="note-columns">
                                    <div>
                                        <span class="field-label">نقاط القوة</span>
                                        <span class="field-value">{!! $text($note->strengths) !!}</span>
                                    </div>
                                    <div>
                                        <span class="field-label">ملاحظات التحسين</span>
                                        <span class="field-value">{!! $text($note->improvements) !!}</span>
                                    </div>
                                    <div>
                                        <span class="field-label">التوصية</span>
                                        <span class="field-value">{!! $text($note->recommendation) !!}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <footer class="footer-note">
                <span>تم إنشاء الملف عبر منصة مكين.</span>
                <span>تاريخ التصدير: {{ $generatedAt }}</span>
            </footer>
        </article>
    </div>

    <script>
        window.addEventListener('load', () => {
            const url = new URL(window.location.href);
            const notesSection = document.querySelector('[data-consultant-notes]');
            const notesButton = document.querySelector('[data-toggle-consultant-notes]');

            notesButton?.addEventListener('click', () => {
                notesSection?.classList.toggle('is-hidden');
                const isHidden = notesSection?.classList.contains('is-hidden');
                notesButton.textContent = isHidden ? notesButton.dataset.showLabel : notesButton.dataset.hideLabel;
            });

            if (url.searchParams.get('autoprint') === '1') {
                window.print();
            }
        });
    </script>
</x-filament-panels::page>
