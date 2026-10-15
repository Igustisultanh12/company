<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Portfolio extends Model
{
    protected $fillable = ['title', 'slug', 'client', 'category', 'excerpt', 'description', 'image_path', 'url', 'tech_stack', 'year', 'color', 'is_featured', 'sort_order', 'is_active'];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['tech_stack' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
