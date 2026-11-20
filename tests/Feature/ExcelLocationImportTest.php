<?php

namespace Tests\Feature;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubLocation;
use App\Models\User;
use App\Models\Uzlah;
use App\Services\ExcelLocationImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExcelLocationImportTest extends TestCase
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

    public function test_excel_import_service_auto_creates_missing_villages_and_coops(): void
    {
        // 1. إنشاء مسمى موجود مسبقاً
        SubLocation::create([
            'uzlah_id' => $this->uzlah->id,
            'directorate_id' => $this->directorate->id,
            'governorate_id' => $this->governorate->id,
            'code' => '020101-0001',
            'name_ar' => 'قرية النصر',
            'entity_type' => 'قرية',
            'is_active' => 1,
        ]);

        // 2. إعداد ملف CSV تجريبي يحتوي على قرية مسجلة مسبقاً ومسميات جديدة
        $csvContent = "\xEF\xBB\xBF" . "المحافظة,المديرية,العزلة,القرية أو الجمعية,نوع الكيان\n";
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,قرية النصر,قرية\n"; // موجودة مسبقاً
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,جمعية سنحان التعاونية الزراعية,جمعية تعاونية\n"; // جديدة
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,قرية وادي الذهب,قرية\n"; // جديدة

        $file = UploadedFile::fake()->createWithContent('locations_sample.csv', $csvContent);

        $service = new ExcelLocationImportService();
        $result = $service->import($file);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['stats']['total']);
        $this->assertEquals(2, $result['stats']['created']);
        $this->assertEquals(1, $result['stats']['existing']);
        $this->assertEquals(0, $result['stats']['failed']);

        // التحقق من إدراج الكيانات الجديدة في قاعدة البيانات
        $this->assertDatabaseHas('sub_locations', [
            'name_ar' => 'جمعية سنحان التعاونية الزراعية',
            'entity_type' => 'جمعية تعاونية',
            'uzlah_id' => $this->uzlah->id,
        ]);

        $this->assertDatabaseHas('sub_locations', [
            'name_ar' => 'قرية وادي الذهب',
            'entity_type' => 'قرية',
            'uzlah_id' => $this->uzlah->id,
        ]);
    }

    public function test_web_excel_import_endpoint(): void
    {
        $csvContent = "\xEF\xBB\xBF" . "المحافظة,المديرية,العزلة,القرية أو الجمعية\n";
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,جمعية الفلاحين النموذجية\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $response = $this->actingAs($this->user)->post(route('geography.import_excel'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sub_locations', [
            'name_ar' => 'جمعية الفلاحين النموذجية',
            'uzlah_id' => $this->uzlah->id,
        ]);
    }

    public function test_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->user)->get(route('geography.download_template'));

        $response->assertStatus(200)
            ->assertHeader('Content-Disposition', 'attachment; filename="hasad_locations_import_template.csv"');
    }

    public function test_smart_import_auto_creates_missing_uzlah_without_failure(): void
    {
        $csvContent = "\xEF\xBB\xBF" . "المحافظة,المديرية,العزلة,القرية أو الجمعية,نوع الكيان\n";
        $csvContent .= "صنعاء,سنحان وبني بهلول,عزلة وادي قرو الجديدة,قرية دار السلام,قرية\n";

        $file = UploadedFile::fake()->createWithContent('new_uzlah.csv', $csvContent);

        $service = new ExcelLocationImportService();
        $result = $service->import($file);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['stats']['created']);
        $this->assertEquals(0, $result['stats']['failed']);

        // التأكد من إنشاء العزلة تلقائياً
        $this->assertDatabaseHas('uzlahs', [
            'name_ar' => 'عزلة وادي قرو الجديدة',
            'directorate_id' => $this->directorate->id,
        ]);

        // التأكد من حفظ القرية وربطها بالعزلة الجديدة
        $this->assertDatabaseHas('sub_locations', [
            'name_ar' => 'قرية دار السلام',
            'entity_type' => 'قرية',
        ]);

        // التأكد من وجود القرية في جدول villages
        $this->assertDatabaseHas('villages', [
            'name' => 'قرية دار السلام',
        ]);
    }

    public function test_smart_import_cooperative_association_persists_in_both_sublocations_and_associations(): void
    {
        $csvContent = "\xEF\xBB\xBF" . "المحافظة,المديرية,العزلة,القرية أو الجمعية,نوع الكيان,ملاحظات\n";
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,جمعية الرواد التعاونية الزراعية,جمعية تعاونية,جمعية نموذجية للتمكين\n";

        $file = UploadedFile::fake()->createWithContent('coop_import.csv', $csvContent);

        $service = new ExcelLocationImportService();
        $result = $service->import($file);

        $this->assertTrue($result['success']);

        // التحقق من الحفظ في جدول المستوى الرابع sub_locations
        $this->assertDatabaseHas('sub_locations', [
            'name_ar' => 'جمعية الرواد التعاونية الزراعية',
            'entity_type' => 'جمعية تعاونية',
            'notes' => 'جمعية نموذجية للتمكين',
        ]);

        // التحقق من الحفظ في جدول الجمعيات المركزي associations
        $this->assertDatabaseHas('associations', [
            'name' => 'جمعية الرواد التعاونية الزراعية',
            'type' => 'زراعية',
            'status' => 'نشطة',
            'governorate_id' => $this->governorate->id,
            'directorate_id' => $this->directorate->id,
        ]);
    }
}
