@php
    $data = $this->getDashboardData();
    $hero = $data['hero'] ?? [];
    $completion = max(0, min(100, (int) ($hero['completion'] ?? 0)));
    $finance = $data['finance'] ?? [];
    $paidPercentage = max(0, min(100, (int) ($finance['paid_percentage'] ?? 0)));

    // Stable per-component widget id so the init script can always find the
    // canvases even after Livewire DOM-morphs (a fresh uniqid() each render
    // breaks the inline script's stale closures).
    $widgetId = 'mk-admin-dash';

    $chartPayload = [
        'timeseries' => $data['timeseries'] ?? [],
        'initiatives_by_status' => $data['distributions']['initiatives_by_status'] ?? [],
        'organizations_by_type' => $data['distributions']['organizations_by_type'] ?? [],
        'users_by_role' => $data['distributions']['users_by_role'] ?? [],
        'consultations_by_specialization' => $data['distributions']['consultations_by_specialization'] ?? [],
    ];
@endphp

<x-filament-widgets::widget>
    <style>
        .mk-dash {
            direction: rtl;
            display: grid;
            gap: 18px;
            font-family: 'Alexandria', 'IBM Plex Sans Arabic', 'Tajawal', system-ui, sans-serif;
        }
        .mk-dash *, .mk-dash *::before, .mk-dash *::after { box-sizing: border-box; }

        /* Default SVG sizing: match font-size unless a parent overrides explicitly. */
        .mk-dash svg {
            width: 1em;
            height: 1em;
            flex-shrink: 0;
            vertical-align: middle;
            display: inline-block;
        }

        /* Filter bar */
        .mk-dash__filterbar {
            background: #fff;
            border: 1px solid rgba(40,57,121,.08);
            border-radius: 14px;
            box-shadow: 0 8px 18px rgba(40,57,121,.05);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            flex-wrap: wrap;
        }
        .mk-dash__filterbar-label {
            color: #283979;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .mk-dash__filterbar-label svg { width: 16px; height: 16px; }
        .mk-dash__period {
            background: rgba(40,57,121,.06);
            border: 1px solid rgba(40,57,121,.12);
            border-radius: 999px;
            color: #283979;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            cursor: pointer;
            outline: none;
            transition: background .2s ease, border-color .2s ease;
        }
        .mk-dash__period:hover { background: rgba(40,57,121,.1); }
        .mk-dash__period:focus { border-color: #21b2b8; box-shadow: 0 0 0 3px rgba(33,178,184,.18); }
        .mk-dash__refresh { display: inline-flex; align-items: center; gap: 6px; }
        .mk-dash__refresh svg { width: 14px; height: 14px; }
        .mk-dash__filterbar-hint { color: #6b7280; font-size: 11px; margin-inline-start: auto; }

        /* Hero */
        .mk-dash__hero {
            background:
                linear-gradient(135deg, rgba(40,57,121,.98), rgba(43,53,79,.98)),
                radial-gradient(circle at 12% 10%, rgba(33,178,184,.32), transparent 32%),
                radial-gradient(circle at 88% 90%, rgba(249,173,28,.28), transparent 35%);
            border-radius: 22px;
            box-shadow: 0 26px 60px rgba(40,57,121,.18);
            color: #fff;
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, .8fr);
            overflow: hidden;
            padding: 26px;
            position: relative;
            animation: mkFadeIn .6s ease-out;
        }
        .mk-dash__hero::before {
            animation: mkPulseBar 2.8s ease-in-out infinite;
            background: linear-gradient(90deg, #f9ad1c, #21b2b8);
            border-radius: 999px;
            content: "";
            height: 8px;
            inset-inline-start: 26px;
            position: absolute;
            top: 0;
            width: 180px;
        }
        .mk-dash__hero h2 { font-size: 28px; font-weight: 500; line-height: 1.25; margin: 0; }
        .mk-dash__hero p  { color: rgba(255,255,255,.78); font-size: 14px; margin: 8px 0 0; max-width: 760px; }
        .mk-dash__hero-time { color: rgba(255,255,255,.55); font-size: 12px; margin-top: 4px; display: inline-flex; align-items: center; gap: 6px; }

        .mk-dash__hero-stats {
            display: grid; gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 22px;
        }
        .mk-dash__hero-stat {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 14px;
            padding: 14px;
            transition: transform .25s ease, background .25s ease;
        }
        .mk-dash__hero-stat:hover { transform: translateY(-2px); background: rgba(255,255,255,.15); }
        .mk-dash__hero-stat span { color: rgba(255,255,255,.7); font-size: 11px; font-weight: 500; display: block; margin-bottom: 6px; }
        .mk-dash__hero-stat strong { font-size: 20px; font-weight: 500; display: block; }

        .mk-dash__ring-wrap { display: grid; place-items: center; gap: 10px; }
        .mk-dash__ring {
            background:
                conic-gradient(#21b2b8 0 calc(var(--pct, 0) * 1%), rgba(255,255,255,.18) 0);
            border-radius: 50%;
            height: 150px; width: 150px;
            display: grid; place-items: center;
            position: relative;
            animation: mkRingFill 1s ease-out;
        }
        .mk-dash__ring::before {
            background: linear-gradient(135deg, #283979, #2b354f);
            border-radius: 50%;
            content: "";
            inset: 12px;
            position: absolute;
        }
        .mk-dash__ring strong { font-size: 32px; font-weight: 500; position: relative; }
        .mk-dash__ring span { color: rgba(255,255,255,.72); font-size: 12px; font-weight: 500; position: relative; }
        .mk-dash__ring-label { color: rgba(255,255,255,.66); font-size: 12px; font-weight: 500; }

        /* KPI cards with icons */
        .mk-dash__kpis {
            display: grid; gap: 14px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .mk-dash__kpi {
            background: #fff;
            border: 1px solid rgba(40,57,121,.08);
            border-radius: 18px;
            box-shadow: 0 12px 26px rgba(40,57,121,.06);
            padding: 18px;
            position: relative;
            overflow: hidden;
            animation: mkRise .55s ease-out both;
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .mk-dash__kpi:hover { transform: translateY(-3px); box-shadow: 0 22px 38px rgba(40,57,121,.12); }
        .mk-dash__kpi::after {
            content: "";
            position: absolute;
            inset-block-start: 0;
            inset-inline-start: 0;
            width: 5px; height: 100%;
            background: var(--kpi, #283979);
        }
        .mk-dash__kpi-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .mk-dash__kpi-icon {
            display: inline-flex; align-items: center; justify-content: center;
            line-height: 0;
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--kpi-bg, rgba(40,57,121,.1));
            color: var(--kpi, #283979);
        }
        .mk-dash__kpi-icon svg { width: 22px; height: 22px; }
        .mk-dash__kpi-trend { font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 999px; display: inline-flex; align-items: center; gap: 4px; line-height: 1; white-space: nowrap; }
        .mk-dash__kpi-trend svg { width: 12px; height: 12px; }
        .mk-dash__kpi-trend[data-direction="up"]   { color: #21b2b8; background: rgba(33,178,184,.12); }
        .mk-dash__kpi-trend[data-direction="down"] { color: #e57373; background: rgba(229,115,115,.12); }
        .mk-dash__kpi-trend[data-direction="flat"] { color: #6b7280; background: rgba(107,114,128,.12); }
        .mk-dash__kpi-value { font-size: 30px; font-weight: 500; color: #283979; margin: 10px 0 4px; line-height: 1; }
        .mk-dash__kpi-label { color: #6b7280; font-size: 12px; font-weight: 500; }
        .mk-dash__kpi-hint  { color: #283979; font-size: 12px; font-weight: 500; margin-top: 6px; opacity: .8; }
        .mk-dash__kpi[data-tone="navy"]   { --kpi: #283979; --kpi-bg: rgba(40,57,121,.10); }
        .mk-dash__kpi[data-tone="teal"]   { --kpi: #21b2b8; --kpi-bg: rgba(33,178,184,.12); }
        .mk-dash__kpi[data-tone="amber"]  { --kpi: #f9ad1c; --kpi-bg: rgba(249,173,28,.14); }
        .mk-dash__kpi[data-tone="slate"]  { --kpi: #56678a; --kpi-bg: rgba(86,103,138,.12); }

        /* Finance strip */
        .mk-dash__finance {
            background: linear-gradient(135deg, #fff 0, #f7f8fc 100%);
            border-radius: 20px;
            border: 1px solid rgba(40,57,121,.08);
            box-shadow: 0 10px 24px rgba(40,57,121,.05);
            padding: 22px;
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(260px, 1fr) repeat(4, minmax(0, 1fr));
            animation: mkFadeIn .6s ease-out;
        }
        .mk-dash__finance-meter { display: grid; gap: 8px; }
        .mk-dash__finance-title { color: #283979; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .mk-dash__finance-bar { background: rgba(40,57,121,.08); border-radius: 999px; height: 12px; overflow: hidden; position: relative; }
        .mk-dash__finance-bar span {
            background: linear-gradient(90deg, #21b2b8, #283979);
            border-radius: 999px;
            display: block;
            height: 100%;
            width: 0;
            animation: mkBarGrow 1.2s ease-out forwards;
        }
        .mk-dash__finance-bar small { color: #283979; font-size: 11px; font-weight: 500; margin-top: 4px; display: block; }
        .mk-dash__finance-cell { background: #fff; border: 1px solid rgba(40,57,121,.08); border-radius: 14px; padding: 14px; }
        .mk-dash__finance-cell span { color: #6b7280; font-size: 11px; font-weight: 500; display: block; }
        .mk-dash__finance-cell strong { color: #283979; font-size: 18px; font-weight: 500; display: block; margin-top: 6px; }

        /* Queue chips */
        .mk-dash__queue {
            display: grid; gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .mk-dash__queue-item {
            background: #fff;
            border-radius: 16px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid rgba(40,57,121,.08);
            box-shadow: 0 8px 18px rgba(40,57,121,.05);
            animation: mkRise .6s ease-out both;
            transition: transform .2s ease, box-shadow .2s ease;
            direction: rtl;
        }
        .mk-dash__queue-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(40,57,121,.10);
        }
        .mk-dash__queue-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
        }
        .mk-dash__queue-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            line-height: 0;
        }
        .mk-dash__queue-icon svg { width: 20px; height: 20px; display: block; }

        /* Headings + small embedded icons */
        .mk-dash__section h3 svg,
        .mk-dash__pipe-title svg,
        .mk-dash__hero-time svg,
        .mk-dash__finance-title svg,
        .mk-dash__panel-head strong svg { width: 16px; height: 16px; }
        .mk-dash__queue-item[data-status="info"]    .mk-dash__queue-icon { background: rgba(33,178,184,.14); color: #21b2b8; }
        .mk-dash__queue-item[data-status="warning"] .mk-dash__queue-icon { background: rgba(249,173,28,.18); color: #f9ad1c; }
        .mk-dash__queue-item[data-status="danger"]  .mk-dash__queue-icon { background: rgba(229,115,115,.16); color: #e57373; }
        .mk-dash__queue-item[data-status="success"] .mk-dash__queue-icon { background: rgba(34,197,94,.16);  color: #16a34a; }
        .mk-dash__queue-item span { color: #56678a; font-size: 12px; font-weight: 500; display: block; }
        .mk-dash__queue-item strong { color: #283979; font-size: 22px; font-weight: 500; display: block; margin-top: 4px; }

        /* Section headings */
        .mk-dash__section { display: flex; align-items: center; gap: 10px; margin-top: 4px; }
        .mk-dash__section h3 { color: #283979; font-size: 16px; font-weight: 500; margin: 0; flex: 1; display: flex; align-items: center; gap: 8px; }
        .mk-dash__section small { color: #6b7280; font-size: 11px; }

        /* Two-column row */
        .mk-dash__grid { display: grid; gap: 14px; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); }
        .mk-dash__grid--equal { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        /* Panel */
        .mk-dash__panel {
            background: #fff;
            border-radius: 18px;
            border: 1px solid rgba(40,57,121,.08);
            box-shadow: 0 10px 22px rgba(40,57,121,.05);
            display: grid;
            grid-template-rows: auto 1fr;
        }
        .mk-dash__panel-head { display: flex; align-items: center; gap: 8px; padding: 16px 18px; border-bottom: 1px solid rgba(40,57,121,.08); }
        .mk-dash__panel-head strong { color: #283979; font-size: 14px; font-weight: 500; flex: 1; }
        .mk-dash__panel-body { padding: 16px 18px; }
        .mk-dash__badge { background: rgba(40,57,121,.08); border-radius: 999px; color: #283979; font-size: 11px; font-weight: 500; padding: 4px 10px; }

        /* Pure-SVG donut chart */
        .mk-svg-donut { display: grid; grid-template-columns: minmax(0, 140px) 1fr; gap: 14px; align-items: center; }
        .mk-svg-donut__chart { width: 100%; max-width: 160px; }
        .mk-svg-donut__chart svg { display: block; width: 100%; height: auto; }
        .mk-svg-donut__legend { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
        .mk-svg-donut__legend li { display: grid; grid-template-columns: 14px 1fr auto auto; gap: 8px; align-items: center; font-size: 12px; color: #283979; }
        .mk-svg-donut__swatch { width: 12px; height: 12px; border-radius: 4px; display: block; }
        .mk-svg-donut__label { color: #283979; font-weight: 500; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .mk-svg-donut__value { color: #283979; font-weight: 500; }
        .mk-svg-donut__pct { color: #6b7280; font-size: 11px; font-weight: 500; }
        .mk-svg-donut__empty { color: #94a3b8; font-size: 12px; }

        /* Pure-SVG multi-series line chart */
        .mk-svg-line svg { display: block; width: 100%; height: auto; }
        .mk-svg-line__legend { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 14px; }
        .mk-svg-line__legend li { display: inline-flex; align-items: center; gap: 6px; color: #283979; font-size: 12px; font-weight: 500; }
        .mk-svg-line__swatch { width: 12px; height: 12px; border-radius: 4px; display: inline-block; }

        /* Chart wrappers (legacy Chart.js fallback, hidden by default) */
        .mk-dash__chart {
            display: none;
            position: relative;
            height: 280px;
            width: 100%;
        }
        .mk-dash__chart canvas { display: block; width: 100% !important; height: 100% !important; }
        .mk-dash__legend { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .mk-dash__legend-chip { font-size: 11px; font-weight: 500; color: #283979; background: rgba(40,57,121,.06); border-radius: 999px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 6px; }
        .mk-dash__legend-chip i { width: 10px; height: 10px; border-radius: 999px; background: var(--c, #283979); display: inline-block; }

        /* Pipeline bars + Tab UI */
        .mk-dash__pipe-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid rgba(40,57,121,.08); }
        .mk-dash__pipe-tab {
            background: rgba(40,57,121,.04);
            border: 1px solid rgba(40,57,121,.1);
            border-radius: 999px;
            color: #56678a;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            padding: 7px 14px;
            transition: background .2s ease, color .2s ease, border-color .2s ease, transform .15s ease;
        }
        .mk-dash__pipe-tab svg { width: 14px; height: 14px; }
        .mk-dash__pipe-tab:hover { background: rgba(40,57,121,.08); color: #283979; transform: translateY(-1px); }
        .mk-dash__pipe-tab.is-active {
            background: linear-gradient(135deg, #283979, #21b2b8);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 10px 24px rgba(40,57,121,.22);
        }
        .mk-dash__pipe-tab .mk-dash__pipe-tab-count { background: rgba(255,255,255,.18); border-radius: 999px; font-size: 10px; padding: 2px 8px; }
        .mk-dash__pipe-tab:not(.is-active) .mk-dash__pipe-tab-count { background: rgba(40,57,121,.1); color: #283979; }
        .mk-dash__pipe-panel { display: none; animation: mkFadeIn .35s ease both; }
        .mk-dash__pipe-panel.is-active { display: grid; gap: 10px; }
        .mk-dash__pipe { display: grid; gap: 14px; }
        .mk-dash__pipe-title { color: #283979; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .mk-dash__pipe-row  { display: grid; gap: 4px; }
        .mk-dash__pipe-meta { display: flex; justify-content: space-between; color: #56678a; font-size: 11px; font-weight: 500; }
        .mk-dash__pipe-meta strong { color: #283979; font-size: 12px; }
        .mk-dash__pipe-bar  { background: rgba(40,57,121,.08); border-radius: 999px; height: 10px; overflow: hidden; position: relative; }
        .mk-dash__pipe-bar span {
            background: linear-gradient(90deg, #21b2b8, #283979);
            border-radius: 999px;
            display: block;
            height: 100%;
            width: 0;
            animation: mkBarGrow 1s ease-out forwards;
        }

        /* Tables / lists */
        .mk-dash__list { display: grid; gap: 8px; }
        .mk-dash__row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 10px;
            align-items: center;
            background: rgba(40,57,121,.04);
            border-radius: 12px;
            padding: 10px 12px;
            transition: background .2s ease;
        }
        .mk-dash__row:hover { background: rgba(40,57,121,.07); }
        .mk-dash__row strong { color: #283979; font-size: 13px; font-weight: 500; }
        .mk-dash__row small { color: #6b7280; font-size: 11px; }

        /* Counters strip */
        .mk-dash__counters { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .mk-dash__counter {
            background: linear-gradient(135deg, #fff, #f7f8fc);
            border: 1px solid rgba(40,57,121,.08);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            direction: rtl;
        }
        .mk-dash__counter-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
        .mk-dash__counter-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(40,57,121,.08); color: #283979; display: inline-flex; align-items: center; justify-content: center; line-height: 0; flex-shrink: 0; }
        .mk-dash__counter-icon svg { width: 20px; height: 20px; display: block; }
        .mk-dash__counter span { color: #6b7280; font-size: 11px; font-weight: 500; display: block; }
        .mk-dash__counter strong { color: #283979; font-size: 18px; font-weight: 500; display: block; }

        /* Rating distribution */
        .mk-dash__rating { display: grid; gap: 6px; }
        .mk-dash__rating-row { display: grid; grid-template-columns: 60px 1fr 40px; gap: 8px; align-items: center; font-size: 12px; color: #56678a; }
        .mk-dash__rating-row strong { color: #283979; }
        .mk-dash__rating-bar { background: rgba(40,57,121,.07); border-radius: 999px; height: 8px; overflow: hidden; }
        .mk-dash__rating-bar span { display: block; height: 100%; background: linear-gradient(90deg, #f9ad1c, #ffd97d); border-radius: 999px; animation: mkBarGrow .9s ease-out forwards; width: 0; }

        /* Activity feed */
        .mk-dash__activity { display: grid; gap: 10px; max-height: 360px; overflow-y: auto; padding-inline-end: 4px; }
        .mk-dash__activity-item {
            display: grid;
            grid-template-columns: 36px 1fr auto;
            gap: 10px;
            align-items: start;
            padding: 10px;
            border-radius: 12px;
            background: rgba(40,57,121,.03);
            transition: background .2s ease;
        }
        .mk-dash__activity-item:hover { background: rgba(40,57,121,.06); }
        .mk-dash__activity-icon { width: 36px; height: 36px; border-radius: 10px; background: #fff; display: inline-flex; align-items: center; justify-content: center; line-height: 0; color: #283979; border: 1px solid rgba(40,57,121,.1); }
        .mk-dash__activity-icon svg { width: 18px; height: 18px; display: block; }
        .mk-dash__activity-text strong { color: #283979; font-size: 12px; font-weight: 500; display: block; }
        .mk-dash__activity-text p { color: #56678a; font-size: 12px; margin: 4px 0 0; line-height: 1.45; }
        .mk-dash__activity-time { color: #8a94a6; font-size: 10px; white-space: nowrap; }

        /* Animations */
        @keyframes mkFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes mkRise  { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes mkRingFill { from { background: conic-gradient(#21b2b8 0 0, rgba(255,255,255,.18) 0); } }
        @keyframes mkPulseBar { 0%,100% { opacity: .85; transform: scaleX(1); } 50% { opacity: 1; transform: scaleX(1.18); } }
        @keyframes mkBarGrow { from { width: 0; } }

        /* Responsiveness */
        @media (max-width: 1100px) {
            .mk-dash__hero      { grid-template-columns: 1fr; }
            .mk-dash__kpis      { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .mk-dash__queue     { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .mk-dash__grid      { grid-template-columns: 1fr; }
            .mk-dash__finance   { grid-template-columns: 1fr; }
            .mk-dash__counters  { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .mk-dash__hero-stats,
            .mk-dash__kpis,
            .mk-dash__queue,
            .mk-dash__counters { grid-template-columns: 1fr; }
        }

        /* === Dark mode overrides ===
           Filament 4 toggles the `.dark` class on <html>. Override the
           card backgrounds, text and borders so the whole dashboard is
           readable in dark theme without rebuilding every component. */
        .dark .mk-dash { color: #e2e8f0; }
        .dark .mk-dash__hero,
        .dark .mk-dash__panel,
        .dark .mk-dash__kpi,
        .dark .mk-dash__queue,
        .dark .mk-dash__counter,
        .dark .mk-dash__pipeline,
        .dark .mk-dash__activity-item,
        .dark .mk-dash__list-item,
        .dark .mk-dash__rating-bar,
        .dark .mk-dash__top-table,
        .dark .mk-dash__finance {
            background: #1e293b !important;
            border-color: rgba(148, 163, 184, .18) !important;
            color: #e2e8f0 !important;
            box-shadow: 0 14px 34px rgba(0, 0, 0, .35) !important;
        }
        .dark .mk-dash__period,
        .dark .mk-dash__refresh {
            background: #334155 !important;
            border-color: rgba(148, 163, 184, .25) !important;
            color: #e2e8f0 !important;
        }
        .dark .mk-dash__period.is-active {
            background: linear-gradient(135deg, #21b2b8, #283979) !important;
            color: #fff !important;
        }
        .dark .mk-dash__panel-head,
        .dark .mk-dash__panel-head strong,
        .dark .mk-dash__kpi-title,
        .dark .mk-dash__kpi-value,
        .dark .mk-dash__queue-title,
        .dark .mk-dash__queue-value,
        .dark .mk-dash__counter-title,
        .dark .mk-dash__counter-value,
        .dark .mk-dash__pipe-title,
        .dark .mk-dash__pipe-tab,
        .dark .mk-svg-donut__label,
        .dark .mk-svg-donut__value,
        .dark .mk-svg-line__legend li,
        .dark .mk-dash__activity-text,
        .dark .mk-dash__list-name,
        .dark .mk-dash__list-meta,
        .dark .mk-dash__top-table th,
        .dark .mk-dash__top-table td,
        .dark .mk-dash__rating-label,
        .dark .mk-dash__rating-value {
            color: #e2e8f0 !important;
        }
        .dark .mk-dash__panel-head {
            border-bottom-color: rgba(148, 163, 184, .15) !important;
        }
        .dark .mk-dash__activity-item,
        .dark .mk-dash__list-item,
        .dark .mk-dash__top-table tr {
            border-color: rgba(148, 163, 184, .12) !important;
        }
        .dark .mk-dash__hint,
        .dark .mk-svg-donut__pct,
        .dark .mk-dash__queue-hint,
        .dark .mk-dash__counter-hint {
            color: #94a3b8 !important;
        }
        .dark .mk-dash__badge {
            background: rgba(33, 178, 184, .18) !important;
            color: #5eead4 !important;
        }
        .dark .mk-dash__pipe-bar-track {
            background: rgba(148, 163, 184, .15) !important;
        }
        .dark .mk-dash__pipe-tab:not(.is-active) {
            background: rgba(148, 163, 184, .12) !important;
            border-color: rgba(148, 163, 184, .18) !important;
            color: #cbd5e1 !important;
        }
        .dark .mk-dash__pipe-tab:not(.is-active) .mk-dash__pipe-tab-count {
            background: rgba(148, 163, 184, .2) !important;
            color: #e2e8f0 !important;
        }
        .dark .mk-svg-donut__chart svg circle:first-child {
            stroke: rgba(148, 163, 184, .18) !important;
        }
        .dark .mk-svg-line__gridline {
            stroke: rgba(148, 163, 184, .15) !important;
        }
        .dark .mk-svg-line__axis-label {
            fill: #94a3b8 !important;
        }
    </style>

    <div class="mk-dash" id="{{ $widgetId }}" dir="rtl" style="--ring-pct: {{ $completion }};">
        {{-- Period filter bar --}}
        <div class="mk-dash__filterbar">
            <span class="mk-dash__filterbar-label">
                @include('filament.widgets.partials.icon', ['name' => 'calendar'])
                فلتر الفترة الزمنية
            </span>
            @foreach (($data['period']['options'] ?? []) as $opt)
                <button type="button"
                        class="mk-dash__period"
                        wire:click="$set('period', '{{ $opt['value'] }}')"
                        @if (($data['period']['current'] ?? '30') === $opt['value'])
                            style="background: #283979; color: #fff; border-color: #283979;"
                        @endif>
                    {{ $opt['label'] }}
                </button>
            @endforeach
            <button type="button"
                    class="mk-dash__period mk-dash__refresh"
                    wire:click="refresh"
                    title="تحديث البيانات الآن"
                    style="background: #fff; border-color: rgba(33,178,184,.4); color: #21b2b8;">
                @include('filament.widgets.partials.icon', ['name' => 'sparkles'])
                <span>تحديث الآن</span>
            </button>
            <span class="mk-dash__filterbar-hint">التغيرات في الترند تعكس حسب الفترة المختارة</span>
        </div>

        {{-- Hero --}}
        <section class="mk-dash__hero">
            <div>
                <h2>{{ $hero['title'] ?? '' }}</h2>
                <p>{{ $hero['subtitle'] ?? '' }}</p>
                <span class="mk-dash__hero-time">
                    @include('filament.widgets.partials.icon', ['name' => 'calendar', 'class' => 'mk-dash-svg'])
                    {{ $hero['now'] ?? '' }}
                </span>

                <div class="mk-dash__hero-stats">
                    <div class="mk-dash__hero-stat">
                        <span>إجمالي الميزانية</span>
                        <strong>{{ $hero['total_budget'] ?? '—' }}</strong>
                    </div>
                    <div class="mk-dash__hero-stat">
                        <span>دفعات خلال 30 يوم</span>
                        <strong>{{ $hero['upcoming_payments'] ?? '—' }}</strong>
                    </div>
                    <div class="mk-dash__hero-stat">
                        <span>متوسط رضا الخدمة</span>
                        <strong>{{ $hero['rating'] ?? '0/5' }} <small style="font-size: 11px; font-weight: 500; color: rgba(255,255,255,.6);">({{ $hero['rating_count'] ?? 0 }} تقييم)</small></strong>
                    </div>
                </div>
            </div>

            <div class="mk-dash__ring-wrap">
                <div class="mk-dash__ring" style="--pct: {{ $completion }};">
                    <strong>{{ $completion }}%</strong>
                    <span>اعتماد</span>
                </div>
                <span class="mk-dash__ring-label">نسبة المبادرات المعتمدة من الإجمالي</span>
            </div>
        </section>

        {{-- KPI cards --}}
        <div class="mk-dash__kpis">
            @foreach (($data['kpis'] ?? []) as $i => $kpi)
                <div class="mk-dash__kpi" data-tone="{{ $kpi['tone'] }}" style="animation-delay: {{ $i * 80 }}ms">
                    <div class="mk-dash__kpi-head">
                        <span class="mk-dash__kpi-icon">
                            @include('filament.widgets.partials.icon', ['name' => $kpi['icon']])
                        </span>
                        <span class="mk-dash__kpi-trend" data-direction="{{ $kpi['trend']['direction'] }}" title="{{ $data['period']['label'] ?? '' }}">
                            @if ($kpi['trend']['direction'] === 'up')
                                @include('filament.widgets.partials.icon', ['name' => 'arrow-up'])
                            @elseif ($kpi['trend']['direction'] === 'down')
                                @include('filament.widgets.partials.icon', ['name' => 'arrow-down'])
                            @else
                                @include('filament.widgets.partials.icon', ['name' => 'minus'])
                            @endif
                            <span>{{ $kpi['trend']['label'] }}</span>
                        </span>
                    </div>
                    <div class="mk-dash__kpi-value">{{ $kpi['value'] }}</div>
                    <div class="mk-dash__kpi-label">{{ $kpi['label'] }}</div>
                    <div class="mk-dash__kpi-hint">{{ $kpi['hint'] }}</div>
                    <div style="color: #8a94a6; font-size: 10px; font-weight: 500; margin-top: 4px;">{{ $data['period']['label'] ?? '' }}</div>
                </div>
            @endforeach
        </div>

        {{-- Finance strip --}}
        <section class="mk-dash__finance">
            <div class="mk-dash__finance-meter">
                <div class="mk-dash__finance-title">
                    @include('filament.widgets.partials.icon', ['name' => 'currency'])
                    تنفيذ الدفعات
                </div>
                <div class="mk-dash__finance-bar">
                    <span style="width: {{ $paidPercentage }}%; animation-delay: .25s"></span>
                </div>
                <small style="color: #283979; font-size: 11px; font-weight: 500;">{{ $paidPercentage }}% من الميزانية مدفوعة</small>
            </div>
            <div class="mk-dash__finance-cell">
                <span>إجمالي الميزانية</span>
                <strong>{{ $finance['total_budget'] ?? '—' }}</strong>
            </div>
            <div class="mk-dash__finance-cell">
                <span>إجمالي المدفوعات</span>
                <strong>{{ $finance['paid_total'] ?? '—' }}</strong>
            </div>
            <div class="mk-dash__finance-cell">
                <span>متوسط ميزانية المبادرة</span>
                <strong>{{ $finance['average_initiative'] ?? '—' }}</strong>
            </div>
            <div class="mk-dash__finance-cell">
                <span>دفعات متأخرة</span>
                <strong style="{{ ($finance['overdue_count'] ?? 0) > 0 ? 'color: #e57373;' : '' }}">{{ $finance['overdue_count'] ?? 0 }}</strong>
            </div>
        </section>

        {{-- Queue strip --}}
        <div class="mk-dash__section">
            <h3>@include('filament.widgets.partials.icon', ['name' => 'bell']) أولويات تحتاج متابعة</h3>
            <small>تحديث مباشر من بيانات النظام</small>
        </div>
        <div class="mk-dash__queue">
            @foreach (($data['queues'] ?? []) as $i => $item)
                <div class="mk-dash__queue-item" data-status="{{ $item['status'] }}" style="animation-delay: {{ $i * 60 }}ms">
                    <span class="mk-dash__queue-icon">
                        @include('filament.widgets.partials.icon', ['name' => $item['icon']])
                    </span>
                    <div class="mk-dash__queue-text">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts row 1 --}}
        <div class="mk-dash__section">
            <h3>@include('filament.widgets.partials.icon', ['name' => 'chart']) إنفوجرافيك تفاعلي</h3>
            <small>تحرّك الفأرة فوق المخططات للتفاصيل</small>
        </div>
        <div class="mk-dash__grid">
            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>الترند الشهري (آخر 12 شهراً)</strong>
                    <span class="mk-dash__badge">مبادرات · استشارات · زيارات · تقارير</span>
                </div>
                <div class="mk-dash__panel-body">
                    @include('filament.widgets.partials.sparkline', ['ts' => $chartPayload['timeseries'] ?? []])
                </div>
            </section>

            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>توزيع المبادرات على الحالات</strong>
                    <span class="mk-dash__badge">funnel</span>
                </div>
                <div class="mk-dash__panel-body">
                    @include('filament.widgets.partials.donut', ['items' => $chartPayload['initiatives_by_status'] ?? []])
                </div>
            </section>
        </div>

        {{-- Charts row 2 --}}
        <div class="mk-dash__grid mk-dash__grid--equal">
            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>توزيع الجهات حسب النوع</strong>
                </div>
                <div class="mk-dash__panel-body">
                    @include('filament.widgets.partials.donut', ['items' => $chartPayload['organizations_by_type'] ?? []])
                </div>
            </section>

            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>المستخدمون حسب الدور</strong>
                </div>
                <div class="mk-dash__panel-body">
                    @include('filament.widgets.partials.donut', ['items' => $chartPayload['users_by_role'] ?? []])
                </div>
            </section>
        </div>

        {{-- Pipelines + Specializations --}}
        <div class="mk-dash__grid">
            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>مسارات التنفيذ (Pipeline)</strong>
                    <span class="mk-dash__badge">إنفوجرافيك الحالات</span>
                </div>
                <div class="mk-dash__panel-body">
                    @php
                        $pipelines = $data['pipelines'] ?? [];
                        $firstPipelineKey = $pipelines[0]['key'] ?? 'initiatives';
                    @endphp
                    <div class="mk-dash__pipe" x-data="{ active: @js($firstPipelineKey) }">
                        <div class="mk-dash__pipe-tabs" role="tablist">
                            @foreach ($pipelines as $pipeline)
                                @php $totalCount = array_sum(array_column($pipeline['items'] ?? [], 'value')); @endphp
                                <button type="button"
                                        class="mk-dash__pipe-tab"
                                        role="tab"
                                        x-on:click="active = @js($pipeline['key'])"
                                        x-bind:class="{ 'is-active': active === @js($pipeline['key']) }">
                                    @include('filament.widgets.partials.icon', ['name' => $pipeline['icon']])
                                    <span>{{ $pipeline['title'] }}</span>
                                    <span class="mk-dash__pipe-tab-count">{{ $totalCount }}</span>
                                </button>
                            @endforeach
                        </div>
                        @foreach ($pipelines as $pipeline)
                            <div class="mk-dash__pipe-panel"
                                 role="tabpanel"
                                 x-bind:class="{ 'is-active': active === @js($pipeline['key']) }">
                                @foreach (($pipeline['items'] ?? []) as $item)
                                    <div class="mk-dash__pipe-row">
                                        <div class="mk-dash__pipe-meta">
                                            <span>{{ $item['label'] }}</span>
                                            <strong>{{ $item['value'] }} · {{ $item['percentage'] }}%</strong>
                                        </div>
                                        <div class="mk-dash__pipe-bar"><span style="width: {{ max(2, (int) $item['percentage']) }}%"></span></div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>الاستشارات حسب التخصص</strong>
                </div>
                <div class="mk-dash__panel-body">
                    @include('filament.widgets.partials.donut', ['items' => $chartPayload['consultations_by_specialization'] ?? []])
                </div>
            </section>
        </div>

        {{-- Top lists --}}
        <div class="mk-dash__grid mk-dash__grid--equal">
            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>الجهات الأكثر نشاطاً</strong>
                    <span class="mk-dash__badge">Top 5</span>
                </div>
                <div class="mk-dash__panel-body">
                    <div class="mk-dash__list">
                        @forelse (($data['organizations'] ?? []) as $org)
                            <div class="mk-dash__row">
                                <div>
                                    <strong>{{ $org['name'] }}</strong>
                                    <small style="display:block;margin-top:2px;">{{ $org['status'] }}</small>
                                </div>
                                <span class="mk-dash__badge">{{ $org['initiatives'] }} مبادرة</span>
                                <span class="mk-dash__badge">{{ $org['tickets'] }} استشارة مفتوحة</span>
                            </div>
                        @empty
                            <div class="mk-dash__row"><strong>لا توجد جهات بعد</strong></div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>أكفأ المستشارين</strong>
                    <span class="mk-dash__badge">Top 5</span>
                </div>
                <div class="mk-dash__panel-body">
                    <div class="mk-dash__list">
                        @forelse (($data['consultants'] ?? []) as $consultant)
                            <div class="mk-dash__row">
                                <strong>{{ $consultant['name'] }}</strong>
                                <span class="mk-dash__badge">{{ $consultant['completed'] }} مكتملة</span>
                                <span class="mk-dash__badge">{{ $consultant['open'] }} نشطة</span>
                            </div>
                        @empty
                            <div class="mk-dash__row"><strong>لا يوجد مستشارون مسجلون</strong></div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        {{-- Budget initiatives + Ratings --}}
        <div class="mk-dash__grid mk-dash__grid--equal">
            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>أكبر المبادرات قيمةً</strong>
                    <span class="mk-dash__badge">Top 5</span>
                </div>
                <div class="mk-dash__panel-body">
                    <div class="mk-dash__list">
                        @forelse (($data['budget_initiatives'] ?? []) as $initiative)
                            <div class="mk-dash__row">
                                <div>
                                    <strong>{{ $initiative['name'] }}</strong>
                                    <small style="display:block;margin-top:2px;">{{ $initiative['organization'] }} · {{ $initiative['status'] }}</small>
                                </div>
                                <span class="mk-dash__badge" style="background: rgba(33,178,184,.14); color: #21b2b8;">{{ $initiative['budget'] }}</span>
                                <span></span>
                            </div>
                        @empty
                            <div class="mk-dash__row"><strong>لا توجد مبادرات بعد</strong></div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="mk-dash__panel">
                <div class="mk-dash__panel-head">
                    <strong>توزيع تقييمات الخدمة</strong>
                    <span class="mk-dash__badge">⭐ {{ $hero['rating'] ?? '0/5' }}</span>
                </div>
                <div class="mk-dash__panel-body">
                    <div class="mk-dash__rating">
                        @php
                            $maxRating = collect($data['evaluations']['distribution'] ?? [])->max('value') ?: 1;
                        @endphp
                        @forelse (($data['evaluations']['distribution'] ?? []) as $row)
                            <div class="mk-dash__rating-row">
                                <span>{{ $row['label'] }}</span>
                                <div class="mk-dash__rating-bar">
                                    <span style="width: {{ (int) ($row['value'] / $maxRating * 100) }}%"></span>
                                </div>
                                <strong>{{ $row['value'] }}</strong>
                            </div>
                        @empty
                            <div class="mk-dash__row"><strong>لا توجد تقييمات بعد</strong></div>
                        @endforelse
                    </div>

                    @if (!empty($data['evaluations']['by_type']))
                        <div style="margin-top: 14px;">
                            <small style="color: #6b7280; font-size: 11px; font-weight: 500; display:block; margin-bottom: 8px;">حسب نوع الخدمة</small>
                            @foreach (($data['evaluations']['by_type'] ?? []) as $row)
                                <div class="mk-dash__row" style="margin-bottom: 6px;">
                                    <strong>{{ $row['label'] }}</strong>
                                    <span class="mk-dash__badge">{{ $row['count'] }} تقييم</span>
                                    <span class="mk-dash__badge" style="background: rgba(249,173,28,.14); color: #f9ad1c;">⭐ {{ $row['rating'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- Counters strip --}}
        <div class="mk-dash__counters">
            @foreach (($data['counters'] ?? []) as $counter)
                <div class="mk-dash__counter">
                    <span class="mk-dash__counter-icon">
                        @include('filament.widgets.partials.icon', ['name' => $counter['icon']])
                    </span>
                    <div class="mk-dash__counter-text">
                        <span>{{ $counter['label'] }}</span>
                        <strong>{{ $counter['value'] }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Activity feed --}}
        <section class="mk-dash__panel">
            <div class="mk-dash__panel-head">
                <strong>@include('filament.widgets.partials.icon', ['name' => 'pulse']) نبض النشاط الأخير</strong>
                <span class="mk-dash__badge">سجل النشاط</span>
            </div>
            <div class="mk-dash__panel-body">
                <div class="mk-dash__activity">
                    @forelse (($data['activity'] ?? []) as $activity)
                        <div class="mk-dash__activity-item">
                            <span class="mk-dash__activity-icon">
                                @include('filament.widgets.partials.icon', ['name' => match ($activity['log_key'] ?? '') {
                                    'initiatives' => 'sparkles',
                                    'consultations' => 'chat',
                                    'visit_reports' => 'map',
                                    'monthly_reports' => 'document',
                                    'service_evaluations' => 'star',
                                    'organization' => 'building',
                                    default => 'pulse',
                                }])
                            </span>
                            <div class="mk-dash__activity-text">
                                <strong>{{ $activity['log'] }} · {{ $activity['causer'] }}</strong>
                                <p>{{ $activity['description'] }}</p>
                            </div>
                            <span class="mk-dash__activity-time">{{ $activity['time'] }}</span>
                        </div>
                    @empty
                        <div class="mk-dash__activity-item">
                            <span class="mk-dash__activity-icon">
                                @include('filament.widgets.partials.icon', ['name' => 'inbox'])
                            </span>
                            <div class="mk-dash__activity-text">
                                <strong>لا توجد أنشطة مسجلة بعد</strong>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-filament-widgets::widget>
