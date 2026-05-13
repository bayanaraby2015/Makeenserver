@php
    $appName = config('brand.platform_name_ar');
    $isRtl = app()->getLocale() === 'ar';
    $colors = config('brand.colors');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ $appName }}</title>
    <link rel="icon" href="{{ asset(config('brand.logo.favicon')) }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ config('brand.font_google_url', 'https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700&display=swap') }}">

    <style>
        :root {
            --brand-gold: {{ $colors['gold'] }};
            --brand-navy: {{ $colors['navy'] }};
            --brand-teal: {{ $colors['teal'] }};
            --brand-slate: {{ $colors['slate'] }};
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: '{{ config('brand.font_family', 'Alexandria') }}', 'Segoe UI', Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
            color: var(--brand-slate);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .topbar img { height: 70px; }
        .topbar-right { color: var(--brand-slate); font-size: .9rem; }
        main { flex: 1; padding: 2rem 1rem; }
        .container { max-width: 880px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, .08);
            padding: 2rem;
        }
        h1 { color: var(--brand-slate); margin: 0 0 .5rem; font-size: 1.75rem; }
        .subtitle { color: #64748b; font-size: .95rem; margin: 0 0 2rem; line-height: 1.7; }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem 1.25rem;
        }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
        .full { grid-column: 1 / -1; }
        .field label {
            display: block;
            font-size: .9rem;
            color: var(--brand-slate);
            font-weight: 600;
            margin-bottom: .35rem;
        }
        .field input, .field textarea, .field select {
            width: 100%;
            padding: .65rem .85rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
            font-size: .95rem;
            font-family: inherit;
            color: var(--brand-slate);
            transition: border-color .15s, box-shadow .15s;
        }
        .field input:focus, .field textarea:focus, .field select:focus {
            outline: none;
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 3px rgba(249, 173, 28, .18);
        }
        .field textarea { min-height: 80px; resize: vertical; }
        .field .error { color: #dc2626; font-size: .82rem; margin-top: .35rem; }
        .section-title {
            color: var(--brand-navy);
            font-size: 1.05rem;
            font-weight: 700;
            margin: 2rem 0 1rem;
            padding-{{ $isRtl ? 'right' : 'left' }}: .75rem;
            border-{{ $isRtl ? 'right' : 'left' }}: 4px solid var(--brand-gold);
        }
        .section-title:first-child { margin-top: 0; }
        .checkbox {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            background: #fef9ef;
            border: 1px solid #fde9bc;
            border-radius: 10px;
            padding: .85rem 1rem;
        }
        .checkbox input { margin-top: .2rem; }
        .checkbox label { font-size: .9rem; color: var(--brand-slate); }
        button[type="submit"] {
            background: var(--brand-navy);
            color: #fff;
            font-weight: 700;
            padding: .85rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: background .15s, transform .05s;
        }
        button[type="submit"]:hover { background: #1e2d5f; }
        button[type="submit"]:active { transform: translateY(1px); }
        .alt-link {
            display: inline-block;
            margin-{{ $isRtl ? 'right' : 'left' }}: 1rem;
            color: var(--brand-teal);
            text-decoration: none;
            font-size: .9rem;
        }
        .alt-link:hover { text-decoration: underline; }
        footer {
            background: var(--brand-slate);
            color: #cbd5e1;
            text-align: center;
            padding: 1.5rem 1rem;
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            font-size: .85rem;
        }
        footer img { height: 38px; background: #fff; padding: 4px 10px; border-radius: 8px; }
    </style>
</head>
<body>
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:1rem;">
            <img src="{{ asset(config('brand.logo.makeen_full')) }}" alt="{{ $appName }}">
            <span style="width:1px;height:32px;background:#fde9bc;display:block;" aria-hidden="true"></span>
            <img src="{{ asset(config('brand.logo.masar_full')) }}" alt="{{ config('brand.parent_program_name_ar') }}">
        </div>
        <div class="topbar-right">{{ config('brand.platform_tagline_ar') }}</div>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer>
        <span>{{ __('register.footer.powered_by') }}</span>
        <img src="{{ asset(config('brand.logo.masar_full')) }}" alt="{{ config('brand.parent_program_name_ar') }}">
        <span>© {{ now()->year }} {{ $appName }}</span>
    </footer>
</body>
</html>
