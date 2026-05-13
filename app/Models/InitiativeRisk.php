<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int $order_index
 * @property string $risk
 * @property string|null $likelihood
 * @property string|null $impact
 * @property string|null $mitigation
 */
class InitiativeRisk extends Model
{
    protected $fillable = [
        'initiative_id',
        'order_index',
        'risk',
        'likelihood',
        'impact',
        'mitigation',
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }
}
