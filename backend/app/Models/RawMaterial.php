<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $table = 'rm_master';
    
    protected $fillable = [
        'company_id',
        'user_id',
        'material',
        'description',
        'base_unit',
        'minimum_stock',
    ];
    
    protected $casts = [
        'minimum_stock' => 'decimal:3',
    ];
    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function units(): HasMany
    {
        return $this->hasMany(RawMaterialUnit::class, 'material_id');
    }
    
    public function purchases(): HasMany
    {
        return $this->hasMany(RawMaterialPurchase::class, 'material_id');
    }

    /**
     * Calculate available stock in raw material's base unit.
     * Converts all purchases and stock movement consumptions using their unit conversion factors.
     */
    public static function getAvailableStockInBaseUnit(int $materialId, int $companyId): float
    {
        $purchases = RawMaterialPurchase::where('material_id', $materialId)
            ->where('company_id', $companyId)
            ->with('unit')
            ->get();

        $totalPurchasedBase = 0.0;
        foreach ($purchases as $p) {
            $factor = ($p->unit && (float) $p->unit->conversion_factor > 0) 
                ? (float) $p->unit->conversion_factor 
                : 1.0;
            $totalPurchasedBase += ((float) $p->qty * $factor);
        }

        $movements = StockMovement::where('material_id', $materialId)
            ->where('company_id', $companyId)
            ->where('movement_type', 'consumption')
            ->with('unit')
            ->get();

        $totalConsumedBase = 0.0;
        foreach ($movements as $m) {
            $factor = ($m->unit && (float) $m->unit->conversion_factor > 0) 
                ? (float) $m->unit->conversion_factor 
                : 1.0;
            $totalConsumedBase += (abs((float) $m->qty_change) * $factor);
        }

        return max(0.0, $totalPurchasedBase - $totalConsumedBase);
    }
}
