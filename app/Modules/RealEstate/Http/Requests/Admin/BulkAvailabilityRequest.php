<?php

namespace App\Modules\RealEstate\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAvailabilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'status' => ['required', Rule::in(['available', 'blocked', 'booked', 'pending'])],
            'price_per_night' => ['nullable', 'numeric', 'min:0'],
            'minimum_stay' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }
}
