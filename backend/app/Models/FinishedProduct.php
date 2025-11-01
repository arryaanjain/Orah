<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedProduct extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'product_name',
        'description',
        'base_unit',
        'selling_price',
        'minimum_stock',
        'is_active',
    ];
    
    protected $casts = [
        'selling_price' => 'decimal:2',
        'minimum_stock' => 'decimal:3',
        'is_active' => 'boolean',
    ];
    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
