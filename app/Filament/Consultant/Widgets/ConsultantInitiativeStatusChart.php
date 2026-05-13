<?php

namespace App\Filament\Consultant\Widgets;

use App\Models\Initiative;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ConsultantInitiativeStatusChart extends ChartWidget
{
    protected ?string $heading = 'حالات المبادرات المرتبطة بي';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = [
            'submitted' => 'مرسلة',
            'under_review' => 'قيد المراجعة',
            'approved' => 'معتمدة',
            'revisions_requested' => 'تحتاج تعديلات',
            'rejected' => 'مرفوضة',
        ];

        $query = Initiative::query();
        ConsultantStatsOverview::scopeInitiativesToSpecializations(
            $query,
            Auth::user()?->consultantSpecializations()->pluck('specialization')->all() ?? [],
        );

        $counts = $query
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
