<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_manifest_json_is_valid_and_accessible()
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $content = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($content);
        $this->assertEquals('نظام حصاد - المنظومة الوطنية للرصد والتقييم', $content['name']);
        $this->assertEquals('standalone', $content['display']);
        $this->assertEquals('/', $content['start_url']);
        $this->assertNotEmpty($content['icons']);
    }

    public function test_service_worker_file_exists_and_is_valid()
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString('hasad-pwa', $content);
        $this->assertStringContainsString('install', $content);
        $this->assertStringContainsString('fetch', $content);
    }

    public function test_offline_page_exists()
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('غير متصل بالإنترنت', $content);
    }

    public function test_pwa_icons_exist()
    {
        $icon192 = public_path('images/icons/icon-192x192.png');
        $icon512 = public_path('images/icons/icon-512x512.png');
        $appleIcon = public_path('images/icons/apple-touch-icon.png');

        $this->assertFileExists($icon192);
        $this->assertFileExists($icon512);
        $this->assertFileExists($appleIcon);
    }

    public function test_pwa_install_page_renders_cleanly()
    {
        $response = $this->get('/app/install');
        $response->assertStatus(200);
        $response->assertSee('تثبيت تطبيق حصاد ERP');
        $response->assertSee('أندرويد');
        $response->assertSee('آيفون وآيباد');
        $response->assertSee('الكمبيوتر واللابتوب');
    }

    public function test_login_page_includes_pwa_capabilities()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('manifest.json');
        $response->assertSee('apple-mobile-web-app-capable');
        $response->assertSee('pwa-install.js');
        $response->assertSee('hasadPwaInstallModal');
    }

    public function test_dashboard_layout_includes_pwa_capabilities()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('manifest.json');
        $response->assertSee('pwa-install.js');
        $response->assertSee('hasadPwaInstallModal');
        $response->assertSee('btnInstallAppHeader');
    }

    public function test_mobile_responsiveness_and_navigation_drawer_are_configured()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('hasad-mobile-bottom-nav');
        $response->assertSee('mobileBottomMenuBtn');
        $response->assertSee('sidebarToggleBtn');
        $response->assertSee('sidebarCloseBtn');
        $response->assertSee('appSidebar');
        $response->assertSee('sidebar-mobile-user-card');
    }
}
