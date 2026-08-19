<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCode extends Model
{
    protected $fillable = [
        'personnel_id',
        'code',
        'mode',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    public static function invalidatePreviousCodes(int $personnelId): void
    {
        self::where('personnel_id', $personnelId)
            ->where('used', false)
            ->update(['used' => true]);
    }
}