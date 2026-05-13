<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'locale',
        'status',
        'primary_organization_id',
        'last_login_at',
        'last_login_ip',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->isForceDeleting() || str_contains($user->email, '#deleted-')) {
                return;
            }

            $user->forceFill([
                'email' => $user->email.'#deleted-'.$user->id,
            ])->saveQuietly();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'phone', 'locale', 'status',
                'primary_organization_id', 'last_login_at', 'last_login_ip',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    public function primaryOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'primary_organization_id');
    }

    /**
     * Returns the absolute URL for the user's avatar shown in the Filament
     * topbar. Falls back to null (initials) when nothing is stored.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        $path = $this->avatar_url;

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /** @return HasMany<Consultation, $this> */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'consultant_user_id');
    }

    /** @return HasMany<ConsultationNote, $this> */
    public function consultationNotes(): HasMany
    {
        return $this->hasMany(ConsultationNote::class);
    }

    /** @return HasMany<ConsultantSpecialization, $this> */
    public function consultantSpecializations(): HasMany
    {
        return $this->hasMany(ConsultantSpecialization::class, 'consultant_user_id');
    }

    /**
     * Per-panel access. Maps panel id -> required role(s).
     * super_admin can access every panel.
     *
     *   admin       => super_admin
     *   excellence  => excellence_manager | excellence_member
     *   donor       => donor_admin
     *   consultant  => consultant
     *   association => association_manager | association_member
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->hasRole(config('makeen.roles.super_admin'))) {
            return true;
        }

        $allowed = match ($panel->getId()) {
            'admin' => [
                config('makeen.roles.super_admin'),
                config('makeen.roles.excellence_manager'),
            ],
            'excellence' => [
                config('makeen.roles.excellence_manager'),
                config('makeen.roles.excellence_member'),
            ],
            'donor' => [config('makeen.roles.donor_admin')],
            'consultant' => [config('makeen.roles.consultant')],
            'association' => [
                config('makeen.roles.association_manager'),
                config('makeen.roles.association_member'),
            ],
            default => [],
        };

        return $this->hasAnyRole($allowed);
    }
}
