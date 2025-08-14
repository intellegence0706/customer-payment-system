<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'payment_month',
        'payment_year',
        'amount',
        'subtotal_amount',
        'tax_total',
        'other_fees_total',
        'grand_total',
        'payment_date',
        'receipt_number',
        'status',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'other_fees_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }
}
