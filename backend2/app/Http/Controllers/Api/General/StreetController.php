<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreetFeatureCollection;
use App\Services\General\StreetService;

class StreetController extends Controller
{
    public function __construct(
        private readonly StreetService $streetService
    ) {}

    public function geoJson(): StreetFeatureCollection
    {
        return new StreetFeatureCollection(
            $this->streetService->getActiveStreets()
        );
    }
}
