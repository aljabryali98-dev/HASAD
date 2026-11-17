<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyAndResearch extends Model
{
    use HasFactory;

    protected $table = 'studies_and_research';
    protected $guarded = ['id'];
    protected $casts = [
        'cost' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'study_date' => 'date',
    ];

    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
}
