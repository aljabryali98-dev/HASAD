<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Directorate extends Model
{
    use HasFactory;

    protected $table = 'directorates';

    protected $fillable = [
        'id',
        'governorate_id',
        'code',
        'name_ar',
        'name',
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

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function uzlahs(): HasMany
    {
        return $this->hasMany(Uzlah::class, 'directorate_id');
    }

    public function subDistricts(): HasMany
    {
        return $this->hasMany(Uzlah::class, 'directorate_id');
    }

    public function villages(): HasManyThrough
    {
        return $this->hasManyThrough(Village::class, Uzlah::class, 'directorate_id', 'uzlah_id');
    }

    public function subLocations(): HasMany
    {
        return $this->hasMany(SubLocation::class, 'directorate_id');
    }
}
