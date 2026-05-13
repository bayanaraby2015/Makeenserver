<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Organization — represents a tenant: an Association, a Donor org, the
 * Excellence Team, or a Consultant firm. Distinguished by `type`.
 *
 * Sprint 1 fields cover association self-registration (ADR-0002 §workspace
 * scoping). Additional fields (logo via Media Library, custom_fields JSON,
 * 13-association linkage to the Makeen project) come in later sprints.
 *
 * @property int $id
 * @property string $type
 * @property string $name_ar
 * @property string|null $name_en
 * @property string|null $license_number
 * @property string|null $license_authority
 * @property string|null $city
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string $status
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property Carbon|null $rejected_at
 * @property int|null $rejected_by
 */
class Organization extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name_ar',
        'name_en',
        'license_number',
        'license_authority',
        'city',
        'address',
        'phone',
        'email',
        'website',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'rejected_at',
        'rejected_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Organization $organization): void {
            if ($organization->isForceDeleting() || $organization->email === null || str_contains($organization->email, '#deleted-')) {
                return;
            }

            $organization->forceFill([
                'email' => $organization->email.'#deleted-'.$organization->id,
            ])->saveQuietly();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type', 'name_ar', 'name_en', 'license_number',
                'city', 'phone', 'email', 'status',
                'approved_at', 'approved_by',
                'rejected_at', 'rejected_by', 'rejection_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('organization');
    }

    /**
     * Users whose primary organization is this org.
     *
     * @return HasMany<User, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'primary_organization_id');
    }

    /** @return HasMany<Consultation, $this> */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'requester_organization_id');
    }

    /** @return HasMany<Initiative, $this> */
    public function initiatives(): HasMany
    {
        return $this->hasMany(Initiative::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
