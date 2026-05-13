<x-filament-panels::page>
    @php
        $gantt = $this->getGanttData();
        $events = $this->getCalendarEvents();
        $initiative = $this->getRecord();

        $totalDays = $gantt['range']['days'];

        $ganttLabels = [
            'today' => __('initiatives.gantt.today'),
            'status_open' => __('initiatives.gantt.statuses.open'),
            'status_in_progress' => __('initiatives.gantt.statuses.in_progress'),
            'status_done' => __('initiatives.gantt.statuses.done'),
            'tooltip_status' => __('initiatives.gantt.tooltip.status'),
            'tooltip_start' => __('initiatives.gantt.tooltip.start'),
            'tooltip_end' => __('initiatives.gantt.tooltip.end'),
            'tooltip_duration' => __('initiatives.gantt.tooltip.duration'),
            'tooltip_days' => __('initiatives.gantt.tooltip.days'),
            'tooltip_progress' => __('initiatives.gantt.tooltip.progress'),
            'tooltip_cost' => __('initiatives.gantt.tooltip.cost'),
            'payment' => __('initiatives.calendar.payment'),
            'percentage' => __('initiatives.fields.percentage'),
            'amount' => __('initiatives.fields.amount'),
            'currency' => 'ر.س',
            'quarter_prefix' => __('initiatives.gantt.quarter_prefix'),
            'calendar_label' => __('initiatives.calendar.label'),
            'calendar_gregorian' => __('initiatives.calendar.gregorian'),
            'calendar_hijri' => __('initiatives.calendar.hijri'),
            'calendar_gregorian_title' => __('initiatives.calendar.gregorian_title'),
            'calendar_hijri_title' => __('initiatives.calendar.hijri_title'),
            'render_error' => __('initiatives.gantt.render_error'),
            'cal_today' => __('initiatives.calendar.today'),
            'cal_month' => __('initiatives.gantt.view_modes.month'),
            'cal_week' => __('initiatives.gantt.view_modes.week'),
            'cal_day' => __('initiatives.gantt.view_modes.day'),
            'cal_list' => __('initiatives.calendar.list'),
        ];
    @endphp

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">

    <style>
        :root {
            --maken-navy: #283979;
            --maken-navy-soft: #4256a3;
            --maken-gold: #b88a3a;
            --maken-ink: #162033;
            --maken-muted: #64748b;
            --maken-line: #e7ecf3;
            --maken-surface: #ffffff;
            --maken-success: #10b981;
            --maken-warning: #f59e0b;
            --maken-danger: #ef4444;
            --row-h: 48px;
            --header-h: 64px;
            --task-w: 340px;
        }

        .timeline-shell { display: grid; gap: 1.25rem; color: var(--maken-ink); }

        .timeline-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 1rem;
        }
        .timeline-summary > div {
            position: relative; isolation: isolate; overflow: hidden; min-height: 112px;
            background: linear-gradient(135deg, rgba(40,57,121,.06), rgba(184,138,58,.08)), var(--maken-surface);
            border: 1px solid rgba(226,232,240,.95); border-radius: .85rem;
            padding: 1rem 1.05rem; text-align: start;
            box-shadow: 0 14px 32px rgba(15,23,42,.06);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            animation: timelineFadeUp .42s ease both;
        }
        .timeline-summary > div::before {
            content: ''; position: absolute; inset-block-start: 0; inset-inline: 0; height: 3px;
            background: linear-gradient(90deg, var(--maken-gold), var(--maken-navy-soft));
        }
        .timeline-summary > div::after {
            content: ''; position: absolute; inset-inline-end: -28px; inset-block-end: -32px;
            width: 96px; height: 96px; border-radius: 999px; background: rgba(40,57,121,.07); z-index: -1;
        }
        .timeline-summary > div:hover { transform: translateY(-3px); border-color: rgba(184,138,58,.35); box-shadow: 0 22px 45px rgba(15,23,42,.1); }
        .timeline-summary .label { display: flex; align-items: center; gap: .4rem; min-height: 1.25rem; font-size: .76rem; font-weight: 800; color: var(--maken-muted); margin-bottom: .6rem; }
        .timeline-summary .label::before { content: ''; width: .55rem; height: .55rem; flex: 0 0 .55rem; border-radius: 999px; background: var(--maken-gold); box-shadow: 0 0 0 4px rgba(184,138,58,.14); }
        .timeline-summary .value { font-size: clamp(1.25rem, 2vw, 1.75rem); font-weight: 850; line-height: 1.1; color: var(--maken-navy); letter-spacing: 0; }

        .gantt-wrap {
            background: rgba(255,255,255,.94); border: 1px solid rgba(226,232,240,.95); border-radius: .95rem;
            overflow: hidden; box-shadow: 0 18px 48px rgba(15,23,42,.08); backdrop-filter: blur(10px);
            animation: timelineFadeUp .46s ease both;
        }
        .gantt-header {
            position: relative; padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--maken-line);
            display: flex; flex-wrap: wrap; gap: .9rem; justify-content: space-between; align-items: flex-start;
            background: linear-gradient(135deg, rgba(40,57,121,.06), rgba(184,138,58,.06)), #fff;
        }
        .gantt-header::before { content: ''; position: absolute; inset-block: 0; inset-inline-start: 0; width: 4px; background: linear-gradient(180deg, var(--maken-navy), var(--maken-gold)); }
        .gantt-header h2 { letter-spacing: 0; }

        .gantt-toolbar {
            display: flex; flex-wrap: wrap; gap: .45rem; align-items: center; padding: .25rem;
            border: 1px solid rgba(226,232,240,.9); border-radius: .7rem; background: rgba(255,255,255,.76);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
        }
        .gantt-toolbar button {
            min-height: 1.95rem; padding: .32rem .68rem; border-radius: .5rem; font-size: .76rem;
            background: transparent; border: 1px solid transparent; cursor: pointer; color: #334155;
            transition: transform .16s ease, background .16s ease, color .16s ease, box-shadow .16s ease;
            font-weight: 750; white-space: nowrap;
        }
        .gantt-toolbar button:hover { transform: translateY(-1px); background: #fff; color: var(--maken-navy); box-shadow: 0 8px 18px rgba(15,23,42,.08); }
        .gantt-toolbar button.is-active { background: linear-gradient(135deg, var(--maken-navy), var(--maken-navy-soft)); color: #fff; border-color: rgba(40,57,121,.2); box-shadow: 0 10px 22px rgba(40,57,121,.24); }
        .gantt-toolbar .group-label { font-size: .68rem; color: var(--maken-muted); margin-inline: .3rem .15rem; font-weight: 800; }

        .gantt-body { display: flex; direction: rtl; background: #fff; }
        .gantt-task-panel { flex: 0 0 var(--task-w); background: #fff; border-inline-end: 1px solid var(--maken-line); position: relative; z-index: 2; box-shadow: -12px 0 24px rgba(15,23,42,.04); }
        .gantt-task-header, .gantt-task-row { display: grid; grid-template-columns: 42px minmax(0, 1fr) 126px; align-items: center; padding: 0 .85rem; gap: .55rem; }
        .gantt-task-header { height: var(--header-h); border-bottom: 1px solid var(--maken-line); font-size: .72rem; font-weight: 850; color: var(--maken-muted); background: #f8fafc; }
        .gantt-task-row { height: var(--row-h); border-bottom: 1px solid #f1f5f9; font-size: .86rem; transition: background .16s ease, box-shadow .16s ease; }
        .gantt-task-row:hover, .gantt-task-row.is-hovered { background: #fff8ed; box-shadow: inset 3px 0 0 var(--maken-gold); }
        .gantt-task-row.is-overall { background: linear-gradient(90deg, rgba(40,57,121,.12), rgba(184,138,58,.1)); font-weight: 850; color: var(--maken-navy); }
        .gantt-task-row .num { display: inline-grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 999px; background: #f1f5f9; color: #64748b; font-weight: 850; font-size: .78rem; }
        .gantt-task-row .name { font-weight: 750; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gantt-task-row.is-overall .name { color: var(--maken-navy); }

        .status-badge { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; max-width: 100%; padding: .22rem .58rem; border-radius: 999px; font-size: .73rem; font-weight: 800; border: 1px solid transparent; white-space: nowrap; }
        .status-badge .dot { width: 7px; height: 7px; border-radius: 50%; box-shadow: 0 0 0 3px currentColor; opacity: .72; }
        .status-badge.open { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }
        .status-badge.open .dot { background: #64748b; }
        .status-badge.in_progress { background: #fff7ed; color: #b45309; border-color: #fed7aa; }
        .status-badge.in_progress .dot { background: #f59e0b; }
        .status-badge.done { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .status-badge.done .dot { background: #10b981; }

        .gantt-chart-panel { flex: 1 1 auto; overflow-x: auto; overflow-y: hidden; direction: ltr; background: #fff; scroll-behavior: smooth; }
        .gantt-chart-panel svg { display: block; user-select: none; }
        .gantt-chart-panel::-webkit-scrollbar { height: 10px; }
        .gantt-chart-panel::-webkit-scrollbar-track { background: #f1f5f9; }
        .gantt-chart-panel::-webkit-scrollbar-thumb { background: linear-gradient(90deg, var(--maken-gold), var(--maken-navy-soft)); border-radius: 999px; border: 2px solid #f1f5f9; }

        .gantt-legend { display: flex; flex-wrap: wrap; gap: .8rem 1.1rem; padding: .95rem 1.25rem; border-top: 1px solid var(--maken-line); font-size: .8rem; color: #475569; background: #f8fafc; }
        .gantt-legend > div { display: flex; align-items: center; gap: .5rem; min-height: 1.8rem; padding: .25rem .55rem; border: 1px solid rgba(226,232,240,.9); border-radius: 999px; background: rgba(255,255,255,.75); }
        .gantt-legend .swatch { width: 14px; height: 14px; border-radius: 4px; display: inline-block; }
        .gantt-legend .swatch.diamond { transform: rotate(45deg); border-radius: 3px; width: 11px; height: 11px; }
        .gantt-legend .swatch.line { width: 20px; height: 2px; background: var(--maken-danger); }
        .gantt-legend .swatch.line::after { content: ''; display: block; width: 20px; height: 2px; }

        .gantt-tooltip { position: fixed; pointer-events: none; z-index: 9999; background: rgba(15,23,42,.94); color: #fff; padding: .72rem .9rem; border: 1px solid rgba(255,255,255,.1); border-radius: .7rem; font-size: .8125rem; line-height: 1.55; box-shadow: 0 22px 45px rgba(15,23,42,.32); max-width: min(310px, calc(100vw - 24px)); direction: rtl; text-align: start; opacity: 0; transform: translateY(4px) scale(.98); transition: opacity .14s ease, transform .14s ease; backdrop-filter: blur(12px); }
        .gantt-tooltip.is-visible { opacity: 1; transform: translateY(0) scale(1); }
        .gantt-tooltip strong { color: #fde68a; font-weight: 850; display: block; margin-bottom: .32rem; }
        .gantt-tooltip .row { display: flex; justify-content: space-between; gap: .9rem; }
        .gantt-tooltip .row .key { color: #cbd5e1; }

        .gantt-empty { padding: 3.5rem 1.5rem; text-align: center; color: #64748b; background: linear-gradient(135deg, #f8fafc, #fff8ed); font-weight: 750; }
        .px-4.py-3 { background: #fff; }
        #initiative-calendar { border-radius: .75rem; overflow: hidden; }
        .fc { --fc-border-color: #e7ecf3; --fc-small-font-size: .78rem; }
        .fc .fc-toolbar { gap: .75rem; flex-wrap: wrap; }
        .fc-toolbar-title { font-weight: 850 !important; color: var(--maken-navy); font-size: 1rem !important; }
        .fc .fc-button { border-radius: .5rem !important; box-shadow: none !important; font-weight: 750 !important; font-size: .78rem !important; padding: .32rem .62rem !important; transition: transform .16s ease, box-shadow .16s ease, background .16s ease !important; }
        .fc .fc-button-primary { background-color: var(--maken-navy) !important; border-color: var(--maken-navy) !important; color: #fff !important; }
        .fc .fc-button-primary:hover, .fc .fc-button-primary:not(:disabled).fc-button-active { transform: translateY(-1px); background-color: var(--maken-navy-soft) !important; border-color: var(--maken-navy-soft) !important; box-shadow: 0 10px 20px rgba(40,57,121,.2) !important; }
        .fc .fc-daygrid-day-frame { padding: .18rem; }
        .fc .fc-daygrid-day-number { color: #334155; font-weight: 750; }
        .fc-day-today { background: rgba(184,138,58,.1) !important; }
        .fc-event { border: 0 !important; border-radius: .45rem !important; padding: .12rem .28rem !important; box-shadow: 0 8px 16px rgba(15,23,42,.1); }

        .dark .timeline-shell { color: #e5e7eb; }
        .dark .timeline-summary > div, .dark .gantt-wrap, .dark .gantt-body, .dark .gantt-task-panel, .dark .gantt-chart-panel, .dark .px-4.py-3 { background: #0f172a; border-color: #1e293b; }
        .dark .timeline-summary > div { background: linear-gradient(135deg, rgba(66,86,163,.18), rgba(184,138,58,.1)), #111827; }
        .dark .timeline-summary .label { color: #94a3b8; }
        .dark .timeline-summary .value { color: #bfdbfe; }
        .dark .gantt-header { background: linear-gradient(135deg, rgba(66,86,163,.2), rgba(184,138,58,.08)), #111827; border-color: #1e293b; }
        .dark .gantt-toolbar { background: rgba(15,23,42,.85); border-color: #263244; }
        .dark .gantt-toolbar button { color: #cbd5e1; }
        .dark .gantt-toolbar button:hover { background: #1e293b; color: #fff; }
        .dark .gantt-task-header { background: #111827; color: #94a3b8; border-color: #1e293b; }
        .dark .gantt-task-row { border-color: #1e293b; }
        .dark .gantt-task-row:hover, .dark .gantt-task-row.is-hovered { background: rgba(184,138,58,.12); }
        .dark .gantt-task-row .num { background: #1e293b; color: #cbd5e1; }
        .dark .gantt-task-row .name { color: #e5e7eb; }
        .dark .gantt-task-row.is-overall { background: rgba(66,86,163,.18); color: #c7d2fe; }
        .dark .status-badge.open { background: #1f2937; color: #d1d5db; border-color: #374151; }
        .dark .status-badge.in_progress { background: #422006; color: #fde68a; border-color: #78350f; }
        .dark .status-badge.done { background: #064e3b; color: #a7f3d0; border-color: #047857; }
        .dark .gantt-legend { background: #111827; color: #cbd5e1; border-color: #1e293b; }
        .dark .gantt-legend > div { background: rgba(15,23,42,.8); border-color: #263244; }
        .dark .gantt-empty { background: #0b1220; color: #94a3b8; }
        .dark .fc { --fc-border-color: #1e293b; }
        .dark .fc-toolbar-title, .dark .fc .fc-daygrid-day-number { color: #dbeafe; }

        @keyframes timelineFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 900px) {
            :root { --task-w: 280px; --row-h: 50px; }
            .gantt-body { flex-direction: column; }
            .gantt-task-panel { flex-basis: auto; border-inline-end: 0; border-bottom: 1px solid var(--maken-line); box-shadow: none; }
            .gantt-task-header, .gantt-task-row { grid-template-columns: 36px minmax(0, 1fr) 104px; }
            .gantt-chart-panel { min-height: 260px; }
            .gantt-toolbar { width: 100%; }
        }
        @media (max-width: 640px) {
            .timeline-summary { grid-template-columns: 1fr; }
            .gantt-header { padding: 1rem; }
            .gantt-toolbar .group-label { width: 100%; }
            .gantt-toolbar button { flex: 1 1 auto; padding-inline: .5rem; }
            .status-badge { font-size: .68rem; padding-inline: .45rem; }
            .fc .fc-toolbar { display: grid; grid-template-columns: 1fr; }
            .fc .fc-toolbar-chunk { display: flex; justify-content: center; }
            #initiative-calendar { min-height: 500px !important; }
            #initiative-calendar .fc-toolbar-title { font-size: .95rem !important; }
            #initiative-calendar .fc-button { font-size: .72rem !important; padding: .28rem .48rem !important; }
            #initiative-calendar .fc-col-header-cell { min-width: 0; }
            #initiative-calendar .fc-col-header-cell-cushion {
                display: block;
                font-size: .62rem;
                font-weight: 850;
                line-height: 1.15;
                overflow: hidden;
                padding: .32rem .02rem;
                text-align: center;
                text-overflow: clip;
                white-space: nowrap;
            }
            #initiative-calendar .fc-daygrid-day-number {
                font-size: .82rem;
                padding: .22rem .28rem;
            }
            #initiative-calendar .fc-event {
                font-size: .68rem !important;
                line-height: 1.25 !important;
                margin-inline: 1px !important;
                padding: .1rem .22rem !important;
            }
            #initiative-calendar .fc-timegrid-axis,
            #initiative-calendar .fc-timegrid-slot-label {
                font-size: .7rem;
            }
            #initiative-calendar .mk-cal-day-short {
                display: block;
                font-size: .64rem;
                line-height: 1.05;
            }
            #initiative-calendar .mk-cal-day-date {
                color: #64748b;
                display: block;
                direction: ltr;
                font-size: .58rem;
                font-weight: 750;
                line-height: 1.05;
                margin-top: .1rem;
            }
        }
    </style>


    <div class="timeline-shell" dir="rtl">
    {{-- Summary cards --}}
    <div class="timeline-summary" dir="rtl">
        <div>
            <div class="label">{{ __('initiatives.timeline_summary.start') }}</div>
            <div class="value">{{ $initiative->start_date?->format('Y-m-d') ?? '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('initiatives.timeline_summary.end') }}</div>
            <div class="value">{{ $initiative->end_date?->format('Y-m-d') ?? '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('initiatives.timeline_summary.total_days') }}</div>
            <div class="value">{{ $totalDays !== null ? \App\Support\DisplayNumber::plain($totalDays) : '-' }}</div>
        </div>
        <div>
            <div class="label">{{ __('initiatives.timeline_summary.milestones_count') }}</div>
            <div class="value">{{ count($gantt['phases']) }}</div>
        </div>
        <div>
            <div class="label">{{ __('initiatives.timeline_summary.payments_count') }}</div>
            <div class="value">{{ count($gantt['payments']) }}</div>
        </div>
    </div>

    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Gantt chart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="gantt-wrap" dir="rtl">
        <div class="gantt-header">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('initiatives.gantt.title') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('initiatives.gantt.subtitle') }}</p>
            </div>
            @if (count($gantt['phases']) > 0)
                <div class="gantt-toolbar">
                    <span class="group-label">{{ __('initiatives.gantt.view_modes.day') }} / {{ __('initiatives.gantt.view_modes.week') }} / {{ __('initiatives.gantt.view_modes.month') }} / {{ __('initiatives.gantt.view_modes.quarter') }}:</span>
                    <button type="button" data-mode="day"     class="js-gantt-mode">{{ __('initiatives.gantt.view_modes.day') }}</button>
                    <button type="button" data-mode="week"    class="js-gantt-mode is-active">{{ __('initiatives.gantt.view_modes.week') }}</button>
                    <button type="button" data-mode="month"   class="js-gantt-mode">{{ __('initiatives.gantt.view_modes.month') }}</button>
                    <button type="button" data-mode="quarter" class="js-gantt-mode">{{ __('initiatives.gantt.view_modes.quarter') }}</button>
                    <button type="button" id="js-gantt-today" class="js-gantt-today" title="{{ __('initiatives.gantt.today') }}">{{ __('initiatives.gantt.today') }}</button>
                </div>
            @endif
        </div>

        @if (count($gantt['phases']) > 0)
            <div class="gantt-body">
                {{-- Task list panel (right side in RTL) --}}
                <div class="gantt-task-panel">
                    <div class="gantt-task-header">
                        <div>{{ __('initiatives.gantt.phase_number') }}</div>
                        <div>{{ __('initiatives.gantt.phase') }}</div>
                        <div>{{ __('initiatives.gantt.status') }}</div>
                    </div>

                    {{-- Overall initiative row --}}
                    <div class="gantt-task-row is-overall">
                        <div class="num">0</div>
                        <div class="name">{{ __('initiatives.gantt.overall_bar') }}</div>
                        <div></div>
                    </div>

                    @foreach ($gantt['phases'] as $idx => $phase)
                        <div class="gantt-task-row" data-phase-id="{{ $phase['id'] }}">
                            <div class="num">{{ $idx + 1 }}</div>
                            <div class="name" title="{{ $phase['name'] }}">{{ $phase['name'] }}</div>
                            <div>
                                <span class="status-badge {{ $phase['status'] }}">
                                    <span class="dot"></span>
                                    {{ __('initiatives.gantt.statuses.'.$phase['status']) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Chart panel (left side in RTL, but internally LTR for time) --}}
                <div class="gantt-chart-panel" id="gantt-chart-scroll">
                    <svg id="gantt-svg" xmlns="http://www.w3.org/2000/svg"></svg>
                </div>
            </div>

            <div class="gantt-legend" dir="rtl">
                <div><span class="swatch" style="background: var(--maken-navy);"></span>{{ __('initiatives.gantt.legend.milestone') }}</div>
                <div><span class="swatch" style="background: #9ca3af;"></span>{{ __('initiatives.gantt.legend.open') }}</div>
                <div><span class="swatch" style="background: #f59e0b;"></span>{{ __('initiatives.gantt.legend.in_progress') }}</div>
                <div><span class="swatch" style="background: #10b981;"></span>{{ __('initiatives.gantt.legend.done') }}</div>
                <div><span class="swatch diamond" style="background: #b88a3a;"></span>{{ __('initiatives.gantt.legend.payment') }}</div>
                <div><span class="swatch line" style="background: #ef4444;"></span>{{ __('initiatives.gantt.legend.today') }}</div>
            </div>
        @else
            <div class="gantt-empty">{{ __('initiatives.gantt.no_data') }}</div>
        @endif
    </div>

    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Calendar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="gantt-wrap mt-6" dir="rtl">
        <div class="gantt-header">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('initiatives.calendar.title') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('initiatives.calendar.subtitle') }}</p>
            </div>
            <div class="gantt-toolbar">
                <span class="group-label">{{ __('initiatives.calendar.label') }}</span>
                    <button type="button" id="js-cal-gregorian" class="is-active">{{ __('initiatives.calendar.gregorian') }}</button>
                    <button type="button" id="js-cal-hijri">{{ __('initiatives.calendar.hijri') }}</button>
            </div>
        </div>
        <div class="px-4 py-3">
            <div id="initiative-calendar" style="direction: ltr; min-height: 520px;"></div>
        </div>
    </div>
    </div>

    <div class="gantt-tooltip" id="gantt-tooltip"></div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script data-navigate-once="false">
    (function () {
        'use strict';
        function bootGantt() {
        try {

        const data    = @json($gantt);
        const events  = @json($events);
        const LABELS  = @json($ganttLabels);
        const locale  = @json(app()->getLocale() === 'ar' ? 'ar-SA-u-nu-latn' : 'en-US');

        const HEADER_H = 64;
        const ROW_H = 48;
        const BAR_H = 26;
        const BAR_Y_OFFSET = (ROW_H - BAR_H) / 2;
        const PAYMENT_ROW_H = 48;

        const MONTHS_LONG = Array.from({ length: 12 }, (_, monthIndex) =>
            new Intl.DateTimeFormat(locale, { month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(2024, monthIndex, 1)))
        );
        const MONTHS_SHORT = Array.from({ length: 12 }, (_, monthIndex) =>
            new Intl.DateTimeFormat(locale, { month: 'short', timeZone: 'UTC' }).format(new Date(Date.UTC(2024, monthIndex, 1)))
        ); // Sun-Sat

        const VIEW_MODES = {
            day:     { dayWidth: 36, name: 'day' },
            week:    { dayWidth: 14, name: 'week' },
            month:   { dayWidth: 5,  name: 'month' },
            quarter: { dayWidth: 2.2, name: 'quarter' },
        };

        function parseDate(s) {
            // YYYY-MM-DD â†’ Date at UTC midnight (avoid TZ off-by-one)
            const [y,m,d] = s.split('-').map(Number);
            return new Date(Date.UTC(y, m-1, d));
        }
        function addDays(d, n) {
            const r = new Date(d.getTime());
            r.setUTCDate(r.getUTCDate() + n);
            return r;
        }
        function diffDays(a, b) {
            return Math.round((b.getTime() - a.getTime()) / (1000*60*60*24));
        }
        function formatDate(d) {
            return d.getUTCFullYear() + '-' + String(d.getUTCMonth()+1).padStart(2,'0') + '-' + String(d.getUTCDate()).padStart(2,'0');
        }
        function formatNumber(value, suffix = '') {
            if (value === null || value === undefined || value === '') return '-';
            const numeric = Number(String(value).replace(/,/g, ''));
            if (!Number.isFinite(numeric)) return String(value);
            const rendered = new Intl.NumberFormat('en-US', {
                useGrouping: true,
                maximumFractionDigits: 0,
            }).format(numeric);
            return suffix ? `${suffix} ${rendered}` : rendered;
        }
        function svg(tag, attrs, parent) {
            const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
            if (attrs) for (const k in attrs) el.setAttribute(k, attrs[k]);
            if (parent) parent.appendChild(el);
            return el;
        }

        if (!data.phases || data.phases.length === 0) return;

        const rangeStart = parseDate(data.range.start);
        const rangeEnd   = parseDate(data.range.end);
        // Pad 3 days on each side for breathing room
        const chartStart = addDays(rangeStart, -3);
        const chartEnd   = addDays(rangeEnd, 3);
        const totalDays  = diffDays(chartStart, chartEnd) + 1;
        const today      = parseDate(data.today);

        let currentMode = 'week';
        const svgEl     = document.getElementById('gantt-svg');
        const tooltip   = document.getElementById('gantt-tooltip');
        const scrollEl  = document.getElementById('gantt-chart-scroll');

        function dateX(d, dayWidth) {
            return diffDays(chartStart, d) * dayWidth;
        }

        function render(mode) {
            currentMode = mode;
            // Auto-fit: when chart would be narrower than container, stretch dayWidth
            // so the bars fill the full available width.
            const baseDW = VIEW_MODES[mode].dayWidth;
            const containerW = scrollEl ? scrollEl.clientWidth : 0;
            const minDW = containerW > 0 ? containerW / totalDays : baseDW;
            const dayWidth = Math.max(baseDW, minDW);
            const chartWidth = totalDays * dayWidth;
            const overallRowY = HEADER_H;
            const phaseRowsY  = overallRowY + ROW_H;
            const paymentRowY = phaseRowsY + data.phases.length * ROW_H;
            const totalHeight = paymentRowY + (data.payments.length > 0 ? PAYMENT_ROW_H : 0);

            svgEl.innerHTML = '';
            svgEl.setAttribute('width', chartWidth);
            svgEl.setAttribute('height', totalHeight);
            svgEl.setAttribute('viewBox', `0 0 ${chartWidth} ${totalHeight}`);

            // â”€â”€ Backgrounds: alternating row stripes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            const totalRows = data.phases.length + 1 + (data.payments.length > 0 ? 1 : 0);
            for (let r = 0; r < totalRows; r++) {
                svg('rect', {
                    x: 0, y: HEADER_H + r * ROW_H,
                    width: chartWidth, height: ROW_H,
                    fill: r % 2 === 0 ? '#fff' : '#fafafa',
                }, svgEl);
            }

            // â”€â”€ Header: two-tier date scale â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            svg('rect', { x:0, y:0, width: chartWidth, height: HEADER_H, fill: '#f9fafb' }, svgEl);
            svg('line', { x1:0, y1:HEADER_H, x2:chartWidth, y2:HEADER_H, stroke:'#e5e7eb', 'stroke-width':1 }, svgEl);

            drawHeader(mode, dayWidth, chartWidth);

            // â”€â”€ Vertical grid lines (per tick) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            drawVerticalGrid(mode, dayWidth, totalHeight);

            // â”€â”€ Today vertical line â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if (today >= chartStart && today <= chartEnd) {
                const tx = dateX(today, dayWidth);
                const line = svg('line', {
                    x1: tx, y1: 0, x2: tx, y2: totalHeight,
                    stroke: '#ef4444', 'stroke-width': 2,
                    'stroke-dasharray': '4 4',
                }, svgEl);
                line.setAttribute('opacity', '0.85');

                // Today label badge
                const g = svg('g', { transform: `translate(${tx}, 8)` }, svgEl);
                svg('rect', { x: -22, y: 0, width: 44, height: 20, rx: 4, ry: 4, fill: '#ef4444' }, g);
                const txt = svg('text', {
                    x: 0, y: 14, 'text-anchor': 'middle',
                    'font-size': 11, 'font-weight': '700', fill: '#fff',
                    'font-family': 'system-ui, sans-serif',
                }, g);
                txt.textContent = LABELS.today;
            }

            // â”€â”€ Overall initiative bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            const overallX = dateX(rangeStart, dayWidth);
            const overallW = (diffDays(rangeStart, rangeEnd) + 1) * dayWidth;
            svg('rect', {
                x: overallX, y: overallRowY + BAR_Y_OFFSET,
                width: Math.max(2, overallW), height: BAR_H,
                rx: 4, ry: 4,
                fill: '#283979', opacity: 0.18,
            }, svgEl);
            // top thin accent bar
            svg('rect', {
                x: overallX, y: overallRowY + BAR_Y_OFFSET + 2,
                width: Math.max(2, overallW), height: 4,
                rx: 2, ry: 2, fill: '#283979', opacity: 0.65,
            }, svgEl);

            // â”€â”€ Phase bars â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            data.phases.forEach((phase, idx) => {
                const y = phaseRowsY + idx * ROW_H + BAR_Y_OFFSET;
                const start = parseDate(phase.start);
                const end   = parseDate(phase.end);
                const x     = dateX(start, dayWidth);
                const w     = Math.max(2, (diffDays(start, end) + 1) * dayWidth);
                const color = phase.color;

                // Bar background (light tint)
                svg('rect', {
                    x, y, width: w, height: BAR_H, rx: 5, ry: 5,
                    fill: color, opacity: 0.22,
                }, svgEl);
                // Bar foreground = progress portion
                const progressW = Math.max(0, Math.min(w, w * (phase.progress / 100)));
                if (progressW > 0) {
                    svg('rect', {
                        x, y, width: progressW, height: BAR_H, rx: 5, ry: 5,
                        fill: color,
                    }, svgEl);
                }
                // Bar border
                svg('rect', {
                    x, y, width: w, height: BAR_H, rx: 5, ry: 5,
                    fill: 'none', stroke: color, 'stroke-width': 1.5,
                }, svgEl);

                // Bar label (only if there's enough room)
                if (w > 60) {
                    const t = svg('text', {
                        x: x + w / 2, y: y + BAR_H / 2 + 4,
                        'text-anchor': 'middle',
                        'font-size': 11, 'font-weight': '600',
                        fill: phase.progress > 50 ? '#fff' : '#1f2937',
                        'font-family': 'system-ui, sans-serif',
                        'pointer-events': 'none',
                    }, svgEl);
                    t.textContent = (phase.name.length > Math.max(6, w / 8))
                        ? phase.name.slice(0, Math.max(6, Math.floor(w/8))) + 'â€¦'
                        : phase.name;
                }

                // Hit area for hover (covers row strip around bar)
                const hit = svg('rect', {
                    x, y: y - 6, width: w, height: BAR_H + 12,
                    fill: 'transparent', cursor: 'pointer',
                    'data-phase-idx': idx,
                }, svgEl);
                hit.addEventListener('mousemove', (e) => showPhaseTooltip(e, phase));
                hit.addEventListener('mouseleave', hideTooltip);
            });

            // â”€â”€ Payment diamonds â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if (data.payments.length > 0) {
                // Row label
                const pY = paymentRowY + PAYMENT_ROW_H / 2;
                data.payments.forEach((p, idx) => {
                    const d = parseDate(p.date);
                    if (d < chartStart || d > chartEnd) return;
                    const x = dateX(d, dayWidth);
                    const size = 12;
                    const diamond = svg('polygon', {
                        points: `${x},${pY-size} ${x+size},${pY} ${x},${pY+size} ${x-size},${pY}`,
                        fill: '#b88a3a', stroke: '#fff', 'stroke-width': 2,
                        cursor: 'pointer',
                    }, svgEl);
                    diamond.addEventListener('mousemove', (e) => showPaymentTooltip(e, p, idx));
                    diamond.addEventListener('mouseleave', hideTooltip);
                });
            }
        }

        // â”€â”€ Header drawing â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        function drawHeader(mode, dayWidth, chartWidth) {
            // Top tier (year-month or year-quarter)
            // Bottom tier (week/day/month)
            if (mode === 'day') {
                // Top: months. Bottom: day numbers
                drawMonthSpans(dayWidth, 0, 30);
                drawDayTicks(dayWidth, 30, 30);
            } else if (mode === 'week') {
                drawMonthSpans(dayWidth, 0, 30);
                drawWeekTicks(dayWidth, 30, 30);
            } else if (mode === 'month') {
                drawYearSpans(dayWidth, 0, 30);
                drawMonthTicksShort(dayWidth, 30, 30);
            } else { // quarter
                drawYearSpans(dayWidth, 0, 30);
                drawQuarterTicks(dayWidth, 30, 30);
            }
        }

        function drawMonthSpans(dayWidth, y, h) {
            let cursor = new Date(chartStart.getTime());
            cursor.setUTCDate(1);
            if (cursor < chartStart) cursor.setUTCMonth(cursor.getUTCMonth() + 1);

            // Start from the chart-start month
            let monthStart = new Date(Date.UTC(chartStart.getUTCFullYear(), chartStart.getUTCMonth(), 1));
            while (monthStart <= chartEnd) {
                const monthEnd = new Date(Date.UTC(monthStart.getUTCFullYear(), monthStart.getUTCMonth() + 1, 0));
                const spanStart = monthStart < chartStart ? chartStart : monthStart;
                const spanEnd   = monthEnd > chartEnd ? chartEnd : monthEnd;
                const x  = dateX(spanStart, dayWidth);
                const w  = (diffDays(spanStart, spanEnd) + 1) * dayWidth;
                const label = MONTHS_LONG[monthStart.getUTCMonth()] + ' ' + monthStart.getUTCFullYear();
                drawHeaderCell(x, y, w, h, label, '#1f2937', true);
                monthStart = new Date(Date.UTC(monthStart.getUTCFullYear(), monthStart.getUTCMonth() + 1, 1));
            }
        }

        function drawYearSpans(dayWidth, y, h) {
            let yearStart = new Date(Date.UTC(chartStart.getUTCFullYear(), 0, 1));
            while (yearStart <= chartEnd) {
                const yearEnd = new Date(Date.UTC(yearStart.getUTCFullYear(), 11, 31));
                const spanStart = yearStart < chartStart ? chartStart : yearStart;
                const spanEnd   = yearEnd > chartEnd ? chartEnd : yearEnd;
                const x  = dateX(spanStart, dayWidth);
                const w  = (diffDays(spanStart, spanEnd) + 1) * dayWidth;
                drawHeaderCell(x, y, w, h, String(yearStart.getUTCFullYear()), '#1f2937', true);
                yearStart = new Date(Date.UTC(yearStart.getUTCFullYear() + 1, 0, 1));
            }
        }

        function drawDayTicks(dayWidth, y, h) {
            for (let i = 0; i < totalDays; i++) {
                const d = addDays(chartStart, i);
                const x = i * dayWidth;
                const isWeekend = d.getUTCDay() === 5 || d.getUTCDay() === 6;
                const t = svg('text', {
                    x: x + dayWidth/2, y: y + h/2 + 4, 'text-anchor': 'middle',
                    'font-size': 10, 'font-weight': '500',
                    fill: isWeekend ? '#ef4444' : '#4b5563',
                    'font-family': 'system-ui, sans-serif',
                }, svgEl);
                t.textContent = d.getUTCDate();
            }
            // Bottom border
            svg('line', { x1:0, y1:y+h, x2:totalDays*dayWidth, y2:y+h, stroke:'#e5e7eb' }, svgEl);
        }

        function drawWeekTicks(dayWidth, y, h) {
            // Show week-of-month: every 7 days
            for (let i = 0; i < totalDays; i += 7) {
                const d = addDays(chartStart, i);
                const x = i * dayWidth;
                const w = 7 * dayWidth;
                drawHeaderCell(x, y, Math.min(w, totalDays*dayWidth - x), h,
                    d.getUTCDate() + ' ' + MONTHS_SHORT[d.getUTCMonth()],
                    '#4b5563', false);
            }
            svg('line', { x1:0, y1:y+h, x2:totalDays*dayWidth, y2:y+h, stroke:'#e5e7eb' }, svgEl);
        }

        function drawMonthTicksShort(dayWidth, y, h) {
            let monthStart = new Date(Date.UTC(chartStart.getUTCFullYear(), chartStart.getUTCMonth(), 1));
            while (monthStart <= chartEnd) {
                const monthEnd = new Date(Date.UTC(monthStart.getUTCFullYear(), monthStart.getUTCMonth() + 1, 0));
                const spanStart = monthStart < chartStart ? chartStart : monthStart;
                const spanEnd   = monthEnd > chartEnd ? chartEnd : monthEnd;
                const x  = dateX(spanStart, dayWidth);
                const w  = (diffDays(spanStart, spanEnd) + 1) * dayWidth;
                drawHeaderCell(x, y, w, h, MONTHS_SHORT[monthStart.getUTCMonth()], '#4b5563', false);
                monthStart = new Date(Date.UTC(monthStart.getUTCFullYear(), monthStart.getUTCMonth() + 1, 1));
            }
            svg('line', { x1:0, y1:y+h, x2:totalDays*dayWidth, y2:y+h, stroke:'#e5e7eb' }, svgEl);
        }

        function drawQuarterTicks(dayWidth, y, h) {
            for (let yr = chartStart.getUTCFullYear(); yr <= chartEnd.getUTCFullYear(); yr++) {
                for (let q = 0; q < 4; q++) {
                    const qStart = new Date(Date.UTC(yr, q * 3, 1));
                    const qEnd = new Date(Date.UTC(yr, q * 3 + 3, 0));
                    if (qEnd < chartStart || qStart > chartEnd) continue;
                    const spanStart = qStart < chartStart ? chartStart : qStart;
                    const spanEnd = qEnd > chartEnd ? chartEnd : qEnd;
                    const x = dateX(spanStart, dayWidth);
                    const w = (diffDays(spanStart, spanEnd) + 1) * dayWidth;
                    drawHeaderCell(x, y, w, h, LABELS.quarter_prefix + (q+1), '#4b5563', false);
                }
            }
            svg('line', { x1:0, y1:y+h, x2:totalDays*dayWidth, y2:y+h, stroke:'#e5e7eb' }, svgEl);
        }

        function drawHeaderCell(x, y, w, h, label, color, bold) {
            // Vertical separator
            svg('line', { x1:x, y1:y, x2:x, y2:y+h, stroke:'#e5e7eb', 'stroke-width':1 }, svgEl);
            if (w < 14) return; // too narrow to show label
            const t = svg('text', {
                x: x + w/2, y: y + h/2 + 4, 'text-anchor': 'middle',
                'font-size': bold ? 12 : 11,
                'font-weight': bold ? '700' : '500',
                fill: color,
                'font-family': 'system-ui, sans-serif',
            }, svgEl);
            t.textContent = label;
        }

        function drawVerticalGrid(mode, dayWidth, totalHeight) {
            // Light vertical lines per logical unit
            let step = 7;
            if (mode === 'day') step = 1;
            else if (mode === 'week') step = 7;
            else if (mode === 'month') step = 30;
            else step = 90;
            for (let i = step; i < totalDays; i += step) {
                const x = i * dayWidth;
                svg('line', {
                    x1: x, y1: HEADER_H, x2: x, y2: totalHeight,
                    stroke: '#f3f4f6', 'stroke-width': 1,
                }, svgEl);
            }
        }

        // â”€â”€ Tooltip â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        function showPhaseTooltip(e, phase) {
            const statusLabel = {
                'open': LABELS.status_open,
                'in_progress': LABELS.status_in_progress,
                'done': LABELS.status_done,
            }[phase.status];
            const duration = (Math.round((new Date(phase.end) - new Date(phase.start)) / (1000*60*60*24)) + 1);
            tooltip.innerHTML = `
                <strong>${escapeHtml(phase.name)}</strong>
                <div class="row"><span class="key">${LABELS.tooltip_status}:</span><span>${statusLabel}</span></div>
                <div class="row"><span class="key">${LABELS.tooltip_start}:</span><span>${phase.start}</span></div>
                <div class="row"><span class="key">${LABELS.tooltip_end}:</span><span>${phase.end}</span></div>
                <div class="row"><span class="key">${LABELS.tooltip_duration}:</span><span>${duration} ${LABELS.tooltip_days}</span></div>
                <div class="row"><span class="key">${LABELS.tooltip_progress}:</span><span>${phase.progress}%</span></div>
                ${phase.total_cost > 0 ? `<div class="row"><span class="key">${LABELS.tooltip_cost}:</span><span>${formatNumber(phase.total_cost, LABELS.currency)}</span></div>` : ''}
            `;
            positionTooltip(e);
            tooltip.classList.add('is-visible');
        }
        function showPaymentTooltip(e, p, idx) {
            const pctRow = (p.percentage !== null && p.percentage !== undefined)
                ? `<div class="row"><span class="key">${LABELS.percentage}:</span><span>${p.percentage}%</span></div>`
                : '';
            tooltip.innerHTML = `
                <strong>${LABELS.payment} #${idx+1}</strong>
                <div class="row"><span class="key">${LABELS.tooltip_start}:</span><span>${p.date}</span></div>
                ${pctRow}
                <div class="row"><span class="key">${LABELS.amount}:</span><span>${formatNumber(p.amount, LABELS.currency)}</span></div>
            `;
            positionTooltip(e);
            tooltip.classList.add('is-visible');
        }
        function positionTooltip(e) {
            const PAD = 14;
            const rect = tooltip.getBoundingClientRect();
            const left = Math.min(window.innerWidth - rect.width - PAD, e.clientX + PAD);
            const top = Math.min(window.innerHeight - rect.height - PAD, e.clientY + PAD);
            tooltip.style.left = Math.max(PAD, left) + 'px';
            tooltip.style.top  = Math.max(PAD, top) + 'px';
        }
        function hideTooltip() { tooltip.classList.remove('is-visible'); }
        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
        }

        // â”€â”€ Toolbar: mode switcher + Today scroll â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        document.querySelectorAll('.js-gantt-mode').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.js-gantt-mode').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                render(btn.dataset.mode);
                scrollToToday();
            });
        });
        const todayBtn = document.getElementById('js-gantt-today');
        if (todayBtn) todayBtn.addEventListener('click', scrollToToday);

        function scrollToToday() {
            if (today < chartStart || today > chartEnd) return;
            const baseDW = VIEW_MODES[currentMode].dayWidth;
            const minDW = scrollEl && scrollEl.clientWidth > 0 ? scrollEl.clientWidth / totalDays : baseDW;
            const dayWidth = Math.max(baseDW, minDW);
            const tx = dateX(today, dayWidth);
            scrollEl.scrollLeft = Math.max(0, tx - scrollEl.clientWidth / 2);
        }

        // Initial render
        render('week');
        scrollToToday();
        window.__makeenGanttRender = render;

        // Re-render on window resize (auto-fit dayWidth must recalculate).
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => render(currentMode), 150);
        });

        // â”€â”€ FullCalendar with Hijri/Gregorian toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const el = document.getElementById('initiative-calendar');
        if (el && typeof FullCalendar !== 'undefined') {
            let calMode = 'gregorian'; // 'gregorian' | 'hijri'
            const hijriDay   = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura-nu-latn', { day: 'numeric' });
            const hijriTitle = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura-nu-latn', { month: 'long', year: 'numeric' });
            const hijriFull  = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura-nu-latn', { day: 'numeric', month: 'long', year: 'numeric' });
            const gregTitle  = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' });
            const gregFull   = new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric' });
            const mobileDayNames = ['أح', 'إث', 'ثل', 'أر', 'خم', 'جم', 'سب'];
            const isCalendarMobile = () => window.matchMedia('(max-width: 640px)').matches;

            function updateTitle() {
                const titleEl = el.querySelector('.fc-toolbar-title');
                if (!titleEl) return;
                const d = calendar.getDate();
                titleEl.textContent = calMode === 'hijri' ? hijriTitle.format(d) : gregTitle.format(d);
            }

            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                locale: locale,
                direction: 'rtl',
                height: 640,
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
                buttonText: { today: LABELS.cal_today, month: LABELS.cal_month, week: LABELS.cal_week, day: LABELS.cal_day, list: LABELS.cal_list },
                dayHeaderContent: (info) => {
                    if (!isCalendarMobile()) {
                        return { html: info.text };
                    }

                    const day = mobileDayNames[info.date.getDay()] || info.text;
                    const date = (info.date.getMonth() + 1) + '/' + info.date.getDate();
                    const showDate = info.view.type !== 'dayGridMonth';

                    return {
                        html: '<span class="mk-cal-day-short">' + day + '</span>' + (showDate ? '<span class="mk-cal-day-date">' + date + '</span>' : ''),
                    };
                },
                events: events,
                eventDisplay: 'block',
                dayCellContent: (info) => {
                    if (calMode === 'hijri') {
                        return { html: '<span style="font-weight:600">' + hijriDay.format(info.date) + '</span>' };
                    }
                    return { html: info.dayNumberText };
                },
                eventDidMount: (info) => {
                    const date = info.event.start;
                    if (!date) return;
                    info.el.setAttribute('title',
                        info.event.title + '\\n' +
                        LABELS.calendar_gregorian_title + ' ' + gregFull.format(date) + '\\n' +
                        LABELS.calendar_hijri_title + ' ' + hijriFull.format(date)
                    );
                },
                datesSet: () => { updateTitle(); },
            });
            calendar.render();
            updateTitle();
            let calendarResizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(calendarResizeTimer);
                calendarResizeTimer = setTimeout(() => {
                    calendar.render();
                    calendar.updateSize();
                    updateTitle();
                }, 160);
            });

            const gregBtn = document.getElementById('js-cal-gregorian');
            const hijriBtn = document.getElementById('js-cal-hijri');
            function switchMode(mode) {
                calMode = mode;
                if (mode === 'gregorian') {
                    gregBtn && gregBtn.classList.add('is-active');
                    hijriBtn && hijriBtn.classList.remove('is-active');
                } else {
                    hijriBtn && hijriBtn.classList.add('is-active');
                    gregBtn && gregBtn.classList.remove('is-active');
                }
                // Re-render the current view so cells re-evaluate their content.
                calendar.changeView(calendar.view.type);
                updateTitle();
            }
            if (gregBtn)  gregBtn.addEventListener('click', () => switchMode('gregorian'));
            if (hijriBtn) hijriBtn.addEventListener('click', () => switchMode('hijri'));
        }
        } catch (err) {
            console.error('[makeen-gantt] render error:', err);
            const errBox = document.getElementById('gantt-chart-scroll');
            if (errBox) {
                errBox.innerHTML = '<div style="padding:2rem;text-align:center;color:#b91c1c;font-family:system-ui">' + LABELS.render_error + '<code style="display:block;direction:ltr;margin-top:.5rem">' + String(err && err.message ? err.message : err) + '</code></div>';
            }
        }
        }

        // Boot now and again on Livewire navigations (in case scripts are
        // re-mounted via wire:navigate).
        function tryBoot() {
            const svgEl = document.getElementById('gantt-svg');
            if (!svgEl) return;
            if (svgEl.dataset.makeenInited === '1') return;
            svgEl.dataset.makeenInited = '1';
            bootGantt();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tryBoot);
        } else {
            tryBoot();
        }
        document.addEventListener('livewire:navigated', () => {
            const svgEl = document.getElementById('gantt-svg');
            if (svgEl) svgEl.dataset.makeenInited = '';
            tryBoot();
        });
    })();
    </script>
</x-filament-panels::page>

