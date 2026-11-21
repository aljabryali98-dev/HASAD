<?php

namespace App\Models;

use App\Traits\ScopeByGovernorate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductiveGroup extends Model
{
    use HasFactory, ScopeByGovernorate;

    protected $table = 'productive_groups';

    protected $guarded = ['id'];

    protected $casts = [
        'founded_date' => 'date',
        'last_visit_date' => 'date',
        'last_update_date' => 'date',
        'productive_area' => 'decimal:2',
        'production_volume' => 'decimal:2',
        'production_value' => 'decimal:2',
        'sales_value' => 'decimal:2',
    ];

    public static function generateCode(?int $uzlahId = null): string
    {
        $prefix = 'GRP';
        if ($uzlahId) {
            $uzlah = Uzlah::find($uzlahId);
            if ($uzlah) {
                $prefix = "GRP-{$uzlah->code}";
            }
        }

        $count = static::count() + 1;
        $code = sprintf('%s-%04d', $prefix, $count);

        while (static::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%04d', $prefix, $count);
        }

        return $code;
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'association_id');
    }

    public function facilitators(): BelongsToMany
    {
        return $this->belongsToMany(Facilitator::class, 'group_facilitators')
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

    public function subLocation(): BelongsTo
    {
        return $this->belongsTo(SubLocation::class, 'sub_location_id');
    }
}
