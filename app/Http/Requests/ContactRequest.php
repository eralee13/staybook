<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'phone' => 'required|min:3|max:255',
            'email' => 'required|min:3|max:255',
            'phone2' => 'required|min:3|max:255',
            'address' => 'required|min:3|max:255',
            'whatsapp' => 'required|min:5|max:255',
            'telegram' => 'required|min:5|max:255',
            'instagram' => 'required|min:5|max:255'
        ];
        return $rules;
    }

    public function messages()
    {
        return[
            'required'=>'Поле :attribute обязательно для ввода',
            'min' => 'Поле :attribute должно иметь минимум :min символов',
            //'code.min' => 'Поле код должно содержать не менее :min символов'
        ];
    }
}
