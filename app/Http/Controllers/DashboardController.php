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
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main executive statistics dashboard.
     */
    public function index(): View
    {
        // 1. إحصائيات الجمعيات التعاونية
        $associationsCount = Association::count();
        $activeAssociations = Association::where('status', 'نشطة')->count();
        $totalCapital = Association::sum('current_capital');

        // 2. إحصائيات المجاميع الإنتاجية
        $groupsCount = ProductiveGroup::count();
        $totalGroupMembers = ProductiveGroup::sum('members_count');
        $totalGroupArea = ProductiveGroup::sum('total_area');

        // 3. إحصائيات الكوادر والفرسان
        $personnelCount = UnifiedPersonnel::count();
        $knightsCount = UnifiedPersonnel::where('category', 'فرسان التنمية')->count();
        $researchersCount = UnifiedPersonnel::where('category', 'باحثين')->count();
        $trainersCount = UnifiedPersonnel::where('category', 'مدربين')->count();

        // 4. إحصائيات الإنتاج والمزارعين والمشاريع
        $projectsCount = AssociationProject::count();
        $projectsCost = AssociationProject::sum('cost');
        $farmersCount = FarmerAndLand::count();
        $cultivatedArea = FarmerAndLand::sum('cultivated_area');
        $nurseriesCount = Nursery::count();
        $initiativesCount = CommunityInitiative::count();

        // 5. إحصائيات القروض البيضاء (بدون فوائد)
        $loansCount = AssociationLoan::count();
        $totalLoansAmount = AssociationLoan::sum('loan_amount');
        $totalLoansPaid = AssociationLoan::sum('paid_amount');
        $totalLoansRemaining = AssociationLoan::sum('remaining_balance');

        // 6. آخر السجلات المضافة
        $recentAssociations = Association::with(['governorate', 'directorate'])->latest()->take(5)->get();
        $recentGroups = ProductiveGroup::with(['governorate', 'directorate', 'association'])->latest()->take(5)->get();
        $recentProjects = AssociationProject::with(['governorate', 'directorate', 'association'])->latest()->take(5)->get();

        // 7. المحافظات النشطة
        $governoratesCount = Governorate::where('is_active', true)->count();

        $kpis = [
            'associations_count' => $associationsCount,
            'active_associations' => $activeAssociations,
            'total_capital' => $totalCapital,
            'groups_count' => $groupsCount,
            'group_members' => $totalGroupMembers,
            'group_area' => $totalGroupArea,
            'personnel_count' => $personnelCount,
            'knights_count' => $knightsCount,
            'researchers_count' => $researchersCount,
            'trainers_count' => $trainersCount,
            'projects_count' => $projectsCount,
            'projects_cost' => $projectsCost,
            'farmers_count' => $farmersCount,
            'cultivated_area' => $cultivatedArea,
            'nurseries_count' => $nurseriesCount,
            'initiatives_count' => $initiativesCount,
            'loans_count' => $loansCount,
            'loans_amount' => $totalLoansAmount,
            'loans_paid' => $totalLoansPaid,
            'loans_remaining' => $totalLoansRemaining,
            'governorates_count' => $governoratesCount,
        ];

        return view('dashboard', compact('kpis', 'recentAssociations', 'recentGroups', 'recentProjects'));
    }
}
