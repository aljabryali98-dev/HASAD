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
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GeneralDatabaseController extends Controller
{
    /**
     * 16. مشاريع الجمعيات
     */
    public function projects(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name', 'code']);
        $governorates = Cache::remember('active_governorates', 86400, function () {
            return Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);
        });

        $query = AssociationProject::with([
            'association:id,name,code',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name'
        ]);

        if ($request->filled('association_id')) $query->where('association_id', $request->association_id);
        if ($request->filled('gov_id')) $query->where('governorate_id', $request->gov_id);

        $projects = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = AssociationProject::selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(cost), 0) as total_cost,
            COUNT(CASE WHEN execution_status = 'منفذ' THEN 1 END) as completed,
            COUNT(CASE WHEN execution_status = 'قيد التنفيذ' THEN 1 END) as ongoing
        ")->first();

        $stats = [
            'total' => (int) ($statsRow->total ?? 0),
            'total_cost' => (float) ($statsRow->total_cost ?? 0),
            'completed' => (int) ($statsRow->completed ?? 0),
            'ongoing' => (int) ($statsRow->ongoing ?? 0),
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
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');
        return redirect()->back()->with('success', "تم تسجيل المشروع بنجاح والانعكاس تلقائياً في ملف الجمعية والنطاق الجغرافي.");
    }

    /**
     * 17 & 18. التوعية بأهمية التطوع والجمعيات
     */
    public function awareness(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $query = AwarenessSession::with([
            'association:id,name',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name'
        ]);

        if ($request->filled('type')) $query->where('awareness_type', $request->type);

        $sessions = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = AwarenessSession::selectRaw("
            COALESCE(SUM(sessions_count), 0) as total_sessions,
            COALESCE(SUM(beneficiaries_count), 0) as beneficiaries,
            COALESCE(SUM(cost), 0) as total_cost
        ")->first();

        $stats = [
            'total_sessions' => (int) ($statsRow->total_sessions ?? 0),
            'beneficiaries' => (int) ($statsRow->beneficiaries ?? 0),
            'total_cost' => (float) ($statsRow->total_cost ?? 0),
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
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');
        return redirect()->back()->with('success', "تم تسجيل جلسة التوعية بنجاح.");
    }

    /**
     * 19. المبادرات
     */
    public function initiatives(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $initiatives = CommunityInitiative::with([
            'association:id,name',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name'
        ])->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = CommunityInitiative::selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(initiators_count), 0) as initiators,
            COALESCE(SUM(beneficiaries_count), 0) as beneficiaries,
            COALESCE(SUM(cost), 0) as total_cost
        ")->first();

        $stats = [
            'total' => (int) ($statsRow->total ?? 0),
            'initiators' => (int) ($statsRow->initiators ?? 0),
            'beneficiaries' => (int) ($statsRow->beneficiaries ?? 0),
            'total_cost' => (float) ($statsRow->total_cost ?? 0),
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
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');
        return redirect()->back()->with('success', "تم تسجيل المبادرة بنجاح.");
    }

    /**
     * 24. المحاصيل
     */
    public function crops(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $query = AssociationCrop::with([
            'association:id,name',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name'
        ]);

        if ($request->filled('association_id')) $query->where('association_id', $request->association_id);
        if ($request->filled('crop_type')) $query->where('crop_type', $request->crop_type);

        $crops = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = AssociationCrop::selectRaw("
            COUNT(*) as total_records,
            COALESCE(SUM(production_quantity), 0) as total_production,
            COUNT(CASE WHEN availability_status = 'متوفرة' THEN 1 END) as available_count
        ")->first();

        $stats = [
            'total_records' => (int) ($statsRow->total_records ?? 0),
            'total_production' => (float) ($statsRow->total_production ?? 0),
            'available_count' => (int) ($statsRow->available_count ?? 0),
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
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');
        return redirect()->back()->with('success', "تم تسجيل المحصول الزراعي وتحديث ملف الجمعية والنطاق الجغرافي تلقائياً.");
    }

    /**
     * 26. المشاتل
     */
    public function nurseries(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $nurseries = Nursery::with([
            'association:id,name',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name'
        ])->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = Nursery::selectRaw("
            COUNT(*) as total_nurseries,
            COALESCE(SUM(capacity), 0) as total_capacity,
            COUNT(CASE WHEN status = 'مفعل' THEN 1 END) as active_nurseries
        ")->first();

        $stats = [
            'total_nurseries' => (int) ($statsRow->total_nurseries ?? 0),
            'total_capacity' => (float) ($statsRow->total_capacity ?? 0),
            'active_nurseries' => (int) ($statsRow->active_nurseries ?? 0),
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
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');
        return redirect()->back()->with('success', "تم تسجيل المشتل بنجاح.");
    }

    /**
     * 32. سجل المزارعين والأراضي المزروعة
     */
    public function farmers(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $query = FarmerAndLand::with([
            'association:id,name',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name'
        ]);

        if ($request->filled('association_id')) $query->where('association_id', $request->association_id);

        $farmers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = FarmerAndLand::selectRaw("
            COUNT(*) as total_farmers,
            COALESCE(SUM(total_land_area), 0) as total_area,
            COALESCE(SUM(cultivated_area), 0) as cultivated_area
        ")->first();

        $stats = [
            'total_farmers' => (int) ($statsRow->total_farmers ?? 0),
            'total_area' => (float) ($statsRow->total_area ?? 0),
            'cultivated_area' => (float) ($statsRow->cultivated_area ?? 0),
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
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');
        return redirect()->back()->with('success', "تم تسجيل المزارع وربطه بالجمعية والنطاق الجغرافي بنجاح.");
    }

    /**
     * 33. السجل العام للقروض التمويلية البيضاء
     */
    public function loans(Request $request): View
    {
        $associations = Association::orderBy('name')->get(['id', 'name']);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $loans = AssociationLoan::with([
            'association:id,name,code',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name'
        ])->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = AssociationLoan::selectRaw("
            COUNT(*) as total_loans,
            COALESCE(SUM(loan_amount), 0) as total_amount,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(remaining_balance), 0) as total_remaining
        ")->first();

        $stats = [
            'total_loans' => (int) ($statsRow->total_loans ?? 0),
            'total_amount' => (float) ($statsRow->total_amount ?? 0),
            'total_paid' => (float) ($statsRow->total_paid ?? 0),
            'total_remaining' => (float) ($statsRow->total_remaining ?? 0),
        ];

        return view('general.loans', compact('loans', 'associations', 'governorates', 'stats'));
    }
}
