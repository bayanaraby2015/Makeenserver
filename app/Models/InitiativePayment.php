<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int $order_index
 * @property string $percentage
 * @property string $amount
 * @property Carbon|null $due_date
 * @property string|null $linked_outputs
 */
class InitiativePayment extends Model
{
    protected $fillable = [
        'initiative_id',
        'order_index',
        'percentage',
        'amount',
        'due_date',
        'linked_outputs',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }
}
