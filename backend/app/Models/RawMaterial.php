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
}
