<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\AssociationLoan;
use App\Models\CommunityInitiative;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\Nursery;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Models\User;
use App\Models\Uzlah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopilotTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $dhamarAdmin;
    protected Governorate $govDhamar;
    protected Governorate $govSanaa;
    protected Directorate $dirDhamar;
    protected Directorate $dirSanaa;
    protected Uzlah $uzlahDhamar;
    protected Uzlah $uzlahSanaa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->govDhamar = Governorate::create([
            'code' => 'YE-DH',
            'name_ar' => 'ذمار',
            'name' => 'Dhamar',
            'is_active' => true,
        ]);

        $this->govSanaa = Governorate::create([
            'code' => 'YE-SN',
            'name_ar' => 'صنعاء',
            'name' => 'Sanaa',
            'is_active' => true,
        ]);

        $this->dirDhamar = Directorate::create([
            'governorate_id' => $this->govDhamar->id,
            'code' => 'DIR-DH-01',
            'name_ar' => 'مدينة ذمار',
            'name' => 'Dhamar City',
            'is_active' => true,
        ]);

        $this->dirSanaa = Directorate::create([
            'governorate_id' => $this->govSanaa->id,
            'code' => 'DIR-SN-01',
            'name_ar' => 'بني حشيش',
            'name' => 'Bani Hushaysh',
            'is_active' => true,
        ]);

        $this->uzlahDhamar = Uzlah::create([
            'governorate_id' => $this->govDhamar->id,
            'directorate_id' => $this->dirDhamar->id,
            'code' => 'UZ-DH-01',
            'name_ar' => 'عزلة ذمار',
            'name' => 'Dhamar Uzlah',
            'is_active' => true,
        ]);

        $this->uzlahSanaa = Uzlah::create([
            'governorate_id' => $this->govSanaa->id,
            'directorate_id' => $this->dirSanaa->id,
            'code' => 'UZ-SN-01',
            'name_ar' => 'عزلة بني حشيش',
            'name' => 'Bani Hushaysh Uzlah',
            'is_active' => true,
        ]);

        // Super Admin (sees all Yemen)
        $this->superAdmin = User::factory()->create([
            'name' => 'المدير العام',
            'email' => 'admin@hasad.local',
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        // Governorate Admin (scoped strictly to Dhamar)
        $this->dhamarAdmin = User::factory()->create([
            'name' => 'مدير ذمار',
            'email' => 'dhamar@hasad.local',
            'role' => 'governorate_admin',
            'permissions' => ['dashboard.view', 'associations.view', 'crops.view'],
            'is_active' => true,
        ]);
        $this->dhamarAdmin->governorates()->attach($this->govDhamar->id);

        // Seed some data across both governorates
        Association::create([
            'governorate_id' => $this->govDhamar->id,
            'directorate_id' => $this->dirDhamar->id,
            'uzlah_id' => $this->uzlahDhamar->id,
            'name' => 'جمعية ذمار الزراعية',
            'code' => 'DH-ASC-01',
            'status' => 'نشطة',
            'current_capital' => 1500000,
            'total_members' => 120,
        ]);

        Association::create([
            'governorate_id' => $this->govSanaa->id,
            'directorate_id' => $this->dirSanaa->id,
            'uzlah_id' => $this->uzlahSanaa->id,
            'name' => 'جمعية صنعاء التعاونية',
            'code' => 'SN-ASC-01',
            'status' => 'نشطة',
            'current_capital' => 3000000,
            'total_members' => 250,
        ]);

        Nursery::create([
            'governorate_id' => $this->govDhamar->id,
            'directorate_id' => $this->dirDhamar->id,
            'nursery_name' => 'مشتل قاع جهران المركزي',
            'ownership_type' => 'تعاوني',
            'capacity' => 50000,
            'status' => 'مفعل',
        ]);

        Nursery::create([
            'governorate_id' => $this->govSanaa->id,
            'directorate_id' => $this->dirSanaa->id,
            'nursery_name' => 'مشتل بني حشيش',
            'ownership_type' => 'تعاوني',
            'capacity' => 100000,
            'status' => 'مفعل',
        ]);

        ProductiveGroup::create([
            'governorate_id' => $this->govDhamar->id,
            'directorate_id' => $this->dirDhamar->id,
            'uzlah_id' => $this->uzlahDhamar->id,
            'name' => 'مجموعة ذمار لإنتاج الثوم',
            'code' => 'PG-DH-01',
            'status' => 'نشطة',
            'total_members' => 15,
        ]);
    }

    public function test_copilot_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/copilot/query', [
            'prompt' => 'الملخص العام'
        ]);

        $response->assertStatus(401);
    }

    public function test_copilot_validates_empty_prompt(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_super_admin_gets_unrestricted_national_overview(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'الملخص الوطني العام للنظام'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'overview');

        // Super admin sees both associations (1 in Dhamar + 1 in Sanaa = 2)
        $summary = $response->json('data.summary');
        $this->assertStringContainsString('2 جمعية', $summary);
        $this->assertStringContainsString('22 محافظة', $summary);
    }

    public function test_super_admin_can_query_specific_governorates(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'تحليل محافظة ذمار'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'governorate')
            ->assertJsonPath('data.title', 'التقرير التحليلي الذكي لمحافظة ذمار');

        $this->assertStringContainsString('1 جمعية', $response->json('data.summary'));
    }

    public function test_scoped_user_can_access_authorized_governorate(): void
    {
        $response = $this->actingAs($this->dhamarAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'تحليل محافظة ذمار'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'governorate')
            ->assertJsonPath('data.title', 'التقرير التحليلي الذكي لمحافظة ذمار');
    }

    public function test_scoped_user_is_blocked_from_unauthorized_governorate(): void
    {
        // Dhamar admin asking about Sana'a
        $response = $this->actingAs($this->dhamarAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'تحليل محافظة صنعاء'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'restricted');

        $summary = $response->json('data.summary');
        $this->assertStringContainsString('لا تملك صلاحية الاطلاع على بيانات محافظة صنعاء', $summary);
        $this->assertStringContainsString('ذمار', $summary);
    }

    public function test_scoped_user_queries_nurseries_restricted_to_their_governorate(): void
    {
        // Dhamar admin queries nurseries
        $response = $this->actingAs($this->dhamarAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'إحصائيات المشاتل الزراعية وطاقتها'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'nurseries');

        // Dhamar has 1 nursery (50,000 capacity). Sana'a has 1 nursery (100,000 capacity).
        // Scoped user MUST only see 1 nursery with 50,000 capacity!
        $kpis = collect($response->json('data.kpis'))->keyBy('label');
        $this->assertEquals('1 مشتل', $kpis->get('إجمالي المشاتل')['value']);
        $this->assertEquals('50,000 شتلة', $kpis->get('الطاقة الإنتاجية')['value']);
    }

    public function test_super_admin_queries_nurseries_nationally(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'إحصائيات المشاتل'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'nurseries');

        // Super Admin sees both nurseries (1 + 1 = 2) with total 150,000 capacity
        $kpis = collect($response->json('data.kpis'))->keyBy('label');
        $this->assertEquals('2 مشتل', $kpis->get('إجمالي المشاتل')['value']);
        $this->assertEquals('150,000 شتلة', $kpis->get('الطاقة الإنتاجية')['value']);
    }

    public function test_scoped_user_without_assigned_governorates_receives_warning(): void
    {
        $unassignedUser = User::factory()->create([
            'role' => 'governorate_admin',
            'permissions' => ['dashboard.view'],
            'is_active' => true,
        ]);
        // No governorates attached!

        $response = $this->actingAs($unassignedUser)->postJson('/api/copilot/query', [
            'prompt' => 'الملخص العام'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'تنبيه: لا توجد محافظات مخصصة لحسابك');
    }

    public function test_copilot_handles_arabic_normalization_and_typos(): void
    {
        // "محافظه ذمار" with haa instead of taa marbuta
        $response = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'محافظه ذمار'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.type', 'governorate')
            ->assertJsonPath('data.title', 'التقرير التحليلي الذكي لمحافظة ذمار');
    }

    public function test_copilot_direct_and_api_routes_both_work_seamlessly(): void
    {
        // اختبار المسار المباشر بدون بادئة api لضمان التوافق مع بيئة Vercel Serverless
        $response1 = $this->actingAs($this->superAdmin)->postJson('/copilot/query', [
            'prompt' => 'الملخص الوطني العام للنظام'
        ]);
        $response1->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'overview');

        // اختبار المسار التقليدي ببادئة api
        $response2 = $this->actingAs($this->superAdmin)->postJson('/api/copilot/query', [
            'prompt' => 'الملخص الوطني العام للنظام'
        ]);
        $response2->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.type', 'overview');
    }
}
