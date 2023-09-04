<?php

namespace App\Rules;

use Closure;
use App\Models\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateOrderStatusId implements ValidationRule
{
    public string $orderStatus;

    public function __construct(string $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $orderStatusId = OrderStatus::where('uuid', $this->orderStatus)->first();
        if (!$orderStatusId) {
            $fail("Order status uuid is not found");
        }
    }
}
