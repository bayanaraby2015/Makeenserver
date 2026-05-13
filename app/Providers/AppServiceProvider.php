<?php

namespace App\Providers;

use App\Filament\Widgets\AdminOperationsDashboardWidget;
use App\Models\Consultation;
use App\Models\Initiative;
use App\Models\InitiativePayment;
use App\Models\MonthlyReport;
use App\Models\Organization;
use App\Models\ServiceEvaluation;
use App\Models\User;
use App\Models\VisitReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Models whose writes invalidate the admin dashboard cache.
     *
     * @var list<class-string<Model>>
     */
    private const DASHBOARD_MODELS = [
        Initiative::class,
        InitiativePayment::class,
        Consultation::class,
        VisitReport::class,
        MonthlyReport::class,
        ServiceEvaluation::class,
        Organization::class,
        User::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $forget = static function (Model $model): void {
            AdminOperationsDashboardWidget::forgetCache();
        };

        foreach (self::DASHBOARD_MODELS as $model) {
            $model::saved($forget);
            $model::deleted($forget);
        }
    }
}
