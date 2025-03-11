<?php

namespace App\Http\Requests\MultiForm;

use Illuminate\Foundation\Http\FormRequest;

class HotelTwoRequest extends FormRequest
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
            'description' => 'required|min:5',
            //'description_en' => 'required|min:5',
            'count' => 'required|min:1',
            'checkin' => 'required|min:2',
            'checkout' => 'required|min:2',
            'early_in' => 'required|min:2',
            'early_out' => 'required|min:2',
            'rating' => 'required|min:1',
            'phone' => 'required|min:5',
            'email' => 'required|min:5',
            //'image' => 'image|mimes:jpg,bmp,png,svg,jpeg,webp|max:3000'
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
