<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBom extends Model
{
    protected $table = 'product_bom';

    protected $fillable = [
        'company_id',
        'user_id',
        'product_id',
        'material_id',
        'qty_required',
        'unit_id',
    ];

    protected $casts = [
        'qty_required' => 'decimal:4',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(FinishedProduct::class, 'product_id');
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
