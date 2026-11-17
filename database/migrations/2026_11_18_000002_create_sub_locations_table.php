<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uzlah_id')->constrained('uzlahs')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('entity_type')->default('قرية'); // قرية، محلة، جمعية تعاونية، منطقة زراعية
            $table->text('notes')->nullable();
            $table->integer('is_active')->default(1);
            $table->timestamps();

            // الفهارس والقيود الفريدة المطلوبة بدقة
            $table->unique(['uzlah_id', 'name_ar', 'entity_type'], 'sub_locations_uzlah_name_type_unique');
            $table->index('uzlah_id');
            $table->index('directorate_id');
            $table->index('governorate_id');
            $table->index('name_ar');
            $table->index('entity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_locations');
    }
};
