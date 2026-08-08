<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class ReCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response=Http::withoutVerifying()->asForm()->get('https://www.google.com/recaptcha/api/siteverify', [
            'secret'=>env('GOOGLE_RECAPTCHA_SECRET'),
            'response'=>$value
        ])->json();
    }
}
