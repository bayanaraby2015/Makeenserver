<?php

namespace App\Filament\Association\Widgets;

use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\ServiceEvaluation;
use App\Models\VisitReport;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AssociationExecutiveOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.executive-overview';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $orgId = Auth::user()?->primary_organization_id;
        $total = Initiative::query()->where('organization_id', $orgId)->count();
        $approved = Initiative::query()->where('organization_id', $orgId)->where('status', 'approved')->count();
        $needsAction = Initiative::query()
            ->where('organization_id', $orgId)
            ->whereIn('status', ['draft', 'revisions_requested', 'rejected'])
            ->count();
        $openTickets = Consultation::query()
            ->where('requester_organization_id', $orgId)
            ->whereIn('status', ['requested', 'accepted', 'scheduled'])
            ->count();
        $visits = VisitReport::query()
            ->where('organization_id', $orgId)
            ->whereIn('status', ['proposed', 'planned'])
            ->count();
        $ratingAverage = ServiceEvaluation::query()->where('organization_id', $orgId)->avg('rating');

        return [
            'title' => 'ملخص الجهة التنفيذي',
            'subtitle' => 'ما تحتاجه الجهة اليوم: مبادرات، تذاكر، زيارات، وتقييمات.',
            'tone' => 'association',
            'progress' => $this->percentage($approved, $total),
            'progressLabel' => 'جاهزية المبادرات المعتمدة',
            'cards' => [
                ['label' => 'مبادرات الجهة', 'value' => $total, 'hint' => 'إجمالي المسجل'],
                ['label' => 'معتمدة', 'value' => $approved, 'hint' => 'جاهزة للمتابعة'],
                ['label' => 'تحتاج إجراء', 'value' => $needsAction, 'hint' => 'تعديل أو استكمال'],
                ['label' => 'تقييم الخدمة', 'value' => $ratingAverage ? number_format((float) $ratingAverage, 1).'/5' : '0/5', 'hint' => 'متوسط تقييم الجهة'],
            ],
            'alerts' => [
                ['label' => 'استشارات وتذاكر مفتوحة', 'value' => $openTickets, 'status' => $openTickets > 0 ? 'warning' : 'success'],
                ['label' => 'زيارات بانتظار اختيار/تنفيذ', 'value' => $visits, 'status' => $visits > 0 ? 'info' : 'success'],
            ],
        ];
    }

    private function percentage(int $part, int $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }
}
