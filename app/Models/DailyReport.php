<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DailyReport extends Model
{
    protected $fillable = ['user_id', 'date', 'summary', 'blockers', 'plan_tomorrow', 'hours', 'mood'];

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d', 'hours' => 'float'];
    }

    /** Simpan sebagai Y-m-d agar pencocokan tanggal konsisten di MySQL & SQLite. */
    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['date'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
