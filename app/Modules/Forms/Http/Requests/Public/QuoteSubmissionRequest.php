<?php

namespace App\Modules\Forms\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class QuoteSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:140'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'property_interest' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'preferred_contact_method' => ['nullable', 'string', 'max:80'],
            'company_name' => ['nullable', 'max:0'],
            'captcha_token' => ['nullable', 'string'],
        ];
    }
}
