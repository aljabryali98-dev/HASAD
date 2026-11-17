<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropExpansion extends Model
{
    use HasFactory;

    protected $table = 'crop_expansions';
    protected $guarded = ['id'];
    protected $casts = [
        'expanded_area_hectare' => 'decimal:2',
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
        'record_date' => 'date',
    ];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
}
