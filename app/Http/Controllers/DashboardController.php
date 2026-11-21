<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\AssociationCrop;
use App\Models\AssociationLoan;
use App\Models\AssociationProject;
use App\Models\CommunityInitiative;
use App\Models\FarmerAndLand;
use App\Models\Governorate;
use App\Models\Nursery;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Traits\ScopeByGovernorate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main executive statistics dashboard.
     * فائقة السرعة ولحظية الاستجابة بفضل التخزين المؤقت الذكي مع دعم التقييد الجغرافي للمحافظات.
     */
    public function index(): View
    {
        $user = Auth::user();
        $allowedGovs = $user?->allowedGovernorateIds();
        $govCacheSuffix = $allowedGovs === null ? 'all' : (empty($allowedGovs) ? 'none' : implode('_', $allowedGovs));
        $kpiCacheKey = "dashboard_kpis_{$govCacheSuffix}";

        // إمكانية فرض تحديث البيانات فوراً عند تمرير ?fresh=1
        if (request()->boolean('fresh')) {
            Cache::forget($kpiCacheKey);
            Cache::forget('dashboard_kpis');
            Cache::forget('dashboard_recent');
        }

        // 1. المؤشرات الإحصائية العامة المركزية مقيدة بنطاق المحافظات المصرح بها
        $kpis = Cache::remember($kpiCacheKey, 300, function () {
            // إحصائيات الجمعيات التعاونية
            $assocStats = Association::forUserGovernorates()->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'نشطة' THEN 1 END) as active,
                COALESCE(SUM(current_capital), 0) as total_capital
            ")->first();

            // إحصائيات المجاميع الإنتاجية
            $groupStats = ProductiveGroup::forUserGovernorates()->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(total_members), 0) as total_members,
                COALESCE(SUM(productive_area), 0) as total_area
            ")->first();

            // إحصائيات الكوادر والفرسان
            $personnelStats = UnifiedPersonnel::forUserGovernorates()->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN category = 'فرسان التنمية' THEN 1 END) as knights,
                COUNT(CASE WHEN category = 'باحثين' THEN 1 END) as researchers,
                COUNT(CASE WHEN category = 'مدربين' THEN 1 END) as trainers
            ")->first();

            // إحصائيات المشاريع والمزارعين
            $projectStats = AssociationProject::forUserGovernorates()->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(cost), 0) as total_cost
            ")->first();

            $farmerStats = FarmerAndLand::forUserGovernorates()->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(cultivated_area), 0) as total_area
            ")->first();

            $nurseriesCount = Nursery::forUserGovernorates()->count();
            $initiativesCount = CommunityInitiative::forUserGovernorates()->count();

            // إحصائيات القروض البيضاء
            $loanStats = AssociationLoan::forUserGovernorates()->selectRaw("
                COUNT(*) as total,
                COALESCE(SUM(loan_amount), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(remaining_balance), 0) as total_remaining
            ")->first();

            $governoratesCount = ScopeByGovernorate::allowedGovernorates()->count();

            return [
                'associations_count' => (int) ($assocStats->total ?? 0),
                'active_associations' => (int) ($assocStats->active ?? 0),
                'total_capital' => (float) ($assocStats->total_capital ?? 0),
                'groups_count' => (int) ($groupStats->total ?? 0),
                'group_members' => (int) ($groupStats->total_members ?? 0),
                'group_area' => (float) ($groupStats->total_area ?? 0),
                'personnel_count' => (int) ($personnelStats->total ?? 0),
                'knights_count' => (int) ($personnelStats->knights ?? 0),
                'researchers_count' => (int) ($personnelStats->researchers ?? 0),
                'trainers_count' => (int) ($personnelStats->trainers ?? 0),
                'projects_count' => (int) ($projectStats->total ?? 0),
                'projects_cost' => (float) ($projectStats->total_cost ?? 0),
                'farmers_count' => (int) ($farmerStats->total ?? 0),
                'cultivated_area' => (float) ($farmerStats->total_area ?? 0),
                'nurseries_count' => (int) $nurseriesCount,
                'initiatives_count' => (int) $initiativesCount,
                'loans_count' => (int) ($loanStats->total ?? 0),
                'loans_amount' => (float) ($loanStats->total_amount ?? 0),
                'loans_paid' => (float) ($loanStats->total_paid ?? 0),
                'loans_remaining' => (float) ($loanStats->total_remaining ?? 0),
                'governorates_count' => (int) $governoratesCount,
            ];
        });

        // 2. آخر السجلات المضافة للمحافظات المصرح بها
        $recentAssociations = Association::forUserGovernorates()
            ->select(['id', 'code', 'name', 'status', 'governorate_id', 'directorate_id', 'created_at'])
            ->with(['governorate:id,name_ar,name', 'directorate:id,name_ar,name'])
            ->latest('id')
            ->take(5)
            ->get();

        $recentGroups = ProductiveGroup::forUserGovernorates()
            ->select(['id', 'code', 'name', 'total_members', 'status', 'association_id', 'governorate_id', 'created_at'])
            ->with(['association:id,name'])
            ->latest('id')
            ->take(5)
            ->get();

        $recentProjects = AssociationProject::forUserGovernorates()
            ->select(['id', 'project_name', 'cost', 'execution_status', 'association_id', 'governorate_id', 'created_at'])
            ->with(['association:id,name'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard', compact('kpis', 'recentAssociations', 'recentGroups', 'recentProjects'));
    }
}
