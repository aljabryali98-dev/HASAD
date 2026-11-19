<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\AssociationCrop;
use App\Models\AssociationLoan;
use App\Models\AssociationProject;
use App\Models\CommunityInitiative;
use App\Models\ContractFarming;
use App\Models\Directorate;
use App\Models\FarmerAndLand;
use App\Models\Governorate;
use App\Models\LandReclamation;
use App\Models\Nursery;
use App\Models\ProductiveGroup;
use App\Models\UnifiedPersonnel;
use App\Models\Uzlah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeoExplorerController extends Controller
{
    /**
     * مستكشف النطاق الجغرافي الشامل (Geo-Explorer)
     */
    public function index(Request $request): View
    {
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $selectedGovId = $request->query('gov_id') ?? $request->query('governorate_id', 2);
        $selectedDirId = $request->query('dir_id') ?? $request->query('directorate_id');
        $selectedUzlId = $request->query('uzl_id') ?? $request->query('uzlah_id');

        $currentGov = Governorate::find($selectedGovId, ['id', 'code', 'name_ar', 'name']);
        $currentDir = $selectedDirId ? Directorate::find($selectedDirId, ['id', 'code', 'name_ar', 'name', 'governorate_id']) : null;
        $currentUzl = $selectedUzlId ? Uzlah::find($selectedUzlId, ['id', 'code', 'name_ar', 'name', 'directorate_id', 'governorate_id']) : null;

        // دالة مساعدة لتطبيق نطاق الفلترة الجغرافي على أي نموذج
        $applyGeoFilter = function ($query) use ($selectedGovId, $selectedDirId, $selectedUzlId) {
            if ($selectedGovId) $query->where('governorate_id', $selectedGovId);
            if ($selectedDirId) $query->where('directorate_id', $selectedDirId);
            if ($selectedUzlId) $query->where('uzlah_id', $selectedUzlId);
            return $query;
        };

        // تجميع السجلات المرتبطة بالنطاق مع تقييد الحقول لتسريع الاستجابة
        $associations = $applyGeoFilter(Association::query())
            ->with(['directorate:id,name_ar,name', 'uzlah:id,name_ar,name'])
            ->get(['id', 'code', 'name', 'status', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $productiveGroups = $applyGeoFilter(ProductiveGroup::query())
            ->with(['association:id,name'])
            ->get(['id', 'code', 'name', 'status', 'total_members', 'productive_area', 'governorate_id', 'directorate_id', 'uzlah_id', 'association_id']);

        $personnel = $applyGeoFilter(UnifiedPersonnel::query())
            ->get(['id', 'code', 'full_name', 'category', 'is_activated_in_field', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $projects = $applyGeoFilter(AssociationProject::query())
            ->get(['id', 'project_name', 'execution_status', 'cost', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $initiatives = $applyGeoFilter(CommunityInitiative::query())
            ->get(['id', 'initiative_name', 'cost', 'beneficiaries_count', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $crops = $applyGeoFilter(AssociationCrop::query())
            ->get(['id', 'crop_type', 'production_quantity', 'availability_status', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $farmers = $applyGeoFilter(FarmerAndLand::query())
            ->get(['id', 'farmer_name', 'cultivated_area', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $nurseries = $applyGeoFilter(Nursery::query())
            ->get(['id', 'nursery_name', 'status', 'capacity', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $loans = $selectedDirId
            ? AssociationLoan::where('directorate_id', $selectedDirId)->get(['id', 'code', 'loan_amount', 'paid_amount', 'remaining_balance', 'loan_status'])
            : ($selectedGovId ? AssociationLoan::where('governorate_id', $selectedGovId)->get(['id', 'code', 'loan_amount', 'paid_amount', 'remaining_balance', 'loan_status']) : collect());

        $stats = [
            'associations_count' => $associations->count(),
            'productive_groups_count' => $productiveGroups->count(),
            'personnel_count' => $personnel->count(),
            'projects_count' => $projects->count(),
            'initiatives_count' => $initiatives->count(),
            'crops_count' => $crops->count(),
            'farmers_count' => $farmers->count(),
            'total_cultivated_area' => $farmers->sum('cultivated_area'),
            'loans_count' => $loans->count(),
            'total_loans_amount' => $loans->sum('loan_amount'),
        ];

        return view('geo_explorer.index', compact(
            'governorates',
            'currentGov',
            'currentDir',
            'currentUzl',
            'selectedGovId',
            'selectedDirId',
            'selectedUzlId',
            'associations',
            'productiveGroups',
            'personnel',
            'projects',
            'initiatives',
            'crops',
            'farmers',
            'loans',
            'stats'
        ));
    }
}
