<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationLoan extends Model
{
    use HasFactory;

    protected $table = 'association_loans';

    protected $guarded = ['id'];

    protected $casts = [
        'received_date' => 'date',
        'loan_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'completion_rate' => 'decimal:2',
    ];

    /**
     * حساب الرصيد المتبقي تلقائياً (مبلغ القرض - المبلغ المسدد)
     */
    protected function remainingBalance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (float) max(0, (float)$this->loan_amount - (float)$this->paid_amount),
            set: fn ($value) => max(0, (float)$this->loan_amount - (float)$this->paid_amount),
        );
    }

    public static function generateCode(): string
    {
        $count = static::count() + 1;
        $code = sprintf('LOAN-%05d', $count);
        while (static::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('LOAN-%05d', $count);
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
}
