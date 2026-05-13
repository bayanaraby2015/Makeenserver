<?php

namespace App\Filament\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\MonthlyReport;
use App\Models\Organization;
use App\Models\ServiceEvaluation;
use App\Models\VisitReport;
use Filament\Widgets\Widget;

class AdminExecutiveOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.executive-overview';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $totalInitiatives = Initiative::query()->count();
        $approvedInitiatives = Initiative::query()->where('status', 'approved')->count();
        $openConsultations = Consultation::query()->whereIn('status', ['requested', 'accepted', 'scheduled'])->count();
        $activeOrganizations = Organization::query()->where('status', 'active')->count();
        $pendingVisits = VisitReport::query()->whereIn('status', ['proposed', 'planned'])->count();
        $monthlyReports = MonthlyReport::query()->whereIn('status', ['draft', 'submitted'])->count();
        $ratingAverage = ServiceEvaluation::query()->avg('rating');

        return [
            'title' => 'المؤشر التنفيذي للنظام',
            'subtitle' => 'صورة شاملة للإدارة على المبادرات والجهات والتذاكر والتقارير.',
            'tone' => 'admin',
            'progress' => $this->percentage($approvedInitiatives, $totalInitiatives),
            'progressLabel' => 'نسبة المبادرات المعتمدة',
            'cards' => [
                ['label' => 'المبادرات', 'value' => $totalInitiatives, 'hint' => 'إجمالي المبادرات'],
                ['label' => 'جهات نشطة', 'value' => $activeOrganizations, 'hint' => 'جهات مشاركة حالياً'],
                ['label' => 'استشارات وتذاكر', 'value' => $openConsultations, 'hint' => 'طلبات مفتوحة'],
                ['label' => 'تقييم الخدمة', 'value' => $ratingAverage ? number_format((float) $ratingAverage, 1).'/5' : '0/5', 'hint' => 'متوسط النظام'],
            ],
            'alerts' => [
                ['label' => 'زيارات تحتاج متابعة', 'value' => $pendingVisits, 'status' => $pendingVisits > 0 ? 'warning' : 'success'],
                ['label' => 'تقارير شهرية غير مكتملة', 'value' => $monthlyReports, 'status' => $monthlyReports > 0 ? 'warning' : 'success'],
            ],
        ];
    }

    private function percentage(int $part, int $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }
}
