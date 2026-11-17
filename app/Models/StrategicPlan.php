<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategicPlan extends Model
{
    use HasFactory;

    protected $table = 'strategic_plans';
    protected $guarded = ['id'];
    protected $casts = [
        'cost' => 'decimal:2',
        'completion_rate' => 'decimal:2',
    ];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
}
