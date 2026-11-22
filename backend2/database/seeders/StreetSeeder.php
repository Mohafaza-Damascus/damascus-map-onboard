<?php

namespace Database\Seeders;

use App\Models\AdminArea;
use App\Models\Street;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;


class StreetSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/damascus_streets.geojson');

        if (! File::exists($path)) {
            throw new RuntimeException(
                "Street GeoJSON file not found: {$path}"
            );
        }

        $data = json_decode(
            File::get($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $features = $data['features'] ?? [];

        $this->command->info(
            "Found ".count($features)." street features."
        );

        // نفس فكرة Django: مطابقة اسم الحي مع AdminArea
        $adminMap = [];

        foreach (AdminArea::all() as $area) {
            $name = trim($area->name_ar);

            $adminMap[$name] = $area;

            if (str_starts_with($name, 'حي ')) {
                $adminMap[trim(mb_substr($name, 3))] = $area;
            }
        }

        $importedCount = 0;

        DB::transaction(function () use (
            $features,
            $adminMap,
            &$importedCount
        ) {
            Model::unguarded(function () use (
                $features,
                $adminMap,
                &$importedCount
            ) {
                foreach ($features as $feature) {
                    $properties = $feature['properties'] ?? [];

                    $nameAr = trim($properties['name_ar'] ?? '');

                    if ($nameAr === '') {
                        continue;
                    }

                    $nameEn = trim($properties['name_en'] ?? '');
                    $highwayType = trim(
                        $properties['highway_type'] ?? 'residential'
                    );

                    $center = $properties['center'] ?? [36.295, 33.513];

                    $bbox = $properties['bbox'] ?? [
                        $center[0],
                        $center[1],
                        $center[0],
                        $center[1],
                    ];

                    $geometry = $feature['geometry'] ?? [];

                    $neighborhood = trim(
                        $properties['neighborhood'] ?? ''
                    );

                    $adminArea = $adminMap[$neighborhood] ?? null;

                    if (
                        ! $adminArea &&
                        str_starts_with($neighborhood, 'حي ')
                    ) {
                        $adminArea = $adminMap[
                            trim(mb_substr($neighborhood, 3))
                        ] ?? null;
                    }

                    $id = $properties['id'] ?? null;

                    if ($id === null) {
                        continue;
                    }

                    Street::updateOrCreate(
                        ['id' => $id],
                        [
                            'name_ar' => $nameAr,
                            'name_en' => $nameEn,
                            'highway_type' => $highwayType,
                            'admin_area_id' => $adminArea?->id,
                            'center_lon' => $center[0],
                            'center_lat' => $center[1],
                            'bbox' => $bbox,
                            'geometry' => $geometry,
                            'is_active' => true,
                        ]
                    );

                    $importedCount++;
                }
            });

            // لأننا أدخلنا IDs من ملف GeoJSON يدوياً
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("
                    SELECT setval(
                        pg_get_serial_sequence('streets', 'id'),
                        COALESCE((SELECT MAX(id) FROM streets), 1),
                        EXISTS(SELECT 1 FROM streets)
                    )
                ");
            }
        });

        $this->command->info(
            "Successfully imported {$importedCount} streets."
        );

        $this->command->info(
            "Total streets in database: ".Street::count()
        );
    }
}
