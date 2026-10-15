<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'title', 'document_number', 'category_id', 'description', 'file_path', 'original_name', 'mime',
        'size', 'sha256', 'document_date', 'expires_at', 'confidentiality', 'tags', 'download_count', 'uploaded_by',
    ];

    protected $hidden = ['file_path'];

    protected $appends = ['extension', 'is_expired', 'is_previewable'];

    protected function casts(): array
    {
        return ['tags' => 'array', 'document_date' => 'date:Y-m-d', 'expires_at' => 'date:Y-m-d', 'size' => 'integer'];
    }

    public static function generateCode(): string
    {
        do {
            $code = 'ARS-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (self::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /** Batasi dokumen sesuai hak akses pengguna. */
    public function scopeVisibleTo(Builder $q, User $user): Builder
    {
        return $user->isAdmin() ? $q : $q->whereIn('confidentiality', ['public', 'internal']);
    }

    public function canBeAccessedBy(User $user): bool
    {
        return $user->isAdmin() || in_array($this->confidentiality, ['public', 'internal'], true);
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo((string) $this->original_name, PATHINFO_EXTENSION));
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array($this->extension, ['pdf', 'png', 'jpg', 'jpeg', 'webp'], true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
