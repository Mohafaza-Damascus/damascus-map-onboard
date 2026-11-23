<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'name_en' => '',
        'icon' => 'map-pin',
        'is_active' => true,
        'sort_order' => 0,
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    public function places(): HasManyThrough
    {
        return $this->hasManyThrough(
            Place::class,
            SubCategory::class,
            'category_id',
            'sub_category_id',
            'id',
            'id'
        );
    }
}
