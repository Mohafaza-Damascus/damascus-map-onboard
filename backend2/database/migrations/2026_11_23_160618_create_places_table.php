<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();

            $table->string('title_ar', 255)->index();
            $table->string('title_en', 255)->default('');
            $table->text('description')->default('');

            $table->foreignId('sub_category_id')
                ->nullable()
                ->constrained('subcategories')
                ->nullOnDelete();

            $table->foreignId('admin_area_id')
                ->nullable()
                ->constrained('admin_areas')
                ->nullOnDelete();

            $table->string('sector', 20)->default('Public')->index();

            $table->double('latitude');
            $table->double('longitude');

            $table->string('location_text', 255)->default('');
            $table->json('opening_hours')->default('{}');

            $table->boolean('is_verified')->default(true);
            $table->boolean('is_visible')->default(true)->index();

            $table->string('search_text', 600)->default('');

            $table->timestamps();

            $table->index(['is_visible', 'search_text']);
            $table->index('search_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
