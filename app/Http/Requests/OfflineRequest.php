<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfflineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => 'required|string',
            'type' => 'required|string',
            'rating' => 'required|string',
            'room_count' => 'required',
            'accommodation' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required',
            'meal' => 'nullable|array',
            'meal.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'required'=>'Поле :attribute обязательно для ввода',
        ];
    }
}
