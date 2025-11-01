<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterialPurchase extends Model
{
    protected $table = 'rm_purchase';
    
    protected $fillable = [
        'company_id',
        'user_id',
        'material_id',
        'supplier_name',
        'purchase_date',
        'qty',
        'unit_id',
        'rate',
        'total_amount',
        'batch_number',
        'expiry_date',
    ];
    
    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'qty' => 'decimal:3',
        'rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
