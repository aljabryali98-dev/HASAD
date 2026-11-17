<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uzlah extends Model
{
    use HasFactory;

    protected $table = 'uzlahs';

    protected $fillable = [
        'id',
        'directorate_id',
        'governorate_id',
        'code',
        'name_ar',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?: $this->name_ar,
        );
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class, 'uzlah_id');
    }

    public function subLocations(): HasMany
    {
        return $this->hasMany(SubLocation::class, 'uzlah_id');
    }
}
