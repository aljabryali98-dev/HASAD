<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Models\Directorate;
use App\Models\Uzlah;
use App\Models\SubLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LocationApiController extends Controller
{
    /**
     * GET /api/locations/hierarchy
     * جلب الهيكل الجغرافي (محافظات، مديريات، عزل، والمواقع الفرعية التابعة لها)
     */
    public function hierarchy(Request $request): JsonResponse
    {
        $governorateId = $request->input('governorate_id');
        $directorateId = $request->input('directorate_id');
        $uzlahId = $request->input('uzlah_id');
        $search = $request->input('search');
        $entityType = $request->input('entity_type');

        // إذا كان هناك بحث نصي عن قرية أو جمعية أو موقع فرعي
        if (!empty($search)) {
            $subLocations = SubLocation::with(['uzlah:id,name_ar,code', 'directorate:id,name_ar,code', 'governorate:id,name_ar,code'])
                ->search($search)
                ->byType($entityType)
                ->limit(100)
                ->get();

            return response()->json([
                'status' => 'success',
                'count' => $subLocations->count(),
                'data' => $subLocations,
            ]);
        }

        // إذا تم طلب عزلة محددة
        if (!empty($uzlahId)) {
            $uzlah = Uzlah::with(['subLocations' => function ($query) use ($entityType) {
                $query->byType($entityType)->orderBy('name_ar');
            }, 'directorate:id,name_ar,code', 'governorate:id,name_ar,code'])->findOrFail($uzlahId);

            return response()->json([
                'status' => 'success',
                'level' => 'uzlah',
                'data' => $uzlah,
            ]);
        }

        // إذا تم طلب مديرية محددة
        if (!empty($directorateId)) {
            $directorate = Directorate::with(['uzlahs' => function ($query) use ($entityType) {
                $query->with(['subLocations' => function ($q) use ($entityType) {
                    $q->byType($entityType)->orderBy('name_ar');
                }])->orderBy('name_ar');
            }, 'governorate:id,name_ar,code'])->findOrFail($directorateId);

            return response()->json([
                'status' => 'success',
                'level' => 'directorate',
                'data' => $directorate,
            ]);
        }

        // إذا تم طلب محافظة محددة
        if (!empty($governorateId)) {
            $governorate = Governorate::with(['directorates' => function ($query) use ($entityType) {
                $query->with(['uzlahs' => function ($q) use ($entityType) {
                    $q->with(['subLocations' => function ($subQ) use ($entityType) {
                        $subQ->byType($entityType)->orderBy('name_ar');
                    }])->orderBy('name_ar');
                }])->orderBy('name_ar');
            }])->findOrFail($governorateId);

            return response()->json([
                'status' => 'success',
                'level' => 'governorate',
                'data' => $governorate,
            ]);
        }

        // جلب قائمة المحافظات الأساسية والمديريات
        $governorates = Governorate::select('id', 'code', 'name_ar', 'name')
            ->where('is_active', true)
            ->withCount(['directorates', 'uzlahs', 'subLocations'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'level' => 'national',
            'count' => $governorates->count(),
            'data' => $governorates,
        ]);
    }

    /**
     * POST /api/locations/sub-locations
     * إضافة (قرية / منطقة فرعية / جمعية تعاونية) جديدة ديناميكياً تحت عزلة محددة
     */
    public function storeSubLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uzlah_id' => 'required|integer|exists:uzlahs,id',
            'name_ar' => 'required|string|max:255',
            'entity_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:sub_locations,code',
            'is_active' => 'nullable|boolean',
        ]);

        $uzlah = Uzlah::findOrFail($validated['uzlah_id']);
        $entityType = $validated['entity_type'] ?? 'قرية';
        $nameAr = trim($validated['name_ar']);

        // التحقق من عدم التكرار تحت نفس العزلة والنوع
        $existing = SubLocation::where('uzlah_id', $uzlah->id)
            ->where('name_ar', $nameAr)
            ->where('entity_type', $entityType)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'exists',
                'message' => 'هذا المسمى مسجل مسبقاً تحت هذه العزلة',
                'data' => $existing->load(['uzlah', 'directorate', 'governorate']),
            ], 200);
        }

        // توليد كود تسلسلي تلقائي إذا لم يتم إرساله
        $code = !empty($validated['code'])
            ? trim($validated['code'])
            : SubLocation::generateCode($uzlah->id);

        $subLocation = SubLocation::create([
            'uzlah_id' => $uzlah->id,
            'directorate_id' => $uzlah->directorate_id,
            'governorate_id' => $uzlah->governorate_id,
            'code' => $code,
            'name_ar' => $nameAr,
            'entity_type' => $entityType,
            'notes' => $validated['notes'] ?? null,
            'is_active' => isset($validated['is_active']) ? (int)$validated['is_active'] : 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة الموقع الفرعي بنجاح',
            'data' => $subLocation->load(['uzlah', 'directorate', 'governorate']),
        ], 201);
    }

    /**
     * PUT /api/locations/sub-locations/{id}
     * تعديل المسمى أو النوع أو الحالة لموقع فرعي
     */
    public function updateSubLocation(Request $request, int $id): JsonResponse
    {
        $subLocation = SubLocation::findOrFail($id);

        $validated = $request->validate([
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('sub_locations')->where(function ($query) use ($request, $subLocation) {
                    return $query->where('uzlah_id', $subLocation->uzlah_id)
                                 ->where('entity_type', $request->input('entity_type', $subLocation->entity_type));
                })->ignore($subLocation->id),
            ],
            'entity_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name_ar'])) {
            $subLocation->name_ar = trim($validated['name_ar']);
        }

        if (isset($validated['entity_type'])) {
            $subLocation->entity_type = trim($validated['entity_type']);
        }

        if (array_key_exists('notes', $validated)) {
            $subLocation->notes = $validated['notes'];
        }

        if (isset($validated['is_active'])) {
            $subLocation->is_active = (int)$validated['is_active'];
        }

        $subLocation->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث البيانات بنجاح',
            'data' => $subLocation->load(['uzlah', 'directorate', 'governorate']),
        ]);
    }

    /**
     * DELETE /api/locations/sub-locations/{id}
     * الحذف الآمن للموقع الفرعي
     */
    public function destroySubLocation(int $id): JsonResponse
    {
        $subLocation = SubLocation::findOrFail($id);
        $subLocation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم الحذف بنجاح',
        ]);
    }

    /**
     * GET /api/locations/sub-locations/by-uzlah/{uzlahId}
     * جلب المواقع الفرعية لعزلة محددة (للقوائم المنسدلة المتتالية)
     */
    public function getSubLocationsByUzlah(int $uzlahId, Request $request): JsonResponse
    {
        $entityType = $request->input('type') ?? $request->input('entity_type');

        $query = SubLocation::where('uzlah_id', $uzlahId)->active();

        if (!empty($entityType)) {
            $query->where('entity_type', $entityType);
        }

        $items = $query->orderBy('name_ar')->get(['id', 'uzlah_id', 'code', 'name_ar', 'entity_type']);

        return response()->json($items);
    }
}
