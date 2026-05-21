<?php

namespace App\Models;

use App\Mail\OrganizationApprovedMail;
use App\Support\SafeMailer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Throwable;

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

        // Whenever the organization's status flips to "active" we
        // activate every pending member and — on the first transition
        // from "pending" — also stamp approved_at/by (if not already
        // set) and dispatch the approval e-mail. This means *every*
        // code path that activates an org (the dedicated approve row
        // action, the edit form's status dropdown, the new header
        // action on /admin/organizations/{id}, tinker, migrations,
        // etc.) produces the exact same observable behaviour.
        static::updated(function (Organization $organization): void {
            if (! $organization->wasChanged('status')) {
                return;
            }

            if ($organization->status !== 'active') {
                return;
            }

            $previousStatus = $organization->getOriginal('status');

            $activated = [];
            foreach ($organization->members()->where('status', 'pending')->get() as $member) {
                $member->forceFill([
                    'status' => 'active',
                    'email_verified_at' => $member->email_verified_at ?? now(),
                ])->save();
                $activated[] = $member->id;
            }

            if ($activated !== []) {
                Log::info('Organization: auto-activated pending members on status change', [
                    'organization_id' => $organization->id,
                    'previous_status' => $previousStatus,
                    'activated_user_ids' => $activated,
                ]);
            }

            // Only the first "pending → active" transition is treated
            // as an approval. Other transitions (suspended → active,
            // archived → active, etc.) re-activate members but do
            // NOT re-send the approval e-mail nor overwrite the
            // original approval audit columns.
            if ($previousStatus !== 'pending') {
                return;
            }

            $patch = [];
            if ($organization->approved_at === null) {
                $patch['approved_at'] = now();
            }
            if ($organization->approved_by === null && Auth::check()) {
                $patch['approved_by'] = Auth::id();
            }
            if ($patch !== []) {
                $organization->forceFill($patch)->saveQuietly();
            }

            self::dispatchApprovalEmails($organization);
        });
    }

    /**
     * Centralised dispatch of the organisation-approval e-mail to
     * both the organisation's official address and every registered
     * member, so the people who actually log in are notified.
     */
    protected static function dispatchApprovalEmails(self $organization): void
    {
        $memberEmails = $organization->members()
            ->whereNotNull('email')
            ->pluck('email')
            ->all();

        Log::info('OrganizationApprove: dispatching approval mail', [
            'organization_id' => $organization->id,
            'org_email' => $organization->email,
            'member_emails' => $memberEmails,
        ]);

        try {
            if ($organization->email) {
                SafeMailer::send(
                    $organization->email,
                    new OrganizationApprovedMail($organization),
                    'organization_approved',
                );
            }

            foreach ($memberEmails as $email) {
                if ($email === $organization->email) {
                    continue;
                }
                SafeMailer::send(
                    $email,
                    new OrganizationApprovedMail($organization),
                    'organization_approved_manager',
                );
            }

            Log::info('OrganizationApprove: mail dispatch complete', [
                'organization_id' => $organization->id,
            ]);
        } catch (Throwable $e) {
            Log::error('OrganizationApprove: mail dispatch failed', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Single canonical "approve this organisation" entry point used
     * by every UI action. Sets status, audit columns, and clears any
     * prior rejection metadata. The model's `updated` listener then
     * activates pending members and sends the approval e-mail, so
     * callers don't have to repeat that boilerplate.
     */
    public function approveBy(?int $approverId = null): void
    {
        $this->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $approverId ?? (Auth::check() ? Auth::id() : null),
            'rejection_reason' => null,
            'rejected_at' => null,
            'rejected_by' => null,
        ]);
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
