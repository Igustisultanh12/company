<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'check_in_at', 'check_out_at', 'check_in_lat', 'check_in_lng', 'check_out_lat',
        'check_out_lng', 'check_in_ip', 'status', 'work_mode', 'note',
    ];

    protected $appends = ['work_minutes'];

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function getWorkMinutesAttribute(): ?int
    {
        if (! $this->check_in_at || ! $this->check_out_at) {
            return null;
        }

        return (int) $this->check_in_at->diffInMinutes($this->check_out_at);
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
