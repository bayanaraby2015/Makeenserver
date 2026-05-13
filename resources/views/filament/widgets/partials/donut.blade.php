@php
    /**
     * Server-rendered SVG donut chart. Works without JavaScript so the panel
     * is never blank, even if Chart.js fails to load.
     *
     * @var array<int, array{label: string, value: int}> $items
     * @var array<int, string>|null $palette
     */
    $items = $items ?? [];
    $palette = $palette ?? ['#283979', '#21b2b8', '#f9ad1c', '#e57373', '#56678a', '#16a34a', '#9c27b0', '#ff7043'];

    $total = array_sum(array_map(fn ($i) => (int) ($i['value'] ?? 0), $items));
    $radius = 60;
    $circumference = 2 * M_PI * $radius;

    // Compute each segment's stroke-dasharray + offset.
    $segments = [];
    $cumulative = 0;
    foreach ($items as $idx => $item) {
        $value = (int) ($item['value'] ?? 0);
        $share = $total > 0 ? ($value / $total) : 0;
        $segmentLength = $share * $circumference;
        $segments[] = [
            'label' => $item['label'] ?? '',
            'value' => $value,
            'pct' => $total > 0 ? round($share * 100) : 0,
            'color' => $palette[$idx % count($palette)],
            'dash' => $segmentLength.' '.($circumference - $segmentLength),
            'offset' => -$cumulative,
        ];
        $cumulative += $segmentLength;
    }
@endphp

<div class="mk-svg-donut">
    <div class="mk-svg-donut__chart">
        <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="مخطط دائري">
            <circle cx="80" cy="80" r="{{ $radius }}" fill="none" stroke="#f1f3f9" stroke-width="20"></circle>
            @foreach ($segments as $seg)
                @if ($seg['value'] > 0)
                    <circle cx="80" cy="80" r="{{ $radius }}"
                            fill="none"
                            stroke="{{ $seg['color'] }}"
                            stroke-width="20"
                            stroke-dasharray="{{ $seg['dash'] }}"
                            stroke-dashoffset="{{ $seg['offset'] }}"
                            transform="rotate(-90 80 80)"
                            style="transition: stroke-dasharray .8s ease">
                        <title>{{ $seg['label'] }}: {{ $seg['value'] }} ({{ $seg['pct'] }}%)</title>
                    </circle>
                @endif
            @endforeach
            <text x="80" y="76" text-anchor="middle"
                  style="font-family: 'Alexandria', 'IBM Plex Sans Arabic', sans-serif; font-weight: 500; font-size: 22px; fill: #283979;">
                {{ $total }}
            </text>
            <text x="80" y="94" text-anchor="middle"
                  style="font-family: 'Alexandria', 'IBM Plex Sans Arabic', sans-serif; font-weight: 500; font-size: 9px; fill: #6b7280;">
                المجموع
            </text>
        </svg>
    </div>
    <ul class="mk-svg-donut__legend">
        @forelse ($segments as $seg)
            <li>
                <span class="mk-svg-donut__swatch" style="background: {{ $seg['color'] }};"></span>
                <span class="mk-svg-donut__label">{{ $seg['label'] }}</span>
                <strong class="mk-svg-donut__value">{{ $seg['value'] }}</strong>
                <small class="mk-svg-donut__pct">{{ $seg['pct'] }}%</small>
            </li>
        @empty
            <li class="mk-svg-donut__empty">لا توجد بيانات</li>
        @endforelse
    </ul>
</div>
