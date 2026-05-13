<?php

/*
|--------------------------------------------------------------------------
| Makeen Platform — Brand Identity
|--------------------------------------------------------------------------
|
| Source: official "Primary Colors" PDF + Makeen logo + Masar Al Ejadh logo.
| All UI colors and panel themes derive from this single source of truth.
|
| Primary palette (from Primary Colors.pdf — confirmed via pdftotext):
|   - Teal/Cyan   #21b2b8 — RGB(33,178,184)   — calm/trust, finance & data
|   - Navy         #283979 — RGB(40,57,121)    — Masar Al Ejadh brand color
|   - Gold/Amber   #f9ad1c — RGB(249,173,28)   — Makeen brand color (CTA/accent)
|   - Dark Slate   #2b354f — RGB(43,53,79)     — professional neutral
|
| Panel-color rationale:
|   The platform is published by "Masar Al Ejadh" and operated under the
|   "Makeen" brand. We use Masar's NAVY as the dominant UI color (admin and
|   excellence panels) because:
|     1. Navy → white text gives the strongest WCAG AAA contrast on buttons.
|     2. The platform owner is Masar Al Ejadh, so the system feel matches.
|     3. Gold remains as the Makeen identity highlight inside logos & badges.
|
| We deliberately reuse the 4 PDF colors across the 5 panels (admin and
| excellence both use Navy) instead of inventing a 5th brand-foreign color.
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Brand names
    |--------------------------------------------------------------------------
    */
    'platform_name_ar' => 'مكين',
    'platform_name_en' => 'Makeen',
    'platform_tagline_ar' => 'تطوير المنظمات غير الربحية',
    'platform_tagline_en' => 'Non-Profit Organizations Development',

    'parent_program_name_ar' => 'مسار الإجادة',
    'parent_program_name_en' => 'Masar Al Ejadh',

    /*
    |--------------------------------------------------------------------------
    | Logo paths (served from /public)
    |--------------------------------------------------------------------------
    */
    'logo' => [
        'makeen_full' => '/brand/makeen-logo.png',
        'makeen_header' => '/brand/makeen-logo-header.png',
        'masar_full' => '/brand/masar-logo.png',
        'masar_header' => '/brand/masar-logo-header.png',
        'favicon' => '/brand/favicon.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary palette (from Primary Colors.pdf)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'gold' => '#f9ad1c',
        'navy' => '#283979',
        'teal' => '#21b2b8',
        'slate' => '#2b354f',
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel color assignment
    |--------------------------------------------------------------------------
    | Each Filament panel gets one brand color. Filament 4 generates a full
    | tonal palette (50..950) from the given hex via Color::hex().
    */
    'panel_colors' => [
        'admin' => '#283979',       // Masar navy — platform control
        'excellence' => '#283979',  // Masar navy — Al Ejadh team panel
        'donor' => '#21b2b8',       // Brand teal — funding organization
        'consultant' => '#2b354f',  // Brand slate — advisory
        'association' => '#f9ad1c', // Makeen gold — associations panel
    ],

    /*
    |--------------------------------------------------------------------------
    | Typography
    |--------------------------------------------------------------------------
    | Primary UI font for both Arabic and Latin glyphs. Loaded from Google
    | Fonts on every panel and the public guest layout. Falls back to native
    | system fonts if the network request fails.
    */
    'font_family' => 'Alexandria',
    'font_weights' => [300, 400, 500, 600, 700],
    'font_google_url' => 'https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700&display=swap',
];
