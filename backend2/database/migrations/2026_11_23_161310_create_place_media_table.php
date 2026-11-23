<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('place_id')
                ->constrained('places')
                ->cascadeOnDelete();

            $table->string('file_url', 500);
            $table->string('media_type', 20)->default('image');
            $table->string('caption', 255)->default('');
            $table->boolean('is_cover')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_media');
    }
};
