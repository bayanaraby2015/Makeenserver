<?php

namespace App\Filament\Consultant\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\MonthlyReport;
use App\Models\ServiceEvaluation;
use App\Models\VisitReport;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ConsultantExecutiveOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.executive-overview';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $user = Auth::user();
        $specializations = $user?->consultantSpecializations()->pluck('specialization')->all() ?? [];
        $initiativeQuery = Initiative::query();
        ConsultantStatsOverview::scopeInitiativesToSpecializations($initiativeQuery, $specializations);

        $totalInitiatives = (clone $initiativeQuery)->count();
        $needsReview = (clone $initiativeQuery)->whereIn('status', ['submitted', 'under_review', 'revisions_requested'])->count();
        $openConsultations = Consultation::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where('consultant_user_id', $user?->id)
                    ->orWhere('responsible_user_id', $user?->id);
            })
            ->whereIn('status', ['requested', 'accepted', 'scheduled'])
            ->count();
        $plannedVisits = VisitReport::query()->where('consultant_user_id', $user?->id)->whereIn('status', ['proposed', 'planned'])->count();
        $reports = MonthlyReport::query()->where('consultant_user_id', $user?->id)->whereIn('status', ['draft', 'submitted'])->count();
        $ratingAverage = ServiceEvaluation::query()->where('evaluator_id', $user?->id)->avg('rating');
        $handled = max(0, $totalInitiatives - $needsReview);

        return [
            'title' => 'ملخص المستشار التنفيذي',
            'subtitle' => 'مبادرات حسب تخصصك، واستشارات وتذاكر مرتبطة بك فقط.',
            'tone' => 'consultant',
            'progress' => $this->percentage($handled, $totalInitiatives),
            'progressLabel' => 'نسبة الملفات المستقرة',
            'cards' => [
                ['label' => 'مبادرات ضمن التخصص', 'value' => $totalInitiatives, 'hint' => 'متاحة للمراجعة'],
                ['label' => 'تحتاج متابعة', 'value' => $needsReview, 'hint' => 'تقييم أو توصية'],
                ['label' => 'زيارات مجدولة', 'value' => $plannedVisits, 'hint' => 'قادمة أو مقترحة'],
                ['label' => 'تقييماتك', 'value' => $ratingAverage ? number_format((float) $ratingAverage, 1).'/5' : '0/5', 'hint' => 'متوسط ما أدخلته'],
            ],
            'alerts' => [
                ['label' => 'استشارات وتذاكر مفتوحة', 'value' => $openConsultations, 'status' => $openConsultations > 0 ? 'warning' : 'success'],
                ['label' => 'تقارير شهرية قيد العمل', 'value' => $reports, 'status' => $reports > 0 ? 'info' : 'success'],
            ],
        ];
    }

    private function percentage(int $part, int $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }
}
