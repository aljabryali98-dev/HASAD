<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@hasad.local',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_smart_import_page_requires_authentication(): void
    {
        $response = $this->get('/import/smart');
        $response->assertRedirect('/login');
    }

    public function test_smart_import_page_renders_for_authenticated_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/import/smart');

        $response->assertStatus(200);
        $response->assertSee('الاستيراد الذكي الشامل من ملفات الإكسل');
        $response->assertSee('Smart Import 2.0');
        $response->assertSee('رفع واستيراد ملف Excel أو CSV');
        $response->assertSee('التسكين الآلي للملف المركزي');
    }
}
