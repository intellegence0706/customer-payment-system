<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class GhanaPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Ghana phone number validation
        // Format: +233XXXXXXXXX or 0XXXXXXXXX
        return preg_match('/^(\+233|0)[2-9][0-9]{8}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid Ghana phone number.';
    }
}
