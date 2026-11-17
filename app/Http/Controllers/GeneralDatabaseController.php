<?php

namespace App\Http\Controllers;

use App\Models\AgriculturalExtension;
use App\Models\Association;
use App\Models\AssociationCrop;
use App\Models\AssociationLoan;
use App\Models\AssociationProject;
use App\Models\AwarenessSession;
use App\Models\CommunityInitiative;
use App\Models\ContractFarming;
use App\Models\CropExpansion;
use App\Models\DairyAndLivestock;
use App\Models\DesertFarming;
use App\Models\Directorate;
use App\Models\FarmerAndLand;
use App\Models\FuelStation;
use App\Models\Governorate;
use App\Models\LandReclamation;
use App\Models\Nursery;
use App\Models\StrategicPlan;
use App\Models\StudyAndResearch;
use App\Models\UnitTraining;
use App\Models\Uzlah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralDatabaseController extends Controller
{
    /**
     * 16. مشاريع الجمعيات
     */
    public function projects(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name', 'code']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $query = AssociationProject::with(['association', 'governorate', 'directorate', 'uzlah']);
        if ($request->filled('association_id')) $query->where('association_id', $request->association_id);
        if ($request->filled('gov_id')) $query->where('governorate_id', $request->gov_id);

        $projects = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $stats = [
            'total' => AssociationProject::count(),
            'total_cost' => AssociationProject::sum('cost'),
            'completed' => AssociationProject::where('execution_status', 'منفذ')->count(),
            'ongoing' => AssociationProject::where('execution_status', 'قيد التنفيذ')->count(),
        ];

        return view('general.projects', compact('projects', 'associations', 'governorates', 'stats'));
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:200',
            'project_type' => 'nullable|string|max:100',
            'quantity' => 'nullable|integer|min:1',
            'execution_status' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'nullable|exists:uzlahs,id',
            'notes' => 'nullable|string',
        ]);

        AssociationProject::create($validated);
        return redirect()->back()->with('success', "تم تسجيل المشروع بنجاح والانعكاس تلقائياً في ملف الجمعية والنطاق الجغرافي.");
    }

    /**
     * 17 & 18. التوعية بأهمية التطوع والجمعيات
     */
    public function awareness(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $query = AwarenessSession::with(['association', 'governorate', 'directorate', 'uzlah']);
        if ($request->filled('type')) $query->where('awareness_type', $request->type);

        $sessions = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $stats = [
            'total_sessions' => AwarenessSession::sum('sessions_count'),
            'beneficiaries' => AwarenessSession::sum('beneficiaries_count'),
            'total_cost' => AwarenessSession::sum('cost'),
        ];

        return view('general.awareness', compact('sessions', 'associations', 'governorates', 'stats'));
    }

    public function storeAwareness(Request $request)
    {
        $validated = $request->validate([
            'awareness_type' => 'required|string|in:تطوع,جمعيات',
            'target_group' => 'nullable|string|max:150',
            'beneficiaries_count' => 'nullable|integer|min:0',
            'sessions_count' => 'nullable|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'funding_source' => 'nullable|string|max:150',
            'session_date' => 'nullable|date',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'nullable|exists:uzlahs,id',
            'notes' => 'nullable|string',
        ]);

        AwarenessSession::create($validated);
        return redirect()->back()->with('success', "تم تسجيل جلسة التوعية بنجاح.");
    }

    /**
     * 19. المبادرات
     */
    public function initiatives(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $initiatives = CommunityInitiative::with(['association', 'governorate', 'directorate', 'uzlah'])
            ->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total' => CommunityInitiative::count(),
            'initiators' => CommunityInitiative::sum('initiators_count'),
            'beneficiaries' => CommunityInitiative::sum('beneficiaries_count'),
            'total_cost' => CommunityInitiative::sum('cost'),
        ];

        return view('general.initiatives', compact('initiatives', 'associations', 'governorates', 'stats'));
    }

    public function storeInitiative(Request $request)
    {
        $validated = $request->validate([
            'initiative_name' => 'required|string|max:200',
            'initiative_type' => 'nullable|string|max:100',
            'initiators_count' => 'nullable|integer|min:0',
            'beneficiaries_count' => 'nullable|integer|min:0',
            'support_type' => 'nullable|string|max:150',
            'execution_entity' => 'nullable|string|max:150',
            'cost' => 'nullable|numeric|min:0',
            'funding_source' => 'nullable|string|max:150',
            'session_date' => 'nullable|date',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'nullable|exists:uzlahs,id',
            'notes' => 'nullable|string',
        ]);

        CommunityInitiative::create($validated);
        return redirect()->back()->with('success', "تم تسجيل المبادرة بنجاح.");
    }

    /**
     * 24. المحاصيل
     */
    public function crops(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $query = AssociationCrop::with(['association', 'governorate', 'directorate', 'uzlah']);
        if ($request->filled('association_id')) $query->where('association_id', $request->association_id);
        if ($request->filled('crop_type')) $query->where('crop_type', $request->crop_type);

        $crops = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $stats = [
            'total_records' => AssociationCrop::count(),
            'total_production' => AssociationCrop::sum('production_quantity'),
            'available_count' => AssociationCrop::where('availability_status', 'متوفرة')->count(),
        ];

        return view('general.crops', compact('crops', 'associations', 'governorates', 'stats'));
    }

    public function storeCrop(Request $request)
    {
        $validated = $request->validate([
            'crop_type' => 'required|string|max:100',
            'crop_variety' => 'nullable|string|max:100',
            'production_quantity' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string|max:50',
            'availability_status' => 'required|string|max:50',
            'execution_status' => 'required|string|max:50',
            'planting_date' => 'nullable|date',
            'harvest_date' => 'nullable|date',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'nullable|exists:uzlahs,id',
            'notes' => 'nullable|string',
        ]);

        AssociationCrop::create($validated);
        return redirect()->back()->with('success', "تم تسجيل المحصول الزراعي وتحديث ملف الجمعية والنطاق الجغرافي تلقائياً.");
    }

    /**
     * 26. المشاتل
     */
    public function nurseries(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $nurseries = Nursery::with(['association', 'governorate', 'directorate', 'uzlah'])
            ->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total_nurseries' => Nursery::count(),
            'total_capacity' => Nursery::sum('capacity'),
            'active_nurseries' => Nursery::where('status', 'مفعل')->count(),
        ];

        return view('general.nurseries', compact('nurseries', 'associations', 'governorates', 'stats'));
    }

    public function storeNursery(Request $request)
    {
        $validated = $request->validate([
            'nursery_name' => 'required|string|max:150',
            'ownership_type' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'capacity_unit' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'nullable|exists:uzlahs,id',
            'notes' => 'nullable|string',
        ]);

        Nursery::create($validated);
        return redirect()->back()->with('success', "تم تسجيل المشتل بنجاح.");
    }

    /**
     * 32. سجل المزارعين والأراضي المزروعة
     */
    public function farmers(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $query = FarmerAndLand::with(['association', 'governorate', 'directorate', 'uzlah']);
        if ($request->filled('association_id')) $query->where('association_id', $request->association_id);

        $farmers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $stats = [
            'total_farmers' => FarmerAndLand::count(),
            'total_area' => FarmerAndLand::sum('total_land_area'),
            'cultivated_area' => FarmerAndLand::sum('cultivated_area'),
        ];

        return view('general.farmers', compact('farmers', 'associations', 'governorates', 'stats'));
    }

    public function storeFarmer(Request $request)
    {
        $validated = $request->validate([
            'farmer_name' => 'required|string|max:150',
            'national_id' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'total_land_area' => 'nullable|numeric|min:0',
            'cultivated_area' => 'nullable|numeric|min:0',
            'area_unit' => 'required|string|max:50',
            'main_crop_type' => 'nullable|string|max:100',
            'irrigation_source' => 'required|string|max:50',
            'membership_status' => 'required|string|max:50',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'required|exists:uzlahs,id',
            'notes' => 'nullable|string',
        ]);

        FarmerAndLand::create($validated);
        return redirect()->back()->with('success', "تم تسجيل المزارع وربطه بالجمعية والنطاق الجغرافي بنجاح.");
    }

    /**
     * 33. السجل العام للقروض التمويلية البيضاء
     */
    public function loans(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        $loans = AssociationLoan::with(['association', 'governorate', 'directorate'])
            ->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total_loans' => AssociationLoan::count(),
            'total_amount' => AssociationLoan::sum('loan_amount'),
            'total_paid' => AssociationLoan::sum('paid_amount'),
            'total_remaining' => AssociationLoan::sum('remaining_balance'),
        ];

        return view('general.loans', compact('loans', 'associations', 'governorates', 'stats'));
    }
}
