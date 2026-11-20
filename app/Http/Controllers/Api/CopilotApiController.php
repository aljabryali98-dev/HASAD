<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\AssociationLoan;
use App\Models\CommunityInitiative;
use App\Models\Governorate;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CopilotApiController extends Controller
{
    /**
     * معالجة استفسارات مساعد حصاد الذكي وتوليد تحليلات لحظية
     */
    public function query(Request $request): JsonResponse
    {
        $prompt = trim($request->input('prompt', ''));

        if (empty($prompt)) {
            return response()->json([
                'status' => 'error',
                'message' => 'يرجى كتابة سؤال أو اختيار أحد التحليلات السريعة.',
            ], 422);
        }

        $response = $this->generateInsight($prompt);

        return response()->json([
            'status' => 'success',
            'data' => $response,
        ]);
    }

    /**
     * محرك الاستدلال والتحليل الذكي للبيانات
     */
    protected function generateInsight(string $prompt): array
    {
        $p = mb_strtolower($prompt);

        // 1. فحص إذا كان السؤال يخص محافظة معينة
        $governorates = Governorate::where('is_active', true)->get(['id', 'name_ar']);
        foreach ($governorates as $gov) {
            if (mb_strpos($p, mb_strtolower($gov->name_ar)) !== false) {
                return $this->analyzeGovernorate($gov);
            }
        }

        // 2. فحص محاور التحليل المتخصصة
        if (mb_strpos($p, 'قرض') !== false || mb_strpos($p, 'قروض') !== false || mb_strpos($p, 'تمويل') !== false) {
            return $this->analyzeLoans();
        }

        if (mb_strpos($p, 'فارس') !== false || mb_strpos($p, 'فرسان') !== false || mb_strpos($p, 'كوادر') !== false || mb_strpos($p, 'تدريب') !== false) {
            return $this->analyzePersonnel();
        }

        if (mb_strpos($p, 'جمعية') !== false || mb_strpos($p, 'جمعيات') !== false || mb_strpos($p, 'تعاوني') !== false) {
            return $this->analyzeAssociations();
        }

        if (mb_strpos($p, 'مبادرة') !== false || mb_strpos($p, 'مبادرات') !== false || mb_strpos($p, 'مشروع') !== false || mb_strpos($p, 'مشاريع') !== false) {
            return $this->analyzeProjectsAndInitiatives();
        }

        // 3. تحليل شامل افتراضي للنظام الوطني
        return $this->analyzeOverview();
    }

    protected function analyzeGovernorate(Governorate $gov): array
    {
        $associationsCount = Association::where('governorate_id', $gov->id)->count();
        $personnelCount = UnifiedPersonnel::where('governorate_id', $gov->id)->count();
        $initiativesCount = CommunityInitiative::where('governorate_id', $gov->id)->count();
        $productiveGroupsCount = ProductiveGroup::where('governorate_id', $gov->id)->count();
        $loans = AssociationLoan::where('governorate_id', $gov->id)->get(['loan_amount', 'paid_amount', 'remaining_balance']);

        return [
            'type' => 'governorate',
            'title' => "التقرير التحليلي الذكي لمحافظة {$gov->name_ar}",
            'summary' => "تضم محافظة {$gov->name_ar} شبكة تنموية تحتوي على {$associationsCount} جمعية تعاونية، وعدد {$personnelCount} كادر وفارس تنموي، بالإضافة إلى {$initiativesCount} مبادرة مجتمعية و {$productiveGroupsCount} مجموعة إنتاجية.",
            'kpis' => [
                ['label' => 'الجمعيات التعاونية', 'value' => number_format($associationsCount)],
                ['label' => 'الفرسان والكوادر', 'value' => number_format($personnelCount)],
                ['label' => 'المبادرات المجتمعية', 'value' => number_format($initiativesCount)],
                ['label' => 'المجاميع الإنتاجية', 'value' => number_format($productiveGroupsCount)],
                ['label' => 'إجمالي التمويلات', 'value' => number_format($loans->sum('loan_amount')) . ' ر.ي'],
            ],
            'recommendations' => [
                "توسيع تفعيل الفرسان التنمويين الميدانيين في مديريات محافظة {$gov->name_ar}.",
                "ربط المجاميع الإنتاجية بالجمعيات التعاونية لتسهيل تسويق المحاصيل.",
            ],
            'action_link' => route('geo-explorer.index', ['gov_id' => $gov->id]),
            'action_label' => "فتح المستكشف الجغرافي لمحافظة {$gov->name_ar}",
        ];
    }

    protected function analyzeLoans(): array
    {
        $totalLoans = AssociationLoan::count();
        $totalAmount = AssociationLoan::sum('loan_amount');
        $paidAmount = AssociationLoan::sum('paid_amount');
        $remainingAmount = AssociationLoan::sum('remaining_balance');
        $recoveryRate = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 1) : 0;

        return [
            'type' => 'loans',
            'title' => 'التحليل المالي لمحفظة القروض التمويلية البيضاء (0% فوائد)',
            'summary' => "يبلغ إجمالي القروض البيضاء المسجلة {$totalLoans} قرضاً بقيمة إجمالية قدرها " . number_format($totalAmount) . " ر.ي، ونسبة سداد وتحصيل فعلية بلغت {$recoveryRate}% حتى تاريخه.",
            'kpis' => [
                ['label' => 'إجمالي القروض', 'value' => number_format($totalLoans) . ' قرض'],
                ['label' => 'إجمالي المبالغ', 'value' => number_format($totalAmount) . ' ر.ي'],
                ['label' => 'المبالغ المحصلة', 'value' => number_format($paidAmount) . ' ر.ي'],
                ['label' => 'الأرصدة المتبقية', 'value' => number_format($remainingAmount) . ' ر.ي'],
                ['label' => 'نسبة التحصيل', 'value' => "{$recoveryRate}%"],
            ],
            'recommendations' => [
                'الالتزام الصارم بمبدأ القرض الأبيض بدون فوائد لتشجيع الاستثمار الزراعي الأصيل.',
                'جدولة أقساط المزارعين المتعثرين بسبب الظروف المناخية أو شح الأمطار.',
            ],
            'action_link' => route('general.loans.index'),
            'action_label' => 'عرض سجل القروض البيضاء العام',
        ];
    }

    protected function analyzePersonnel(): array
    {
        $totalPersonnel = UnifiedPersonnel::count();
        $activatedPersonnel = UnifiedPersonnel::where('is_activated_in_field', true)->count();
        $knightsCount = UnifiedPersonnel::where('category', 'like', '%فرسان%')->count();
        $activationRate = $totalPersonnel > 0 ? round(($activatedPersonnel / $totalPersonnel) * 100, 1) : 0;

        return [
            'type' => 'personnel',
            'title' => 'التقرير الذكي للفرسان والكوادر التنموية واللجان الميدانية',
            'summary' => "تحتوي المنظومة على قاعدة بيانات متكاملة تضم {$totalPersonnel} كادر تنموي وفارس، منهم {$knightsCount} في مسارات فرسان التنمية بمختلف تخصصاتها الـ 17، بنسبة تفعيل ميداني بلغت {$activationRate}%.",
            'kpis' => [
                ['label' => 'إجمالي الكوادر', 'value' => number_format($totalPersonnel)],
                ['label' => 'فرسان الجمعيات', 'value' => number_format($knightsCount)],
                ['label' => 'المفعلين ميدانياً', 'value' => number_format($activatedPersonnel)],
                ['label' => 'نسبة التفعيل', 'value' => "{$activationRate}%"],
            ],
            'recommendations' => [
                'إطلاق دورات تدريبية تنشيطية للفرسان غير المفعلين لإشراكهم في حصر الأراضي والمحاصيل.',
                'استكمال الربط الجغرافي الرباعي لجميع الكوادر حتى مستوى القرية والمجموعة الإنتاجية.',
            ],
            'action_link' => route('personnel.index'),
            'action_label' => 'فتح سجل الكوادر والفرسان الموحد',
        ];
    }

    protected function analyzeAssociations(): array
    {
        $totalAssoc = Association::count();
        $activeAssoc = Association::where('status', 'نشطة')->count();
        $underSetup = Association::where('status', 'تحت التأسيس')->count();
        $totalCapital = Association::sum('current_capital');
        $totalMembers = Association::sum('total_members');

        return [
            'type' => 'associations',
            'title' => 'التحليل الاستراتيجي لقطاع الجمعيات التعاونية الزراعية',
            'summary' => "تضم المنظومة {$totalAssoc} جمعية تعاونية زراعية موثقة رسمياً، منها {$activeAssoc} جمعية نشطة تمارس دورها المؤسسي، بإجمالي رأس مال تعامل يتجاوز " . number_format($totalCapital) . " ر.ي وقاعدة منتسبين تبلغ " . number_format($totalMembers) . " عضو.",
            'kpis' => [
                ['label' => 'إجمالي الجمعيات', 'value' => number_format($totalAssoc)],
                ['label' => 'الجمعيات النشطة', 'value' => number_format($activeAssoc)],
                ['label' => 'تحت التأسيس', 'value' => number_format($underSetup)],
                ['label' => 'رأس المال الحالي', 'value' => number_format($totalCapital) . ' ر.ي'],
                ['label' => 'إجمالي الأعضاء', 'value' => number_format($totalMembers)],
            ],
            'recommendations' => [
                'تسريع إجراءات الجمعيات الواقعة تحت التأسيس واستكمال محاضرها التأسيسية.',
                'تشجيع الجمعيات على فتح سجلات المساهمين والاكتتابات لتنمية رؤوس أموالها.',
            ],
            'action_link' => route('associations.index'),
            'action_label' => 'استعراض دليل الجمعيات التعاونية',
        ];
    }

    protected function analyzeProjectsAndInitiatives(): array
    {
        $initiativesCount = CommunityInitiative::count();
        $totalCost = CommunityInitiative::sum('cost');
        $totalBeneficiaries = CommunityInitiative::sum('beneficiaries_count');

        return [
            'type' => 'initiatives',
            'title' => 'تقرير المبادرات المجتمعية والمشاريع التنموية',
            'summary' => "حققت المبادرات المجتمعية الموثقة في النظام طفرة كبرى بلغت {$initiativesCount} مبادرة مجتمعية ذاتية بإجمالي تكلفة تقدر بـ " . number_format($totalCost) . " ر.ي، خدمت أكثر من " . number_format($totalBeneficiaries) . " مستفيد.",
            'kpis' => [
                ['label' => 'المبادرات المجتمعية', 'value' => number_format($initiativesCount)],
                ['label' => 'التكلفة الإجمالية', 'value' => number_format($totalCost) . ' ر.ي'],
                ['label' => 'المستفيدين', 'value' => number_format($totalBeneficiaries) . ' فرد'],
            ],
            'recommendations' => [
                'توثيق قصص النجاح في المبادرات الزراعية ومشاريع الحصاد المائي وتعميمها.',
                'إسناد المبادرات المجتمعية بحوافز عينية كالبذور والشتلات والديزل.',
            ],
            'action_link' => route('general.initiatives.index'),
            'action_label' => 'عرض سجل المبادرات المجتمعية العام',
        ];
    }

    protected function analyzeOverview(): array
    {
        $assocCount = Association::count();
        $personnelCount = UnifiedPersonnel::count();
        $initiativesCount = CommunityInitiative::count();
        $loansCount = AssociationLoan::count();

        return [
            'type' => 'overview',
            'title' => 'الملخص التنفيذي المركزي للمنظومة الوطنية (حصاد ERP)',
            'summary' => "نظام حصاد يربط مركزياً بين {$assocCount} جمعية تعاونية زراعية، {$personnelCount} كادر وفارس تنموي، {$initiativesCount} مبادرة مجتمعية، و {$loansCount} قرضاً أبيض تمويلياً عبر 22 محافظة و 333 مديرية في الجمهورية اليمنية.",
            'kpis' => [
                ['label' => 'الجمعيات التعاونية', 'value' => number_format($assocCount)],
                ['label' => 'الفرسان والكوادر', 'value' => number_format($personnelCount)],
                ['label' => 'المبادرات المجتمعية', 'value' => number_format($initiativesCount)],
                ['label' => 'المحافظات المشمولة', 'value' => '22 محافظة'],
            ],
            'recommendations' => [
                'استخدام مستكشف النطاق الجغرافي للاطلاع على ملفات المحافظات والمديريات بضغطة زر واحدة.',
                'الاستفادة من محرك تقارير PDF الرسمية لاستخراج المستندات الميدانية المعتمدة.',
            ],
            'action_link' => route('dashboard'),
            'action_label' => 'الانتقال إلى لوحة القيادة المركزية',
        ];
    }
}
