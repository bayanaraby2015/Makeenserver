<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int $order_index
 * @property string $phase
 * @property string|null $outputs
 * @property int $quantity
 * @property array<int, int>|null $execution_months
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $unit_cost
 * @property string $total_cost
 */
class InitiativeMilestone extends Model
{
    protected $fillable = [
        'initiative_id',
        'order_index',
        'phase',
        'outputs',
        'quantity',
        'execution_months',
        'start_date',
        'end_date',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'quantity' => 'integer',
        'execution_months' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }
}
