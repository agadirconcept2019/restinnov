<?php

namespace App\Modules\OwnerPortal\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmNoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'max:4000'],
            'visibility' => ['nullable', Rule::in(['owner', 'admin'])],
        ];
    }
}
