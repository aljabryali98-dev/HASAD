<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\Directorate;
use App\Models\Facilitator;
use App\Models\Governorate;
use App\Models\ProductiveGroup;
use App\Models\Uzlah;
use App\Models\SubLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductiveGroupController extends Controller
{
    /**
     * قائمة المجاميع الإنتاجية
     */
    public function index(Request $request): View
    {
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);
        $associations = Association::orderBy('name')->get(['id', 'name', 'code']);
        $facilitators = Facilitator::where('status', 'نشط')->orderBy('full_name')->get(['id', 'full_name', 'phone']);

        $query = ProductiveGroup::with([
            'association:id,name,code',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name',
            'subLocation:id,name_ar',
            'facilitators:id,full_name,phone'
        ]);

        if ($request->filled('gov_id')) {
            $query->where('governorate_id', $request->gov_id);
        }
        if ($request->filled('dir_id')) {
            $query->where('directorate_id', $request->dir_id);
        }
        if ($request->filled('association_id')) {
            $query->where('association_id', $request->association_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('group_type')) {
            $query->where('group_type', $request->group_type);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $likeOp = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($term, $likeOp) {
                $q->where('name', $likeOp, "%{$term}%")
                  ->orWhere('code', $likeOp, "%{$term}%")
                  ->orWhere('leader_name', $likeOp, "%{$term}%")
                  ->orWhere('main_product', $likeOp, "%{$term}%");
            });
        }

        $groups = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $statsRow = ProductiveGroup::selectRaw("
            COUNT(*) as total_groups,
            COUNT(CASE WHEN status = 'نشطة' THEN 1 END) as active_groups,
            COALESCE(SUM(total_members), 0) as total_members,
            COALESCE(SUM(production_value), 0) as total_production_value,
            COALESCE(SUM(sales_value), 0) as total_sales_value
        ")->first();

        $stats = [
            'total_groups' => (int) ($statsRow->total_groups ?? 0),
            'active_groups' => (int) ($statsRow->active_groups ?? 0),
            'total_members' => (int) ($statsRow->total_members ?? 0),
            'total_production_value' => (float) ($statsRow->total_production_value ?? 0),
            'total_sales_value' => (float) ($statsRow->total_sales_value ?? 0),
        ];

        return view('productive_groups.index', compact('groups', 'governorates', 'associations', 'facilitators', 'stats'));
    }

    /**
     * حفظ مجموعة إنتاجية جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group_type' => 'required|string|max:100',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'required|exists:uzlahs,id',
            'sub_location_id' => 'nullable|exists:sub_locations,id',
            'status' => 'required|string|max:50',
            'main_product' => 'nullable|string|max:150',
            'productive_activity' => 'nullable|string|max:150',
            'founded_date' => 'nullable|date',
            'leader_name' => 'nullable|string|max:150',
            'leader_phone' => 'nullable|string|max:50',
            'total_members' => 'nullable|integer|min:0',
            'male_members' => 'nullable|integer|min:0',
            'female_members' => 'nullable|integer|min:0',
            'productive_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:50',
            'production_volume' => 'nullable|numeric|min:0',
            'measurement_unit' => 'nullable|string|max:50',
            'production_value' => 'nullable|numeric|min:0',
            'sales_value' => 'nullable|numeric|min:0',
            'marketing_method' => 'nullable|string|max:100',
            'facilitator_id' => 'nullable|exists:facilitators,id',
        ]);

        $facilitatorId = $validated['facilitator_id'] ?? null;
        unset($validated['facilitator_id']);

        $validated['code'] = ProductiveGroup::generateCode($validated['uzlah_id']);
        $group = ProductiveGroup::create($validated);

        // تفريغ الكاش لتحديث لوحة التحكم فوراً
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');

        if ($facilitatorId) {
            $group->facilitators()->attach($facilitatorId, [
                'assigned_at' => now(),
                'is_primary' => true,
            ]);
        }

        return redirect()->route('productive-groups.show', $group->id)
            ->with('success', "تم تأسيس المجموعة الإنتاجية ({$group->name}) بنجاح بالكود: {$group->code}");
    }

    /**
     * عرض الملف الشامل للمجموعة الإنتاجية بالتبويبات الـ 5
     */
    public function show(int $id): View
    {
        $group = ProductiveGroup::with([
            'association',
            'governorate',
            'directorate',
            'uzlah',
            'subLocation',
            'facilitators.productiveGroups',
        ])->findOrFail($id);

        $facilitators = Facilitator::where('status', 'نشط')->orderBy('full_name')->get();

        return view('productive_groups.show', compact('group', 'facilitators'));
    }

    /**
     * تحديث بيانات المجموعة الإنتاجية
     */
    public function update(Request $request, int $id)
    {
        $group = ProductiveGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group_type' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'main_product' => 'nullable|string|max:150',
            'productive_activity' => 'nullable|string|max:150',
            'leader_name' => 'nullable|string|max:150',
            'leader_phone' => 'nullable|string|max:50',
            'total_members' => 'nullable|integer|min:0',
            'male_members' => 'nullable|integer|min:0',
            'female_members' => 'nullable|integer|min:0',
            'productive_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:50',
            'production_volume' => 'nullable|numeric|min:0',
            'measurement_unit' => 'nullable|string|max:50',
            'production_value' => 'nullable|numeric|min:0',
            'sales_value' => 'nullable|numeric|min:0',
            'marketing_method' => 'nullable|string|max:100',
            'key_achievements' => 'nullable|string',
            'needs' => 'nullable|string',
            'challenges' => 'nullable|string',
        ]);

        $group->update($validated);

        return redirect()->route('productive-groups.show', $group->id)
            ->with('success', "تم تحديث بيانات المجموعة ({$group->name}) بنجاح.");
    }

    /**
     * حذف مجموعة إنتاجية
     */
    public function destroy(int $id)
    {
        $group = ProductiveGroup::findOrFail($id);
        $name = $group->name;
        $group->delete();

        return redirect()->route('productive-groups.index')
            ->with('success', "تم حذف المجموعة الإنتاجية ({$name}) بنجاح.");
    }

    /**
     * ربط ميسّر بالمجموعة
     */
    public function attachFacilitator(Request $request, int $id)
    {
        $group = ProductiveGroup::findOrFail($id);
        $request->validate(['facilitator_id' => 'required|exists:facilitators,id']);

        $group->facilitators()->syncWithoutDetaching([
            $request->facilitator_id => [
                'assigned_at' => now(),
                'is_primary' => true,
            ]
        ]);

        return redirect()->route('productive-groups.show', ['productive_group' => $group->id, '#tab-facilitator'])
            ->with('success', 'تم ربط الميسّر بالمجموعة بنجاح.');
    }
}
