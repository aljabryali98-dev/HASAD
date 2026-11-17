<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationCrop extends Model
{
    use HasFactory;

    protected $table = 'association_crops';
    protected $guarded = ['id'];
    protected $casts = [
        'production_quantity' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'planting_date' => 'date',
        'harvest_date' => 'date',
    ];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
    public function uzlah(): BelongsTo { return $this->belongsTo(Uzlah::class); }
}
