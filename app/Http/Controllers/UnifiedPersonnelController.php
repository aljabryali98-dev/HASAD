<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\UnifiedPersonnel;
use App\Models\Uzlah;
use App\Models\SubLocation;
use App\Traits\ScopeByGovernorate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class UnifiedPersonnelController extends Controller
{
    /**
     * قائمة الكوادر والفرسان والمتدربين
     */
    public function index(Request $request): View
    {
        $category = $request->has('category') 
            ? $request->query('category') 
            : ($request->filled('association_id') ? null : 'فرسان الجمعية');
        $subSpecialty = $request->query('sub_specialty');

        $governorates = ScopeByGovernorate::allowedGovernorates()->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);
        $associations = Association::forUserGovernorates()->orderBy('name')->get(['id', 'name', 'code']);

        $query = UnifiedPersonnel::forUserGovernorates()->with([
            'association:id,name,code',
            'governorate:id,name_ar,name',
            'directorate:id,name_ar,name',
            'uzlah:id,name_ar,name',
            'subLocation:id,name_ar'
        ]);

        if (!empty($category)) {
            $query->where('category', $category);
        }
        if (!empty($subSpecialty)) {
            $query->where('sub_specialty', $subSpecialty);
        }
        if ($request->filled('gov_id')) {
            $query->where('governorate_id', $request->gov_id);
        }
        if ($request->filled('dir_id')) {
            $query->where('directorate_id', $request->dir_id);
        }
        if ($request->filled('association_id')) {
            $query->where('association_id', $request->association_id);
        }
        if ($request->filled('activated')) {
            $query->where('is_activated_in_field', (bool) $request->activated);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $likeOp = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($term, $likeOp) {
                $q->where('full_name', $likeOp, "%{$term}%")
                  ->orWhere('code', $likeOp, "%{$term}%")
                  ->orWhere('phone', $likeOp, "%{$term}%")
                  ->orWhere('specialization', $likeOp, "%{$term}%");
            });
        }

        $personnel = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $statsRow = UnifiedPersonnel::forUserGovernorates()->selectRaw("
            COUNT(*) as total_all,
            COUNT(CASE WHEN category = 'فرسان الجمعية' THEN 1 END) as knights_count,
            COUNT(CASE WHEN category = 'باحثين' THEN 1 END) as researchers_count,
            COUNT(CASE WHEN category LIKE '%مدرب%' THEN 1 END) as trainers_count,
            COUNT(CASE WHEN is_activated_in_field = true THEN 1 END) as activated_count
        ")->first();

        $stats = [
            'total_all' => (int) ($statsRow->total_all ?? 0),
            'knights_count' => (int) ($statsRow->knights_count ?? 0),
            'researchers_count' => (int) ($statsRow->researchers_count ?? 0),
            'trainers_count' => (int) ($statsRow->trainers_count ?? 0),
            'activated_count' => (int) ($statsRow->activated_count ?? 0),
        ];

        $categories = UnifiedPersonnel::CATEGORIES;
        $knightsTypes = UnifiedPersonnel::KNIGHTS_TYPES;
        $selectedAssociation = $request->filled('association_id') ? Association::find($request->association_id) : null;

        return view('personnel.index', compact(
            'personnel',
            'category',
            'subSpecialty',
            'governorates',
            'associations',
            'selectedAssociation',
            'stats',
            'categories',
            'knightsTypes'
        ));
    }

    /**
     * توجيه نموذج إضافة كادر جديد
     */
    public function create()
    {
        return redirect()->route('personnel.index', ['open_create' => 1]);
    }

    /**
     * عرض تفاصيل كادر محدد
     */
    public function show(int $id)
    {
        $person = UnifiedPersonnel::findOrFail($id);
        ScopeByGovernorate::authorize($person->governorate_id);
        return redirect()->route('personnel.index', ['search' => $id]);
    }

    /**
     * تعديل بيانات كادر
     */
    public function edit(int $id)
    {
        $person = UnifiedPersonnel::findOrFail($id);
        ScopeByGovernorate::authorize($person->governorate_id);
        return redirect()->route('personnel.index', ['edit_id' => $id]);
    }

    /**
     * حفظ كادر أو فارس جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'sub_specialty' => 'nullable|string|max:100',
            'full_name' => 'required|string|max:150',
            'gender' => 'required|string|in:ذكر,أنثى',
            'birth_date' => 'nullable|date',
            'education_level' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'graduation_date' => 'nullable|date',
            'employer' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:50',
            'alt_phone' => 'nullable|string|max:50',
            'association_id' => 'nullable|exists:associations,id',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'uzlah_id' => 'required|exists:uzlahs,id',
            'sub_location_id' => 'nullable|exists:sub_locations,id',
            'training_program_name' => 'nullable|string|max:200',
            'target_group' => 'nullable|string|max:100',
            'training_date' => 'nullable|date',
            'blood_type' => 'nullable|string|max:10',
            'is_activated_in_field' => 'nullable|boolean',
            'activation_entity' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            // للمرشدين
            'general_program' => 'nullable|string|max:150',
            'initiative_type' => 'nullable|string|max:150',
            'trainer_name' => 'nullable|string|max:150',
            // لطلاب الزراعة
            'enrollment_year' => 'nullable|string|max:20',
            'coordinating_entity' => 'nullable|string|max:150',
            'activation_type' => 'nullable|string|max:100',
        ]);

        ScopeByGovernorate::authorize($validated['governorate_id']);

        if (!empty($validated['birth_date'])) {
            $validated['age'] = Carbon::parse($validated['birth_date'])->age;
        }

        $validated['code'] = UnifiedPersonnel::generateCode($validated['category']);
        $validated['is_activated_in_field'] = isset($validated['is_activated_in_field']) ? (bool)$validated['is_activated_in_field'] : false;

        $person = UnifiedPersonnel::create($validated);

        // تفريغ كاش لوحة التحكم
        Cache::forget('dashboard_kpis');
        Cache::forget('dashboard_recent');

        return redirect()->back()
            ->with('success', "تم تسجيل الكادر/الفارس ({$person->full_name}) بنجاح بالكود: {$person->code}");
    }

    /**
     * تحديث بيانات الكادر
     */
    public function update(Request $request, int $id)
    {
        $person = UnifiedPersonnel::findOrFail($id);
        ScopeByGovernorate::authorize($person->governorate_id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:50',
            'education_level' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'is_activated_in_field' => 'nullable|boolean',
            'activation_entity' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $validated['is_activated_in_field'] = isset($validated['is_activated_in_field']) ? (bool)$validated['is_activated_in_field'] : false;

        $person->update($validated);

        return redirect()->back()
            ->with('success', "تم تحديث بيانات ({$person->full_name}) بنجاح.");
    }

    /**
     * حذف كادر
     */
    public function destroy(int $id)
    {
        $person = UnifiedPersonnel::findOrFail($id);
        ScopeByGovernorate::authorize($person->governorate_id);
        $name = $person->full_name;
        $person->delete();

        return redirect()->back()
            ->with('success', "تم حذف سجل ({$name}) بنجاح.");
    }
}
