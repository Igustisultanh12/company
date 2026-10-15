<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = ['title', 'body', 'level', 'is_pinned', 'is_active', 'expires_at', 'created_by'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean', 'is_active' => 'boolean', 'expires_at' => 'date:Y-m-d'];
    }

    public function scopeCurrent(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
            ->orderByDesc('is_pinned')->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
