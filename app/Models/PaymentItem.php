<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'row_no',
        'item_date',
        'product_code',
        'product_name',
        'unit_price',
        'quantity',
        'amount',
        'tax_rate',
        'tax_amount',
        'category',
        'notes',
    ];

    protected $casts = [
        'item_date' => 'date',
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}


