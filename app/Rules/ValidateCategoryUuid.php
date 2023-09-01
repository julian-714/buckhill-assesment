<?php

namespace App\Rules;

use Closure;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateCategoryUuid implements ValidationRule
{
    public string $categoryUuid;
    public function __construct(string $categoryUuid)
    {
        $this->categoryUuid = $categoryUuid;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $categoryId = Category::where('uuid', $this->categoryUuid)->first();
        if (!$categoryId) {
            $fail("Category uuid is not found");
        }
    }
}
