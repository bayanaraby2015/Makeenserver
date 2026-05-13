<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ConsultationNote extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'consultation_id',
        'user_id',
        'note',
        'visibility',
    ];

    /** @return BelongsTo<Consultation, $this> */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('consultation_notes')
            ->logOnly([
                'consultation_id',
                'user_id',
                'visibility',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
