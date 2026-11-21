<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'role', 'permissions', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * العلاقة مع المحافظات المصرح للمستخدم بالوصول إليها وإدارتها.
     */
    public function governorates(): BelongsToMany
    {
        return $this->belongsToMany(Governorate::class, 'governorate_user', 'user_id', 'governorate_id')->withTimestamps();
    }

    /**
     * الحصول على معرفات المحافظات المصرح للمستخدم بالوصول إليها.
     * يعيد null إذا كان المستخدم مدير نظام مركزي (super_admin) أو لديه الصلاحية الشاملة (*) / isAdmin.
     * وإلا يعيد مصفوفة من المعرفات الرقمية (integer IDs) للمحافظات المحددة له.
     *
     * @return array<int>|null
     */
    public function allowedGovernorateIds(): ?array
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return null;
        }

        $perms = $this->permissions ?? [];
        if (in_array('*', $perms, true)) {
            return null;
        }

        if ($this->relationLoaded('governorates')) {
            return $this->governorates->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        return $this->governorates()->pluck('governorates.id')->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * التحقق مما إذا كان للمستخدم صلاحية الوصول لمحافظة محددة.
     */
    public function canAccessGovernorate(int|string|null $governorateId): bool
    {
        $allowed = $this->allowedGovernorateIds();
        if ($allowed === null) {
            return true;
        }
        if ($governorateId === null) {
            return false;
        }
        return in_array((int) $governorateId, $allowed, true);
    }

    /**
     * Check if user is super admin (full central system admin).
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || $this->role === 'admin' || in_array('*', $this->permissions ?? [], true);
    }

    /**
     * Check if user is governorate admin / supervisor.
     */
    public function isGovernorateAdmin(): bool
    {
        return $this->role === 'governorate_admin' || $this->role === 'supervisor';
    }

    /**
     * Check if user is data entry.
     */
    public function isDataEntry(): bool
    {
        return $this->role === 'data_entry';
    }

    /**
     * Check if user is viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /**
     * Check if user is system administrator.
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user has specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has a specific granular permission.
     */
    public function hasPermission(string $permission): bool
    {
        // مدير النظام المركزي يملك كامل الصلاحيات دائماً دون قيود
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->is_active) {
            return false;
        }

        $perms = $this->permissions ?? [];

        // إذا تم إعطاؤه الرمز الشامل للنظام
        if (in_array('*', $perms, true)) {
            return true;
        }

        // مطابقة الصلاحية المباشرة
        if (in_array($permission, $perms, true)) {
            return true;
        }

        // فحص البادئة العامة للمحور (مثل associations.*)
        $parts = explode('.', $permission);
        if (count($parts) > 1 && in_array($parts[0] . '.*', $perms, true)) {
            return true;
        }

        return false;
    }

    /**
     * قائمة كافة الصلاحيات المتاحة في النظام مصنفة بحسب المحاور
     */
    public static function availablePermissions(): array
    {
        return [
            'dashboard' => [
                'title' => 'لوحة المؤشرات والتحليلات المركزية',
                'icon' => 'fa-gauge-high',
                'permissions' => [
                    'dashboard.view' => 'استعراض لوحة المؤشرات والإحصائيات العامة',
                    'dashboard.financial' => 'الاطلاع على المؤشرات المالية والأرقام النقدية',
                ],
            ],
            'associations' => [
                'title' => 'الجمعيات التعاونية (الكيان المركزي)',
                'icon' => 'fa-building-columns',
                'permissions' => [
                    'associations.view' => 'استعراض سجل وقوائم الجمعيات التعاونية',
                    'associations.create' => 'تسجيل وتأسيس جمعيات جديدة',
                    'associations.edit' => 'تعديل بيانات الجمعيات وتشكيل مجالس الإدارة',
                    'associations.delete' => 'حذف أو تجميد سجلات الجمعيات',
                ],
            ],
            'productive_groups' => [
                'title' => 'المجاميع الإنتاجية',
                'icon' => 'fa-people-roof',
                'permissions' => [
                    'productive_groups.view' => 'استعراض سجل المجاميع الإنتاجية',
                    'productive_groups.create' => 'إنشاء وتشكيل مجاميع إنتاجية جديدة',
                    'productive_groups.edit' => 'تعديل بيانات المجاميع وتكليف وتغيير الميسرين',
                    'productive_groups.delete' => 'حذف المجاميع الإنتاجية',
                ],
            ],
            'personnel' => [
                'title' => 'الكوادر والفرسان وبنك المعلومات',
                'icon' => 'fa-user-graduate',
                'permissions' => [
                    'personnel.view' => 'استعراض سجل وبنك الكوادر والفرسان الميدانيين',
                    'personnel.create' => 'تسجيل وإضافة فرسان وكوادر جديدة',
                    'personnel.edit' => 'تعديل بيانات الكوادر والمؤهلات وتوزيع المهام',
                    'personnel.delete' => 'حذف قيود الكوادر',
                ],
            ],
            'projects' => [
                'title' => 'المشاريع والمبادرات المجتمعية والتوعية',
                'icon' => 'fa-diagram-project',
                'permissions' => [
                    'projects.view' => 'استعراض مشاريع الجمعيات والمبادرات والتوعية',
                    'projects.manage' => 'إدارة وإنشاء وتعديل مشاريع الجمعيات والمبادرات والتوعية',
                ],
            ],
            'agriculture' => [
                'title' => 'الزراعة والمحاصيل والمشاتل والمزارعين',
                'icon' => 'fa-wheat-awn',
                'permissions' => [
                    'crops.view' => 'استعراض سجل المحاصيل والإنتاج والمشاتل والمزارعين',
                    'crops.manage' => 'إدارة وإضافة وتعديل بيانات المحاصيل والمشاتل والمزارعين',
                ],
            ],
            'loans' => [
                'title' => 'محفظة القروض التمويلية البيضاء (0% فوائد)',
                'icon' => 'fa-hand-holding-dollar',
                'permissions' => [
                    'loans.view' => 'استعراض سجل القروض والتمويلات التمكينية',
                    'loans.manage' => 'منح وجدولة وسداد القروض البيضاء ومتابعة المحفظة',
                ],
            ],
            'geography' => [
                'title' => 'المستكشف الجغرافي والتقسيم الإداري',
                'icon' => 'fa-earth-asia',
                'permissions' => [
                    'geography.view' => 'استعراض المستكشف الجغرافي الشامل (34 قسماً) والخرائط',
                    'geography.manage' => 'إدارة التقسيم الإداري وربط القرى بالعزل والجمعيات',
                ],
            ],
            'import' => [
                'title' => 'الاستيراد الذكي الشامل من ملفات الإكسل',
                'icon' => 'fa-file-excel',
                'permissions' => [
                    'import.view' => 'الدخول إلى مركز الاستيراد الذكي الشامل',
                    'import.execute' => 'رفع وتسكين ومعالجة ملفات الإكسل في قاعدة البيانات المركزية',
                ],
            ],
            'users' => [
                'title' => 'إدارة المستخدمين وضبط الصلاحيات',
                'icon' => 'fa-users-gear',
                'permissions' => [
                    'users.view' => 'استعراض سجل مستخدمي النظام وحالات الدخول',
                    'users.manage' => 'إضافة وتعديل وحذف المستخدمين والتحكم بكامل الصلاحيات والأدوار',
                ],
            ],
        ];
    }

    /**
     * قوالب الأدوار الجاهزة لتسهيل الضبط السريع
     */
    public static function rolePresets(): array
    {
        return [
            'admin' => [
                'name' => 'مدير النظام المركزي',
                'badge_class' => 'bg-danger-subtle text-danger border-danger-subtle',
                'description' => 'صلاحية سيادية كاملة على كافة المحاور والوظائف والعمليات بدون قيود (*)',
                'permissions' => ['*'],
            ],
            'governorate_admin' => [
                'name' => 'مدير فرع / نطاق المحافظة',
                'badge_class' => 'bg-success-subtle text-success border-success-subtle',
                'description' => 'إدارة شاملة لبيانات ومشاريع وجمعيات المحافظات المصرح له بها فقط',
                'permissions' => [
                    'dashboard.view', 'dashboard.financial',
                    'associations.view', 'associations.create', 'associations.edit',
                    'productive_groups.view', 'productive_groups.create', 'productive_groups.edit',
                    'personnel.view', 'personnel.create', 'personnel.edit',
                    'projects.view', 'projects.manage',
                    'crops.view', 'crops.manage',
                    'loans.view',
                    'geography.view',
                    'import.view',
                ],
            ],
            'supervisor' => [
                'name' => 'مشرف ميداني عام',
                'badge_class' => 'bg-primary-subtle text-primary border-primary-subtle',
                'description' => 'إدارة الجمعيات والمجاميع والكوادر والمشاريع والمحاصيل واستعراض الإحصائيات',
                'permissions' => [
                    'dashboard.view', 'dashboard.financial',
                    'associations.view', 'associations.create', 'associations.edit',
                    'productive_groups.view', 'productive_groups.create', 'productive_groups.edit',
                    'personnel.view', 'personnel.create', 'personnel.edit',
                    'projects.view', 'projects.manage',
                    'crops.view', 'crops.manage',
                    'loans.view',
                    'geography.view',
                    'import.view',
                ],
            ],
            'data_entry' => [
                'name' => 'مدخل بيانات ومسجل',
                'badge_class' => 'bg-info-subtle text-info border-info-subtle',
                'description' => 'إدخال وتسجيل الجمعيات والفرسان والمجاميع والمحاصيل بدون صلاحيات حذف أو تعديل سيادي',
                'permissions' => [
                    'dashboard.view',
                    'associations.view', 'associations.create',
                    'productive_groups.view', 'productive_groups.create',
                    'personnel.view', 'personnel.create',
                    'projects.view',
                    'crops.view',
                    'geography.view',
                ],
            ],
            'viewer' => [
                'name' => 'مستعرض ومحلل فقط (قراءة فقط)',
                'badge_class' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                'description' => 'استعراض المؤشرات والجداول والخرائط الجغرافية دون إمكانية التعديل أو الحذف',
                'permissions' => [
                    'dashboard.view',
                    'associations.view',
                    'productive_groups.view',
                    'personnel.view',
                    'projects.view',
                    'crops.view',
                    'loans.view',
                    'geography.view',
                ],
            ],
            'custom' => [
                'name' => 'صلاحيات مخصصة (Custom)',
                'badge_class' => 'bg-warning-subtle text-warning border-warning-subtle',
                'description' => 'تحديد يدوي دقيق للأقسام والعمليات المتاحة لهذا المستخدم بعينه',
                'permissions' => [],
            ],
        ];
    }
}
