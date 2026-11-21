<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\AssociationLoan;
use App\Models\CommunityInitiative;
use App\Models\Governorate;
use App\Models\Nursery;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CopilotApiController extends Controller
{
    /**
     * معالجة استفسارات مساعد حصاد الذكي وتوليد تحليلات لحظية دقيقة
     */
    public function query(Request $request): JsonResponse
    {
        $prompt = trim((string) $request->input('prompt', ''));

        if (empty($prompt)) {
            return response()->json([
                'status' => 'error',
                'message' => 'يرجى كتابة سؤال أو اختيار أحد التحليلات السريعة.',
            ], 422);
        }

        try {
            $user = auth()->user();
            $allowedGovIds = $this->resolveAllowedGovernorateIds($user);

            $response = $this->generateInsight($prompt, $allowedGovIds);

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error('Copilot Query Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'prompt' => $prompt,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في محرك التحليل الذكي: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * استخراج وتحديد معرفات المحافظات المصرحة للمستخدم
     * ترجع null إذا كان المستخدم مدير نظام مركزي أو يملك كافة الصلاحيات (*)،
     * وترجع مصفوفة أرقام [1, 2, ...] إذا كان مقيداً بمحافظات محددة.
     */
    protected function resolveAllowedGovernorateIds(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        // إذا كان النموذج يحتوي على الدالة الرسمية allowedGovernorateIds
        if (method_exists($user, 'allowedGovernorateIds')) {
            return $user->allowedGovernorateIds();
        }

        // فحص مدير النظام أو الرمز الشامل
        if ($user->isAdmin() || ($user->role ?? '') === 'super_admin' || in_array('*', $user->permissions ?? [], true)) {
            return null;
        }

        // علاقة المحافظات إذا كانت معرفة
        if (method_exists($user, 'governorates')) {
            return $user->governorates()->pluck('governorates.id')->map(fn($id) => (int)$id)->all();
        }

        return null;
    }

    /**
     * محرك الاستدلال والتحليل الذكي للبيانات مع مراعاة الصلاحيات الجغرافية
     */
    protected function generateInsight(string $prompt, ?array $allowedGovIds): array
    {
        $norm = $this->normalizeArabic($prompt);

        // 1. فحص إذا كان السؤال يخص محافظة معينة
        $governorates = Governorate::where('is_active', true)->get(['id', 'name_ar']);
        // ترتيب المحافظات بحسب طول الاسم تنازلياً لضمان مطابقة الأسماء المركبة مثل "أمانة العاصمة" أولاً
        $sortedGovs = $governorates->sortByDesc(fn($g) => mb_strlen($g->name_ar));

        foreach ($sortedGovs as $gov) {
            $govNorm = $this->normalizeArabic($gov->name_ar);
            if (mb_strpos($norm, $govNorm) !== false) {
                return $this->analyzeGovernorate($gov, $allowedGovIds);
            }
        }

        // 2. فحص محاور التحليل المتخصصة
        // المشاتل الزراعية (Nurseries)
        if (str_contains($norm, 'مشتل') || str_contains($norm, 'مشاتل') || str_contains($norm, 'شتل') || str_contains($norm, 'شتلات')) {
            return $this->analyzeNurseries($allowedGovIds);
        }

        // المجاميع الإنتاجية (Productive Groups)
        if (str_contains($norm, 'مجموع') || str_contains($norm, 'مجاميع') || str_contains($norm, 'انتاجي')) {
            return $this->analyzeProductiveGroups($allowedGovIds);
        }

        // القروض التمويلية البيضاء (White Loans)
        if (str_contains($norm, 'قرض') || str_contains($norm, 'قروض') || str_contains($norm, 'تمويل') || str_contains($norm, 'سداد') || str_contains($norm, 'بيضاء')) {
            return $this->analyzeLoans($allowedGovIds);
        }

        // الفرسان والكوادر التنموية (Personnel & Knights)
        if (str_contains($norm, 'فارس') || str_contains($norm, 'فرسان') || str_contains($norm, 'كوادر') || str_contains($norm, 'كادر') || str_contains($norm, 'تدريب') || str_contains($norm, 'تفعيل')) {
            return $this->analyzePersonnel($allowedGovIds);
        }

        // الجمعيات التعاونية (Cooperative Associations)
        if (str_contains($norm, 'جمعيه') || str_contains($norm, 'جمعيات') || str_contains($norm, 'تعاوني') || str_contains($norm, 'مساهم') || str_contains($norm, 'راس مال')) {
            return $this->analyzeAssociations($allowedGovIds);
        }

        // المبادرات والمشاريع التنموية (Initiatives & Projects)
        if (str_contains($norm, 'مبادر') || str_contains($norm, 'مبادرات') || str_contains($norm, 'مشروع') || str_contains($norm, 'مشاريع')) {
            return $this->analyzeProjectsAndInitiatives($allowedGovIds);
        }

        // 3. تحليل شامل افتراضي للنظام الوطني أو النطاق المصرح
        return $this->analyzeOverview($allowedGovIds);
    }

    /**
     * تطبيع وتوحيد النصوص العربية لدقة المطابقة الدلالية
     */
    protected function normalizeArabic(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        // توحيد الألف
        $text = preg_replace('/[أإآٱ]/u', 'ا', $text);
        // توحيد التاء المربوطة
        $text = preg_replace('/[ة]/u', 'ه', $text);
        // توحيد الياء والألف المقصورة
        $text = preg_replace('/[ى]/u', 'ي', $text);
        // إزالة التشكيل
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        // إزالة التطويل (الكشيدة)
        $text = preg_replace('/ـ+/u', '', $text);

        return trim($text);
    }

    /**
     * تحليل مؤشرات محافظة معينة مع التحقق الإلزامي من الصلاحيات الجغرافية
     */
    protected function analyzeGovernorate(Governorate $gov, ?array $allowedGovIds): array
    {
        // التحقق الأمني: إذا كان المستخدم مقيداً بنطاق محافظات معينة ولا تشمل هذه المحافظة
        if ($allowedGovIds !== null && !in_array((int)$gov->id, $allowedGovIds, true)) {
            $allowedGovs = Governorate::whereIn('id', $allowedGovIds)->pluck('name_ar')->all();
            $allowedList = !empty($allowedGovs) ? implode('، ', $allowedGovs) : 'لا توجد محافظات مخصصة لحسابك حالياً';

            return [
                'type' => 'restricted',
                'title' => "🔒 تنبيه الصلاحيات الجغرافية: محافظة {$gov->name_ar}",
                'summary' => "عذراً، لا تملك صلاحية الاطلاع على بيانات محافظة {$gov->name_ar}. نطاقك الجغرافي المصرح يقتصر على: ({$allowedList}). تم حجب البيانات التزاماً بسياسات الخصوصية والتقييد الجغرافي.",
                'kpis' => [
                    ['label' => 'حالة الوصول', 'value' => 'محظور جغرافياً'],
                    ['label' => 'المحافظات المصرحة لك', 'value' => count($allowedGovIds) . ' محافظة'],
                ],
                'recommendations' => [
                    'يمكنك الاستفسار عن مؤشرات المحافظات الواقعة ضمن نطاقك الجغرافي المصرح فقط.',
                    'إذا كنت بحاجة إلى تفويض للاطلاع على هذه المحافظة، يرجى التنسيق مع مدير النظام المركزي.',
                ],
                'action_link' => route('dashboard'),
                'action_label' => 'الانتقال إلى لوحة المؤشرات المصرحة',
            ];
        }

        // استعلامات سريعة ومباشرة على مستوى المحافظة
        $associationsCount = Association::where('governorate_id', $gov->id)->count();
        $activeAssoc = Association::where('governorate_id', $gov->id)->where('status', 'نشطة')->count();
        $capital = Association::where('governorate_id', $gov->id)->sum('current_capital');

        $groupsCount = ProductiveGroup::where('governorate_id', $gov->id)->count();
        $activeGroups = ProductiveGroup::where('governorate_id', $gov->id)->where('status', 'نشطة')->count();

        $personnelCount = UnifiedPersonnel::where('governorate_id', $gov->id)->count();
        $knightsCount = UnifiedPersonnel::where('governorate_id', $gov->id)->where('category', 'like', '%فرسان%')->count();
        $activatedPersonnel = UnifiedPersonnel::where('governorate_id', $gov->id)->where('is_activated_in_field', true)->count();

        $initiativesCount = CommunityInitiative::where('governorate_id', $gov->id)->count();
        $initiativesCost = CommunityInitiative::where('governorate_id', $gov->id)->sum('cost');

        $nurseriesCount = Nursery::where('governorate_id', $gov->id)->count();
        $nurseriesCapacity = Nursery::where('governorate_id', $gov->id)->sum('capacity');

        $loansCount = AssociationLoan::where('governorate_id', $gov->id)->count();
        $loansTotal = AssociationLoan::where('governorate_id', $gov->id)->sum('loan_amount');
        $loansPaid = AssociationLoan::where('governorate_id', $gov->id)->sum('paid_amount');
        $recoveryRate = $loansTotal > 0 ? round(($loansPaid / $loansTotal) * 100, 1) : 0;

        return [
            'type' => 'governorate',
            'title' => "التقرير التحليلي الذكي لمحافظة {$gov->name_ar}",
            'summary' => "تضم محافظة {$gov->name_ar} شبكة تنموية تشمل {$associationsCount} جمعية تعاونية (منها {$activeAssoc} نشطة برأس مال " . number_format($capital) . " ر.ي)، و {$groupsCount} مجموعة إنتاجية، و {$personnelCount} كادر وفارس تنموي (منهم {$activatedPersonnel} مفعلين ميدانياً)، بالإضافة إلى {$initiativesCount} مبادرة مجتمعية، و {$nurseriesCount} مشتل بطاقة استيعابية " . number_format($nurseriesCapacity) . " شتلة، ومحفظة قروض بيضاء بقيمة " . number_format($loansTotal) . " ر.ي بنسبة تحصيل {$recoveryRate}%.",
            'kpis' => [
                ['label' => 'الجمعيات التعاونية', 'value' => number_format($associationsCount) . ' جمعية'],
                ['label' => 'المجاميع الإنتاجية', 'value' => number_format($groupsCount) . ' مجموعة'],
                ['label' => 'الفرسان والكوادر', 'value' => number_format($personnelCount) . ' كادر'],
                ['label' => 'المشاتل الزراعية', 'value' => number_format($nurseriesCount) . ' مشتل'],
                ['label' => 'المبادرات المجتمعية', 'value' => number_format($initiativesCount) . ' مبادرة'],
                ['label' => 'إجمالي التمويلات', 'value' => number_format($loansTotal) . ' ر.ي'],
            ],
            'recommendations' => [
                "توسيع تفعيل الفرسان التنمويين الميدانيين في مديريات محافظة {$gov->name_ar}.",
                "ربط المجاميع الإنتاجية بالجمعيات التعاونية لتسهيل تسويق المحاصيل ودعم سلاسل القيمة.",
                "الاستفادة من المشاتل الزراعية المحلية لتزويد المزارعين بالشتلات المحسنة بأسعار التكلفة.",
            ],
            'action_link' => route('geo-explorer.index', ['gov_id' => $gov->id]),
            'action_label' => "فتح المستكشف الجغرافي لمحافظة {$gov->name_ar}",
        ];
    }

    /**
     * تحليل منظومة المشاتل الزراعية
     */
    protected function analyzeNurseries(?array $allowedGovIds): array
    {
        $query = Nursery::query();
        if ($allowedGovIds !== null) {
            $query->whereIn('governorate_id', $allowedGovIds);
        }

        $totalNurseries = (clone $query)->count();
        $activeNurseries = (clone $query)->where('status', 'مفعل')->count();
        $coopNurseries = (clone $query)->where('ownership_type', 'تعاوني')->count();
        $totalCapacity = (clone $query)->sum('capacity');

        $scopeDesc = $allowedGovIds !== null ? 'ضمن نطاق المحافظات المصرحة لحسابك' : 'على الصعيد الوطني العام';

        return [
            'type' => 'nurseries',
            'title' => 'التقرير الذكي لمنظومة المشاتل الزراعية وإنتاج الشتلات',
            'summary' => "تضم شبكة المشاتل {$scopeDesc} عدداً قدره {$totalNurseries} مشتل زراعي، من بينها {$activeNurseries} مشتل مفعل بطاقة إنتاجية إجمالية تبلغ " . number_format($totalCapacity) . " شتلة، وتستحوذ المشاتل التعاونية على {$coopNurseries} مشتل تخدم التوسع الزراعي وتحسين الأصناف.",
            'kpis' => [
                ['label' => 'إجمالي المشاتل', 'value' => number_format($totalNurseries) . ' مشتل'],
                ['label' => 'المشاتل المفعلة', 'value' => number_format($activeNurseries) . ' مشتل'],
                ['label' => 'المشاتل التعاونية', 'value' => number_format($coopNurseries) . ' مشتل'],
                ['label' => 'الطاقة الإنتاجية', 'value' => number_format($totalCapacity) . ' شتلة'],
            ],
            'recommendations' => [
                'توسيع طاقة المشاتل التعاونية لإنتاج شتلات المحاصيل الاستراتيجية والأشجار المثمرة والمقاومة للجفاف.',
                'ربط المشاتل بالمجاميع الإنتاجية والجمعيات لتأمين الشتلات الزراعية بأسعار مدعومة.',
            ],
            'action_link' => route('general.nurseries.index'),
            'action_label' => 'استعراض سجل المشاتل الزراعية',
        ];
    }

    /**
     * تحليل المجاميع الإنتاجية
     */
    protected function analyzeProductiveGroups(?array $allowedGovIds): array
    {
        $query = ProductiveGroup::query();
        if ($allowedGovIds !== null) {
            $query->whereIn('governorate_id', $allowedGovIds);
        }

        $totalGroups = (clone $query)->count();
        $activeGroups = (clone $query)->where('status', 'نشطة')->count();
        $totalMembers = (clone $query)->sum('total_members');
        $avgMembers = $totalGroups > 0 ? round($totalMembers / $totalGroups) : 0;

        $scopeDesc = $allowedGovIds !== null ? 'ضمن المحافظات المصرحة لك' : 'على مستوى الجمهورية';

        return [
            'type' => 'productive_groups',
            'title' => 'التقرير الذكي للمجاميع الإنتاجية المجتمعية',
            'summary' => "تم حصر وتوثيق {$totalGroups} مجموعة إنتاجية {$scopeDesc}، تضم في عضويتها " . number_format($totalMembers) . " عضواً ومشاركاً، من بينها {$activeGroups} مجموعة نشطة بمعدل {$avgMembers} منتسباً لكل مجموعة.",
            'kpis' => [
                ['label' => 'إجمالي المجاميع', 'value' => number_format($totalGroups) . ' مجموعة'],
                ['label' => 'المجاميع النشطة', 'value' => number_format($activeGroups) . ' مجموعة'],
                ['label' => 'إجمالي المنتسبين', 'value' => number_format($totalMembers) . ' عضو'],
                ['label' => 'متوسط الأعضاء', 'value' => number_format($avgMembers) . ' عضو/مجموعة'],
            ],
            'recommendations' => [
                'تعزيز التكامل بين المجاميع الإنتاجية والجمعيات التعاونية لفتح نوافذ تسويقية دائمة وتأمين المدخلات.',
                'إدراج منتسبي المجاميع في برامج فرسان التنمية والتأهيل الحرفي والزراعي المتخصص.',
            ],
            'action_link' => route('productive-groups.index'),
            'action_label' => 'فتح سجل المجاميع الإنتاجية',
        ];
    }

    /**
     * تحليل القروض البيضاء
     */
    protected function analyzeLoans(?array $allowedGovIds): array
    {
        $query = AssociationLoan::query();
        if ($allowedGovIds !== null) {
            $query->whereIn('governorate_id', $allowedGovIds);
        }

        $totalLoans = (clone $query)->count();
        $totalAmount = (clone $query)->sum('loan_amount');
        $paidAmount = (clone $query)->sum('paid_amount');
        $remainingAmount = (clone $query)->sum('remaining_balance');
        $recoveryRate = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 1) : 0;

        $scopeDesc = $allowedGovIds !== null ? 'ضمن نطاق المحافظات المصرحة لك' : 'على الصعيد الوطني';

        return [
            'type' => 'loans',
            'title' => 'التحليل المالي لمحفظة القروض التمويلية البيضاء (0% فوائد)',
            'summary' => "يبلغ إجمالي القروض البيضاء المسجلة {$scopeDesc} {$totalLoans} قرضاً بقيمة إجمالية قدرها " . number_format($totalAmount) . " ر.ي، ونسبة سداد وتحصيل فعلية بلغت {$recoveryRate}% حتى تاريخه.",
            'kpis' => [
                ['label' => 'إجمالي القروض', 'value' => number_format($totalLoans) . ' قرض'],
                ['label' => 'إجمالي المبالغ', 'value' => number_format($totalAmount) . ' ر.ي'],
                ['label' => 'المبالغ المحصلة', 'value' => number_format($paidAmount) . ' ر.ي'],
                ['label' => 'الأرصدة المتبقية', 'value' => number_format($remainingAmount) . ' ر.ي'],
                ['label' => 'نسبة التحصيل', 'value' => "{$recoveryRate}%"],
            ],
            'recommendations' => [
                'الالتزام الصارم بمبدأ القرض الأبيض بدون فوائد لتشجيع الإنتاج الزراعي والتمكين الاقتصادي.',
                'جدولة أقساط المزارعين المتأثرين بالظروف المناخية مع المحافظة على انتظام التدوير المالي.',
            ],
            'action_link' => route('general.loans.index'),
            'action_label' => 'عرض سجل القروض البيضاء العام',
        ];
    }

    /**
     * تحليل الفرسان والكوادر التنموية
     */
    protected function analyzePersonnel(?array $allowedGovIds): array
    {
        $query = UnifiedPersonnel::query();
        if ($allowedGovIds !== null) {
            $query->whereIn('governorate_id', $allowedGovIds);
        }

        $totalPersonnel = (clone $query)->count();
        $activatedPersonnel = (clone $query)->where('is_activated_in_field', true)->count();
        $knightsCount = (clone $query)->where('category', 'like', '%فرسان%')->count();
        $activationRate = $totalPersonnel > 0 ? round(($activatedPersonnel / $totalPersonnel) * 100, 1) : 0;

        $scopeDesc = $allowedGovIds !== null ? 'ضمن المحافظات المصرحة لحسابك' : 'في المنظومة الوطنية';

        return [
            'type' => 'personnel',
            'title' => 'التقرير الذكي للفرسان والكوادر التنموية واللجان الميدانية',
            'summary' => "تحتوي قاعدة البيانات {$scopeDesc} على {$totalPersonnel} كادر وفارس تنموي، من بينهم {$knightsCount} في مسارات فرسان التنمية بمختلف التخصصات، بنسبة تفعيل ميداني بلغت {$activationRate}%.",
            'kpis' => [
                ['label' => 'إجمالي الكوادر', 'value' => number_format($totalPersonnel)],
                ['label' => 'فرسان الجمعيات', 'value' => number_format($knightsCount)],
                ['label' => 'المفعلين ميدانياً', 'value' => number_format($activatedPersonnel)],
                ['label' => 'نسبة التفعيل', 'value' => "{$activationRate}%"],
            ],
            'recommendations' => [
                'إطلاق دورات تدريبية تنشيطية للفرسان غير المفعلين لإشراكهم في حصر الأراضي والمحاصيل الموسمية.',
                'استكمال الربط الجغرافي الرباعي لجميع الكوادر حتى مستوى القرية والمجموعة الإنتاجية.',
            ],
            'action_link' => route('personnel.index'),
            'action_label' => 'فتح سجل الكوادر والفرسان الموحد',
        ];
    }

    /**
     * تحليل الجمعيات التعاونية
     */
    protected function analyzeAssociations(?array $allowedGovIds): array
    {
        $query = Association::query();
        if ($allowedGovIds !== null) {
            $query->whereIn('governorate_id', $allowedGovIds);
        }

        $totalAssoc = (clone $query)->count();
        $activeAssoc = (clone $query)->where('status', 'نشطة')->count();
        $underSetup = (clone $query)->where('status', 'تحت التأسيس')->count();
        $totalCapital = (clone $query)->sum('current_capital');
        $totalMembers = (clone $query)->sum('total_members');

        $scopeDesc = $allowedGovIds !== null ? 'في النطاق الجغرافي المصرح لك' : 'على الصعيد الوطني';

        return [
            'type' => 'associations',
            'title' => 'التحليل الاستراتيجي لقطاع الجمعيات التعاونية الزراعية',
            'summary' => "تضم المنظومة {$scopeDesc} {$totalAssoc} جمعية تعاونية زراعية موثقة، منها {$activeAssoc} جمعية نشطة تمارس دورها المؤسسي، بإجمالي رأس مال تعامل يبلغ " . number_format($totalCapital) . " ر.ي، وقاعدة منتسبين ومساهمين تبلغ " . number_format($totalMembers) . " عضواً.",
            'kpis' => [
                ['label' => 'إجمالي الجمعيات', 'value' => number_format($totalAssoc)],
                ['label' => 'الجمعيات النشطة', 'value' => number_format($activeAssoc)],
                ['label' => 'تحت التأسيس', 'value' => number_format($underSetup)],
                ['label' => 'رأس المال الحالي', 'value' => number_format($totalCapital) . ' ر.ي'],
                ['label' => 'إجمالي الأعضاء', 'value' => number_format($totalMembers)],
            ],
            'recommendations' => [
                'تسريع إجراءات الجمعيات الواقعة تحت التأسيس واستكمال لوائحها ومحاضرها التأسيسية.',
                'تشجيع الجمعيات على فتح سجلات الاكتتاب وتنمية رؤوس أموالها عبر اشتراكات الأعضاء.',
            ],
            'action_link' => route('associations.index'),
            'action_label' => 'استعراض دليل الجمعيات التعاونية',
        ];
    }

    /**
     * تحليل المشاريع والمبادرات المجتمعية
     */
    protected function analyzeProjectsAndInitiatives(?array $allowedGovIds): array
    {
        $query = CommunityInitiative::query();
        if ($allowedGovIds !== null) {
            $query->whereIn('governorate_id', $allowedGovIds);
        }

        $initiativesCount = (clone $query)->count();
        $totalCost = (clone $query)->sum('cost');
        $totalBeneficiaries = (clone $query)->sum('beneficiaries_count');

        $scopeDesc = $allowedGovIds !== null ? 'ضمن المحافظات المصرحة لك' : 'في المنظومة الوطنية';

        return [
            'type' => 'initiatives',
            'title' => 'تقرير المبادرات المجتمعية والمشاريع التنموية',
            'summary' => "حققت المبادرات المجتمعية الذاتية {$scopeDesc} رصيداً بلغ {$initiativesCount} مبادرة، بإجمالي تكلفة تقدر بـ " . number_format($totalCost) . " ر.ي، خدمت أكثر من " . number_format($totalBeneficiaries) . " مستفيد.",
            'kpis' => [
                ['label' => 'المبادرات المجتمعية', 'value' => number_format($initiativesCount)],
                ['label' => 'التكلفة الإجمالية', 'value' => number_format($totalCost) . ' ر.ي'],
                ['label' => 'المستفيدين', 'value' => number_format($totalBeneficiaries) . ' فرد'],
            ],
            'recommendations' => [
                'توثيق قصص النجاح في المبادرات الزراعية ومشاريع الحصاد المائي وتعميمها.',
                'إسناد المبادرات المجتمعية بحوافز عينية كالبذور والشتلات والديزل بالتنسيق مع الجمعيات.',
            ],
            'action_link' => route('general.initiatives.index'),
            'action_label' => 'عرض سجل المبادرات المجتمعية العام',
        ];
    }

    /**
     * التحليل الشامل للمنظومة الوطنية أو النطاق الجغرافي المصرح
     */
    protected function analyzeOverview(?array $allowedGovIds): array
    {
        $assocQuery = Association::query();
        $groupsQuery = ProductiveGroup::query();
        $personnelQuery = UnifiedPersonnel::query();
        $initiativesQuery = CommunityInitiative::query();
        $loansQuery = AssociationLoan::query();
        $nurseriesQuery = Nursery::query();

        if ($allowedGovIds !== null) {
            if (empty($allowedGovIds)) {
                return [
                    'type' => 'overview',
                    'title' => 'تنبيه: لا توجد محافظات مخصصة لحسابك',
                    'summary' => 'حسابك مفعل ولكن لم يتم ربطه بأي محافظات مصرحة حتى الآن من قِبل إدارة النظام. يرجى التواصل مع مسؤول النظام لتحديد المحافظات المصرحة لك.',
                    'kpis' => [
                        ['label' => 'المحافظات المصرحة', 'value' => '0 محافظة'],
                        ['label' => 'حالة الوصول', 'value' => 'بانتظار التخصيص'],
                    ],
                    'recommendations' => [
                        'مراجعة مسؤول النظام المركزي لتحديد المحافظات التابعة لعملك الميداني.',
                    ],
                    'action_link' => route('dashboard'),
                    'action_label' => 'العودة للوحة الرئيسية',
                ];
            }

            $assocQuery->whereIn('governorate_id', $allowedGovIds);
            $groupsQuery->whereIn('governorate_id', $allowedGovIds);
            $personnelQuery->whereIn('governorate_id', $allowedGovIds);
            $initiativesQuery->whereIn('governorate_id', $allowedGovIds);
            $loansQuery->whereIn('governorate_id', $allowedGovIds);
            $nurseriesQuery->whereIn('governorate_id', $allowedGovIds);

            $govNames = Governorate::whereIn('id', $allowedGovIds)->pluck('name_ar')->all();
            $govsStr = count($allowedGovIds) . ' محافظة (' . implode('، ', $govNames) . ')';
            $title = 'الملخص التنفيذي للنطاق الجغرافي المصرح (حصاد ERP)';
            $scopeText = "ضمن نطاق صلاحياتك الجغرافية ({$govsStr})";
        } else {
            $govsStr = '22 محافظة في الجمهورية اليمنية';
            $title = 'الملخص التنفيذي المركزي للمنظومة الوطنية (حصاد ERP)';
            $scopeText = 'عبر 22 محافظة و 333 مديرية في الجمهورية اليمنية';
        }

        $assocCount = $assocQuery->count();
        $groupsCount = $groupsQuery->count();
        $personnelCount = $personnelQuery->count();
        $initiativesCount = $initiativesQuery->count();
        $loansCount = $loansQuery->count();
        $nurseriesCount = $nurseriesQuery->count();

        return [
            'type' => 'overview',
            'title' => $title,
            'summary' => "نظام حصاد يربط بين {$assocCount} جمعية تعاونية زراعية، {$groupsCount} مجموعة إنتاجية، {$personnelCount} كادر وفارس تنموي، {$initiativesCount} مبادرة مجتمعية، {$loansCount} قرضاً أبيض، و {$nurseriesCount} مشتل زراعي {$scopeText}.",
            'kpis' => [
                ['label' => 'الجمعيات التعاونية', 'value' => number_format($assocCount)],
                ['label' => 'المجاميع الإنتاجية', 'value' => number_format($groupsCount)],
                ['label' => 'الفرسان والكوادر', 'value' => number_format($personnelCount)],
                ['label' => 'المشاتل الزراعية', 'value' => number_format($nurseriesCount)],
                ['label' => 'المبادرات المجتمعية', 'value' => number_format($initiativesCount)],
                ['label' => 'القروض البيضاء', 'value' => number_format($loansCount)],
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
