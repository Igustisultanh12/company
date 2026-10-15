<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'url', 'level', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /**
     * Kirim notifikasi ke satu / banyak pengguna.
     *
     * @param  int|array<int>|iterable  $userIds
     */
    public static function send(int|iterable $userIds, string $title, ?string $body = null, ?string $url = null, string $level = 'info'): void
    {
        $ids = is_int($userIds) ? [$userIds] : collect($userIds)->filter()->unique()->values()->all();
        foreach ($ids as $id) {
            self::create(['user_id' => $id, 'title' => $title, 'body' => $body ? mb_substr($body, 0, 480) : null, 'url' => $url, 'level' => $level]);
        }
    }

    public static function toAdmins(string $title, ?string $body = null, ?string $url = null, string $level = 'info'): void
    {
        self::send(User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->pluck('id')->all(), $title, $body, $url, $level);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
