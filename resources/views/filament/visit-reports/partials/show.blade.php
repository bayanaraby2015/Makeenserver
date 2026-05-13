@php
    $statusMap = [
        'proposed' => 'بانتظار اختيار الجهة',
        'planned' => 'مجدولة',
        'completed' => 'منفذة',
        'cancelled' => 'ملغاة',
    ];

    $typeMap = [
        'office' => 'حضور للمكتب',
        'zoom' => 'زوم',
        'field' => 'زيارة ميدانية',
    ];

    $status = $record->status ?? 'proposed';
    $statusLabel = $statusMap[$status] ?? '-';
    $typeLabel = $typeMap[$record->visit_type ?? ''] ?? '-';
    $options = collect($record->appointment_options ?? [])
        ->filter(fn ($option) => is_array($option) && filled($option['starts_at'] ?? null));
    $attachments = \App\Support\AttachmentLinks::render($record->evidence_files);
    $serviceEvaluations = \App\Filament\Support\ServiceEvaluationSummary::render('visit_report', $record->id);
    $formatDate = fn ($date) => \App\Support\VisitAppointmentFormatter::dateTime($date);
@endphp

<style>
    .visit-report-shell {
        direction: rtl;
        display: grid;
        gap: 18px;
    }

    .visit-report-hero {
        background:
            radial-gradient(circle at top left, rgba(33, 178, 184, .16), transparent 28%),
            linear-gradient(135deg, #283979, #2b354f);
        border-radius: 18px;
        box-shadow: 0 22px 50px rgba(40, 57, 121, .18);
        color: #fff;
        overflow: hidden;
        padding: 24px;
        position: relative;
    }

    .visit-report-hero::after {
        background: #f9ad1c;
        border-radius: 999px;
        content: "";
        height: 8px;
        inset-inline-start: 24px;
        position: absolute;
        top: 0;
        width: 110px;
    }

    .visit-report-hero h2 {
        font-size: 26px;
        font-weight: 900;
        line-height: 1.35;
        margin: 0;
    }

    .visit-report-hero p {
        color: rgba(255, 255, 255, .76);
        margin: 8px 0 0;
    }

    .visit-report-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .visit-report-badge {
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-weight: 900;
        margin-top: 16px;
        padding: 8px 14px;
    }

    .visit-report-badge--gold {
        background: #f9ad1c;
        border-color: #f9ad1c;
        color: #283979;
    }

    .visit-report-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .visit-report-card {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(43, 53, 79, .08);
        padding: 16px;
    }

    .visit-report-card span {
        color: #647085;
        display: block;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .visit-report-card strong {
        color: #111827;
        display: block;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.55;
    }

    .visit-report-card--accent {
        background: linear-gradient(135deg, rgba(33, 178, 184, .12), rgba(249, 173, 28, .10)), #fff;
        border-color: rgba(33, 178, 184, .24);
    }

    .visit-report-section {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(43, 53, 79, .07);
        overflow: hidden;
    }

    .visit-report-section h3 {
        background: linear-gradient(90deg, rgba(40, 57, 121, .08), rgba(33, 178, 184, .08));
        border-bottom: 1px solid #e7ecf3;
        color: #283979;
        font-size: 17px;
        font-weight: 900;
        margin: 0;
        padding: 14px 18px;
    }

    .visit-report-body {
        color: #2b354f;
        display: grid;
        gap: 14px;
        line-height: 1.9;
        padding: 18px;
    }

    .visit-report-options {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .visit-report-option {
        background: #f8fafc;
        border: 1px solid #dfe7f2;
        border-radius: 12px;
        color: #2b354f;
        font-weight: 800;
        padding: 12px 14px;
    }

    .visit-report-option.is-selected {
        background: rgba(33, 178, 184, .11);
        border-color: #21b2b8;
        color: #283979;
    }

    .visit-report-content-block {
        border-bottom: 1px solid #edf1f6;
        padding-bottom: 14px;
    }

    .visit-report-content-block:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .visit-report-content-block h4 {
        color: #283979;
        font-size: 14px;
        font-weight: 900;
        margin: 0 0 8px;
    }

    @media (max-width: 980px) {
        .visit-report-grid,
        .visit-report-options {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .visit-report-grid,
        .visit-report-options {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="visit-report-shell">
    <section class="visit-report-hero">
        <h2>{{ $record->initiative?->name_ar ?? 'تقرير زيارة' }}</h2>
        <p>{{ $record->organization?->name_ar ?? '-' }} · {{ $record->consultant?->name ?? '-' }}</p>
        <div class="visit-report-meta-row">
            <div class="visit-report-badge visit-report-badge--gold">رقم الزيارة #{{ $record->id }}</div>
            <div class="visit-report-badge">{{ $statusLabel }}</div>
        </div>
    </section>

    <div class="visit-report-grid">
        <div class="visit-report-card">
            <span>نوع الزيارة</span>
            <strong>{{ $typeLabel }}</strong>
        </div>
        <div class="visit-report-card visit-report-card--accent">
            <span>الموعد المعتمد</span>
            <strong>{{ $formatDate($record->scheduled_at) }}</strong>
        </div>
        <div class="visit-report-card">
            <span>وقت اختيار الموعد</span>
            <strong>{{ $formatDate($record->selected_at) }}</strong>
        </div>
        <div class="visit-report-card">
            <span>تاريخ التنفيذ</span>
            <strong>{{ $formatDate($record->completed_at) }}</strong>
        </div>
    </div>

    <section class="visit-report-section">
        <h3>المواعيد المقترحة</h3>
        <div class="visit-report-body">
            <div class="visit-report-options">
                @forelse ($options as $option)
                    @php
                        $startsAt = $option['starts_at'];
                        $selected = \App\Support\VisitAppointmentFormatter::isSelected($startsAt, $record->scheduled_at);
                    @endphp
                    <div class="visit-report-option {{ $selected ? 'is-selected' : '' }}">
                        {{ \App\Support\VisitAppointmentFormatter::dateTime($startsAt) }}
                    </div>
                @empty
                    <div class="visit-report-option">لا توجد مواعيد مقترحة</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="visit-report-section">
        <h3>محتوى تقرير الزيارة</h3>
        <div class="visit-report-body">
            <div class="visit-report-content-block">
                <h4>تقرير ما قبل الزيارة</h4>
                {!! filled($record->pre_visit_notes) ? $record->pre_visit_notes : '-' !!}
            </div>
            <div class="visit-report-content-block">
                <h4>ملخص الزيارة</h4>
                {!! filled($record->summary) ? $record->summary : '-' !!}
            </div>
            <div class="visit-report-content-block">
                <h4>أبرز الإنجازات</h4>
                {!! filled($record->achievements) ? $record->achievements : '-' !!}
            </div>
            <div class="visit-report-content-block">
                <h4>التحديات</h4>
                {!! filled($record->challenges) ? $record->challenges : '-' !!}
            </div>
            <div class="visit-report-content-block">
                <h4>التوصيات</h4>
                {!! filled($record->recommendations) ? $record->recommendations : '-' !!}
            </div>
        </div>
    </section>

    <section class="visit-report-section">
        <h3>الشواهد والمرفقات</h3>
        <div class="visit-report-body">
            {!! $attachments !!}
        </div>
    </section>

    <section class="visit-report-section">
        <h3>تقييمات الخدمة</h3>
        <div class="visit-report-body">
            {!! $serviceEvaluations !!}
        </div>
    </section>
</div>
