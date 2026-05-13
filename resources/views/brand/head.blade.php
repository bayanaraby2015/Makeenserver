<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
      href="{{ config('brand.font_google_url', 'https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700&display=swap') }}">

<style>
    body.fi-body {
        font-family: '{{ config('brand.font_family', 'Alexandria') }}', system-ui, -apple-system, sans-serif !important;
        background:
            linear-gradient(180deg, rgba(33, 178, 184, .045), rgba(249, 173, 28, .03) 42%, rgba(255,255,255,0) 72%),
            #f8fafc;
    }

    :root {
        --makeen-navy: #283979;
        --makeen-teal: #21b2b8;
        --makeen-slate: #2b354f;
        --makeen-gold: #f9ad1c;
    }

    .fi-color-primary {
        --color-300: oklch(0.42 0.13 269);
        --color-400: oklch(0.36 0.13 269);
        --color-500: oklch(0.36 0.13 269);
        --color-600: oklch(0.32 0.13 269);
        --color-700: oklch(0.28 0.13 269);
        --color-950: oklch(1 0 0);
    }

    .fi-ac-btn-action.fi-color-primary,
    .fi-btn.fi-color-primary {
        box-shadow: 0 10px 24px rgba(40, 57, 121, .16);
    }

    .fi-ac-btn-action.fi-color-primary span,
    .fi-ac-btn-action.fi-color-primary svg,
    .fi-btn.fi-color-primary span,
    .fi-btn.fi-color-primary svg {
        color: #ffffff !important;
        fill: currentColor;
    }

    .fi-sidebar,
    .fi-topbar nav {
        border-color: rgba(40, 57, 121, .10) !important;
    }

    .fi-sidebar-item-active a,
    .fi-sidebar-item-active button {
        background: linear-gradient(90deg, rgba(40, 57, 121, .10), rgba(33, 178, 184, .08)) !important;
        border-inline-start: 3px solid var(--makeen-gold);
    }

    .fi-section,
    .fi-ta-ctn,
    .fi-in-entry-wrp,
    .fi-fo-field-wrp {
        border-radius: 12px;
    }

    .fi-section-header {
        border-top: 3px solid rgba(33, 178, 184, .85);
    }

    .fi-badge {
        border-radius: 8px;
    }

    .fi-page-dashboard .fi-wi-widget,
    .fi-page-dashboard .fi-wi-stats-overview-stat,
    .fi-page-dashboard .fi-ta-ctn {
        animation: makeen-dashboard-rise .45s ease both;
    }

    .fi-page-dashboard .fi-wi-stats-overview-stat {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(33, 178, 184, .07)),
            #ffffff;
        border: 1px solid rgba(40, 57, 121, .10);
        box-shadow: 0 14px 34px rgba(43, 53, 79, .09);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .fi-page-dashboard .fi-wi-stats-overview-stat:hover {
        border-color: rgba(33, 178, 184, .34);
        box-shadow: 0 22px 46px rgba(40, 57, 121, .14);
        transform: translateY(-3px);
    }

    .fi-page-dashboard .fi-wi-chart,
    .fi-page-dashboard .fi-ta-ctn {
        border: 1px solid rgba(40, 57, 121, .10);
        box-shadow: 0 14px 34px rgba(43, 53, 79, .08);
    }

    .fi-page-dashboard .fi-header-heading {
        color: var(--makeen-navy);
        letter-spacing: 0;
    }

    @keyframes makeen-dashboard-rise {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@if(app()->isLocale('ar'))
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const locale = {
                weekdays: {
                    shorthand: ['أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت'],
                    longhand: ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                },
                months: {
                    shorthand: ['ينا', 'فبر', 'مار', 'أبر', 'مايو', 'يون', 'يول', 'أغس', 'سبت', 'أكت', 'نوف', 'ديس'],
                    longhand: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                },
                rangeSeparator: ' إلى ',
                weekAbbreviation: 'أسبوع',
                scrollTitle: 'مرر للتغيير',
                toggleTitle: 'اضغط للتبديل',
                amPM: ['ص', 'م'],
                firstDayOfWeek: 6,
            };

            const localizeDatePickers = () => {
                if (window.flatpickr?.localize) {
                    window.flatpickr.localize(locale);
                }
            };

            localizeDatePickers();
            setTimeout(localizeDatePickers, 300);
            setTimeout(localizeDatePickers, 1000);
        });
    </script>
@endif
