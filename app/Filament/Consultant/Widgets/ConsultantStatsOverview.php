<?php

namespace App\Filament\Consultant\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\MonthlyReport;
use App\Models\ServiceEvaluation;
use App\Models\VisitReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ConsultantStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $specializations = $user?->consultantSpecializations()->pluck('specialization')->all() ?? [];

        $initiativeQuery = Initiative::query();
        self::scopeInitiativesToSpecializations($initiativeQuery, $specializations);

        $assignedConsultations = Consultation::query()->where('consultant_user_id', $user?->id);

        $initiatives = (clone $initiativeQuery)->count();
        $needsReview = (clone $initiativeQuery)->whereIn('status', ['excellence_approved', 'revisions_requested'])->count();
        $scheduled = (clone $assignedConsultations)->where('status', 'scheduled')->count();
        $openConsultations = Consultation::query()
            ->where('consultant_user_id', $user?->id)
            ->whereIn('status', ['requested', 'accepted', 'scheduled'])
            ->count();
        $plannedVisits = VisitReport::query()
            ->where('consultant_user_id', $user?->id)
            ->whereIn('status', ['proposed', 'planned'])
            ->count();
        $monthlyReports = MonthlyReport::query()
            ->where('consultant_user_id', $user?->id)
            ->whereIn('status', ['draft', 'submitted'])
            ->count();
        $evaluationAverage = ServiceEvaluation::query()
            ->where('evaluator_id', $user?->id)
            ->avg('rating');
        $evaluationCount = ServiceEvaluation::query()
            ->where('evaluator_id', $user?->id)
            ->count();

        return [
            Stat::make('مبادرات ضمن التخصص', (string) $initiatives)
                ->description('حسب تخصصات المستشار')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('تحتاج متابعة', (string) $needsReview)
                ->description('مراجعة أو تقييم')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($needsReview > 0 ? 'warning' : 'gray'),
            Stat::make('استشارات مفتوحة', (string) $openConsultations)
                ->description('جلسات مجدولة: '.$scheduled)
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($openConsultations > 0 ? 'warning' : 'gray'),
            Stat::make('زيارات مجدولة', (string) $plannedVisits)
                ->description('بانتظار التنفيذ أو التقرير')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($plannedVisits > 0 ? 'info' : 'gray'),
            Stat::make('تقارير شهرية', (string) $monthlyReports)
                ->description('مسودة أو مرسلة للمراجعة')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color($monthlyReports > 0 ? 'primary' : 'gray'),
            Stat::make('تقييمات الخدمة', $evaluationAverage ? number_format((float) $evaluationAverage, 1).'/5' : '0/5')
                ->description('عدد التقييمات المدخلة: '.$evaluationCount)
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($evaluationAverage >= 4 ? 'success' : ($evaluationAverage ? 'warning' : 'gray')),
        ];
    }

    public static function scopeInitiativesToSpecializations(Builder $query, array $specializations): void
    {
        $query->where(function (Builder $query) use ($specializations): void {
            if ($specializations === []) {
                $query->whereRaw('1 = 0');

                return;
            }

            foreach ($specializations as $specialization) {
                $query->orWhereJsonContains('specializations', $specialization)
                    ->orWhere(function (Builder $fallback) use ($specialization): void {
                        $fallback
                            ->whereNull('specializations')
                            ->where('domain', $specialization);
                    });
            }
        });
    }
}
