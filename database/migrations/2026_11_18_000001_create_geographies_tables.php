<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. المحافظات (Governorates - 22 محافظة)
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. المديريات (Directorates - 333 مديرية)
        Schema::create('directorates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. العزل (Uzlahs - 259 عزلة ومركز)
        Schema::create('uzlahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name_ar');
            $table->string('name')->nullable();
            $table->string('type')->default('عزلة');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. القرى والمحلات (Villages)
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->cascadeOnDelete();
            $table->unsignedBigInteger('sub_district_id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // إنشاء Views للتوافق المزدوج مع التسميات البديلة (districts / sub_districts)
        DB::statement("CREATE VIEW IF NOT EXISTS districts AS SELECT id, governorate_id, code, name_ar, COALESCE(name, name_ar) as name, is_active, created_at, updated_at FROM directorates");
        DB::statement("CREATE VIEW IF NOT EXISTS sub_districts AS SELECT id, directorate_id as district_id, governorate_id, code, name_ar, COALESCE(name, name_ar) as name, type, is_active, created_at, updated_at FROM uzlahs");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS sub_districts");
        DB::statement("DROP VIEW IF EXISTS districts");
        Schema::dropIfExists('villages');
        Schema::dropIfExists('uzlahs');
        Schema::dropIfExists('directorates');
        Schema::dropIfExists('governorates');
    }
};
