<?php

namespace App\Modules\RealEstate\Http\Requests\Admin;

use App\Modules\RealEstate\Services\IcalService;
use Illuminate\Foundation\Http\FormRequest;

class StoreIcalFeedRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'feed_url' => ['required', 'url', 'max:1000', function ($attribute, $value, $fail) {
                if (! app(IcalService::class)->validateUrl((string) $value)) {
                    $fail('The iCal feed URL failed SSRF safety validation.');
                }
            }],
            'is_active' => ['nullable', 'boolean'],
            'sync_interval_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ];
    }
}
