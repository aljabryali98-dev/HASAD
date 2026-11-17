<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول الكوادر والفرسان والتدريب الموحد (Unified Personnel & Knights)
        Schema::create('unified_personnel', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // كود تسلسلي فريد
            $table->string('category'); // الفئة الرئيسية (فرسان الجمعية، باحثين، تدريب محاسبين، ضباط إقراض، صحة حيوانية، إعلام، سكرتارية الجمعيات، مدربي الجمعية، مساعدي مدربي الجمعية، مدربين فرسان التنمية، رائد تنموي، مساعدين ثقافيين، رائد جامعي، تدريب مرشدين، طلاب زراعة)
            $table->string('sub_specialty')->nullable(); // التخصص الفرعي لفرسان الجمعية (التنمية، الهندسة، التسويق، الديزل، المحاماة، الري، المسح، الألبان...)

            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete(); // ربط اختياري بالجمعية

            // الموقع الجغرافي الرباعي
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();
            $table->foreignId('uzlah_id')->constrained('uzlahs')->cascadeOnDelete();
            $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();
            $table->string('village_name')->nullable();

            // البيانات الشخصية والمهنية
            $table->string('full_name'); // الاسم الرباعي
            $table->string('gender')->default('ذكر');
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable(); // العمر
            $table->string('education_level')->nullable(); // المؤهل
            $table->string('specialization')->nullable(); // التخصص
            $table->date('graduation_date')->nullable(); // تاريخ التخرج
            $table->string('employer')->nullable(); // جهة العمل
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();

            // بيانات التدريب والتأهيل
            $table->string('training_program_name')->nullable(); // اسم البرنامج التدريبي
            $table->string('target_group')->nullable(); // الفئة المستهدفة
            $table->date('training_date')->nullable(); // تاريخ التدريب
            $table->string('blood_type')->nullable(); // فصيلة الدم
            $table->boolean('is_activated_in_field')->default(false); // هل تم تفعيله بالميدان (نعم/لا)
            $table->string('activation_entity')->nullable(); // جهة التفعيل
            $table->text('notes')->nullable();

            // حقول إضافية خاصة بتدريب المرشدين
            $table->string('general_program')->nullable(); // البرنامج العام
            $table->string('initiative_type')->nullable(); // المبادرة (إرشاد زراعي / الإرشاد الزراعي المجتمعي / الإرشاد الزراعي الجمعيات / الإرشاد الزراعي سفراء التنمية)
            $table->string('trainer_name')->nullable(); // اسم المدرب

            // حقول إضافية خاصة بطلاب الزراعة
            $table->string('enrollment_year')->nullable(); // سنة الالتحاق
            $table->string('coordinating_entity')->nullable(); // الجهة المنسقة للالتحاق
            $table->string('activation_type')->nullable(); // نوع التفعيل

            $table->timestamps();

            // الفهارس
            $table->index(['category', 'sub_specialty']);
            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id', 'uzlah_id']);
            $table->index('full_name');
            $table->index('is_activated_in_field');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unified_personnel');
    }
};
