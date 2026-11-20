<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\AssociationBoardMember;
use App\Models\AssociationLoan;
use App\Models\AssociationShareholder;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\SubLocation;
use App\Models\UnifiedPersonnel;
use App\Models\Uzlah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AssociationController extends Controller
{
    /**
     * قائمة الجمعيات التعاونية مع الفلترة والإحصائيات
     */
    public function index(Request $request): View
    {
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        $query = Association::with([
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name',
            'subLocation:id,name_ar'
        ])
        ->withCount(['productiveGroups', 'personnel', 'loans', 'projects', 'crops', 'farmers']);

        if ($request->filled('gov_id')) {
            $query->where('governorate_id', $request->gov_id);
        }
        if ($request->filled('dir_id')) {
            $query->where('directorate_id', $request->dir_id);
        }
        if ($request->filled('uzl_id')) {
            $query->where('uzlah_id', $request->uzl_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $likeOp = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($term, $likeOp) {
                $q->where('name', $likeOp, "%{$term}%")
                  ->orWhere('code', $likeOp, "%{$term}%")
                  ->orWhere('president_name', $likeOp, "%{$term}%")
                  ->orWhere('license_number', $likeOp, "%{$term}%");
            });
        }

        $associations = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $statsRow = Association::selectRaw("
            COUNT(*) as total_associations,
            COUNT(CASE WHEN status = 'نشطة' THEN 1 END) as active_associations,
            COUNT(CASE WHEN status = 'تحت التأسيس' THEN 1 END) as under_establishment,
            COALESCE(SUM(current_capital), 0) as total_capital,
            COALESCE(SUM(total_members), 0) as total_members
        ")->first();

        $stats = [
            'total_associations' => (int) ($statsRow->total_associations ?? 0),
            'active_associations' => (int) ($statsRow->active_associations ?? 0),
            'under_establishment' => (int) ($statsRow->under_establishment ?? 0),
            'total_capital' => (float) ($statsRow->total_capital ?? 0),
            'total_members' => (int) ($statsRow->total_members ?? 0),
        ];

        return view('associations.index', compact('associations', 'governorates', 'stats'));
    }

    /**
     * صفحة إضافة جمعية جديدة
     */
    public function create(): View
    {
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);
        return view('associations.create', compact('governorates'));
    }

    /**
     * حفظ جمعية جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'required|exists:uzlahs,id',
            'sub_location_id' => 'nullable|exists:sub_locations,id',
            'status' => 'required|string|max:50',
            'license_number' => 'nullable|string|max:100',
            'license_date' => 'nullable|date',
            'founded_date' => 'nullable|date',
            'announced_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website_url' => 'nullable|url|max:255',

            // قيادة
            'president_name' => 'nullable|string|max:150',
            'president_phone' => 'nullable|string|max:50',
            'vice_president_name' => 'nullable|string|max:150',
            'secretary_general_name' => 'nullable|string|max:150',
            'financial_officer_name' => 'nullable|string|max:150',
            'oversight_officer_name' => 'nullable|string|max:150',
            'executive_director_name' => 'nullable|string|max:150',
            'board_members_count' => 'nullable|integer|min:0',
            'oversight_members_count' => 'nullable|integer|min:0',

            // أعضاء
            'total_members' => 'nullable|integer|min:0',
            'male_members' => 'nullable|integer|min:0',
            'female_members' => 'nullable|integer|min:0',
            'new_members_this_year' => 'nullable|integer|min:0',
            'beneficiaries_count' => 'nullable|integer|min:0',
            'beneficiary_families_count' => 'nullable|integer|min:0',

            // مالي
            'initial_capital' => 'nullable|numeric|min:0',
            'current_capital' => 'nullable|numeric|min:0',
            'share_value' => 'nullable|numeric|min:0',
            'shares_count' => 'nullable|integer|min:0',
            'annual_subscription_fee' => 'nullable|numeric|min:0',
            'total_assets_value' => 'nullable|numeric|min:0',
            'has_bank_account' => 'nullable|boolean',
            'has_accounting_records' => 'nullable|boolean',
            'last_approved_budget_date' => 'nullable|date',
        ]);

        $validated['code'] = Association::generateCode($validated['uzlah_id']);
        $association = Association::create($validated);

        // تفريغ الكاش لتحديث لوحة التحكم والمؤشرات فوراً
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');

        // إذا كان هناك اسم رئيس للجمعية، إدراجه تلقائياً كعضو هيئة إدارية أول
        if (!empty($validated['president_name'])) {
            AssociationBoardMember::create([
                'association_id' => $association->id,
                'full_name' => $validated['president_name'],
                'role_title' => 'رئيس الجمعية',
                'phone' => $validated['president_phone'] ?? null,
                'governorate_id' => $association->governorate_id,
                'directorate_id' => $association->directorate_id,
                'uzlah_id' => $association->uzlah_id,
                'sub_location_id' => $association->sub_location_id,
                'membership_status' => 'نشط',
            ]);
        }

        return redirect()->route('associations.show', $association->id)
            ->with('success', "تم تأسيس الجمعية ({$association->name}) بنجاح بالكود التعريفي: {$association->code}");
    }

    /**
     * صفحة ملف الجمعية الشامل (360° Profile) بجميع التبويبات الـ 9 والبيانات المترابطة تلقائياً
     */
    public function show(int $id): View
    {
        $association = Association::with([
            'governorate',
            'directorate',
            'uzlah',
            'subLocation',
            'boardMembers',
            'shareholders',
            'productiveGroups.facilitators',
            'personnel',
            'loans',
            'projects',
            'crops',
            'contractFarmings',
            'nurseries',
            'products',
            'farmers',
            'unitTrainings',
            'fuelStations',
        ])->findOrFail($id);

        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();

        return view('associations.show', compact('association', 'governorates'));
    }

    /**
     * صفحة تعديل بيانات الجمعية
     */
    public function edit(int $id): View
    {
        $association = Association::findOrFail($id);
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();
        return view('associations.edit', compact('association', 'governorates'));
    }

    /**
     * تحديث بيانات الجمعية
     */
    public function update(Request $request, int $id)
    {
        $association = Association::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'license_number' => 'nullable|string|max:100',
            'license_date' => 'nullable|date',
            'founded_date' => 'nullable|date',
            'announced_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website_url' => 'nullable|url|max:255',

            // قانوني
            'issuing_authority' => 'nullable|string|max:150',
            'license_status' => 'nullable|string|max:100',
            'has_bylaws' => 'nullable|boolean',
            'has_member_registry' => 'nullable|boolean',
            'has_founding_minutes' => 'nullable|boolean',
            'has_headquarters' => 'nullable|boolean',
            'headquarters_ownership' => 'nullable|string|max:50',
            'holds_regular_meetings' => 'nullable|boolean',
            'last_general_assembly_date' => 'nullable|date',
            'last_election_date' => 'nullable|date',
            'last_data_update_date' => 'nullable|date',

            // قيادة
            'president_name' => 'nullable|string|max:150',
            'president_phone' => 'nullable|string|max:50',
            'vice_president_name' => 'nullable|string|max:150',
            'secretary_general_name' => 'nullable|string|max:150',
            'financial_officer_name' => 'nullable|string|max:150',
            'oversight_officer_name' => 'nullable|string|max:150',
            'executive_director_name' => 'nullable|string|max:150',
            'board_members_count' => 'nullable|integer|min:0',
            'oversight_members_count' => 'nullable|integer|min:0',
            'board_status' => 'nullable|string|max:50',
            'board_term_end_date' => 'nullable|date',
            'employees_count' => 'nullable|integer|min:0',
            'volunteers_count' => 'nullable|integer|min:0',

            // أعضاء
            'total_members' => 'nullable|integer|min:0',
            'male_members' => 'nullable|integer|min:0',
            'female_members' => 'nullable|integer|min:0',
            'new_members_this_year' => 'nullable|integer|min:0',
            'beneficiaries_count' => 'nullable|integer|min:0',
            'beneficiary_families_count' => 'nullable|integer|min:0',

            // مالي
            'initial_capital' => 'nullable|numeric|min:0',
            'current_capital' => 'nullable|numeric|min:0',
            'share_value' => 'nullable|numeric|min:0',
            'shares_count' => 'nullable|integer|min:0',
            'annual_subscription_fee' => 'nullable|numeric|min:0',
            'total_assets_value' => 'nullable|numeric|min:0',
            'has_bank_account' => 'nullable|boolean',
            'has_accounting_records' => 'nullable|boolean',
            'last_approved_budget_date' => 'nullable|date',

            // أنشطة وأصول ومتابعة
            'main_activities' => 'nullable|string',
            'agricultural_activities' => 'nullable|string',
            'marketing_activities' => 'nullable|string',
            'productive_activities' => 'nullable|string',
            'service_activities' => 'nullable|string',
            'key_achievements' => 'nullable|string',
            'key_products' => 'nullable|string',
            'annual_production_volume' => 'nullable|string',
            'target_markets' => 'nullable|string',
            'partners_and_supporters' => 'nullable|string',
            'agricultural_lands' => 'nullable|string',
            'wells_water_structures' => 'nullable|string',
            'machinery_equipment' => 'nullable|string',
            'transport_vehicles' => 'nullable|string',
            'warehouses' => 'nullable|string',
            'buildings_headquarters' => 'nullable|string',
            'other_assets' => 'nullable|string',
            'estimated_assets_value' => 'nullable|numeric|min:0',
            'cooperative_activity_level' => 'nullable|string|max:50',
            'administrative_status' => 'nullable|string|max:50',
            'financial_status' => 'nullable|string|max:50',
            'legal_status' => 'nullable|string|max:50',
            'activity_execution_level' => 'nullable|string|max:50',
            'top_needs' => 'nullable|string',
            'challenges_difficulties' => 'nullable|string',
            'training_needs' => 'nullable|string',
            'required_support' => 'nullable|string',
            'last_field_visit_date' => 'nullable|date',
            'visiting_entity' => 'nullable|string|max:150',
            'monitoring_team_notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'general_evaluation' => 'nullable|string|max:50',
        ]);

        $association->update($validated);

        return redirect()->route('associations.show', $association->id)
            ->with('success', "تم تحديث بيانات الجمعية ({$association->name}) بنجاح.");
    }

    /**
     * حذف جمعية
     */
    public function destroy(int $id)
    {
        $association = Association::findOrFail($id);
        $name = $association->name;
        $association->delete();

        return redirect()->route('associations.index')
            ->with('success', "تم حذف الجمعية ({$name}) بنجاح.");
    }

    /**
     * إضافة عضو قيادة / إدارة للجمعية
     */
    public function storeBoardMember(Request $request, int $id)
    {
        $association = Association::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'role_title' => 'required|string|max:100',
            'gender' => 'required|string|in:ذكر,أنثى',
            'birth_date' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'education_level' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'alt_phone' => 'nullable|string|max:50',
            'assignment_start_date' => 'nullable|date',
            'assignment_end_date' => 'nullable|date',
            'membership_status' => 'nullable|string|max:50',
            'assigning_entity' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $validated['association_id'] = $association->id;
        $validated['governorate_id'] = $association->governorate_id;
        $validated['directorate_id'] = $association->directorate_id;
        $validated['uzlah_id'] = $association->uzlah_id;
        $validated['sub_location_id'] = $association->sub_location_id;

        AssociationBoardMember::create($validated);

        return redirect()->route('associations.show', ['association' => $association->id, '#tab-leadership'])
            ->with('success', "تمت إضافة عضو القيادة والإدارة ({$validated['full_name']}) بنجاح.");
    }

    /**
     * إضافة قرض أبيض للجمعية
     */
    public function storeLoan(Request $request, int $id)
    {
        $association = Association::findOrFail($id);

        $validated = $request->validate([
            'loan_type' => 'required|string|max:50',
            'funding_entity' => 'required|string|max:150',
            'beneficiary_name' => 'required|string|max:150',
            'loan_amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:50',
            'received_date' => 'nullable|date',
            'repayment_period_months' => 'required|integer|min:1',
            'grace_period_months' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'loan_status' => 'required|string|max:50',
            'purpose' => 'nullable|string',
            'collaterals' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['code'] = AssociationLoan::generateCode();
        $validated['association_id'] = $association->id;
        $validated['governorate_id'] = $association->governorate_id;
        $validated['directorate_id'] = $association->directorate_id;
        $validated['remaining_balance'] = max(0, $validated['loan_amount'] - ($validated['paid_amount'] ?? 0));

        AssociationLoan::create($validated);

        return redirect()->route('associations.show', ['association' => $association->id, '#tab-loans'])
            ->with('success', "تم تسجيل القرض الأبيض بنجاح بالكود: {$validated['code']}");
    }

    /**
     * تسجيل مساهم جديد في الجمعية وتحديث رأس المال المكتتب والمدفوع
     */
    public function storeShareholder(Request $request, int $id)
    {
        $association = Association::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'gender' => 'required|in:ذكر,أنثى',
            'shares_count' => 'required|integer|min:1',
            'share_value' => 'required|numeric|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'join_date' => 'nullable|date',
            'receipt_number' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $sharesCount = (int) $validated['shares_count'];
        $shareValue = (float) $validated['share_value'];
        $totalSubscription = $sharesCount * $shareValue;
        $paidAmount = (float) $validated['paid_amount'];
        $remainingAmount = max(0, $totalSubscription - $paidAmount);

        $shareholder = AssociationShareholder::create([
            'association_id' => $association->id,
            'shareholder_number' => AssociationShareholder::generateShareholderNumber($association->id),
            'full_name' => $validated['full_name'],
            'national_id' => $validated['national_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'],
            'governorate_id' => $association->governorate_id,
            'directorate_id' => $association->directorate_id,
            'uzlah_id' => $association->uzlah_id,
            'sub_location_id' => $association->sub_location_id,
            'shares_count' => $sharesCount,
            'share_value' => $shareValue,
            'total_subscription_amount' => $totalSubscription,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'join_date' => $validated['join_date'] ?? now()->toDateString(),
            'receipt_number' => $validated['receipt_number'] ?: ('REC-' . date('Ymd') . '-' . rand(100, 999)),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // تحديث إحصائيات الجمعية ورأس المال المحصل
        $association->increment('total_members');
        if ($validated['gender'] === 'أنثى') {
            $association->increment('female_members');
        } else {
            $association->increment('male_members');
        }
        $association->increment('new_members_this_year');
        if ($paidAmount > 0) {
            $association->increment('current_capital', $paidAmount);
        }

        return redirect()->route('associations.show', ['association' => $association->id, '#tab-members'])
            ->with('success', "تم تسجيل المساهم ({$shareholder->full_name}) برقم عضوية [{$shareholder->shareholder_number}] وإيداع {$paidAmount} ر.ي بنجاح.");
    }

    /**
     * طباعة سند مساهمة رسمي
     */
    public function printShareholderReceipt(int $associationId, int $shareholderId): View
    {
        $association = Association::with(['governorate', 'directorate'])->findOrFail($associationId);
        $shareholder = AssociationShareholder::where('association_id', $associationId)->findOrFail($shareholderId);

        return view('associations.receipt', compact('association', 'shareholder'));
    }

    /**
     * إضافة كادر/فارس مباشرة تحت مظلة الجمعية
     */
    public function storePersonnel(Request $request, int $id)
    {
        $association = Association::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'sub_specialty' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'gender' => 'required|in:ذكر,أنثى',
            'qualification' => 'nullable|string|max:100',
            'training_date' => 'nullable|date',
            'is_activated_in_field' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['association_id'] = $association->id;
        $validated['governorate_id'] = $association->governorate_id;
        $validated['directorate_id'] = $association->directorate_id;
        $validated['uzlah_id'] = $association->uzlah_id;
        $validated['sub_location_id'] = $association->sub_location_id;
        $validated['code'] = UnifiedPersonnel::generateCode($validated['category']);
        $validated['is_activated_in_field'] = $request->boolean('is_activated_in_field');

        $person = UnifiedPersonnel::create($validated);

        return redirect()->route('associations.show', ['association' => $association->id, '#tab-personnel'])
            ->with('success', "تم إضافة الكادر/الفارس ({$person->full_name}) إلى كشف الجمعية بنجاح بالكود: [{$person->code}]");
    }

    /**
     * طباعة الملف الشامل الرسمي للجمعية
     */
    public function printDossier(int $id): View
    {
        $association = Association::with([
            'governorate', 'directorate', 'uzlah', 'subLocation',
            'boardMembers', 'shareholders', 'productiveGroups',
            'personnel', 'loans', 'projects', 'crops', 'nurseries'
        ])->findOrFail($id);

        return view('associations.print', compact('association'));
    }
}
