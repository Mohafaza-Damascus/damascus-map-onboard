<?php

namespace App\Services\General;

use App\Models\Street;
use Illuminate\Database\Eloquent\Collection;

class StreetService
{
    /**
     * الشوارع الفعّالة للخريطة، مرتبة بالاسم ثم id (مثل Meta.ordering بـ Django).
     * بنجيب بس الأعمدة اللي بيحتاجها الرد لأنو geometry هي الأثقل.
     *
     * @return Collection<int, Street>
     */
    public function activeForMap(): Collection
    {
        return Street::query()
            ->active()
            ->orderBy('name_ar')
            ->orderBy('id')
            ->get(['id', 'name_ar', 'name_en', 'highway_type', 'admin_area_id', 'geometry']);
    }

    /**
     * شوارع البحث الشامل: أول $limit نتيجة بالاسم (نفس [:30] بـ Django).
     *
     * @param  list<string>  $variants
     * @return Collection<int, Street>
     */
    public function search(string $term, array $variants, int $limit = 30): Collection
    {
        return Street::query()
            ->active()
            ->matching($term, $variants)
            ->with('adminArea:id,name_ar')
            ->orderBy('name_ar')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
