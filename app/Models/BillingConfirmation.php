<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BillingConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'target_year_month',
        'company_name',
        'customer_display_name',
        'billing_year_month',
        'target_month',
        'rental_equipment_total',
        'previous_billing_total',
        'other_costs_total',
        'other_billing_total',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'payment_date',
        'payment_amount',
        'receipt_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'rental_equipment_total' => 'decimal:2',
        'previous_billing_total' => 'decimal:2',
        'other_costs_total' => 'decimal:2',
        'other_billing_total' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    // Section-specific relationships
    public function rentalEquipmentItems()
    {
        return $this->billingItems()->where('item_section', 'rental_equipment')->orderBy('display_order');
    }

    public function previousBillingItems()
    {
        return $this->billingItems()->where('item_section', 'previous_billing')->orderBy('display_order');
    }

    public function otherCostsItems()
    {
        return $this->billingItems()->where('item_section', 'other_costs')->orderBy('display_order');
    }

    public function otherBillingItems()
    {
        return $this->billingItems()->where('item_section', 'other_billing')->orderBy('display_order');
    }

    // Scopes
    public function scopeByTargetMonth($query, $yearMonth)
    {
        return $query->where('target_year_month', $yearMonth);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_CONFIRMED]);
    }

    // Helper methods
    public function calculateTotals()
    {
        // Calculate section totals
        $this->rental_equipment_total = $this->rentalEquipmentItems()->sum('amount');
        $this->previous_billing_total = $this->previousBillingItems()->sum('amount');
        $this->other_costs_total = $this->otherCostsItems()->sum('amount');
        $this->other_billing_total = $this->otherBillingItems()->sum('amount');

        // Calculate overall totals
        $this->subtotal_amount = $this->rental_equipment_total + 
                                $this->previous_billing_total + 
                                $this->other_costs_total + 
                                $this->other_billing_total;

        $this->tax_amount = $this->billingItems()->sum('tax_amount');
        $this->total_amount = $this->subtotal_amount + $this->tax_amount;

        return $this;
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->payment_amount;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->remaining_balance <= 0;
    }

    // Format target year month for display (e.g., "令和07年05月")
    public function getFormattedTargetYearMonthAttribute()
    {
        return $this->target_year_month;
    }

    // Convert Reiwa year to Gregorian year
    public function getGregorianYearAttribute()
    {
        if (preg_match('/令和(\d+)年(\d+)月/', $this->target_year_month, $matches)) {
            return (int)$matches[1] + 2018; // Reiwa 1 = 2019
        }
        return date('Y');
    }

    public function getTargetMonthNumberAttribute()
    {
        if (preg_match('/令和(\d+)年(\d+)月/', $this->target_year_month, $matches)) {
            return (int)$matches[2];
        }
        return date('n');
    }
}

