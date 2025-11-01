<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterialUnit extends Model
{
    protected $table = 'rm_master_units';
    
    protected $fillable = [
        'company_id',
        'user_id',
        'material_id',
        'unit_name',
        'conversion_factor',
    ];
    
    protected $casts = [
        'conversion_factor' => 'decimal:4',
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
}
