<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 16. مشاريع الجمعيات (Association Projects)
        Schema::create('association_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('project_name');
            $table->integer('quantity')->default(1);
            $table->string('project_type')->nullable();
            $table->string('execution_status')->default('قيد التنفيذ'); // منفذ، قيد التنفيذ، متعثر، مخطط له
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id']);
        });

        // 17 & 18. التوعية بأهمية التطوع والجمعيات (Awareness Sessions)
        Schema::create('awareness_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('awareness_type')->default('تطوع'); // تطوع / جمعيات
            $table->string('target_group')->nullable();
            $table->integer('beneficiaries_count')->default(0);
            $table->integer('sessions_count')->default(1);
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->date('session_date')->nullable();
            $table->string('funding_type')->default('ذاتي');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('awareness_type');
            $table->index(['governorate_id', 'directorate_id']);
        });

        // 19. المبادرات (Community Initiatives)
        Schema::create('community_initiatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('village_name')->nullable();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('initiative_name');
            $table->string('initiative_type')->nullable();
            $table->integer('initiators_count')->default(0);
            $table->integer('beneficiaries_count')->default(0);
            $table->string('support_type')->nullable();
            $table->string('execution_entity')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->date('session_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 20. الدراسات (Studies & Research)
        Schema::create('studies_and_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->string('study_name');
            $table->string('study_type')->nullable();
            $table->string('study_field')->nullable();
            $table->string('target_group')->nullable();
            $table->integer('beneficiaries_count')->default(0);
            $table->string('execution_entity')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->string('funding_type')->default('منحة');
            $table->string('execution_status')->default('مكتملة');
            $table->decimal('completion_rate', 5, 2)->default(100);
            $table->date('study_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 21. الخطط (Strategic Plans)
        Schema::create('strategic_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('plan_name');
            $table->text('general_goal')->nullable();
            $table->string('execution_entity')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->string('execution_status')->default('قيد التنفيذ');
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 22. تكوين وتدريب الوحدات (Unit Trainings)
        Schema::create('unit_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('unit_name');
            $table->string('training_title')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone')->nullable();
            $table->date('creation_date')->nullable();
            $table->date('training_date')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 23. الإرشاد (المدارس الحقلية والجلسات الإرشادية - Agricultural Extension)
        Schema::create('agricultural_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->string('activity_name');
            $table->string('intervention_type')->nullable(); // مدرسة حقلية، جلسة إرشادية...
            $table->string('program_field')->nullable();
            $table->string('program_type')->nullable();
            $table->integer('beneficiaries_count')->default(0);
            $table->string('execution_entity')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->string('funding_type')->default('ذاتي');
            $table->string('execution_status')->default('منفذ');
            $table->date('activity_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 24. المحاصيل (Association Crops)
        Schema::create('association_crops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('intervention_status')->nullable();
            $table->string('crop_type'); // حبوب، بقوليات، خضروات، فواكه، أعلاف...
            $table->string('crop_variety')->nullable(); // الصنف
            $table->decimal('production_quantity', 12, 2)->default(0);
            $table->string('quantity_unit')->default('طن');
            $table->string('availability_status')->default('متوفرة'); // متوفرة / متوقع إنتاجها
            $table->string('execution_status')->default('منفذ');
            $table->decimal('completion_rate', 5, 2)->default(100);
            $table->date('planting_date')->nullable();
            $table->date('harvest_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id']);
        });

        // 25. الزراعة التعاقدية (Contract Farming)
        Schema::create('contract_farming', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('village_name')->nullable();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('crop_name');
            $table->string('crop_type')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('quantity_unit')->default('طن');
            $table->string('marketing_type')->nullable();
            $table->string('marketing_party')->nullable();
            $table->integer('beneficiaries_count')->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->string('execution_status')->default('ساري');
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->date('contract_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id']);
        });

        // 26. المشاتل (Nurseries)
        Schema::create('nurseries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('nursery_name');
            $table->string('ownership_type')->default('تعاوني'); // حكومي، خاص، تعاوني
            $table->decimal('capacity', 12, 2)->default(0);
            $table->string('capacity_unit')->default('شتلة');
            $table->string('status')->default('مفعل'); // مفعل، غير مفعل
            $table->date('record_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id']);
        });

        // 27. الصحراء (مشروع زراعة الصحراء والكثبان الرملية - Desert Farming)
        Schema::create('desert_farmings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->string('work_location')->nullable();
            $table->decimal('red_corn_seeds_kg', 10, 2)->default(0);
            $table->decimal('white_corn_seeds_kg', 10, 2)->default(0);
            $table->decimal('millet_seeds_kg', 10, 2)->default(0);
            $table->decimal('shahbi_seeds_kg', 10, 2)->default(0);
            $table->decimal('dujrah_seeds_kg', 10, 2)->default(0);
            $table->integer('plowing_equipment_count')->default(0);
            $table->decimal('total_work_hours', 10, 2)->default(0);
            $table->decimal('work_area_maad', 10, 2)->default(0);
            $table->decimal('diesel_quantity_liters', 12, 2)->default(0);
            $table->integer('seeding_workers_count')->default(0);
            $table->decimal('seeded_area_maad', 10, 2)->default(0);
            $table->string('seeds_source')->nullable();
            $table->decimal('drivers_expenses', 12, 2)->default(0);
            $table->decimal('workers_wages', 12, 2)->default(0);
            $table->decimal('loan_amount', 15, 2)->default(0);
            $table->date('planting_date')->nullable();
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 28. استصلاح وحماية الأراضي (Land Reclamation)
        Schema::create('land_reclamations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('village_name')->nullable();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('project_name');
            $table->string('intervention_type')->default('استصلاح'); // استصلاح، حراثة، حماية
            $table->string('land_type')->default('جديدة'); // جديدة، صلبة، صحراوية، أخرى
            $table->decimal('actual_completed_area', 12, 2)->default(0);
            $table->string('area_unit')->default('هكتار');
            $table->integer('beneficiaries_count')->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->string('execution_status')->default('منفذ');
            $table->date('execution_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 29. التوسع في زراعة المحاصيل (Crop Expansions)
        Schema::create('crop_expansions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('project_name');
            $table->date('record_date')->nullable();
            $table->string('intervention_type')->default('توسع'); // استزراع، توسع، توسع واستزراع
            $table->decimal('expanded_area_hectare', 12, 2)->default(0);
            $table->string('crop_category')->default('حبوب'); // حبوب، بقوليات، اقتصادية، فواكه، خضروات
            $table->string('crop_variety')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('quantity_unit')->default('طن');
            $table->integer('beneficiaries_count')->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 30. توطين صناعة الحليب وحملات التحصين (Dairy & Livestock)
        Schema::create('dairy_and_livestocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('village_name')->nullable();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('activity_type')->default('توطين صناعة الحليب'); // توطين صناعة الحليب / حملة تحصين
            $table->integer('target_immunized_heads')->default(0);
            $table->decimal('produced_milk_liters', 12, 2)->default(0);
            $table->string('execution_entity')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->date('activity_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });

        // 31. سجل المحاصيل والمنتجات والصناعات التحويلية للجمعية (Association Products)
        Schema::create('association_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained('associations')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('classification')->default('محصول خام'); // محصول خام، منتج حيواني، صناعة تحويلية
            $table->decimal('annual_quantity', 12, 2)->default(0);
            $table->string('unit')->default('طن');
            $table->string('productive_capacity')->nullable();
            $table->string('marketing_status')->default('محلي');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
        });

        // 32. سجل المزارعين والأراضي المزروعة (Farmers & Cultivated Lands)
        Schema::create('farmers_and_lands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->constrained('uzlahs')->cascadeOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('farmer_name'); // اسم المزارع الرباعي
            $table->string('national_id')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('total_land_area', 12, 2)->default(0);
            $table->decimal('cultivated_area', 12, 2)->default(0);
            $table->string('area_unit')->default('معاد');
            $table->string('main_crop_type')->nullable();
            $table->string('irrigation_source')->default('أمطار'); // أمطار، آبار، سدود، سيول
            $table->string('membership_status')->default('عضو'); // عضو، غير عضو، منتسب
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id', 'uzlah_id']);
            $table->index('farmer_name');
        });

        // 34. محطات الوقود (Fuel Stations)
        Schema::create('fuel_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->integer('stations_count')->default(1);
            $table->decimal('capacity_liters', 12, 2)->default(0);
            $table->string('target_group')->nullable();
            $table->integer('target_beneficiaries_count')->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('funding_source')->nullable();
            $table->string('funding_type')->default('استثماري');
            $table->string('execution_entity')->nullable();
            $table->string('execution_status')->default('قيد الإنشاء');
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_stations');
        Schema::dropIfExists('farmers_and_lands');
        Schema::dropIfExists('association_products');
        Schema::dropIfExists('dairy_and_livestocks');
        Schema::dropIfExists('crop_expansions');
        Schema::dropIfExists('land_reclamations');
        Schema::dropIfExists('desert_farmings');
        Schema::dropIfExists('nurseries');
        Schema::dropIfExists('contract_farming');
        Schema::dropIfExists('association_crops');
        Schema::dropIfExists('agricultural_extensions');
        Schema::dropIfExists('unit_trainings');
        Schema::dropIfExists('strategic_plans');
        Schema::dropIfExists('studies_and_research');
        Schema::dropIfExists('community_initiatives');
        Schema::dropIfExists('awareness_sessions');
        Schema::dropIfExists('association_projects');
    }
};
