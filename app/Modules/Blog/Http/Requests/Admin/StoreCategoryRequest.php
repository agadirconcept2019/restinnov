<?php

namespace App\Modules\Blog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:160', Rule::unique('post_categories', 'slug')->ignore($this->route('category'))],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'name_en' => ['required', 'string', 'max:180'],
            'name_fr' => ['nullable', 'string', 'max:180'],
            'name_es' => ['nullable', 'string', 'max:180'],
        ];
    }
}
