<?php

namespace App\Rules;

use Closure;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidatePaymentId implements ValidationRule
{
    public string $paymentUuid;
    public function __construct(string $paymentUuid)
    {
        $this->paymentUuid = $paymentUuid;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $paymentId = Payment::where('uuid', $this->paymentUuid)->first();
        if (!$paymentId) {
            $fail("Payment uuid is not found");
        }
    }
}
