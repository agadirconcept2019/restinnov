<?php

namespace App\Modules\RealEstate\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('from_date') || ! $this->filled('to_date')) {
                return;
            }

            $days = (int) ((strtotime((string) $this->input('to_date')) - strtotime((string) $this->input('from_date'))) / 86400) + 1;
            if ($days > 90) {
                $validator->errors()->add('to_date', 'The selected range cannot exceed 90 days.');
            }
        });
    }
}
