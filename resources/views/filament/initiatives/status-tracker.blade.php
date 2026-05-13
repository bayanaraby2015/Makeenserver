@php
    $record = $getRecord();
    $status = $record->status ?? 'draft';
    $statusLabel = __('initiatives.statuses.' . $status);
    $latestEvaluation = $record->evaluations->sortByDesc('updated_at')->first();
    $latestDecision = $latestEvaluation?->decision ? __('initiatives.decisions.' . $latestEvaluation->decision) : '-';
    $latestEvaluator = $latestEvaluation?->evaluator?->name ?? '-';
    $lastActivityAt = $latestEvaluation?->updated_at ?? $record->updated_at;

    $steps = [
        'draft' => 'مسودة',
        'submitted' => 'رفع المبادرة',
        'under_review' => 'مراجعة مسار الإجادة',
        'approved' => 'اعتماد نهائي',
    ];

    $activeIndex = match ($status) {
        'approved' => 3,
        'under_review', 'revisions_requested' => 2,
        'submitted' => 1,
        default => 0,
    };

    $tone = match ($status) {
        'approved' => ['bg' => '#ecfdf5', 'line' => '#16a34a', 'text' => '#166534', 'chip' => 'معتمدة'],
        'rejected' => ['bg' => '#fef2f2', 'line' => '#dc2626', 'text' => '#991b1b', 'chip' => 'مرفوضة'],
        'revisions_requested' => ['bg' => '#fff7ed', 'line' => '#f9ad1c', 'text' => '#92400e', 'chip' => 'تحتاج تعديلات'],
        default => ['bg' => '#eefbfd', 'line' => '#21b2b8', 'text' => '#283979', 'chip' => $statusLabel],
    };
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div dir="rtl" class="mk-status-tracker" style="--mk-status-bg: {{ $tone['bg'] }}; --mk-status-line: {{ $tone['line'] }}; --mk-status-text: {{ $tone['text'] }};">
        <div class="mk-status-tracker__hero">
            <div>
                <div class="mk-status-tracker__eyebrow">متابعة الحالة</div>
                <h2>مسار المبادرة الآن</h2>
                <p>ملخص تنفيذي سريع لآخر قرار، التقييمات، والتنبيهات التي تحتاج متابعة قبل الدخول لتفاصيل الأقسام.</p>
            </div>
            <div class="mk-status-tracker__badge">{{ $tone['chip'] }}</div>
        </div>

        <div class="mk-status-tracker__steps">
            @foreach ($steps as $key => $label)
                @php
                    $index = $loop->index;
                    $isDone = $index <= $activeIndex && $status !== 'rejected';
                    $isCurrent = $index === $activeIndex && $status !== 'approved';
                @endphp
                <div class="mk-status-step {{ $isDone ? 'is-done' : '' }} {{ $isCurrent ? 'is-current' : '' }}">
                    <span>{{ $index + 1 }}</span>
                    <strong>{{ $label }}</strong>
                </div>
            @endforeach
        </div>

        <div class="mk-status-tracker__grid">
            <div>
                <span>الحالة الحالية</span>
                <strong>{{ $statusLabel }}</strong>
            </div>
            <div>
                <span>آخر تقييم</span>
                <strong>{{ $latestDecision }}</strong>
                <small>{{ $latestEvaluator }}</small>
            </div>
            <div>
                <span>آخر تحديث</span>
                <strong>{{ $lastActivityAt?->format('Y-m-d h:i A') ?? '-' }}</strong>
            </div>
            <div>
                <span>تاريخ الرفع</span>
                <strong>{{ $record->submitted_at?->format('Y-m-d h:i A') ?? '-' }}</strong>
            </div>
        </div>

        @if (filled($record->rejection_reason))
            <div class="mk-status-tracker__note">
                <strong>سبب الرفض / طلب التعديل</strong>
                <p>{{ $record->rejection_reason }}</p>
            </div>
        @elseif ($latestEvaluation?->recommendation)
            <div class="mk-status-tracker__note">
                <strong>توصية المستشار الأخيرة</strong>
                <p>{{ $latestEvaluation->recommendation }}</p>
            </div>
        @endif
    </div>
</x-dynamic-component>

<style>
    .mk-status-tracker {
        border: 1px solid color-mix(in srgb, var(--mk-status-line), white 62%);
        border-radius: 18px;
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--mk-status-bg), white 8%), #fff 62%),
            var(--mk-status-bg);
        box-shadow: 0 18px 50px rgba(43, 53, 79, 0.10);
        color: #2b354f;
        overflow: hidden;
        padding: 22px;
    }

    .mk-status-tracker__hero {
        align-items: start;
        display: flex;
        gap: 18px;
        justify-content: space-between;
    }

    .mk-status-tracker__eyebrow {
        color: var(--mk-status-line);
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .mk-status-tracker h2 {
        color: #283979;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.25;
        margin: 0;
    }

    .mk-status-tracker p {
        color: #5b6476;
        font-size: 14px;
        line-height: 1.8;
        margin: 8px 0 0;
    }

    .mk-status-tracker__badge {
        background: var(--mk-status-line);
        border-radius: 999px;
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        padding: 9px 18px;
        white-space: nowrap;
    }

    .mk-status-tracker__steps {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 20px;
        position: relative;
    }

    .mk-status-step {
        align-items: center;
        background: #fff;
        border: 1px solid #e5eaf3;
        border-radius: 14px;
        display: flex;
        gap: 10px;
        min-height: 56px;
        padding: 10px 12px;
    }

    .mk-status-step span {
        align-items: center;
        background: #eef2f7;
        border-radius: 999px;
        color: #647085;
        display: inline-flex;
        flex: 0 0 30px;
        font-size: 13px;
        font-weight: 900;
        height: 30px;
        justify-content: center;
    }

    .mk-status-step strong {
        color: #2b354f;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.45;
    }

    .mk-status-step.is-done {
        border-color: color-mix(in srgb, var(--mk-status-line), white 48%);
        box-shadow: inset 0 -3px 0 var(--mk-status-line);
    }

    .mk-status-step.is-done span,
    .mk-status-step.is-current span {
        background: var(--mk-status-line);
        color: #fff;
    }

    .mk-status-tracker__grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 16px;
    }

    .mk-status-tracker__grid > div {
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(229, 234, 243, 0.92);
        border-radius: 14px;
        min-height: 84px;
        padding: 13px 14px;
    }

    .mk-status-tracker__grid span {
        color: #6b7280;
        display: block;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .mk-status-tracker__grid strong {
        color: #111827;
        display: block;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.5;
    }

    .mk-status-tracker__grid small {
        color: #647085;
        display: block;
        font-size: 12px;
        margin-top: 2px;
    }

    .mk-status-tracker__note {
        background: color-mix(in srgb, var(--mk-status-bg), white 35%);
        border-inline-start: 5px solid var(--mk-status-line);
        border-radius: 14px;
        margin-top: 16px;
        padding: 14px 16px;
    }

    .mk-status-tracker__note strong {
        color: var(--mk-status-text);
        display: block;
        font-size: 14px;
        font-weight: 900;
    }

    .mk-status-tracker__note p {
        color: #2b354f;
        margin-top: 4px;
    }

    @media (max-width: 900px) {
        .mk-status-tracker__hero {
            flex-direction: column;
        }

        .mk-status-tracker__steps,
        .mk-status-tracker__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 560px) {
        .mk-status-tracker {
            border-radius: 14px;
            padding: 16px;
        }

        .mk-status-tracker__steps,
        .mk-status-tracker__grid {
            grid-template-columns: 1fr;
        }
    }
</style>
