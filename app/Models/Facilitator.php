<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facilitator extends Model
{
    use HasFactory;

    protected $table = 'facilitators';

    protected $guarded = ['id'];

    protected $casts = [
        'assignment_date' => 'date',
    ];

    public static function generateCode(): string
    {
        $count = static::count() + 1;
        $code = sprintf('FAC-%04d', $count);
        while (static::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('FAC-%04d', $count);
        }
        return $code;
    }

    public function productiveGroups(): BelongsToMany
    {
        return $this->belongsToMany(ProductiveGroup::class, 'group_facilitators')
            ->withPivot('assigned_at', 'is_primary')
            ->withTimestamps();
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    public function uzlah(): BelongsTo
    {
        return $this->belongsTo(Uzlah::class, 'uzlah_id');
    }
}
