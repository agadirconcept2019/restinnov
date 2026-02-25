<?php

namespace App\Modules\CmsPages\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:180', Rule::unique('pages', 'slug')->ignore($this->route('page'))],
            'template' => ['required', Rule::in(['home', 'services', 'rd', 'faq', 'contact', 'legal'])],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],

            'title_en' => ['required', 'string', 'max:255'],
            'title_fr' => ['nullable', 'string', 'max:255'],
            'title_es' => ['nullable', 'string', 'max:255'],

            'content_en' => ['nullable', 'string'],
            'content_fr' => ['nullable', 'string'],
            'content_es' => ['nullable', 'string'],

            'template_data_en' => ['nullable', 'json'],
            'template_data_fr' => ['nullable', 'json'],
            'template_data_es' => ['nullable', 'json'],

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
