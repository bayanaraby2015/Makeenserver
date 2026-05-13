<?php

namespace App\Filament\Widgets;

use App\Models\Consultation;
use Filament\Widgets\ChartWidget;

class AdminConsultationStatusChart extends ChartWidget
{
    protected ?string $heading = 'توزيع حالات الاستشارات';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = [
            'requested' => 'طلب جديد',
            'accepted' => 'مقبولة',
            'scheduled' => 'مجدولة',
            'completed' => 'مغلقة',
            'rejected' => 'مرفوضة',
            'cancelled' => 'ملغاة',
        ];

        $counts = Consultation::query()
            ->selectRaw('status, count(*) as aggregate')
            ->whereIn('status', array_keys($statuses))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'datasets' => [[
                'data' => array_map(fn (string $status): int => (int) ($counts[$status] ?? 0), array_keys($statuses)),
                'backgroundColor' => ['#f9ad1c', '#21b2b8', '#283979', '#22c55e', '#ef4444', '#94a3b8'],
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
