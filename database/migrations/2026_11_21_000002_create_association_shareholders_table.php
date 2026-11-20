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
        if (!Schema::hasTable('association_shareholders')) {
            Schema::create('association_shareholders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('association_id')->constrained('associations')->cascadeOnDelete();
                $table->string('shareholder_number')->nullable()->index(); // رقم العضوية أو رقم المساهم
                $table->string('full_name');
                $table->string('national_id')->nullable();
                $table->string('phone')->nullable();
                $table->string('gender')->default('ذكر'); // ذكر / أنثى
                
                // النطاق الجغرافي للمساهم
                $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
                $table->foreignId('directorate_id')->nullable()->constrained('directorates')->nullOnDelete();
                $table->foreignId('uzlah_id')->nullable()->constrained('uzlahs')->nullOnDelete();
                $table->foreignId('sub_location_id')->nullable()->constrained('sub_locations')->nullOnDelete();

                // بيانات المساهمة والأسهم
                $table->integer('shares_count')->default(1);
                $table->decimal('share_value', 12, 2)->default(1000.00); // قيمة السهم الواحد بالريال
                $table->decimal('total_subscription_amount', 12, 2)->default(1000.00); // إجمالي الاكتتاب
                $table->decimal('paid_amount', 12, 2)->default(1000.00); // المبلغ المسدد
                $table->decimal('remaining_amount', 12, 2)->default(0.00); // المبلغ المتبقي

                $table->date('join_date')->nullable();
                $table->string('receipt_number')->nullable(); // رقم سند القبض / الإيصال
                $table->string('status')->default('نشط'); // نشط، منسحب، مجمد
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['association_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('association_shareholders');
    }
};
