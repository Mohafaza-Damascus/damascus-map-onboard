<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * FeatureCollection بصيغة GeoJSON للشوارع.
 */
class StreetFeatureCollection extends ResourceCollection
{
    public static $wrap = null;

    public $collects = StreetFeatureResource::class;

    public function toArray(Request $request): array
    {
        return [
            'type' => 'FeatureCollection',
            'count' => $this->collection->count(),
            'features' => $this->collection,
        ];
    }
}
