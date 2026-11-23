<?php

namespace Database\Factories;

use App\Models\Street;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Street>
 */
class StreetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_ar' => 'شارع '.fake()->unique()->numerify('####'),
            'name_en' => fake()->unique()->words(2, true),
            'highway_type' => 'residential',
            'admin_area_id' => null,
            'center_lat' => 33.51,
            'center_lon' => 36.29,
            'bbox' => [36.289, 33.509, 36.291, 33.511],
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => [[36.289, 33.509], [36.291, 33.511]],
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function highway(string $type): static
    {
        return $this->state(['highway_type' => $type]);
    }
}
