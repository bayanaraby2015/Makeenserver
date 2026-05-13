@php
    $data = $this->getDashboardData();
    $hero = $data['hero'] ?? [];
    $completion = max(0, min(100, (int) ($hero['completion'] ?? 0)));
@endphp

<x-filament-widgets::widget>
    <style>
        .mk-admin-dash {
            direction: rtl;
            display: grid;
            gap: 16px;
        }

        .mk-admin-dash * {
            box-sizing: border-box;
        }

        .mk-admin-dash__hero {
            background:
                linear-gradient(135deg, rgba(40, 57, 121, .98), rgba(43, 53, 79, .98)),
                radial-gradient(circle at 12% 10%, rgba(33, 178, 184, .3), transparent 30%);
            border-radius: 18px;
            box-shadow: 0 26px 60px rgba(40, 57, 121, .18);
            color: #fff;
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1.5fr) minmax(260px, .75fr);
            overflow: hidden;
            padding: 22px;
            position: relative;
        }

        .mk-admin-dash__hero::before {
            animation: mkAdminPulse 2.6s ease-in-out infinite;
            background: #f9ad1c;
            border-radius: 999px;
            content: "";
            height: 8px;
            inset-inline-start: 22px;
            position: absolute;
            top: 0;
            width: 150px;
        }

        .mk-admin-dash__hero h2 {
            font-size: 26px;
            font-weight: 900;
            line-height: 1.25;
            margin: 0;
        }

        .mk-admin-dash__hero p {
            color: rgba(255, 255, 255, .76);
            font-size: 13px;
            margin: 8px 0 0;
            max-width: 720px;
        }

        .mk-admin-dash__hero-stats {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 18px;
        }

        .mk-admin-dash__hero-stat {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 14px;
            padding: 12px;
        }

        .mk-admin-dash__label {
            color: rgba(255, 255, 255, .68);
            display: block;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .mk-admin-dash__value {
            display: block;
            font-size: 20px;
            font-weight: 900;
            line-height: 1.2;
        }

        .mk-admin-dash__radial {
            align-items: center;
            align-self: stretch;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 16px;
            display: grid;
            justify-items: center;
            min-height: 190px;
            padding: 18px;
            text-align: center;
        }

        .mk-admin-dash__ring {
            align-items: center;
            background: conic-gradient(#21b2b8 {{ $completion }}%, rgba(255, 255, 255, .16) 0);
            border-radius: 999px;
            display: grid;
            height: 128px;
            place-items: center;
            position: relative;
            width: 128px;
        }

        .mk-admin-dash__ring::after {
            background: #283979;
            border-radius: inherit;
            content: "";
            height: 94px;
            position: absolute;
            width: 94px;
        }

        .mk-admin-dash__ring strong,
        .mk-admin-dash__ring span {
            position: relative;
            z-index: 1;
        }

        .mk-admin-dash__ring strong {
            font-size: 25px;
            font-weight: 900;
        }

        .mk-admin-dash__ring span {
            color: rgba(255, 255, 255, .72);
            font-size: 10px;
            font-weight: 800;
            margin-top: -12px;
        }

        .mk-admin-dash__overview,
        .mk-admin-dash__queue {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .mk-admin-dash__mini,
        .mk-admin-dash__queue-item,
        .mk-admin-dash__panel {
            background: #fff;
            border: 1px solid #e6edf4;
            border-radius: 14px;
            box-shadow: 0 14px 34px rgba(43, 53, 79, .06);
        }

        .mk-admin-dash__mini {
            min-height: 116px;
            overflow: hidden;
            padding: 14px;
            position: relative;
        }

        .mk-admin-dash__mini::before {
            border-radius: 999px;
            content: "";
            height: 56px;
            inset-inline-start: -14px;
            opacity: .12;
            position: absolute;
            top: -14px;
            width: 56px;
        }

        .mk-admin-dash__mini[data-tone="navy"]::before { background: #283979; }
        .mk-admin-dash__mini[data-tone="teal"]::before { background: #21b2b8; }
        .mk-admin-dash__mini[data-tone="slate"]::before { background: #2b354f; }
        .mk-admin-dash__mini[data-tone="amber"]::before { background: #f9ad1c; }

        .mk-admin-dash__mini span,
        .mk-admin-dash__queue-item span,
        .mk-admin-dash__row span {
            color: #667085;
            display: block;
            font-size: 12px;
            font-weight: 800;
        }

        .mk-admin-dash__mini strong {
            color: #283979;
            display: block;
            font-size: 26px;
            font-weight: 900;
            margin-top: 10px;
        }

        .mk-admin-dash__mini small {
            color: #8a94a6;
            display: block;
            font-size: 11px;
            margin-top: 4px;
        }

        .mk-admin-dash__section-title {
            align-items: center;
            color: #24304a;
            display: flex;
            font-size: 16px;
            font-weight: 900;
            gap: 8px;
            justify-content: space-between;
            margin: 4px 0 0;
        }

        .mk-admin-dash__section-title small {
            color: #7b8496;
            font-size: 11px;
            font-weight: 800;
        }

        .mk-admin-dash__queue-item {
            align-items: center;
            display: flex;
            justify-content: space-between;
            min-height: 74px;
            padding: 14px;
        }

        .mk-admin-dash__queue-item strong {
            align-items: center;
            background: #eef6ff;
            border-radius: 999px;
            color: #283979;
            display: inline-flex;
            font-size: 18px;
            font-weight: 900;
            height: 42px;
            justify-content: center;
            min-width: 42px;
            padding: 0 12px;
        }

        .mk-admin-dash__queue-item[data-status="warning"] strong { background: #fff7e6; color: #b7791f; }
        .mk-admin-dash__queue-item[data-status="danger"] strong { background: #fff1f2; color: #be123c; }
        .mk-admin-dash__queue-item[data-status="success"] strong { background: #ecfdf3; color: #15803d; }

        .mk-admin-dash__grid {
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
        }

        .mk-admin-dash__panel {
            overflow: hidden;
        }

        .mk-admin-dash__panel-head {
            align-items: center;
            background: linear-gradient(90deg, rgba(33, 178, 184, .1), rgba(249, 173, 28, .08));
            border-bottom: 1px solid #e6edf4;
            display: flex;
            justify-content: space-between;
            padding: 14px 16px;
        }

        .mk-admin-dash__panel-head strong {
            color: #283979;
            font-size: 15px;
            font-weight: 900;
        }

        .mk-admin-dash__panel-body {
            display: grid;
            gap: 14px;
            padding: 16px;
        }

        .mk-admin-dash__pipeline {
            display: grid;
            gap: 8px;
        }

        .mk-admin-dash__pipeline-title {
            color: #2b354f;
            font-size: 13px;
            font-weight: 900;
        }

        .mk-admin-dash__bar-row {
            display: grid;
            gap: 8px;
        }

        .mk-admin-dash__bar-meta {
            align-items: center;
            color: #667085;
            display: flex;
            font-size: 11px;
            font-weight: 800;
            justify-content: space-between;
        }

        .mk-admin-dash__bar {
            background: #eef2f7;
            border-radius: 999px;
            height: 9px;
            overflow: hidden;
        }

        .mk-admin-dash__bar span {
            animation: mkAdminGrow .8s ease-out both;
            background: linear-gradient(90deg, #283979, #21b2b8);
            border-radius: inherit;
            display: block;
            height: 100%;
            min-width: 7px;
        }

        .mk-admin-dash__table,
        .mk-admin-dash__activity {
            display: grid;
            gap: 8px;
        }

        .mk-admin-dash__row {
            align-items: center;
            border: 1px solid #edf1f6;
            border-radius: 12px;
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(0, 1fr) auto auto;
            padding: 10px 12px;
        }

        .mk-admin-dash__row strong {
            color: #25304a;
            font-size: 13px;
            font-weight: 900;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mk-admin-dash__badge {
            background: #f5f7fb;
            border: 1px solid #e5eaf2;
            border-radius: 999px;
            color: #283979;
            font-size: 11px;
            font-weight: 900;
            padding: 5px 9px;
            white-space: nowrap;
        }

        .mk-admin-dash__activity-item {
            border-inline-start: 3px solid #21b2b8;
            display: grid;
            gap: 4px;
            padding: 2px 10px 10px 0;
        }

        .mk-admin-dash__activity-item strong {
            color: #283979;
            font-size: 12px;
            font-weight: 900;
        }

        .mk-admin-dash__activity-item p {
            color: #3e485f;
            font-size: 12px;
            margin: 0;
        }

        .mk-admin-dash__activity-item small {
            color: #8a94a6;
            font-size: 11px;
        }

        @keyframes mkAdminPulse {
            0%, 100% { opacity: .9; transform: scaleX(1); }
            50% { opacity: 1; transform: scaleX(1.12); }
        }

        @keyframes mkAdminGrow {
            from { width: 0; }
        }

        @media (max-width: 1100px) {
            .mk-admin-dash__hero,
            .mk-admin-dash__grid {
                grid-template-columns: 1fr;
            }

            .mk-admin-dash__overview,
            .mk-admin-dash__queue {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .mk-admin-dash__hero-stats,
            .mk-admin-dash__overview,
            .mk-admin-dash__queue {
                grid-template-columns: 1fr;
            }

            .mk-admin-dash__row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="mk-admin-dash">
        <section class="mk-admin-dash__hero">
            <div>
                <h2>{{ $hero['title'] ?? '' }}</h2>
                <p>{{ $hero['subtitle'] ?? '' }}</p>

                <div class="mk-admin-dash__hero-stats">
                    <div class="mk-admin-dash__hero-stat">
                        <span class="mk-admin-dash__label">إجمالي الميزانية</span>
                        <strong class="mk-admin-dash__value">{{ $hero['total_budget'] ?? '-' }}</strong>
                    </div>
                    <div class="mk-admin-dash__hero-stat">
                        <span class="mk-admin-dash__label">دفعات خلال 30 يوم</span>
                        <strong class="mk-admin-dash__value">{{ $hero['upcoming_payments'] ?? '-' }}</strong>
                    </div>
                    <div class="mk-admin-dash__hero-stat">
                        <span class="mk-admin-dash__label">متوسط رضا الخدمة</span>
                        <strong class="mk-admin-dash__value">{{ $hero['rating'] ?? '0/5' }}</strong>
                    </div>
                </div>
            </div>

            <div class="mk-admin-dash__radial">
                <div class="mk-admin-dash__ring">
                    <strong>{{ $completion }}%</strong>
                    <span>الاعتماد</span>
                </div>
                <span class="mk-admin-dash__label">نسبة الاعتماد العام</span>
            </div>
        </section>

        <div class="mk-admin-dash__overview">
            @foreach (($data['overview'] ?? []) as $card)
                <div class="mk-admin-dash__mini" data-tone="{{ $card['tone'] ?? 'navy' }}">
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                    <small>{{ $card['hint'] }}</small>
                </div>
            @endforeach
        </div>

        <div class="mk-admin-dash__section-title">
            <span>أولويات تحتاج متابعة</span>
            <small>تحديث مباشر من بيانات النظام</small>
        </div>

        <div class="mk-admin-dash__queue">
            @foreach (($data['queues'] ?? []) as $item)
                <div class="mk-admin-dash__queue-item" data-status="{{ $item['status'] ?? 'info' }}">
                    <span>{{ $item['label'] }}</span>
                    <strong>{{ $item['value'] }}</strong>
                </div>
            @endforeach
        </div>

        <div class="mk-admin-dash__grid">
            <section class="mk-admin-dash__panel">
                <div class="mk-admin-dash__panel-head">
                    <strong>مسارات التنفيذ</strong>
                    <span class="mk-admin-dash__badge">إنفوجرافيك الحالات</span>
                </div>
                <div class="mk-admin-dash__panel-body">
                    @foreach (($data['pipelines'] ?? []) as $pipeline)
                        <div class="mk-admin-dash__pipeline">
                            <div class="mk-admin-dash__pipeline-title">{{ $pipeline['title'] }}</div>
                            @foreach (($pipeline['items'] ?? []) as $item)
                                <div class="mk-admin-dash__bar-row">
                                    <div class="mk-admin-dash__bar-meta">
                                        <span>{{ $item['label'] }}</span>
                                        <strong>{{ $item['value'] }} | {{ $item['percentage'] }}%</strong>
                                    </div>
                                    <div class="mk-admin-dash__bar">
                                        <span style="width: {{ max(4, (int) $item['percentage']) }}%"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="mk-admin-dash__panel">
                <div class="mk-admin-dash__panel-head">
                    <strong>الجهات الأكثر نشاطا</strong>
                    <span class="mk-admin-dash__badge">متابعة تشغيلية</span>
                </div>
                <div class="mk-admin-dash__panel-body">
                    <div class="mk-admin-dash__table">
                        @forelse (($data['organizations'] ?? []) as $organization)
                            <div class="mk-admin-dash__row">
                                <strong>{{ $organization['name'] }}</strong>
                                <span class="mk-admin-dash__badge">{{ $organization['initiatives'] }} مبادرات</span>
                                <span class="mk-admin-dash__badge">{{ $organization['tickets'] }} مفتوحة</span>
                            </div>
                        @empty
                            <div class="mk-admin-dash__row">
                                <strong>لا توجد جهات بعد</strong>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <section class="mk-admin-dash__panel">
            <div class="mk-admin-dash__panel-head">
                <strong>نبض النشاط الأخير</strong>
                <span class="mk-admin-dash__badge">سجل النشاط</span>
            </div>
            <div class="mk-admin-dash__panel-body">
                <div class="mk-admin-dash__activity">
                    @forelse (($data['activity'] ?? []) as $activity)
                        <div class="mk-admin-dash__activity-item">
                            <strong>{{ $activity['log'] }} | {{ $activity['causer'] }}</strong>
                            <p>{{ $activity['description'] }}</p>
                            <small>{{ $activity['time'] }}</small>
                        </div>
                    @empty
                        <div class="mk-admin-dash__activity-item">
                            <strong>لا توجد أنشطة مسجلة بعد</strong>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-filament-widgets::widget>
