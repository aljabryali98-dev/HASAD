<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationBoardMember extends Model
{
    use HasFactory;

    protected $table = 'association_board_members';

    protected $guarded = ['id'];

    protected $casts = [
        'birth_date' => 'date',
        'assignment_start_date' => 'date',
        'assignment_end_date' => 'date',
    ];

    /**
     * حساب العمر تلقائياً من تاريخ الميلاد
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->birth_date) {
                    return Carbon::parse($this->birth_date)->age;
                }
                return null;
            }
        );
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'association_id');
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
