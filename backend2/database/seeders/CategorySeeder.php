<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/categories.json');

        if (! File::exists($path)) {
            throw new RuntimeException(
                "Categories JSON file not found: {$path}"
            );
        }

        $categories = json_decode(
            File::get($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        DB::transaction(function () use ($categories) {
            foreach ($categories as $item) {
                Category::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'name_ar' => $item['name_ar'],
                        'name_en' => $item['name_en'] ?? '',
                        'icon' => $item['icon'] ?? 'map-pin',
                        'is_active' => true,
                        'sort_order' => $item['sort_order'] ?? 0,
                    ]
                );
            }
        });

        $this->command->info(
            'Successfully imported '.count($categories).' categories.'
        );

        $this->command->info(
            'Total categories in database: '.Category::count()
        );
    }
}
