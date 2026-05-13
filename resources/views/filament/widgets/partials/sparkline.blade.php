@php
    /**
     * Server-rendered multi-series SVG line chart for the dashboard's monthly trend.
     * No JavaScript dependency.
     *
     * @var array{labels?: array<int, string>, initiatives?: array<int, int>, consultations?: array<int, int>, visit_reports?: array<int, int>, monthly_reports?: array<int, int>} $ts
     */
    $ts = $ts ?? [];
    $labels = $ts['labels'] ?? [];
    $series = [
        ['key' => 'initiatives',     'label' => 'مبادرات',       'color' => '#283979', 'values' => $ts['initiatives']     ?? []],
        ['key' => 'consultations',   'label' => 'استشارات',      'color' => '#21b2b8', 'values' => $ts['consultations']   ?? []],
        ['key' => 'visit_reports',   'label' => 'زيارات',        'color' => '#f9ad1c', 'values' => $ts['visit_reports']   ?? []],
        ['key' => 'monthly_reports', 'label' => 'تقارير شهرية', 'color' => '#9c27b0', 'values' => $ts['monthly_reports'] ?? []],
    ];

    $width = 720;
    $height = 220;
    $padX = 24;
    $padTop = 14;
    $padBottom = 30;

    $count = max(count($labels), 1);
    $stepX = $count > 1 ? ($width - 2 * $padX) / ($count - 1) : 0;

    // Compute global max across all series so they share a y-axis.
    $allValues = array_merge(...array_map(fn ($s) => $s['values'], $series));
    $max = count($allValues) > 0 ? max($allValues) : 0;
    $max = $max > 0 ? $max : 1;

    $plotHeight = $height - $padTop - $padBottom;
@endphp

<div class="mk-svg-line">
    <svg viewBox="0 0 {{ $width }} {{ $height }}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="مخطط زمني">
        {{-- gridlines --}}
        @for ($g = 0; $g <= 4; $g++)
            @php $y = $padTop + ($plotHeight * $g / 4); @endphp
            <line x1="{{ $padX }}" y1="{{ $y }}" x2="{{ $width - $padX }}" y2="{{ $y }}"
                  stroke="rgba(40,57,121,.06)" stroke-width="1"></line>
            <text x="{{ $width - $padX + 4 }}" y="{{ $y + 4 }}"
                  style="font-family: 'Alexandria', sans-serif; font-size: 9px; fill: #94a3b8;">
                {{ (int) round($max * (1 - $g / 4)) }}
            </text>
        @endfor

        {{-- x-axis month labels --}}
        @foreach ($labels as $i => $label)
            @php $x = $padX + $stepX * $i; @endphp
            <text x="{{ $x }}" y="{{ $height - 10 }}" text-anchor="middle"
                  style="font-family: 'Alexandria', sans-serif; font-size: 10px; fill: #6b7280;">
                {{ $label }}
            </text>
        @endforeach

        {{-- series paths --}}
        @foreach ($series as $s)
            @if (count($s['values']) > 0)
                @php
                    $points = [];
                    foreach ($s['values'] as $i => $v) {
                        $x = $padX + $stepX * $i;
                        $y = $padTop + $plotHeight * (1 - ($max > 0 ? ($v / $max) : 0));
                        $points[] = round($x, 1).','.round($y, 1);
                    }
                    $polyline = implode(' ', $points);
                    // Build area polygon: line then close along bottom edge.
                    $firstX = $padX;
                    $lastX = $padX + $stepX * (count($s['values']) - 1);
                    $bottom = $padTop + $plotHeight;
                @endphp
                <polygon points="{{ $polyline }} {{ round($lastX, 1) }},{{ $bottom }} {{ round($firstX, 1) }},{{ $bottom }}"
                         fill="{{ $s['color'] }}" fill-opacity="0.10"></polygon>
                <polyline points="{{ $polyline }}"
                          fill="none"
                          stroke="{{ $s['color'] }}"
                          stroke-width="2"
                          stroke-linejoin="round"
                          stroke-linecap="round"></polyline>
                @foreach ($s['values'] as $i => $v)
                    @php
                        $x = $padX + $stepX * $i;
                        $y = $padTop + $plotHeight * (1 - ($max > 0 ? ($v / $max) : 0));
                    @endphp
                    <circle cx="{{ round($x, 1) }}" cy="{{ round($y, 1) }}" r="3" fill="{{ $s['color'] }}">
                        <title>{{ $s['label'] }} ({{ $labels[$i] ?? '' }}): {{ $v }}</title>
                    </circle>
                @endforeach
            @endif
        @endforeach
    </svg>

    <ul class="mk-svg-line__legend">
        @foreach ($series as $s)
            <li>
                <span class="mk-svg-line__swatch" style="background: {{ $s['color'] }};"></span>
                <span>{{ $s['label'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
