<?php

namespace Database\Seeders;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\Uzlah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GeographySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/yemen_geography.json');
        
        if (!File::exists($jsonPath)) {
            return;
        }

        $json = json_decode(File::get($jsonPath), true);
        $tables = $json['flat_tables'] ?? [];

        // 1. زراعة المحافظات (22 محافظة)
        if (!empty($tables['governorates'])) {
            $now = now();
            $govRows = array_map(function ($g) use ($now) {
                return [
                    'id' => $g['id'],
                    'code' => $g['code'],
                    'name_ar' => $g['name_ar'],
                    'name' => $g['name_ar'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $tables['governorates']);

            // إزالة أي سجلات قديمة ذات أكواد متعارضة ومعرفات غير متطابقة لضمان عدم التكرار
            $canonicalGovCodes = array_column($govRows, 'code');
            $canonicalGovIds = array_column($govRows, 'id');
            DB::table('governorates')
                ->whereIn('code', $canonicalGovCodes)
                ->whereNotIn('id', $canonicalGovIds)
                ->delete();

            DB::table('governorates')->upsert($govRows, ['id'], ['code', 'name_ar', 'name', 'is_active', 'updated_at']);
        }

        // 2. زراعة المديريات (333 مديرية)
        if (!empty($tables['directorates'])) {
            $now = now();
            $dirRows = array_map(function ($d) use ($now) {
                return [
                    'id' => $d['id'],
                    'governorate_id' => $d['governorate_id'],
                    'code' => $d['code'],
                    'name_ar' => $d['name_ar'],
                    'name' => $d['name_ar'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $tables['directorates']);

            // إزالة أي سجلات قديمة ذات أكواد متعارضة ومعرفات غير متطابقة
            $canonicalDirCodes = array_column($dirRows, 'code');
            $canonicalDirIds = array_column($dirRows, 'id');
            DB::table('directorates')
                ->whereIn('code', $canonicalDirCodes)
                ->whereNotIn('id', $canonicalDirIds)
                ->delete();

            foreach (array_chunk($dirRows, 100) as $chunk) {
                DB::table('directorates')->upsert($chunk, ['id'], ['governorate_id', 'code', 'name_ar', 'name', 'is_active', 'updated_at']);
            }
        }

        // 3. زراعة العزل ومراكز المديريات (2,613 عزلة ومركز وحي حضري - التغطية الشاملة 100%)
        if (!empty($tables['uzlahs'])) {
            $now = now();
            $uzlahRows = array_map(function ($u) use ($now) {
                return [
                    'id' => $u['id'],
                    'directorate_id' => $u['directorate_id'],
                    'governorate_id' => $u['governorate_id'],
                    'code' => $u['code'],
                    'name_ar' => $u['name_ar'],
                    'name' => $u['name_ar'],
                    'type' => $u['type'] ?? 'عزلة',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $tables['uzlahs']);

            // إزالة أي سجلات قديمة ذات أكواد متعارضة ومعرفات غير متطابقة لضمان عدم التكرار
            $canonicalUzlahCodes = array_column($uzlahRows, 'code');
            $canonicalUzlahIds = array_column($uzlahRows, 'id');
            DB::table('uzlahs')
                ->whereIn('code', $canonicalUzlahCodes)
                ->whereNotIn('id', $canonicalUzlahIds)
                ->delete();

            foreach (array_chunk($uzlahRows, 100) as $chunk) {
                DB::table('uzlahs')->upsert($chunk, ['id'], ['directorate_id', 'governorate_id', 'code', 'name_ar', 'name', 'type', 'is_active', 'updated_at']);
            }
        }
    }
}
