<?php

namespace App\Services\General;

use App\Models\Street;
use Illuminate\Database\Eloquent\Collection;

class StreetService
{
    public function getActiveStreets(): Collection
    {
        return Street::query()
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get();
    }
}
