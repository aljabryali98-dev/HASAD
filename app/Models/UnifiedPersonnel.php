<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnifiedPersonnel extends Model
{
    use HasFactory;

    protected $table = 'unified_personnel';

    protected $guarded = ['id'];

    protected $casts = [
        'birth_date' => 'date',
        'graduation_date' => 'date',
        'training_date' => 'date',
        'is_activated_in_field' => 'boolean',
    ];

    /**
     * حساب العمر تلقائياً من تاريخ الميلاد
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->birth_date) {
                    return Carbon::parse($this->birth_date)->age;
                }
                return $value;
            }
        );
    }

    public static function generateCode(string $category = 'فرسان'): string
    {
        $prefix = mb_strpos($category, 'فرسان') !== false ? 'KNT' : 'PER';
        $count = static::count() + 1;
        $code = sprintf('%s-%05d', $prefix, $count);

        while (static::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%05d', $prefix, $count);
        }

        return $code;
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'association_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    public function uzlah(): BelongsTo
    {
        return $this->belongsTo(Uzlah::class, 'uzlah_id');
    }

    public function subLocation(): BelongsTo
    {
        return $this->belongsTo(SubLocation::class, 'sub_location_id');
    }

    public function scopeKnights($query)
    {
        return $query->where('category', 'فرسان الجمعية');
    }

    public function scopeByCategory($query, ?string $cat)
    {
        if (!empty($cat)) {
            return $query->where('category', $cat);
        }
        return $query;
    }
}
