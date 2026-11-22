
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streets', function (Blueprint $table) {
            $table->id();

            $table->string('name_ar', 255)->index();
            $table->string('name_en', 255)->default('');

            $table->string('highway_type', 50)->default('residential');

            $table->foreignId('admin_area_id')
                ->nullable()
                ->constrained('admin_areas')
                ->nullOnDelete();

            $table->double('center_lat');
            $table->double('center_lon');

            $table->jsonb('bbox');
            $table->jsonb('geometry');

            $table->string('search_text', 400)
                ->default('')
                ->index();

            $table->boolean('is_active')->default(true);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streets');
    }
};
