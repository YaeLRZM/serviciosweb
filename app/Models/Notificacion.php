<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'data',
        'leida_at',
    ];

    protected $casts = [
        'data' => 'array',
        'leida_at' => 'datetime',
    ];

    protected $appends = [
        'leida',
    ];

    public function getLeidaAttribute(): bool
    {
        return $this->leida_at !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
