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
 * @property int $evaluator_id
 * @property string|null $overall_score
 * @property string|null $strengths
 * @property string|null $improvements
 * @property string|null $recommendation
 * @property string $decision
 * @property Carbon|null $finalized_at
 */
class InitiativeEvaluation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'initiative_id',
        'evaluator_id',
        'overall_score',
        'strengths',
        'improvements',
        'recommendation',
        'decision',
        'finalized_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('initiative_evaluations')
            ->logOnly([
                'initiative_id',
                'evaluator_id',
                'overall_score',
                'decision',
                'finalized_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
