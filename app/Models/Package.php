<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    protected $fillable = ['service_id', 'name', 'slug', 'price', 'original_price', 'price_note', 'description', 'features', 'delivery_time', 'color', 'is_popular', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['features' => 'array', 'is_active' => 'boolean', 'is_popular' => 'boolean', 'price' => 'integer', 'original_price' => 'integer'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('price');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
