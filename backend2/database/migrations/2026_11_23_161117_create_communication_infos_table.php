<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_info', function (Blueprint $table) {
            $table->id();

            $table->foreignId('place_id')
                ->constrained('places')
                ->cascadeOnDelete();

            $table->string('value', 200);
            $table->string('type', 50);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_info');
    }
};
