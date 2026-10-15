<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id', 'type', 'start_date', 'end_date', 'days', 'reason', 'attachment_path', 'status', 'reviewed_by', 'reviewed_at', 'review_note'];

    protected $hidden = ['attachment_path'];

    protected $appends = ['has_attachment'];

    protected function casts(): array
    {
        return ['start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d', 'reviewed_at' => 'datetime'];
    }

    public function getHasAttachmentAttribute(): bool
    {
        return ! empty($this->attachment_path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
