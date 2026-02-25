<?php

namespace App\Modules\Forms\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class QuoteSubmissionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'property_interest' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'preferred_contact_method' => ['nullable', 'string', 'max:120'],
            'company_name' => ['nullable', 'max:0'],
            'submitted_at' => ['required', 'integer', 'max:'.(time() - 2)],
            'captcha_token' => ['nullable', 'string', 'max:400'],
        ];
    }
}
