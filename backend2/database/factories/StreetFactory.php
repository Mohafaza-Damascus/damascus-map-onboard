<?php

namespace Database\Factories;

use App\Models\AdminArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Street>
 */
class StreetFactory extends Factory
{
    public function definition(): array
    {
        $lon = fake()->randomFloat(6, 36.14, 36.44);
        $lat = fake()->randomFloat(6, 33.40, 33.63);

        return [
            'name_ar' => 'شارع '.fake()->unique()->word(),
            'name_en' => fake()->streetName(),
            'highway_type' => 'residential',
            'admin_area_id' => AdminArea::factory(),
            'center_lat' => $lat,
            'center_lon' => $lon,
            'bbox' => [
                $lon - 0.001,
                $lat - 0.001,
                $lon + 0.001,
                $lat + 0.001,
            ],
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => [
                    [$lon, $lat],
                    [$lon + 0.001, $lat + 0.001],
                ],
            ],
            'is_active' => true,
        ];
    }
}
