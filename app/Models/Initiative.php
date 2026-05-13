<?php

namespace App\Models;

use App\Policies\InitiativePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $name_ar
 * @property string|null $name_en
 * @property string $domain
 * @property string|null $related_criteria
 * @property string|null $development_justification
 * @property string|null $main_goal
 * @property string|null $description
 * @property string|null $strategic_objectives
 * @property string|null $responsible_department
 * @property string|null $owner_name
 * @property string|null $partners
 * @property string|null $beneficiaries_scope
 * @property int|null $duration_weeks
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $total_cost
 * @property string $vat_amount
 * @property string $grand_total
 * @property string $currency
 * @property string $status
 * @property Carbon|null $submitted_at
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property Carbon|null $rejected_at
 * @property int|null $rejected_by
 *
 * @method static \Database\Factories\InitiativeFactory factory($count = null, $state = [])
 */
#[UsePolicy(InitiativePolicy::class)]
class Initiative extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name_ar',
        'name_en',
        'domain',
        'specializations',
        'related_criteria',
        'development_justification',
        'main_goal',
        'description',
        'strategic_objectives',
        'responsible_department',
        'owner_name',
        'partners',
        'beneficiaries_scope',
        'duration_weeks',
        'start_date',
        'end_date',
        'total_cost',
        'vat_amount',
        'grand_total',
        'currency',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'rejected_at',
        'rejected_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'duration_weeks' => 'integer',
        'specializations' => 'array',
        'total_cost' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('initiatives')
            ->logOnly([
                'name_ar',
                'name_en',
                'status',
                'submitted_at',
                'approved_at',
                'rejected_at',
                'rejection_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /** @return HasMany<InitiativeOutput, $this> */
    public function outputs(): HasMany
    {
        return $this->hasMany(InitiativeOutput::class)->orderBy('order_index');
    }

    /** @return HasMany<InitiativeMilestone, $this> */
    public function milestones(): HasMany
    {
        return $this->hasMany(InitiativeMilestone::class)->orderBy('order_index');
    }

    /** @return HasMany<InitiativePayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(InitiativePayment::class)->orderBy('order_index');
    }

    /** @return HasMany<InitiativeRisk, $this> */
    public function risks(): HasMany
    {
        return $this->hasMany(InitiativeRisk::class)->orderBy('order_index');
    }

    /** @return HasMany<InitiativeKpiValue, $this> */
    public function kpiValues(): HasMany
    {
        return $this->hasMany(InitiativeKpiValue::class);
    }

    /** @return HasOne<InitiativeEvaluation, $this> */
    public function evaluation(): HasOne
    {
        return $this->hasOne(InitiativeEvaluation::class);
    }

    /** @return HasMany<InitiativeEvaluation, $this> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(InitiativeEvaluation::class)->latest();
    }

    /** @return HasMany<DonorInterest, $this> */
    public function donorInterests(): HasMany
    {
        return $this->hasMany(DonorInterest::class);
    }

    /** @return HasMany<Consultation, $this> */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /** @return HasMany<VisitReport, $this> */
    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class);
    }

    /** @return HasMany<MonthlyReport, $this> */
    public function monthlyReports(): HasMany
    {
        return $this->hasMany(MonthlyReport::class);
    }

    /**
     * @return array<int, string>
     */
    public function specializationLabels(): array
    {
        return \App\Support\InitiativeSpecializations::labels($this->specializations);
    }
}
