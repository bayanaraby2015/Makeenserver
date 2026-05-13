@php
    /**
     * Inline SVG icon set used by the admin dashboard widget.
     * Heroicons-inspired strokes (24x24, currentColor).
     *
     * @var string $name Icon key.
     * @var string|null $class Optional extra class on the <svg>.
     */
    $icons = [
        'sparkles'  => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1" stroke-linecap="round"/><circle cx="12" cy="12" r="3.5"/>',
        'building'  => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2M10 21v-3h4v3" stroke-linecap="round"/>',
        'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17" cy="9" r="2.5"/><path d="M14 20c0-2.5 2-4.5 4.5-4.5S23 17.5 23 20"/>',
        'chat'      => '<path d="M21 12a8 8 0 0 1-11.5 7.2L4 21l1.8-5.5A8 8 0 1 1 21 12z"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
        'inbox'     => '<path d="M3 13l3-8h12l3 8M3 13v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6M3 13h5l1 2h6l1-2h5"/>',
        'pencil'    => '<path d="M4 20l4-1 11-11-3-3L5 16l-1 4z"/>',
        'bell'      => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9zM10 21a2 2 0 0 0 4 0"/>',
        'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
        'map'       => '<path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14"/>',
        'document'  => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'currency'  => '<circle cx="12" cy="12" r="9"/><path d="M14.5 9.5a2.5 2.5 0 0 0-5 0c0 1.4 1.1 2 2.5 2s2.5.6 2.5 2a2.5 2.5 0 0 1-5 0M12 7v10"/>',
        'star'      => '<path d="M12 3l2.7 5.7 6.3.6-4.8 4.3 1.5 6.2L12 16.9 6.3 19.8l1.5-6.2L3 9.3l6.3-.6L12 3z"/>',
        'flow'      => '<path d="M5 7h6m0 0v10m0-10l3-3m-3 13l3 3M18 12l3 3-3 3M3 17h6m12-10h-6"/>',
        'chart'     => '<path d="M4 19V5M4 19h16M9 15v-5M13 15V8M17 15v-3" stroke-linecap="round"/>',
        'arrow-up'   => '<path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/>',
        'arrow-down' => '<path d="M12 5v14M5 12l7 7 7-7" stroke-linecap="round" stroke-linejoin="round"/>',
        'minus'      => '<path d="M5 12h14" stroke-linecap="round"/>',
        'pulse'      => '<path d="M3 12h4l3-7 4 14 3-7h4" stroke-linecap="round" stroke-linejoin="round"/>',
    ];

    $name = $name ?? 'sparkles';
    $class = $class ?? '';
    $body = $icons[$name] ?? $icons['sparkles'];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="{{ $class }}" aria-hidden="true">{!! $body !!}</svg>
