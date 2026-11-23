<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StreetFeatureCollection extends ResourceCollection
{
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
