<?php

use App\Models\AdminArea;
use App\Models\Street;

describe('GET /api/streets/geojson', function () {
    it('returns only active streets as a GeoJSON FeatureCollection', function () {
        $area = AdminArea::factory()->neighborhood()->create();
        $street = Street::factory()->for($area, 'adminArea')->create([
            'name_ar' => 'شارع فايز منصور',
            'name_en' => 'Fayez Mansour Street',
            'highway_type' => 'primary',
        ]);
        Street::factory()->inactive()->create();

        $this->getJson('/api/streets/geojson')
            ->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonPath('count', 1)
            ->assertJsonPath('features.0.type', 'Feature')
            ->assertJsonPath('features.0.id', $street->id)
            ->assertJsonPath('features.0.properties.name', 'شارع فايز منصور')
            ->assertJsonPath('features.0.properties.name:ar', 'شارع فايز منصور')
            ->assertJsonPath('features.0.properties.name:latin', 'Fayez Mansour Street')
            ->assertJsonPath('features.0.properties.highway_type', 'primary')
            ->assertJsonPath('features.0.properties.admin_area_id', $area->id)
            ->assertJsonPath('features.0.geometry.type', 'LineString');
    });

    it('falls back to the english name when the arabic name is empty', function () {
        Street::factory()->create(['name_ar' => '', 'name_en' => 'Baghdad Street']);

        $this->getJson('/api/streets/geojson')
            ->assertJsonPath('features.0.properties.name', 'Baghdad Street')
            ->assertJsonPath('features.0.properties.name:ar', 'Baghdad Street');
    });

    it('classifies roads like the old backend', function (string $highway, string $class, int $ramp) {
        Street::factory()->highway($highway)->create();

        $this->getJson('/api/streets/geojson')
            ->assertJsonPath('features.0.properties.class', $class)
            ->assertJsonPath('features.0.properties.ramp', $ramp);
    })->with([
        'residential' => ['residential', 'minor', 0],
        'unclassified' => ['unclassified', 'minor', 0],
        'living_street' => ['living_street', 'minor', 0],
        'road' => ['road', 'minor', 0],
        'footway' => ['footway', 'path', 0],
        'pedestrian' => ['pedestrian', 'path', 0],
        'steps' => ['steps', 'path', 0],
        'trunk_link' => ['trunk_link', 'trunk', 1],
        'primary_link' => ['primary_link', 'primary', 1],
        'motorway' => ['motorway', 'motorway', 0],
        'service' => ['service', 'service', 0],
    ]);
});

describe('Street model', function () {
    it('builds search_text from the names and the area name on save', function () {
        $area = AdminArea::factory()->create(['name_ar' => 'حي المزة']);

        $street = Street::factory()->for($area, 'adminArea')->create([
            'name_ar' => 'شارع فايز منصور',
            'name_en' => 'Fayez Mansour Street',
        ]);

        expect($street->search_text)
            ->toContain('فايز')
            ->toContain('المزه')
            ->toContain('fayez');
    });
});
