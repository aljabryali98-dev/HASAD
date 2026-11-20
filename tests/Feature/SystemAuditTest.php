<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Governorate;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
        ]);
    }

    public function test_all_core_pages_render_successfully(): void
    {
        $routes = [
            '/',
            '/dashboard',
            '/associations',
            '/associations/create',
            '/productive-groups',
            '/productive-groups/create',
            '/personnel',
            '/personnel/create',
            '/geo-explorer',
            '/geography',
            '/geography/manage',
            '/import/smart',
            '/users',
            '/users/create',
            '/general/projects',
            '/general/crops',
            '/general/loans',
            '/general/farmers',
            '/general/nurseries',
            '/general/awareness',
            '/general/initiatives',
            '/app/install',
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302]),
                "Route [{$url}] failed with HTTP status {$response->getStatusCode()}"
            );
        }
    }

    public function test_associations_create_renders_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/associations/create');
        $response->assertStatus(200);
    }

    public function test_productive_groups_create_redirects_to_modal(): void
    {
        $response = $this->actingAs($this->admin)->get('/productive-groups/create');
        $response->assertRedirect(route('productive-groups.index', ['open_create' => 1]));
    }

    public function test_personnel_create_redirects_to_modal(): void
    {
        $response = $this->actingAs($this->admin)->get('/personnel/create');
        $response->assertRedirect(route('personnel.index', ['open_create' => 1]));
    }

    public function test_association_shareholder_and_personnel_management(): void
    {
        $gov = Governorate::create([
            'code' => 'YE-DH',
            'name_ar' => 'ذمار',
            'name' => 'Dhamar',
        ]);

        $dir = \App\Models\Directorate::create([
            'governorate_id' => $gov->id,
            'code' => 'DIR-01',
            'name_ar' => 'عنس',
            'name' => 'Anas',
        ]);

        $uzl = \App\Models\Uzlah::create([
            'governorate_id' => $gov->id,
            'directorate_id' => $dir->id,
            'code' => 'UZL-01',
            'name_ar' => 'العنسيين',
            'name' => 'Al-Ansiyyin',
        ]);

        $assoc = Association::create([
            'name' => 'جمعية رداع التعاونية الزراعية',
            'type' => 'زراعية متعددة الأغراض',
            'status' => 'نشطة',
            'code' => 'RAD-001',
            'governorate_id' => $gov->id,
            'directorate_id' => $dir->id,
            'uzlah_id' => $uzl->id,
            'share_value' => 2000.00,
            'current_capital' => 100000.00,
            'total_members' => 50,
        ]);

        // 1. تسجيل مساهم جديد
        $postData = [
            'full_name' => 'أحمد علي القاسمي',
            'national_id' => '01010099887',
            'phone' => '771234567',
            'gender' => 'ذكر',
            'shares_count' => 5,
            'share_value' => 2000.00,
            'paid_amount' => 10000.00,
            'join_date' => '2026-11-21',
            'receipt_number' => 'REC-TEST-001',
            'status' => 'نشط',
            'notes' => 'مساهم مؤسس',
        ];

        $response = $this->actingAs($this->admin)->post("/associations/{$assoc->id}/shareholders", $postData);
        $response->assertRedirect(route('associations.show', ['association' => $assoc->id, '#tab-members']));
        $this->assertDatabaseHas('association_shareholders', [
            'association_id' => $assoc->id,
            'full_name' => 'أحمد علي القاسمي',
            'shares_count' => 5,
            'paid_amount' => 10000.00,
        ]);

        $assoc->refresh();
        $this->assertEquals(51, $assoc->total_members);
        $this->assertEquals(110000.00, (float)$assoc->current_capital);

        // 2. استعراض سند المساهمة
        $shareholder = $assoc->shareholders()->first();
        $this->assertNotNull($shareholder);
        $receiptResponse = $this->actingAs($this->admin)->get("/associations/{$assoc->id}/shareholders/{$shareholder->id}/receipt");
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('أحمد علي القاسمي');
        $receiptResponse->assertSee('سند قبض واكتتاب');

        // 3. إضافة كادر للجمعية
        $personnelData = [
            'full_name' => 'صالح محمد التنموي',
            'category' => 'فرسان الجمعية',
            'sub_specialty' => 'فرسان التنمية',
            'phone' => '777888999',
            'gender' => 'ذكر',
            'is_activated_in_field' => '1',
        ];

        $pResponse = $this->actingAs($this->admin)->post("/associations/{$assoc->id}/personnel", $personnelData);
        $pResponse->assertRedirect(route('associations.show', ['association' => $assoc->id, '#tab-personnel']));
        $this->assertDatabaseHas('unified_personnel', [
            'association_id' => $assoc->id,
            'full_name' => 'صالح محمد التنموي',
        ]);

        // 4. استعراض الملف الشامل للطباعة
        $printResponse = $this->actingAs($this->admin)->get("/associations/{$assoc->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('الملف التعريفي والتشغيلي الشامل');
    }

    public function test_copilot_ai_api_insights(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/copilot/query', [
            'prompt' => 'ما هو وضع الجمعيات التعاونية؟'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'type',
                    'title',
                    'summary',
                    'kpis',
                    'recommendations',
                    'action_link',
                    'action_label',
                ]
            ]);
    }

    public function test_geo_explorer_print_dossier(): void
    {
        $gov = Governorate::first() ?? Governorate::create([
            'code' => 'YE-SN',
            'name_ar' => 'صنعاء',
            'name' => 'Sanaa',
        ]);

        $response = $this->actingAs($this->admin)->get("/geo-explorer/print?gov_id={$gov->id}");
        $response->assertStatus(200);
        $response->assertSee('كشف وحصر الأنشطة التنموية والزراعية');
    }
}
