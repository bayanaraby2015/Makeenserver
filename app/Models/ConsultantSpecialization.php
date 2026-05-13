<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantSpecialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultant_user_id',
        'specialization',
    ];

    /** @return BelongsTo<User, $this> */
    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_user_id');
    }
}

