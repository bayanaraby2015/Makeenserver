<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int $kpi_definition_id
 * @property string|null $baseline
 * @property string|null $target
 * @property int|null $score
 * @property string|null $reviewer_notes
 */
class InitiativeKpiValue extends Model
{
    protected $table = 'initiative_kpi_values';

    protected $fillable = [
        'initiative_id',
        'kpi_definition_id',
        'baseline',
        'target',
        'score',
        'reviewer_notes',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /** @return BelongsTo<Initiative, $this> */
    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }

    /** @return BelongsTo<KpiDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'kpi_definition_id');
    }
}
