@php
    $data = $this->getDashboardData();
    $tone = $data['tone'] ?? 'admin';
    $progress = max(0, min(100, (int) ($data['progress'] ?? 0)));
    $toneColors = [
        'admin' => ['#283979', '#21b2b8'],
        'association' => ['#283979', '#f9ad1c'],
        'consultant' => ['#2b354f', '#21b2b8'],
        'excellence' => ['#283979', '#21b2b8'],
    ];
    [$primary, $accent] = $toneColors[$tone] ?? $toneColors['admin'];
@endphp

<x-filament-widgets::widget>
    <style>
        .mk-exec {
            direction: rtl;
            display: grid;
            gap: 16px;
        }

        .mk-exec * {
            box-sizing: border-box;
        }

        .mk-exec__hero {
            background:
                radial-gradient(circle at 8% 0%, color-mix(in srgb, {{ $accent }} 24%, transparent), transparent 30%),
                linear-gradient(135deg, {{ $primary }}, #2b354f);
            border-radius: 18px;
            box-shadow: 0 24px 55px rgba(40, 57, 121, .16);
            color: #fff;
            overflow: hidden;
            padding: 20px;
            position: relative;
        }

        .mk-exec__hero::before {
            animation: mkPulse 2.4s ease-in-out infinite;
            background: #f9ad1c;
            border-radius: 999px;
            content: "";
            height: 8px;
            inset-inline-start: 22px;
            position: absolute;
            top: 0;
            width: 130px;
        }

        .mk-exec__top {
            align-items: stretch;
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
        }

        .mk-exec__title h2 {
            font-size: 24px;
            font-weight: 900;
            line-height: 1.3;
            margin: 0;
        }

        .mk-exec__title p {
            color: rgba(255, 255, 255, .78);
            font-size: 13px;
            line-height: 1.7;
            margin: 7px 0 0;
            max-width: 760px;
        }

        .mk-exec__progress {
            align-content: center;
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 16px;
            display: grid;
            gap: 11px;
            padding: 16px;
        }

        .mk-exec__progress-head {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }

        .mk-exec__progress-head span {
            color: rgba(255, 255, 255, .74);
            font-size: 12px;
            font-weight: 900;
        }

        .mk-exec__progress-head strong {
            background: #fff;
            border-radius: 999px;
            color: {{ $primary }};
            direction: ltr;
            font-size: 24px;
            font-weight: 900;
            line-height: 1;
            padding: 9px 13px;
            white-space: nowrap;
        }

        .mk-exec__progress-track {
            background: rgba(255, 255, 255, .16);
            border-radius: 999px;
            height: 12px;
            overflow: hidden;
        }

        .mk-exec__progress-track span {
            animation: mkGrow .8s ease-out both;
            background: linear-gradient(90deg, {{ $accent }}, #f9ad1c);
            border-radius: inherit;
            display: block;
            height: 100%;
            min-width: 8px;
        }

        .mk-exec__progress small {
            color: rgba(255, 255, 255, .68);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.6;
        }

        .mk-exec__cards {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 16px;
        }

        .mk-exec__card {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 14px;
            padding: 12px;
            transition: transform .18s ease, background .18s ease;
        }

        .mk-exec__card:hover {
            background: rgba(255, 255, 255, .17);
            transform: translateY(-2px);
        }

        .mk-exec__card span,
        .mk-exec__alert span {
            color: rgba(255, 255, 255, .72);
            display: block;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .mk-exec__card strong {
            display: block;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.2;
        }

        .mk-exec__card small {
            color: rgba(255, 255, 255, .72);
            display: block;
            font-size: 11px;
            margin-top: 4px;
        }

        .mk-exec__alerts {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 10px;
        }

        .mk-exec__alert {
            align-items: center;
            background: #fff;
            border: 1px solid #e7ecf3;
            border-radius: 14px;
            box-shadow: 0 14px 34px rgba(43, 53, 79, .07);
            display: flex;
            justify-content: space-between;
            padding: 14px 16px;
        }

        .mk-exec__alert span {
            color: #647085;
            margin: 0;
        }

        .mk-exec__alert strong {
            color: #283979;
            font-size: 22px;
            font-weight: 900;
        }

        .mk-exec__alert[data-status="warning"] strong { color: #b7791f; }
        .mk-exec__alert[data-status="success"] strong { color: #15803d; }
        .mk-exec__alert[data-status="info"] strong { color: #0f766e; }

        @keyframes mkPulse {
            0%, 100% { opacity: .9; transform: scaleX(1); }
            50% { opacity: 1; transform: scaleX(1.12); }
        }

        @keyframes mkGrow {
            from { width: 0; }
        }

        @media (max-width: 980px) {
            .mk-exec__top,
            .mk-exec__cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .mk-exec__top,
            .mk-exec__cards,
            .mk-exec__alerts {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="mk-exec">
        <section class="mk-exec__hero">
            <div class="mk-exec__top">
                <div class="mk-exec__title">
                    <h2>{{ $data['title'] }}</h2>
                    <p>{{ $data['subtitle'] }}</p>
                </div>

                <div class="mk-exec__progress">
                    <div class="mk-exec__progress-head">
                        <span>{{ $data['progressLabel'] ?? 'نسبة الاعتماد العام' }}</span>
                        <strong>{{ $progress }}%</strong>
                    </div>
                    <div class="mk-exec__progress-track">
                        <span style="width: {{ max(4, $progress) }}%"></span>
                    </div>
                    <small>مؤشر سريع يوضح حجم الاعتماد مقارنة بإجمالي العناصر المسجلة في هذا المسار.</small>
                </div>
            </div>

            <div class="mk-exec__cards">
                @foreach (($data['cards'] ?? []) as $card)
                    <div class="mk-exec__card">
                        <span>{{ $card['label'] }}</span>
                        <strong>{{ $card['value'] }}</strong>
                        <small>{{ $card['hint'] }}</small>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="mk-exec__alerts">
            @foreach (($data['alerts'] ?? []) as $alert)
                <div class="mk-exec__alert" data-status="{{ $alert['status'] ?? 'info' }}">
                    <span>{{ $alert['label'] }}</span>
                    <strong>{{ $alert['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
