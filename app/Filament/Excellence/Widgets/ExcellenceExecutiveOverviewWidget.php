<?php

namespace App\Filament\Excellence\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\MonthlyReport;
use App\Models\ServiceEvaluation;
use App\Models\VisitReport;
use Filament\Widgets\Widget;

class ExcellenceExecutiveOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.executive-overview';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $total = Initiative::query()->count();
        $underReview = Initiative::query()->whereIn('status', ['submitted', 'under_review'])->count();
        $approved = Initiative::query()->where('status', 'approved')->count();
        $revisions = Initiative::query()->where('status', 'revisions_requested')->count();
        $tickets = Consultation::query()->whereIn('status', ['requested', 'accepted', 'scheduled'])->count();
        $visits = VisitReport::query()->whereIn('status', ['proposed', 'planned'])->count();
        $reports = MonthlyReport::query()->whereIn('status', ['submitted', 'reviewed'])->count();
        $ratingAverage = ServiceEvaluation::query()->avg('rating');

        return [
            'title' => 'ملخص مسار الإجادة',
            'subtitle' => 'تركيز على الاعتماد والمراجعات والمتابعة التشغيلية للمبادرات والجهات.',
            'tone' => 'excellence',
            'progress' => $this->percentage($approved, $total),
            'progressLabel' => 'نسبة الاعتماد العام',
            'cards' => [
                ['label' => 'قيد المراجعة', 'value' => $underReview, 'hint' => 'تحتاج قرار'],
                ['label' => 'طلبات تعديل', 'value' => $revisions, 'hint' => 'راجعة للجهة'],
                ['label' => 'تقارير متابعة', 'value' => $reports, 'hint' => 'شهرية ومراجعة'],
                ['label' => 'تقييم الخدمة', 'value' => $ratingAverage ? number_format((float) $ratingAverage, 1).'/5' : '0/5', 'hint' => 'متوسط المنصة'],
            ],
            'alerts' => [
                ['label' => 'تذاكر واستشارات نشطة', 'value' => $tickets, 'status' => $tickets > 0 ? 'warning' : 'success'],
                ['label' => 'زيارات قيد الجدولة أو التنفيذ', 'value' => $visits, 'status' => $visits > 0 ? 'info' : 'success'],
            ],
        ];
    }

    private function percentage(int $part, int $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }
}
