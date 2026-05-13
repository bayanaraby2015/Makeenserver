@php
    $statusMap = [
        'draft' => 'مسودة',
        'submitted' => 'مرسل للمراجعة',
        'reviewed' => 'تمت المراجعة',
    ];

    $status = $record->status ?? 'draft';
    $statusLabel = $statusMap[$status] ?? '-';
    $attachments = \App\Support\AttachmentLinks::render($record->attachments);
    $serviceEvaluations = \App\Filament\Support\ServiceEvaluationSummary::render('monthly_report', $record->id);
    $formatDate = fn ($date, string $format = 'Y-m-d h:i A') => $date
        ? $date->copy()->timezone(config('app.timezone'))->format($format)
        : '-';
@endphp

<style>
    .monthly-report-shell {
        direction: rtl;
        display: grid;
        gap: 18px;
    }

    .monthly-report-hero {
        background:
            radial-gradient(circle at top left, rgba(249, 173, 28, .18), transparent 30%),
            linear-gradient(135deg, #283979, #2b354f);
        border-radius: 18px;
        box-shadow: 0 22px 50px rgba(40, 57, 121, .18);
        color: #fff;
        padding: 24px;
        position: relative;
    }

    .monthly-report-hero::after {
        background: #21b2b8;
        border-radius: 999px;
        content: "";
        height: 8px;
        inset-inline-start: 24px;
        position: absolute;
        top: 0;
        width: 110px;
    }

    .monthly-report-hero h2 {
        font-size: 26px;
        font-weight: 900;
        line-height: 1.35;
        margin: 0;
    }

    .monthly-report-hero p {
        color: rgba(255, 255, 255, .76);
        margin: 8px 0 0;
    }

    .monthly-report-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .monthly-report-badge {
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-weight: 900;
        padding: 8px 14px;
    }

    .monthly-report-badge--gold {
        background: #f9ad1c;
        border-color: #f9ad1c;
        color: #283979;
    }

    .monthly-report-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .monthly-report-card,
    .monthly-report-section {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(43, 53, 79, .08);
    }

    .monthly-report-card {
        padding: 16px;
    }

    .monthly-report-card span {
        color: #647085;
        display: block;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .monthly-report-card strong {
        color: #111827;
        display: block;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.55;
    }

    .monthly-report-section {
        overflow: hidden;
    }

    .monthly-report-section h3 {
        background: linear-gradient(90deg, rgba(40, 57, 121, .08), rgba(33, 178, 184, .08));
        border-bottom: 1px solid #e7ecf3;
        color: #283979;
        font-size: 17px;
        font-weight: 900;
        margin: 0;
        padding: 14px 18px;
    }

    .monthly-report-body {
        color: #2b354f;
        display: grid;
        gap: 14px;
        line-height: 1.9;
        padding: 18px;
    }

    .monthly-report-block {
        border-bottom: 1px solid #edf1f6;
        padding-bottom: 14px;
    }

    .monthly-report-block:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .monthly-report-block h4 {
        color: #283979;
        font-size: 14px;
        font-weight: 900;
        margin: 0 0 8px;
    }

    @media (max-width: 980px) {
        .monthly-report-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .monthly-report-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="monthly-report-shell">
    <section class="monthly-report-hero">
        <h2>{{ $record->initiative?->name_ar ?? 'تقرير شهري' }}</h2>
        <p>{{ $record->organization?->name_ar ?? '-' }} · {{ $record->consultant?->name ?? '-' }}</p>
        <div class="monthly-report-badges">
            <div class="monthly-report-badge monthly-report-badge--gold">رقم التقرير #{{ $record->id }}</div>
            <div class="monthly-report-badge">{{ $statusLabel }}</div>
        </div>
    </section>

    <div class="monthly-report-grid">
        <div class="monthly-report-card">
            <span>شهر التقرير</span>
            <strong>{{ $formatDate($record->report_month, 'Y-m') }}</strong>
        </div>
        <div class="monthly-report-card">
            <span>تاريخ الإرسال</span>
            <strong>{{ $formatDate($record->submitted_at) }}</strong>
        </div>
        <div class="monthly-report-card">
            <span>تاريخ المراجعة</span>
            <strong>{{ $formatDate($record->reviewed_at) }}</strong>
        </div>
        <div class="monthly-report-card">
            <span>آخر تحديث</span>
            <strong>{{ $formatDate($record->updated_at) }}</strong>
        </div>
    </div>

    <section class="monthly-report-section">
        <h3>محتوى التقرير</h3>
        <div class="monthly-report-body">
            <div class="monthly-report-block">
                <h4>الملخص التنفيذي</h4>
                {!! filled($record->executive_summary) ? $record->executive_summary : '-' !!}
            </div>
            <div class="monthly-report-block">
                <h4>ملخص تقدم الإنجاز</h4>
                {!! filled($record->progress_summary) ? $record->progress_summary : '-' !!}
            </div>
            <div class="monthly-report-block">
                <h4>المخاطر والتحديات</h4>
                {!! filled($record->risks_summary) ? $record->risks_summary : '-' !!}
            </div>
            <div class="monthly-report-block">
                <h4>ملخص الأسئلة والاستفسارات</h4>
                {!! filled($record->questions_summary) ? $record->questions_summary : '-' !!}
            </div>
            <div class="monthly-report-block">
                <h4>التوصيات</h4>
                {!! filled($record->recommendations) ? $record->recommendations : '-' !!}
            </div>
        </div>
    </section>

    <section class="monthly-report-section">
        <h3>المرفقات</h3>
        <div class="monthly-report-body">
            {!! $attachments !!}
        </div>
    </section>

    <section class="monthly-report-section">
        <h3>تقييمات الخدمة</h3>
        <div class="monthly-report-body">
            {!! $serviceEvaluations !!}
        </div>
    </section>
</div>
