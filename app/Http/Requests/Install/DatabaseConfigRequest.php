<?php

namespace App\Http\Requests\Install;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'db_prefix' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9_]*$/'],
        ];
    }
}
