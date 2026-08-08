<?php

namespace App\Http\Requests\Salon;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalonLayoutRequest extends FormRequest
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
            // salon
            /*             'name' => ['required', 'min:3', Rule::unique('salons', 'name')->ignore($this->salon)],
            'address' => ['required', 'string', 'min:3'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // categories
            'main_category' => ['required', 'exists:categories,id'],
            'child_categories' => ['nullable', 'array'],
            'child_categories.*' => ['exists:categories,id'], */

            // Floors
            'floors' => ['required', 'array'],
            'floors.*.id' => ['sometimes', 'exists:floors,id'],
            'floors.*.name' => ['required', 'string', 'min:3'],
            'floors.*.position' => ['required', 'integer'],

            // Sections
            'floors.*.sections' => ['required', 'array'],
            'floors.*.sections.*.id' => ['sometimes', 'exists:sections,id'],
            'floors.*.sections.*.name' => ['sometimes', 'min:3'],
            'floors.*.sections.*.position' => ['sometimes', 'integer'],
            'floors.*.sections.*.image' => ['nullable', 'image'],

            // Seats
            'floors.*.sections.*.seats' => [
                'required',
                'array',
            ],

            'floors.*.sections.*.seats.*.id' => [
                'nullable',
                'exists:seats,id',
            ],

            'floors.*.sections.*.seats.*.row' => [
                'required',
                'integer',
                'min:1',
            ],

            'floors.*.sections.*.seats.*.number' => [
                'required',
                'integer',
                'min:1',
            ],
            'floors.*.sections.*.seats.*.customText' => [
                'required',
                'string',
                'min:3',
            ],

            /*             'floors.*.sections.*.seats.*.column_number' => [
                'required',
                'integer',
                'min:1',
            ], */

            'floors.*.sections.*.seats.*.x' => [
                'required',
                'integer',
            ],

            'floors.*.sections.*.seats.*.y' => [
                'required',
                'integer',
            ],

            'floors.*.sections.*.seats.*.type' => [
                'required',
                Rule::in([
                    'regular',
                    'VIP',
                    'wheelchair'
                ]),
            ],

            'floors.*.sections.*.seats.*.status' => [
                'required',
                Rule::in([
                    'available',
                    'reserved',
                    'sold',
                    'blocked'
                ]),
            ],

            'floors.*.sections.*.seats.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

        ];
    }
}
