<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationProduct extends Model
{
    use HasFactory;

    protected $table = 'association_products';
    protected $guarded = ['id'];
    protected $casts = [
        'annual_quantity' => 'decimal:2',
    ];

    public function association(): BelongsTo { return $this->belongsTo(Association::class); }
}
