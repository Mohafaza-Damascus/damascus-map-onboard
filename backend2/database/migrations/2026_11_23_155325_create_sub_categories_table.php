<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->string('name_ar', 100);
            $table->string('name_en', 100)->default('');
            $table->string('slug', 100);
            $table->string('icon', 50)->nullable();

            $table->timestamps();

            $table->unique(['category_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};
