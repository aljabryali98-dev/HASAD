<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use XMLReader;
use SimpleXMLElement;
use ZipArchive;

class ImportMasterDataCommand extends Command
{
    protected $signature = 'hasad:import-master-data {file? : المسار الكامل لملف الإكسل}';
    protected $description = 'استيراد وتسكين قاعدة البيانات التشغيلية الكبرى من ملف الإكسل (60 ورقة عمل و57 ألف سجل)';

    private array $sharedStrings = [];
    private array $govMap = [];
    private array $dirMap = [];
    private array $dirByGovMap = [];
    private array $uzlahMap = [];
    private array $firstUzlahByDir = [];
    private array $assocMap = [];
    private array $assocByDir = [];
    private int $defaultGovId = 1;
    private int $defaultDirId = 101;
    private int $defaultUzlahId = 10101;

    public function handle(): int
    {
        $filePath = $this->argument('file') ?: 'C:\\Users\\Ali\\Desktop\\بيانات.xlsx';

        if (!file_exists($filePath)) {
            $this->error("ملف الإكسل غير موجود في المسار: {$filePath}");
            return 1;
        }

        $this->info("===================================================================");
        $this->info("بدء تشغيل محرك التسكين والاستيراد الشامل لنظام حصاد المركزي");
        $this->info("الملف: {$filePath} (" . round(filesize($filePath) / 1024 / 1024, 2) . " MB)");
        $this->info("===================================================================");

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            $this->error("تعذر فتح ملف الإكسل كأرشيف مضغوط.");
            return 1;
        }

        // 1. قراءة النصوص المشتركة
        $this->info("1. تحميل النصوص المشتركة (Shared Strings)...");
        $this->loadSharedStrings($zip);

        // 2. قراءة خريطة أوراق العمل
        $this->info("2. فهرسة أوراق العمل الـ 60 والمسارات الداخلية...");
        $sheets = $this->loadSheetsMap($zip);

        // 3. بناء فهارس المطابقة الجغرافية السريعة في الذاكرة
        $this->info("3. بناء محرك المطابقة الجغرافية في الذاكرة (22 محافظة، 333 مديرية، 2613 عزلة)...");
        $this->buildGeoLookups();

        // 4. استيراد الجمعيات أولاً لبناء خريطة ربط الجمعيات
        $this->info("4. استيراد الجمعيات التعاونية (Cooperative Associations)...");
        $this->importAssociations($zip, $sheets['الجمعيات'] ?? null);

        // 5. استيراد مشاريع الجمعيات
        $this->info("5. استيراد مشاريع الجمعيات...");
        $this->importAssociationProjects($zip, $sheets['مشاريع الجمعية'] ?? null);

        // 6. استيراد الهيئات الإدارية والقيادات التنفيذية
        $this->info("6. استيراد الهيئات الإدارية والقيادات التنفيذية...");
        $this->importBoardMembers($zip, $sheets);

        // 7. استيراد المبادرات المجتمعية (أكثر من 22,000 سجل)
        $this->info("7. استيراد المبادرات المجتمعية (Community Initiatives)...");
        $this->importInitiatives($zip, $sheets['المبادرات'] ?? null);

        // 8. استيراد القطاعات التنموية والإنتاجية
        $this->info("8. استيراد القطاعات التنموية والإنتاجية (القروض، الاستصلاح، التعاقدية، التوسع، المشاتل...)...");
        $this->importProductiveModules($zip, $sheets);

        // 9. استيراد سجل الفرسان والكوادر الموحد (Unified Personnel - ~30,000 سجل)
        $this->info("9. استيراد سجل الفرسان والكوادر البشرية الموحد (Unified Personnel)...");
        $this->importUnifiedPersonnel($zip, $sheets);

        $zip->close();

        // 10. إفراغ الكاش الجغرافي والإحصائي
        $this->info("10. إفراغ وتحديث كافة الكاشات واللوحات الإحصائية...");
        Cache::flush();

        $this->info("===================================================================");
        $this->info("تمت عملية الاستيراد والتسكين الشاملة بنجاح 100%!");
        $this->displayFinalCounts();
        $this->info("===================================================================");

        return 0;
    }

    private function loadSharedStrings(ZipArchive $zip): void
    {
        $xmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if (!$xmlContent) return;

        $reader = new XMLReader();
        $reader->XML($xmlContent);
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'si') {
                $node = new SimpleXMLElement($reader->readOuterXml());
                $text = '';
                if (isset($node->t)) {
                    $text = (string)$node->t;
                } elseif (isset($node->r)) {
                    foreach ($node->r as $r) {
                        $text .= (string)$r->t;
                    }
                }
                $this->sharedStrings[] = $text;
            }
        }
        $this->info("   تم تحميل " . count($this->sharedStrings) . " نصاً مشتركاً.");
    }

    private function loadSheetsMap(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        $rels = [];
        if ($relsXml) {
            foreach (simplexml_load_string($relsXml)->Relationship as $rel) {
                $rels[(string)$rel['Id']] = (string)$rel['Target'];
            }
        }

        $sheets = [];
        if ($workbookXml) {
            foreach (simplexml_load_string($workbookXml)->sheets->sheet as $s) {
                $name = (string)$s['name'];
                $rId = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                $target = ltrim($rels[$rId] ?? '', '/');
                if (!str_starts_with($target, 'xl/')) {
                    $target = 'xl/' . $target;
                }
                $sheets[$name] = $target;
            }
        }
        return $sheets;
    }

    private function buildGeoLookups(): void
    {
        // المحافظات
        $govs = DB::table('governorates')->select('id', 'name_ar')->get();
        foreach ($govs as $g) {
            $norm = $this->normalizeArabic($g->name_ar);
            $this->govMap[$norm] = $g->id;
            // Aliases
            if ($g->name_ar === 'أمانة العاصمة') {
                $this->govMap['الامانه'] = $g->id;
                $this->govMap['امانه العاصمه'] = $g->id;
                $this->govMap['صنعاء الامانه'] = $g->id;
                $this->govMap['الامانة'] = $g->id;
            }
            if ($g->name_ar === 'إب') {
                $this->govMap['اب'] = $g->id;
            }
            if ($g->name_ar === 'الحديدة') {
                $this->govMap['الحديده'] = $g->id;
                $this->govMap['حديده'] = $g->id;
            }
        }
        $this->defaultGovId = $govs->first()->id ?? 1;

        // المديريات
        $dirs = DB::table('directorates')->select('id', 'governorate_id', 'name_ar')->get();
        foreach ($dirs as $d) {
            $norm = $this->normalizeArabic($d->name_ar);
            $this->dirMap[$d->governorate_id][$norm] = $d->id;
            $this->dirByGovMap[$norm] = $d->id;
        }
        $this->defaultDirId = $dirs->first()->id ?? 101;

        // العزل
        $uzlahs = DB::table('uzlahs')->select('id', 'directorate_id', 'name_ar')->get();
        foreach ($uzlahs as $u) {
            $norm = $this->normalizeArabic($u->name_ar);
            $this->uzlahMap[$u->directorate_id][$norm] = $u->id;
            if (!isset($this->firstUzlahByDir[$u->directorate_id])) {
                $this->firstUzlahByDir[$u->directorate_id] = $u->id;
            }
        }
        $this->defaultUzlahId = $uzlahs->first()->id ?? 10101;
    }

    private function matchGov(?string $name): int
    {
        if (!$name) return $this->defaultGovId;
        $norm = $this->normalizeArabic($name);
        if (isset($this->govMap[$norm])) return $this->govMap[$norm];
        foreach ($this->govMap as $k => $id) {
            if ($k !== '' && (str_contains($norm, $k) || str_contains($k, $norm))) {
                return $id;
            }
        }
        return $this->defaultGovId;
    }

    private function matchDir(int $govId, ?string $name): int
    {
        if (!$name) {
            return array_values($this->dirMap[$govId] ?? [])[0] ?? $this->defaultDirId;
        }
        $norm = $this->normalizeArabic($name);
        if (isset($this->dirMap[$govId][$norm])) return $this->dirMap[$govId][$norm];

        // Search within governorate
        if (isset($this->dirMap[$govId])) {
            foreach ($this->dirMap[$govId] as $k => $id) {
                if ($k !== '' && (str_contains($norm, $k) || str_contains($k, $norm))) {
                    return $id;
                }
            }
        }

        // Global fallback
        if (isset($this->dirByGovMap[$norm])) return $this->dirByGovMap[$norm];

        return array_values($this->dirMap[$govId] ?? [])[0] ?? $this->defaultDirId;
    }

    private function matchUzlah(int $dirId, ?string $name): int
    {
        if (!$name || $name === '0') {
            return $this->firstUzlahByDir[$dirId] ?? $this->defaultUzlahId;
        }
        $norm = $this->normalizeArabic($name);
        if (isset($this->uzlahMap[$dirId][$norm])) return $this->uzlahMap[$dirId][$norm];

        if (isset($this->uzlahMap[$dirId])) {
            foreach ($this->uzlahMap[$dirId] as $k => $id) {
                if ($k !== '' && (str_contains($norm, $k) || str_contains($k, $norm))) {
                    return $id;
                }
            }
        }

        return $this->firstUzlahByDir[$dirId] ?? $this->defaultUzlahId;
    }

    private function normalizeArabic(?string $str): string
    {
        if (!$str) return '';
        $str = mb_strtolower(trim($str), 'UTF-8');
        $str = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $str); // إزالة التشكيل
        $str = preg_replace('/[أإآ]/u', 'ا', $str);
        $str = preg_replace('/ة/u', 'ه', $str);
        $str = preg_replace('/ى/u', 'ي', $str);
        $str = preg_replace('/^(محافظة|مديرية|عزلة|قرية|مركز|قطاع)\s+/u', '', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }

    private function parseDate(?string $val): ?string
    {
        if (empty($val)) return null;
        $val = trim($val);
        if (is_numeric($val)) {
            $num = (float)$val;
            if ($num > 30000 && $num < 60000) {
                return gmdate('Y-m-d', (int)(($num - 25569) * 86400));
            }
            if ($num >= 1970 && $num <= 2035) {
                return ((int)$num) . '-01-01';
            }
        }
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        if (preg_match('/(20\d{2}|19\d{2})/', $val, $m)) {
            return $m[1] . '-01-01';
        }
        return null;
    }

    private function parseNum($val): float
    {
        if (empty($val)) return 0.0;
        $clean = preg_replace('/[^\d\.\-]/', '', (string)$val);
        return is_numeric($clean) ? (float)$clean : 0.0;
    }

    private function colLetterToIndex(string $cellRef): ?int
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches)) return null;
        $colLetters = $matches[1];
        $col = 0;
        for ($i = 0; $i < strlen($colLetters); $i++) {
            $col = $col * 26 + (ord($colLetters[$i]) - 64);
        }
        return $col - 1;
    }

    private function iterateRows(ZipArchive $zip, ?string $target, callable $callback): void
    {
        if (!$target) return;
        $xml = $zip->getFromName($target);
        if (!$xml) return;

        $reader = new XMLReader();
        $reader->XML($xml);
        $rowIndex = 0;

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'row') {
                $rowIndex++;
                $rowXml = new SimpleXMLElement($reader->readOuterXml());
                $cells = [];
                foreach ($rowXml->c as $c) {
                    $ref = (string)$c['r'];
                    $colIdx = $this->colLetterToIndex($ref);
                    $t = (string)$c['t'];
                    $val = '';
                    if ($t === 's') {
                        $val = $this->sharedStrings[(int)(string)$c->v] ?? '';
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    } else {
                        $val = (string)$c->v;
                    }
                    if ($colIdx !== null) $cells[$colIdx] = trim($val);
                }
                $maxIdx = !empty($cells) ? max(array_keys($cells)) : -1;
                $rowArr = [];
                for ($k = 0; $k <= $maxIdx; $k++) $rowArr[$k] = $cells[$k] ?? '';
                $callback($rowArr, $rowIndex);
            }
        }
    }

    // ==========================================
    // 4. استيراد الجمعيات
    // ==========================================
    private function importAssociations(ZipArchive $zip, ?string $target): void
    {
        if (!$target) return;

        $rowsToInsert = [];
        $headerFound = false;
        $seq = 1;

        $this->iterateRows($zip, $target, function ($row, $rIdx) use (&$rowsToInsert, &$headerFound, &$seq) {
            $joined = implode(' ', $row);
            if (!$headerFound) {
                if (str_contains($joined, 'اسم الجمعية') || str_contains($joined, 'المحافظة')) {
                    $headerFound = true;
                }
                return;
            }

            $name = $row[3] ?? '';
            if (empty($name) || str_contains($name, 'اسم الجمعية')) return;

            $govName = $row[1] ?? '';
            $dirName = $row[2] ?? '';
            $govId = $this->matchGov($govName);
            $dirId = $this->matchDir($govId, $dirName);
            $uzlahId = $this->matchUzlah($dirId, null);

            $type = !empty($row[4]) ? $row[4] : 'زراعية';
            $activity = $row[5] ?? '';
            $boardCount = (int)$this->parseNum($row[6] ?? 7) ?: 7;
            $membersCount = (int)$this->parseNum($row[7] ?? 0);
            $capital = $this->parseNum($row[8] ?? 0);
            $shares = (int)$this->parseNum($row[9] ?? 0);
            $foundedDate = $this->parseDate($row[10] ?? null);
            $assets = $row[11] ?? '';
            $notes = $row[14] ?? '';

            $code = 'ASOC-' . str_pad($seq++, 4, '0', STR_PAD_LEFT);

            $rowsToInsert[] = [
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'status' => 'نشطة',
                'governorate_id' => $govId,
                'directorate_id' => $dirId,
                'uzlah_id' => $uzlahId,
                'board_members_count' => $boardCount,
                'total_members' => $membersCount,
                'initial_capital' => $capital,
                'current_capital' => $capital,
                'shares_count' => $shares,
                'founded_date' => $foundedDate,
                'other_assets' => $assets,
                'main_activities' => $activity,
                'monitoring_team_notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::transaction(function () use ($rowsToInsert) {
            DB::table('associations')->delete(); // إعادة تسكين نظيفة
            foreach (array_chunk($rowsToInsert, 100) as $chunk) {
                DB::table('associations')->insert($chunk);
            }
        });

        // بناء خريطة الجمعيات في الذاكرة
        $assocs = DB::table('associations')->select('id', 'name', 'directorate_id')->get();
        foreach ($assocs as $a) {
            $norm = $this->normalizeArabic($a->name);
            $this->assocMap[$norm] = $a->id;
            $this->assocByDir[$a->directorate_id] = $a->id;

            // Simplified alias: e.g. "جمعية الرضمة" -> "الرضمة"
            $short = preg_replace('/^جمعيه\s+/u', '', $norm);
            $short = preg_replace('/(التعاونيه|الزراعيه|متعدده الاغراض|لمنتجي الحبوب)/u', '', $short);
            $short = trim($short);
            if (!empty($short)) {
                $this->assocMap[$short] = $a->id;
            }
        }

        $this->info("   تم استيراد " . count($assocs) . " جمعية تعاونية بنجاح.");
    }

    private function matchAssoc(?string $name, int $dirId): ?int
    {
        if (!empty($name)) {
            $norm = $this->normalizeArabic($name);
            if (isset($this->assocMap[$norm])) return $this->assocMap[$norm];
            foreach ($this->assocMap as $k => $id) {
                if ($k !== '' && (str_contains($norm, $k) || str_contains($k, $norm))) {
                    return $id;
                }
            }
        }
        return $this->assocByDir[$dirId] ?? null;
    }

    // ==========================================
    // 5. استيراد مشاريع الجمعيات
    // ==========================================
    private function importAssociationProjects(ZipArchive $zip, ?string $target): void
    {
        if (!$target) return;

        $rows = [];
        $headerFound = false;

        $this->iterateRows($zip, $target, function ($row) use (&$rows, &$headerFound) {
            $joined = implode(' ', $row);
            if (!$headerFound) {
                if (str_contains($joined, 'المشروع') || str_contains($joined, 'اسم الجمعية')) {
                    $headerFound = true;
                }
                return;
            }
            $projName = $row[4] ?? '';
            if (empty($projName) || str_contains($projName, 'المشروع')) return;

            $govId = $this->matchGov($row[1] ?? '');
            $dirId = $this->matchDir($govId, $row[2] ?? '');
            $assocId = $this->matchAssoc($row[3] ?? '', $dirId);

            $rows[] = [
                'governorate_id' => $govId,
                'directorate_id' => $dirId,
                'uzlah_id' => $this->firstUzlahByDir[$dirId] ?? null,
                'association_id' => $assocId,
                'project_name' => $projName,
                'quantity' => (int)$this->parseNum($row[5] ?? 1) ?: 1,
                'project_type' => $row[6] ?? 'تمكين اقتصادي',
                'execution_status' => 'منفذ',
                'cost' => 0,
                'notes' => $row[7] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::transaction(function () use ($rows) {
            DB::table('association_projects')->delete();
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('association_projects')->insert($chunk);
            }
        });

        $this->info("   تم استيراد " . count($rows) . " مشروع جمعية.");
    }

    // ==========================================
    // 6. استيراد الهيئات الإدارية والقيادات
    // ==========================================
    private function importBoardMembers(ZipArchive $zip, array $sheets): void
    {
        $boardSheets = [
            'الهيئة الإدارية والرقابية والإد' => 'عضو هيئة إدارية',
            'المدير التنفيذي' => 'مدير تنفيذي',
            'المحاسبين' => 'محاسب',
            'سكرتارية الجمعيات' => 'سكرتير',
            'اللجان التجضيرية' => 'عضو لجنة تحضيرية',
        ];

        $allMembers = [];

        foreach ($boardSheets as $shName => $defaultRole) {
            $target = $sheets[$shName] ?? null;
            if (!$target) continue;

            $headerFound = false;
            $this->iterateRows($zip, $target, function ($row) use (&$allMembers, &$headerFound, $defaultRole) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'الاسم') || str_contains($joined, 'المحافظة')) {
                        $headerFound = true;
                    }
                    return;
                }

                $name = $row[3] ?? '';
                if (empty($name) || str_contains($name, 'الاسم')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');
                $assocId = $this->assocByDir[$dirId] ?? DB::table('associations')->first()->id ?? 1;

                $role = !empty($row[4]) ? $row[4] : $defaultRole;
                $phone = $row[9] ?? null;
                $edu = $row[6] ?? null;
                $spec = $row[7] ?? null;
                $notes = $row[12] ?? null;

                $allMembers[] = [
                    'association_id' => $assocId,
                    'full_name' => $name,
                    'gender' => 'ذكر',
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'uzlah_id' => $this->firstUzlahByDir[$dirId] ?? null,
                    'role_title' => $role,
                    'phone' => $phone,
                    'education_level' => $edu,
                    'specialization' => $spec,
                    'membership_status' => 'نشط',
                    'notes' => $notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
        }

        DB::transaction(function () use ($allMembers) {
            DB::table('association_board_members')->delete();
            foreach (array_chunk($allMembers, 250) as $chunk) {
                DB::table('association_board_members')->insert($chunk);
            }
        });

        $this->info("   تم استيراد " . count($allMembers) . " عضواً وقيادياً في مجالس الإدارة والإدارة التنفيذية.");
    }

    // ==========================================
    // 7. استيراد المبادرات المجتمعية (22,356)
    // ==========================================
    private function importInitiatives(ZipArchive $zip, ?string $target): void
    {
        if (!$target) return;

        DB::table('community_initiatives')->delete();

        $batch = [];
        $headerFound = false;
        $totalImported = 0;

        $this->iterateRows($zip, $target, function ($row) use (&$batch, &$headerFound, &$totalImported) {
            $joined = implode(' ', $row);
            if (!$headerFound) {
                if (str_contains($joined, 'اسم المبادرة') || str_contains($joined, 'نوع المبادرة')) {
                    $headerFound = true;
                }
                return;
            }

            $initName = $row[5] ?? '';
            if (empty($initName) || str_contains($initName, 'اسم المبادرة')) return;

            $govId = $this->matchGov($row[1] ?? '');
            $dirId = $this->matchDir($govId, $row[2] ?? '');
            $uzlahId = $this->matchUzlah($dirId, $row[3] ?? null);
            $village = !empty($row[4]) ? $row[4] : null;

            $type = !empty($row[6]) ? $row[6] : 'زراعية';
            $initiators = (int)$this->parseNum($row[7] ?? 0);
            $beneficiaries = (int)$this->parseNum($row[8] ?? 0);
            $supportType = $row[9] ?? null;
            $execEntity = $row[10] ?? null;
            $cost = $this->parseNum($row[11] ?? 0);
            $funding = $row[12] ?? null;
            $date = $this->parseDate($row[13] ?? null);
            $notes = $row[14] ?? null;

            $batch[] = [
                'governorate_id' => $govId,
                'directorate_id' => $dirId,
                'uzlah_id' => $uzlahId,
                'village_name' => $village,
                'association_id' => $this->assocByDir[$dirId] ?? null,
                'initiative_name' => $initName,
                'initiative_type' => $type,
                'initiators_count' => $initiators,
                'beneficiaries_count' => $beneficiaries,
                'support_type' => $supportType,
                'execution_entity' => $execEntity,
                'cost' => $cost,
                'funding_source' => $funding,
                'session_date' => $date,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('community_initiatives')->insert($batch);
                $totalImported += count($batch);
                $batch = [];
            }
        });

        if (!empty($batch)) {
            DB::table('community_initiatives')->insert($batch);
            $totalImported += count($batch);
        }

        $this->info("   تم استيراد {$totalImported} مبادرة مجتمعية بنجاح.");
    }

    // ==========================================
    // 8. استيراد القطاعات التنموية والإنتاجية
    // ==========================================
    private function importProductiveModules(ZipArchive $zip, array $sheets): void
    {
        // 1. القروض
        $loanTarget = $sheets['القروض'] ?? null;
        if ($loanTarget) {
            DB::table('association_loans')->delete();
            $loans = [];
            $headerFound = false;
            $seq = 1;
            $this->iterateRows($zip, $loanTarget, function ($row) use (&$loans, &$headerFound, &$seq) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'برنامج التمكين') || str_contains($joined, 'النوع')) {
                        $headerFound = true;
                    }
                    return;
                }
                $prog = $row[4] ?? '';
                if (empty($prog) || str_contains($prog, 'برنامج')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');
                $amount = $this->parseNum($row[10] ?? 0);
                if ($amount <= 0) $amount = 100000;

                $loans[] = [
                    'code' => 'LN-' . str_pad($seq++, 5, '0', STR_PAD_LEFT),
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'loan_type' => 'تمكين اقتصادي',
                    'funding_entity' => $row[12] ?? 'وحدة التمويل',
                    'beneficiary_name' => $row[11] ?? 'المستفيدين بالمديرية',
                    'loan_amount' => $amount,
                    'currency' => 'ريال يمني',
                    'empowerment_program_name' => $prog,
                    'activity_field' => $row[3] ?? null,
                    'quantity_description' => ($row[6] ?? '') . ' ' . ($row[7] ?? ''),
                    'beneficiaries_count' => (int)$this->parseNum($row[8] ?? 1) ?: 1,
                    'execution_entity' => $row[11] ?? null,
                    'funding_type' => $row[13] ?? 'قرض أبيض',
                    'execution_status' => $row[14] ?? 'قيد التنفيذ',
                    'notes' => $row[17] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            if (!empty($loans)) {
                DB::table('association_loans')->insert($loans);
                $this->info("   - القروض وبرامج التمكين: " . count($loans) . " سجل.");
            }
        }

        // 2. استصلاح وحماية الأراضي
        $landTarget = $sheets['استصلاح وحماية الاراضي'] ?? null;
        if ($landTarget) {
            DB::table('land_reclamations')->delete();
            $lands = [];
            $headerFound = false;
            $this->iterateRows($zip, $landTarget, function ($row) use (&$lands, &$headerFound) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'اسم المشروع') || str_contains($joined, 'المساحة')) {
                        $headerFound = true;
                    }
                    return;
                }
                $proj = $row[5] ?? '';
                if (empty($proj) || str_contains($proj, 'اسم المشروع')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');

                $lands[] = [
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'uzlah_id' => $this->matchUzlah($dirId, $row[3] ?? null),
                    'village_name' => $row[4] ?? null,
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'project_name' => $proj,
                    'intervention_type' => $row[6] ?? 'حراثة',
                    'land_type' => $row[7] ?? 'أراضي زراعية',
                    'actual_completed_area' => $this->parseNum($row[8] ?? 0),
                    'area_unit' => $row[9] ?? 'هكتار',
                    'beneficiaries_count' => (int)$this->parseNum($row[10] ?? 0),
                    'cost' => $this->parseNum($row[11] ?? 0),
                    'funding_source' => $row[12] ?? 'ذاتي',
                    'execution_status' => $row[13] ?? 'منفذ',
                    'execution_date' => $this->parseDate($row[14] ?? null),
                    'notes' => $row[15] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            if (!empty($lands)) {
                DB::table('land_reclamations')->insert($lands);
                $this->info("   - استصلاح وحماية الأراضي: " . count($lands) . " سجل.");
            }
        }

        // 3. الزراعة التعاقدية
        $contractTarget = $sheets['الزراعة التعاقدية'] ?? null;
        if ($contractTarget) {
            DB::table('contract_farming')->delete();
            $contracts = [];
            $headerFound = false;
            $this->iterateRows($zip, $contractTarget, function ($row) use (&$contracts, &$headerFound) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'اسم المحصول') || str_contains($joined, 'الكمية')) {
                        $headerFound = true;
                    }
                    return;
                }
                $crop = $row[5] ?? '';
                if (empty($crop) || str_contains($crop, 'المحصول')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');

                $contracts[] = [
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'uzlah_id' => $this->matchUzlah($dirId, $row[3] ?? null),
                    'village_name' => $row[4] ?? null,
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'crop_name' => $crop,
                    'crop_type' => $row[6] ?? 'حبوب',
                    'quantity' => $this->parseNum($row[7] ?? 0),
                    'quantity_unit' => $row[8] ?? 'طن',
                    'marketing_type' => $row[9] ?? 'الزراعة التعاقدية',
                    'marketing_party' => $row[10] ?? null,
                    'beneficiaries_count' => (int)$this->parseNum($row[11] ?? 0),
                    'cost' => $this->parseNum($row[12] ?? 0),
                    'funding_source' => $row[13] ?? null,
                    'execution_status' => $row[14] ?? 'ساري',
                    'contract_date' => $this->parseDate($row[16] ?? null),
                    'notes' => $row[17] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            if (!empty($contracts)) {
                DB::table('contract_farming')->insert($contracts);
                $this->info("   - الزراعة التعاقدية: " . count($contracts) . " سجل.");
            }
        }

        // 4. التوسع في المحاصيل
        $expandTarget = $sheets['التوسع'] ?? null;
        if ($expandTarget) {
            DB::table('crop_expansions')->delete();
            $expansions = [];
            $headerFound = false;
            $this->iterateRows($zip, $expandTarget, function ($row) use (&$expansions, &$headerFound) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'المساحة') || str_contains($joined, 'المحصول')) {
                        $headerFound = true;
                    }
                    return;
                }
                $proj = $row[4] ?? '';
                if (empty($proj) || str_contains($proj, 'المشروع')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');

                $expansions[] = [
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'project_name' => $proj,
                    'intervention_type' => $row[5] ?? 'توسع',
                    'expanded_area_hectare' => $this->parseNum($row[6] ?? 0),
                    'crop_category' => $row[8] ?? 'حبوب',
                    'crop_variety' => $row[9] ?? 'قمح',
                    'quantity' => $this->parseNum($row[10] ?? 0),
                    'quantity_unit' => $row[11] ?? 'كيس',
                    'beneficiaries_count' => (int)$this->parseNum($row[12] ?? 0),
                    'cost' => $this->parseNum($row[13] ?? 0),
                    'funding_source' => $row[14] ?? null,
                    'record_date' => $this->parseDate($row[15] ?? null),
                    'notes' => $row[16] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            if (!empty($expansions)) {
                DB::table('crop_expansions')->insert($expansions);
                $this->info("   - التوسع في المحاصيل: " . count($expansions) . " سجل.");
            }
        }

        // 5. المشاتل والتشتيل
        $nurseryTarget = $sheets['المشاتل'] ?? null;
        if ($nurseryTarget) {
            DB::table('nurseries')->delete();
            $nurseries = [];
            $headerFound = false;
            $this->iterateRows($zip, $nurseryTarget, function ($row) use (&$nurseries, &$headerFound) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'اسم المشتل') || str_contains($joined, 'الطاقة')) {
                        $headerFound = true;
                    }
                    return;
                }
                $name = $row[4] ?? '';
                if (empty($name) || str_contains($name, 'اسم المشتل')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');

                $nurseries[] = [
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'uzlah_id' => $this->firstUzlahByDir[$dirId] ?? null,
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'nursery_name' => $name,
                    'ownership_type' => $row[5] ?? 'تعاوني',
                    'capacity' => $this->parseNum($row[6] ?? 0),
                    'capacity_unit' => $row[7] ?? 'شتلة',
                    'status' => 'مفعل',
                    'record_date' => $this->parseDate($row[10] ?? null),
                    'notes' => $row[11] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            if (!empty($nurseries)) {
                DB::table('nurseries')->insert($nurseries);
                $this->info("   - المشاتل والشتلات: " . count($nurseries) . " سجل.");
            }
        }

        // 6. الثروة الحيوانية والألبان والتحصين
        $livestockSheets = [
            'فرسن الالبان' => 'فرسان الألبان',
            'توطين صناعة الحليب' => 'توطين صناعة الحليب',
            'حملات التحصين' => 'حملة تحصين',
        ];
        DB::table('dairy_and_livestocks')->delete();
        $dairyRows = [];
        foreach ($livestockSheets as $shName => $actType) {
            $target = $sheets[$shName] ?? null;
            if (!$target) continue;
            $headerFound = false;
            $this->iterateRows($zip, $target, function ($row) use (&$dairyRows, &$headerFound, $actType) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'المحافظة') || str_contains($joined, 'المديرية')) {
                        $headerFound = true;
                    }
                    return;
                }
                $govName = $row[1] ?? '';
                if (empty($govName) || str_contains($govName, 'المحافظة')) return;

                $govId = $this->matchGov($govName);
                $dirId = $this->matchDir($govId, $row[2] ?? '');

                $dairyRows[] = [
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'uzlah_id' => $this->firstUzlahByDir[$dirId] ?? null,
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'activity_type' => $actType,
                    'target_immunized_heads' => (int)$this->parseNum($row[5] ?? 0),
                    'produced_milk_liters' => $this->parseNum($row[6] ?? 0),
                    'execution_entity' => 'اللجنة الزراعية',
                    'cost' => $this->parseNum($row[8] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
        }
        if (!empty($dairyRows)) {
            DB::table('dairy_and_livestocks')->insert($dairyRows);
            $this->info("   - الثروة الحيوانية والألبان والتحصين: " . count($dairyRows) . " سجل.");
        }

        // 7. الدراسات والخطط
        $studiesTarget = $sheets['الدراسات'] ?? null;
        if ($studiesTarget) {
            DB::table('studies_and_research')->delete();
            $studies = [];
            $headerFound = false;
            $this->iterateRows($zip, $studiesTarget, function ($row) use (&$studies, &$headerFound) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'الدراسة') || str_contains($joined, 'المحافظة')) {
                        $headerFound = true;
                    }
                    return;
                }
                $study = $row[3] ?? '';
                if (empty($study) || str_contains($study, 'الدراسة')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');

                $studies[] = [
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'study_name' => $study,
                    'study_type' => $row[4] ?? 'دراسة ميدانية',
                    'target_group' => $row[5] ?? 'المجتمع المحلي',
                    'cost' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            if (!empty($studies)) {
                DB::table('studies_and_research')->insert($studies);
                $this->info("   - الدراسات والبحوث: " . count($studies) . " سجل.");
            }
        }
    }

    // ==========================================
    // 9. استيراد سجل الفرسان والكوادر البشرية (~30,000)
    // ==========================================
    private function importUnifiedPersonnel(ZipArchive $zip, array $sheets): void
    {
        DB::table('unified_personnel')->delete();

        $personnelSheets = [
            'فرسان التنمية' => ['فرسان التنمية', 'فرسان التنمية'],
            'فرسان التنمية الحضرية' => ['فرسان التنمية الحضرية', 'فرسان التنمية الحضرية'],
            'الباحثين' => ['باحثين', 'بحوث ودراسات'],
            'ضباط الإقراض' => ['ضباط إقراض', 'تمويل وإقراض'],
            'تدريب الارشاد' => ['تدريب مرشدين', 'إرشاد زراعي'],
            'الصحة الحيوانية' => ['صحة حيوانية', 'صحة وبيطرة'],
            'الاعلام' => ['إعلام', 'إعلام زراعي'],
            'فرسان الهندسة' => ['فرسان الجمعية', 'فرسان الهندسة'],
            'فرسان التسويق' => ['فرسان الجمعية', 'فرسان التسويق'],
            'فرسان الديزل' => ['فرسان الجمعية', 'فرسان الديزل'],
            'فرسان المحاماة' => ['فرسان الجمعية', 'فرسان المحاماة'],
            'فرسان الري' => ['فرسان الجمعية', 'فرسان الري'],
            'فرسان المسح الميداني ' => ['فرسان المسح الميداني', 'مسح ميداني'],
            'مدربين جمعيات' => ['مدربي الجمعية', 'تدريب جمعيات'],
            'مساعدي مدربين جمعيات' => ['مساعدي مدربي الجمعية', 'مساعد تدريب'],
            'مدربين فرسان التنمية' => ['مدربين فرسان التنمية', 'تدريب فرسان'],
            'فرسان المحليات ' => ['فرسان المحليات', 'محليات'],
            'السلطة المحلية' => ['السلطة المحلية', 'سلطة محلية'],
            'فرسان الاصلاحيات' => ['فرسان الإصلاحيات', 'إصلاحيات'],
            'فرسان الالغام' => ['فرسان الألغام', 'ألغام'],
            'الرائد التنموي ' => ['رائد تنموي', 'رائد تنموي'],
            'فرسان الابداع' => ['فرسان الإبداع', 'إبداع وابتكار'],
            'فرسان الاستثمار' => ['فرسان الاستثمار', 'استثمار تعاوني'],
            'فرسان الاودية' => ['فرسان الأودية', 'أودية وسيول'],
            'فرسان المعالم الزراعية' => ['فرسان المعالم الزراعية', 'معالم زراعية'],
            'فرسان الدواء' => ['فرسان الدواء', 'دواء زراعي'],
            'مساعدين ثقافيين' => ['مساعدين ثقافيين', 'تثقيف مجتمعي'],
            'طلاب الزراعة' => ['طلاب زراعة', 'تعليم زراعي'],
            'الرائد الجامعي' => ['رائد جامعي', 'رائد جامعي'],
        ];

        $globalSeq = 1;
        $totalImported = 0;

        foreach ($personnelSheets as $shName => $catInfo) {
            $target = $sheets[$shName] ?? null;
            if (!$target) continue;

            $category = $catInfo[0];
            $specialty = $catInfo[1];

            $batch = [];
            $headerFound = false;

            $this->iterateRows($zip, $target, function ($row) use (&$batch, &$headerFound, &$globalSeq, &$totalImported, $category, $specialty) {
                $joined = implode(' ', $row);
                if (!$headerFound) {
                    if (str_contains($joined, 'اسم المتدرب') || str_contains($joined, 'الاسم') || str_contains($joined, 'اسم الطالب')) {
                        $headerFound = true;
                    }
                    return;
                }

                // اسم الكادر في العمود 5 أو 3
                $name = $row[5] ?? ($row[3] ?? '');
                if (empty($name) || str_contains($name, 'اسم المتدرب') || str_contains($name, 'الاسم')) return;

                $govId = $this->matchGov($row[1] ?? '');
                $dirId = $this->matchDir($govId, $row[2] ?? '');
                $uzlahId = $this->matchUzlah($dirId, $row[3] ?? null);
                $village = !empty($row[4]) ? $row[4] : null;

                $age = (int)$this->parseNum($row[6] ?? 0) ?: null;
                $edu = $row[7] ?? null;
                $spec = $row[8] ?? $specialty;
                $employer = $row[9] ?? null;
                $phone = $row[10] ?? null;
                $prog = $row[11] ?? null;
                $date = $this->parseDate($row[13] ?? null);
                $notes = $row[16] ?? null;

                $batch[] = [
                    'code' => 'UP-' . str_pad($globalSeq++, 6, '0', STR_PAD_LEFT),
                    'category' => $category,
                    'sub_specialty' => $spec,
                    'association_id' => $this->assocByDir[$dirId] ?? null,
                    'governorate_id' => $govId,
                    'directorate_id' => $dirId,
                    'uzlah_id' => $uzlahId,
                    'village_name' => $village,
                    'full_name' => $name,
                    'gender' => 'ذكر',
                    'age' => $age,
                    'education_level' => $edu,
                    'specialization' => $spec,
                    'employer' => $employer,
                    'phone' => $phone,
                    'training_program_name' => $prog,
                    'training_date' => $date,
                    'notes' => $notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= 500) {
                    DB::table('unified_personnel')->insert($batch);
                    $totalImported += count($batch);
                    $batch = [];
                }
            });

            if (!empty($batch)) {
                DB::table('unified_personnel')->insert($batch);
                $totalImported += count($batch);
            }
        }

        $this->info("   تم استيراد {$totalImported} كادر وفارس في السجل الموحد بنجاح.");
    }

    private function displayFinalCounts(): void
    {
        $stats = [
            'الجمعيات التعاونية (Associations)' => DB::table('associations')->count(),
            'مشاريع الجمعيات (Projects)' => DB::table('association_projects')->count(),
            'الهيئات الإدارية والقيادات (Board Members)' => DB::table('association_board_members')->count(),
            'المبادرات المجتمعية (Initiatives)' => DB::table('community_initiatives')->count(),
            'سجل الفرسان والكوادر الموحد (Unified Personnel)' => DB::table('unified_personnel')->count(),
            'القروض والتمكين (Loans)' => DB::table('association_loans')->count(),
            'استصلاح وحماية الأراضي (Land Reclamations)' => DB::table('land_reclamations')->count(),
            'الزراعة التعاقدية (Contract Farming)' => DB::table('contract_farming')->count(),
            'التوسع في المحاصيل (Crop Expansions)' => DB::table('crop_expansions')->count(),
            'المشاتل والشتلات (Nurseries)' => DB::table('nurseries')->count(),
            'الثروة الحيوانية والألبان (Livestock & Dairy)' => DB::table('dairy_and_livestocks')->count(),
            'الدراسات والبحوث (Studies)' => DB::table('studies_and_research')->count(),
        ];

        $totalRecords = array_sum($stats);
        $this->table(['البيان / الوحدة', 'إجمالي السجلات المسكنة في قاعدة البيانات'], array_map(function ($k, $v) {
            return [$k, number_format($v)];
        }, array_keys($stats), array_values($stats)));

        $this->info("إجمالي السجلات المسكنة حديثاً: " . number_format($totalRecords) . " سجلاً.");
    }
}
