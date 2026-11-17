<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerAndLand extends Model
{
    use HasFactory;

    protected $table = 'farmers_and_lands';
    protected $guarded = ['id'];
    protected $casts = [
        'total_land_area' => 'decimal:2',
        'cultivated_area' => 'decimal:2',
    ];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
    public function uzlah(): BelongsTo { return $this->belongsTo(Uzlah::class); }
    public function subLocation(): BelongsTo { return $this->belongsTo(SubLocation::class); }
}
