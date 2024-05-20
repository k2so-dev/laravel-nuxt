<?php

namespace App\Rules;

use App\Models\TemporaryUpload;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;

class TemporaryFileExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !Storage::disk('public')->exists($value) ||
            !TemporaryUpload::where('path', $value)->exists()
        ) {
            $fail(__('The :attribute does not exist.', ['attribute' => $attribute]));
        }
    }
}
