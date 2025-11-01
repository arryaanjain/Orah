<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesBook extends Model
{
    protected $table = 'sales_book';
    
    protected $fillable = [
        'company_id',
        'user_id',
        'order_id',
        'customer_id',
        'product_id',
        'qty',
        'unit_price',
        'total_amount',
        'sale_date',
        'payment_status',
        'notes',
    ];
    
    protected $casts = [
        'sale_date' => 'date',
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
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderBook::class, 'order_id');
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
