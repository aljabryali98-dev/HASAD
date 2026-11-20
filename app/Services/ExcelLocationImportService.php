<?php

namespace App\Services;

use App\Models\Governorate;
use App\Models\Directorate;
use App\Models\Uzlah;
use App\Models\SubLocation;
use App\Models\Village;
use App\Models\Association;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use ZipArchive;

class ExcelLocationImportService
{
    /**
     * استيراد ومعالجة ملف Excel أو CSV مع التكامل الذكي والإدراج التلقائي
     */
    public function import(UploadedFile|string $file, array $options = []): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $originalName = is_string($file) ? basename($file) : $file->getClientOriginalName();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $rows = [];
        if ($extension === 'xlsx') {
            $rows = $this->parseXlsx($filePath);
        } else {
            $rows = $this->parseCsv($filePath);
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'الملف فارغ أو لا يحتوي على صفوف بيانات صالحة',
                'stats' => ['total' => 0, 'created' => 0, 'existing' => 0, 'failed' => 0],
                'failed_rows' => [],
                'imported_records' => [],
            ];
        }

        // تحديد فهارس الأعمدة من الصف الأول (Header)
        $header = array_shift($rows);
        $colMap = $this->detectColumnMapping($header);

        if ($colMap['governorate'] === null && $colMap['uzlah'] === null && $colMap['sub_location'] === null) {
            return [
                'success' => false,
                'message' => 'لم يتم العثور على أعمدة التقسيم الجغرافي الأساسية (المحافظة، المديرية، العزلة، القرية/الجمعية)',
                'stats' => ['total' => 0, 'created' => 0, 'existing' => 0, 'failed' => 0],
                'failed_rows' => [],
                'imported_records' => [],
            ];
        }

        $totalRows = count($rows);
        $createdCount = 0;
        $matchedCount = 0;
        $failedRows = [];
        $importedRecords = [];
        $touchedUzlahIds = [];

        // كاش في الذاكرة لتسريع المطابقة أثناء الاستيراد الكبير
        $govCache = [];
        $dirCache = [];
        $uzlCache = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // رقم الصف في ملف الإكسل (1-based + header)

            $govName = $this->getVal($row, $colMap['governorate']);
            $dirName = $this->getVal($row, $colMap['directorate']);
            $uzlName = $this->getVal($row, $colMap['uzlah']);
            $subName = $this->getVal($row, $colMap['sub_location']);
            $entityType = $this->getVal($row, $colMap['entity_type']);
            $notes = $this->getVal($row, $colMap['notes']);
            $userCode = $this->getVal($row, $colMap['code']);

            // التحقق من الحقول الأساسية
            if (empty($govName) || empty($dirName) || empty($subName)) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => 'حقول جغرافية ناقصة في هذا الصف (المحافظة أو المديرية أو اسم القرية/الجمعية)',
                    'data' => $row,
                ];
                continue;
            }

            // إذا كانت العزلة فارغة، نعتمد مركز المديرية تلقائياً
            if (empty($uzlName)) {
                $uzlName = 'مركز المديرية';
            }

            // 1. مطابقة المحافظة الذكية
            $govId = $this->matchGovernorate($govName, $govCache);
            if (!$govId) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => "لم يتم العثور على المحافظة: '{$govName}'",
                    'data' => $row,
                ];
                continue;
            }

            // 2. مطابقة المديرية الذكية تحت المحافظة
            $directorate = $this->matchDirectorate($govId, $dirName, $dirCache);
            if (!$directorate) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => "لم يتم العثور على المديرية: '{$dirName}' ضمن المحافظة المحددة",
                    'data' => $row,
                ];
                continue;
            }

            // 3. التكامل الذكي: مطابقة العزلة أو إنشاؤها تلقائياً إذا لم تكن مسجلة
            $uzlah = $this->matchOrCreateUzlah($directorate, $uzlName, $uzlCache);
            $touchedUzlahIds[$uzlah->id] = true;

            // 4. استنتاج نوع الكيان بدقة إذا لم يُحدد
            $entityType = $this->normalizeEntityType($entityType, $subName);

            // 5. التحقق من وجود الموقع الفرعي أو إدراجه (الحفظ الدائم)
            $subLocation = SubLocation::where('uzlah_id', $uzlah->id)
                ->where('name_ar', trim($subName))
                ->where('entity_type', $entityType)
                ->first();

            $wasCreated = false;
            if (!$subLocation) {
                $code = (!empty($userCode) && !SubLocation::where('code', trim($userCode))->exists())
                    ? trim($userCode)
                    : SubLocation::generateCode($uzlah->id);

                $finalNotes = !empty($notes) ? trim($notes) : 'تم الإدراج التلقائي عبر استيراد ملف الإكسل';

                $subLocation = SubLocation::create([
                    'uzlah_id' => $uzlah->id,
                    'directorate_id' => $directorate->id,
                    'governorate_id' => $govId,
                    'code' => $code,
                    'name_ar' => trim($subName),
                    'entity_type' => $entityType,
                    'notes' => $finalNotes,
                    'is_active' => 1,
                ]);

                $createdCount++;
                $wasCreated = true;
            } else {
                $matchedCount++;
            }

            // 6. الحفظ التكاملي في جدول villages لضمان التوافق مع الأنظمة والقوائم القديمة
            Village::firstOrCreate(
                [
                    'uzlah_id' => $uzlah->id,
                    'name' => trim($subName),
                ],
                [
                    'sub_district_id' => $uzlah->id,
                    'is_active' => true,
                ]
            );

            // 7. إذا كان الكيان جمعية تعاونية، التحقق من وجودها في جدول الجمعيات وإدراجها إن لم توجد
            if ($entityType === 'جمعية تعاونية' || mb_strpos($subName, 'جمعية') !== false) {
                $assocExists = Association::where('governorate_id', $govId)
                    ->where('directorate_id', $directorate->id)
                    ->where('name', trim($subName))
                    ->exists();

                if (!$assocExists) {
                    $assocCode = 'ASC-' . strtoupper(substr(uniqid(), -6));
                    Association::create([
                        'code' => $assocCode,
                        'name' => trim($subName),
                        'type' => 'زراعية',
                        'status' => 'نشطة',
                        'governorate_id' => $govId,
                        'directorate_id' => $directorate->id,
                        'uzlah_id' => $uzlah->id,
                        'sub_location_id' => $subLocation->id,
                        'has_bylaws' => true,
                        'has_member_registry' => true,
                        'has_headquarters' => true,
                    ]);
                }
            }

            $importedRecords[] = [
                'row' => $rowNum,
                'governorate_id' => $govId,
                'directorate_id' => $directorate->id,
                'uzlah_id' => $uzlah->id,
                'sub_location_id' => $subLocation->id,
                'name_ar' => $subLocation->name_ar,
                'entity_type' => $subLocation->entity_type,
                'code' => $subLocation->code,
                'was_auto_created' => $wasCreated,
                'raw_row' => $row,
            ];
        }

        // تفريغ الكاش للعزل التي تم تحديثها
        foreach (array_keys($touchedUzlahIds) as $uId) {
            Cache::forget("geo_villages_{$uId}");
        }
        Cache::forget('geo_manage_stats');
        Cache::forget('geo_index_stats');
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');

        return [
            'success' => true,
            'message' => "اكتملت معالجة الملف بنجاح. تم إنشاء {$createdCount} مسمى جديد ومطابقة {$matchedCount} مسمى مسجل مسبقاً.",
            'stats' => [
                'total' => $totalRows,
                'created' => $createdCount,
                'existing' => $matchedCount,
                'failed' => count($failedRows),
            ],
            'failed_rows' => $failedRows,
            'imported_records' => $importedRecords,
        ];
    }

    /**
     * مطابقة المحافظة بالاسم أو الكود أو بالاحتواء
     */
    protected function matchGovernorate(string $govName, array &$cache): ?int
    {
        $govNorm = $this->normalizeName($govName);
        if (isset($cache[$govNorm])) {
            return $cache[$govNorm];
        }

        // 1. مطابقة مباشرة
        $gov = Governorate::all()->first(function ($g) use ($govNorm) {
            $normAr = $this->normalizeName($g->name_ar);
            $normEn = $this->normalizeName($g->name);
            return $normAr === $govNorm || $normEn === $govNorm || $g->code === $govNorm;
        });

        // 2. مطابقة بالاحتواء (Contains Match)
        if (!$gov) {
            $gov = Governorate::all()->first(function ($g) use ($govNorm) {
                $normAr = $this->normalizeName($g->name_ar);
                return (!empty($normAr) && mb_stripos($normAr, $govNorm) !== false) ||
                       (!empty($govNorm) && mb_stripos($govNorm, $normAr) !== false);
            });
        }

        // 3. مطابقة حالات خاصة مشهورة (أمانة العاصمة / صنعاء)
        if (!$gov) {
            if (mb_stripos($govNorm, 'امان') !== false || mb_stripos($govNorm, 'عاصم') !== false) {
                $gov = Governorate::where('id', 1)->orWhere('code', '01')->first();
            }
        }

        $id = $gov ? $gov->id : null;
        $cache[$govNorm] = $id;
        return $id;
    }

    /**
     * مطابقة المديرية ضمن المحافظة بالاسم أو الكود أو بالاحتواء
     */
    protected function matchDirectorate(int $govId, string $dirName, array &$cache): ?Directorate
    {
        $dirNorm = $this->normalizeName($dirName);
        $key = "{$govId}_{$dirNorm}";
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $dirs = Directorate::where('governorate_id', $govId)->get();

        // 1. مطابقة مباشرة
        $dir = $dirs->first(function ($d) use ($dirNorm) {
            $normAr = $this->normalizeName($d->name_ar);
            $normEn = $this->normalizeName($d->name);
            return $normAr === $dirNorm || $normEn === $dirNorm || $d->code === $dirNorm;
        });

        // 2. مطابقة بالاحتواء (Contains Match)
        if (!$dir) {
            $dir = $dirs->first(function ($d) use ($dirNorm) {
                $normAr = $this->normalizeName($d->name_ar);
                return (!empty($normAr) && mb_stripos($normAr, $dirNorm) !== false) ||
                       (!empty($dirNorm) && mb_stripos($dirNorm, $normAr) !== false);
            });
        }

        $cache[$key] = $dir ?: null;
        return $dir;
    }

    /**
     * مطابقة العزلة ضمن المديرية، أو إنشاؤها تلقائياً إذا لم تكن موجودة
     */
    protected function matchOrCreateUzlah(Directorate $directorate, string $uzlName, array &$cache): Uzlah
    {
        $uzlNorm = $this->normalizeName($uzlName);
        $key = "{$directorate->id}_{$uzlNorm}";
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $uzlahs = Uzlah::where('directorate_id', $directorate->id)->get();

        // 1. مطابقة مباشرة
        $uzlah = $uzlahs->first(function ($u) use ($uzlNorm) {
            $normAr = $this->normalizeName($u->name_ar);
            $normEn = $this->normalizeName($u->name);
            return $normAr === $uzlNorm || $normEn === $uzlNorm || $u->code === $uzlNorm;
        });

        // 2. مطابقة بالاحتواء
        if (!$uzlah) {
            $uzlah = $uzlahs->first(function ($u) use ($uzlNorm) {
                $normAr = $this->normalizeName($u->name_ar);
                return (!empty($normAr) && mb_stripos($normAr, $uzlNorm) !== false) ||
                       (!empty($uzlNorm) && mb_stripos($uzlNorm, $normAr) !== false);
            });
        }

        // 3. التكامل الذكي الفائق: إنشاء العزلة تلقائياً دون إيقاف الاستيراد
        if (!$uzlah) {
            $nextSeq = Uzlah::where('directorate_id', $directorate->id)->count() + 1;
            $code = sprintf('%s%02d', $directorate->code, $nextSeq);
            while (Uzlah::where('code', $code)->exists()) {
                $nextSeq++;
                $code = sprintf('%s%02d', $directorate->code, $nextSeq);
            }

            $uzlah = Uzlah::create([
                'directorate_id' => $directorate->id,
                'governorate_id' => $directorate->governorate_id,
                'code' => $code,
                'name_ar' => trim($uzlName),
                'name' => trim($uzlName),
                'type' => 'عزلة',
                'is_active' => true,
            ]);
        }

        $cache[$key] = $uzlah;
        return $uzlah;
    }

    /**
     * استنتاج وتطبيع نوع الكيان
     */
    protected function normalizeEntityType(?string $type, string $name): string
    {
        $raw = mb_strtolower(trim($type ?? ''), 'UTF-8');

        if (!empty($raw)) {
            if (mb_stripos($raw, 'جمع') !== false || mb_stripos($raw, 'تعاون') !== false) {
                return 'جمعية تعاونية';
            }
            if (mb_stripos($raw, 'محل') !== false) {
                return 'محلة';
            }
            if (mb_stripos($raw, 'زراع') !== false || mb_stripos($raw, 'مزرع') !== false || mb_stripos($raw, 'مشروع') !== false) {
                return 'منطقة زراعية';
            }
            if (mb_stripos($raw, 'حي') !== false || mb_stripos($raw, 'حار') !== false) {
                return 'حي/حارة';
            }
            if (mb_stripos($raw, 'قري') !== false) {
                return 'قرية';
            }
        }

        // استنتاج تلقائي من الاسم
        if (mb_strpos($name, 'جمعية') !== false) {
            return 'جمعية تعاونية';
        }
        if (mb_strpos($name, 'محلة') !== false) {
            return 'محلة';
        }
        if (mb_strpos($name, 'مزرعة') !== false || mb_strpos($name, 'مشروع') !== false || mb_strpos($name, 'حوض') !== false) {
            return 'منطقة زراعية';
        }
        if (mb_strpos($name, 'حي') !== false || mb_strpos($name, 'حارة') !== false) {
            return 'حي/حارة';
        }

        return 'قرية';
    }

    /**
     * قراءة ملف XLSX عالي الأداء مع معالجة فهارس الأعمدة الصحيحة
     */
    protected function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. قراءة النصوص المشتركة (Shared Strings)
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml !== false) {
            $xml = @simplexml_load_string($stringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string)$val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $text = '';
                        if (method_exists($val, 'xpath')) {
                            foreach ($val->xpath('.//t') as $t) {
                                $text .= (string)$t;
                            }
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        // 2. البحث عن ورقة العمل الأولى
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (str_starts_with($entryName, 'xl/worksheets/') && str_ends_with($entryName, '.xml')) {
                    $sheetXml = $zip->getFromName($entryName);
                    break;
                }
            }
        }

        if ($sheetXml === false) {
            $zip->close();
            return [];
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            $zip->close();
            return [];
        }

        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $attr = $cell->attributes();
                $cellRef = isset($attr['r']) ? (string)$attr['r'] : '';
                $cellType = isset($attr['t']) ? (string)$attr['t'] : '';
                $val = isset($cell->v) ? (string)$cell->v : '';

                if ($cellType === 's' && isset($sharedStrings[(int)$val])) {
                    $val = $sharedStrings[(int)$val];
                } elseif ($cellType === 'inlineStr' && isset($cell->is->t)) {
                    $val = (string)$cell->is->t;
                }

                $colIdx = !empty($cellRef) ? $this->colLetterToIndex($cellRef) : count($rowData);
                $rowData[$colIdx] = trim($val);
            }

            if (!empty($rowData)) {
                $maxCol = max(array_keys($rowData));
                $cleanRow = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $cleanRow[$c] = $rowData[$c] ?? '';
                }
                if (!empty(array_filter($cleanRow))) {
                    $rows[] = $cleanRow;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * تحويل حرف العمود في Excel إلى رقم فهرس 0-based
     * مثال: A -> 0, B -> 1, Z -> 25, AA -> 26
     */
    protected function colLetterToIndex(string $ref): int
    {
        if (preg_match('/^([A-Z]+)/i', $ref, $m)) {
            $letters = strtoupper($m[1]);
            $num = 0;
            for ($i = 0; $i < strlen($letters); $i++) {
                $num = $num * 26 + (ord($letters[$i]) - ord('A') + 1);
            }
            return max(0, $num - 1);
        }
        return 0;
    }

    /**
     * قراءة ملف CSV أو TXT مع الكشف التلقائي عن الفاصل والترميز UTF-8 / Windows-1256
     */
    protected function parseCsv(string $filePath): array
    {
        $rows = [];
        $raw = file_get_contents($filePath);
        if ($raw === false || empty(trim($raw))) {
            return [];
        }

        // تحويل الترميز إلى UTF-8 إذا كان بترميز Windows-1256 العربي
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $converted = @mb_convert_encoding($raw, 'UTF-8', ['Windows-1256', 'ISO-8859-6', 'ASCII']);
            if ($converted !== false) {
                $raw = $converted;
            }
        }

        // إزالة علامة الـ BOM إذا وُجدت
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw);

        // اكتشاف الفاصل الأنسب (فاصلة، فاصلة منقوطة، تاب)
        $delimiter = ',';
        if (!empty($lines[0])) {
            $counts = [
                ',' => substr_count($lines[0], ','),
                ';' => substr_count($lines[0], ';'),
                "\t" => substr_count($lines[0], "\t"),
                '|' => substr_count($lines[0], '|'),
            ];
            arsort($counts);
            $bestDelim = array_key_first($counts);
            if ($counts[$bestDelim] > 0) {
                $delimiter = $bestDelim;
            }
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            $data = str_getcsv($trimmed, $delimiter);
            $data = array_map('trim', $data);
            if (!empty(array_filter($data))) {
                $rows[] = $data;
            }
        }

        return $rows;
    }

    /**
     * التعرف الذكي على فهارس الأعمدة من الترويسة
     */
    protected function detectColumnMapping(array $header): array
    {
        $mapping = [
            'governorate' => null,
            'directorate' => null,
            'uzlah' => null,
            'sub_location' => null,
            'entity_type' => null,
            'notes' => null,
            'code' => null,
        ];

        foreach ($header as $idx => $rawCol) {
            $raw = mb_strtolower(trim($rawCol), 'UTF-8');

            if (preg_match('/(محافظ|governorate|gov)/iu', $raw)) {
                $mapping['governorate'] = $idx;
            } elseif (preg_match('/(مديري|directorate|district|dir)/iu', $raw)) {
                $mapping['directorate'] = $idx;
            } elseif (preg_match('/(عزل|مركز|sub_district|uzlah)/iu', $raw)) {
                $mapping['uzlah'] = $idx;
            } elseif (preg_match('/(قري|جمع|محل|منطق|موقع|sub_location|village|coop|اسم_القرية|المسمى)/iu', $raw)) {
                $mapping['sub_location'] = $idx;
            } elseif (preg_match('/(نوع|كيان|entity_type|type)/iu', $raw)) {
                $mapping['entity_type'] = $idx;
            } elseif (preg_match('/(ملاحظ|note|بيان|وصف)/iu', $raw)) {
                $mapping['notes'] = $idx;
            } elseif (preg_match('/(كود|رمز|code)/iu', $raw)) {
                $mapping['code'] = $idx;
            }
        }

        // خطة بديلة إذا لم تكن الترويسات معرّفة (ترتيب الأعمدة الافتراضي)
        if ($mapping['governorate'] === null && isset($header[0])) $mapping['governorate'] = 0;
        if ($mapping['directorate'] === null && isset($header[1])) $mapping['directorate'] = 1;
        if ($mapping['uzlah'] === null && isset($header[2])) $mapping['uzlah'] = 2;
        if ($mapping['sub_location'] === null && isset($header[3])) $mapping['sub_location'] = 3;
        if ($mapping['entity_type'] === null && isset($header[4])) $mapping['entity_type'] = 4;
        if ($mapping['notes'] === null && isset($header[5])) $mapping['notes'] = 5;

        return $mapping;
    }

    protected function getVal(array $row, ?int $index): string
    {
        return ($index !== null && isset($row[$index])) ? trim($row[$index]) : '';
    }

    /**
     * تطبيع الأسماء العربية للبحث والمطابقة الدقيقة متجاوزاً اختلافات الهمزات والتاء المربوطة
     */
    protected function normalizeName(?string $str): string
    {
        if (!$str) return '';
        $str = mb_strtolower(trim($str), 'UTF-8');
        // إزالة التشكيل
        $str = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $str);
        // توحيد الألف
        $str = preg_replace('/[أإآ]/u', 'ا', $str);
        // توحيد التاء المربوطة والهاء
        $str = preg_replace('/ة/u', 'ه', $str);
        // توحيد الياء والألف المقصورة
        $str = preg_replace('/ى/u', 'ي', $str);
        // إزالة الكلمات الزائدة
        $str = preg_replace('/^(محافظة|مديرية|عزلة|قرية|حي|مركز)\s+/u', '', $str);
        // إزالة المسافات الزائدة
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }
}
