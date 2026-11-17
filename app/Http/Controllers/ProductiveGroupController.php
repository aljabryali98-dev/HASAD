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
use Illuminate\View\View;

class ProductiveGroupController extends Controller
{
    /**
     * قائمة المجاميع الإنتاجية
     */
    public function index(Request $request): View
    {
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get();
        $associations = Association::orderBy('name')->get(['id', 'name', 'code']);
        $facilitators = Facilitator::where('status', 'نشط')->orderBy('full_name')->get();

        $query = ProductiveGroup::with(['association', 'governorate', 'directorate', 'uzlah', 'subLocation', 'facilitators']);

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
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('leader_name', 'like', "%{$term}%")
                  ->orWhere('main_product', 'like', "%{$term}%");
            });
        }

        $groups = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_groups' => ProductiveGroup::count(),
            'active_groups' => ProductiveGroup::where('status', 'نشطة')->count(),
            'total_members' => ProductiveGroup::sum('total_members'),
            'total_production_value' => ProductiveGroup::sum('production_value'),
            'total_sales_value' => ProductiveGroup::sum('sales_value'),
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
