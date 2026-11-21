<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Models\User;
use App\Models\Uzlah;
use App\Traits\ScopeByGovernorate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GovernorateScopingTest extends TestCase
{
    use RefreshDatabase;

    protected Governorate $dhamar;
    protected Governorate $sanaa;
    protected Directorate $dhamarDir;
    protected Directorate $sanaaDir;
    protected Uzlah $dhamarUzl;
    protected Uzlah $sanaaUzl;

    protected User $superAdmin;
    protected User $dhamarAdmin;
    protected User $sanaaAdmin;
    protected User $unassignedAdmin;

    protected Association $dhamarAssoc;
    protected Association $sanaaAssoc;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Governorates
        $this->dhamar = Governorate::create([
            'code' => '05',
            'name_ar' => 'ذمار',
            'name' => 'Dhamar',
            'is_active' => true,
        ]);

        $this->sanaa = Governorate::create([
            'code' => '02',
            'name_ar' => 'صنعاء',
            'name' => 'Sanaa',
            'is_active' => true,
        ]);

        // 2. Create Directorates
        $this->dhamarDir = Directorate::create([
            'governorate_id' => $this->dhamar->id,
            'code' => '05-01',
            'name_ar' => 'مدينة ذمار',
            'name' => 'Dhamar City',
            'is_active' => true,
        ]);

        $this->sanaaDir = Directorate::create([
            'governorate_id' => $this->sanaa->id,
            'code' => '02-01',
            'name_ar' => 'سنحان',
            'name' => 'Sanhan',
            'is_active' => true,
        ]);

        // 3. Create Uzlahs
        $this->dhamarUzl = Uzlah::create([
            'governorate_id' => $this->dhamar->id,
            'directorate_id' => $this->dhamarDir->id,
            'code' => '05-01-01',
            'name_ar' => 'عنس',
            'name' => 'Anss',
            'is_active' => true,
        ]);

        $this->sanaaUzl = Uzlah::create([
            'governorate_id' => $this->sanaa->id,
            'directorate_id' => $this->sanaaDir->id,
            'code' => '02-01-01',
            'name_ar' => 'ريمة حميد',
            'name' => 'Rimat Humaid',
            'is_active' => true,
        ]);

        // 4. Create Users
        $this->superAdmin = User::factory()->create([
            'name' => 'مدير النظام المركزي',
            'email' => 'superadmin@hasad.local',
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        $this->dhamarAdmin = User::factory()->create([
            'name' => 'مدير محافظة ذمار',
            'email' => 'dhamar.admin@hasad.local',
            'role' => 'governorate_admin',
            'permissions' => [
                'dashboard.view',
                'associations.view',
                'associations.create',
                'associations.edit',
                'associations.delete',
                'productive_groups.view',
                'personnel.view',
                'general_db.view',
            ],
            'is_active' => true,
        ]);
        $this->dhamarAdmin->governorates()->attach($this->dhamar->id);

        $this->sanaaAdmin = User::factory()->create([
            'name' => 'مدير محافظة صنعاء',
            'email' => 'sanaa.admin@hasad.local',
            'role' => 'governorate_admin',
            'permissions' => [
                'dashboard.view',
                'associations.view',
                'associations.create',
                'associations.edit',
                'associations.delete',
                'productive_groups.view',
            ],
            'is_active' => true,
        ]);
        $this->sanaaAdmin->governorates()->attach($this->sanaa->id);

        $this->unassignedAdmin = User::factory()->create([
            'name' => 'مشرف بدون محافظة',
            'email' => 'unassigned@hasad.local',
            'role' => 'governorate_admin',
            'permissions' => ['dashboard.view', 'associations.view'],
            'is_active' => true,
        ]);

        // 5. Create Associations
        $this->dhamarAssoc = Association::create([
            'name' => 'جمعية ذمار التعاونية متعددة الأغراض',
            'type' => 'زراعية متعددة الأغراض',
            'status' => 'نشطة',
            'code' => 'DH-001',
            'governorate_id' => $this->dhamar->id,
            'directorate_id' => $this->dhamarDir->id,
            'uzlah_id' => $this->dhamarUzl->id,
            'current_capital' => 500000.00,
            'total_members' => 150,
        ]);

        $this->sanaaAssoc = Association::create([
            'name' => 'جمعية سنحان التعاونية الزراعية',
            'type' => 'زراعية متعددة الأغراض',
            'status' => 'نشطة',
            'code' => 'SN-001',
            'governorate_id' => $this->sanaa->id,
            'directorate_id' => $this->sanaaDir->id,
            'uzlah_id' => $this->sanaaUzl->id,
            'current_capital' => 800000.00,
            'total_members' => 300,
        ]);
    }

    /**
     * اختبار دوال فحص الأدوار وتحديد المحافظات المصرح بها في User Model
     */
    public function test_user_model_role_helpers_and_allowed_governorates(): void
    {
        // Super Admin
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->isAdmin());
        $this->assertNull($this->superAdmin->allowedGovernorateIds());
        $this->assertTrue($this->superAdmin->canAccessGovernorate($this->dhamar->id));
        $this->assertTrue($this->superAdmin->canAccessGovernorate($this->sanaa->id));

        // Dhamar Admin
        $this->assertFalse($this->dhamarAdmin->isSuperAdmin());
        $this->assertTrue($this->dhamarAdmin->isGovernorateAdmin());
        $this->assertEquals([$this->dhamar->id], $this->dhamarAdmin->allowedGovernorateIds());
        $this->assertTrue($this->dhamarAdmin->canAccessGovernorate($this->dhamar->id));
        $this->assertFalse($this->dhamarAdmin->canAccessGovernorate($this->sanaa->id));

        // Unassigned Admin
        $this->assertEquals([], $this->unassignedAdmin->allowedGovernorateIds());
        $this->assertFalse($this->unassignedAdmin->canAccessGovernorate($this->dhamar->id));
    }

    /**
     * اختبار نطاق الاستعلام ScopeByGovernorate مباشرة على مستوى الـ Eloquent Models
     */
    public function test_scope_by_governorate_trait_filters_eloquent_queries(): void
    {
        // مدير النظام المركزي يرى كل الجمعيات
        $superAdminCount = Association::forUserGovernorates($this->superAdmin)->count();
        $this->assertEquals(2, $superAdminCount);

        // مدير ذمار يرى فقط جمعية ذمار
        $dhamarCount = Association::forUserGovernorates($this->dhamarAdmin)->count();
        $this->assertEquals(1, $dhamarCount);
        $dhamarList = Association::forUserGovernorates($this->dhamarAdmin)->pluck('id')->all();
        $this->assertContains($this->dhamarAssoc->id, $dhamarList);
        $this->assertNotContains($this->sanaaAssoc->id, $dhamarList);

        // مستخدم بدون محافظات مخصصة يرى 0
        $unassignedCount = Association::forUserGovernorates($this->unassignedAdmin)->count();
        $this->assertEquals(0, $unassignedCount);
    }

    /**
     * اختبار دالة التخويل ScopeByGovernorate::authorize
     */
    public function test_scope_by_governorate_authorize_throws_403_on_unauthorized_governorate(): void
    {
        // مصرح لمدير ذمار الوصول لمحافظة ذمار (لا يرمي استثناء)
        ScopeByGovernorate::authorize($this->dhamar->id, $this->dhamarAdmin);
        $this->assertTrue(true);

        // غير مصرح لمدير ذمار الوصول لمحافظة صنعاء (يرمي 403)
        try {
            ScopeByGovernorate::authorize($this->sanaa->id, $this->dhamarAdmin);
            $this->fail('Expected HttpException with status 403 was not thrown.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /**
     * اختبار استعراض لوحة التحكم والمؤشرات الإحصائية بحسب النطاق الجغرافي
     */
    public function test_dashboard_respects_governorate_scoping(): void
    {
        // 1. مدير النظام المركزي يرى كافة البيانات
        $response = $this->actingAs($this->superAdmin)->get('/dashboard?fresh=1');
        $response->assertStatus(200);

        // 2. مدير ذمار يرى فقط بيانات ذمار
        $response = $this->actingAs($this->dhamarAdmin)->get('/dashboard?fresh=1');
        $response->assertStatus(200);
        $response->assertSee('جمعية ذمار التعاونية متعددة الأغراض');
        $response->assertDontSee('جمعية سنحان التعاونية الزراعية');
    }

    /**
     * اختبار صفحة قائمة الجمعيات (Associations Index) وتقييد البيانات
     */
    public function test_associations_index_scoped_by_governorate(): void
    {
        // 1. سوبر أدمن يرى كلتا الجمعيتين
        $response = $this->actingAs($this->superAdmin)->get(route('associations.index'));
        $response->assertStatus(200);
        $response->assertSee('جمعية ذمار التعاونية');
        $response->assertSee('جمعية سنحان التعاونية');

        // 2. مدير ذمار يرى فقط جمعية ذمار
        $response = $this->actingAs($this->dhamarAdmin)->get(route('associations.index'));
        $response->assertStatus(200);
        $response->assertSee('جمعية ذمار التعاونية');
        $response->assertDontSee('جمعية سنحان التعاونية');
    }

    /**
     * اختبار استعراض تفاصيل وتحديث جمعية تابعة للمحافظة المصرحة بنجاح
     */
    public function test_governorate_admin_can_view_and_update_own_governorate_association(): void
    {
        // استعراض تفاصيل جمعية ذمار من قبل مدير ذمار (200 OK)
        $response = $this->actingAs($this->dhamarAdmin)->get(route('associations.show', $this->dhamarAssoc->id));
        $response->assertStatus(200);
        $response->assertSee($this->dhamarAssoc->name);

        // تحديث بيانات جمعية ذمار من قبل مدير ذمار
        $response = $this->actingAs($this->dhamarAdmin)->put(route('associations.update', $this->dhamarAssoc->id), [
            'name' => 'جمعية ذمار التعاونية المحدثة',
            'type' => 'زراعية متعددة الأغراض',
            'status' => 'نشطة',
        ]);
        $response->assertStatus(302);
        $this->assertEquals('جمعية ذمار التعاونية المحدثة', $this->dhamarAssoc->fresh()->name);
    }

    /**
     * اختبار حظر مدير المحافظة من الوصول لجمعية محافظة أخرى برمز 403 Forbidden
     */
    public function test_governorate_admin_gets_403_when_accessing_other_governorate_association(): void
    {
        // محاولة استعراض تفاصيل جمعية صنعاء من قبل مدير ذمار
        $response = $this->actingAs($this->dhamarAdmin)->get(route('associations.show', $this->sanaaAssoc->id));
        $response->assertStatus(403);

        // محاولة إرسال تحديث لجمعية صنعاء
        $response = $this->actingAs($this->dhamarAdmin)->put(route('associations.update', $this->sanaaAssoc->id), [
            'name' => 'اسم معدل غير مصرح به',
            'type' => 'زراعية',
            'status' => 'نشطة',
        ]);
        $response->assertStatus(403);

        // محاولة حذف جمعية صنعاء
        $response = $this->actingAs($this->dhamarAdmin)->delete(route('associations.destroy', $this->sanaaAssoc->id));
        $response->assertStatus(403);
    }

    /**
     * اختبار التقييد الجغرافي عند إنشاء سجل جديد (منع التأسيس بمحافظة غير مصرحة)
     */
    public function test_governorate_admin_cannot_create_association_in_unauthorized_governorate(): void
    {
        // محاولة إنشاء جمعية في صنعاء من قِبل مدير ذمار
        $response = $this->actingAs($this->dhamarAdmin)->post(route('associations.store'), [
            'name' => 'جمعية غير مصرح بها في صنعاء',
            'type' => 'زراعية',
            'governorate_id' => $this->sanaa->id,
            'directorate_id' => $this->sanaaDir->id,
            'uzlah_id' => $this->sanaaUzl->id,
            'status' => 'نشطة',
        ]);

        $response->assertStatus(403);
    }

    /**
     * اختبار مساعد حصاد الذكي Copilot API واحترامه للتقييد الجغرافي
     */
    public function test_copilot_api_endpoint_respects_governorate_scoping(): void
    {
        // 1. فحص استجابة Super Admin للملخص العام
        $superResponse = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'الملخص الوطني العام للنظام'
        ]);
        $superResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'overview');

        // يجب أن يرى السوبر أدمن جمعيات المحافظتين (2 جمعية)
        $superSummary = $superResponse->json('data.summary');
        $this->assertStringContainsString('2 جمعية', $superSummary);

        // 2. فحص استجابة مدير ذمار للاستفسار عن محافظته
        $dhamarResponse = $this->actingAs($this->dhamarAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'تحليل محافظة ذمار'
        ]);
        $dhamarResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'governorate')
            ->assertJsonPath('data.title', 'التقرير التحليلي الذكي لمحافظة ذمار');

        // 3. فحص استجابة مدير ذمار عند محاولة الاستعلام عن محافظة غير مصرحة (صنعاء)
        $restrictedResponse = $this->actingAs($this->dhamarAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'تحليل محافظة صنعاء'
        ]);
        $restrictedResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'restricted');
        $this->assertStringContainsString('لا تملك صلاحية الاطلاع على بيانات محافظة صنعاء', $restrictedResponse->json('data.summary'));
        $this->assertStringContainsString('ذمار', $restrictedResponse->json('data.summary'));
    }
}
