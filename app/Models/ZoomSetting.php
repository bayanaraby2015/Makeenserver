<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoomSetting extends Model
{
    protected $fillable = [
        'account_id',
        'client_id',
        'client_secret',
        'user_id',
        'default_duration',
    ];

    protected $casts = [
        'client_secret' => 'encrypted',
        'default_duration' => 'integer',
    ];

    public function isConfigured(): bool
    {
        return filled($this->account_id)
            && filled($this->client_id)
            && filled($this->client_secret)
            && filled($this->user_id);
    }
}
