<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'verification_code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:25',
        ];
    }
}
