<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends Model
{
    use HasFactory;

    protected $table = 'villages';

    protected $fillable = [
        'uzlah_id',
        'sub_district_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function uzlah(): BelongsTo
    {
        return $this->belongsTo(Uzlah::class, 'uzlah_id');
    }

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(Uzlah::class, 'uzlah_id');
    }
}
