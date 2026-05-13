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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class AdminOperationsDashboardWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-operations-dashboard';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    private const CACHE_TTL_SECONDS = 30; // very short — admin needs near real-time data

    /**
     * All cache keys this widget might use (one per period option).
     * Used by refresh() to invalidate every period variant in one shot.
     *
     * @return array<int, string>
     */
    public static function cacheKeys(): array
    {
        return [
            'makeen.admin-dashboard.v4.7',
            'makeen.admin-dashboard.v4.30',
            'makeen.admin-dashboard.v4.90',
            'makeen.admin-dashboard.v4.180',
            'makeen.admin-dashboard.v4.365',
        ];
    }

    /**
     * Forget every cached period bucket. Call this from Eloquent observers
     * whenever a dashboard-relevant model is created / updated / deleted.
     */
    public static function forgetCache(): void
    {
        foreach (self::cacheKeys() as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Livewire action: bound to the manual refresh button in the header.
     */
    public function refresh(): void
    {
        self::forgetCache();
        $this->dispatch('mk-dash:refreshed');
    }

    /**
     * Livewire-reactive period filter (in days).
     * Allowed: 7, 30, 90, 180, 365.
     */
    public string $period = '30';

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getPeriodOptions(): array
    {
        return [
            ['value' => '7', 'label' => 'آخر 7 أيام'],
            ['value' => '30', 'label' => 'آخر 30 يوم'],
            ['value' => '90', 'label' => 'آخر 90 يوم'],
            ['value' => '180', 'label' => 'آخر 6 أشهر'],
            ['value' => '365', 'label' => 'آخر سنة'],
        ];
    }

    private function periodDays(): int
    {
        $allowed = [7, 30, 90, 180, 365];
        $value = (int) $this->period;

        return in_array($value, $allowed, true) ? $value : 30;
    }

    private function periodLabel(): string
    {
        return match ($this->periodDays()) {
            7 => 'آخر 7 أيام',
            30 => 'آخر 30 يوم',
            90 => 'آخر 90 يوم',
            180 => 'آخر 6 أشهر',
            365 => 'آخر سنة',
            default => 'آخر 30 يوم',
        };
    }

    private function cacheKey(): string
    {
        return 'makeen.admin-dashboard.v4.'.$this->periodDays();
    }

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        return Cache::remember(
            $this->cacheKey(),
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->computeDashboardData(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function computeDashboardData(): array
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
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
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

        $days = $this->periodDays();
        $today = now()->startOfDay();
        $nextMonth = now()->addDays(30)->endOfDay();
        $periodStart = now()->subDays($days)->startOfDay();
        $priorPeriodStart = now()->subDays($days * 2)->startOfDay();

        // ---- Top level ----
        $totalInitiatives = Initiative::query()->count();
        $approvedInitiatives = Initiative::query()->where('status', 'approved')->count();
        $totalOrganizations = Organization::query()->count();
        $activeUsers = User::query()->where('status', 'active')->count();
        $totalUsers = User::query()->count();
        $averageRating = ServiceEvaluation::query()->avg('rating');
        $totalEvaluations = ServiceEvaluation::query()->count();
        $totalBudget = (float) Initiative::query()->sum('grand_total');
        $totalConsultations = Consultation::query()->count();
        $totalVisitReports = VisitReport::query()->count();
        $totalMonthlyReports = MonthlyReport::query()->count();

        // ---- Period comparisons (period vs prior period) ----
        $initiativesCurrent = Initiative::query()
            ->where('created_at', '>=', $periodStart)->count();
        $initiativesPrior = Initiative::query()
            ->whereBetween('created_at', [$priorPeriodStart, $periodStart])->count();

        $consultationsCurrent = Consultation::query()
            ->where('created_at', '>=', $periodStart)->count();
        $consultationsPrior = Consultation::query()
            ->whereBetween('created_at', [$priorPeriodStart, $periodStart])->count();

        $orgsCurrent = Organization::query()
            ->where('created_at', '>=', $periodStart)->count();
        $orgsPrior = Organization::query()
            ->whereBetween('created_at', [$priorPeriodStart, $periodStart])->count();

        $usersCurrent = User::query()
            ->where('created_at', '>=', $periodStart)->count();
        $usersPrior = User::query()
            ->whereBetween('created_at', [$priorPeriodStart, $periodStart])->count();

        $overduePayments = InitiativePayment::query()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->count();

        $upcomingPaymentAmount = (float) InitiativePayment::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $nextMonth])
            ->sum('amount');

        $totalPaymentsAmount = (float) InitiativePayment::query()->sum('amount');

        return [
            'period' => [
                'days' => $days,
                'label' => $this->periodLabel(),
                'options' => $this->getPeriodOptions(),
                'current' => (string) $days,
            ],

            'hero' => [
                'title' => 'لوحة قيادة الإدارة',
                'subtitle' => 'متابعة تنفيذية شاملة لكل أنشطة منصة مكين — المبادرات، الجهات، الاستشارات، الزيارات، التقارير المالية والتشغيلية.',
                'completion' => $this->percentage($approvedInitiatives, $totalInitiatives),
                'total_budget' => DisplayNumber::riyal($totalBudget),
                'total_budget_raw' => $totalBudget,
                'upcoming_payments' => DisplayNumber::riyal($upcomingPaymentAmount),
                'total_payments' => DisplayNumber::riyal($totalPaymentsAmount),
                'rating' => $averageRating ? number_format((float) $averageRating, 1).'/5' : '0/5',
                'rating_count' => $totalEvaluations,
                'now' => now()->locale('ar')->isoFormat('dddd D MMMM YYYY'),
            ],

            // ---- 4 main KPI cards (with icon + trend) ----
            'kpis' => [
                [
                    'icon' => 'sparkles',
                    'tone' => 'navy',
                    'label' => 'إجمالي المبادرات',
                    'value' => DisplayNumber::plain($totalInitiatives),
                    'hint' => "{$approvedInitiatives} معتمدة",
                    'trend' => $this->trend($initiativesCurrent, $initiativesPrior),
                ],
                [
                    'icon' => 'building',
                    'tone' => 'teal',
                    'label' => 'الجهات المسجّلة',
                    'value' => DisplayNumber::plain($totalOrganizations),
                    'hint' => Organization::query()->where('status', 'active')->count().' نشطة',
                    'trend' => $this->trend($orgsCurrent, $orgsPrior),
                ],
                [
                    'icon' => 'users',
                    'tone' => 'amber',
                    'label' => 'المستخدمون',
                    'value' => DisplayNumber::plain($activeUsers),
                    'hint' => "{$totalUsers} إجمالي · ".User::query()->where('status', 'pending')->count().' بانتظار التفعيل',
                    'trend' => $this->trend($usersCurrent, $usersPrior),
                ],
                [
                    'icon' => 'chat',
                    'tone' => 'slate',
                    'label' => 'الاستشارات',
                    'value' => DisplayNumber::plain($totalConsultations),
                    'hint' => Consultation::query()->where('status', 'requested')->count().' جديدة بانتظار التوزيع',
                    'trend' => $this->trend($consultationsCurrent, $consultationsPrior),
                ],
            ],

            // ---- Financial summary ----
            'finance' => [
                'total_budget' => DisplayNumber::riyal($totalBudget),
                'paid_total' => DisplayNumber::riyal($totalPaymentsAmount),
                'upcoming' => DisplayNumber::riyal($upcomingPaymentAmount),
                'overdue_count' => $overduePayments,
                'paid_percentage' => $totalBudget > 0
                    ? (int) round(($totalPaymentsAmount / $totalBudget) * 100)
                    : 0,
                'average_initiative' => $totalInitiatives > 0
                    ? DisplayNumber::riyal($totalBudget / $totalInitiatives)
                    : DisplayNumber::riyal(0),
            ],

            // ---- Queues that need follow-up ----
            'queues' => [
                ['label' => 'جهات بانتظار الاعتماد', 'icon' => 'briefcase', 'value' => Organization::query()->where('status', 'pending')->count(), 'status' => 'warning'],
                ['label' => 'مبادرات وصلت للإدارة', 'icon' => 'inbox', 'value' => Initiative::query()->whereIn('status', ['submitted', 'under_review'])->count(), 'status' => 'info'],
                ['label' => 'مبادرات تحتاج تعديل', 'icon' => 'pencil', 'value' => Initiative::query()->where('status', 'revisions_requested')->count(), 'status' => 'warning'],
                ['label' => 'استشارات جديدة', 'icon' => 'bell', 'value' => Consultation::query()->where('status', 'requested')->count(), 'status' => 'danger'],
                ['label' => 'جلسات مجدولة', 'icon' => 'calendar', 'value' => Consultation::query()->where('status', 'scheduled')->count(), 'status' => 'info'],
                ['label' => 'زيارات بانتظار موعد', 'icon' => 'map', 'value' => VisitReport::query()->where('status', 'proposed')->count(), 'status' => 'warning'],
                ['label' => 'تقارير شهرية مرسلة', 'icon' => 'document', 'value' => MonthlyReport::query()->where('status', 'submitted')->count(), 'status' => 'info'],
                ['label' => 'دفعات متأخرة', 'icon' => 'currency', 'value' => $overduePayments, 'status' => $overduePayments > 0 ? 'danger' : 'success'],
            ],

            // ---- Pipelines (status bars) ----
            'pipelines' => [
                ['key' => 'initiatives',    'title' => 'مسار المبادرات',    'icon' => 'flow', 'items' => $this->statusPipeline(Initiative::class, $initiativeStatuses)],
                ['key' => 'consultations',  'title' => 'مسار الاستشارات',  'icon' => 'chat', 'items' => $this->statusPipeline(Consultation::class, $consultationStatuses)],
                ['key' => 'visits',         'title' => 'مسار الزيارات',     'icon' => 'map', 'items' => $this->statusPipeline(VisitReport::class, $visitStatuses)],
                ['key' => 'reports',        'title' => 'مسار التقارير الشهرية', 'icon' => 'document', 'items' => $this->statusPipeline(MonthlyReport::class, $reportStatuses)],
            ],

            // ---- Distributions (donut chart data) ----
            'distributions' => [
                'initiatives_by_status' => $this->donutData(Initiative::class, 'status', $initiativeStatuses),
                'organizations_by_type' => $this->organizationsByType(),
                'users_by_role' => $this->usersByRole(),
                'consultations_by_specialization' => $this->consultationsBySpecialization(),
            ],

            // ---- Time series (last 12 months) ----
            'timeseries' => [
                'labels' => $this->monthLabels(12),
                'initiatives' => $this->monthlyCounts(Initiative::class, 12),
                'consultations' => $this->monthlyCounts(Consultation::class, 12),
                'visit_reports' => $this->monthlyCounts(VisitReport::class, 12),
                'monthly_reports' => $this->monthlyCounts(MonthlyReport::class, 12),
            ],

            // ---- Rich content ----
            'organizations' => $this->topOrganizations(),
            'consultants' => $this->topConsultants(),
            'budget_initiatives' => $this->topBudgetInitiatives(),
            'evaluations' => $this->evaluationStats(),
            'activity' => $this->recentActivity(),

            // ---- Counters strip (small numbers) ----
            'counters' => [
                ['label' => 'تقارير زيارة', 'value' => DisplayNumber::plain($totalVisitReports), 'icon' => 'map'],
                ['label' => 'تقارير شهرية', 'value' => DisplayNumber::plain($totalMonthlyReports), 'icon' => 'document'],
                ['label' => 'تقييمات الخدمة', 'value' => DisplayNumber::plain($totalEvaluations), 'icon' => 'star'],
                ['label' => 'دفعات قادمة (30 يوم)', 'value' => DisplayNumber::riyal($upcomingPaymentAmount), 'icon' => 'currency'],
            ],
        ];
    }

    private function percentage(int|float $part, int|float $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }

    /**
     * @return array{direction: 'up'|'down'|'flat', delta: int, label: string}
     */
    private function trend(int $current, int $previous): array
    {
        $delta = $current - $previous;

        $direction = 'flat';
        if ($delta > 0) {
            $direction = 'up';
        } elseif ($delta < 0) {
            $direction = 'down';
        }

        $sign = $delta > 0 ? '+' : ($delta < 0 ? '−' : '');
        $abs = abs($delta);

        return [
            'direction' => $direction,
            'delta' => $delta,
            'label' => "{$sign}{$abs}",
        ];
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
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @param  array<string, string>  $labels
     * @return array<int, array<string, int|string>>
     */
    private function donutData(string $model, string $column, array $labels): array
    {
        $counts = $model::query()
            ->selectRaw($column.', count(*) as aggregate')
            ->whereIn($column, array_keys($labels))
            ->groupBy($column)
            ->pluck('aggregate', $column);

        return collect($labels)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'value' => (int) ($counts[$key] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['value'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function organizationsByType(): array
    {
        $counts = Organization::query()
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $labels = [
            'association' => 'جمعيات',
            'donor' => 'جهات داعمة',
            'excellence_team' => 'فريق التميّز',
            'consultant_firm' => 'بيوت خبرة',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'value' => (int) ($counts[$key] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['value'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function usersByRole(): array
    {
        $rows = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->selectRaw('roles.name as role, count(*) as aggregate')
            ->groupBy('roles.name')
            ->pluck('aggregate', 'role');

        $labels = [
            'super_admin' => 'مدير النظام',
            'excellence_manager' => 'مدير التميّز',
            'excellence_member' => 'فريق التميّز',
            'consultant' => 'مستشارون',
            'association_manager' => 'مدراء جمعيات',
            'association_member' => 'موظفو جمعيات',
            'donor_admin' => 'الجهات الداعمة',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'value' => (int) ($rows[$key] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['value'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function consultationsBySpecialization(): array
    {
        $counts = Consultation::query()
            ->selectRaw('specialization, count(*) as aggregate')
            ->whereNotNull('specialization')
            ->groupBy('specialization')
            ->pluck('aggregate', 'specialization');

        $labels = [
            'financial_resources' => 'الموارد المالية',
            'endowments_investment' => 'استثمار الأوقاف',
            'institutional_planning' => 'التخطيط المؤسسي',
            'developmental_impact' => 'الأثر التنموي',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key): array => [
                'label' => $label,
                'value' => (int) ($counts[$key] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['value'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function monthLabels(int $months): array
    {
        $labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->locale('ar')->isoFormat('MMM');
        }

        return $labels;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @return array<int, int>
     */
    private function monthlyCounts(string $model, int $months): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $rows = $model::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, count(*) as aggregate")
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('aggregate', 'ym');

        $values = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return $values;
    }

    /**
     * @return array<int, array<string, string|int>>
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
                'name' => $organization->name_ar ?? '-',
                'status' => $this->organizationStatusLabel($organization->status),
                'status_key' => $organization->status ?? 'unknown',
                'initiatives' => (int) $organization->initiatives_count,
                'tickets' => (int) $organization->open_consultations_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    private function topConsultants(): array
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'consultant'))
            ->withCount([
                'consultations as completed_consultations' => fn ($q) => $q->where('status', 'completed'),
                'consultations as scheduled_consultations' => fn ($q) => $q->whereIn('status', ['accepted', 'scheduled']),
            ])
            ->orderByDesc('completed_consultations')
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'name' => $user->name,
                'completed' => (int) ($user->completed_consultations ?? 0),
                'open' => (int) ($user->scheduled_consultations ?? 0),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string|int|float>>
     */
    private function topBudgetInitiatives(): array
    {
        return Initiative::query()
            ->with('organization')
            ->orderByDesc('grand_total')
            ->limit(5)
            ->get()
            ->map(fn (Initiative $initiative): array => [
                'name' => $initiative->name_ar ?? '-',
                'organization' => $initiative->organization?->name_ar ?? '-',
                'budget' => DisplayNumber::riyal((float) $initiative->grand_total),
                'status' => $this->initiativeStatusLabel($initiative->status),
                'status_key' => $initiative->status ?? 'draft',
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function evaluationStats(): array
    {
        $byType = ServiceEvaluation::query()
            ->selectRaw('service_type, count(*) as cnt, avg(rating) as avg_rating')
            ->groupBy('service_type')
            ->get();

        $labelMap = [
            'consultation' => 'الاستشارات',
            'initiative' => 'المبادرات',
            'visit_report' => 'الزيارات',
            'monthly_report' => 'التقارير الشهرية',
        ];

        return [
            'by_type' => $byType
                ->map(fn ($row): array => [
                    'label' => $labelMap[$row->service_type] ?? $row->service_type,
                    'count' => (int) $row->cnt,
                    'rating' => $row->avg_rating ? number_format((float) $row->avg_rating, 1) : '—',
                ])
                ->all(),
            'distribution' => $this->ratingDistribution(),
        ];
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function ratingDistribution(): array
    {
        $rows = ServiceEvaluation::query()
            ->selectRaw('rating, count(*) as cnt')
            ->groupBy('rating')
            ->pluck('cnt', 'rating');

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[] = [
                'label' => str_repeat('★', $i),
                'value' => (int) ($rows[$i] ?? 0),
            ];
        }

        return $distribution;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function recentActivity(): array
    {
        return Activity::query()
            ->with('causer')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Activity $activity): array => [
                'time' => Carbon::parse($activity->created_at)->diffForHumans(),
                'log' => $this->activityLogLabel((string) $activity->log_name),
                'log_key' => (string) $activity->log_name,
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

    private function initiativeStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'submitted' => 'مرسلة',
            'under_review' => 'قيد المراجعة',
            'revisions_requested' => 'تحتاج تعديل',
            'approved' => 'معتمدة',
            'rejected' => 'مرفوضة',
            default => '-',
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
