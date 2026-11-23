<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceMedia extends Model
{
    protected $table = 'place_media';

    protected $fillable = [
        'place_id',
        'file_url',
        'media_type',
        'caption',
        'is_cover',
    ];

    protected $attributes = [
        'media_type' => 'image',
        'caption' => '',
        'is_cover' => false,
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}
