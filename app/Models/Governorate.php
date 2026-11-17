<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Governorate extends Model
{
    use HasFactory;

    protected $table = 'governorates';

    protected $fillable = [
        'id',
        'code',
        'name_ar',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * الحقل name يرجع دائماً name_ar في حال كان فارغاً
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?: $this->name_ar,
        );
    }

    public function directorates(): HasMany
    {
        return $this->hasMany(Directorate::class, 'governorate_id');
    }

    public function districts(): HasMany
    {
        return $this->hasMany(Directorate::class, 'governorate_id');
    }

    public function uzlahs(): HasMany
    {
        return $this->hasMany(Uzlah::class, 'governorate_id');
    }

    public function subDistricts(): HasMany
    {
        return $this->hasMany(Uzlah::class, 'governorate_id');
    }

    public function subLocations(): HasMany
    {
        return $this->hasMany(SubLocation::class, 'governorate_id');
    }
}
