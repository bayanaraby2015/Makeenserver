<?php

namespace App\Models;

use App\Policies\ConsultationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[UsePolicy(ConsultationPolicy::class)]
class Consultation extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'requester_organization_id',
        'initiative_id',
        'consultant_user_id',
        'responsible_user_id',
        'specialization',
        'request_type',
        'routing_target',
        'subject',
        'details',
        'attachments',
        'status',
        'requested_at',
        'proposed_at',
        'scheduled_at',
        'meeting_provider',
        'meeting_id',
        'meeting_url',
        'meeting_password',
        'closed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'attachments' => 'array',
        'requested_at' => 'datetime',
        'proposed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /** @return BelongsTo<Organization, $this> */
    public function requesterOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'requester_organization_id');
    }

    /** @return BelongsTo<Initiative, $this> */
    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class);
    }

    /** @return BelongsTo<User, $this> */
    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /** @return HasMany<ConsultationNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(ConsultationNote::class)->latest();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('consultations')
            ->logOnly([
                'requester_organization_id',
                'initiative_id',
                'consultant_user_id',
                'responsible_user_id',
                'specialization',
                'request_type',
                'routing_target',
                'subject',
                'status',
                'scheduled_at',
                'meeting_provider',
                'meeting_url',
                'closed_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
