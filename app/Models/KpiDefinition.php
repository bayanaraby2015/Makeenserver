<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $domain
 * @property string $criterion
 * @property string $indicator
 * @property int $order_index
 * @property bool $is_active
 */
class KpiDefinition extends Model
{
    protected $fillable = [
        'domain',
        'criterion',
        'indicator',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
    ];

    /** @return HasMany<InitiativeKpiValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(InitiativeKpiValue::class);
    }
}
