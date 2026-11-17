<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // سجل القروض البيضاء (قروض بيضاء بدون فوائد)
        Schema::create('association_loans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // رقم القرض (تلقائي)
            $table->foreignId('association_id')->nullable()->constrained('associations')->nullOnDelete(); // رقم الجمعية
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            $table->foreignId('directorate_id')->constrained('directorates')->cascadeOnDelete();

            $table->string('loan_type')->default('ممنوح'); // نوع القرض (مستلم / ممنوح / تمكين اقتصادي)
            $table->string('funding_entity'); // الجهة الممولة
            $table->string('beneficiary_name'); // المستفيد
            $table->decimal('loan_amount', 15, 2); // مبلغ القرض
            $table->string('currency')->default('ريال يمني'); // العملة
            $table->date('received_date')->nullable(); // تاريخ الحصول
            $table->integer('repayment_period_months')->default(12); // مدة السداد بالشهور
            $table->integer('grace_period_months')->default(3); // فترة السماح بالشهور
            $table->decimal('paid_amount', 15, 2)->default(0); // المبلغ المسدد
            $table->decimal('remaining_balance', 15, 2)->default(0); // الرصيد المتبقي (محسوب تلقائياً)
            $table->string('loan_status')->default('ساري'); // حالة القرض (ساري، منتظم، متعثر، مسدد بالكامل)
            $table->text('purpose')->nullable(); // الغرض من القرض
            $table->text('collaterals')->nullable(); // الضمانات

            // حقول إضافية للمحور (د) - التمويل والتمكين
            $table->string('empowerment_program_name')->nullable(); // اسم برنامج التمكين
            $table->string('activity_field')->nullable(); // مجال نشاط القرض
            $table->string('quantity_description')->nullable(); // الكمية والوحدة
            $table->integer('beneficiaries_count')->default(1);
            $table->string('execution_entity')->nullable(); // الجهة المنفذة
            $table->string('funding_type')->default('قرض أبيض'); // نوع التمويل
            $table->string('execution_status')->default('قيد التنفيذ'); // حالة التنفيذ
            $table->decimal('completion_rate', 5, 2)->default(0); // نسبة الإنجاز %
            $table->string('repayment_status')->default('قيد السداد'); // حالة السداد
            $table->string('data_source')->nullable(); // مصدر البيانات
            $table->text('notes')->nullable(); // ملاحظات

            $table->timestamps();

            $table->index('association_id');
            $table->index(['governorate_id', 'directorate_id']);
            $table->index('loan_status');
            $table->index('beneficiary_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('association_loans');
    }
};
