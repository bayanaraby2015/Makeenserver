<?php

namespace App\Filament\Excellence\Widgets;

use App\Models\Initiative;
use Filament\Widgets\ChartWidget;

class ExcellenceInitiativeStatusChart extends ChartWidget
{
    protected ?string $heading = 'توزيع حالات المبادرات';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = [
            'submitted' => 'مرسلة للمراجعة',
            'under_review' => 'قيد المراجعة',
            'approved' => 'معتمدة',
            'revisions_requested' => 'تحتاج تعديلات',
            'rejected' => 'مرفوضة',
        ];

        $counts = Initiative::query()
            ->selectRaw('status, count(*) as aggregate')
            ->whereIn('status', array_keys($statuses))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'datasets' => [[
                'data' => array_map(fn (string $status): int => (int) ($counts[$status] ?? 0), array_keys($statuses)),
                'backgroundColor' => ['#f9ad1c', '#21b2b8', '#22c55e', '#283979', '#ef4444'],
                'borderWidth' => 0,
            ]],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
