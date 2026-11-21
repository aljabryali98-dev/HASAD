<?php

namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * استعراض قائمة مستخدمي النظام مع الإحصائيات والفلاتر
     */
    public function index(Request $request)
    {
        $query = User::with('governorates')->latest();

        // فلترة بالبحث النصي (الاسم، البريد، الهاتف)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // فلترة بالدور
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // فلترة بالحالة (نشط / معطل)
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', (bool)$request->input('status'));
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
        ];

        $rolePresets = User::rolePresets();

        return view('users.index', compact('users', 'stats', 'rolePresets'));
    }

    /**
     * شاشة إضافة مستخدم جديد وتحديد صلاحياته
     */
    public function create()
    {
        $availablePermissions = User::availablePermissions();
        $rolePresets = User::rolePresets();
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        return view('users.create', compact('availablePermissions', 'rolePresets', 'governorates'));
    }

    /**
     * حفظ مستخدم جديد في قاعدة البيانات
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'string', 'in:admin,super_admin,governorate_admin,supervisor,data_entry,viewer,custom'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'governorate_ids' => ['nullable', 'array'],
            'governorate_ids.*' => ['integer', 'exists:governorates,id'],
        ]);

        $permissions = $validated['permissions'] ?? [];

        // إذا كان الدور مدير نظام مركزي، يُمنح الرمز الشامل دائماً
        if (in_array($validated['role'], ['admin', 'super_admin'], true)) {
            $permissions = ['*'];
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'permissions' => $permissions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->has('governorate_ids')) {
            $user->governorates()->sync($request->input('governorate_ids', []));
        }

        return redirect()->route('users.index')->with('success', 'تم إنشاء حساب المستخدم وتعيين حزمة الصلاحيات والمحافظات بنجاح!');
    }

    /**
     * شاشة تعديل بيانات المستخدم وصلاحياته
     */
    public function edit(User $user)
    {
        $user->load('governorates');
        $availablePermissions = User::availablePermissions();
        $rolePresets = User::rolePresets();
        $governorates = Governorate::where('is_active', true)->orderBy('id')->get(['id', 'code', 'name_ar', 'name']);

        return view('users.edit', compact('user', 'availablePermissions', 'rolePresets', 'governorates'));
    }

    /**
     * تحديث بيانات المستخدم وصلاحياته
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'string', 'in:admin,super_admin,governorate_admin,supervisor,data_entry,viewer,custom'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'governorate_ids' => ['nullable', 'array'],
            'governorate_ids.*' => ['integer', 'exists:governorates,id'],
        ]);

        // حماية: منع المستخدم من تعطيل حسابه الخاص
        if ($user->id === auth()->id() && !$request->boolean('is_active')) {
            return back()->with('error', 'لا يمكنك تعطيل حسابك الحالي المسجل به في النظام.');
        }

        $permissions = $validated['permissions'] ?? [];

        if (in_array($validated['role'], ['admin', 'super_admin'], true)) {
            $permissions = ['*'];
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'permissions' => $permissions,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($request->has('governorate_ids')) {
            $user->governorates()->sync($request->input('governorate_ids', []));
        }

        return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم والصلاحيات والمحافظات بنجاح!');
    }

    /**
     * حذف حساب المستخدم
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف الحساب الخاص بك وأنت مسجل دخول به.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'لا يمكن حذف الحساب الأخير لمدير النظام المركزي لحماية الوصول.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف حساب المستخدم بنجاح.');
    }

    /**
     * التبديل السريع لحالة التفعيل (تفعيل / تجميد)
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تغيير حالة الحساب الخاص بك.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $statusText = $user->is_active ? 'تفعيل' : 'تعطيل';
        return back()->with('success', "تم {$statusText} حساب المستخدم بنجاح.");
    }
}
