<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationInfo extends Model
{
    protected $table = 'communication_info';

    protected $fillable = [
        'place_id',
        'value',
        'type',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}
