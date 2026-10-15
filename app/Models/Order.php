<?php

namespace App\Models;

use App\Support\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'client_name', 'client_email', 'client_phone', 'company_name', 'website_type', 'package_id',
        'features', 'pages_estimate', 'budget_range', 'deadline', 'reference_urls', 'description', 'color_preference',
        'has_domain', 'has_hosting', 'status', 'priority', 'quoted_price', 'progress', 'assigned_to',
        'referral_agent_id', 'source', 'internal_notes', 'ip_address', 'started_at', 'completed_at',
    ];

    protected $appends = ['status_label', 'type_label'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'deadline' => 'date:Y-m-d',
            'has_domain' => 'boolean',
            'has_hosting' => 'boolean',
            'quoted_price' => 'integer',
            'progress' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        do {
            $code = 'KSA-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (self::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    public function getStatusLabelAttribute(): string
    {
        return Catalog::ORDER_STATUSES[$this->status ?? '']['label'] ?? (string) $this->status;
    }

    public function getTypeLabelAttribute(): string
    {
        return Catalog::WEBSITE_TYPES[$this->website_type ?? '']['label'] ?? (string) $this->website_type;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function referralAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_agent_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class)->latest();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function log(string $message, string $type = 'comment', bool $public = false, ?int $userId = null, array $meta = []): OrderActivity
    {
        return $this->activities()->create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'message' => $message,
            'is_public' => $public,
            'meta' => $meta ?: null,
        ]);
    }
}
