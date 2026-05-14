@php
    /**
     * Inline SVG icon set used by the admin dashboard widget.
     * Clean, visually balanced 24x24 paths drawn from Heroicons v1/v2
     * outline. All paths are designed to render centered within the
     * 24x24 viewBox so the SVG fits perfectly inside the colored chip.
     *
     * @var string $name Icon key.
     * @var string|null $class Optional extra class on the <svg>.
     */
    $icons = [
        'sparkles'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16 2.286 6.857L23 12l-6.857 2.143L14 21l-2.143-6.857L5 12l6.857-2.143L14 3z"/>',
        'building'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 21V5a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v16M3 21h18M9 7h.01M9 11h.01M9 15h.01M15 7h.01M15 11h.01M15 15h.01M10 21v-4h4v4"/>',
        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m0 0a4 4 0 1 1 8 0m-4-6a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6 0a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>',
        'chat'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z"/>',
        'briefcase' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7zM8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'inbox'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6L18.55 5.11A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'pencil'    => '<path stroke-linecap="round" stroke-linejoin="round" d="m18 2 4 4-13 13H5v-4L18 2zm-3 3 4 4"/>',
        'bell'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>',
        'calendar'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM8 3v4M16 3v4"/>',
        'map'       => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 6-7 3-6-3-6 3v12l6-3 6 3 7-3V6zM9 6v12M14 9v12"/>',
        'document'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 2v6h6M9 13h6M9 17h6M9 9h2"/>',
        'currency'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22m5-18H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'star'      => '<path stroke-linecap="round" stroke-linejoin="round" d="m12 2 3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"/>',
        'flow'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6h6v6H3V6zm0 12h6m6 0h6m0-12h-6v6h6V6z"/>',
        'chart'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'arrow-up'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-7 7 7-7 7 7"/>',
        'arrow-down' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7 7 7 7-7"/>',
        'minus'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>',
        'pulse'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    ];

    $name = $name ?? 'sparkles';
    $body = $icons[$name] ?? $icons['sparkles'];
    $class = trim('mk-icon ' . ($class ?? ''));
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="{{ $class }}" aria-hidden="true" focusable="false">{!! $body !!}</svg>
