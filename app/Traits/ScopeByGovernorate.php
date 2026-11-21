<?php

namespace App\Traits;

use App\Models\Governorate;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopeByGovernorate
{
    /**
     * نطاق استعلام محلي لتقييد السجلات بحسب المحافظات المصرح للمستخدم بالوصول إليها.
     *
     * @param Builder $query
     * @param User|null $user
     * @param string $column
     * @return Builder
     */
    public function scopeForUserGovernorates(Builder $query, ?User $user = null, string $column = 'governorate_id'): Builder
    {
        return static::applyGovernorateScope($query, $user, $column, $this->getTable());
    }

    /**
     * تطبيق نطاق المحافظات برمجياً على أي استعلام Builder.
     *
     * @param Builder $query
     * @param User|null $user
     * @param string $column
     * @param string|null $table
     * @return Builder
     */
    public static function applyGovernorateScope(Builder $query, ?User $user = null, string $column = 'governorate_id', ?string $table = null): Builder
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return $query;
        }

        $allowedIds = $user->allowedGovernorateIds();
        // إذا كان المستخدم مدير نظام مركزي أو لديه الصلاحية الشاملة (*)، فلا يوجد قيد
        if ($allowedIds === null) {
            return $query;
        }

        $qualifiedColumn = $column;
        if (!str_contains($column, '.')) {
            $tableName = $table ?? $query->getModel()->getTable();
            $qualifiedColumn = "{$tableName}.{$column}";
        }

        return $query->whereIn($qualifiedColumn, $allowedIds);
    }

    /**
     * تطبيق الفلترة على أي استعلام بشكل عام كدالة مساعدة ثابتة.
     *
     * @param Builder $query
     * @param User|null $user
     * @param string $column
     * @return Builder
     */
    public static function apply(Builder $query, ?User $user = null, string $column = 'governorate_id'): Builder
    {
        return static::applyGovernorateScope($query, $user, $column);
    }

    /**
     * التحقق من تخويل المستخدم للوصول إلى محافظة محددة، والرمي بخطأ 403 Forbidden في حال عدم التخويل.
     *
     * @param int|string|null $governorateId
     * @param User|null $user
     * @param string $message
     * @return void
     */
    public static function authorize(
        int|string|null $governorateId,
        ?User $user = null,
        string $message = 'غير مصرح لك بالوصول إلى بيانات هذا النطاق الجغرافي / المحافظة.'
    ): void {
        $user = $user ?? Auth::user();

        if (!$user) {
            abort(401, 'يرجى تسجيل الدخول أولاً.');
        }

        if (!$user->canAccessGovernorate($governorateId)) {
            abort(403, $message);
        }
    }

    /**
     * جلب استعلام المحافظات المصرح للمستخدم بالوصول إليها (للقوائم المنسدلة والفلاتر).
     *
     * @param User|null $user
     * @return Builder
     */
    public static function allowedGovernorates(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        $query = Governorate::where('is_active', true);

        if (!$user) {
            return $query;
        }

        $allowedIds = $user->allowedGovernorateIds();
        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        return $query;
    }

    /**
     * الحصول على مصفوفة المعرفات المصرح بها للمستخدم الحالي مباشرة.
     *
     * @param User|null $user
     * @return array<int>|null
     */
    public static function allowedIds(?User $user = null): ?array
    {
        $user = $user ?? Auth::user();
        return $user?->allowedGovernorateIds();
    }
}
