<?php

namespace App\Models;

use App\Traits\ScopeByGovernorate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationProject extends Model
{
    use HasFactory, ScopeByGovernorate;

    protected $table = 'association_projects';
    protected $guarded = ['id'];
    protected $casts = ['cost' => 'decimal:2'];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
    public function governorate(): BelongsTo { return $this->belongsTo(Governorate::class); }
    public function directorate(): BelongsTo { return $this->belongsTo(Directorate::class); }
    public function uzlah(): BelongsTo { return $this->belongsTo(Uzlah::class); }
}
