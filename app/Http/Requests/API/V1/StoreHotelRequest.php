<?php

namespace App\Http\Requests\API\V1;

use App\Models\Hotel;
use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
//        $user = $this->user;
//
//        return $user != null && $user->tokenCan('create');
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
            'description' => 'required|min:5',
            'description_en' => 'required|min:5',
            'type' => 'required|min:2',
            'count' => 'required|min:1',
            'checkin' => 'required|min:2',
            'checkout' => 'required|min:2',
            'early_in' => 'required|min:2',
            'early_out' => 'required|min:2',
            'rating' => 'required|min:1',
            'address' => 'required|min:5',
            'address_en' => 'required|min:5',
            'lat' => 'required|min:5',
            'lng' => 'required|min:5',
            'phone' => 'required|min:5',
            'email' => 'required|min:5',
            'image' => 'image|mimes:jpg,bmp,png,svg,jpeg,webp|max:3000'
        ];
        return $rules;
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return[
            'required'=>'This field :attribute is required',
            'min' => 'This field :attribute must contain at least :min characters',
        ];
    }
}
