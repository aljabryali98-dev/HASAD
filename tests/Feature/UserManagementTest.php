<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $supervisor;
    protected $restrictedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'مدير النظام المركزي',
            'email' => 'admin@hasad.local',
            'role' => 'admin',
            'permissions' => ['*'],
            'is_active' => true,
        ]);

        $this->supervisor = User::factory()->create([
            'name' => 'مشرف ميداني',
            'email' => 'supervisor@hasad.local',
            'role' => 'supervisor',
            'permissions' => ['associations.view', 'productive_groups.view', 'personnel.view'],
            'is_active' => true,
        ]);

        $this->restrictedUser = User::factory()->create([
            'name' => 'مستخدم مقيد',
            'email' => 'restricted@hasad.local',
            'role' => 'custom',
            'permissions' => ['associations.view'],
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_users_list()
    {
        $response = $this->actingAs($this->admin)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('إدارة المستخدمين والصلاحيات');
        $response->assertSee('admin@hasad.local');
        $response->assertSee('supervisor@hasad.local');
    }

    public function test_admin_can_view_create_user_page()
    {
        $response = $this->actingAs($this->admin)->get('/users/create');
        $response->assertStatus(200);
        $response->assertSee('إضافة مستخدم جديد');
        $response->assertSee('مصفوفة الصلاحيات');
        $response->assertSee('associations.view');
        $response->assertSee('import.execute');
    }

    public function test_admin_can_create_new_user_with_custom_permissions()
    {
        $userData = [
            'name' => 'مهندس زراعي جديد',
            'email' => 'agri.engineer@hasad.local',
            'password' => 'secret1234',
            'phone' => '777123456',
            'role' => 'custom',
            'is_active' => '1',
            'permissions' => ['crops.view', 'crops.manage', 'nurseries.view'],
        ];

        $response = $this->actingAs($this->admin)->post('/users', $userData);
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'agri.engineer@hasad.local',
            'role' => 'custom',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'agri.engineer@hasad.local')->first();
        $this->assertTrue($newUser->hasPermission('crops.view'));
        $this->assertTrue($newUser->hasPermission('crops.manage'));
        $this->assertFalse($newUser->hasPermission('users.view'));
        $this->assertFalse($newUser->hasPermission('associations.delete'));
    }

    public function test_admin_can_update_user_and_permissions()
    {
        $response = $this->actingAs($this->admin)->put("/users/{$this->restrictedUser->id}", [
            'name' => 'مستخدم بعد الترقية',
            'email' => 'restricted@hasad.local',
            'phone' => '777888999',
            'role' => 'custom',
            'is_active' => '1',
            'permissions' => ['associations.view', 'associations.create', 'personnel.view'],
        ]);

        $response->assertRedirect('/users');
        $this->restrictedUser->refresh();

        $this->assertEquals('مستخدم بعد الترقية', $this->restrictedUser->name);
        $this->assertTrue($this->restrictedUser->hasPermission('associations.create'));
        $this->assertTrue($this->restrictedUser->hasPermission('personnel.view'));
        $this->assertFalse($this->restrictedUser->hasPermission('users.view'));
    }

    public function test_admin_can_toggle_user_active_status()
    {
        $response = $this->actingAs($this->admin)->post("/users/{$this->restrictedUser->id}/toggle-status");
        $response->assertSessionHas('success');

        $this->restrictedUser->refresh();
        $this->assertFalse($this->restrictedUser->is_active);
    }

    public function test_user_cannot_deactivate_or_delete_themselves()
    {
        // محاولة تعطيل الحساب الخاص
        $response = $this->actingAs($this->admin)->post("/users/{$this->admin->id}/toggle-status");
        $response->assertSessionHas('error');

        // محاولة حذف الحساب الخاص
        $deleteResponse = $this->actingAs($this->admin)->delete("/users/{$this->admin->id}");
        $deleteResponse->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_restricted_user_cannot_access_unauthorized_routes()
    {
        // لديه فقط associations.view
        $allowedResponse = $this->actingAs($this->restrictedUser)->get('/associations');
        $allowedResponse->assertStatus(200);

        // محاولة الدخول لصفحة المستخدمين (ليس لديه users.view)
        $blockedUsers = $this->actingAs($this->restrictedUser)->get('/users');
        $blockedUsers->assertRedirect(route('dashboard'));
        $blockedUsers->assertSessionHas('error');

        // محاولة الدخول للاستيراد الذكي (ليس لديه import.view)
        $blockedImport = $this->actingAs($this->restrictedUser)->get('/import/smart');
        $blockedImport->assertRedirect(route('dashboard'));
    }

    public function test_deactivated_user_is_logged_out_automatically()
    {
        $this->restrictedUser->update(['is_active' => false]);

        $response = $this->actingAs($this->restrictedUser)->get('/associations');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_sidebar_hides_unauthorized_menu_items()
    {
        // مستخدم لديه فقط associations.view
        $response = $this->actingAs($this->restrictedUser)->get('/associations');
        $response->assertStatus(200);

        // يرى الجمعيات
        $response->assertSee('الجمعيات التعاونية');

        // لا يرى إدارة المستخدمين
        $response->assertDontSee('إدارة المستخدمين والصلاحيات');

        // لا يرى المجاميع الإنتاجية في القائمة الجانبية
        $response->assertDontSee('المجاميع الإنتاجية');
    }
}
