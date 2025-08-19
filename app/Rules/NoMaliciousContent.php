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

        $maliciousTags = '/<\s*(script|style|iframe|object|embed)[^>]*>/i';

        // Bloquear atributos de evento (onclick, onerror, onload etc)
        $eventAttributes = '/<[^>]+on[a-z]+\s*=\s*["\'][^"\']*["\']/i';

        // Bloquear javascript: e data: nas URLs
        $maliciousUrls = '/(javascript:|data:)/i';

        if (
            preg_match($maliciousTags, $value) ||
            preg_match($eventAttributes, $value) ||
            preg_match($maliciousUrls, $value)
        ) {
            $fail(trans("validation.invalid_characters", [
                'attribute' => trans('validation.attributes.' . $attribute)
            ]));
        }
    }
}
