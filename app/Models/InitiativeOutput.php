<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int $order_index
 * @property string $phase
 * @property string|null $activities
 * @property string|null $output
 * @property int $quantity
 * @property string|null $output_description
 */
class InitiativeOutput extends Model
{
    protected $fillable = [
        'initiative_id',
        'order_index',
        'phase',
        'activities',
        'output',
        'quantity',
        'output_description',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'quantity' => 'integer',
    ];

    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }
}
