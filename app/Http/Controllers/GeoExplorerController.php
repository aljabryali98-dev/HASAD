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
use App\Traits\ScopeByGovernorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GeoExplorerController extends Controller
{
    /**
     * مستكشف النطاق الجغرافي الشامل (Geo-Explorer)
     */
    public function index(Request $request): View
    {
        $governorates = ScopeByGovernorate::allowedGovernorates()->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $defaultGovId = $governorates->contains('id', 2) ? 2 : $governorates->first()?->id;
        $selectedGovId = $request->query('gov_id') ?? $request->query('governorate_id', $defaultGovId);

        if ($selectedGovId) {
            ScopeByGovernorate::authorize($selectedGovId);
        }

        $selectedDirId = $request->query('dir_id') ?? $request->query('directorate_id');
        $selectedUzlId = $request->query('uzl_id') ?? $request->query('uzlah_id');

        $currentGov = $selectedGovId ? Governorate::find($selectedGovId, ['id', 'code', 'name_ar', 'name']) : null;
        $currentDir = $selectedDirId ? Directorate::where('id', $selectedDirId)->where('governorate_id', $selectedGovId)->first(['id', 'code', 'name_ar', 'name', 'governorate_id']) : null;
        if (!$currentDir) {
            $selectedDirId = null;
        }

        $currentUzl = $selectedUzlId ? Uzlah::where('id', $selectedUzlId)->where('governorate_id', $selectedGovId)->first(['id', 'code', 'name_ar', 'name', 'directorate_id', 'governorate_id']) : null;
        if (!$currentUzl) {
            $selectedUzlId = null;
        }

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
            ->get(['id', 'code', 'name', 'type', 'status', 'president_name', 'total_members', 'governorate_id', 'directorate_id', 'uzlah_id']);

        $productiveGroups = $applyGeoFilter(ProductiveGroup::query())
            ->with(['association:id,name'])
            ->get(['id', 'code', 'name', 'status', 'total_members', 'productive_area', 'governorate_id', 'directorate_id', 'uzlah_id', 'association_id']);

        $personnel = $applyGeoFilter(UnifiedPersonnel::query())
            ->with(['association:id,name', 'uzlah:id,name_ar'])
            ->get(['id', 'code', 'full_name', 'category', 'sub_specialty', 'phone', 'is_activated_in_field', 'governorate_id', 'directorate_id', 'uzlah_id', 'association_id']);

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
            'nurseries_count' => $nurseries->count(),
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
            'nurseries',
            'loans',
            'stats'
        ));
    }

    /**
     * طباعة التقرير والكشف الرسمي للنطاق الجغرافي الشامل
     */
    public function printDossier(Request $request): View
    {
        $selectedGovId = $request->query('gov_id') ?? $request->query('governorate_id', 2);
        $selectedDirId = $request->query('dir_id') ?? $request->query('directorate_id');
        $selectedUzlId = $request->query('uzl_id') ?? $request->query('uzlah_id');

        $currentGov = Governorate::find($selectedGovId, ['id', 'code', 'name_ar', 'name']);
        $currentDir = $selectedDirId ? Directorate::find($selectedDirId, ['id', 'code', 'name_ar', 'name', 'governorate_id']) : null;
        $currentUzl = $selectedUzlId ? Uzlah::find($selectedUzlId, ['id', 'code', 'name_ar', 'name', 'directorate_id', 'governorate_id']) : null;

        $applyGeoFilter = function ($query) use ($selectedGovId, $selectedDirId, $selectedUzlId) {
            if ($selectedGovId) $query->where('governorate_id', $selectedGovId);
            if ($selectedDirId) $query->where('directorate_id', $selectedDirId);
            if ($selectedUzlId) $query->where('uzlah_id', $selectedUzlId);
            return $query;
        };

        $associations = $applyGeoFilter(Association::query())
            ->with(['directorate:id,name_ar', 'uzlah:id,name_ar'])
            ->get(['id', 'code', 'name', 'type', 'status', 'president_name', 'total_members']);

        $productiveGroups = $applyGeoFilter(ProductiveGroup::query())
            ->get(['id', 'code', 'name', 'total_members']);

        $personnel = $applyGeoFilter(UnifiedPersonnel::query())
            ->with(['association:id,name'])
            ->get(['id', 'code', 'full_name', 'category', 'sub_specialty', 'phone', 'is_activated_in_field', 'association_id']);

        $projects = $applyGeoFilter(AssociationProject::query())
            ->get(['id', 'project_name', 'cost', 'execution_status']);

        $initiatives = $applyGeoFilter(CommunityInitiative::query())
            ->get(['id', 'initiative_name', 'cost', 'beneficiaries_count']);

        $loans = $selectedDirId
            ? AssociationLoan::where('directorate_id', $selectedDirId)->get(['id', 'code', 'loan_amount'])
            : ($selectedGovId ? AssociationLoan::where('governorate_id', $selectedGovId)->get(['id', 'code', 'loan_amount']) : collect());

        $stats = [
            'associations_count' => $associations->count(),
            'productive_groups_count' => $productiveGroups->count(),
            'personnel_count' => $personnel->count(),
            'projects_count' => $projects->count(),
            'initiatives_count' => $initiatives->count(),
            'farmers_count' => FarmerAndLand::when($selectedGovId, fn($q) => $q->where('governorate_id', $selectedGovId))->count(),
            'loans_count' => $loans->count(),
        ];

        return view('geo_explorer.print', compact(
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
            'loans',
            'stats'
        ));
    }
}
