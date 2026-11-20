<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use App\Services\ExcelLocationImportService;

class SmartImportController extends Controller
{
    /**
     * عرض لوحة مركز الاستيراد الذكي الشامل
     */
    public function index(): View
    {
        // إحصائيات السجلات المسكنة حالياً في النظام
        $stats = [
            'associations' => DB::table('associations')->count(),
            'board_members' => DB::table('association_board_members')->count(),
            'projects' => DB::table('association_projects')->count(),
            'initiatives' => DB::table('community_initiatives')->count(),
            'personnel' => DB::table('unified_personnel')->count(),
            'loans' => DB::table('association_loans')->count(),
            'land_reclamations' => DB::table('land_reclamations')->count(),
            'contract_farming' => DB::table('contract_farming')->count(),
            'crop_expansions' => DB::table('crop_expansions')->count(),
            'nurseries' => DB::table('nurseries')->count(),
            'dairy_livestock' => DB::table('dairy_and_livestocks')->count(),
            'studies' => DB::table('studies_and_research')->count(),
            'sub_locations' => DB::table('sub_locations')->count(),
            'villages' => DB::table('villages')->count(),
        ];

        $masterFileExists = file_exists('C:\\Users\\Ali\\Desktop\\بيانات.xlsx');
        $masterFileSize = $masterFileExists ? round(filesize('C:\\Users\\Ali\\Desktop\\بيانات.xlsx') / 1024 / 1024, 2) : 0;

        return view('import.smart', compact('stats', 'masterFileExists', 'masterFileSize'));
    }

    /**
     * معالجة استيراد ملف إكسل عبر محرك الاستيراد الذكي
     */
    public function upload(Request $request, ExcelLocationImportService $importService)
    {
        $request->validate([
            'excel_file' => 'required|file|max:51200', // حتى 50 ميجابايت
        ], [
            'excel_file.required' => 'يرجى اختيار ملف الإكسل أو الـ CSV المراد استيراده.',
            'excel_file.max' => 'حجم الملف يتجاوز الحد المسموح به (50 ميجابايت).',
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt', 'tsv'])) {
            return redirect()->back()->with('error', 'صيغة الملف غير مدعومة. يرجى رفع ملف بصيغة XLSX أو CSV.');
        }

        $result = $importService->import($file);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error'] ?? 'فشلت عملية الاستيراد الذكي.');
        }

        $created = $result['stats']['created'] ?? 0;
        $updated = $result['stats']['updated'] ?? 0;
        $skipped = $result['stats']['skipped'] ?? 0;

        // إفراغ الكاشات لتظهر البيانات فوراً
        Cache::flush();

        return redirect()->back()->with('success', "تم اكتمال الاستيراد الذكي بنجاح! تم حفظ وتسكين {$created} سجلاً جديداً وتحديث {$updated}، وتم تخطي {$skipped} سجلاً مكرراً.");
    }

    /**
     * تشغيل محرك التسكين الآلي لملف بيانات.xlsx الرئيسي
     */
    public function runMaster(Request $request)
    {
        $filePath = 'C:\\Users\\Ali\\Desktop\\بيانات.xlsx';

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'ملف البيانات الرئيسي (بيانات.xlsx) غير موجود على سطح المكتب.');
        }

        try {
            Artisan::call('hasad:import-master-data', ['file' => $filePath]);
            Cache::flush();

            return redirect()->back()->with('success', 'تم تشغيل وتسكين قاعدة البيانات الكبرى بالكامل بنجاح من ملف بيانات.xlsx!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تشغيل محرك التسكين: ' . $e->getMessage());
        }
    }
}
