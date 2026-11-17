<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractFarming extends Model
{
    use HasFactory;

    protected $table = 'contract_farming';
    protected $guarded = ['id'];
    protected $casts = [
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'contract_date' => 'date',
    ];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
    public function uzlah(): BelongsTo { return $this->belongsTo(Uzlah::class); }
    public function subLocation(): BelongsTo { return $this->belongsTo(SubLocation::class); }
}
