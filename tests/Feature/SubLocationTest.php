<?php

namespace Tests\Feature;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubLocation;
use App\Models\User;
use App\Models\Uzlah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubLocationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Governorate $governorate;
    private Directorate $directorate;
    private Uzlah $uzlah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

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

    public function test_can_generate_automatic_unique_code_for_sub_location(): void
    {
        $code1 = SubLocation::generateCode($this->uzlah->id);
        $this->assertEquals('020101-0001', $code1);

        SubLocation::create([
            'uzlah_id' => $this->uzlah->id,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'code' => $code1,
            'name_ar' => 'قرية النصر',
            'entity_type' => 'قرية',
            'is_active' => 1,
        ]);

        $code2 = SubLocation::generateCode($this->uzlah->id);
        $this->assertEquals('020101-0002', $code2);
    }

    public function test_api_locations_hierarchy_returns_proper_structure(): void
    {
        SubLocation::create([
            'uzlah_id' => $this->uzlah->id,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'code' => '020101-0001',
            'name_ar' => 'قرية الأمل',
            'entity_type' => 'قرية',
            'is_active' => 1,
        ]);

        $response = $this->getJson('/api/locations/hierarchy?uzlah_id=' . $this->uzlah->id);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name_ar', 'سنحان')
            ->assertJsonPath('data.sub_locations.0.name_ar', 'قرية الأمل');
    }

    public function test_api_can_create_sub_location_dynamically(): void
    {
        $payload = [
            'uzlah_id' => $this->uzlah->id,
            'name_ar' => 'جمعية سنحان التعاونية الزراعية',
            'entity_type' => 'جمعية تعاونية',
            'notes' => 'جمعية نشطة نموذجية',
        ];

        $response = $this->postJson('/api/locations/sub-locations', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name_ar', 'جمعية سنحان التعاونية الزراعية')
            ->assertJsonPath('data.entity_type', 'جمعية تعاونية')
            ->assertJsonPath('data.code', '020101-0001');

        $this->assertDatabaseHas('sub_locations', [
            'uzlah_id' => $this->uzlah->id,
            'name_ar' => 'جمعية سنحان التعاونية الزراعية',
            'entity_type' => 'جمعية تعاونية',
            'code' => '020101-0001',
        ]);
    }

    public function test_api_prevents_duplicate_sub_location_under_same_uzlah_and_type(): void
    {
        SubLocation::create([
            'uzlah_id' => $this->uzlah->id,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'code' => '020101-0001',
            'name_ar' => 'قرية السلام',
            'entity_type' => 'قرية',
            'is_active' => 1,
        ]);

        $payload = [
            'uzlah_id' => $this->uzlah->id,
            'name_ar' => 'قرية السلام',
            'entity_type' => 'قرية',
        ];

        $response = $this->postJson('/api/locations/sub-locations', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'exists');

        $this->assertEquals(1, SubLocation::where('name_ar', 'قرية السلام')->count());
    }

    public function test_api_can_update_sub_location(): void
    {
        $sub = SubLocation::create([
            'uzlah_id' => $this->uzlah->id,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'code' => '020101-0001',
            'name_ar' => 'قرية السلام',
            'entity_type' => 'قرية',
            'is_active' => 1,
        ]);

        $response = $this->putJson("/api/locations/sub-locations/{$sub->id}", [
            'name_ar' => 'قرية السلام الجديدة',
            'entity_type' => 'محلة',
            'notes' => 'تم التحديث بنجاح',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name_ar', 'قرية السلام الجديدة')
            ->assertJsonPath('data.entity_type', 'محلة');

        $this->assertDatabaseHas('sub_locations', [
            'id' => $sub->id,
            'name_ar' => 'قرية السلام الجديدة',
            'entity_type' => 'محلة',
        ]);
    }

    public function test_api_can_delete_sub_location(): void
    {
        $sub = SubLocation::create([
            'uzlah_id' => $this->uzlah->id,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'code' => '020101-0001',
            'name_ar' => 'قرية السلام',
            'entity_type' => 'قرية',
            'is_active' => 1,
        ]);

        $response = $this->deleteJson("/api/locations/sub-locations/{$sub->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('sub_locations', [
            'id' => $sub->id,
        ]);
    }

    public function test_web_manage_screen_renders_properly(): void
    {
        $response = $this->actingAs($this->user)->get(route('geography.manage'));

        $response->assertStatus(200)
            ->assertSee('إدارة النطاق الجغرافي والجمعيات والقرى')
            ->assertSee('المستوى الرابع (قرى وجمعيات)');
    }

    public function test_bulk_sub_locations_addition(): void
    {
        $response = $this->actingAs($this->user)->post(route('geography.sub_locations.bulk'), [
            'uzlah_id' => $this->uzlah->id,
            'entity_type' => 'قرية',
            'names_list' => "قرية النور\nقرية الإيمان\nقرية التقوى",
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('sub_locations', ['name_ar' => 'قرية النور', 'uzlah_id' => $this->uzlah->id]);
        $this->assertDatabaseHas('sub_locations', ['name_ar' => 'قرية الإيمان', 'uzlah_id' => $this->uzlah->id]);
        $this->assertDatabaseHas('sub_locations', ['name_ar' => 'قرية التقوى', 'uzlah_id' => $this->uzlah->id]);
    }
}
