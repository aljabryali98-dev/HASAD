<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgriculturalExtension extends Model
{
    use HasFactory;

    protected $table = 'agricultural_extensions';
    protected $guarded = ['id'];
    protected $casts = [
        'cost' => 'decimal:2',
        'activity_date' => 'date',
    ];

    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
    public function uzlah(): BelongsTo { return $this->belongsTo(Uzlah::class); }
}
