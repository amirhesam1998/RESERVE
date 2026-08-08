<?php

namespace App\Http\Requests\Salon;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'name' => ['required', 'min:3', Rule::unique('salons')],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'nullable'],
            'address' => ['required', 'min:3'],
            'main_category' => ['required', 'exists:categories,id'],
            'child_categories' => ['nullable', 'array'],
            'child_categories.*' => ['exists:categories,id'],
        ];
    }
}
