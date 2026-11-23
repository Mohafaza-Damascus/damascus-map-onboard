<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StreetFeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $highway = $this->highway_type ?: 'residential';

        $roadClass = str_ends_with($highway, '_link')
            ? substr($highway, 0, -5)
            : $highway;

        if (in_array($roadClass, [
            'residential',
            'unclassified',
            'living_street',
            'road',
        ], true)) {
            $roadClass = 'minor';
        } elseif (in_array($roadClass, [
            'footway',
            'cycleway',
            'pedestrian',
            'steps',
            'bridleway',
        ], true)) {
            $roadClass = 'path';
        }

        return [
            'type' => 'Feature',
            'id' => $this->id,
            'properties' => [
                'id' => $this->id,
                'name' => $this->name_ar ?: $this->name_en,
                'name:ar' => $this->name_ar ?: $this->name_en,
                'name:latin' => $this->name_en,
                'name_ar' => $this->name_ar,
                'name_en' => $this->name_en,
                'highway_type' => $highway,
                'class' => $roadClass,
                'ramp' => (int) str_ends_with($highway, '_link'),
                'admin_area_id' => $this->admin_area_id,
            ],
            'geometry' => $this->geometry,
        ];
    }
}
