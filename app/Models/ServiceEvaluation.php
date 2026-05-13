<?php

namespace App\Models;

use App\Policies\ServiceEvaluationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $service_type
 * @property int|null $service_id
 * @property int|null $evaluator_id
 * @property int|null $organization_id
 * @property int $rating
 * @property Carbon|null $evaluated_at
 */
#[UsePolicy(ServiceEvaluationPolicy::class)]
class ServiceEvaluation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'service_type',
        'service_id',
        'evaluator_id',
        'organization_id',
        'rating',
        'comments',
        'evaluated_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'evaluated_at' => 'datetime',
    ];

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public static function existsForEvaluator(string $serviceType, int|string|null $serviceId, int|string|null $evaluatorId): bool
    {
        if ($serviceId === null || $evaluatorId === null) {
            return false;
        }

        return self::query()
            ->where('service_type', $serviceType)
            ->where('service_id', $serviceId)
            ->where('evaluator_id', $evaluatorId)
            ->exists();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('service_evaluations')
            ->logOnly(['service_type', 'service_id', 'evaluator_id', 'organization_id', 'rating'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
