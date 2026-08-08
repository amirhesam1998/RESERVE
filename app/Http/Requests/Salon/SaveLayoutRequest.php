<?php

namespace App\Http\Requests\Salon;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveLayoutRequest extends FormRequest
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
            'floors' => ['required', 'array'],
            'floors.*.name' => ['required', 'string'],
            'floors.*.position' => ['required', 'integer'],
            //--------------------------------------------------
            'floors.*.sections' => ['required', 'array'],
            'floors.*.sections.*.name' => ['required', 'string'],
            'floors.*.sections.*.x' => ['required', 'integer'],
            'floors.*.sections.*.y' => ['required', 'integer'],
            'floors.*.sections.*.image' => ['nullable', 'string'],
            //-------------------------------------------------
            'floors.*.sections.*.seats' => ['required', 'array'],
            'floors.*.sections.*.seats.*.row' => ['required', 'integer'],
            //'floors.*.sections.*.seats.*.column_number' => ['required', 'integer'],
            'floors.*.sections.*.seats.*.number' => ['required', 'integer'],
            'floors.*.sections.*.seats.*.customText' => ['required', 'string'],
            'floors.*.sections.*.seats.*.x' => ['required', 'integer'],
            'floors.*.sections.*.seats.*.y' => ['required', 'integer'],
            'floors.*.sections.*.seats.*.type' => ['required', 'in:regular,VIP,wheelchair'],
            'floors.*.sections.*.seats.*.status' => ['required', 'in:available,reserved,sold,blocked'],
            'floors.*.sections.*.seats.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
