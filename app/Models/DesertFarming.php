<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesertFarming extends Model
{
    use HasFactory;

    protected $table = 'desert_farmings';
    protected $guarded = ['id'];
    protected $casts = [
        'red_corn_seeds_kg' => 'decimal:2',
        'white_corn_seeds_kg' => 'decimal:2',
        'millet_seeds_kg' => 'decimal:2',
        'shahbi_seeds_kg' => 'decimal:2',
        'dujrah_seeds_kg' => 'decimal:2',
        'total_work_hours' => 'decimal:2',
        'work_area_maad' => 'decimal:2',
        'diesel_quantity_liters' => 'decimal:2',
        'seeded_area_maad' => 'decimal:2',
        'drivers_expenses' => 'decimal:2',
        'workers_wages' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'planting_date' => 'date',
    ];

    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
}
