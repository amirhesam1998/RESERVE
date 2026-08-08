<?php

namespace App\Http\Requests\users;

use App\Rules\ReCaptcha;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')],
            'phone_number' => ['required',  'regex:/^(09\d{9}|\+98\d{9})$/', Rule::unique('users')],
            'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()],
            'g-recaptcha-response' => [new ReCaptcha()]
        ];
    }
}
