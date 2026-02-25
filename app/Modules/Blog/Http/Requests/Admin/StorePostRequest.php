<?php

namespace App\Modules\Blog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:180', Rule::unique('posts', 'slug')->ignore($this->route('post'))],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:post_categories,id'],

            'title_en' => ['required', 'string', 'max:255'],
            'title_fr' => ['nullable', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],
            'excerpt_en' => ['nullable', 'string', 'max:1000'],
            'excerpt_fr' => ['nullable', 'string', 'max:1000'],
            'excerpt_es' => ['nullable', 'string', 'max:1000'],
            'content_en' => ['nullable', 'string'],
            'content_fr' => ['nullable', 'string'],
            'content_es' => ['nullable', 'string'],
            'meta_title_en' => ['nullable', 'string', 'max:255'],
            'meta_title_fr' => ['nullable', 'string', 'max:255'],
            'meta_title_es' => ['nullable', 'string', 'max:255'],
            'meta_description_en' => ['nullable', 'string', 'max:1000'],
            'meta_description_fr' => ['nullable', 'string', 'max:1000'],
            'meta_description_es' => ['nullable', 'string', 'max:1000'],
            'canonical_url_en' => ['nullable', 'url', 'max:255'],
            'canonical_url_fr' => ['nullable', 'url', 'max:255'],
            'canonical_url_es' => ['nullable', 'url', 'max:255'],
        ];
    }
}
