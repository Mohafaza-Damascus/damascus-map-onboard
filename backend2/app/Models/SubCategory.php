<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCategory extends Model
{
    protected $table = 'subcategories';

    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'slug',
        'icon',
    ];

    protected $attributes = [
        'name_en' => '',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // public function places(): HasMany
    // {
    //     return $this->hasMany(Place::class, 'sub_category_id');
    // }

    public function getEffectiveIconAttribute(): string
    {
        return $this->icon
            ?: ($this->category?->icon ?: 'map-pin');
    }

    public function getEffectiveColorAttribute(): string
    {
        return '#064239';
    }
}
