<?php

namespace App\Filament\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\InitiativePayment;
use App\Models\MonthlyReport;
use App\Models\Organization;
use App\Models\ServiceEvaluation;
use App\Models\User;
use App\Models\VisitReport;
use App\Support\DisplayNumber;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class AdminOperationsDashboardWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-operations-dashboard';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $initiativeStatuses = [
            'draft' => 'مسودة',
            'submitted' => 'مرسلة',
            'under_review' => 'قيد المراجعة',
            'revisions_requested' => 'تحتاج تعديل',
            'approved' => 'معتمدة',
            'rejected' => 'مرفوضة',
        ];

        $consultationStatuses = [
            'requested' => 'طلب جديد',
            'accepted' => 'مقبولة',
            'scheduled' => 'مجدولة',
            'closed' => 'مغلقة',
            'rejected' => 'مرفوضة',
        ];

        $visitStatuses = [
            'proposed' => 'بانتظار اختيار موعد',
            'planned' => 'مجدولة',
            'completed' => 'مكتملة',
        ];

        $reportStatuses = [
            'draft' => 'مسودة',
            'submitted' => 'مرسلة',
            'reviewed' => 'مراجعة',
        ];

        $today = now()->startOfDay();
        $nextMonth = now()->addDays(30)->endOfDay();

        $totalInitiatives = Initiative::query()->count();
        $approvedInitiatives = Initiative::query()->where('status', 'approved')->count();
        $totalOrganizations = Organization::query()->count();
        $activeUsers = User::query()->where('status', 'active')->count();
        $averageRating = ServiceEvaluation::query()->avg('rating');

        $overduePayments = InitiativePayment::query()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->count();

        $upcomingPaymentAmount = InitiativePayment::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $nextMonth])
            ->sum('amount');

        return [
            'hero' => [
                'title' => 'لوحة قيادة الإدارة',
                'subtitle' => 'متابعة تنفيذية شاملة للمبادرات، الجهات، الاستشارات، الزيارات، التقارير، والدفعات.',
                'completion' => $this->percentage($approvedInitiatives, $totalInitiatives),
                'total_budget' => DisplayNumber::riyal(Initiative::query()->sum('grand_total')),
                'upcoming_payments' => DisplayNumber::riyal($upcomingPaymentAmount),
                'rating' => $averageRating ? number_format((float) $averageRating, 1).'/5' : '0/5',
            ],
            'overview' => [
                ['label' => 'المبادرات', 'value' => DisplayNumber::plain($totalInitiatives), 'hint' => 'كل المبادرات المسجلة', 'tone' => 'navy'],
                ['label' => 'الجهات', 'value' => DisplayNumber::plain($totalOrganizations), 'hint' => 'إجمالي الجهات', 'tone' => 'teal'],
                ['label' => 'المستخدمون النشطون', 'value' => DisplayNumber::plain($activeUsers), 'hint' => 'حسب حالة الحساب', 'tone' => 'slate'],
                ['label' => 'دفعات متأخرة', 'value' => DisplayNumber::plain($overduePayments), 'hint' => 'تحتاج متابعة مالية', 'tone' => $overduePayments > 0 ? 'amber' : 'teal'],
            ],
            'queues' => [
                ['label' => 'جهات بانتظار الاعتماد', 'value' => Organization::query()->where('status', 'pending')->count(), 'status' => 'warning'],
                ['label' => 'مبادرات وصلت للإدارة', 'value' => Initiative::query()->whereIn('status', ['submitted', 'under_review'])->count(), 'status' => 'info'],
                ['label' => 'مبادرات تحتاج تعديل', 'value' => Initiative::query()->where('status', 'revisions_requested')->count(), 'status' => 'warning'],
                ['label' => 'استشارات جديدة', 'value' => Consultation::query()->where('status', 'requested')->count(), 'status' => 'danger'],
                ['label' => 'جلسات مجدولة', 'value' => Consultation::query()->where('status', 'scheduled')->count(), 'status' => 'info'],
                ['label' => 'زيارات بانتظار اختيار موعد', 'value' => VisitReport::query()->where('status', 'proposed')->count(), 'status' => 'warning'],
                ['label' => 'تقارير شهرية مرسلة', 'value' => MonthlyReport::query()->where('status', 'submitted')->count(), 'status' => 'info'],
                ['label' => 'تقييمات الخدمة', 'value' => ServiceEvaluation::query()->count(), 'status' => 'success'],
            ],
            'pipelines' => [
                ['title' => 'مسار المبادرات', 'items' => $this->statusPipeline(Initiative::class, $initiativeStatuses)],
                ['title' => 'مسار الاستشارات', 'items' => $this->statusPipeline(Consultation::class, $consultationStatuses)],
                ['title' => 'مسار الزيارات', 'items' => $this->statusPipeline(VisitReport::class, $visitStatuses)],
                ['title' => 'مسار التقارير الشهرية', 'items' => $this->statusPipeline(MonthlyReport::class, $reportStatuses)],
            ],
            'organizations' => $this->topOrganizations(),
            'activity' => $this->recentActivity(),
        ];
    }

    private function percentage(int|float $part, int|float $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  array<string, string>  $labels
     * @return array<int, array<string, int|string>>
     */
    private function statusPipeline(string $model, array $labels): array
    {
        $counts = $model::query()
            ->selectRaw('status, count(*) as aggregate')
            ->whereIn('status', array_keys($labels))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = max(1, (int) $counts->sum());

        return collect($labels)
            ->map(fn (string $label, string $status): array => [
                'label' => $label,
                'value' => (int) ($counts[$status] ?? 0),
                'percentage' => $this->percentage((int) ($counts[$status] ?? 0), $total),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function topOrganizations(): array
    {
        return Organization::query()
            ->withCount([
                'initiatives',
                'consultations as open_consultations_count' => fn ($query) => $query->whereIn('status', ['requested', 'accepted', 'scheduled']),
            ])
            ->orderByDesc('initiatives_count')
            ->limit(5)
            ->get()
            ->map(fn (Organization $organization): array => [
                'name' => $organization->name_ar,
                'status' => $this->organizationStatusLabel($organization->status),
                'initiatives' => DisplayNumber::plain($organization->initiatives_count),
                'tickets' => DisplayNumber::plain($organization->open_consultations_count),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function recentActivity(): array
    {
        return Activity::query()
            ->with('causer')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Activity $activity): array => [
                'time' => Carbon::parse($activity->created_at)->diffForHumans(),
                'log' => $this->activityLogLabel((string) $activity->log_name),
                'description' => (string) $activity->description,
                'causer' => $activity->causer?->name ?? 'النظام',
            ])
            ->all();
    }

    private function organizationStatusLabel(?string $status): string
    {
        return match ($status) {
            'active', 'approved' => 'نشطة',
            'pending' => 'بانتظار الاعتماد',
            'inactive' => 'معطلة',
            'rejected' => 'مرفوضة',
            default => 'غير محددة',
        };
    }

    private function activityLogLabel(string $logName): string
    {
        return match ($logName) {
            'initiatives' => 'المبادرات',
            'consultations' => 'الاستشارات',
            'visit_reports' => 'الزيارات',
            'monthly_reports' => 'التقارير',
            'service_evaluations' => 'التقييمات',
            'organization' => 'الجهات',
            default => $logName,
        };
    }
}
