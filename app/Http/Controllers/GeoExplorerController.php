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
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $selectedGovId = $request->query('gov_id') ?? $request->query('governorate_id', 2);
        $selectedDirId = $request->query('dir_id') ?? $request->query('directorate_id');
        $selectedUzlId = $request->query('uzl_id') ?? $request->query('uzlah_id');

        $currentGov = Governorate::find($selectedGovId);
        $currentDir = $selectedDirId ? Directorate::find($selectedDirId) : null;
        $currentUzl = $selectedUzlId ? Uzlah::find($selectedUzlId) : null;

        // دالة مساعدة لتطبيق نطاق الفلترة الجغرافي على أي نموذج
        $applyGeoFilter = function ($query) use ($selectedGovId, $selectedDirId, $selectedUzlId) {
            if ($selectedGovId) $query->where('governorate_id', $selectedGovId);
            if ($selectedDirId) $query->where('directorate_id', $selectedDirId);
            if ($selectedUzlId) $query->where('uzlah_id', $selectedUzlId);
            return $query;
        };

        // تجميع السجلات المرتبطة بالنطاق
        $associations = $applyGeoFilter(Association::query())->with(['directorate', 'uzlah'])->get();
        $productiveGroups = $applyGeoFilter(ProductiveGroup::query())->with(['association'])->get();
        $personnel = $applyGeoFilter(UnifiedPersonnel::query())->get();
        $projects = $applyGeoFilter(AssociationProject::query())->get();
        $initiatives = $applyGeoFilter(CommunityInitiative::query())->get();
        $crops = $applyGeoFilter(AssociationCrop::query())->get();
        $farmers = $applyGeoFilter(FarmerAndLand::query())->get();
        $nurseries = $applyGeoFilter(Nursery::query())->get();
        $loans = $selectedDirId
            ? AssociationLoan::where('directorate_id', $selectedDirId)->get()
            : ($selectedGovId ? AssociationLoan::where('governorate_id', $selectedGovId)->get() : collect());

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
