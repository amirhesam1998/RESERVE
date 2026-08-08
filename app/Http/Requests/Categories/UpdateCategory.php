<?php

namespace App\Http\Requests\categories;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategory extends FormRequest
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
            'name' => ['required', 'min:3'],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')]
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $category = $this->route('category');

                if (in_array($this->parent_id, $category->decentialids())) {
                    $validator->errors()->add(
                        'parent_id',
                        'A category cannot be assigned to one of its descendants.'
                    );
                }

                if ($this->parent_id === $category->id) {
                    $validator->errors()->add(
                        'parent_id',
                        'A category cannot be its own parent.'
                    );
                }
            }
        ];
    }
}
