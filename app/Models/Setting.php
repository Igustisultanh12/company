<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'site_settings_v1';

    /**
     * Semua pengaturan dalam bentuk array key => value (di-cache).
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }
            } catch (\Throwable) {
                return [];
            }

            return self::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::allCached();

        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '' ? $all[$key] : $default;
    }

    public static function put(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function putMany(array $values): void
    {
        foreach ($values as $k => $v) {
            self::query()->updateOrCreate(['key' => $k], ['value' => is_bool($v) ? ($v ? '1' : '0') : $v]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::get($key);

        return $v === null ? $default : in_array((string) $v, ['1', 'true', 'on', 'yes'], true);
    }
}
