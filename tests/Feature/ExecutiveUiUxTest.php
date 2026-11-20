<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveUiUxTest extends TestCase
{
    use RefreshDatabase;

    private \App\Models\Governorate $gov;
    private \App\Models\Directorate $dir;
    private \App\Models\Uzlah $uzl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gov = \App\Models\Governorate::create([
            'id' => 1,
            'name_ar' => 'أمانة العاصمة',
            'code' => '01',
            'is_active' => true,
        ]);
        $this->dir = \App\Models\Directorate::create([
            'id' => 101,
            'governorate_id' => $this->gov->id,
            'name_ar' => 'مديرية التحرير',
            'code' => '0101',
            'is_active' => true,
        ]);
        $this->uzl = \App\Models\Uzlah::create([
            'id' => 10101,
            'governorate_id' => $this->gov->id,
            'directorate_id' => $this->dir->id,
            'name_ar' => 'عزلة التحرير',
            'code' => '010101',
            'is_active' => true,
        ]);
    }

    protected function getAuthenticatedUser(): User
    {
        return User::first() ?? User::factory()->create([
            'email' => 'admin@hasad.local',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_login_page_renders_with_security_and_password_toggle(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول - نظام حصاد المركزي');
        $response->assertSee('btnTogglePassword');
        $response->assertSee('btnFillDemo');
        $response->assertSee('256-Bit TLS');
    }

    public function test_dashboard_renders_with_executive_kpis_and_charts(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('لوحة الإحصائيات والمتابعة المركزية');
        $response->assertSee('chartLoansDonut');
        $response->assertSee('chartPersonnelBar');
        $response->assertSee('btnToggleDetailedView');
        $response->assertSee('ApexCharts');
        $response->assertSee('hasadToastContainer');
        $response->assertSee('themeToggleBtn');
        $response->assertSee('commandPaletteBackdrop');
        $response->assertSee('networkStatusPill');
        $response->assertSee('hasad-print-header');
    }

    public function test_associations_index_renders_with_collapsible_filter(): void
    {
        $user = $this->getAuthenticatedUser();

        \App\Models\Association::create([
            'governorate_id' => $this->gov->id,
            'directorate_id' => $this->dir->id,
            'uzlah_id' => $this->uzl->id,
            'code' => 'ASC-001',
            'name' => 'جمعية صنعاء المركزية',
            'status' => 'نشطة',
            'type' => 'زراعية',
            'current_capital' => 500000,
        ]);

        $response = $this->actingAs($user)->get(route('associations.index'));

        $response->assertStatus(200);
        $response->assertSee('filterFormCollapse');
        $response->assertSee('copyable-badge');
        $response->assertSee('assocCompareDrawer');
    }

    public function test_productive_groups_renders_with_collapsible_filter(): void
    {
        $user = $this->getAuthenticatedUser();

        \App\Models\ProductiveGroup::create([
            'governorate_id' => $this->gov->id,
            'directorate_id' => $this->dir->id,
            'uzlah_id' => $this->uzl->id,
            'code' => 'GRP-001',
            'name' => 'مجموعة بركة الأرض',
            'status' => 'نشطة',
            'group_type' => 'زراعية',
            'total_members' => 15,
        ]);

        $response = $this->actingAs($user)->get(route('productive-groups.index'));

        $response->assertStatus(200);
        $response->assertSee('filterGroupCollapse');
        $response->assertSee('copyable-badge');
    }

    public function test_personnel_renders_with_copyable_badges(): void
    {
        $user = $this->getAuthenticatedUser();

        \App\Models\UnifiedPersonnel::create([
            'governorate_id' => $this->gov->id,
            'directorate_id' => $this->dir->id,
            'uzlah_id' => $this->uzl->id,
            'code' => 'KNG-001',
            'full_name' => 'علي عبدالله اليماني',
            'category' => 'فرسان الجمعية',
            'phone' => '777123456',
            'is_activated_in_field' => true,
        ]);

        $response = $this->actingAs($user)->get(route('personnel.index'));

        $response->assertStatus(200);
        $response->assertSee('copyable-badge');
    }

    public function test_glassmorphism_aurora_mesh_and_lucide_integration(): void
    {
        $user = $this->getAuthenticatedUser();

        // 1. اختبار شاشة تسجيل الدخول
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('aurora-bg-mesh');
        $loginRes->assertSee('spotlight-card');
        $loginRes->assertSee('lucide@latest');

        // 2. اختبار لوحة المؤشرات
        $dashRes = $this->actingAs($user)->get(route('dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee('aurora-bg-mesh');
        $dashRes->assertSee('spotlight-card');
        $dashRes->assertSee('data-lucide');
        $dashRes->assertSee('lucide@latest');
        $dashRes->assertSee('stat-num');
    }
}
