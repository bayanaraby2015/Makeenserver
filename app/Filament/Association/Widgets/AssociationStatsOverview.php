<?php

namespace App\Filament\Association\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\MonthlyReport;
use App\Models\ServiceEvaluation;
use App\Models\VisitReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AssociationStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $orgId = Auth::user()?->primary_organization_id;

        $initiatives = Initiative::query()->where('organization_id', $orgId);
        $consultations = Consultation::query()->where('requester_organization_id', $orgId);

        $total = (clone $initiatives)->count();
        $approved = (clone $initiatives)->where('status', 'approved')->count();
        $needsAction = Initiative::query()
            ->where('organization_id', $orgId)
            ->whereIn('status', ['draft', 'revisions_requested', 'rejected'])
            ->count();
        $openConsultations = (clone $consultations)->whereIn('status', ['requested', 'accepted', 'scheduled'])->count();
        $plannedVisits = VisitReport::query()
            ->where('organization_id', $orgId)
            ->whereIn('status', ['proposed', 'planned'])
            ->count();
        $monthlyReports = MonthlyReport::query()
            ->where('organization_id', $orgId)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->count();
        $evaluationAverage = ServiceEvaluation::query()
            ->where('organization_id', $orgId)
            ->avg('rating');
        $evaluationCount = ServiceEvaluation::query()
            ->where('organization_id', $orgId)
            ->count();

        return [
            Stat::make('مبادرات الجهة', (string) $total)
                ->description('كل المبادرات المرتبطة بجهتك')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('معتمدة', (string) $approved)
                ->description('جاهزة للمتابعة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('تحتاج إجراء', (string) $needsAction)
                ->description('مسودة أو تعديل أو مرفوضة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($needsAction > 0 ? 'warning' : 'gray'),
            Stat::make('استشارات مفتوحة', (string) $openConsultations)
                ->description('بانتظار رد أو جلسة')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($openConsultations > 0 ? 'info' : 'gray'),
            Stat::make('زيارات قادمة', (string) $plannedVisits)
                ->description('مواعيد زيارة مرتبطة بالجهة')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($plannedVisits > 0 ? 'warning' : 'gray'),
            Stat::make('تقارير شهرية', (string) $monthlyReports)
                ->description('تقارير مرسلة أو مراجعة')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color($monthlyReports > 0 ? 'primary' : 'gray'),
            Stat::make('تقييم الخدمة', $evaluationAverage ? number_format((float) $evaluationAverage, 1).'/5' : '0/5')
                ->description('عدد التقييمات: '.$evaluationCount)
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($evaluationAverage >= 4 ? 'success' : ($evaluationAverage ? 'warning' : 'gray')),
        ];
    }
}
