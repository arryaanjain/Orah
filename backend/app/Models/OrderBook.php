<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderBook extends Model
{
    protected $table = 'order_book';
    
    protected $fillable = [
        'company_id',
        'user_id',
        'customer_id',
        'product_id',
        'qty',
        'unit_price',
        'total_amount',
        'order_date',
        'expected_delivery_date',
        'status',
        'notes',
    ];
    
    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'qty' => 'decimal:3',
        'unit_price' => 'decimal:2',
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
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(FinishedProduct::class, 'product_id');
    }
}
