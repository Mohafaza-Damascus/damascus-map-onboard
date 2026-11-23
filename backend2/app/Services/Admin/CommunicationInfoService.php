<?php

namespace App\Services\Admin;

use App\Models\Place;

class CommunicationInfoService
{
    public function updateForPlace(Place $place, array $contacts): void
    {
        if (empty($contacts)) {
            return;
        }

        $types = collect($contacts)
            ->pluck('type')
            ->unique()
            ->all();

        $place->communicationInfo()
            ->whereIn('type', $types)
            ->delete();

        $place->communicationInfo()->createMany($contacts);
    }
}
