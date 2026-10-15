<?php

namespace App\Models;

use App\Support\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SignatureRequest extends Model
{
    protected $fillable = [
        'code', 'user_id', 'title', 'document_number', 'purpose', 'original_path', 'original_name', 'original_sha256',
        'signed_path', 'signed_sha256', 'page_count', 'placements', 'include_stamp', 'status', 'signer_id',
        'signer_name', 'signer_position', 'note', 'signed_at', 'rejected_at', 'revoked_at', 'revoke_reason', 'verify_count',
    ];

    protected $hidden = ['original_path', 'signed_path'];

    protected $appends = ['status_label'];

    protected function casts(): array
    {
        return [
            'placements' => 'array',
            'include_stamp' => 'boolean',
            'signed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'revoked_at' => 'datetime',
            'page_count' => 'integer',
        ];
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TTE-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getStatusLabelAttribute(): string
    {
        return Catalog::TTE_STATUSES[$this->status ?? '']['label'] ?? (string) $this->status;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}
