<?php

namespace App\Http\Controllers;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubLocation;
use App\Models\Uzlah;
use App\Models\Village;
use App\Services\ExcelLocationImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GeographyController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للتقسيم الجغرافي والإداري (22 محافظة + 333 مديرية + العزل)
     */
    public function index(Request $request): View
    {
        $governorates = Governorate::withCount('directorates')->orderBy('id')->get();

        $selectedGovId = $request->query('gov_id', $governorates->first()?->id);
        
        $selectedGov = null;
        $directorates = collect();

        if ($selectedGovId) {
            $selectedGov = Governorate::with(['directorates.uzlahs.subLocations', 'directorates.uzlahs.villages'])->find($selectedGovId);
            $directorates = $selectedGov ? $selectedGov->directorates : collect();
        }

        $stats = Cache::remember('geo_index_stats', 600, function () {
            $subStats = SubLocation::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN entity_type = 'جمعية تعاونية' THEN 1 END) as coops,
                COUNT(CASE WHEN entity_type = 'قرية' THEN 1 END) as villages
            ")->first();

            return [
                'governorates_count' => Governorate::count(),
                'districts_count' => Directorate::count(),
                'sub_districts_count' => Uzlah::count(),
                'villages_count' => (int) ($subStats->villages ?: Village::count()),
                'sub_locations_count' => (int) ($subStats->total ?? 0),
                'coops_count' => (int) ($subStats->coops ?? 0),
            ];
        });

        return view('geography.index', compact('governorates', 'selectedGov', 'directorates', 'stats'));
    }

    /**
     * شاشة إدارة النطاق الجغرافي والجمعيات والقرى (المستوى الرابع الديناميكي)
     */
    public function manage(Request $request): View
    {
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $selectedGovId = $request->query('gov_id');
        $selectedDirId = $request->query('dir_id');
        $selectedUzlId = $request->query('uzl_id');
        $entityType = $request->query('type');
        $search = $request->query('search');

        $query = SubLocation::with(['uzlah:id,name_ar,name', 'directorate:id,name_ar,name', 'governorate:id,name_ar,name']);

        if (!empty($selectedGovId)) {
            $query->where('governorate_id', $selectedGovId);
        }
        if (!empty($selectedDirId)) {
            $query->where('directorate_id', $selectedDirId);
        }
        if (!empty($selectedUzlId)) {
            $query->where('uzlah_id', $selectedUzlId);
        }
        if (!empty($entityType)) {
            $query->where('entity_type', $entityType);
        }
        if (!empty($search)) {
            $query->search($search);
        }

        $subLocations = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $stats = Cache::remember('geo_manage_stats', 600, function () {
            $subStats = SubLocation::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN entity_type = 'جمعية تعاونية' THEN 1 END) as coops,
                COUNT(CASE WHEN entity_type = 'قرية' THEN 1 END) as villages
            ")->first();

            return [
                'governorates_count' => Governorate::count(),
                'districts_count' => Directorate::count(),
                'sub_districts_count' => Uzlah::count(),
                'sub_locations_count' => (int) ($subStats->total ?? 0),
                'villages_count' => (int) ($subStats->villages ?? 0),
                'coops_count' => (int) ($subStats->coops ?? 0),
            ];
        });

        return view('geography.manage', compact(
            'governorates',
            'subLocations',
            'stats',
            'selectedGovId',
            'selectedDirId',
            'selectedUzlId',
            'entityType',
            'search'
        ));
    }

    /**
     * استرجاع مديريات محافظة محددة (AJAX)
     */
    public function getDistricts(int $governorateId): JsonResponse
    {
        $directorates = Cache::remember("geo_districts_{$governorateId}", 86400, function () use ($governorateId) {
            return Directorate::where('governorate_id', $governorateId)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name_ar', 'name'])
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name_ar ?: $item->name,
                    ];
                });
        });

        return response()->json($directorates)
            ->header('Cache-Control', 'no-cache, private, must-revalidate');
    }

    /**
     * استرجاع عزل مديرية محددة (AJAX)
     */
    public function getSubDistricts(int $districtId): JsonResponse
    {
        $uzlahs = Cache::remember("geo_uzlahs_{$districtId}", 86400, function () use ($districtId) {
            return Uzlah::where('directorate_id', $districtId)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code', 'name_ar', 'name', 'type'])
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name_ar ?: $item->name,
                        'type' => $item->type,
                    ];
                });
        });

        return response()->json($uzlahs)
            ->header('Cache-Control', 'no-cache, private, must-revalidate');
    }

    /**
     * استرجاع قرى ومواقع عزلة محددة (AJAX)
     */
    public function getVillages(int $subDistrictId): JsonResponse
    {
        $villages = Cache::remember("geo_villages_{$subDistrictId}", 86400, function () use ($subDistrictId) {
            // دمج نتائج sub_locations مع villages للتوافق التام
            $subLocations = SubLocation::where('uzlah_id', $subDistrictId)
                ->where('is_active', 1)
                ->orderBy('name_ar')
                ->get(['id', 'code', 'name_ar as name', 'entity_type']);

            if ($subLocations->isNotEmpty()) {
                return $subLocations;
            }

            return Village::where(function ($query) use ($subDistrictId) {
                    $query->where('uzlah_id', $subDistrictId)
                          ->orWhere('sub_district_id', $subDistrictId);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->select(['id', 'name'])
                ->get();
        });

        return response()->json($villages)
            ->header('Cache-Control', 'no-cache, private, must-revalidate');
    }

    /**
     * إضافة مديرية جديدة
     */
    public function storeDistrict(Request $request)
    {
        $validated = $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:directorates,code',
        ]);

        Directorate::create([
            'governorate_id' => $validated['governorate_id'],
            'code' => $validated['code'],
            'name_ar' => $validated['name'],
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        // إفراغ الكاش الجغرافي
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget("geo_districts_{$validated['governorate_id']}");

        return redirect()->route('geography.index', ['gov_id' => $validated['governorate_id']])
            ->with('success', 'تمت إضافة المديرية بنجاح.');
    }

    /**
     * إضافة عزلة جديدة
     */
    public function storeSubDistrict(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:directorates,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:uzlahs,code',
            'type' => 'nullable|string|max:50',
        ]);

        $directorate = Directorate::findOrFail($validated['district_id']);

        Uzlah::create([
            'directorate_id' => $directorate->id,
            'governorate_id' => $directorate->governorate_id,
            'code' => $validated['code'],
            'name_ar' => $validated['name'],
            'name' => $validated['name'],
            'type' => $validated['type'] ?? 'عزلة',
            'is_active' => true,
        ]);

        // إفراغ الكاش الجغرافي
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget("geo_uzlahs_{$directorate->id}");

        return redirect()->route('geography.index', ['gov_id' => $directorate->governorate_id])
            ->with('success', 'تمت إضافة العزلة بنجاح.');
    }

    /**
     * إضافة قرية أو موقع فرعي (متوافق مع النظام القديم والجديد)
     */
    public function storeVillage(Request $request)
    {
        $validated = $request->validate([
            'sub_district_id' => 'required|exists:uzlahs,id',
            'name' => 'required|string|max:150',
            'entity_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $uzlah = Uzlah::with('directorate')->findOrFail($validated['sub_district_id']);
        $entityType = $validated['entity_type'] ?? 'قرية';

        // الحفظ في جدول sub_locations الجديد
        $code = SubLocation::generateCode($uzlah->id);
        $subLocation = SubLocation::firstOrCreate(
            [
                'uzlah_id' => $uzlah->id,
                'name_ar' => trim($validated['name']),
                'entity_type' => $entityType,
            ],
            [
                'directorate_id' => $uzlah->directorate_id,
                'governorate_id' => $uzlah->governorate_id,
                'code' => $code,
                'notes' => $validated['notes'] ?? null,
                'is_active' => 1,
            ]
        );

        // الحفظ في جدول villages القديم للتوافق
        Village::firstOrCreate(
            [
                'uzlah_id' => $uzlah->id,
                'name' => trim($validated['name']),
            ],
            [
                'sub_district_id' => $uzlah->id,
                'is_active' => true,
            ]
        );

        // إفراغ الكاش فوراً حتى تظهر البيانات الجديدة لحظياً
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget("geo_villages_{$uzlah->id}");

        return redirect()->back()->with('success', "تمت إضافة ({$subLocation->name_ar}) بنجاح بالكود: {$subLocation->code}");
    }

    /**
     * تعديل مسمى موقع فرعي أو نوعه
     */
    public function updateSubLocation(Request $request, int $id)
    {
        $subLocation = SubLocation::findOrFail($id);

        $validated = $request->validate([
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_locations')->where(function ($query) use ($request, $subLocation) {
                    return $query->where('uzlah_id', $subLocation->uzlah_id)
                                 ->where('entity_type', $request->input('entity_type', $subLocation->entity_type));
                })->ignore($subLocation->id),
            ],
            'entity_type' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $subLocation->update([
            'name_ar' => trim($validated['name_ar']),
            'entity_type' => trim($validated['entity_type']),
            'notes' => $validated['notes'] ?? null,
            'is_active' => isset($validated['is_active']) ? (int)$validated['is_active'] : 1,
        ]);

        // إفراغ الكاش
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget("geo_villages_{$subLocation->uzlah_id}");

        return redirect()->back()->with('success', "تم تحديث بيانات '{$subLocation->name_ar}' بنجاح.");
    }

    /**
     * حذف موقع فرعي
     */
    public function destroySubLocation(int $id)
    {
        $subLocation = SubLocation::findOrFail($id);
        $name = $subLocation->name_ar;
        $uzlahId = $subLocation->uzlah_id;
        $subLocation->delete();

        // إفراغ الكاش
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget("geo_villages_{$uzlahId}");

        return redirect()->back()->with('success', "تم حذف '{$name}' بنجاح.");
    }

    /**
     * إضافة مسميات وقرى بالجملة (Bulk Addition) تحت عزلة محددة
     */
    public function storeBulkSubLocations(Request $request)
    {
        $validated = $request->validate([
            'uzlah_id' => 'required|exists:uzlahs,id',
            'entity_type' => 'required|string|max:100',
            'names_list' => 'required|string',
        ]);

        $uzlah = Uzlah::findOrFail($validated['uzlah_id']);
        $rawLines = preg_split('/[\r\n,]+/', $validated['names_list']);
        $names = array_unique(array_filter(array_map('trim', $rawLines)));

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($names as $name) {
            if (empty($name)) continue;

            $exists = SubLocation::where('uzlah_id', $uzlah->id)
                ->where('name_ar', $name)
                ->where('entity_type', $validated['entity_type'])
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            $code = SubLocation::generateCode($uzlah->id);
            SubLocation::create([
                'uzlah_id' => $uzlah->id,
                'directorate_id' => $uzlah->directorate_id,
                'governorate_id' => $uzlah->governorate_id,
                'code' => $code,
                'name_ar' => $name,
                'entity_type' => $validated['entity_type'],
                'notes' => 'إضافة بالجملة',
                'is_active' => 1,
            ]);

            Village::firstOrCreate(
                [
                    'uzlah_id' => $uzlah->id,
                    'name' => $name,
                ],
                [
                    'sub_district_id' => $uzlah->id,
                    'is_active' => true,
                ]
            );

            $addedCount++;
        }

        // إفراغ الكاش فوراً
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget("geo_villages_{$uzlah->id}");

        return redirect()->back()->with('success', "تمت إضافة {$addedCount} مسمى بنجاح (تم تخطي {$skippedCount} مسمى مكرر مسبقاً).");
    }

    /**
     * معالجة استيراد ملف الإكسل مع الإدراج التلقائي
     */
    public function importExcel(Request $request, ExcelLocationImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|max:15360',
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'csv', 'txt', 'tsv'])) {
            return redirect()->back()->with('error', 'صيغة الملف غير مدعومة. يرجى اختيار ملف Excel (.xlsx) أو CSV (.csv).');
        }

        $result = $importService->import($request->file('file'));

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // إفراغ الكاش الإحصائي والجغرافي فوراً
        Cache::forget('geo_index_stats');
        Cache::forget('geo_manage_stats');
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');

        $stats = $result['stats'];
        $msg = "تم استيراد الملف وحفظ البيانات بنجاح! إجمالي الصفوف: {$stats['total']} | مسميات جديدة أُنشئت: {$stats['created']} | مسميات مطابقة وموجودة مسبقاً: {$stats['existing']}";

        if ($stats['failed'] > 0) {
            $msg .= " | صفوف غير مكتملة تم تخطيها: {$stats['failed']}";
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * تحميل نموذج إكسل تجريبي للاستيراد
     */
    public function downloadTemplate(): Response
    {
        $csvContent = "\xEF\xBB\xBF" . "المحافظة,المديرية,العزلة,القرية_أو_الجمعية,نوع_الكيان,ملاحظات\n";
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,قرية بيت الشاطبي,قرية,منطقة نموذجية\n";
        $csvContent .= "صنعاء,سنحان وبني بهلول,سنحان,جمعية سنحان التعاونية الزراعية,جمعية تعاونية,جمعية مسجلة رسمياً\n";
        $csvContent .= "عمران,عمران,عمران,قرية الورك,قرية,منطقة زراعية\n";
        $csvContent .= "أمانة العاصمة,السبعين,السبعين,جمعية الأمان التعاونية,جمعية تعاونية,خدمات تسويق زراعي\n";

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="hasad_locations_import_template.csv"',
        ]);
    }
}
