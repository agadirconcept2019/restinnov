<?php

namespace App\Modules\RealEstate\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slug' => ['required','string','max:180','alpha_dash', Rule::unique('properties','slug')->ignore($this->route('property'))],
            'status' => ['required', Rule::in(['draft','published','archived'])],
            'property_type_id' => ['required','exists:property_types,id'],
            'rental_mode_id' => ['required','exists:rental_modes,id'],
            'city_id' => ['required','exists:cities,id'],
            'area_id' => ['nullable','exists:areas,id'],
            'owner_user_id' => ['nullable','exists:users,id'],
            'base_price_per_night' => ['required','numeric','min:0'],
            'currency' => ['required','string','size:3'],
            'max_guests' => ['required','integer','min:1'],
            'bedrooms' => ['required','integer','min:0'],
            'beds' => ['required','integer','min:0'],
            'bathrooms' => ['required','integer','min:0'],
            'title_en' => ['required','string','max:255'],
            'title_fr' => ['nullable','string','max:255'],
        ];
    }
}
