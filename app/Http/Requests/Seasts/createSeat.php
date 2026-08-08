<?php

namespace App\Http\Requests\Seasts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class createSeat extends FormRequest
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
            'row' => ['number'],
            // 'column_number' => ['number'],
            'number' => ['required', 'integer'],
            'customText' => ['required', 'string'],
            'x' => ['required', 'number'],
            'y' => ['required', 'number'],
            'type' => ['required', 'string'],
            'status' => ['required', 'string'],
            'price' => ['required', 'number']

        ];
    }
}
