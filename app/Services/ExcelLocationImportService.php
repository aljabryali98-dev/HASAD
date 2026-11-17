<?php

namespace App\Services;

use App\Models\Governorate;
use App\Models\Directorate;
use App\Models\Uzlah;
use App\Models\SubLocation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use ZipArchive;

class ExcelLocationImportService
{
    /**
     * استيراد ومعالجة ملف Excel أو CSV مع الإدراج التلقائي للقرى والجمعيات غير المسجلة
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
            ];
        }

        // تحديد فهارس الأعمدة من الصف الأول (Header)
        $header = array_shift($rows);
        $colMap = $this->detectColumnMapping($header);

        if (!$colMap['governorate'] && !$colMap['uzlah']) {
            return [
                'success' => false,
                'message' => 'لم يتم العثور على أعمدة التقسيم الجغرافي الأساسية (المحافظة، المديرية، العزلة، القرية/الجمعية)',
                'stats' => ['total' => 0, 'created' => 0, 'existing' => 0, 'failed' => 0],
            ];
        }

        $totalRows = count($rows);
        $createdCount = 0;
        $matchedCount = 0;
        $failedRows = [];
        $importedRecords = [];

        // كاش في الذاكرة لتسريع المطابقة
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

            if (empty($govName) || empty($dirName) || empty($uzlName) || empty($subName)) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => 'حقول جغرافية ناقصة في هذا الصف',
                    'data' => $row,
                ];
                continue;
            }

            // 1. مطابقة المحافظة
            $govNorm = $this->normalizeName($govName);
            if (!isset($govCache[$govNorm])) {
                $gov = Governorate::all()->first(function ($g) use ($govNorm) {
                    return $this->normalizeName($g->name_ar) === $govNorm ||
                           $this->normalizeName($g->name) === $govNorm ||
                           $g->code === $govNorm;
                });
                $govCache[$govNorm] = $gov ? $gov->id : null;
            }

            $govId = $govCache[$govNorm];
            if (!$govId) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => "لم يتم العثور على المحافظة: '{$govName}'",
                    'data' => $row,
                ];
                continue;
            }

            // 2. مطابقة المديرية تحت المحافظة
            $dirKey = "{$govId}_" . $this->normalizeName($dirName);
            if (!isset($dirCache[$dirKey])) {
                $dirNorm = $this->normalizeName($dirName);
                $dir = Directorate::where('governorate_id', $govId)->get()->first(function ($d) use ($dirNorm) {
                    return $this->normalizeName($d->name_ar) === $dirNorm ||
                           $this->normalizeName($d->name) === $dirNorm ||
                           $d->code === $dirNorm;
                });
                $dirCache[$dirKey] = $dir ? $dir->id : null;
            }

            $dirId = $dirCache[$dirKey];
            if (!$dirId) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => "لم يتم العثور على المديرية: '{$dirName}' ضمن محافظة '{$govName}'",
                    'data' => $row,
                ];
                continue;
            }

            // 3. مطابقة العزلة تحت المديرية
            $uzlKey = "{$dirId}_" . $this->normalizeName($uzlName);
            if (!isset($uzlCache[$uzlKey])) {
                $uzlNorm = $this->normalizeName($uzlName);
                $uzl = Uzlah::where('directorate_id', $dirId)->get()->first(function ($u) use ($uzlNorm) {
                    return $this->normalizeName($u->name_ar) === $uzlNorm ||
                           $this->normalizeName($u->name) === $uzlNorm ||
                           $u->code === $uzlNorm;
                });
                $uzlCache[$uzlKey] = $uzl ? $uzl : null;
            }

            $uzlah = $uzlCache[$uzlKey];
            if (!$uzlah) {
                $failedRows[] = [
                    'row' => $rowNum,
                    'error' => "لم يتم العثور على العزلة: '{$uzlName}' ضمن مديرية '{$dirName}'",
                    'data' => $row,
                ];
                continue;
            }

            // 4. استنتاج نوع الكيان إذا لم يتم تحديده
            if (empty($entityType)) {
                if (mb_strpos($subName, 'جمعية') !== false) {
                    $entityType = 'جمعية تعاونية';
                } elseif (mb_strpos($subName, 'محلة') !== false) {
                    $entityType = 'محلة';
                } elseif (mb_strpos($subName, 'مزرعة') !== false || mb_strpos($subName, 'مشروع') !== false) {
                    $entityType = 'منطقة زراعية';
                } else {
                    $entityType = 'قرية';
                }
            }

            // 5. البحث عن الموقع الفرعي أو إدراجه تلقائياً (INSERT OR IGNORE / Auto-Creation)
            $subLocation = SubLocation::where('uzlah_id', $uzlah->id)
                ->where('name_ar', trim($subName))
                ->where('entity_type', $entityType)
                ->first();

            $wasCreated = false;
            if (!$subLocation) {
                $code = SubLocation::generateCode($uzlah->id);
                $subLocation = SubLocation::create([
                    'uzlah_id' => $uzlah->id,
                    'directorate_id' => $uzlah->directorate_id,
                    'governorate_id' => $uzlah->governorate_id,
                    'code' => $code,
                    'name_ar' => trim($subName),
                    'entity_type' => $entityType,
                    'notes' => 'تم الإدراج التلقائي عبر استيراد ملف الإكسل',
                    'is_active' => 1,
                ]);
                $createdCount++;
                $wasCreated = true;
            } else {
                $matchedCount++;
            }

            $importedRecords[] = [
                'row' => $rowNum,
                'governorate_id' => $govId,
                'directorate_id' => $dirId,
                'uzlah_id' => $uzlah->id,
                'sub_location_id' => $subLocation->id,
                'name_ar' => $subLocation->name_ar,
                'entity_type' => $subLocation->entity_type,
                'code' => $subLocation->code,
                'was_auto_created' => $wasCreated,
                'raw_row' => $row,
            ];
        }

        return [
            'success' => true,
            'message' => "اكتملت معالجة الملف بنجاح. تم إنشاء {$createdCount} مسمى جديد ومطابقة {$matchedCount} مسمى موجود.",
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
     * قراءة ملف XLSX عالي الأداء باستخدام SimpleXML و ZipArchive
     */
    protected function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // قراءة النصوص المشتركة (Shared Strings)
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml !== false) {
            $xml = new SimpleXMLElement($stringsXml);
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
                    $sharedStrings[] = '';
                }
            }
        }

        // قراءة ورقة العمل الأولى (Sheet 1)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return [];
        }

        $xml = new SimpleXMLElement($sheetXml);
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $attr = $cell->attributes();
                $cellType = isset($attr['t']) ? (string)$attr['t'] : '';
                $val = isset($cell->v) ? (string)$cell->v : '';

                if ($cellType === 's' && isset($sharedStrings[(int)$val])) {
                    $val = $sharedStrings[(int)$val];
                }

                $rowData[] = trim($val);
            }

            if (!empty(array_filter($rowData))) {
                $rows[] = $rowData;
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * قراءة ملف CSV مع معالجة الترميز UTF-8
     */
    protected function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // تخطي الـ BOM إذا وُجد
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                if (count($data) === 1 && strpos($data[0], ';') !== false) {
                    $data = str_getcsv($data[0], ';');
                }
                if (!empty(array_filter($data))) {
                    $rows[] = array_map('trim', $data);
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * التعرف الذكي على فهارس الأعمدة من ترويسة الجدول
     */
    protected function detectColumnMapping(array $header): array
    {
        $mapping = [
            'governorate' => null,
            'directorate' => null,
            'uzlah' => null,
            'sub_location' => null,
            'entity_type' => null,
        ];

        foreach ($header as $idx => $rawCol) {
            $col = $this->normalizeName($rawCol);

            if (preg_match('/(محافظ|governorate|gov)/i', $col)) {
                $mapping['governorate'] = $idx;
            } elseif (preg_match('/(مديري|district|directorate|dir)/i', $col)) {
                $mapping['directorate'] = $idx;
            } elseif (preg_match('/(عزل|مركز|sub_district|uzlah)/i', $col)) {
                $mapping['uzlah'] = $idx;
            } elseif (preg_match('/(قري|جمع|محل|منطق|موقع|sub_location|village|coop)/i', $col)) {
                $mapping['sub_location'] = $idx;
            } elseif (preg_match('/(نوع|كيان|entity_type|type)/i', $col)) {
                $mapping['entity_type'] = $idx;
            }
        }

        // كخطة بديلة إذا كانت 4 أعمدة بالترتيب التقليدي
        if ($mapping['governorate'] === null && isset($header[0])) $mapping['governorate'] = 0;
        if ($mapping['directorate'] === null && isset($header[1])) $mapping['directorate'] = 1;
        if ($mapping['uzlah'] === null && isset($header[2])) $mapping['uzlah'] = 2;
        if ($mapping['sub_location'] === null && isset($header[3])) $mapping['sub_location'] = 3;

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
        // إزالة علامات التشكيل
        $str = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $str);
        // توحيد الألف
        $str = preg_replace('/[أإآ]/u', 'ا', $str);
        // توحيد التاء المربوطة والهاء
        $str = preg_replace('/ة/u', 'ه', $str);
        // توحيد الياء والألف المقصورة
        $str = preg_replace('/ى/u', 'ي', $str);
        // إزالة الكلمات الزائدة مثل محافظة أو مديرية أو عزلة
        $str = preg_replace('/^(محافظة|مديرية|عزلة|قرية|حي|مركز)\s+/u', '', $str);
        // إزالة المسافات الزائدة
        $str = preg_replace('/\s+/', ' ', $str);
        return trim($str);
    }
}
