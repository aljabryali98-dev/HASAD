<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubLocation extends Model
{
    use HasFactory;

    protected $table = 'sub_locations';

    protected $fillable = [
        'uzlah_id',
        'directorate_id',
        'governorate_id',
        'code',
        'name_ar',
        'entity_type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'uzlah_id' => 'integer',
        'directorate_id' => 'integer',
        'governorate_id' => 'integer',
    ];

    /**
     * العلاقة مع العزلة
     */
    public function uzlah(): BelongsTo
    {
        return $this->belongsTo(Uzlah::class, 'uzlah_id');
    }

    /**
     * العلاقة مع المديرية
     */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    /**
     * العلاقة مع المحافظة
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    /**
     * توليد كود تسلسلي فريد تلقائياً تحت العزلة
     */
    public static function generateCode(int $uzlahId): string
    {
        $uzlah = Uzlah::find($uzlahId);
        $prefix = $uzlah ? $uzlah->code : (string)$uzlahId;

        // البحث عن آخر تسلسل لنفس العزلة
        $lastRecord = static::where('uzlah_id', $uzlahId)
            ->where('code', 'like', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->code, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        } else {
            // كخطة بديلة نعتمد على العدد الحالي + 1
            $count = static::where('uzlah_id', $uzlahId)->count();
            $nextSeq = $count + 1;
        }

        $code = sprintf('%s-%04d', $prefix, $nextSeq);

        // التأكد من عدم التصادم
        while (static::where('code', $code)->exists()) {
            $nextSeq++;
            $code = sprintf('%s-%04d', $prefix, $nextSeq);
        }

        return $code;
    }

    /**
     * نطاق السجلات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق التصفية حسب النوع
     */
    public function scopeByType($query, ?string $type)
    {
        if (!empty($type)) {
            return $query->where('entity_type', $type);
        }
        return $query;
    }

    /**
     * نطاق البحث النصي
     */
    public function scopeSearch($query, ?string $term)
    {
        if (!empty($term)) {
            return $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%");
            });
        }
        return $query;
    }
}
