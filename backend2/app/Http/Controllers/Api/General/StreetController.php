<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreetFeatureCollection;
use App\Services\General\StreetService;
use Dedoc\Scramble\Attributes\Group;

#[Group('Streets', description: 'شوارع وطرقات دمشق')]
class StreetController extends Controller
{
    public function __construct(private readonly StreetService $service) {}

    /**
     * شوارع دمشق للخريطة
     *
     * FeatureCollection بصيغة GeoJSON للشوارع الفعّالة فقط. (views.list_streets_geojson)
     */
    public function geojson(): StreetFeatureCollection
    {
        return new StreetFeatureCollection($this->service->activeForMap());
    }
}
