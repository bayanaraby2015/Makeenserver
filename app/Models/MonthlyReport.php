<?php

namespace App\Models;

use App\Policies\MonthlyReportPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int|null $organization_id
 * @property int|null $consultant_user_id
 * @property Carbon|null $report_month
 * @property string $status
 */
#[UsePolicy(MonthlyReportPolicy::class)]
class MonthlyReport extends Model
{
    use LogsActivity;

    protected $fillable = [
        'initiative_id',
        'organization_id',
        'consultant_user_id',
        'report_month',
        'status',
        'executive_summary',
        'progress_summary',
        'risks_summary',
        'questions_summary',
        'recommendations',
        'attachments',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'report_month' => 'date',
        'attachments' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('monthly_reports')
            ->logOnly(['initiative_id', 'consultant_user_id', 'report_month', 'status', 'submitted_at', 'reviewed_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
