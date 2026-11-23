<?php

namespace App\Http\Resources;

use App\Models\Street;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * شارع واحد بصيغة GeoJSON Feature (للخريطة).
 *
 * @mixin Street
 */
class StreetFeatureResource extends JsonResource
{
    // بدون غلاف "data" حتى يبقى الشكل مطابق لـ Django
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $name = $this->name_ar ?: $this->name_en;

        return [
            'type' => 'Feature',
            'id' => $this->id,
            'properties' => [
                'id' => $this->id,
                'name' => $name,
                'name:ar' => $name,
                'name:latin' => $this->name_en,
                'name_ar' => $this->name_ar,
                'name_en' => $this->name_en,
                'highway_type' => $this->highwayOrDefault(),
                'class' => $this->roadClass(),
                'ramp' => (int) $this->isRamp(),
                'admin_area_id' => $this->admin_area_id,
            ],
            'geometry' => $this->geometry,
        ];
    }
}
