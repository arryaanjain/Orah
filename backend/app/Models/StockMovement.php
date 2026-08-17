<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'company_id',
        'user_id',
        'material_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'qty_change',
        'unit_id',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'qty_change' => 'decimal:3',
        'movement_date' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'material_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RawMaterialUnit::class, 'unit_id');
    }
}
