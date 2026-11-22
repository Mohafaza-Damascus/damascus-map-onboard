<?php

namespace App\Models;

use App\Support\SearchIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Street extends Model
{
    use HasFactory;

    protected $table = 'streets';

    protected $fillable = [
        'name_ar',
        'name_en',
        'highway_type',
        'admin_area_id',
        'center_lat',
        'center_lon',
        'bbox',
        'geometry',
        'is_active',
        'search_text',
    ];

    protected $casts = [
        'center_lat' => 'float',
        'center_lon' => 'float',
        'bbox' => 'array',
        'geometry' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Street $street) {
            $adminName = $street->adminArea?->name_ar ?? '';

            $street->search_text = SearchIndex::build(
                    $street->name_ar,
                    $street->name_en,
                    $adminName
                );
           
        });
    }

    public function adminArea(): BelongsTo
    {
        return $this->belongsTo(AdminArea::class, 'admin_area_id');
    }
}
