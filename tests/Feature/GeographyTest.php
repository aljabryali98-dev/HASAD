<?php

namespace Tests\Feature;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\User;
use App\Models\Uzlah;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeographyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Governorate $governorate;
    private Directorate $directorate;
    private Uzlah $uzlah;
    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->governorate = Governorate::create(['name_ar' => 'صنعاء', 'code' => '02', 'is_active' => true]);
        $this->directorate = Directorate::create([
            'governorate_id' => $this->governorate->id,
            'name_ar' => 'سنحان وبني بهلول',
            'code' => '0201',
            'is_active' => true
        ]);
        $this->uzlah = Uzlah::create([
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'name_ar' => 'العزلة الأولى',
            'code' => '020101',
            'type' => 'عزلة',
            'is_active' => true
        ]);
        $this->village = Village::create([
            'uzlah_id' => $this->uzlah->id,
            'name' => 'قرية النصر',
            'is_active' => true
        ]);
    }

    public function test_guest_is_redirected_from_geography_page(): void
    {
        $response = $this->get(route('geography.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_geography_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('geography.index'));
        $response->assertStatus(200);
        $response->assertSee('التقسيم الإداري والجغرافي');
        $response->assertSee('صنعاء');
        $response->assertSee('سنحان');
    }

    public function test_can_fetch_districts_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('geo.districts', ['governorate' => $this->governorate->id]));
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'سنحان وبني بهلول']);
    }

    public function test_can_fetch_sub_districts_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('geo.sub_districts', ['district' => $this->directorate->id]));
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'العزلة الأولى']);
    }

    public function test_can_fetch_villages_via_ajax(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('geo.villages', ['sub_district' => $this->uzlah->id]));
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'قرية النصر']);
    }

    public function test_can_store_new_district(): void
    {
        $response = $this->actingAs($this->user)->post(route('geography.districts.store'), [
            'governorate_id' => $this->governorate->id,
            'name' => 'بني مطر',
            'code' => '0203',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('directorates', [
            'governorate_id' => $this->governorate->id,
            'name_ar' => 'بني مطر',
            'code' => '0203',
        ]);
    }

    public function test_can_store_new_sub_district(): void
    {
        $response = $this->actingAs($this->user)->post(route('geography.sub_districts.store'), [
            'district_id' => $this->directorate->id,
            'name' => 'عزلة جديدة',
            'code' => '020199',
            'type' => 'عزلة',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('uzlahs', [
            'directorate_id' => $this->directorate->id,
            'name_ar' => 'عزلة جديدة',
            'code' => '020199',
        ]);
    }

    public function test_can_store_new_village(): void
    {
        $response = $this->actingAs($this->user)->post(route('geography.villages.store'), [
            'sub_district_id' => $this->uzlah->id,
            'name' => 'قرية السلام',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('villages', [
            'uzlah_id' => $this->uzlah->id,
            'name' => 'قرية السلام',
        ]);
    }

    public function test_geography_seeder_seeds_all_22_governorates_333_directorates_and_2613_uzlahs(): void
    {
        $this->seed(\Database\Seeders\GeographySeeder::class);

        $this->assertEquals(22, Governorate::count());
        $this->assertEquals(333, Directorate::count());
        $this->assertEquals(2613, Uzlah::count());
    }
}
