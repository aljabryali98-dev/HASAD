<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الميسرين المستقل (Facilitators)
        Schema::create('facilitators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // الرقم التعريفي للميسر
            $table->string('full_name'); // الاسم الرباعي
            $table->string('phone')->nullable();
            $table->string('gender')->default('ذكر');
            $table->string('education_level')->nullable();
            $table->string('specialization')->nullable();
            $table->string('experience_field')->nullable();
            $table->date('assignment_date')->nullable();
            $table->string('assigning_entity')->nullable();
            $table->string('assignment_type')->default('متطوع'); // متطوع، متعاقد، موظف، مكلف
            $table->string('status')->default('نشط'); // نشط، متوقف
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('directorate_id')->nullable()->constrained('directorates')->nullOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('full_name');
            $table->index('status');
        });

        // 2. جدول المجاميع الإنتاجية (Productive Groups)
        Schema::create('productive_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // الرقم التعريفي للمجموعة (تلقائي)
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->string('name'); // اسم المجموعة الإنتاجية
            $table->string('group_type')->default('زراعية'); // زراعية، حيوانية، نحلية، سمكية، حرفية، صناعية...
            $table->string('activity_field')->nullable(); // مجال النشاط
            $table->string('productive_activity')->nullable(); // النشاط الإنتاجي
            $table->string('main_product')->nullable(); // المنتج الرئيسي
            $table->date('founded_date')->nullable();
            $table->string('founding_minutes_number')->nullable();
            $table->string('status')->default('نشطة'); // نشطة، متوقفة، تحت التأسيس، موسمية، غير فعالة

            // الموقع الجغرافي الرباعي
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->constrained('uzlahs')->cascadeOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();

            // قيادة المجموعة وأعضاؤها
            $table->string('leader_name')->nullable();
            $table->string('leader_phone')->nullable();
            $table->integer('total_members')->default(0);
            $table->integer('male_members')->default(0);
            $table->integer('female_members')->default(0);
            $table->integer('youth_members')->default(0);
            $table->integer('active_members')->default(0);

            // النشاط والإنتاج
            $table->string('main_activity')->nullable();
            $table->string('sub_activity')->nullable();
            $table->decimal('productive_area', 12, 2)->default(0);
            $table->string('area_unit')->default('هكتار'); // هكتار، فدان، قصبة، معاد...
            $table->decimal('production_volume', 12, 2)->default(0);
            $table->string('measurement_unit')->default('كجم'); // كجم، طن، لتر، رأس...
            $table->string('production_season')->nullable();
            $table->decimal('production_value', 15, 2)->default(0);
            $table->decimal('sales_value', 15, 2)->default(0);
            $table->string('marketing_method')->default('عبر الجمعية'); // عبر الجمعية، مباشر، تاجر، تعاقدي
            $table->string('marketing_destination')->nullable();

            // المتابعة والتيسير
            $table->integer('field_visits_count')->default(0);
            $table->date('last_visit_date')->nullable();
            $table->integer('meetings_count')->default(0);
            $table->integer('trainings_count')->default(0);
            $table->text('implemented_activities')->nullable();
            $table->string('members_interaction_level')->default('جيد');
            $table->string('performance_level')->default('جيد');
            $table->text('key_achievements')->nullable();
            $table->text('needs')->nullable();
            $table->text('challenges')->nullable();
            $table->text('suggested_actions')->nullable();
            $table->date('last_update_date')->nullable();

            $table->timestamps();

            $table->index(['governorate_id', 'directorate_id', 'uzlah_id']);
            $table->index('association_id');
            $table->index('name');
            $table->index('status');
        });

        // 3. جدول الربط بين المجاميع والميسرين (Pivot: group_facilitators)
        Schema::create('group_facilitators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('productive_group_id')->constrained('productive_groups')->cascadeOnDelete();
            $table->foreignId('facilitator_id')->constrained('facilitators')->cascadeOnDelete();
            $table->date('assigned_at')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->unique(['productive_group_id', 'facilitator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_facilitators');
        Schema::dropIfExists('productive_groups');
        Schema::dropIfExists('facilitators');
    }
};
