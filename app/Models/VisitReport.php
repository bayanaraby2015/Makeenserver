<?php

namespace App\Models;

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
 * @property string $visit_type
 * @property string $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $completed_at
 */
class VisitReport extends Model
{
    use LogsActivity;

    protected $fillable = [
        'initiative_id',
        'organization_id',
        'consultant_user_id',
        'visit_type',
        'status',
        'scheduled_at',
        'appointment_options',
        'selected_at',
        'selected_by',
        'completed_at',
        'pre_visit_notes',
        'summary',
        'achievements',
        'challenges',
        'recommendations',
        'evidence_files',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'appointment_options' => 'array',
        'selected_at' => 'datetime',
        'completed_at' => 'datetime',
        'evidence_files' => 'array',
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

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('visit_reports')
            ->logOnly(['initiative_id', 'consultant_user_id', 'visit_type', 'status', 'scheduled_at', 'completed_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
