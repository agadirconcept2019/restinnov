<?php

namespace App\Modules\Forms\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'subject_type' => ['required', Rule::in(['owner', 'traveler', 'other'])],
            'website_url' => ['nullable', 'url', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'company_name' => ['nullable', 'max:0'],
            'captcha_token' => ['nullable', 'string'],
        ];
    }
}
