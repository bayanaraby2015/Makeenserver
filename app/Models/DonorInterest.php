<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $initiative_id
 * @property int $user_id
 * @property int|null $donor_organization_id
 * @property string|null $proposed_amount
 * @property string|null $message
 * @property string $status
 * @property Carbon|null $acknowledged_at
 */
class DonorInterest extends Model
{
    protected $fillable = [
        'initiative_id',
        'user_id',
        'donor_organization_id',
        'proposed_amount',
        'message',
        'status',
        'acknowledged_at',
    ];

    protected $casts = [
        'proposed_amount' => 'decimal:2',
        'acknowledged_at' => 'datetime',
    ];

    /** @return BelongsTo<Initiative, $this> */
    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function donorOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'donor_organization_id');
    }
}
