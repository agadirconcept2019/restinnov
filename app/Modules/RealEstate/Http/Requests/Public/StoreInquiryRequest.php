<?php

namespace App\Modules\RealEstate\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => ['required','string','max:120'],
            'last_name' => ['required','string','max:120'],
            'email' => ['required','email','max:255'],
            'phone' => ['nullable','string','max:40'],
            'checkin' => ['nullable','date'],
            'checkout' => ['nullable','date','after_or_equal:checkin'],
            'guests' => ['nullable','integer','min:1'],
            'message' => ['nullable','string','max:4000'],
            'status' => ['nullable', Rule::in(['new','contacted','qualified','closed'])],
        ];
    }
}
