<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الجمعيات التعاونية المركزي (Cooperative Associations)
        Schema::create('associations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // الرقم التعريفي للجمعية
            $table->string('name'); // اسم الجمعية الرسمي
            $table->string('type')->default('زراعية'); // نوع الجمعية
            $table->string('license_number')->nullable();
            $table->date('license_date')->nullable();
            $table->date('founded_date')->nullable();
            $table->date('announced_date')->nullable();
            $table->string('status')->default('نشطة'); // نشطة، متوقفة، تحت التأسيس، متعثرة

            // الربط الجغرافي الرباعي
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->constrained('uzlahs')->cascadeOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('address')->nullable(); // عنوان مقر الجمعية / القرية
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website_url')->nullable();

            // البيانات القانونية والتنظيمية
            $table->string('issuing_authority')->nullable();
            $table->string('license_status')->nullable();
            $table->boolean('has_bylaws')->default(true); // نظام أساسي
            $table->boolean('has_member_registry')->default(true); // سجل رسمي للأعضاء
            $table->boolean('has_founding_minutes')->default(true); // محضر تأسيسي
            $table->boolean('has_headquarters')->default(true); // مقر رسمي
            $table->string('headquarters_ownership')->default('إيجار'); // ملك، إيجار، مستضاف
            $table->boolean('holds_regular_meetings')->default(true); // اجتماعات دورية
            $table->date('last_general_assembly_date')->nullable();
            $table->date('last_election_date')->nullable();
            $table->date('last_data_update_date')->nullable();

            // ملخص القيادة والإدارة
            $table->string('president_name')->nullable();
            $table->string('president_phone')->nullable();
            $table->string('vice_president_name')->nullable();
            $table->string('secretary_general_name')->nullable();
            $table->string('financial_officer_name')->nullable();
            $table->string('oversight_officer_name')->nullable();
            $table->string('executive_director_name')->nullable();
            $table->integer('board_members_count')->default(7);
            $table->integer('oversight_members_count')->default(3);
            $table->string('board_status')->default('سارية');
            $table->date('board_term_end_date')->nullable();
            $table->integer('employees_count')->default(0);
            $table->integer('volunteers_count')->default(0);

            // بيانات الأعضاء والمنتسبين
            $table->integer('total_members')->default(0);
            $table->integer('male_members')->default(0);
            $table->integer('female_members')->default(0);
            $table->integer('new_members_this_year')->default(0);
            $table->integer('beneficiaries_count')->default(0);
            $table->integer('beneficiary_families_count')->default(0);

            // البيانات الاقتصادية والمالية
            $table->decimal('initial_capital', 15, 2)->default(0);
            $table->decimal('current_capital', 15, 2)->default(0);
            $table->decimal('share_value', 10, 2)->default(1000);
            $table->integer('shares_count')->default(0);
            $table->decimal('annual_subscription_fee', 10, 2)->default(0);
            $table->decimal('total_assets_value', 15, 2)->default(0);
            $table->boolean('has_bank_account')->default(false);
            $table->boolean('has_accounting_records')->default(true);
            $table->date('last_approved_budget_date')->nullable();

            // الأنشطة والمشاريع التعاونية (نصوص وإحصائيات)
            $table->text('main_activities')->nullable();
            $table->text('agricultural_activities')->nullable();
            $table->text('marketing_activities')->nullable();
            $table->text('productive_activities')->nullable();
            $table->text('service_activities')->nullable();
            $table->integer('implemented_projects_count')->default(0);
            $table->integer('ongoing_projects_count')->default(0);
            $table->integer('stalled_projects_count')->default(0);
            $table->integer('planned_projects_count')->default(0);
            $table->text('key_achievements')->nullable();
            $table->text('key_products')->nullable();
            $table->string('annual_production_volume')->nullable();
            $table->text('target_markets')->nullable();
            $table->text('partners_and_supporters')->nullable();

            // الأصول والممتلكات
            $table->text('agricultural_lands')->nullable();
            $table->text('wells_water_structures')->nullable();
            $table->text('machinery_equipment')->nullable();
            $table->text('transport_vehicles')->nullable();
            $table->text('warehouses')->nullable();
            $table->text('collection_marketing_centers')->nullable();
            $table->text('buildings_headquarters')->nullable();
            $table->text('other_assets')->nullable();
            $table->decimal('estimated_assets_value', 15, 2)->default(0);

            // بيانات المتابعة والتقييم
            $table->string('cooperative_activity_level')->default('متوسط');
            $table->string('administrative_status')->default('مستقر');
            $table->string('financial_status')->default('مستقر');
            $table->string('legal_status')->default('ساري');
            $table->string('activity_execution_level')->default('جيد');
            $table->text('top_needs')->nullable();
            $table->text('challenges_difficulties')->nullable();
            $table->text('training_needs')->nullable();
            $table->text('required_support')->nullable();
            $table->date('last_field_visit_date')->nullable();
            $table->string('visiting_entity')->nullable();
            $table->text('monitoring_team_notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('general_evaluation')->default('جيد');

            $table->timestamps();

            // الفهارس للأداء العالي والبحث الفوري
            $table->index(['governorate_id', 'directorate_id', 'uzlah_id']);
            $table->index('name');
            $table->index('status');
            $table->index('type');
        });

        // 2. جدول تفصيلي لأعضاء القيادة والإدارة (Association Board Members)
        Schema::create('association_board_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained('associations')->cascadeOnDelete();
            $table->string('full_name'); // الاسم الرباعي
            $table->string('gender')->default('ذكر'); // ذكر / أنثى
            $table->date('birth_date')->nullable();
            $table->string('national_id')->nullable(); // رقم البطاقة الشخصية
            $table->string('education_level')->nullable(); // المؤهل التعليمي
            $table->string('specialization')->nullable(); // التخصص
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('directorate_id')->nullable()->constrained('directorates')->nullOnDelete();
            $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('village_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('role_title')->default('عضو هيئة إدارية'); // رئيس، نائب رئيس، أمين عام، مسؤول مالي...
            $table->date('assignment_start_date')->nullable();
            $table->date('assignment_end_date')->nullable();
            $table->string('membership_status')->default('نشط'); // نشط، متوقف، منتهي التكليف
            $table->string('assigning_entity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('association_id');
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('association_board_members');
        Schema::dropIfExists('associations');
    }
};
