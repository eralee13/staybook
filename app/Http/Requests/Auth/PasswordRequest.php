<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation cancellations that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'current_password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return[
            'required'=>'Поле :attribute обязательно для ввода',
            'failed' => 'Неверные данные'
        ];
    }
}
