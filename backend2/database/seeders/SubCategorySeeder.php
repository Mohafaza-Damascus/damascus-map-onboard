<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class SubCategorySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/categories.json');

        if (!File::exists($path)) {
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
            $total = 0;

            foreach ($categories as $categoryData) {
                $category = Category::where(
                    'slug',
                    $categoryData['slug']
                )->first();

                if (!$category) {
                    throw new RuntimeException(
                        "Category not found: {$categoryData['slug']}. Run CategorySeeder first."
                    );
                }

                foreach ($categoryData['subcategories'] ?? [] as $item) {
                    SubCategory::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'slug' => $item['slug'],
                        ],
                        [
                            'name_ar' => $item['name_ar'],
                            'name_en' => $item['name_en'] ?? '',
                            'icon' => $item['icon'] ?? null,
                        ]
                    );

                    $total++;
                }
            }

            $this->command->info(
                "Successfully imported {$total} subcategories."
            );

            $this->command->info(
                'Total subcategories in database: ' . SubCategory::count()
            );
        });
    }
}
