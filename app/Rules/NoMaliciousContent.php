<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoMaliciousContent implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $maliciousPattern = '/<[^>]*>|javascript:|data:|url\(|<script.*?>.*?<\/script>/i';

        if (preg_match($maliciousPattern, $value)) {
            $fail(
                trans("validation.invalid_characters", [
                    'attribute' => trans('validation.attributes.' . $attribute)
                ])
            );
        }
    }
}
