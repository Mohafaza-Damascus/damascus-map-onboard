<?php

namespace Database\Seeders;

use App\Models\AdminArea;
use App\Models\Street;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StreetSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/damascus_streets.geojson');

        if (! is_file($path)) {
            $this->command->error("GeoJSON file not found: {$path}");

            return;
        }

        $features = json_decode(file_get_contents($path), true)['features'] ?? [];

        $this->command->info('Found '.count($features).' street features. Importing into database...');

        // نفس admin_map بـ Django: بالترتيب (level, id) والأخير بيغلب إذا تكرر الاسم
        $areas = AdminArea::query()->orderBy('level')->orderBy('id')->get(['id', 'name_ar']);
        $adminMap = $this->buildAdminMap($areas);
        $areaNames = $areas->pluck('name_ar', 'id')->all();

        if ($adminMap === []) {
            $this->command->warn('admin_areas فاضي: شغّل AdminAreaSeeder أولاً، وإلا بتنربط كل الشوارع بدون حي.');
        }

        $imported = 0;

        DB::transaction(function () use ($features, $adminMap, $areaNames, &$imported) {
            // unguarded لأن id مو بالـ $fillable ونحن بدنا نحافظ على نفس الـ ids القديمة
            Model::unguarded(function () use ($features, $adminMap, $areaNames, &$imported) {
                foreach ($features as $feature) {
                    $row = $this->rowFromFeature($feature, $adminMap, $areaNames);

                    if ($row === null) {
                        continue;
                    }

                    Street::updateOrCreate(['id' => $row['id']], Arr::except($row, ['id']));
                    $imported++;
                }
            });

            // أدخلنا ids يدوياً، لازم نحرّك الـ sequence وإلا أي إدخال جديد رح يتعارض
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('streets', 'id'), (SELECT COALESCE(MAX(id), 1) FROM streets))");
            }
        });

        $this->command->info("Successfully imported {$imported} streets into database.");
        $this->command->info('Total streets in database: '.Street::count());
    }

    /**
     * اسم الحي (وبدون بادئة "حي ") => id المنطقة.
     *
     * @param  iterable<object{id: int, name_ar: string}>  $areas
     * @return array<string, int>
     */
    private function buildAdminMap(iterable $areas): array
    {
        $map = [];

        foreach ($areas as $area) {
            $map[$this->strip($area->name_ar)] = $area->id;

            if (str_starts_with($area->name_ar, 'حي ')) {
                $map[$this->strip(mb_substr($area->name_ar, 3))] = $area->id;
            }
        }

        return $map;
    }

    /**
     * يحوّل Feature لصف جاهز للحفظ، أو null إذا ما في اسم (نفس منطق seed_streets بـ Django).
     *
     * @param  array<string, mixed>  $feature
     * @param  array<string, int>  $adminMap
     * @param  array<int, string>  $areaNames  id => name_ar (لحساب search_text)
     * @return array<string, mixed>|null
     */
    private function rowFromFeature(array $feature, array $adminMap, array $areaNames): ?array
    {
        $props = $feature['properties'] ?? [];

        $nameAr = $this->strip((string) (($props['name_ar'] ?? null) ?: ''));

        if ($nameAr === '') {
            return null;
        }

        $nameEn = $this->strip((string) (($props['name_en'] ?? null) ?: ''));
        $highway = $this->strip((string) (($props['highway_type'] ?? null) ?: 'residential'));
        $center = ($props['center'] ?? null) ?: [36.295, 33.513];
        $bbox = ($props['bbox'] ?? null) ?: [$center[0], $center[1], $center[0], $center[1]];
        $geometry = ($feature['geometry'] ?? null) ?: [];

        // ربط الشارع بالحي: بالاسم الكامل، وإذا ما لقينا بدون بادئة "حي "
        $neighborhood = $this->strip((string) (($props['neighborhood'] ?? null) ?: ''));
        $adminAreaId = $adminMap[$neighborhood] ?? null;

        if ($adminAreaId === null && str_starts_with($neighborhood, 'حي ')) {
            $adminAreaId = $adminMap[$this->strip(mb_substr($neighborhood, 3))] ?? null;
        }

        return [
            'id' => $props['id'] ?? null,
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'highway_type' => $highway,
            'admin_area_id' => $adminAreaId,
            'center_lon' => $center[0],
            'center_lat' => $center[1],
            'bbox' => $bbox,
            'geometry' => $geometry,
            'is_active' => true,
            // نحسبه هون صراحةً: ما بنعتمد على أحداث الموديل لأنها بتتعطّل بـ WithoutModelEvents
            'search_text' => Street::searchTextFor($nameAr, $nameEn, $adminAreaId !== null ? ($areaNames[$adminAreaId] ?? null) : null),
        ];
    }

    /** مثل str.strip() بـ Python (يدعم مسافات Unicode) */
    private function strip(string $value): string
    {
        return preg_replace('/^\s+|\s+$/u', '', $value) ?? $value;
    }
}