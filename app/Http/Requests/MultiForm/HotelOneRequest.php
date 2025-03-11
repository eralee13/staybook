<?php

namespace App\Http\Requests\MultiForm;

use Illuminate\Foundation\Http\FormRequest;

class HotelOneRequest extends FormRequest
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
            'title' => 'required|min:3|max:255',
            'title_en' => 'required|min:3|max:255',
            'type' => 'required',
            'city' => 'required',
            'address' => 'required',
            'address_en' => 'required',
            'lat' => 'required|min:5',
            'lng' => 'required|min:5',
        ];
        return $rules;
    }

    public function messages()
    {
        return[
            'required'=>'This field :attribute is required',
            'min' => 'This field :attribute must contain at least :min characters',
        ];
    }
}
