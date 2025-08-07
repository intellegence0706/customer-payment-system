<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ghana',
        'gender',
        'postal_code',
        'address',
        'phone_number',
        'note',
        'bank_name',
        'branch_name',
        'account_name',
        'account_ghana',
        'account_number',
        'account_holder',
        'customer_number',
        'bank_note',
        'bank_code',
        'branch_code'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function setBankCodeAttribute($value)
    {
        $this->attributes['bank_code'] = $value;
        if ($value && strlen($value) === 4) {
            $this->attributes['bank_name'] = $this->getBankNameByCode($value);
        }
    }

    public function setBranchCodeAttribute($value)
    {
        $this->attributes['branch_code'] = $value;
        if ($value && strlen($value) === 3) {
            $this->attributes['branch_name'] = $this->getBranchNameByCode($value);
        }
    }

    private function getBankNameByCode($code)
    {
        $banks = [
            '0001' => 'Ghana Commercial Bank',
            '0002' => 'Standard Chartered Bank',
            '0003' => 'Barclays Bank Ghana',
            '0004' => 'Ecobank Ghana',
            '0005' => 'Zenith Bank Ghana',
            // Add more bank codes as needed
        ];

        return $banks[$code] ?? null;
    }

    private function getBranchNameByCode($code)
    {
        $branches = [
            '001' => 'Accra Main Branch',
            '002' => 'Kumasi Branch',
            '003' => 'Tamale Branch',
            '004' => 'Cape Coast Branch',
            '005' => 'Takoradi Branch',
            // Add more branch codes as needed
        ];

        return $branches[$code] ?? null;
    }
}
