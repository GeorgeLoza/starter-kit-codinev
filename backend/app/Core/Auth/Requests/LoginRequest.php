<?php

declare(strict_types=1);

namespace App\Core\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'device'   => ['nullable', 'string', 'max:255'],
        ];
    }
}