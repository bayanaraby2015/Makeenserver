<x-filament-panels::page>
    @php
        $events = $this->getCalendarEvents();
        $labels = [
            'today' => __('consultations.calendar.today'),
            'month' => __('consultations.calendar.month'),
            'week' => __('consultations.calendar.week'),
            'day' => __('consultations.calendar.day'),
            'list' => __('consultations.calendar.list'),
            'empty' => __('consultations.calendar.empty'),
            'status' => __('consultations.fields.status'),
            'organization' => __('consultations.fields.requester_organization'),
            'consultant' => __('consultations.fields.consultant'),
            'meeting_url' => __('consultations.fields.meeting_url'),
        ];
    @endphp

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">

    <style>
        .consultation-calendar-shell {
            display: grid;
            gap: 1rem;
            direction: rtl;
        }

        .consultation-calendar-card {
            overflow: hidden;
            border: 1px solid #e7ecf3;
            border-radius: .75rem;
            background: #fff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
        }

        .consultation-calendar-header {
            border-bottom: 1px solid #e7ecf3;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, rgba(40, 57, 121, .08), rgba(184, 138, 58, .08));
        }

        .consultation-calendar-header h2 {
            margin: 0;
            color: #283979;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        #consultation-calendar {
            min-height: 560px;
            padding: 1rem;
            direction: ltr;
        }

        #consultation-calendar .fc-toolbar-title {
            color: #162033;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        #consultation-calendar .fc-button-primary {
            border-color: #283979;
            background: #283979;
        }

        #consultation-calendar .fc-button-primary:not(:disabled):hover,
        #consultation-calendar .fc-button-primary:not(:disabled).fc-button-active {
            border-color: #b88a3a;
            background: #b88a3a;
        }

        #consultation-calendar .fc-event {
            border: 0;
            border-radius: 6px;
            padding: 2px 4px;
            font-weight: 700;
        }

        .dark .consultation-calendar-card {
            border-color: #1e293b;
            background: #0f172a;
        }

        .dark .consultation-calendar-header {
            border-color: #1e293b;
            background: linear-gradient(135deg, rgba(66, 86, 163, .2), rgba(184, 138, 58, .08)), #111827;
        }

        .dark .consultation-calendar-header h2,
        .dark #consultation-calendar .fc-toolbar-title {
            color: #e5e7eb;
        }
    </style>

    <div class="consultation-calendar-shell">
        <div class="consultation-calendar-card">
            <div class="consultation-calendar-header">
                <h2>{{ __('consultations.calendar.title') }}</h2>
            </div>
            <div id="consultation-calendar"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('consultation-calendar');
            const events = @json($events);
            const labels = @json($labels);

            if (!el || typeof FullCalendar === 'undefined') {
                return;
            }

            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                locale: document.documentElement.lang || 'ar',
                direction: 'rtl',
                height: 'auto',
                events,
                noEventsContent: labels.empty,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
                },
                buttonText: {
                    today: labels.today,
                    month: labels.month,
                    week: labels.week,
                    day: labels.day,
                    list: labels.list,
                },
                eventDidMount(info) {
                    const props = info.event.extendedProps || {};
                    const details = [
                        props.status ? `${labels.status}: ${props.status}` : null,
                        props.organization ? `${labels.organization}: ${props.organization}` : null,
                        props.consultant ? `${labels.consultant}: ${props.consultant}` : null,
                        props.meeting_url ? `${labels.meeting_url}: ${props.meeting_url}` : null,
                    ].filter(Boolean).join('\n');

                    if (details) {
                        info.el.setAttribute('title', details);
                    }
                },
            });

            calendar.render();
        });
    </script>
</x-filament-panels::page>
