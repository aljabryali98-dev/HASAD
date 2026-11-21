<?php

namespace App\Models;

use App\Traits\ScopeByGovernorate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationShareholder extends Model
{
    use HasFactory, ScopeByGovernorate;

    protected $fillable = [
        'association_id',
        'shareholder_number',
        'full_name',
        'national_id',
        'phone',
        'gender',
        'governorate_id',
        'directorate_id',
        'uzlah_id',
        'sub_location_id',
        'shares_count',
        'share_value',
        'total_subscription_amount',
        'paid_amount',
        'remaining_amount',
        'join_date',
        'receipt_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'shares_count' => 'integer',
        'share_value' => 'decimal:2',
        'total_subscription_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'join_date' => 'date',
    ];

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function uzlah(): BelongsTo
    {
        return $this->belongsTo(Uzlah::class);
    }

    public function subLocation(): BelongsTo
    {
        return $this->belongsTo(SubLocation::class);
    }

    /**
     * توليد رقم تسلسلي للمساهم
     */
    public static function generateShareholderNumber(int $associationId): string
    {
        $count = static::where('association_id', $associationId)->count() + 1;
        return 'SH-' . str_pad((string)$associationId, 3, '0', STR_PAD_LEFT) . '-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }
}
