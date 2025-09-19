<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class GhanaBankCode implements Rule
{
    private $validBankCodes = [
        '0001', '0002', '0003', '0004', '0005', '0006', '0007', '0008', '0009', '0010',
        '0011', '0012', '0013', '0014', '0015', '0016', '0017', '0018', '0019', '0020'
    ];

    public function passes($attribute, $value)
    {
        return in_array($value, $this->validBankCodes);
    }

    public function message()
    {
        return 'The :attribute must be a valid Ghana bank code.';
    }
}
