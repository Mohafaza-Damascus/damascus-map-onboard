<?php

namespace App\Models;

use App\Support\SearchIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    protected $table = 'places';

    protected $fillable = [
        'title_ar',
        'title_en',
        'description',
        'sub_category_id',
        'admin_area_id',
        'sector',
        'latitude',
        'longitude',
        'location_text',
        'opening_hours',
        'is_verified',
        'is_visible',
        'search_text',
    ];

    protected $attributes = [
        'title_en' => '',
        'description' => '',
        'sector' => 'Public',
        'location_text' => '',
        'opening_hours' => '{}',
        'is_verified' => true,
        'is_visible' => true,
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'opening_hours' => 'array',
        'is_verified' => 'boolean',
        'is_visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Place $place) {
            $adminName = $place->admin_area_id
                ? AdminArea::whereKey($place->admin_area_id)->value('name_ar')
                : '';

            $subcategoryName = $place->sub_category_id
                ? SubCategory::whereKey($place->sub_category_id)->value('name_ar')
                : '';

            $place->search_text = SearchIndex::build(
                $place->title_ar,
                $place->title_en,
                $adminName,
                $subcategoryName,
                $place->location_text
            );
        });
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function adminArea(): BelongsTo
    {
        return $this->belongsTo(AdminArea::class, 'admin_area_id');
    }

    public function communicationInfo(): HasMany
    {
        return $this->hasMany(CommunicationInfo::class, 'place_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PlaceMedia::class, 'place_id');
    }

    public function getCategoryAttribute(): ?Category
    {
        return $this->subCategory?->category;
    }
}
