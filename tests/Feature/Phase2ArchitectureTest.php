<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\AssociationBoardMember;
use App\Models\AssociationCrop;
use App\Models\AssociationLoan;
use App\Models\AssociationProject;
use App\Models\Directorate;
use App\Models\Facilitator;
use App\Models\FarmerAndLand;
use App\Models\Governorate;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Models\User;
use App\Models\Uzlah;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2ArchitectureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Governorate $governorate;
    private Directorate $directorate;
    private Uzlah $uzlah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->governorate = Governorate::create([
            'id' => 2,
            'name_ar' => 'صنعاء',
            'code' => '02',
            'is_active' => true,
        ]);

        $this->directorate = Directorate::create([
            'id' => 201,
            'governorate_id' => $this->governorate->id,
            'name_ar' => 'سنحان وبني بهلول',
            'code' => '0201',
            'is_active' => true,
        ]);

        $this->uzlah = Uzlah::create([
            'id' => 20101,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'name_ar' => 'سنحان',
            'code' => '020101',
            'type' => 'عزلة',
            'is_active' => true,
        ]);
    }

    /**
     * 1. فحص لوحة التحكم المركزية ومؤشرات الأداء المباشرة
     */
    public function test_dashboard_loads_with_live_aggregations_and_kpis(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('لوحة الإحصائيات والمتابعة المركزية');
        $response->assertSee('الجمعيات التعاونية');
        $response->assertSee('المجاميع الإنتاجية');
        $response->assertSee('محفظة القروض البيضاء');
        $response->assertViewHas('kpis');
    }

    /**
     * 2. إنشاء جمعية تعاونية مع التوليد التلقائي للكود وإدراج رئيس الجمعية كعضو قيادي أول
     */
    public function test_association_creation_with_auto_generated_code_and_president_board_member(): void
    {
        $payload = [
            'name' => 'جمعية سنحان التعاونية الزراعية متعددة الأغراض',
            'type' => 'زراعية متعددة الأغراض',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
            'president_name' => 'محمد أحمد الصنعاني',
            'president_phone' => '777123456',
            'shares_count' => 1000,
            'share_value' => 5000,
            'initial_capital' => 5000000,
            'current_capital' => 7500000,
        ];

        $response = $this->actingAs($this->user)->post(route('associations.store'), $payload);

        $this->assertDatabaseHas('associations', [
            'name' => 'جمعية سنحان التعاونية الزراعية متعددة الأغراض',
            'uzlah_id' => $this->uzlah->id,
        ]);

        $association = Association::where('name', 'جمعية سنحان التعاونية الزراعية متعددة الأغراض')->first();
        $this->assertNotNull($association);
        $this->assertStringStartsWith('ASS-020101-', $association->code);

        // التحقق من الإدراج التلقائي لرئيس الجمعية في جدول أعضاء الهيئة الإدارية
        $this->assertDatabaseHas('association_board_members', [
            'association_id' => $association->id,
            'full_name' => 'محمد أحمد الصنعاني',
            'role_title' => 'رئيس الجمعية',
            'phone' => '777123456',
        ]);
    }

    /**
     * 3. التحقق من الحقول المحسوبة تلقائياً (إجمالي قيمة الأسهم والعمر)
     */
    public function test_association_shares_total_value_and_age_computed_attributes(): void
    {
        $association = Association::create([
            'code' => 'ASS-020101-0001',
            'name' => 'جمعية الوادي الأخضر',
            'type' => 'زراعية',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
            'shares_count' => 200,
            'share_value' => 10000,
        ]);

        // إجمالي قيمة الأسهم = عدد الأسهم * قيمة السهم
        $this->assertEquals(2000000, $association->total_shares_value);

        // إضافة عضو مع تاريخ ميلاد وحساب العمر تلقائياً
        $member = AssociationBoardMember::create([
            'association_id' => $association->id,
            'full_name' => 'علي صالح ناصر',
            'role_title' => 'أمين الصندوق',
            'birth_date' => Carbon::now()->subYears(35)->format('Y-m-d'),
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
        ]);

        $this->assertEquals(35, $member->age);
    }

    /**
     * 4. فحص ملف الجمعية الشامل بالتبويبات الـ 9
     */
    public function test_association_360_profile_view_with_nine_tabs(): void
    {
        $association = Association::create([
            'code' => 'ASS-020101-0001',
            'name' => 'جمعية الحصاد التعاونية',
            'type' => 'زراعية متعددة الأغراض',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
        ]);

        $response = $this->actingAs($this->user)->get(route('associations.show', $association->id));

        $response->assertStatus(200);
        $response->assertSee('جمعية الحصاد التعاونية');
        $response->assertSee('الأساسية');
        $response->assertSee('القانونية');
        $response->assertSee('القيادة والإدارة');
        $response->assertSee('الأعضاء والمنتسبين');
        $response->assertSee('الاقتصادية والمالية');
        $response->assertSee('المشاريع والأنشطة');
        $response->assertSee('الأصول والممتلكات');
        $response->assertSee('القروض البيضاء');
        $response->assertSee('المتابعة والتقييم');
    }

    /**
     * 5. تأسيس مجموعة إنتاجية وتعيين ميسر وإظهار عدد المجاميع للميسر
     */
    public function test_productive_group_creation_and_facilitator_assignment(): void
    {
        $association = Association::create([
            'code' => 'ASS-020101-0001',
            'name' => 'جمعية بني بهلول',
            'type' => 'زراعية',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
        ]);

        $facilitator = Facilitator::create([
            'code' => 'FAC-001',
            'full_name' => 'يحيى عبد الله مرشد',
            'phone' => '771234567',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'status' => 'نشط',
        ]);

        $payload = [
            'name' => 'مجموعة منتجي البن اليافعي والصنعاني',
            'group_type' => 'مجموعة بن',
            'association_id' => $association->id,
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
            'total_members' => 25,
            'facilitator_id' => $facilitator->id,
        ];

        $response = $this->actingAs($this->user)->post(route('productive-groups.store'), $payload);

        $this->assertDatabaseHas('productive_groups', [
            'name' => 'مجموعة منتجي البن اليافعي والصنعاني',
            'association_id' => $association->id,
        ]);

        $group = ProductiveGroup::where('name', 'مجموعة منتجي البن اليافعي والصنعاني')->first();
        $this->assertNotNull($group);
        $this->assertStringStartsWith('GRP-020101-', $group->code);

        // التحقق من ربط الميسر بالمجموعة
        $this->assertTrue($group->facilitators->contains($facilitator));
        $this->assertEquals(1, $facilitator->productiveGroups()->count());

        // فحص عرض ملف المجموعة بالتبويبات الـ 5
        $viewResponse = $this->actingAs($this->user)->get(route('productive-groups.show', $group->id));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('البيانات التعريفية والتنظيمية');
        $viewResponse->assertSee('بيانات الميسّر المسؤول');
        $viewResponse->assertSee('قيادة المجموعة وأعضاؤها');
        $viewResponse->assertSee('النشاط والإنتاج والتسويق');
        $viewResponse->assertSee('المتابعة والتيسير الميداني');
    }

    /**
     * 6. تسجيل كادر في بنك الفرسان والكوادر الموحد (الـ 17 تخصص) وتوليد الكود
     */
    public function test_unified_personnel_registration_with_knight_specialty_and_auto_code(): void
    {
        $payload = [
            'category' => 'فرسان الجمعية',
            'sub_specialty' => 'فارس تنموي متطوع',
            'full_name' => 'حمير عبد الرحمن الوادعي',
            'gender' => 'ذكر',
            'phone' => '777987654',
            'education_level' => 'بكالوريوس زراعة',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'is_activated_in_field' => 1,
            'activation_entity' => 'مؤسسة بنيان التنموية',
        ];

        $response = $this->actingAs($this->user)->post(route('personnel.store'), $payload);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('unified_personnel', [
            'full_name' => 'حمير عبد الرحمن الوادعي',
            'sub_specialty' => 'فارس تنموي متطوع',
        ]);

        $person = UnifiedPersonnel::where('full_name', 'حمير عبد الرحمن الوادعي')->first();
        $this->assertNotNull($person);
        $this->assertStringStartsWith('KNT-', $person->code);
        $this->assertTrue($person->is_activated_in_field);
    }

    /**
     * التحقق من تحميل صفحة الكوادر والفرسان لجميع الفئات الفرعية بنجاح دون أي خطأ
     */
    public function test_personnel_index_renders_for_all_categories(): void
    {
        foreach (\App\Models\UnifiedPersonnel::CATEGORIES as $catKey => $catLabel) {
            $response = $this->actingAs($this->user)->get(route('personnel.index', ['category' => $catKey]));
            $response->assertOk();
            $response->assertSee($catKey);
        }
    }

    /**
     * 7. فحص القروض التمويلية البيضاء (0% فوائد) وحساب الرصيد المتبقي تلقائياً
     */
    public function test_white_loan_interest_free_and_auto_calculated_remaining_balance(): void
    {
        $association = Association::create([
            'code' => 'ASS-020101-0001',
            'name' => 'جمعية سد السائلة',
            'type' => 'زراعية',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
        ]);

        $loan = AssociationLoan::create([
            'code' => 'LOAN-00001',
            'funding_entity' => 'وحدة التدخلات المركزية التنموية',
            'beneficiary_name' => 'جمعية سد السائلة',
            'association_id' => $association->id,
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'loan_amount' => 10000000.00,
            'paid_amount' => 4000000.00,
            'loan_status' => 'ساري',
        ]);

        // الرصيد المتبقي = 10,000,000 - 4,000,000 = 6,000,000
        $this->assertEquals(6000000.00, $loan->remaining_balance);

        // عند سداد مبلغ إضافي
        $loan->paid_amount = 7500000.00;
        $loan->save();

        $this->assertEquals(2500000.00, $loan->fresh()->remaining_balance);
    }

    /**
     * 8. قاعدة الترابط التلقائي الشامل والانعكاس الفوري (Bi-directional Auto-Sync)
     * إضافة مشروع ومحصول ومزارع باسم جمعية ينعكس فوراً في ملف الجمعية
     */
    public function test_bi_directional_auto_sync_between_general_modules_and_association_profile(): void
    {
        $association = Association::create([
            'code' => 'ASS-020101-0001',
            'name' => 'جمعية التطوير الزراعي',
            'type' => 'زراعية',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
        ]);

        // 1. تسجيل مشروع عبر الواجهة العامة مرتبط بالجمعية
        AssociationProject::create([
            'project_name' => 'مشروع حفر كرفان حصاد مياه الأمطار',
            'project_type' => 'مائي زراعي',
            'execution_status' => 'قيد التنفيذ',
            'cost' => 3500000,
            'association_id' => $association->id,
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
        ]);

        // 2. تسجيل محصول زراعي عبر الواجهة العامة مرتبط بالجمعية
        AssociationCrop::create([
            'crop_type' => 'حبوب وبقوليات',
            'crop_variety' => 'قمح يمني صلب',
            'production_quantity' => 120,
            'quantity_unit' => 'طن',
            'availability_status' => 'متوفرة',
            'execution_status' => 'منفذ',
            'association_id' => $association->id,
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
        ]);

        // 3. تسجيل مزارع عبر السجل العام مرتبط بالجمعية
        FarmerAndLand::create([
            'farmer_name' => 'صالح محمد الحاوري',
            'phone' => '777000111',
            'total_land_area' => 15,
            'cultivated_area' => 12,
            'area_unit' => 'معاد',
            'main_crop_type' => 'قمح',
            'irrigation_source' => 'غيول وأمطار',
            'membership_status' => 'عضو مساهم',
            'association_id' => $association->id,
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
        ]);

        // فحص ملف الجمعية في show - يجب أن تظهر فيه هذه العناصر المضافة دون أي ربط يدوي إضافي
        $response = $this->actingAs($this->user)->get(route('associations.show', $association->id));
        $response->assertStatus(200);

        // التحقق من ظهور المشروع في التبويب السادس
        $response->assertSee('مشروع حفر كرفان حصاد مياه الأمطار');

        // التحقق من العلاقات على الكيان المركزي
        $this->assertEquals(1, $association->fresh()->projects()->count());
        $this->assertEquals(1, $association->fresh()->crops()->count());
        $this->assertEquals(1, $association->fresh()->farmers()->count());
    }

    /**
     * 9. مستكشف النطاق الجغرافي الشامل (360° Geo-Explorer)
     */
    public function test_geo_explorer_aggregates_data_across_governorate_and_directorate(): void
    {
        $association = Association::create([
            'code' => 'ASS-020101-0001',
            'name' => 'جمعية سنحان للتنمية',
            'type' => 'زراعية',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
            'uzlah_id' => $this->uzlah->id,
            'status' => 'نشطة',
            'current_capital' => 4000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('geo-explorer.index', [
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('مستكشف النطاق الجغرافي الشامل');
        $response->assertSee('صنعاء');
        $response->assertSee('سنحان وبني بهلول');
        $response->assertSee('جمعية سنحان للتنمية');
    }
}
