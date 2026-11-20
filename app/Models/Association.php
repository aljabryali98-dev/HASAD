<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Association extends Model
{
    use HasFactory;

    protected $table = 'associations';

    protected $guarded = ['id'];

    protected $casts = [
        'has_bylaws' => 'boolean',
        'has_member_registry' => 'boolean',
        'has_founding_minutes' => 'boolean',
        'has_headquarters' => 'boolean',
        'holds_regular_meetings' => 'boolean',
        'has_bank_account' => 'boolean',
        'has_accounting_records' => 'boolean',
        'license_date' => 'date',
        'founded_date' => 'date',
        'announced_date' => 'date',
        'last_general_assembly_date' => 'date',
        'last_election_date' => 'date',
        'board_term_end_date' => 'date',
        'last_data_update_date' => 'date',
        'last_approved_budget_date' => 'date',
        'last_field_visit_date' => 'date',
        'initial_capital' => 'decimal:2',
        'current_capital' => 'decimal:2',
        'share_value' => 'decimal:2',
        'annual_subscription_fee' => 'decimal:2',
        'total_assets_value' => 'decimal:2',
        'estimated_assets_value' => 'decimal:2',
    ];

    /**
     * إجمالي قيمة الأسهم المحسوبة تلقائياً (عدد الأسهم × قيمة السهم)
     */
    protected function totalSharesValue(): Attribute
    {
        return Attribute::make(
            get: fn () => (float)($this->shares_count * $this->share_value),
        );
    }

    /**
     * توليد كود تسلسلي فريد تلقائياً للجمعية
     */
    public static function generateCode(?int $uzlahId = null): string
    {
        $prefix = 'ASS';
        if ($uzlahId) {
            $uzlah = Uzlah::find($uzlahId);
            if ($uzlah) {
                $prefix = "ASS-{$uzlah->code}";
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

    // العلاقات الجغرافية
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

    // علاقات ملف الجمعية الشامل (360° Profile)
    public function boardMembers(): HasMany
    {
        return $this->hasMany(AssociationBoardMember::class, 'association_id');
    }

    public function shareholders(): HasMany
    {
        return $this->hasMany(AssociationShareholder::class, 'association_id');
    }

    public function productiveGroups(): HasMany
    {
        return $this->hasMany(ProductiveGroup::class, 'association_id');
    }

    public function personnel(): HasMany
    {
        return $this->hasMany(UnifiedPersonnel::class, 'association_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssociationLoan::class, 'association_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(AssociationProject::class, 'association_id');
    }

    public function crops(): HasMany
    {
        return $this->hasMany(AssociationCrop::class, 'association_id');
    }

    public function contractFarmings(): HasMany
    {
        return $this->hasMany(ContractFarming::class, 'association_id');
    }

    public function nurseries(): HasMany
    {
        return $this->hasMany(Nursery::class, 'association_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(AssociationProduct::class, 'association_id');
    }

    public function farmers(): HasMany
    {
        return $this->hasMany(FarmerAndLand::class, 'association_id');
    }

    public function unitTrainings(): HasMany
    {
        return $this->hasMany(UnitTraining::class, 'association_id');
    }

    public function fuelStations(): HasMany
    {
        return $this->hasMany(FuelStation::class, 'association_id');
    }
}
