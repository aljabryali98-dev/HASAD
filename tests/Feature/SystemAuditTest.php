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
}
